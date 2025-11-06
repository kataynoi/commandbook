<?= $this->extend('layout/default') ?>
<?= $this->section('content') ?>

<div class="container-fluid px-4 mt-4">
    <h1 class="mb-4">Dashboard ภาพรวม</h1>

    <div class="row">
        <?php if (empty($riskStats)): ?>
            <div class="col-12">
                <p>ไม่พบข้อมูลระดับความเสี่ยง</p>
            </div>
        <?php else: ?>
            <?php foreach ($riskStats as $stat): ?>
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card text-white shadow" style="background-color: <?= esc($stat['color_hex']) ?>;">
                        <div class="card-body pb-0">
                            <h1 class="display-4 fw-bold"><?= esc($stat['patient_count']) ?></h1>
                            <p class="mb-2"><?= esc($stat['risk_level_name']) ?></p>
                        </div>
                        <div class="card-footer d-flex align-items-center justify-content-between">
                            <a class="small text-white stretched-link" href="#">ดูรายละเอียด</a>
                            <div class="small text-white"><i class="fas fa-angle-right"></i></div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php endif; ?>
    </div>
    
    </div>

<?= $this->endSection() ?>