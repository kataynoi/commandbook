<?= $this->extend('layout/default') ?>
<?= $this->section('content') ?>

<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h3>รายการหนังสือคำสั่ง</h3>
        <a href="<?= site_url('commands/create') ?>" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> เพิ่มเอกสารใหม่
        </a>
    </div>

    <?php if (session()->getFlashdata('message')): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?= session()->getFlashdata('message') ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <table id="commandsTable" class="table table-striped table-hover" style="width:100%">
        <thead>
            <tr>
                <th>ลำดับ</th>
                <th>เลขที่คำสั่ง</th>
                <th>ชื่อเรื่อง</th>
                <th>วันที่ออก</th>
                <th>ผู้อัปโหลด</th>
                <th>จัดการ</th>
            </tr>
        </thead>
        <tbody></tbody>
    </table>
</div>

<!-- modals -->
<div id="docDetailModal" class="modal fade" tabindex="-1"></div>
<div id="qrModal" class="modal fade" tabindex="-1"></div>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.js"></script>
<script src="https://cdn.datatables.net/2.0.8/js/dataTables.bootstrap5.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
document.addEventListener('DOMContentLoaded', function () {
    // --- DataTable Initialization ---
    const table = $('#commandsTable').DataTable({
        ajax: '<?= site_url('commands/fetch') ?>',
        responsive: true,
        pageLength: 10,
        columns: [
            { data: null, render: (d, t, r, meta) => meta.row + 1, width: '5%' },
            { data: 'doc_number' },
            { data: 'doc_title', render: (d, t, row) => `<a href="#" class="doc-title" data-id="${row.id}" style="text-decoration: none;">${d || '-'}</a>` },
            { data: 'doc_date' },
            { data: 'uploader_name' },
            {
                data: 'qr_token',
                orderable: false,
                render: function(data, type, row) {
                    const token = row.qr_token || '';
                    let actions = `<button class="btn btn-sm btn-info qr-btn" data-token="${token}"><i class="bi bi-qr-code"></i> QR</button>`;
                    
                    // Normalize roles from PHP session
                    const rawRoles = <?= json_encode(session()->get('roles') ? session()->get('roles') : []) ?>;
                    // Ensure roles is always an array of numbers
                    const roles = (Array.isArray(rawRoles) ? rawRoles : [rawRoles]).map(Number);

                    // Add Edit and Delete buttons for roles 1 or 2
                    if (roles.includes(1) || roles.includes(2)) {
                        actions += ` <a class="btn btn-sm btn-warning" href="<?= site_url('commands/create') ?>?edit=${row.id}" title="แก้ไข"><i class="bi bi-pencil"></i>แก้ไข</a>`;
                        actions += ` <button class="btn btn-sm btn-danger delete-btn" data-id="${row.id}" title="ลบ"><i class="bi bi-trash"></i>ลบ</button>`;
                    }
                    return actions;
                }
            }
        ]
    });

    // click title -> load detail (AJAX)
    $('#commandsTable').on('click', '.doc-title', function(e){
        e.preventDefault();
        const id = $(this).data('id');
        
        // เพิ่ม headers ใน fetch()
        fetch('<?= site_url('commands/get') ?>/' + id, {
            method: 'GET',
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(r => {
            if (!r.ok) {
                throw new Error('Network response was not ok');
            }
            return r.json();
        })
        .then(json => {
            if (json.error) { 
                Swal.fire('ข้อผิดพลาด', json.error, 'error'); 
                return; 
            }

            // สร้าง HTML สำหรับ "คำขยายความ"
            let descriptionHtml = json.description ? `<hr><p><strong>คำขยายความ:</strong></p><div>${json.description}</div>` : '';

            // สร้าง HTML สำหรับ "รายชื่อหน่วยงาน"
            let accessListHtml = '';
            if (json.access_list && json.access_list.length > 0) {
                const listItems = json.access_list.map(item => `<li>${item.hospname} (${item.hospcode})</li>`).join('');
                accessListHtml = `<hr><p><strong>หน่วยงานที่รับหนังสือ:</strong></p><ul class="list-unstyled" style="max-height: 150px; overflow-y: auto; border: 1px solid #eee; padding: 10px; border-radius: 5px;">${listItems}</ul>`;
            }

            // รวม HTML ทั้งหมดเพื่อแสดงใน Modal
            let html = `<div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">${json.doc_title || '-'}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><p><strong>เลขที่:</strong> ${json.doc_number||'-'}</p><p><strong>วันที่:</strong> ${json.doc_date||'-'}</p><p><strong>ผู้อัปโหลด:</strong> ${json.uploader_name||'-'}</p>${descriptionHtml}${accessListHtml}</div><div class="modal-footer"><a class="btn btn-success" href="<?= site_url('access') ?>/${encodeURIComponent(json.qr_token||'')}" target="_blank">ดาวน์โหลด</a><button class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button></div></div></div>`;
            
            $('#docDetailModal').html(html);
            new bootstrap.Modal(document.getElementById('docDetailModal')).show();
        }).catch(()=> Swal.fire('ข้อผิดพลาด', 'ไม่สามารถโหลดข้อมูลได้', 'error'));
    });

    // --- QR Button Click Handler ---
    $('#commandsTable').on('click', '.qr-btn', function() {
        const token = $(this).data('token');
        if (!token) {
            Swal.fire('ข้อผิดพลาด', 'ไม่พบ QR Token สำหรับเอกสารนี้', 'error');
            return;
        }
        const qrSrc = `<?= site_url('commands/qr/') ?>${token}`;
        const modalHtml = `
          <div class="modal-dialog modal-sm">
            <div class="modal-content">
              <div class="modal-header"><h5 class="modal-title">QR Code</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
              <div class="modal-body text-center"><img id="qrImg" src="${qrSrc}" class="img-fluid" alt="QR Code" style="min-height: 250px;"></div>
            </div>
          </div>`;
        $('#qrModal').html(modalHtml).modal('show');
    });

    // --- Delete Button Click Handler ---
    $('#commandsTable').on('click', '.delete-btn', function(){
        const id = $(this).data('id');
        Swal.fire({
            title: 'ต้องการลบเอกสารนี้?',
            text: "การกระทำนี้ไม่สามารถย้อนกลับได้!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'ใช่, ลบเลย!',
            cancelButtonText: 'ยกเลิก'
        }).then((result) => {
            if (result.isConfirmed) {
                fetch('<?= site_url('commands/delete') ?>/' + id, { 
                    method: 'POST', 
                    headers: {'X-Requested-With': 'XMLHttpRequest'} 
                })
                .then(r => r.json())
                .then(res => {
                    if (res.success) {
                        Swal.fire('ลบแล้ว!', 'เอกสารถูกลบเรียบร้อย', 'success');
                        table.ajax.reload(null, false); // Reload table without resetting page
                    } else {
                        Swal.fire('ข้อผิดพลาด', res.error || 'ไม่สามารถลบเอกสารได้', 'error');
                    }
                }).catch(() => Swal.fire('ข้อผิดพลาด', 'เกิดปัญหาในการเชื่อมต่อ', 'error'));
            }
        });
    });
});
</script>
<?= $this->endSection() ?>