<?= $this->extend('layout/default') ?>
<?= $this->section('content') ?>

<div class="container mt-4">
    <h3><i class="bi bi-files"></i> รายการหนังสือคำสั่งทั้งหมด</h3>

    <?php if (in_array(1, session()->get('roles') ?? []) || in_array(2, session()->get('roles') ?? [])): ?>
        <a href="<?= site_url('commands/new') ?>" class="btn btn-primary mb-3">
            <i class="bi bi-file-earmark-arrow-up"></i> อัปโหลดเอกสารใหม่
        </a>
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
    const table = $('#commandsTable').DataTable({
        ajax: '<?= site_url('commands/fetch') ?>',
        columns: [
            { data: null, render: (d, t, r, meta) => meta.row + 1 },
            { data: 'doc_number' },
            { data: 'doc_title', render: (d, t, row) => `<a href="#" class="doc-title" data-id="${row.id}">${$('<div>').text(d||'-').html()}</a>` },
            { data: 'doc_date' },
            { data: 'uploader_name' },
            { data: null, orderable:false, render: function(d, t, row){
                // token fallback: qr_token || token || file_token
                const token = row.qr_token || row.token || row.file_token || '';
                const accessUrl = token ? '<?= base_url('access') ?>/' + encodeURIComponent(token) : '';
                let actions = `<button class="btn btn-sm btn-info qr-btn me-1" data-url="${accessUrl}" data-token="${token}"><i class="bi bi-qr-code"></i> QR</button>`;
                const roles = <?= json_encode(array_values((array) session()->get('roles'))) ?>;
                if (roles.includes(1) || roles.includes(2)) {
                    actions += ` <a class="btn btn-sm btn-warning" href="<?= site_url('commands/new') ?>?edit=${row.id}"><i class="bi bi-pencil"></i> แก้ไข</a>`;
                    actions += ` <button class="btn btn-sm btn-danger delete-btn" data-id="${row.id}"><i class="bi bi-trash"></i> ลบ</button>`;
                }
                return actions;
            }}
        ],
        pageLength: 10,
        responsive: true
    });

    // click title -> load detail (AJAX)
    $('#commandsTable').on('click', '.doc-title', function(e){
        e.preventDefault();
        const id = $(this).data('id');
        fetch('<?= site_url('commands/get') ?>/' + id)
            .then(r => r.json()).then(json => {
                if (json.error) { alert(json.error); return; }
                // แสดง modal แบบง่าย
                let html = `<div class="modal-dialog modal-lg"><div class="modal-content"><div class="modal-header"><h5 class="modal-title">${json.doc_title || '-'}</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div><div class="modal-body"><p><strong>เลขที่:</strong> ${json.doc_number||'-'}</p><p><strong>วันที่:</strong> ${json.doc_date||'-'}</p><p><strong>ผู้อัปโหลด:</strong> ${json.uploader_name||'-'}</p><div>${json.description||''}</div></div><div class="modal-footer"><a class="btn btn-success" href="<?= site_url('access') ?>/${encodeURIComponent(json.qr_token||'')}" target="_blank">ดาวน์โหลด</a><button class="btn btn-secondary" data-bs-dismiss="modal">ปิด</button></div></div></div>`;
                $('#docDetailModal').html(html);
                new bootstrap.Modal(document.getElementById('docDetailModal')).show();
            }).catch(()=> alert('เกิดข้อผิดพลาด'));
    });

    // QR button — อ่าน data-url; ถ้าไม่มี token แจ้งผู้ใช้
    $('#commandsTable').on('click', '.qr-btn', function(){
        const token = $(this).data('token') || '';
        const accessUrl = $(this).data('url') || ''; // direct access link (for open)
        console.log('QR button clicked', { token, accessUrl });

        if (! token) {
            Swal.fire({
                icon: 'warning',
                title: 'ไม่พบ QR Token',
                text: 'ไม่พบข้อมูลสำหรับสร้าง QR Code ของเอกสารนี้'
            });
            return;
        }

        // เปลี่ยนให้ใช้ endpoint ภายในเซิร์ฟเวอร์ ที่จะ redirect ไปยัง Google Charts
        const qrSrc = '<?= site_url('commands/qr') ?>/' + encodeURIComponent(token);

        const modalHtml = `
          <div class="modal-dialog modal-sm">
            <div class="modal-content">
              <div class="modal-header">
                <h5 class="modal-title">QR Code</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body text-center">
                <img id="qrImg" src="${qrSrc}" class="img-fluid" alt="QR Code">
                <p class="mt-2"><a id="qrOpenLink" href="${accessUrl}" target="_blank">เปิดลิงก์</a></p>
              </div>
            </div>
          </div>
        `;

        $('#qrModal').html(modalHtml);
        const modalEl = document.getElementById('qrModal');
        new bootstrap.Modal(modalEl).show();

        const img = document.getElementById('qrImg');
        img.onerror = function() {
            console.warn('QR image failed to load:', qrSrc);
            this.style.display = 'none';
            const link = document.getElementById('qrOpenLink');
            if (link) {
                link.insertAdjacentHTML('beforebegin', '<p class="text-danger">ไม่สามารถแสดงภาพ QR ได้ — โปรดใช้ลิงก์ด้านล่าง</p>');
            }
        };
    });

    // delete
    $('#commandsTable').on('click', '.delete-btn', function(){
        if (!confirm('ต้องการลบเอกสารนี้หรือไม่?')) return;
        const id = $(this).data('id');
        fetch('<?= site_url('commands/delete') ?>/' + id, { method: 'POST', headers: {'X-Requested-With': 'XMLHttpRequest'} })
            .then(r=>r.json()).then(res=>{
                if (res.success) table.ajax.reload(null,false);
                else alert(res.error || 'ลบไม่สำเร็จ');
            }).catch(()=> alert('เกิดข้อผิดพลาด'));
    });
});
</script>
<?= $this->endSection() ?>