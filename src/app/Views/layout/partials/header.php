<?php
$userRoles = session()->get('roles') ?? [];
// ถ้าต้องการให้สิทธิ์ 'Edit' (ID 5) เพิ่มได้ด้วย ให้ใช้ if(in_array(4, $userRoles) || in_array(5, $userRoles))
?>
<nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
    <!-- Navbar Brand-->
    <a class="navbar-brand ps-3" href="<?= site_url('/dashboard'); ?>">หนังสือคำสั่ง  สสจ. Mahasarakham</a>
    <!-- Sidebar Toggle-->
    <button class="btn btn-link btn-sm order-1 order-lg-0 me-4 me-lg-0" id="sidebarToggle" href="#!"><i class="fas fa-bars"></i></button>
    <!-- Navbar Search-->
    <form class="d-none d-md-inline-block form-inline ms-auto me-0 me-md-3 my-2 my-md-0">
        <div class="input-group">
            <input class="form-control" type="text" placeholder="Search for..." aria-label="Search for..." aria-describedby="btnNavbarSearch" />
            <button class="btn btn-primary" id="btnNavbarSearch" type="button"><i class="fas fa-search"></i></button>
        </div>
    </form>
    <!-- Navbar-->

   
    <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-user fa-fw"></i>
                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?= esc(session()->get('fullname')) ?>[<?= esc(session()->get('position')) ?>]
                </span>
            </a>

            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                <li>
                    <?php
                    if (in_array(1, $userRoles) || in_array(2, $userRoles)):
                    ?>
                        <a class="dropdown-item" href="<?= base_url('/admin/manage-users') ?>">
                            <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                            ผู้ใช้งานทั้งหมด
                        </a>
                    <?php endif; ?>
                </li>
                <?php if (in_array(1, $userRoles)): ?>
                <li>
                    <a class="dropdown-item" href="<?= base_url('/activity-logs') ?>">
                        <i class="fas fa-history fa-sm fa-fw mr-2 text-gray-400"></i>
                        Activity Logs
                    </a>
                </li>
                <?php endif; ?>
                <li>
                    <hr class="dropdown-divider" />
                </li>
                <li><a type="button" class="dropdown-item" data-bs-toggle="modal" data-bs-target="#logoutModal"> Logout</a></li>
            </ul>
        </li>
    </ul>
</nav>

<!-- Logout Modal-->

<div class="modal fade" id="logoutModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel"
    aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">คุณต้องการออกจากระบบ?</h5>
                <button class="close" type="button" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">×</span>
                </button>
            </div>
            <div class="modal-body">เลือก 'ออกจากระบบ' ด้านล่าง หากคุณพร้อมที่จะสิ้นสุดเซสชันปัจจุบันของคุณ</div>
            <div class="modal-footer">
                <button class="btn btn-secondary" type="button" data-dismiss="modal">ยกเลิก</button>
                <a class="btn btn-primary" href="<?= site_url("logout") ?>">ออกจากระบบ</a>
            </div>
        </div>
    </div>
</div>

<!-- กล่องแจ้งเตือน -->
