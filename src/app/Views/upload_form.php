<?= $this->extend('layout/default') ?>
<?= $this->section('content') ?>

<div class="container mt-4">
    <h3><i class="bi bi-file-earmark-arrow-up"></i> อัปโหลดหนังสือคำสั่ง</h3>
    
    <?php
    $errors = session()->getFlashdata('errors');
    if ($errors) :
        if (is_array($errors)) :
            foreach ($errors as $err) : ?>
                <div class="alert alert-danger"><?= esc($err) ?></div>
            <?php endforeach;
        else: ?>
            <div class="alert alert-danger"><?= esc($errors) ?></div>
        <?php endif;
    endif;
    ?>

    <?php if(session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?= session()->getFlashdata('error') ?>
        </div>
    <?php endif; ?>

    <form action="<?= site_url('commands/save') ?>" method="post" enctype="multipart/form-data">
        <?= csrf_field() ?>
        
        <div class="row">
            <div class="col-md-6 mb-3">
                <label for="doc_number" class="form-label">เลขหนังสือคำสั่ง</label>
                <input type="text" class="form-control" id="doc_number" name="doc_number" value="<?= esc(old('doc_number')) ?>" required>
            </div>
            <div class="col-md-6 mb-3">
                <label for="doc_date" class="form-label">วันที่ออกคำสั่ง</label>
                <input type="date" class="form-control" id="doc_date" name="doc_date" value="<?= esc(old('doc_date')) ?>" required>
            </div>
        </div>
        
        <div class="mb-3">
            <label for="doc_title" class="form-label">ชื่อเรื่อง</label>
            <input type="text" class="form-control" id="doc_title" name="doc_title" value="<?= esc(old('doc_title')) ?>" required>
        </div>

        <div class="mb-3">
            <label for="command_file" class="form-label">เลือกไฟล์คำสั่ง (PDF เท่านั้น)</label>
            <input class="form-control" type="file" id="command_file" name="command_file" accept="application/pdf" required>
        </div>

        <div class="mb-3">
            <label for="hospcodes" class="form-label">กำหนดสิทธิ์การเข้าถึง (เลือกได้หลายหน่วยงาน)</label>
            <select class="form-select" id="hospcodes" name="hospcodes[]" multiple size="10" required>
                <?php
                    // ถ้า controller ส่ง $hospitals มาให้ ให้วนแสดงค่าจาก DB
                    $selected = old('hospcodes') ?? [];
                    if (!is_array($selected)) {
                        $selected = [$selected];
                    }

                    if (!empty($hospitals) && is_array($hospitals)) :
                        foreach ($hospitals as $h) :
                            $code = esc($h['hospcode']);
                            $name = esc($h['hospname'] ?? $h['hosname'] ?? $h['hospname']);
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

<?= $this->endSection() ?>