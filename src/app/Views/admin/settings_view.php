<?= $this->extend('layout/default') ?>
<?= $this->section('content') ?>
<div class="container-fluid mt-4">
    <h3 class="mb-4">ตั้งค่าการเชื่อมต่อ API</h3>
    <?php if (session()->get('success')): ?>
        <div class="alert alert-success"><?= session()->get('success') ?></div>
    <?php endif; ?>
    <div class="card shadow-sm">
        <div class="card-body">
            <form action="<?= site_url('admin/settings') ?>" method="post">
                <?= csrf_field() ?>
                <div class="mb-3">
                    <label for="api_url" class="form-label">API Base URL</label>
                    <input type="url" class="form-control" id="api_url" name="api_url" value="<?= esc($api_url) ?>" placeholder="http://localhost:8081/" required>
                </div>
                <div class="mb-3">
                    <label for="api_username" class="form-label">API Username</label>
                    <input type="text" class="form-control" id="api_username" name="api_username" value="<?= esc($api_username) ?>" required>
                </div>
                <div class="mb-3">
                    <label for="api_password" class="form-label">API Password</label>
                    <input type="password" class="form-control" id="api_password" name="api_password" placeholder="กรอกเพื่อเปลี่ยนรหัสผ่านใหม่">
                    <div class="form-text">เว้นว่างไว้หากไม่ต้องการเปลี่ยนรหัสผ่าน</div>
                </div>
                <button type="submit" class="btn btn-primary">บันทึกการตั้งค่า</button>
            </form>
        </div>
    </div>
</div>
<?= $this->endSection() ?>