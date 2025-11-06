<?php
// --------------------------------------------------------------------
// (2/4) View: import_view.php
// --------------------------------------------------------------------
// สร้าง View ใหม่ทั้งหมดที่: app/Views/import/import_view.php
?>
<?= $this->extend('layout/default') ?>
<?= $this->section('content') ?>

<div class="container-fluid mt-4">
    <h3 class="mb-4">นำเข้าข้อมูลผู้ป่วยด้วย Excel</h3>

    <div class="row">
        <div class="col-lg-8">
            <div class="card shadow-sm">
                <div class="card-header">
                    <h5 class="mb-0"><i class="fas fa-file-excel"></i> ขั้นตอนการนำเข้าข้อมูล</h5>
                </div>
                <div class="card-body">
                    <!-- แสดงข้อความแจ้งเตือน -->
                    <?php if (session()->get('success')): ?>
                        <div class="alert alert-success"><?= session()->get('success') ?></div>
                    <?php endif; ?>
                    <?php if (session()->get('error')): ?>
                        <div class="alert alert-danger"><?= session()->get('error') ?></div>
                    <?php endif; ?>

                    <ol class="list-group list-group-numbered">
                        <li class="list-group-item">ดาวน์โหลดไฟล์เทมเพลต Excel <a href="<?= site_url('import/download-template') ?>"><b>คลิกที่นี่</b></a></li>
                        <li class="list-group-item">กรอกข้อมูลผู้ป่วยลงในไฟล์ (ห้ามลบหรือแก้ไขหัวข้อคอลัมน์)</li>
                        <li class="list-group-item">คอลัมน์ `id_card` และ `screening_date` เป็นข้อมูลที่จำเป็นต้องกรอก</li>
                        <li class="list-group-item">บันทึกไฟล์และอัปโหลดโดยใช้ฟอร์มด้านล่างนี้</li>
                    </ol>
                    <hr>
                    <form action="<?= site_url('import/patients/upload') ?>" method="post" enctype="multipart/form-data">
                        <?= csrf_field() ?>
                        <div class="mb-3">
                            <label for="excel_file" class="form-label">เลือกไฟล์ Excel (.xlsx)</label>
                            <input class="form-control" type="file" name="excel_file" id="excel_file" accept=".xlsx" required>
                        </div>
                        <button type="submit" class="btn btn-primary"><i class="fas fa-upload"></i> เริ่มการนำเข้า</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?= $this->endSection() ?>