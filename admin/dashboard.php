<?php
include '../includes/auth.php';
redirectIfNotAdmin();

include '../includes/db.php';

// Get statistics
$total_kamar = mysqli_query($conn, "SELECT COUNT(*) as total FROM kamar")->fetch_assoc()['total'];
$kamar_tersedia = mysqli_query($conn, "SELECT COUNT(*) as total FROM kamar WHERE status = 'tersedia'")->fetch_assoc()['total'];
$total_pemesanan = mysqli_query($conn, "SELECT COUNT(*) as total FROM pemesanan")->fetch_assoc()['total'];
$pemesanan_menunggu = mysqli_query($conn, "SELECT COUNT(*) as total FROM pemesanan WHERE status = 'menunggu'")->fetch_assoc()['total'];
$total_pembayaran = mysqli_query($conn, "SELECT COUNT(*) as total FROM pembayaran")->fetch_assoc()['total'];
$pembayaran_menunggu = mysqli_query($conn, "SELECT COUNT(*) as total FROM pembayaran WHERE status = 'menunggu'")->fetch_assoc()['total'];

// Check for success messages from other pages
$success_message = '';
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

// Check for error messages from other pages
$error_message = '';
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

// Check for login success message
$login_success = '';
if (isset($_SESSION['login_success'])) {
    $login_success = $_SESSION['login_success'];
    unset($_SESSION['login_success']);
}

// Check for info messages
$info_message = '';
if (isset($_SESSION['info_message'])) {
    $info_message = $_SESSION['info_message'];
    unset($_SESSION['info_message']);
}

