<!DOCTYPE html>
<html lang="th">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <title>หนังสือ Dashboard</title>

    <!-- Custom fonts for this template-->
    <link href="<?php echo base_url(); ?>vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link
        href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">
    <!-- Custom styles for this template-->
    <link href="<?php echo base_url(); ?>css/sb-admin-2.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
</head>

<body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">
        <!-- Sidebar -->
        <?= $this->include('layout/partials/sidebar') ?>
        <!-- End of Sidebar -->
        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <!-- Topbar -->
                <?= $this->include('layout/partials/header') ?>
                <!-- End of Topbar -->
                <!-- Begin Page Content -->
                <div class="content-wrapper" style="padding: 2rem;">
                    <?= $this->renderSection('content') ?>
                </div>
                <!-- /.container-fluid -->
            </div>
            <!-- End of Main Content -->
            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; Dechachit@mkho.moph.go.th 2025</span>
                    </div>
                </div>
            </footer>
            <!-- End of Footer -->
        </div>
        <!-- End of Content Wrapper -->
    </div>
    <!-- End of Page Wrapper -->
    <!-- Scroll to Top Button-->
    <a class="scroll-to-top rounded" href="#page-top">
        <i class="fas fa-angle-up"></i>
    </a>
    <!-- Bootstrap core JavaScript-->
    <script src="<?php echo base_url(); ?>vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- Core plugin JavaScript-->
    <script src="<?php echo base_url(); ?>vendor/jquery-easing/jquery.easing.min.js"></script>
    <!-- Custom scripts for all pages-->
    <script src="<?php echo base_url(); ?>js/sb-admin-2.min.js"></script>
    <!-- Page level plugins -->

    <script>
        // เลือก element ทั้งหมดที่มี attribute data-bs-dismiss="modal"
        const closeButtons = document.querySelectorAll('[data-bs-dismiss="modal"]');

        // วนลูปเพื่อเพิ่ม event listener ให้กับทุกปุ่ม
        closeButtons.forEach(button => {
            button.addEventListener('click', function(event) {
                // หา modal ที่ใกล้ที่สุดจากปุ่มที่ถูกคลิก
                const modalElement = event.target.closest('.modal');

                // ตรวจสอบว่าเจอ modal หรือไม่
                if (modalElement) {
                    // สร้าง instance ของ modal จาก element
                    const modalInstance = bootstrap.Modal.getInstance(modalElement);

                    // ตรวจสอบว่ามี instance หรือไม่ก่อนที่จะสั่งซ่อน
                    if (modalInstance) {
                        // สั่งซ่อน modal
                        modalInstance.hide();

                        // (Optional) คุณสามารถเพิ่มโค้ดอื่นๆ ที่นี่ได้
                        console.log('Modal has been hidden by custom script.');
                    }
                }
            });
        });
    </script>
</body>s

</html>