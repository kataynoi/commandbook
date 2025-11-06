<?php
$userRoles = session()->get('roles') ?? [];
// ถ้าต้องการให้สิทธิ์ 'Edit' (ID 5) เพิ่มได้ด้วย ให้ใช้ if(in_array(4, $userRoles) || in_array(5, $userRoles))
?>
<nav class="sb-topnav navbar navbar-expand navbar-dark bg-dark">
    <!-- Navbar Brand-->
    <a class="navbar-brand ps-3" href="<?= site_url('/dashboard'); ?>">SMIV Mahasarakham</a>
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
            <a class="nav-link" data-bs-toggle="dropdown" href="#" id="notification-bell">
                <i class="far fa-bell"></i>
                <span class="badge badge-warning navbar-badge" id="notification-count" style="display: none;"></span>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-end" id="notification-dropdown">
                <span class="dropdown-item dropdown-header"><span id="notification-header-count">0</span> Notifications</span>
                <div class="dropdown-divider"></div>
                <div id="notification-list">
                    <!-- รายการแจ้งเตือนจะถูกเพิ่มที่นี่โดย JavaScript -->
                </div>
                <div class="dropdown-divider"></div>
                <a href="<?= site_url('notifications') ?>" class="dropdown-item dropdown-footer">See All Notifications</a>
            </div>
        </li>
    </ul>
    <ul class="navbar-nav ms-auto ms-md-0 me-3 me-lg-4">
        <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle" id="navbarDropdown" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false"><i class="fas fa-user fa-fw"></i>
                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?= esc(session()->get('fullname')) ?>[<?= esc(session()->get('position')) ?>]
                </span>
            </a>

            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="navbarDropdown">
                <li><a class="dropdown-item" href="#!">Settings</a></li>
                <li><a class="dropdown-item" href="#!">Activity Log</a></li>
                <li>
                    <?php
                    if (in_array(1, $userRoles) || in_array(2, $userRoles) || in_array(3, $userRoles)):
                    ?>
                        <a class="dropdown-item" href="<?= base_url('/admin/manage-users') ?>">
                            <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                            ผู้ใช้งานทั้งหมด
                        </a>
                    <?php endif; ?>
                </li>
                <li>
                    <?php
                    if (in_array(3, $userRoles)):
                    ?>
                        <a class="dropdown-item" href="<?= base_url('/admin/user-approval') ?>">
                            <i class="fas fa-list fa-sm fa-fw mr-2 text-gray-400"></i>
                            อนุมัติผู้ใช้งาน
                        </a>
                    <?php endif; ?>
                </li>
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
<div class="dropdown me-3">
    <button class="btn btn-light position-relative" type="button" id="notificationDropdown" data-bs-toggle="dropdown" aria-expanded="false" title="แจ้งเตือน">
        <i class="fas fa-bell"></i>แจ้งเตือน
        <?php if (!empty($notifications)): ?>
            <span class="position-absolute top-0 start-100 translate-middle badge rounded-pill bg-danger">
                <?= count($notifications) ?>
            </span>
        <?php endif; ?>
    </button>
    <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="notificationDropdown" style="min-width: 350px;">
        <li class="dropdown-header">แจ้งเตือน OAS</li>
        <?php if (!empty($notifications)): ?>
            <?php foreach ($notifications as $note): ?>
                <li>
                    <a class="dropdown-item small" href="<?= site_url('followup/visit/' . $note['patient_id']) ?>">
                        <?= esc($note['message']) ?>
                        <br>
                        <span class="text-muted small"><?= esc($note['created_at']) ?></span>
                    </a>
                </li>
            <?php endforeach; ?>
        <?php else: ?>
            <li><span class="dropdown-item text-muted">ไม่มีแจ้งเตือนใหม่</span></li>
        <?php endif; ?>
    </ul>
</div>
<script>
$(document).ready(function() {
    function fetchNotifications() {
        $.get("<?= site_url('notifications/fetch-unread') ?>", function(data) {
            const count = data.count;
            const notifList = $('#notification-list');
            notifList.empty();

            if (count > 0) {
                $('#notification-count').text(count).show();
                $('#notification-header-count').text(count);
                
                data.notifications.forEach(notif => {
                    const notifItem = `
                        <a href="<?= site_url('notifications/mark-as-read/') ?>${notif.id}" class="dropdown-item">
                            <i class="fas fa-exclamation-triangle text-warning mr-2"></i> ${notif.message}
                            <span class="float-right text-muted text-sm">${new Date(notif.created_at).toLocaleString('th-TH')}</span>
                        </a>
                        <div class="dropdown-divider"></div>`;
                    notifList.append(notifItem);
                });

            } else {
                $('#notification-count').hide();
                $('#notification-header-count').text('0');
                notifList.html('<span class="dropdown-item text-muted">ไม่มีการแจ้งเตือนใหม่</span>');
            }
        }, 'json');
    }
    // ดึงข้อมูลครั้งแรกเมื่อโหลดหน้า
    fetchNotifications(); 
    // (Optional) ตั้งให้ดึงข้อมูลทุกๆ 1 นาที
    // setInterval(fetchNotifications, 60000); 
});
</script>