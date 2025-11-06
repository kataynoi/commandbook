<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8" />
    <meta http-equiv="X-UA-Compatible" content="IE=edge" />
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no" />
    <meta name="description" content="" />
    <meta name="author" content="" />
    <title>SMIV มหาสารคาม</title>
    <link href="<?= base_url() ?>css/styles.css" rel="stylesheet" />
    <script src="https://use.fontawesome.com/releases/v6.3.0/js/all.js" crossorigin="anonymous"></script>
<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>

<body class="sb-nav-fixed">
    <!-- Start Top Menu -->
    <?= $this->include('layout/partials/header') ?>
    <!-- End Top Menu -->
    <div id="layoutSidenav">
        <!-- SlideBar -->
        <?= $this->include('layout/partials/sidebar') ?>
       <!-- End Slidebar  -->
        <div id="layoutSidenav_content">
            <main>
                <!-- Content -->
                <?= $this->renderSection('content') ?>
                <!-- End Content -->
            </main>
            <footer class="py-4 bg-light mt-auto">
                <div class="container-fluid px-4">
                    <div class="d-flex align-items-center justify-content-between small">
                        <div class="text-muted">Copyright &copy; Your Website 2023</div>
                        <div>
                            <a href="#">Privacy Policy</a>
                            &middot;
                            <span>Copyright &copy; Dechachit@mkho.moph.go.th 2025</span>
                        </div>
                    </div>
                </div>
            </footer>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
    <script src="<?= base_url() ?>js/scripts.js"></script>
</body>

</html>