<?php
// --------------------------------------------------------------------
// (3/4) View: patient_summary_view.php (Updated)
// --------------------------------------------------------------------
// อัปเดตไฟล์ที่: app/Views/reports/patient_summary_view.php
// ** ลบ Dropdown จังหวัด และแสดงชื่อจังหวัดแทน **
?>
<?= $this->extend('layout/default') ?>
<?= $this->section('content') ?>

<div class="container-fluid mt-4">
    <!-- *** START: จุดที่แก้ไข *** -->
    <h3 class="mb-4">รายงานสรุปจำนวนผู้ป่วย จังหวัด<?= esc($provinceName) ?></h3>
    <!-- *** END: จุดที่แก้ไข *** -->

    <!-- ส่วนของ Filter -->
    <div class="card shadow-sm mb-4">
        <div class="card-body">
            <div class="row g-3 align-items-end">
                <!-- *** START: จุดที่แก้ไข *** -->
                <div class="col-md-5">
                    <label for="amphurFilter" class="form-label">เลือกอำเภอ</label>
                    <select id="amphurFilter" class="form-select">
                        <option value="">-- แสดงภาพรวมทุกอำเภอ --</option>
                        <?php foreach($amphurs as $amphur): ?>
                            <option value="<?= $amphur['ampurcodefull'] ?>"><?= $amphur['ampurname'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-5">
                    <label for="hospFilter" class="form-label">เลือก รพ.สต.</label>
                    <select id="hospFilter" class="form-select">
                        <option value="">-- แสดงทุก รพ.สต. ในอำเภอ --</option>
                    </select>
                </div>
                <!-- *** END: จุดที่แก้ไข *** -->
                <div class="col-md-2">
                    <button id="searchBtn" class="btn btn-primary w-100">
                        <i class="fas fa-search"></i> ค้นหา
                    </button>
                </div>
                 <div class="col-md-2">
                    <button id="resetBtn" class="btn btn-secondary w-100">
                        <i class="fas fa-sync-alt"></i> รีเซ็ต
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- ส่วนของตารางรายงาน (ยังคงเหมือนเดิม) -->
    <div class="card shadow-sm">
        <!-- ... (โค้ดตารางเหมือนเดิม) ... -->
    </div>
</div>
<script>
// JavaScript ทั้งหมดในส่วนนี้ยังคงทำงานได้เหมือนเดิม ไม่ต้องแก้ไข
$(document).ready(function() {
    
    // --- Event Handlers ---
    $('#amphurFilter').on('change', function() {
        const amphurCode = $(this).val();
        const hospFilter = $('#hospFilter');
        
        hospFilter.prop('disabled', true).html('<option value="">-- แสดงทุก รพ.สต. ในอำเภอ --</option>');

        if (amphurCode) {
            $.post("<?= site_url('reports/get-hospitals') ?>", { amphur_code: amphurCode }, function(data) {
                if(data.length > 0){
                    data.forEach(hosp => {
                        hospFilter.append(`<option value="${hosp.hoscode}">${hosp.hosname}</option>`);
                    });
                    hospFilter.prop('disabled', false);
                }
            }, 'json');
        }
    });

    $('#searchBtn').on('click', function() {
        fetchReportData();
    });

    $('#resetBtn').on('click', function() {
        $('#amphurFilter').val('').trigger('change');
        fetchReportData();
    });

    // --- Core Functions ---
    function fetchReportData() {
        // ... (โค้ดส่วนนี้เหมือนเดิม) ...
    }

    function renderReportTable(response) {
        // ... (โค้ดส่วนนี้เหมือนเดิม) ...
    }

    // --- Initial Load ---
    fetchReportData();
});
</script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<?= $this->endSection() ?>