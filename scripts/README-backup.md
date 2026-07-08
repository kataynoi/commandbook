# คู่มือติดตั้ง Backup — commandbook → NAS (Synology 192.168.10.36)

สำรอง **ฐานข้อมูล `command_db`** + **ไฟล์ `src/writable/uploads`** ไปเก็บบน NAS
ผ่าน SMB/CIFS mount ทุกวันเวลา **20.00 น.** เก็บย้อนหลัง **14 วัน**

ทำทั้งหมดบน **Production Server `192.168.10.60`** (Linux + Docker)

---

## 1) เตรียมบัญชี NAS
บน DSM ของ NAS (192.168.10.36):
- สร้าง user เช่น `backup_svc` ให้สิทธิ์ **read/write เฉพาะ shared folder `backup_dechachit`**
- เปิด **Control Panel → File Services → SMB** ให้รองรับ SMB3
- ยืนยันชื่อ shared folder จริง (โครงสร้างที่เห็นคือ `backup_dechachit/commandbook`)

## 2) ติดตั้ง cifs-utils + mount ถาวร
```bash
sudo apt-get update && sudo apt-get install -y cifs-utils
sudo mkdir -p /mnt/nas-commandbook

# credential file (อย่าใส่รหัสใน fstab ตรงๆ)
sudo tee /etc/cifs-commandbook.cred >/dev/null <<'EOF'
username=backup_svc
password=<NAS_PASSWORD>
EOF
sudo chmod 600 /etc/cifs-commandbook.cred
```
เพิ่มบรรทัดใน `/etc/fstab` (mount share `backup_dechachit`):
```
//192.168.10.36/backup_dechachit  /mnt/nas-commandbook  cifs  credentials=/etc/cifs-commandbook.cred,iocharset=utf8,vers=3.0,file_mode=0640,dir_mode=0750,_netdev,nofail  0  0
```
ทดสอบ mount:
```bash
sudo mount -a && ls /mnt/nas-commandbook
```

## 3) วาง script
```bash
sudo mkdir -p /opt/commandbook-backup
sudo cp scripts/backup.sh /opt/commandbook-backup/backup.sh
sudo chmod 750 /opt/commandbook-backup/backup.sh
```
ตรวจค่าตัวแปรหัวไฟล์ให้ตรงเครื่องจริง โดยเฉพาะ **`PROJECT_DIR`** (โฟลเดอร์ที่มี `docker-compose.yml`)

## 4) ตั้ง cron ทุกวัน 20.00 น.
```bash
sudo crontab -e
```
เพิ่ม:
```
0 20 * * * /opt/commandbook-backup/backup.sh >> /var/log/commandbook-backup.log 2>&1
```
ยืนยัน timezone เป็น Asia/Bangkok:
```bash
timedatectl   # ถ้าไม่ใช่: sudo timedatectl set-timezone Asia/Bangkok
```

---

## โครงสร้างไฟล์ backup บน NAS
```
backup_dechachit/commandbook/
  2026-07-08/
    command_db_2026-07-08_2000.sql.gz
    uploads_2026-07-08_2000.tar.gz
  2026-07-09/
    ...
  logs/backup.log
```

## ทดสอบ / ยืนยันผล
```bash
# รันมือ
sudo /opt/commandbook-backup/backup.sh
cat /mnt/nas-commandbook/commandbook/logs/backup.log

# ตรวจ integrity
gzip -t  /mnt/nas-commandbook/commandbook/<วันนี้>/command_db_*.sql.gz
tar tzf  /mnt/nas-commandbook/commandbook/<วันนี้>/uploads_*.tar.gz | head
```

## กู้คืน (Restore)
```bash
# DB — ทดสอบลง DB ชั่วคราวก่อนเพื่อไม่ทับของจริง
docker exec -i command-db mysql -u root -p command_db_test < <(gunzip < command_db_*.sql.gz)

# uploads
tar xzf uploads_*.tar.gz -C /tmp/restore-test && ls /tmp/restore-test/uploads/commands
# ถ้าจะกู้ของจริง: tar xzf uploads_*.tar.gz -C /opt/commandbook/src/writable
```

## ข้อควรระวัง
- รหัสผ่านอยู่ในไฟล์ chmod 600 (`.cred`, `.env`) และ script ใช้ `MYSQL_PWD` เลี่ยงรหัสโผล่ใน `ps`
- `mountpoint -q` + `nofail` กันเขียนลง local disk / กันบูตค้างเมื่อ NAS หลุด
- ถ้าชื่อ SMB share ไม่ใช่ `backup_dechachit` ให้แก้ที่ `/etc/fstab`