// Check for warning messages
$warning_message = '';
if (isset($_SESSION['warning_message'])) {
    $warning_message = $_SESSION['warning_message'];
    unset($_SESSION['warning_message']);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Kos Management</title>
    
    <!-- SB Admin 2 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/startbootstrap-sb-admin-2/4.1.4/css/sb-admin-2.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <!-- Custom CSS -->
    <style>
        .sidebar {
            background-color: #4e73df;
            background-image: linear-gradient(180deg, #4e73df 10%, #224abe 100%);
            background-size: cover;
        }
        .sidebar .nav-item .nav-link {
            color: rgba(255, 255, 255, 0.8);
        }
        .sidebar .nav-item .nav-link:hover {
            color: #fff;
        }
        .sidebar .nav-item.active .nav-link {
            color: #fff;
            font-weight: bold;
        }
        .card-icon {
            font-size: 2rem;
        }
        .bg-primary {
            background-color: #4e73df !important;
        }
        .bg-success {
            background-color: #1cc88a !important;
        }
        .bg-warning {
            background-color: #f6c23e !important;
        }
        .bg-info {
            background-color: #36b9cc !important;
        }
        
        /* Custom animations for cards */
        .card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
        }
        
        /* Quick action buttons styling */
        .quick-action-btn {
            transition: all 0.3s ease;
        }
        .quick-action-btn:hover {
            transform: scale(1.05);
        }
    </style>
</head>
<body id="page-top">
    <!-- Page Wrapper -->
    <div id="wrapper">
        <!-- Sidebar -->
        <ul class="navbar-nav bg-gradient-primary sidebar sidebar-dark accordion" id="accordionSidebar">
            <!-- Sidebar - Brand -->
            <a class="sidebar-brand d-flex align-items-center justify-content-center" href="dashboard.php">
                <div class="sidebar-brand-icon rotate-n-15">
                    <i class="fas fa-home"></i>
                </div>
                <div class="sidebar-brand-text mx-3">Kos Management</div>
            </a>

            <!-- Divider -->
            <hr class="sidebar-divider my-0">

            <!-- Nav Item - Dashboard -->
            <li class="nav-item active">
                <a class="nav-link" href="dashboard.php">
                    <i class="fas fa-fw fa-tachometer-alt"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider">

            <!-- Heading -->
            <div class="sidebar-heading">
                Manajemen
            </div>

            <!-- Nav Item - Kamar -->
            <li class="nav-item">
                <a class="nav-link" href="kamar.php">
                    <i class="fas fa-fw fa-bed"></i>
                    <span>Kelola Kamar</span>
                </a>
            </li>

            <!-- Nav Item - Pemesanan -->
            <li class="nav-item">
                <a class="nav-link" href="pemesanan.php">
                    <i class="fas fa-fw fa-calendar-check"></i>
                    <span>Kelola Pemesanan</span>
                </a>
            </li>

            <!-- Nav Item - Pembayaran -->
            <li class="nav-item">
                <a class="nav-link" href="pembayaran.php">
                    <i class="fas fa-fw fa-money-check-alt"></i>
                    <span>Verifikasi Pembayaran</span>
                </a>
            </li>

            <!-- Nav Item - Notifikasi -->
            <li class="nav-item">
                <a class="nav-link" href="notifikasi.php">
                    <i class="fas fa-fw fa-bell"></i>
                    <span>Notifikasi</span>
                </a>
            </li>

            <!-- Divider -->
            <hr class="sidebar-divider d-none d-md-block">

            <!-- Sidebar Toggler (Sidebar) -->
            <div class="text-center d-none d-md-inline">
                <button class="rounded-circle border-0" id="sidebarToggle"></button>
            </div>
        </ul>
        <!-- End of Sidebar -->

        <!-- Content Wrapper -->
        <div id="content-wrapper" class="d-flex flex-column">
            <!-- Main Content -->
            <div id="content">
                <!-- Topbar -->
                <nav class="navbar navbar-expand navbar-light bg-white topbar mb-4 static-top shadow">
                    <!-- Sidebar Toggle (Topbar) -->
                    <button id="sidebarToggleTop" class="btn btn-link d-md-none rounded-circle mr-3">
                        <i class="fa fa-bars"></i>
                    </button>

                    <!-- Topbar Navbar -->
                    <ul class="navbar-nav ml-auto">
                        <!-- Nav Item - User Information -->
                        <li class="nav-item dropdown no-arrow">
                            <a class="nav-link dropdown-toggle" href="#" id="userDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <span class="mr-2 d-none d-lg-inline text-gray-600 small"><?php echo $_SESSION['admin_nama']; ?></span>
                                <img class="img-profile rounded-circle" src="https://ui-avatars.com/api/?name=<?php echo urlencode($_SESSION['admin_nama']); ?>&background=4e73df&color=ffffff&size=32">
                            </a>
                            <!-- Dropdown - User Information -->
                            <div class="dropdown-menu dropdown-menu-right shadow animated--grow-in" aria-labelledby="userDropdown">
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-user fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Profile
                                </a>
                                <a class="dropdown-item" href="#">
                                    <i class="fas fa-cogs fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Settings
                                </a>
                                <div class="dropdown-divider"></div>
                                <a class="dropdown-item" href="#" id="logoutBtn">
                                    <i class="fas fa-sign-out-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                    Logout
                                </a>
                            </div>
                        </li>
                    </ul>
                </nav>
                <!-- End of Topbar -->

                <!-- Begin Page Content -->
                <div class="container-fluid">
                    <!-- Page Heading -->
                    <div class="d-sm-flex align-items-center justify-content-between mb-4">
                        <h1 class="h3 mb-0 text-gray-800">Dashboard Admin</h1>
                        <button class="btn btn-primary btn-sm" onclick="refreshDashboard()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>

                    <!-- Content Row -->
                    <div class="row">
                        <!-- Total Kamar Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Total Kamar</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $total_kamar; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-bed fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Kamar Tersedia Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Kamar Tersedia</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $kamar_tersedia; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pemesanan Menunggu Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                Pemesanan Menunggu</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $pemesanan_menunggu; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Pembayaran Menunggu Card -->
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                Pembayaran Menunggu</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $pembayaran_menunggu; ?></div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-dollar-sign fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Content Row -->
                    <div class="row">
                        <!-- Recent Activities -->
                        <div class="col-xl-8 col-lg-7">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Aktivitas Terbaru</h6>
                                    <button class="btn btn-sm btn-outline-primary" onclick="showAllActivities()">
                                        <i class="fas fa-list"></i> Lihat Semua
                                    </button>
                                </div>
                                <div class="card-body">
                                    <div class="table-responsive">
                                        <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                            <thead>
                                                <tr>
                                                    <th>Waktu</th>
                                                    <th>Aktivitas</th>
                                                    <th>Pengguna</th>
                                                    <th>Kamar</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                <?php
                                                $recent_activities = mysqli_query($conn, "
                                                    SELECT 'pemesanan' as type, p.created_at, u.nama, k.nama_kamar 
                                                    FROM pemesanan p 
                                                    JOIN users u ON p.id_user = u.id 
                                                    JOIN kamar k ON p.id_kamar = k.id 
                                                    ORDER BY p.created_at DESC LIMIT 5
                                                ");
                                                
                                                while ($activity = mysqli_fetch_assoc($recent_activities)) {
                                                    echo "
                                                    <tr>
                                                        <td>" . date('d M Y H:i', strtotime($activity['created_at'])) . "</td>
                                                        <td>Pemesanan Baru</td>
                                                        <td>{$activity['nama']}</td>
                                                        <td>{$activity['nama_kamar']}</td>
                                                    </tr>";
                                                }
                                                ?>
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Quick Actions -->
                        <div class="col-xl-4 col-lg-5">
                            <div class="card shadow mb-4">
                                <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                                    <h6 class="m-0 font-weight-bold text-primary">Aksi Cepat</h6>
                                </div>
                                <div class="card-body">
                                    <div class="text-center">
                                        <a href="kamar.php" class="btn btn-primary btn-icon-split mb-3 quick-action-btn">
                                            <span class="icon text-white-50">
                                                <i class="fas fa-bed"></i>
                                            </span>
                                            <span class="text">Kelola Kamar</span>
                                        </a>
                                        <a href="pemesanan.php" class="btn btn-success btn-icon-split mb-3 quick-action-btn">
                                            <span class="icon text-white-50">
                                                <i class="fas fa-calendar-check"></i>
                                            </span>
                                            <span class="text">Kelola Pemesanan</span>
                                        </a>
                                        <a href="pembayaran.php" class="btn btn-warning btn-icon-split mb-3 quick-action-btn">
                                            <span class="icon text-white-50">
                                                <i class="fas fa-money-check-alt"></i>
                                            </span>
                                            <span class="text">Verifikasi Pembayaran</span>
                                        </a>
                                        <a href="notifikasi.php" class="btn btn-info btn-icon-split quick-action-btn">
                                            <span class="icon text-white-50">
                                                <i class="fas fa-bell"></i>
                                            </span>
                                            <span class="text">Notifikasi</span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.container-fluid -->
            </div>
            <!-- End of Main Content -->

            <!-- Footer -->
            <footer class="sticky-footer bg-white">
                <div class="container my-auto">
                    <div class="copyright text-center my-auto">
                        <span>Copyright &copy; Kos Management <?php echo date('Y'); ?></span>
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
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.0/dist/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-easing/1.4.1/jquery.easing.min.js"></script>

    <!-- SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/startbootstrap-sb-admin-2/4.1.4/js/sb-admin-2.min.js"></script>

    <!-- Custom Script -->
    <script>
        // SweetAlert notifications based on PHP session messages
        $(document).ready(function() {
            <?php if (!empty($login_success)): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Selamat Datang!',
                    text: '<?php echo $login_success; ?>',
                    timer: 3000,
                    showConfirmButton: false,
                    position: 'top-end',
                    toast: true
                });
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: '<?php echo $success_message; ?>',
                    timer: 4000,
                    showConfirmButton: true,
                    confirmButtonText: 'OK',
                    confirmButtonColor: '#1cc88a'
                });
            <?php endif; ?>

            <?php if (!empty($error_message)): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Oops...',
                    text: '<?php echo $error_message; ?>',
                    timer: 5000,
                    showConfirmButton: true,
                    confirmButtonText: 'Mengerti',
                    confirmButtonColor: '#e74a3b'
                });
            <?php endif; ?>

            <?php if (!empty($info_message)): ?>
                Swal.fire({
                    icon: 'info',
                    title: 'Informasi',
                    text: '<?php echo $info_message; ?>',
                    timer: 4000,
                    showConfirmButton: false,
                    position: 'top-end',
                    toast: true
                });
            <?php endif; ?>

            <?php if (!empty($warning_message)): ?>
                Swal.fire({
                    icon: 'warning',
                    title: 'Peringatan',
                    text: '<?php echo $warning_message; ?>',
                    timer: 5000,
                    showConfirmButton: true,
                    confirmButtonText: 'Mengerti',
                    confirmButtonColor: '#f6c23e'
                });
            <?php endif; ?>
        });

        // Logout confirmation with SweetAlert
        $('#logoutBtn').on('click', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Konfirmasi Logout',
                text: 'Apakah Anda yakin ingin logout?',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1cc88a',
                cancelButtonColor: '#e74a3b',
                confirmButtonText: 'Ya, Logout!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '../logout.php';
                }
            });
        });

        // Refresh dashboard function
        function refreshDashboard() {
            Swal.fire({
                title: 'Memperbarui Data',
                text: 'Sedang memuat data terbaru...',
                icon: 'info',
                showConfirmButton: false,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            setTimeout(() => {
                location.reload();
            }, 1000);
        }

        // Show all activities
        function showAllActivities() {
            Swal.fire({
                title: 'Semua Aktivitas',
                text: 'Fitur ini akan menampilkan semua aktivitas pemesanan.',
                icon: 'info',
                confirmButtonText: 'Mengerti',
                confirmButtonColor: '#4e73df'
            });
        }

        // Card click animations
        $('.card').on('click', function() {
            $(this).addClass('animate__animated animate__pulse');
            setTimeout(() => {
                $(this).removeClass('animate__animated animate__pulse');
            }, 1000);
        });

        // Auto refresh notifications (optional)
        setInterval(() => {
            // You can add real-time notification check here
            console.log('Checking for new notifications...');
        }, 30000);
    </script>
</body>
</html>