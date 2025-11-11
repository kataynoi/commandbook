
<?= $this->extend('layout/default') ?>
<?= $this->section('content') ?>

<?php
$session = session();
$token = $session->getFlashdata('qr_token') ?? null;
$urlToAccessFile = $token ? site_url('access/' . $token) : null;

// สร้าง URL สำหรับภาพ QR โดยใช้ Google Charts API
$qrImageUrl = $urlToAccessFile ? 'https://chart.googleapis.com/chart?cht=qr&chs=300x300&chl=' . urlencode($urlToAccessFile) : null;
?>

<div class="container text-center mt-5">
    <div class="alert alert-success">
        <h4 class="alert-heading">อัปโหลดสำเร็จ!</h4>
        <p>คุณสามารถใช้ QR Code นี้เพื่อส่งให้หน่วยงานที่เกี่ยวข้องสแกนเพื่อดาวน์โหลดเอกสาร</p>
    </div>
    
    <?php if ($token && $qrImageUrl): ?>
    <div class="mt-4">
        <h4>QR Code สำหรับเข้าถึงเอกสาร</h4>
        <img src="<?= esc($qrImageUrl) ?>" alt="QR Code" class="img-fluid">
        
        <p class="mt-2">
            <small>URL: <a href="<?= esc($urlToAccessFile) ?>" target="_blank"><?= esc($urlToAccessFile) ?></a></small>
        </p>
        
        <a href="<?= site_url('commands/new') ?>" class="btn btn-primary mt-3">
            <i class="bi bi-plus-circle"></i> อัปโหลดเอกสารฉบับอื่น
        </a>
    </div>
    <?php else: ?>
    <div class="mt-4">
        <p class="text-muted">ไม่พบข้อมูลเอกสาร (QR Token หายไป) กรุณากลับไปที่หน้าจัดการเอกสาร</p>
        <a href="<?= site_url('commands/new') ?>" class="btn btn-primary mt-3">
            <i class="bi bi-plus-circle"></i> อัปโหลดเอกสารฉบับอื่น
        </a>
    </div>
    <?php endif; ?>
</div>

<div class="container mt-4">
    <h3>อัปโหลดสำเร็จ</h3>
    <?php if ($token): ?>
        <p>QR Token: <?= esc($token) ?></p>
        <p><a href="<?= site_url('access/' . $token) ?>" target="_blank">ดาวน์โหลดเอกสาร</a></p>
    <?php endif; ?>
</div>

<?= $this->endSection() ?>