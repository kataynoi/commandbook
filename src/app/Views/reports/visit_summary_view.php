<?php
// --------------------------------------------------------------------
// (3/4) View: visit_summary_view.php
// --------------------------------------------------------------------
// สร้าง View ใหม่ทั้งหมดที่: app/Views/reports/visit_summary_view.php
?>
<?= $this->extend('layout/default') ?>

<?= $this->section('content') ?>
<div class="container-fluid mt-4">
    <h3 class="mb-4">รายงานสรุปการเยี่ยมบ้าน (คน/ครั้ง)</h3>

    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <input type="hidden" id="provinceCode" value="<?= esc($provinceCode) ?>">
                <div class="col-md-3">
                    <label class="form-label">จังหวัด</label>
                    <input type="text" class="form-control" value="<?= esc($provinceName) ?>" readonly>
                </div>
                <div class="col-md-3">
                    <label for="amphurFilter" class="form-label">อำเภอ</label>
                    <select id="amphurFilter" class="form-select">
                        <option value="">-- แสดงทุกอำเภอ --</option>
                        <?php foreach ($amphurs as $amphur): ?>
                            <option value="<?= $amphur['ampurcodefull'] ?>"><?= $amphur['ampurname'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="hospFilter" class="form-label">รพ.สต.</label>
                    <select id="hospFilter" class="form-select" disabled>
                        <option value="">-- เลือกอำเภอก่อน --</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label for="villageFilter" class="form-label">หมู่บ้าน</label>
                    <select id="villageFilter" class="form-select" disabled>
                        <option value="">-- เลือก รพ.สต. ก่อน --</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card shadow-sm">
        <div class="card-header" id="reportTitle">
            สรุปข้อมูลระดับจังหวัด
        </div>
        <div class="card-body" id="reportContent">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead id="reportTableHead"></thead>
                    <tbody id="reportTableBody"></tbody>
                    <tfoot id="reportTableFoot"></tfoot>
                </table>
            </div>
            <div class="text-center" id="loadingSpinner">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        let currentLevel = 'amphur';
        let currentTitle = 'สรุปข้อมูลระดับจังหวัด';

        function fetchData() {
            const filters = {
                level: currentLevel,
                provinceCode: $('#provinceCode').val(),
                amphurCode: $('#amphurFilter').val(),
                hospCode: $('#hospFilter').val(),
                villageCode: $('#villageFilter').val(),
            };

            $('#loadingSpinner').show();
            $('#reportContent table').hide();

            $.post("<?= site_url('reports/ajax-get-visit-summary') ?>", filters, function(response) {
                renderTable(response);
                $('#reportTitle').text(currentTitle);
            }, 'json').fail(function() {
                Swal.fire('ผิดพลาด', 'ไม่สามารถดึงข้อมูลรายงานได้', 'error');
            }).always(function() {
                $('#loadingSpinner').hide();
                $('#reportContent table').show();
            });
        }

        function renderTable(response) {
            const head = $('#reportTableHead');
            const body = $('#reportTableBody');
            const foot = $('#reportTableFoot');
            head.empty();
            body.empty();
            foot.empty();

            if (response.type === 'summary') {
                head.html('<tr><th>พื้นที่</th><th>จำนวนคน (คน)</th><th>จำนวนครั้ง (ครั้ง)</th></tr>');
                let totalPerson = 0;
                let totalVisit = 0;
                response.data.forEach(row => {
                    body.append(`<tr>
                                <td>${row.area_name || 'ไม่ระบุ'}</td>
                                <td>${row.person_count}</td>
                                <td>${row.visit_count}</td>
                             </tr>`);
                    totalPerson += parseInt(row.person_count || 0);
                    totalVisit += parseInt(row.visit_count || 0);
                });
                foot.html(`<tr>
                        <th class="text-end">รวมทั้งหมด</th>
                        <th>${totalPerson}</th>
                        <th>${totalVisit}</th>
                       </tr>`);
            } else if (response.type === 'patient_list') {
                head.html('<tr><th>CID</th><th>ชื่อ-สกุล</th><th>ระดับความเสี่ยง</th><th>เยี่ยมล่าสุด</th></tr>');
                if (response.data.length > 0) {
                    response.data.forEach(row => {
                        body.append(`<tr>
                                    <td>${row.cid}</td>
                                    <td>${row.fullname}</td>
                                    <td><span class="badge" style="background-color:${row.color_hex};">${row.risk_level_name}</span></td>
                                    <td>${row.last_visit_date || 'N/A'}</td>
                                 </tr>`);
                    });
                } else {
                    body.append('<tr><td colspan="4" class="text-center">ไม่พบข้อมูลผู้ป่วยที่ถูกเยี่ยมในพื้นที่นี้</td></tr>');
                }
            }
        }

        // --- Event Handlers for Filters ---
        $('#amphurFilter').on('change', function() {
            const amphurCode = $(this).val();
            currentLevel = 'hospcode';
            currentTitle = `สรุปข้อมูลระดับ รพ.สต. ใน อ.${$('#amphurFilter option:selected').text()}`;
            if (!amphurCode) {
                currentLevel = 'amphur';
                currentTitle = 'สรุปข้อมูลระดับจังหวัด';
            }
            $('#hospFilter').val('');
            $('#villageFilter').val('').prop('disabled', true);
            loadDropdown('hospFilter', `<?= site_url('ajax/get-hospitals') ?>`, {
                amphur_code: amphurCode
            }, '-- ทุก รพ.สต. --');
            fetchData();
        });

        $('#hospFilter').on('change', function() {
            const hospCode = $(this).val();
            currentLevel = 'village';
            currentTitle = `สรุปข้อมูลระดับหมู่บ้าน ใน ${$('#hospFilter option:selected').text()}`;
            if (!hospCode) {
                currentLevel = 'hospcode';
                currentTitle = `สรุปข้อมูลระดับ รพ.สต. ใน อ.${$('#amphurFilter option:selected').text()}`;
            }
            $('#villageFilter').val('');
            loadDropdown('villageFilter', `<?= site_url('ajax/get-villages') ?>`, {
                hosp_code: hospCode
            }, '-- ทุกหมู่บ้าน --');
            fetchData();
        });

        $('#villageFilter').on('change', function() {
            const villageCode = $(this).val();
            currentLevel = 'village'; // level is village, but ajax checks if villageCode has value
            currentTitle = `รายชื่อผู้ป่วยใน ${$('#villageFilter option:selected').text()}`;
            if (!villageCode) {
                currentLevel = 'village';
                currentTitle = `สรุปข้อมูลระดับหมู่บ้าน ใน ${$('#hospFilter option:selected').text()}`;
            }
            fetchData();
        });

        function loadDropdown(elementId, url, data, placeholder) {
            // (Use your existing loadDropdown function)
        }

        // Initial data load
        fetchData();
    });
</script>
<?= $this->endSection() ?>