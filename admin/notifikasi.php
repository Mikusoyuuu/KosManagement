<?php
include '../includes/auth.php';
redirectIfNotAdmin();

include '../includes/db.php';

// Variables untuk feedback messages
$success_message = '';
$info_message = '';
$warning_message = '';

// Mark notification as read
if (isset($_GET['baca'])) {
    $id = mysqli_real_escape_string($conn, $_GET['baca']);
    $query = "UPDATE notifikasi SET status = 'dibaca' WHERE id = '$id'";
    if (mysqli_query($conn, $query)) {
        $_SESSION['info_message'] = "Notifikasi telah ditandai sebagai sudah dibaca!";
        header('Location: notifikasi.php');
        exit();
    }
}

// Mark all as read
if (isset($_GET['baca_semua'])) {
    $query = "UPDATE notifikasi SET status = 'dibaca' WHERE (id_admin IS NULL OR id_admin = '{$_SESSION['admin_id']}') AND status = 'belum_dibaca'";
    if (mysqli_query($conn, $query)) {
        $_SESSION['success_message'] = "Semua notifikasi telah ditandai sebagai sudah dibaca!";
        header('Location: notifikasi.php');
        exit();
    }
}

// Delete notification
if (isset($_GET['hapus'])) {
    $id = mysqli_real_escape_string($conn, $_GET['hapus']);
    $query = "DELETE FROM notifikasi WHERE id = '$id'";
    if (mysqli_query($conn, $query)) {
        $_SESSION['warning_message'] = "Notifikasi telah dihapus!";
        header('Location: notifikasi.php');
        exit();
    }
}

// Check for messages from session
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['info_message'])) {
    $info_message = $_SESSION['info_message'];
    unset($_SESSION['info_message']);
}

if (isset($_SESSION['warning_message'])) {
    $warning_message = $_SESSION['warning_message'];
    unset($_SESSION['warning_message']);
}

