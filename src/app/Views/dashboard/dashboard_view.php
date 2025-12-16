<?= $this->extend('layout/default') ?>
<?= $this->section('content') ?>

<div class="container-fluid px-4">
    <h1 class="mt-4">Dashboard</h1>
    <ol class="breadcrumb mb-4">
        <li class="breadcrumb-item active">ภาพรวมระบบหนังสือคำสั่ง</li>
    </ol>

    <!-- Summary Cards -->
    <div class="row" id="summaryCards">
        <div class="col-xl-3 col-md-6">
            <div class="card bg-primary text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small">หนังสือคำสั่งทั้งหมด</div>
                            <div class="h2 mb-0" id="totalDocuments">
                                <div class="spinner-border spinner-border-sm" role="status"></div>
                            </div>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-file-alt"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <small>เดือนนี้: <span id="documentsThisMonth">-</span> ฉบับ</small>
                    <a class="small text-white stretched-link" href="<?= site_url('commands') ?>">
                        <i class="fas fa-angle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-success text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small">จำนวนผู้ใช้งาน</div>
                            <div class="h2 mb-0" id="totalUsers">
                                <div class="spinner-border spinner-border-sm" role="status"></div>
                            </div>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-users"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <small>รออนุมัติ: <span id="pendingUsers">-</span> คน</small>
                    <a class="small text-white stretched-link" href="<?= site_url('admin/manage-users') ?>">
                        <i class="fas fa-angle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-warning text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small">ดาวน์โหลดทั้งหมด</div>
                            <div class="h2 mb-0" id="totalDownloads">
                                <div class="spinner-border spinner-border-sm" role="status"></div>
                            </div>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-download"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <small>เดือนนี้: <span id="downloadsThisMonth">-</span> ครั้ง</small>
                    <a class="small text-white stretched-link" href="<?= site_url('activity-logs') ?>">
                        <i class="fas fa-angle-right"></i>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-xl-3 col-md-6">
            <div class="card bg-info text-white mb-4">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <div class="small">เข้าชมเดือนนี้</div>
                            <div class="h2 mb-0" id="visitsThisMonth">
                                <span id="downloadsThisMonth2">-</span>
                            </div>
                        </div>
                        <div class="fs-1 opacity-50">
                            <i class="fas fa-chart-line"></i>
                        </div>
                    </div>
                </div>
                <div class="card-footer d-flex align-items-center justify-content-between">
                    <small>กิจกรรมทั้งหมด</small>
                    <a class="small text-white stretched-link" href="<?= site_url('activity-logs') ?>">
                        <i class="fas fa-angle-right"></i>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Row -->
    <div class="row">
        <!-- Upload & Download Trend Chart -->
        <div class="col-xl-8">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-area me-1"></i>
                    สถิติการอัปโหลดและดาวน์โหลด (6 เดือนล่าสุด)
                </div>
                <div class="card-body">
                    <canvas id="trendChart" width="100%" height="40"></canvas>
                </div>
            </div>
        </div>

        <!-- Top Downloads Chart -->
        <div class="col-xl-4">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-chart-bar me-1"></i>
                    เอกสารยอดนิยม (Top 5)
                </div>
                <div class="card-body">
                    <canvas id="topDownloadsChart" width="100%" height="40"></canvas>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Documents Table -->
    <div class="row">
        <div class="col-xl-12">
            <div class="card mb-4">
                <div class="card-header">
                    <i class="fas fa-file-alt me-1"></i>
                    หนังสือคำสั่งล่าสุด
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover" id="recentDocumentsTable">
                            <thead>
                                <tr>
                                    <th>เลขที่</th>
                                    <th>ชื่อเรื่อง</th>
                                    <th>วันที่</th>
                                    <th>ผู้อัปโหลด</th>
                                    <th>การดำเนินการ</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="5" class="text-center">
                                        <div class="spinner-border text-primary" role="status">
                                            <span class="visually-hidden">Loading...</span>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>

