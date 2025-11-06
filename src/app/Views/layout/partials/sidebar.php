<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <div class="nav">
                <div class="sb-sidenav-menu-heading">หน้าหลัก</div>
                <a class="nav-link" href="<?= site_url('/dashboard'); ?>">
                    <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                    Dashboard
                </a>
                <a class="nav-link" href="<?= site_url('/patients'); ?>">
                    <div class="sb-nav-link-icon"><i class="fas fa-tachometer-alt"></i></div>
                    ผู้ป่วย
                </a>

                <div class="sb-sidenav-menu-heading">รายงาน</div>
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseLayouts" aria-expanded="false" aria-controls="collapseLayouts">
                    <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
                    รายงานมาตรฐาน
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <div class="collapse" id="collapseLayouts" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">

                        <a class="nav-link" href="<?= site_url('reports/patient-summary') ?>">จำนวนผู้ป่วยที่ละทะเบียน</a>
                        <a class="nav-link" href="<?= site_url('reports/visit-summary') ?>">จำนวนผู้ป่วยที่ได้รับการเยี่ยม</a>
                        <!-- <a class="nav-link" href="<?= site_url('reports/visit-summary') ?>">จำนวนการเยี่ยมบ้าน</a> -->
                    </nav>
                </div>
                <a class="nav-link collapsed" href="#" data-bs-toggle="collapse" data-bs-target="#collapseLayouts" aria-expanded="false" aria-controls="collapseLayouts">
                    <div class="sb-nav-link-icon"><i class="fas fa-columns"></i></div>
                    รายงานงานใช้งาน
                    <div class="sb-sidenav-collapse-arrow"><i class="fas fa-angle-down"></i></div>
                </a>
                <!-- <div class="collapse" id="collapseLayouts" aria-labelledby="headingOne" data-bs-parent="#sidenavAccordion">
                    <nav class="sb-sidenav-menu-nested nav">
                        <a class="nav-link" href="layout-static.html">การลงทะเบียนเจ้าหน้าที่</a>
                        <a class="nav-link" href="layout-sidenav-light.html">สถิติการเข้าใช้งานระบบ</a>
                        <a class="nav-link" href="layout-sidenav-light.html">.....................</a>
                    </nav>
                </div> -->
                <?php

                $userRoles = session()->get('roles') ?? [];
                if (in_array(1, $userRoles) || in_array(2, $userRoles) || in_array(3, $userRoles)):
                ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= site_url('import/patients') ?>">
                            <i class="fas fa-file-import"></i>
                            <span>นำเข้าข้อมูล Excel</span>
                        </a>
                    </li>
                <?php endif;
                if (in_array(1, $userRoles)):
                ?>
                    <li class="nav-item">
                        <a class="nav-link" href="<?= site_url('admin/settings/province') ?>">
                            <i class="fas fa-file-import"></i>
                            <span>ตั้งค่าจังหวัด</span>
                        </a>
                    </li>
                <?php endif ?>
            </div>
        </div>
        <div class="sb-sidenav-footer">
            <div class="small">Logged in as:</div>
            <?= esc(session()->get('fullname')) ?>
        </div>
    </nav>
</div>