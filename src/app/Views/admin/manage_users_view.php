<?php
// --------------------------------------------------------------------
// (2/4) View: manage_users_view.php (Updated)
// --------------------------------------------------------------------
// อัปเดตไฟล์ที่: app/Views/admin/manage_users_view.php
// แก้ไขคอลัมน์ Actions และเพิ่ม Modal ใหม่
?>
<?= $this->extend('layout/default') ?>
<?= $this->section('content') ?>

<div class="container-fluid mt-4">
    <h3 class="mb-3">จัดการผู้ใช้งาน</h3>
    <div class="card shadow-sm">
        <div class="card-header">
            ผู้ใช้งานรอการอนุมัติและผู้ใช้งานทั้งหมด
        </div>
        <div class="card-body">
            <table id="usersTable" class="table table-hover" style="width:100%">
                <thead>
                    <tr>
                        <th>UserID</th>
                        <th>ชื่อ-สกุล</th>
                        <th>ตำแหน่ง</th>
                        <th>UserRole</th>
                        <th>สังกัด</th> 
                        <th>สถานะ</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
            </table>
        </div>
    </div>
</div>

<!-- [Modal ใหม่] Approve User Modal -->
<div class="modal fade" id="approveUserModal" tabindex="-1" aria-labelledby="approveUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approveUserModalLabel">อนุมัติและกำหนดสิทธิ์</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="approveUserForm">
                <div class="modal-body">
                    <?= csrf_field() ?>
                    <input type="hidden" name="user_id" id="approve_user_id">
                    <p>ผู้ใช้: <strong id="approve_user_fullname"></strong></p>
                    
                    <div class="mb-3">
                        <label for="approve_status" class="form-label">สถานะ</label>
                        <select class="form-select" id="approve_status" name="status" required>
                            <option value="1">อนุมัติ</option>
                            <option value="0">รออนุมัติ</option>
                            <option value="2">ระงับการใช้งาน</option>
                            <option value="3">ปฏิเสธ</option>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">สิทธิ์การใช้งาน (Roles)</label>
                        <div id="approve_roles_container">
                            <?php foreach ($all_roles as $role): ?>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="roles[]" value="<?= $role['id'] ?>" id="approve_role_<?= $role['id'] ?>">
                                    <label class="form-check-label" for="approve_role_<?= $role['id'] ?>"><?= esc($role['role_name']) ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                    <button type="submit" class="btn btn-success">บันทึก</button>
                </div>
            </form>
        </div>
    </div>
</div>


<!-- [Modal เดิม ปรับปรุง] Edit User Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editUserModalLabel">แก้ไขข้อมูลผู้ใช้งาน</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="editUserForm">
                <div class="modal-body">
                    <?= csrf_field() ?>
                    <input type="hidden" name="user_id" id="edit_user_id">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_fullname" class="form-label">ชื่อ-สกุล</label>
                            <input type="text" class="form-control" id="edit_fullname" name="fullname" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_position" class="form-label">ตำแหน่ง</label>
                            <input type="text" class="form-control" id="edit_position" name="position" required>
                        </div>
                    </div>

                    <hr>
                    <p class="text-muted">แก้ไขพื้นที่สังกัด</p>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="edit_changwatcode" class="form-label">จังหวัด</label>
                            <select class="form-select" id="edit_changwatcode" name="changwatcode" required>
                                <!-- Options will be loaded by JS -->
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_ampurcodefull" class="form-label">อำเภอ</label>
                            <select class="form-select" id="edit_ampurcodefull" name="ampurcodefull" required disabled>
                                <option value="">-- เลือกจังหวัดก่อน --</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                         <div class="col-md-6 mb-3">
                            <label for="edit_hospcode" class="form-label">รพ.สต.</label>
                            <select class="form-select" id="edit_hospcode" name="hospcode" disabled>
                                <option value="">-- เลือกอำเภอก่อน --</option>
                            </select>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="edit_villagecodefull" class="form-label">หมู่บ้าน</label>
                            <select class="form-select" id="edit_villagecodefull" name="villagecodefull" disabled>
                                 <option value="">-- เลือก รพ.สต. ก่อน --</option>
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button>
                    <button type="submit" class="btn btn-primary">บันทึกการเปลี่ยนแปลง</button>
                </div>
            </form>
        </div>
    </div>
</div>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.js"></script>
    <script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// แก้ไขฟังก์ชัน loadDropdown โดยเพิ่มการ abort request เก่า
