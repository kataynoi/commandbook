<?= $this->extend('layout/default') ?>
<?= $this->section('content') ?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Activity Logs</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item"><a href="<?= site_url('/dashboard') ?>">Dashboard</a></li>
        <li class="breadcrumb-item active">Activity Logs</li>
    </ol>

    <!-- Alert Messages -->
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('error') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('success') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Card สำหรับ Activity Logs -->
    <div class="card mb-4">
        <div class="card-header">
            <i class="fas fa-history me-1"></i>
            ประวัติการใช้งานระบบ (Activity Logs)
            
            <button class="btn btn-sm btn-danger float-end" id="btnCleanup">
                <i class="fas fa-trash"></i> ล้าง Logs เก่า
            </button>
        </div>
        <div class="card-body">
            <table id="activityLogsTable" class="table table-striped table-bordered" style="width:100%">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>ผู้ใช้งาน</th>
                        <th>การกระทำ</th>
                        <th>รายละเอียด</th>
                        <th>IP Address</th>
                        <th>เวลา</th>
                        <th>จัดการ</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- DataTables จะโหลดข้อมูลทีหลัง -->
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- Scripts for Activity Logs -->
<script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).ready(function() {
    // Get CSRF token
    var csrfName = '<?= csrf_token() ?>';
    var csrfHash = '<?= csrf_hash() ?>';
    
    // Initialize DataTable
    var table = $('#activityLogsTable').DataTable({
        processing: true,
        serverSide: true,
        ajax: {
            url: '<?= base_url('activity-logs/fetch') ?>',
            type: 'POST',
            data: function(d) {
                d[csrfName] = csrfHash;
            },
            dataSrc: function(json) {
                // Update CSRF token
                if (json.token) {
                    csrfHash = json.token;
                }
                return json.data;
            }
        },
        columns: [
            { data: 'id' },
            { 
                data: null,
                render: function(data, type, row) {
                    var hospcode = row.hospcode ? ' (' + row.hospcode + ')' : '';
                    return row.fullname + hospcode;
                }
            },
            { 
                data: 'action',
                render: function(data) {
                    // แสดง badge สีต่างกันตาม action
                    var badgeClass = 'bg-secondary';
                    if (data === 'download_document') badgeClass = 'bg-primary';
                    else if (data === 'create') badgeClass = 'bg-success';
                    else if (data === 'update') badgeClass = 'bg-warning';
                    else if (data === 'delete') badgeClass = 'bg-danger';
                    
                    return '<span class="badge ' + badgeClass + '">' + data + '</span>';
                }
            },
            { 
                data: 'description',
                render: function(data) {
                    // จำกัดความยาวไม่เกิน 100 ตัวอักษร
                    if (data && data.length > 100) {
                        return data.substring(0, 100) + '...';
                    }
                    return data;
                }
            },
            { data: 'ip_address' },
            { 
                data: 'created_at',
                render: function(data) {
                    if (!data) return '-';
                    // แปลงวันที่เป็นรูปแบบไทย
                    var date = new Date(data);
                    var options = { 
                        year: 'numeric', 
                        month: 'short', 
                        day: 'numeric',
                        hour: '2-digit',
                        minute: '2-digit'
                    };
                    return date.toLocaleDateString('th-TH', options);
                }
            },
            {
                data: null,
                orderable: false,
                render: function(data, type, row) {
                    return '<button class="btn btn-sm btn-danger btn-delete" data-id="' + row.id + '">' +
                           '<i class="fas fa-trash"></i></button>';
                }
            }
        ],
        order: [[0, 'desc']], // เรียงตาม ID ล่าสุด
        pageLength: 25,
        language: {
            processing: "กำลังประมวลผล...",
            search: "ค้นหา:",
            lengthMenu: "แสดง _MENU_ รายการ",
            info: "แสดง _START_ ถึง _END_ จาก _TOTAL_ รายการ",
            infoEmpty: "แสดง 0 ถึง 0 จาก 0 รายการ",
            infoFiltered: "(กรองจาก _MAX_ รายการทั้งหมด)",
            loadingRecords: "กำลังโหลด...",
            zeroRecords: "ไม่พบข้อมูล",
            emptyTable: "ไม่มีข้อมูลในตาราง",
            paginate: {
                first: "หน้าแรก",
                previous: "ก่อนหน้า",
                next: "ถัดไป",
                last: "หน้าสุดท้าย"
            }
        }
    });

    // Delete Log
    $('#activityLogsTable').on('click', '.btn-delete', function() {
        var id = $(this).data('id');
        
        Swal.fire({
            title: 'ยืนยันการลบ?',
            text: "คุณต้องการลบ Log นี้หรือไม่?",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'ใช่, ลบเลย!',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= base_url('activity-logs/delete') ?>/' + id,
                    type: 'POST',
                    data: {
                        [csrfName]: csrfHash
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('สำเร็จ!', response.message, 'success');
                            table.ajax.reload();
                        } else {
                            Swal.fire('ผิดพลาด!', response.message, 'error');
                        }
                    },
                    error: function() {
                        Swal.fire('ผิดพลาด!', 'ไม่สามารถลบ Log ได้', 'error');
                    }
                });
            }
        });
    });

    // Cleanup Old Logs
    $('#btnCleanup').on('click', function() {
        Swal.fire({
            title: 'ล้าง Logs เก่า',
            input: 'number',
            inputLabel: 'ลบ Logs ที่เก่ากว่ากี่วัน?',
            inputValue: 90,
            inputAttributes: {
                min: 1,
                max: 365,
                step: 1
            },
            showCancelButton: true,
            confirmButtonText: 'ล้าง Logs',
            cancelButtonText: 'ยกเลิก',
            confirmButtonColor: '#d33',
            inputValidator: (value) => {
                if (!value || value < 1) {
                    return 'กรุณาระบุจำนวนวันที่ถูกต้อง!';
                }
            }
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: '<?= base_url('activity-logs/cleanup') ?>',
                    type: 'POST',
                    data: { 
                        days: result.value,
                        [csrfName]: csrfHash
                    },
                    dataType: 'json',
                    success: function(response) {
                        if (response.success) {
                            Swal.fire('สำเร็จ!', response.message, 'success');
                            table.ajax.reload();
                        } else {
                            Swal.fire('แจ้งเตือน', response.message, 'info');
                        }
                    },
                    error: function() {
                        Swal.fire('ผิดพลาด!', 'ไม่สามารถล้าง Logs ได้', 'error');
                    }
                });
            }
        });
    });
});
</script>

<?= $this->endSection() ?>
