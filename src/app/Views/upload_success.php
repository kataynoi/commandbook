<?php
use chillerlan\QRCode\QRCode;
use chillerlan\QRCode\QROptions;

// ตั้งค่า QR Code
$options = new QROptions([
    'outputType' => QRCode::OUTPUT_IMAGE_PNG,
    'eccLevel'   => QRCode::ECC_L,
    'scale'      => 5,
]);

// สร้าง URL ที่ QR Code จะชี้ไป
// เราจะสร้าง Route ชื่อ 'access'
$urlToAccessFile = site_url('access/' . $qr_token); 

// สร้าง QR Code เป็น Base64 image
$qrCodeImage = (new QRCode($options))->render($urlToAccessFile);
?>
<?= $this->extend('layout/default') ?>
<?= $this->section('content') ?>
<div class="container text-center mt-5">
    <div class="alert alert-success">
        <h4 class="alert-heading">อัปโหลดสำเร็จ!</h4>
        <p>คุณสามารถใช้ QR Code นี้เพื่อส่งให้หน่วยงานที่เกี่ยวข้องสแกนเพื่อดาวน์โหลดเอกสาร</p>
    </div>
    
    <div class="mt-4">
        <h4>QR Code สำหรับเข้าถึงเอกสาร</h4>
        <img src="<?= $qrCodeImage ?>" alt="QR Code">
        
        <p class="mt-2">
            <small>URL: <?= $urlToAccessFile ?></small>
        </p>
        
        <a href="<?= site_url('commands/create') ?>" class="btn btn-primary mt-3">
            <i class="bi bi-plus-circle"></i> อัปโหลดเอกสารฉบับอื่น
        </a>
    </div>
</div>
<?= $this->endSection() ?>