<script>
$(document).ready(function() {
    // Load Dashboard Data
    loadDashboardData();

    function loadDashboardData() {
        $.ajax({
            url: '<?= base_url('dashboard/data') ?>',
            type: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.success) {
                    updateSummaryCards(response.data);
                    renderTrendChart(response.data.uploadsByMonth, response.data.downloadsByMonth);
                    renderTopDownloadsChart(response.data.topDownloads);
                    renderRecentDocuments(response.data.recentDocuments);
                }
            },
            error: function() {
                console.error('Failed to load dashboard data');
            }
        });
    }

    function updateSummaryCards(data) {
        $('#totalDocuments').html(data.totalDocuments.toLocaleString());
        $('#documentsThisMonth').html(data.documentsThisMonth);
        $('#totalUsers').html(data.totalUsers.toLocaleString());
        $('#pendingUsers').html(data.pendingUsers);
        $('#totalDownloads').html(data.totalDownloads.toLocaleString());
        $('#downloadsThisMonth').html(data.downloadsThisMonth);
        $('#downloadsThisMonth2').html(data.downloadsThisMonth);
    }

    function renderTrendChart(uploads, downloads) {
        const ctx = document.getElementById('trendChart').getContext('2d');
        
        // สร้าง labels จากข้อมูล
        const labels = uploads.map(item => {
            const date = new Date(item.month + '-01');
            return date.toLocaleDateString('th-TH', { year: 'numeric', month: 'short' });
        });

        const uploadData = uploads.map(item => item.count);
        
        // จับคู่ downloads กับ uploads
        const downloadData = uploads.map(upload => {
            const found = downloads.find(d => d.month === upload.month);
            return found ? found.count : 0;
        });

        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'อัปโหลด',
                        data: uploadData,
                        borderColor: 'rgb(54, 162, 235)',
                        backgroundColor: 'rgba(54, 162, 235, 0.1)',
                        tension: 0.4
                    },
                    {
                        label: 'ดาวน์โหลด',
                        data: downloadData,
                        borderColor: 'rgb(255, 159, 64)',
                        backgroundColor: 'rgba(255, 159, 64, 0.1)',
                        tension: 0.4
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'top',
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }

    function renderTopDownloadsChart(topDownloads) {
        const ctx = document.getElementById('topDownloadsChart').getContext('2d');
        
        // เอาแค่ 5 อันดับแรก
        const top5 = topDownloads.slice(0, 5);
        
        const labels = top5.map(item => {
            const title = item.doc_number || 'N/A';
            return title.length > 15 ? title.substring(0, 15) + '...' : title;
        });
        
        const data = top5.map(item => item.download_count);

        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'จำนวนดาวน์โหลด',
                    data: data,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.8)',
                        'rgba(54, 162, 235, 0.8)',
                        'rgba(255, 206, 86, 0.8)',
                        'rgba(75, 192, 192, 0.8)',
                        'rgba(153, 102, 255, 0.8)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: {
                            precision: 0
                        }
                    }
                }
            }
        });
    }

    function renderRecentDocuments(documents) {
        const tbody = $('#recentDocumentsTable tbody');
        tbody.empty();

        if (documents.length === 0) {
            tbody.append('<tr><td colspan="5" class="text-center">ไม่มีข้อมูล</td></tr>');
            return;
        }

        documents.forEach(doc => {
            const row = `
                <tr>
                    <td>${doc.doc_number || '-'}</td>
                    <td><a href="<?= site_url('commands') ?>" class="text-decoration-none">${doc.doc_title || '-'}</a></td>
                    <td>${doc.doc_date || '-'}</td>
                    <td>${doc.uploader_name || '-'}</td>
                    <td>
                        <a href="<?= site_url('access') ?>/${doc.qr_token}" class="btn btn-sm btn-primary" target="_blank">
                            <i class="fas fa-download"></i>
                        </a>
                    </td>
                </tr>
            `;
            tbody.append(row);
        });
    }
});
</script>

<?= $this->endSection() ?>