<?php
// --------------------------------------------------------------------
// (3/4) View: dashboard_view.php (Updated with DataLabels Plugin)
// --------------------------------------------------------------------
// อัปเดตไฟล์ที่: app/Views/dashboard/dashboard_view.php
// ** มีการเปลี่ยนแปลงในไฟล์นี้ **
?>
<?= $this->extend('layout/default') ?>
<?= $this->section('content') ?>
<?php
$session = session();

// ดึงข้อมูลทั้งหมดจาก session มาเก็บในตัวแปร $sessionData
$sessionData = $session->get();
 echo '<pre>';
 print_r($sessionData);
 echo '</pre>';
//exit;
?>
<!-- เริ่มต้นส่วนของ Dashboard -->
<div class="container-fluid mt-4">
    <h1 class="h3 mb-4 text-gray-800">Dashboard ภาพรวม</h1>

    <!-- Filter: จังหวัด/อำเภอ/รพ.สต. -->
    <div class="row mb-3">
        <div class="col-md-3">
            <label class="form-label">จังหวัด</label>
            <select id="provinceFilter" class="form-select" disabled>
                <option value="<?= esc($provinceCode) ?>"><?= esc($provinceName) ?></option>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">อำเภอ</label>
            <select id="amphurFilter" class="form-select">
                <option value="">-- ทุกอำเภอ --</option>
                <?php foreach ($amphurs as $amphur): ?>
                    <option value="<?= $amphur['ampurcodefull'] ?>"><?= $amphur['ampurname'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-md-3">
            <label class="form-label">รพ.สต.</label>
            <select id="hospFilter" class="form-select" disabled>
                <option value="">-- ทุก รพ.สต. --</option>
            </select>
        </div>
        <div class="col-md-3 d-flex align-items-end">
            <button id="searchBtn" class="btn btn-primary w-100"><i class="fas fa-search"></i> ค้นหา</button>
        </div>
    </div>

    <!-- แถบสรุปข้อมูล (Summary Cards) -->
    <div class="row" id="summaryCardsContainer">
        <div class="col-12 text-center p-5">
            <div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>
        </div>
    </div>

    <!-- กราฟต่างๆ -->
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">จำนวนผู้ป่วยแยกตามระดับความเสี่ยง (รายอำเภอ)</h6>
                </div>
                <div class="card-body">
                    <div class="chart-bar" style="height: 320px;"><canvas id="stackedBarChart"></canvas></div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">สัดส่วนผู้ป่วยตามระดับความเสี่ยง</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4" style="height: 320px;"><canvas id="doughnutChart"></canvas></div>
                </div>
            </div>
        </div>
        <div class="col-lg-6">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">สัดส่วนผู้ป่วยแยกตามเพศ</h6>
                </div>
                <div class="card-body">
                    <div class="chart-pie pt-4" style="height: 320px;"><canvas id="genderPieChart"></canvas></div>
                </div>
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <div class="card shadow mb-4">
                <div class="card-header py-3">
                    <h6 class="m-0 font-weight-bold text-primary">จำนวนผู้ป่วยแยกตามกลุ่มอายุ (ทุก 5 ปี)</h6>
                </div>
                <div class="card-body">
                    <div class="chart-area" style="height: 320px;"><canvas id="ageGroupAreaChart"></canvas></div>
                </div>
            </div>
        </div>
    </div>

</div>



<?= $this->endSection() ?>