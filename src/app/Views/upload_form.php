<?= $this->extend('layout/default') ?>
<?= $this->section('content') ?>

<div class="container mt-4">
    <h3><?= isset($doc['id']) ? 'แก้ไขเอกสาร' : 'อัปโหลดเอกสารใหม่' ?></h3>
    <hr>
    <form action="<?= site_url('commands/save') ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        
        <!-- เพิ่ม Hidden Field สำหรับ ID -->
        <input type="hidden" name="id" value="<?= esc(isset($doc['id']) ? $doc['id'] : '') ?>">

        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="doc_number" class="form-label">เลขหนังสือคำสั่ง</label>
                <input type="text" class="form-control" id="doc_number" name="doc_number" value="<?= esc(old('doc_number', isset($doc['doc_number']) ? $doc['doc_number'] : '')) ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="doc_date" class="form-label">วันที่ออกคำสั่ง</label>
                <input type="date" class="form-control" id="doc_date" name="doc_date" value="<?= esc(old('doc_date', isset($doc['doc_date']) ? $doc['doc_date'] : ''))?>" required>
            </div>
        </div>
        
        <div class="mb-3">
            <label for="doc_title" class="form-label">ชื่อเรื่อง</label>
            <input type="text" class="form-control" id="doc_title" name="doc_title" value="<?= esc(old('doc_title', isset($doc['doc_title']) ? $doc['doc_title'] : '')) ?>" required>
        </div>

        <div class="mb-3">
            <label for="description" class="form-label">คำขยายความ (ถ้ามี)</label>
            <textarea class="form-control" id="description" name="description" rows="3"><?= esc(old('description', isset($doc['description']) ? $doc['description'] : '')) ?></textarea>
        </div>

        <div class="mb-3">
            <label for="command_file" class="form-label">เลือกไฟล์คำสั่ง (PDF เท่านั้น)</label>
            <input class="form-control" type="file" id="command_file" name="command_file" accept="application/pdf" required>
        </div>

        <div class="mb-3">
            <div class="form-check">
                <input class="form-check-input" type="checkbox" id="is_public" name="is_public" value="1"
                    <?= (old('is_public') == '1' || (isset($doc['is_public']) && $doc['is_public'] == 1)) ? 'checked' : '' ?>>
                <label class="form-check-label fw-bold" for="is_public">
                    คำสั่งไม่เป็นความลับ เข้าถึงได้โดยสาธารณะ
                </label>
                <div class="form-text text-muted">เมื่อเปิดใช้งาน ผู้ใช้ทั่วไปสามารถ Scan QR Code เพื่อเปิดเอกสารได้โดยไม่ต้อง Login</div>
            </div>
        </div>

        <div class="mb-3">
            <div class="form-check">
                <?php
                    // ค่าเริ่มต้น: checked (เอกสารใหม่หรือไม่มีค่าใน DB ให้ checked)
                    $watermarkChecked = true;
                    if (old('add_watermark') !== null) {
                        $watermarkChecked = old('add_watermark') == '1';
                    } elseif (isset($doc['add_watermark'])) {
                        $watermarkChecked = (int) $doc['add_watermark'] === 1;
                    }
                ?>
                <input class="form-check-input" type="hidden" name="add_watermark_present" value="1">
                <input class="form-check-input" type="checkbox" id="add_watermark" name="add_watermark" value="1"
                    <?= $watermarkChecked ? 'checked' : '' ?>>
                <label class="form-check-label fw-bold" for="add_watermark">
                    ใส่ลายน้ำผู้ดาวน์โหลดในเอกสาร
                </label>
                <div class="form-text text-muted">เมื่อเปิดใช้งาน เอกสาร PDF ที่ถูกดาวน์โหลดจะมีลายน้ำชื่อผู้ดาวน์โหลดปรากฏในทุกหน้า</div>
            </div>
        </div>

        <div class="mb-3" id="hospcodes-wrapper">
            <label for="hospcodes" class="form-label">กำหนดสิทธิ์การเข้าถึง (เลือกได้หลายหน่วยงาน)</label>
            <select class="form-select" id="hospcodes" name="hospcodes[]" multiple size="26" required>
                <?php
                    // ถ้า controller ส่ง $hospitals มาให้ ให้วนแสดงค่าจาก DB
                    $selected = old('hospcodes');
                    if ($selected === null) {
                        $selected = [];
                    }
                    if (!is_array($selected)) {
                        $selected = [$selected];
                    }
                    if (!empty($hospitals)):
                        foreach ($hospitals as $h):
                            $code = esc($h['hospcode']);
                            $name = esc(isset($h['hospname']) ? $h['hospname'] : (isset($h['hosname']) ? $h['hosname'] : ''));
                            $isSelected = in_array($code, $selected) ? 'selected' : '';
                ?>
                    <option value="<?= $code ?>" <?= $isSelected ?>><?= $name ?> (<?= $code ?>)</option>
                <?php
                        endforeach;
                    else:
                ?>
                    <option value="">ยังไม่มีรายการหน่วยบริการ</option>
                <?php endif; ?>
            </select>
        </div>

        <button type="submit" class="btn btn-primary">
            <i class="bi bi-save"></i> บันทึกและอัปโหลด
        </button>
    </form>
</div>

<script>
(function () {
    const checkbox = document.getElementById('is_public');
    const wrapper  = document.getElementById('hospcodes-wrapper');
    const select   = document.getElementById('hospcodes');

    function toggleHospcodes(isPublic) {
        select.disabled = isPublic;
        select.required = !isPublic;
        wrapper.style.opacity = isPublic ? '0.4' : '1';
        wrapper.style.pointerEvents = isPublic ? 'none' : '';
    }

    // ตั้งค่าเริ่มต้นตามสถานะ checkbox ตอนโหลดหน้า
    toggleHospcodes(checkbox.checked);

    checkbox.addEventListener('change', function () {
        toggleHospcodes(this.checked);
    });
})();
</script>

<?= $this->endSection() ?>