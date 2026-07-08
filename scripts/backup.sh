#!/usr/bin/env bash
#
# backup.sh — สำรอง database (command_db) + ไฟล์ writable/uploads ของ commandbook
# ไปเก็บบน NAS Synology (192.168.10.36) ผ่าน SMB/CIFS mount
#
# รันบน Production Server 192.168.10.60 ผ่าน cron ทุกวัน 20.00 น.
# ดูวิธีติดตั้ง/ตั้งเวลาได้ที่ scripts/README-backup.md
#
set -euo pipefail

# ===== ปรับตามเครื่องจริง =====
PROJECT_DIR="/opt/commandbook"              # โฟลเดอร์ที่มี docker-compose.yml บน prod
MOUNT_POINT="/mnt/nas-commandbook"          # จุด mount ของ SMB share
NAS_ROOT="$MOUNT_POINT/commandbook"         # โฟลเดอร์ปลายทางย่อยบน NAS
DB_CONTAINER="command-db"
DB_NAME="command_db"
RETENTION_DAYS=14
# =============================

# โหลด DB_ROOT_PASS จาก .env ของโปรเจกต์ (ไฟล์นี้ควร chmod 600)
DB_ROOT_PASS="$(grep -E '^DB_ROOT_PASS=' "$PROJECT_DIR/.env" | cut -d= -f2-)"
UPLOADS_PARENT="$PROJECT_DIR/src/writable"  # จะ tar เฉพาะโฟลเดอร์ย่อย "uploads"

TS="$(date +%F_%H%M)"                        # เช่น 2026-07-08_2000
DAY="$(date +%F)"
DEST="$NAS_ROOT/$DAY"
LOG="$NAS_ROOT/logs/backup.log"

log() { echo "[$(date '+%F %T')] $*" | tee -a "$LOG"; }
trap 'log "ERROR ที่บรรทัด $LINENO — backup ล้มเหลว"; exit 1' ERR

# 1) ตรวจว่า NAS ถูก mount อยู่จริง (กันการเขียนลง local disk เงียบๆ เวลา NAS หลุด)
mountpoint -q "$MOUNT_POINT" || { echo "NAS ไม่ได้ mount ที่ $MOUNT_POINT — ยกเลิก"; exit 1; }
mkdir -p "$DEST" "$NAS_ROOT/logs"
log "===== เริ่ม backup $TS ====="

# 2) Dump ฐานข้อมูล (consistent, utf8mb4) — ใช้ MYSQL_PWD เลี่ยงรหัสโผล่ใน process list
docker exec -e MYSQL_PWD="$DB_ROOT_PASS" "$DB_CONTAINER" \
  mysqldump -u root --single-transaction --quick --routines --triggers --events \
  --no-tablespaces --default-character-set=utf8mb4 "$DB_NAME" \
  | gzip -9 > "$DEST/${DB_NAME}_${TS}.sql.gz"
gzip -t "$DEST/${DB_NAME}_${TS}.sql.gz"      # ตรวจไฟล์ gzip ไม่เสีย
log "DB dump เสร็จ: $(du -h "$DEST/${DB_NAME}_${TS}.sql.gz" | cut -f1)"

# 3) tar เฉพาะโฟลเดอร์ uploads
tar czf "$DEST/uploads_${TS}.tar.gz" -C "$UPLOADS_PARENT" uploads
log "uploads เสร็จ: $(du -h "$DEST/uploads_${TS}.tar.gz" | cut -f1)"

# 4) Retention — ลบโฟลเดอร์วันที่เก่ากว่า RETENTION_DAYS วัน
while IFS= read -r -d '' olddir; do
  rm -rf "$olddir"
  log "ลบ backup เก่า: $olddir"
done < <(find "$NAS_ROOT" -mindepth 1 -maxdepth 1 -type d -name '20*' -mtime +"$RETENTION_DAYS" -print0)

log "===== backup สำเร็จ ====="
