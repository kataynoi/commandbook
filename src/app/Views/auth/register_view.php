<?php
// --------------------------------------------------------------------
// (3/4) View: register_view.php (Updated)
// --------------------------------------------------------------------
// อัปเดตไฟล์ที่: app/Views/auth/register_view.php
// ** เพิ่ม Dropdown ตำบลและหมู่บ้าน และ JavaScript ที่เกี่ยวข้อง **
$lineData = session()->get('line_register_data');
?>
<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>สมัครสมาชิก</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #f0f2f5;
        }

        .register-container {
            max-width: 800px;
        }

        .password-strength {
            font-size: 0.875em;
        }

        .password-strength .invalid {
            color: #dc3545;
        }

        .password-strength .valid {
            color: #198754;
        }
    </style>
</head>

<body>
    <div class="container register-container mt-5 mb-5">
        <div class="card shadow-lg">
            <div class="card-body p-5">
                <h2 class="card-title text-center mb-4">สร้างบัญชีผู้ใช้งานใหม่</h2>
                <p class="text-center text-muted mb-4">กรุณากรอกข้อมูลและเลือกพื้นที่สังกัดของท่านให้ครบถ้วน</p>

                <?php if (session()->get('errors')): ?>
                    <div class="alert alert-danger">
                        <ul>
                            <?php foreach (session()->get('errors') as $error): ?>
                                <li><?= esc($error) ?></li>
                            <?php endforeach ?>
                        </ul>
                    </div>
                <?php endif; ?>

                <form action="<?= site_url('register') ?>" method="post">
                    <?= csrf_field() ?>

                    <?php if ($lineData): ?>
                        <div class="alert alert-info">
                            กำลังลงทะเบียนโดยเชื่อมต่อกับบัญชี LINE ของคุณ: <strong><?= esc($lineData['fullname']) ?></strong>
                        </div>
                    <?php endif; ?>
                    <div class="row">
                        <div class="col-md-6 mb-3"><label for="fullname" class="form-label">ชื่อ-สกุล</label><input type="text" class="form-control" id="fullname" name="fullname" value="<?= old('fullname') ?>" required></div>
                        <div class="col-md-6 mb-3"><label for="position" class="form-label">ตำแหน่ง</label><input type="text" class="form-control" id="position" name="position" value="<?= old('position') ?>" required></div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="username" class="form-label">ชื่อผู้ใช้ (Username)</label>
                            <input type="text" class="form-control" id="username" name="username" value="<?= old('username') ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="cid" class="form-label">เลขบัตรประชาชน</label>
                            <input type="text" class="form-control" id="cid" name="cid" value="<?= old('cid') ?>" maxlength="13" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="password" class="form-label">รหัสผ่าน</label>
                            <div class="input-group">
                                <input type="text" class="form-control" id="password" name="password" required>
                                <button class="btn btn-outline-secondary" type="button" id="generatePasswordBtn" title="สร้างรหัสผ่านที่ปลอดภัย">
                                    <i class="fas fa-random">สรา้งรหัสผ่าน</i>
                                </button>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="pass_confirm" class="form-label">ยืนยันรหัสผ่าน</label>
                            <input type="text" class="form-control" id="pass_confirm" name="pass_confirm" required>
                        </div>
                    </div>
                    <!-- ส่วนแสดงผลความแข็งแรงของรหัสผ่าน -->
                    <div id="password-feedback" class="password-strength">
                        <div id="length" class="invalid">อย่างน้อย 8 ตัวอักษร</div>
                        <div id="uppercase" class="invalid">มีตัวพิมพ์ใหญ่ (A-Z)</div>
                        <div id="lowercase" class="invalid">มีตัวพิมพ์เล็ก (a-z)</div>
                        <div id="number" class="invalid">มีตัวเลข (0-9)</div>
                        <div id="symbol" class="invalid">มีสัญลักษณ์พิเศษ (!@#$%^&*)</div>
                    </div>

                    <hr>
                    <p class="text-muted">กรุณาเลือกพื้นที่ที่ท่านสังกัด</p>
                    <div class="row">
                        <!-- จังหวัด: ซ่อนหรือ disabled -->
                        <div class="col-md-6 mb-3" style="display:none;">
                            <label for="changwatcode" class="form-label">จังหวัด</label>
                            <select class="form-select" id="changwatcode" name="changwatcode" disabled>
                                <option value="<?= esc($default_province ?? '') ?>">
                                    <?= esc($provinceName ?? '') ?>
                                </option>
                            </select>
                        </div>
                        <!-- เพิ่ม input hidden เพื่อให้ส่ง changwatcode ไปกับฟอร์ม -->
                        <input type="hidden" name="changwatcode" value="<?= esc($default_province ?? '') ?>">
                        <!-- อำเภอ: preload จาก $amphurs -->
                        <div class="col-md-6 mb-3">
                            <label for="ampurcodefull" class="form-label">อำเภอ</label>
                            <select class="form-select" id="ampurcodefull" name="ampurcodefull" required>
                                <option value="">-- เลือกอำเภอ --</option>
                                <?php foreach ($amphurs as $amphur): ?>
                                    <option value="<?= $amphur['ampurcodefull'] ?>" <?= old('ampurcodefull') == $amphur['ampurcodefull'] ? 'selected' : '' ?>>
                                        <?= $amphur['ampurname'] ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3"><label for="hospcode" class="form-label">รพ.สต.</label><select class="form-select" id="hospcode" name="hospcode" required disabled>
                                <option value="">-- กรุณาเลือกอำเภอก่อน --</option>
                            </select></div>
                        <div class="col-md-6 mb-3"><label for="villagecodefull" class="form-label">หมู่บ้าน (ถ้ามี)</label><select class="form-select" id="villagecodefull" name="villagecodefull" disabled>
                                <option value="">-- กรุณาเลือก รพ.สต. ก่อน --</option>
                            </select></div>
                    </div>
                    <div class="d-grid mt-4"><button type="submit" class="btn btn-primary btn-lg">ลงทะเบียน</button></div>
                </form>
            </div>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        $(document).ready(function() {

            const passwordInput = $('#password');
            const passConfirmInput = $('#pass_confirm');
            const feedback = {
                length: $('#length'),
                uppercase: $('#uppercase'),
                lowercase: $('#lowercase'),
                number: $('#number'),
                symbol: $('#symbol')
            };
            // อำเภอ -> รพ.สต.
            $('#ampurcodefull').on('change', function() {
                let amphureCode = $(this).val();
                let hospitalSelect = $('#hospcode');
                $('#villagecodefull').prop('disabled', true).html('<option value="">-- เลือก รพ.สต. ก่อน --</option>');
                hospitalSelect.prop('disabled', true).html('<option value="">-- กำลังโหลด --</option>');
                if (amphureCode) {
                    $.post("<?= site_url('ajax/get-hospitals') ?>", {
                        amphure_code: amphureCode
                    }, function(data) {
                        hospitalSelect.prop('disabled', false).html('<option value="">-- เลือก รพ.สต. --</option>');
                        data.forEach(val => hospitalSelect.append(`<option value="${val.hoscode}">${val.hosname}</option>`));
                    }, 'json');
                } else {
                    hospitalSelect.html('<option value="">-- กรุณาเลือกอำเภอก่อน --</option>');
                }
            });

            // รพ.สต. -> หมู่บ้าน
            $('#hospcode').on('change', function() {
                let hospcode = $(this).val();
                let villageSelect = $('#villagecodefull');
                villageSelect.prop('disabled', true).html('<option value="">-- กำลังโหลด --</option>');
                if (hospcode) {
                    $.post("<?= site_url('ajax/get-villages') ?>", {
                        hospcode: hospcode
                    }, function(data) {
                        villageSelect.prop('disabled', false).html('<option value="">-- เลือกหมู่บ้าน --</option>');
                        if (data.length > 0) {
                            data.forEach(val => villageSelect.append(`<option value="${val.villagecodefull}">${val.villagecodefull}.${val.villagename}</option>`));
                        } else {
                            villageSelect.html('<option value="">-- ไม่พบข้อมูลหมู่บ้านในสังกัด --</option>');
                        }
                    }, 'json');
                } else {
                    villageSelect.html('<option value="">-- กรุณาเลือก รพ.สต. ก่อน --</option>');
                }
            });

            function checkPasswordStrength() {
                const pass = passwordInput.val();

                // Check length
                updateFeedback(feedback.length, pass.length >= 8);
                // Check uppercase
                updateFeedback(feedback.uppercase, /[A-Z]/.test(pass));
                // Check lowercase
                updateFeedback(feedback.lowercase, /[a-z]/.test(pass));
                // Check number
                updateFeedback(feedback.number, /\d/.test(pass));
                // Check symbol
                updateFeedback(feedback.symbol, /[!@#$%^&*]/.test(pass));
            }

            function updateFeedback(element, isValid) {
                if (isValid) {
                    element.removeClass('invalid').addClass('valid');
                } else {
                    element.removeClass('valid').addClass('invalid');
                }
            }

            passwordInput.on('keyup', checkPasswordStrength);

            $('#generatePasswordBtn').on('click', function() {
                const upper = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
                const lower = 'abcdefghijklmnopqrstuvwxyz';
                const numbers = '0123456789';
                const symbols = '!@#$%^&*';
                const allChars = upper + lower + numbers + symbols;
                let generatedPassword = '';

                // Ensure at least one of each type
                generatedPassword += upper[Math.floor(Math.random() * upper.length)];
                generatedPassword += lower[Math.floor(Math.random() * lower.length)];
                generatedPassword += numbers[Math.floor(Math.random() * numbers.length)];
                generatedPassword += symbols[Math.floor(Math.random() * symbols.length)];

                // Fill the rest of the password
                for (let i = 4; i < 12; i++) {
                    generatedPassword += allChars[Math.floor(Math.random() * allChars.length)];
                }

                // Shuffle the password to make it more random
                generatedPassword = generatedPassword.split('').sort(() => 0.5 - Math.random()).join('');

                passwordInput.val(generatedPassword);
                passConfirmInput.val(generatedPassword);
                checkPasswordStrength(); // Update feedback after generating
            });
        });
    </script>
</body>

</html>