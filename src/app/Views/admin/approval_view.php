<?= $this->extend('layout/default') ?>
<?= $this->section('content') ?>
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>อนุมัติผู้ใช้งาน</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/2.0.8/css/dataTables.bootstrap5.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css" rel="stylesheet">
</head>
<body>

<div class=" mt-4" style="padding: 2rem;">
    <h3 class="mb-3">รายชื่อผู้ใช้งานที่รอการอนุมัติ</h3>
    <div class="card shadow-sm">
        <div class="card-body">
            <table id="approvalTable" class="table table-striped table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ชื่อ-สกุล</th>
                        <th>ตำแหน่ง</th>
                        <th>วันที่สมัคร</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody></tbody>
            </table>
        </div>
    </div>
</div>

<!-- Approval Modal -->
<div class="modal fade" id="approvalModal" tabindex="-1" aria-labelledby="approvalModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="approvalModalLabel">อนุมัติและกำหนดสิทธิ์</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form id="approvalForm">
                    <input type="hidden" name="user_id" id="approve_user_id">
                    <div class="mb-3">
                        <p><strong>ผู้ใช้:</strong> <span id="approve_fullname"></span></p>
                        <p><strong>ตำแหน่ง:</strong> <span id="approve_position"></span></p>
                    </div>
                    <hr>
                    <div class="mb-3">
                        <label class="form-label">กำหนดสิทธิ์ (เลือกได้มากกว่า 1):</label>
                        <?php foreach($roles as $role): ?>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="roles[]" value="<?= $role['id'] ?>" id="role_<?= $role['id'] ?>">
                                <label class="form-check-label" for="role_<?= $role['id'] ?>">
                                    <?= esc($role['role_name']) ?> (<?= esc($role['role_description']) ?>)
                                </label>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">ยกเลิก</button>
                <button type="submit" class="btn btn-success" form="approvalForm">ยืนยันการอนุมัติ</button>
            </div>
        </div>
    </div>
</div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    var table = $('#approvalTable').DataTable({
        ajax: "<?= site_url('admin/users/pending') ?>",
        columns: [
            { data: 'id' },
            { data: 'fullname' },
            { data: 'position' },
            { data: 'created_at' },
            {
                data: null, orderable: false,
                render: (data, type, row) => `
                    <button class="btn btn-success btn-sm approveBtn" data-id="${row.id}" data-fullname="${row.fullname}" data-position="${row.position}">อนุมัติ</button>
                    <button class="btn btn-danger btn-sm rejectBtn" data-id="${row.id}">ปฏิเสธ</button>
                `
            }
        ],
        language: { url: 'https://cdn.datatables.net/plug-ins/2.0.8/i18n/th.json' }
    });

    const approvalModal = new bootstrap.Modal(document.getElementById('approvalModal'));

    // --- Event Handlers ---
    $('#approvalTable tbody').on('click', '.approveBtn', function() {
        $('#approvalForm')[0].reset();
        $('#approve_user_id').val($(this).data('id'));
        $('#approve_fullname').text($(this).data('fullname'));
        $('#approve_position').text($(this).data('position'));
        approvalModal.show();
    });

    $('#approvalForm').submit(function(e) {
        e.preventDefault();
        $.post("<?= site_url('admin/users/approve') ?>", $(this).serialize(), (response) => {
            approvalModal.hide();
            Swal.fire({ icon: response.status, title: response.message, showConfirmButton: false, timer: 1500 });
            table.ajax.reload();
        }, 'json');
    });

    $('#approvalTable tbody').on('click', '.rejectBtn', function() {
        let id = $(this).data('id');
        Swal.fire({
            title: 'ยืนยันการปฏิเสธ?',
            text: "บัญชีนี้จะไม่สามารถใช้งานได้", icon: 'warning',
            showCancelButton: true, confirmButtonText: 'ใช่, ปฏิเสธ', cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                $.post(`<?= site_url('admin/users/reject/') ?>${id}`, (response) => {
                    Swal.fire('เรียบร้อย!', response.message, 'success');
                    table.ajax.reload();
                }, 'json');
            }
        });
    });
});
</script>
</body>
</html>
<?= $this->endSection() ?>