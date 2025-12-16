<div id="layoutSidenav_nav">
    <nav class="sb-sidenav accordion sb-sidenav-dark" id="sidenavAccordion">
        <div class="sb-sidenav-menu">
            <div class="nav">
                <div class="sb-sidenav-menu-heading">หน้าหลัก</div>
                <a class="nav-link" href="<?= site_url('dashboard') ?>">
                    <div class="sb-nav-link-icon"><i class="fas fa-list"></i> Dashboard</div>
                </a>
                <a class="nav-link" href="<?= site_url('commands') ?>">
                    <div class="sb-nav-link-icon"><i class="fas fa-list"></i> คำสั่งทั้งหมด</div>
                </a>
            </div>


        </div>
        <div class="sb-sidenav-footer">
            <div class="small">Logged in as:</div>
            <?= esc(session()->get('fullname')) ?>
        </div>
    </nav>
</div>