let currentRequests = {};

function loadDropdown(elementId, url, data, placeholder, selectedValue = null, callback = null) {
    const select = $(`#${elementId}`);
    select.prop('disabled', true).html(`<option value="">-- กำลังโหลด --</option>`);

    // Abort previous request for this dropdown if exists
    if (currentRequests[elementId]) {
        currentRequests[elementId].abort();
    }

    // ยกเลิก required สำหรับทุก dropdown
    select.prop('required', false);

    currentRequests[elementId] = $.post(url, data, function(response) {
        select.prop('disabled', false).html(`<option value="">${placeholder}</option>`);
        
        response.forEach(item => {
            let value, text;
            switch(elementId) {
                case 'edit_changwatcode':
                    value = item.changwatcode;
                    text = item.changwatname;
                    break;
                case 'edit_ampurcodefull':
                    value = item.ampurcodefull;
                    text = item.ampurname;
                    break;
                case 'edit_hospcode':
                    value = item.hoscode;
                    text = item.hosname;
                    break;
                case 'edit_villagecodefull':
                    value = item.villagecodefull;
                    text = item.villagename;
                    break;
            }
            select.append(new Option(text, value, false, value === selectedValue));
        });

        if (selectedValue) {
            select.val(selectedValue);
        }
        if (callback) {
            callback();
        }
        
        // Clear the request object after completion
        delete currentRequests[elementId];
    }, 'json');
}