// Get notifications count
$notifikasi_belum_dibaca = mysqli_query($conn, "
    SELECT COUNT(*) as total 
    FROM notifikasi 
    WHERE (id_admin IS NULL OR id_admin = '{$_SESSION['admin_id']}') 
    AND status = 'belum_dibaca'
")->fetch_assoc()['total'];

// Get notifications
$notifikasi_query = "
    SELECT n.*, u.nama as nama_user, u.email
    FROM notifikasi n 
    LEFT JOIN users u ON n.id_user = u.id 
    WHERE n.id_admin IS NULL OR n.id_admin = '{$_SESSION['admin_id']}'
    ORDER BY n.created_at DESC
";
$notifikasi_result = mysqli_query($conn, $notifikasi_query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Notifikasi - Admin</title>
    
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
        .notification-unread {
            background-color: #f8f9fc;
            border-left: 4px solid #4e73df;
        }
        .notification-read {
            background-color: #ffffff;
            border-left: 4px solid #e3e6f0;
        }
        .notification-item {
            transition: all 0.3s ease;
            border-bottom: 1px solid #eaecf4;
        }
        .notification-item:hover {
            background-color: #f8f9fa;
            transform: translateX(5px);
        }
        .notification-icon {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin-right: 15px;
        }
        .notification-icon-unread {
            background-color: #4e73df;
            color: white;
        }
        .notification-icon-read {
            background-color: #b7b9cc;
            color: white;
        }
        .action-btn {
            transition: all 0.3s ease;
        }
        .action-btn:hover {
            transform: translateY(-2px);
        }
        .notification-badge {
            animation: pulse 2s infinite;
        }
        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); }
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
            <li class="nav-item">
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
            <li class="nav-item active">
                <a class="nav-link" href="notifikasi.php">
                    <i class="fas fa-fw fa-bell"></i>
                    <span>Notifikasi</span>
                    <?php if ($notifikasi_belum_dibaca > 0): ?>
                        <span class="badge badge-danger badge-counter notification-badge"><?php echo $notifikasi_belum_dibaca; ?></span>
                    <?php endif; ?>
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
                        <!-- Nav Item - Alerts -->
                        <li class="nav-item dropdown no-arrow mx-1">
                            <a class="nav-link dropdown-toggle" href="#" id="alertsDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                <!-- Counter - Alerts -->
                                <?php if ($notifikasi_belum_dibaca > 0): ?>
                                    <span class="badge badge-danger badge-counter notification-badge"><?php echo $notifikasi_belum_dibaca; ?></span>
                                <?php endif; ?>
                            </a>
                        </li>

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
                        <h1 class="h3 mb-0 text-gray-800">Notifikasi</h1>
                        <div class="d-flex">
                            <?php if ($notifikasi_belum_dibaca > 0): ?>
                                <button class="btn btn-success btn-icon-split mr-2 action-btn" id="markAllReadBtn">
                                    <span class="icon text-white-50">
                                        <i class="fas fa-check-double"></i>
                                    </span>
                                    <span class="text">Tandai Semua Dibaca</span>
                                </button>
                            <?php endif; ?>
                            <button class="btn btn-primary btn-icon-split action-btn" onclick="refreshNotifications()">
                                <span class="icon text-white-50">
                                    <i class="fas fa-sync-alt"></i>
                                </span>
                                <span class="text">Refresh</span>
                            </button>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Total Notifikasi</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php 
                                                $total = mysqli_query($conn, "
                                                    SELECT COUNT(*) as total 
                                                    FROM notifikasi 
                                                    WHERE id_admin IS NULL OR id_admin = '{$_SESSION['admin_id']}'
                                                ")->fetch_assoc()['total'];
                                                echo $total;
                                                ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-bell fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-warning shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                Belum Dibaca</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php echo $notifikasi_belum_dibaca; ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-envelope fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-success shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Sudah Dibaca</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php 
                                                $dibaca = mysqli_query($conn, "
                                                    SELECT COUNT(*) as total 
                                                    FROM notifikasi 
                                                    WHERE (id_admin IS NULL OR id_admin = '{$_SESSION['admin_id']}') 
                                                    AND status = 'dibaca'
                                                ")->fetch_assoc()['total'];
                                                echo $dibaca;
                                                ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-envelope-open fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-info shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                                Hari Ini</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php 
                                                $hari_ini = mysqli_query($conn, "
                                                    SELECT COUNT(*) as total 
                                                    FROM notifikasi 
                                                    WHERE (id_admin IS NULL OR id_admin = '{$_SESSION['admin_id']}') 
                                                    AND DATE(created_at) = CURDATE()
                                                ")->fetch_assoc()['total'];
                                                echo $hari_ini;
                                                ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-calendar-day fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Notifications List Card -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-bell"></i> Daftar Notifikasi
                            </h6>
                            <div class="dropdown no-arrow">
                                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                                    <div class="dropdown-header">Filter:</div>
                                    <a class="dropdown-item filter-btn" href="#" data-filter="all">Semua</a>
                                    <a class="dropdown-item filter-btn" href="#" data-filter="unread">Belum Dibaca</a>
                                    <a class="dropdown-item filter-btn" href="#" data-filter="read">Sudah Dibaca</a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item text-danger" href="#" id="deleteAllReadBtn">
                                        <i class="fas fa-trash mr-2"></i>Hapus Semua yang Sudah Dibaca
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <?php if (mysqli_num_rows($notifikasi_result) > 0): ?>
                                <div class="list-group" id="notificationsList">
                                    <?php while ($notif = mysqli_fetch_assoc($notifikasi_result)): 
                                        $is_unread = $notif['status'] == 'belum_dibaca';
                                        $notification_class = $is_unread ? 'notification-unread' : 'notification-read';
                                        $icon_class = $is_unread ? 'notification-icon-unread' : 'notification-icon-read';
                                    ?>
                                        <div class="list-group-item notification-item <?php echo $notification_class; ?> p-4" data-id="<?php echo $notif['id']; ?>" data-status="<?php echo $notif['status']; ?>">
                                            <div class="d-flex align-items-start">
                                                <div class="notification-icon <?php echo $icon_class; ?>">
                                                    <i class="fas fa-bell"></i>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <div class="d-flex justify-content-between align-items-start">
                                                        <div>
                                                            <h6 class="mb-1 font-weight-bold text-gray-800"><?php echo $notif['pesan']; ?></h6>
                                                            <p class="mb-1 text-gray-600">
                                                                <small>
                                                                    <i class="fas fa-user"></i> 
                                                                    Dari: <?php echo $notif['nama_user'] ?: 'System'; ?>
                                                                    <?php if ($notif['email']): ?>
                                                                        (<?php echo $notif['email']; ?>)
                                                                    <?php endif; ?>
                                                                </small>
                                                            </p>
                                                            <small class="text-muted">
                                                                <i class="fas fa-clock"></i> 
                                                                <?php echo date('d M Y H:i', strtotime($notif['created_at'])); ?>
                                                            </small>
                                                        </div>
                                                        <div class="dropdown no-arrow">
                                                            <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink-<?php echo $notif['id']; ?>" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                                                <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                                            </a>
                                                            <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink-<?php echo $notif['id']; ?>">
                                                                <?php if ($is_unread): ?>
                                                                    <a class="dropdown-item mark-read-btn" href="#" data-id="<?php echo $notif['id']; ?>">
                                                                        <i class="fas fa-check text-success"></i> Tandai Dibaca
                                                                    </a>
                                                                <?php endif; ?>
                                                                <a class="dropdown-item delete-notification-btn" href="#" data-id="<?php echo $notif['id']; ?>" data-message="<?php echo htmlspecialchars($notif['pesan']); ?>">
                                                                    <i class="fas fa-trash text-danger"></i> Hapus
                                                                </a>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-bell-slash fa-3x text-gray-300 mb-3"></i>
                                    <h5 class="text-gray-500">Tidak ada notifikasi</h5>
                                    <p class="text-gray-400">Semua notifikasi sudah dibaca atau belum ada notifikasi baru.</p>
                                </div>
                            <?php endif; ?>
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
                    timer: 4000,
                    showConfirmButton: true,
                    confirmButtonText: 'Mengerti',
                    confirmButtonColor: '#f6c23e'
                });
            <?php endif; ?>
        });

        // Mark all as read with SweetAlert confirmation
        $('#markAllReadBtn').on('click', function() {
            Swal.fire({
                title: 'Tandai Semua Dibaca?',
                text: 'Semua notifikasi yang belum dibaca akan ditandai sebagai sudah dibaca.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1cc88a',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Tandai Semua',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '?baca_semua=true';
                }
            });
        });

        // Mark single notification as read
        $('.mark-read-btn').on('click', function(e) {
            e.preventDefault();
            const notificationId = $(this).data('id');
            
            Swal.fire({
                title: 'Tandai Sebagai Dibaca?',
                text: 'Notifikasi ini akan ditandai sebagai sudah dibaca.',
                icon: 'info',
                showCancelButton: true,
                confirmButtonColor: '#1cc88a',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Tandai Dibaca',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '?baca=' + notificationId;
                }
            });
        });

        // Delete notification with SweetAlert confirmation
        $('.delete-notification-btn').on('click', function(e) {
            e.preventDefault();
            const notificationId = $(this).data('id');
            const message = $(this).data('message');
            
            Swal.fire({
                title: 'Hapus Notifikasi?',
                html: `Apakah Anda yakin ingin menghapus notifikasi:<br><strong>"${message}"</strong>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return new Promise((resolve) => {
                        setTimeout(() => {
                            window.location.href = '?hapus=' + notificationId;
                            resolve();
                        }, 1000);
                    });
                }
            });
        });

        // Delete all read notifications
        $('#deleteAllReadBtn').on('click', function(e) {
            e.preventDefault();
            
            Swal.fire({
                title: 'Hapus Semua Notifikasi yang Sudah Dibaca?',
                text: 'Tindakan ini tidak dapat dibatalkan! Semua notifikasi yang sudah dibaca akan dihapus permanen.',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus Semua!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return new Promise((resolve) => {
                        // This would need backend implementation for bulk delete
                        Swal.fire({
                            icon: 'info',
                            title: 'Fitur Segera Hadir',
                            text: 'Fitur hapus semua notifikasi akan segera tersedia.',
                            confirmButtonText: 'Mengerti',
                            confirmButtonColor: '#4e73df'
                        });
                        resolve(false);
                    });
                }
            });
        });

        // Filter functionality with SweetAlert feedback
        $('.filter-btn').on('click', function(e) {
            e.preventDefault();
            const filter = $(this).data('filter');
            
            let filterText = '';
            switch(filter) {
                case 'all':
                    filterText = 'Semua Notifikasi';
                    break;
                case 'unread':
                    filterText = 'Notifikasi Belum Dibaca';
                    break;
                case 'read':
                    filterText = 'Notifikasi Sudah Dibaca';
                    break;
            }
            
            Swal.fire({
                title: 'Memfilter Notifikasi',
                text: `Menampilkan ${filterText.toLowerCase()}...`,
                icon: 'info',
                timer: 1500,
                showConfirmButton: false
            });
            
            // Apply filter
            $('.notification-item').each(function() {
                const isUnread = $(this).data('status') === 'belum_dibaca';
                
                if (filter === 'all') {
                    $(this).show();
                } else if (filter === 'unread' && !isUnread) {
                    $(this).hide();
                } else if (filter === 'read' && isUnread) {
                    $(this).hide();
                } else {
                    $(this).show();
                }
            });
        });

        // Refresh notifications with loading
        function refreshNotifications() {
            Swal.fire({
                title: 'Memperbarui Notifikasi',
                text: 'Sedang memuat notifikasi terbaru...',
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

        // Auto refresh notifications every 30 seconds
        setInterval(() => {
            // Check if there are new notifications without reloading the page
            console.log('Checking for new notifications...');
        }, 30000);

        // Logout confirmation
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

        // Notification click handler
        $('.notification-item').on('click', function() {
            const notificationId = $(this).data('id');
            const isUnread = $(this).data('status') === 'belum_dibaca';
            
            if (isUnread) {
                // Mark as read when clicked
                $(this).removeClass('notification-unread').addClass('notification-read');
                $(this).find('.notification-icon')
                    .removeClass('notification-icon-unread')
                    .addClass('notification-icon-read');
                $(this).data('status', 'dibaca');
                
                // Update badge count
                const currentCount = parseInt($('.badge-counter').text());
                if (currentCount > 0) {
                    $('.badge-counter').text(currentCount - 1);
                }
                
                // Show quick feedback
                Swal.fire({
                    icon: 'success',
                    title: 'Ditandai Dibaca',
                    timer: 1500,
                    showConfirmButton: false,
                    position: 'top-end',
                    toast: true
                });
            }
        });
    </script>
</body>
</html>