<?php
// --------------------------------------------------------------------
// (2/2) View: login_view.php
// --------------------------------------------------------------------
// อัปเดตไฟล์ที่: app/Views/auth/login_view.php
// ** ปรับปรุงส่วนแสดงข้อความแจ้งเตือน **
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <title>เข้าสู่ระบบ - SMIV CARE</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f8f9fa;
        }

        .card {
            border-radius: 1rem;
        }

        .form-floating label {
            padding-left: 0.75rem;
        }

        .form-control:focus {
            box-shadow: none;
        }
    </style>
</head>

<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-5 col-md-8">
                <div class="card shadow-lg border-0 rounded-lg mt-5">
                    <div class="card-header bg-primary text-white">
                        <h3 class="text-center font-weight-light my-4">SMIV CARE</h3>
                    </div>
                    <div class="card-body p-4">

                        <!-- *** START: จุดที่แก้ไข *** -->
                        <!-- แสดงข้อความแจ้งเตือน (Error) -->
                        <?php if (session()->getFlashdata('error')): ?>
                            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                                <?= session()->getFlashdata('error') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>

                        <!-- แสดงข้อความแจ้งเตือน (Success) -->
                        <?php if (session()->getFlashdata('success')): ?>
                            <div class="alert alert-success alert-dismissible fade show" role="alert">
                                <?= session()->getFlashdata('success') ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                            </div>
                        <?php endif; ?>
                        <!-- *** END: จุดที่แก้ไข *** -->

                        <form action="<?= site_url('login') ?>" method="post">
                            <?= csrf_field() ?>
                            <div class="form-floating mb-3">
                                <input class="form-control" id="inputUsername" name="username" type="text" placeholder="ชื่อผู้ใช้" value="<?= old('username') ?>" required />
                                <label for="inputUsername"><i class="fas fa-user me-2"></i>ชื่อผู้ใช้ (Username)</label>
                            </div>
                            <div class="form-floating mb-3">
                                <input class="form-control" id="inputPassword" name="password" type="password" placeholder="รหัสผ่าน" required />
                                <label for="inputPassword"><i class="fas fa-lock me-2"></i>รหัสผ่าน (Password)</label>
                            </div>
                            <div class="d-grid mt-4">
                                <button type="submit" class="btn btn-primary btn-lg">เข้าสู่ระบบ</button>
                            </div>

                        </form>
                        <div class="text-center my-3">หรือ</div>
                        <div class="d-grid">
                            <a href="<?= site_url('login/line') ?>" class="btn btn-success btn-lg">
                                <i class="fab fa-line"></i> เข้าสู่ระบบด้วย LINE
                            </a>
                        </div>
                    </div>
                    <div class="card-footer text-center py-3">
                        <!-- <div class="small"><a href="<?= site_url('register') ?>">ยังไม่มีบัญชี? สมัครสมาชิกที่นี่</a></div> -->
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>