$(document).ready(function() {
    
    // === DataTable Initialization ===
    $('#usersTable').DataTable({
        ajax: "<?= site_url('admin/users/fetch') ?>",
        columns: [
            { data: 'id' },
            { data: 'fullname' },
            { data: 'position' },
            { data: 'roles_list' },
            { 
                data: null,
                render: function(data, type, row) {
                    let affiliation = [];
                    if (row.changwatname) {
                        affiliation.push(row.changwatname);
                    }
                    if (row.ampurname) {
                        affiliation.push(row.ampurname);
                    }
                    if (row.hosname) {  // เพิ่มการแสดง hosname
                        affiliation.push(row.hosname);
                    }
                    return affiliation.join(' / ') || '-';
                }
            },
            { 
                data: 'status',
                render: function(data, type, row) {
                    switch(data) {
                        case '1': return '<span class="badge bg-success">อนุมัติแล้ว</span>';
                        case '0': return '<span class="badge bg-warning">รออนุมัติ</span>';
                        case '2': return '<span class="badge bg-danger">ระงับการใช้งาน</span>';
                        default: return '<span class="badge bg-secondary">อื่นๆ</span>';
                    }
                }
            },
            {
                data: null,
                orderable: false,
                render: function(data, type, row) {
                    return `
                        <button class="btn btn-success btn-sm approve-btn" data-id="${row.id}" title="อนุมัติ/กำหนดสิทธิ์">
                            <i class="fas fa-check-circle"></i>
                        </button>
                        <button class="btn btn-warning btn-sm edit-btn" data-id="${row.id}" title="แก้ไขข้อมูล">
                            <i class="fas fa-edit"></i>
                        </button>
                    `;
                }
            }
        ],
    });

    // === Event Handlers for Buttons ===
    
    // 1. Approve Button Click
    $('#usersTable').on('click', '.approve-btn', function() {
        const userId = $(this).data('id');
        $.get(`<?= site_url('admin/users/get-details/') ?>${userId}`, function(data) {
            $('#approve_user_id').val(data.id);
            $('#approve_user_fullname').text(data.fullname);
            $('#approve_status').val(data.status);
            
            // Reset and set roles
            $('#approveUserForm input[name="roles[]"]').prop('checked', false);
            data.roles.forEach(roleId => {
                $(`#approve_role_${roleId}`).prop('checked', true);
            });

            new bootstrap.Modal(document.getElementById('approveUserModal')).show();
        });
    });

    // 2. Edit Button Click
    $('#usersTable').on('click', '.edit-btn', function() {
        const userId = $(this).data('id');
        $.get(`<?= site_url('admin/users/get-details/') ?>${userId}`, function(data) {
            $('#edit_user_id').val(data.id);
            $('#edit_fullname').val(data.fullname);
            $('#edit_position').val(data.position);
            
            // Load Province Dropdown
            loadDropdown(
                'edit_changwatcode',
                '<?= site_url('ajax/get-provinces') ?>',
                {},
                '-- เลือกจังหวัด --',
                data.changwatcode,
                function() {
                    if (data.changwatcode) {
                        // Load Amphur Dropdown
                        loadDropdown(
                            'edit_ampurcodefull',
                            '<?= site_url('ajax/get-amphures') ?>',
                            { province_code: data.changwatcode },
                            '-- เลือกอำเภอ --',
                            data.ampurcodefull,
                            function() {
                                if (data.ampurcodefull) {
                                    // Load Hospital Dropdown
                                    loadDropdown(
                                        'edit_hospcode',
                                        '<?= site_url('ajax/get-hospitals') ?>',
                                        { amphurcode: data.ampurcodefull },
                                        '-- เลือก รพ.สต. --',
                                        data.hospcode,
                                        function() {
                                            if (data.hospcode) {
                                                // Load Village Dropdown
                                                loadDropdown(
                                                    'edit_villagecodefull',
                                                    '<?= site_url('ajax/get-villages') ?>',
                                                    { hospcode: data.hospcode },
                                                    '-- เลือกหมู่บ้าน --',
                                                    data.villagecodefull
                                                );
                                            }
                                        }
                                    );
                                }
                            }
                        );
                    }
                }
            );

            new bootstrap.Modal(document.getElementById('editUserModal')).show();
        });
    });

    // Cascade dropdowns on user interaction
    $('#edit_changwatcode').on('change', function() {
        const provinceCode = $(this).val();
        // Reset dependent dropdowns
        $('#edit_ampurcodefull').html('<option value="">-- เลือกอำเภอ --</option>').prop('disabled', !provinceCode);
        $('#edit_hospcode').html('<option value="">-- เลือก รพ.สต. --</option>').prop('disabled', true);
        $('#edit_villagecodefull').html('<option value="">-- เลือกหมู่บ้าน --</option>').prop('disabled', true);
        
        if (provinceCode) {
            loadDropdown(
                'edit_ampurcodefull',
                '<?= site_url('ajax/get-amphures') ?>',
                { province_code: provinceCode },
                '-- เลือกอำเภอ --'
            );
        }
    });

    $('#edit_ampurcodefull').on('change', function() {
        const amphurCode = $(this).val();
        // Reset dependent dropdowns
        $('#edit_hospcode').html('<option value="">-- เลือก รพ.สต. --</option>').prop('disabled', !amphurCode);
        $('#edit_villagecodefull').html('<option value="">-- เลือกหมู่บ้าน --</option>').prop('disabled', true);
        
        if (amphurCode) {
            loadDropdown(
                'edit_hospcode',
                '<?= site_url('ajax/get-hospitals') ?>',
                { amphurcode: amphurCode },
                '-- เลือก รพ.สต. --'
            );
        }
    });

    $('#edit_hospcode').on('change', function() {
        const hospCode = $(this).val();
        // Reset dependent dropdown
        $('#edit_villagecodefull').html('<option value="">-- เลือกหมู่บ้าน --</option>').prop('disabled', !hospCode);
        
        if (hospCode) {
            loadDropdown(
                'edit_villagecodefull',
                '<?= site_url('ajax/get-villages') ?>',
                { hospcode: hospCode },
                '-- เลือกหมู่บ้าน --'
            );
        }
    });

    // === Form Submissions ===

    // 1. Approve Form Submit
    $('#approveUserForm').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();
        $.post("<?= site_url('admin/users/approve') ?>", formData, function(response) {
            Swal.fire('สำเร็จ!', 'อัปเดตสถานะและสิทธิ์เรียบร้อย', 'success');
            $('#approveUserModal').modal('hide');
            $('#usersTable').DataTable().ajax.reload();
        }).fail(function() {
            Swal.fire('ผิดพลาด!', 'ไม่สามารถบันทึกข้อมูลได้', 'error');
        });
    });
    
    // 2. Edit Form Submit
    $('#editUserForm').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();
        $.post("<?= site_url('admin/users/update') ?>", formData, function(response) {
            Swal.fire('สำเร็จ!', 'แก้ไขข้อมูลผู้ใช้เรียบร้อย', 'success');
            $('#editUserModal').modal('hide');
            $('#usersTable').DataTable().ajax.reload();
        }).fail(function() {
            Swal.fire('ผิดพลาด!', 'ไม่สามารถบันทึกข้อมูลได้', 'error');
        });
    });

    // ... (Your Cascading Dropdown JavaScript logic here) ...

});
</script>
<?= $this->endSection() ?>