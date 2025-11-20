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
    <h3 class="mb-3">จัดการผู้ใช้งานxxxx</h3>
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

<!-- Modal จัดการข้อมูลผู้ใช้ -->
<div class="modal fade" id="userManageModal" tabindex="-1" aria-labelledby="userManageModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="userManageModalLabel">จัดการข้อมูลผู้ใช้</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="userManageForm">
                <div class="modal-body">
                    <?= csrf_field() ?>
                    <input type="hidden" name="user_id" id="user_id">
                    
                    <!-- ข้อมูลผู้ใช้ -->
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

                    <!-- หน่วยบริการสังกัด -->
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="edit_hospcode" class="form-label">หน่วยบริการ</label>
                            <select class="form-select" id="edit_hospcode" name="hospcode" required>
                                <option value="">-- เลือกหน่วยบริการ --</option>
                            </select>
                        </div>
                    </div>

                    <hr>

                    <!-- สถานะและสิทธิ์การใช้งาน -->
                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label for="user_status" class="form-label">สถานะ</label>
                            <select class="form-select" id="user_status" name="status" required>
                                <option value="1">อนุมัติ</option>
                                <option value="0">รออนุมัติ</option>
                                <option value="2">ระงับการใช้งาน</option>
                                <option value="3">ปฏิเสธ</option>
                            </select>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12 mb-3">
                            <label class="form-label">สิทธิ์การใช้งาน (Roles)</label>
                            <div id="roles_container">
                                <?php foreach ($all_roles as $role): ?>
                                    <div class="form-check">
                                        <input class="form-check-input" type="checkbox" name="roles[]" 
                                               value="<?= $role['id'] ?>" id="role_<?= $role['id'] ?>">
                                        <label class="form-check-label" for="role_<?= $role['id'] ?>">
                                            <?= esc($role['role_name']) ?>
                                        </label>
                                    </div>
                                <?php endforeach; ?>
                            </div>
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
                    value = item.hospcode;
                    text = item.hospname;
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
                    if (row.hospname) {  // เพิ่มการแสดง hospname
                        affiliation.push(row.hospname);
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
                        <button class="btn btn-primary btn-sm edit-btn" data-id="${row.id}" title="จัดการข้อมูลผู้ใช้">
                            <i class="fas fa-edit"></i> จัดการ
                        </button>
                    `;
                }
            }
        ],
    });

    // === Event Handlers for Buttons ===
    
    // Handle both Approve and Edit button clicks
    $('#usersTable').on('click', '.approve-btn, .edit-btn', function() {
        const userId = $(this).data('id');
        $.get(`<?= site_url('admin/users/get-details/') ?>${userId}`, function(data) {
            // Fill in the form fields
            $('#user_id').val(data.id);
            $('#edit_fullname').val(data.fullname);
            $('#edit_position').val(data.position);
            $('#user_status').val(data.status);
            
            // Reset and set roles
            $('#userManageForm input[name="roles[]"]').prop('checked', false);
            data.roles.forEach(roleId => {
                $(`#role_${roleId}`).prop('checked', true);
            });
            
            // Load Hospitals Dropdown
            loadDropdown(
                'edit_hospcode',
                '<?= site_url('ajax/get-hospitals') ?>',
                {},
                '-- เลือกหน่วยบริการ --',
                data.hospcode
            );

            new bootstrap.Modal(document.getElementById('userManageModal')).show();
        });
    });

    // === Form Submission ===
    $('#userManageForm').on('submit', function(e) {
        e.preventDefault();
        const formData = $(this).serialize();
        $.post("<?= site_url('admin/users/update') ?>", formData, function(response) {
            Swal.fire('สำเร็จ!', 'บันทึกการเปลี่ยนแปลงเรียบร้อย', 'success');
            $('#userManageModal').modal('hide');
            $('#usersTable').DataTable().ajax.reload();
        }).fail(function() {
            Swal.fire('ผิดพลาด!', 'ไม่สามารถบันทึกข้อมูลได้', 'error');
        });
    });

    // ... (Your Cascading Dropdown JavaScript logic here) ...

});
</script>
<?= $this->endSection() ?>