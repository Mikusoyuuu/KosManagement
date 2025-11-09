<?php
include '../includes/auth.php';
redirectIfNotAdmin();

include '../includes/db.php';

// Variables untuk feedback messages
$success_message = '';
$error_message = '';

// Update payment status
if (isset($_GET['verifikasi'])) {
    $id = mysqli_real_escape_string($conn, $_GET['verifikasi']);
    $query = "UPDATE pembayaran SET status = 'terverifikasi' WHERE id = '$id'";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success_message'] = "Pembayaran berhasil diverifikasi!";
        header('Location: pembayaran.php');
        exit();
    } else {
        $_SESSION['error_message'] = "Gagal memverifikasi pembayaran!";
        header('Location: pembayaran.php');
        exit();
    }
}

if (isset($_GET['tolak'])) {
    $id = mysqli_real_escape_string($conn, $_GET['tolak']);
    $query = "UPDATE pembayaran SET status = 'gagal' WHERE id = '$id'";
    
    if (mysqli_query($conn, $query)) {
        $_SESSION['success_message'] = "Pembayaran berhasil ditolak!";
        header('Location: pembayaran.php');
        exit();
    } else {
        $_SESSION['error_message'] = "Gagal menolak pembayaran!";
        header('Location: pembayaran.php');
        exit();
    }
}

// Check for messages from session
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}

// Get all payments
$pembayaran_query = "
    SELECT pb.*, p.id_user, u.nama as nama_user, u.email, u.no_hp, k.nama_kamar, k.harga
    FROM pembayaran pb 
    JOIN pemesanan p ON pb.id_pemesanan = p.id 
    JOIN users u ON p.id_user = u.id 
    JOIN kamar k ON p.id_kamar = k.id 
    ORDER BY pb.tanggal_bayar DESC
";
$pembayaran_result = mysqli_query($conn, $pembayaran_query);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Pembayaran - Admin</title>
    
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
        .badge-menunggu { background-color: #f6c23e; color: #fff; }
        .badge-terverifikasi { background-color: #1cc88a; color: #fff; }
        .badge-gagal { background-color: #e74a3b; color: #fff; }
        .payment-image {
            max-width: 200px;
            max-height: 150px;
            cursor: pointer;
            border-radius: 4px;
            transition: transform 0.2s;
        }
        .payment-image:hover {
            transform: scale(1.05);
        }
        .action-btn {
            transition: all 0.3s ease;
        }
        .action-btn:hover {
            transform: translateY(-2px);
        }
        .table-card {
            transition: all 0.3s ease;
        }
        .table-card:hover {
            box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
        }
        .status-badge {
            font-size: 0.8rem;
            padding: 0.4em 0.8em;
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
            <li class="nav-item active">
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
                        <h1 class="h3 mb-0 text-gray-800">Verifikasi Pembayaran</h1>
                        <div class="d-flex">
                            <div class="dropdown mr-2">
                                <button class="btn btn-outline-primary dropdown-toggle action-btn" type="button" id="filterDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-filter"></i> Filter Status
                                </button>
                                <div class="dropdown-menu" aria-labelledby="filterDropdown">
                                    <a class="dropdown-item filter-option" href="#" data-filter="all">Semua</a>
                                    <a class="dropdown-item filter-option" href="#" data-filter="menunggu">Menunggu</a>
                                    <a class="dropdown-item filter-option" href="#" data-filter="terverifikasi">Terverifikasi</a>
                                    <a class="dropdown-item filter-option" href="#" data-filter="gagal">Gagal</a>
                                </div>
                            </div>
                            <button class="btn btn-primary action-btn" onclick="refreshPage()">
                                <i class="fas fa-sync-alt"></i> Refresh
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
                                                Total Pembayaran</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php 
                                                $total = mysqli_query($conn, "SELECT COUNT(*) as total FROM pembayaran")->fetch_assoc()['total'];
                                                echo $total;
                                                ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-money-bill-wave fa-2x text-gray-300"></i>
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
                                                Menunggu Verifikasi</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php 
                                                $menunggu = mysqli_query($conn, "SELECT COUNT(*) as total FROM pembayaran WHERE status = 'menunggu'")->fetch_assoc()['total'];
                                                echo $menunggu;
                                                ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clock fa-2x text-gray-300"></i>
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
                                                Terverifikasi</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php 
                                                $terverifikasi = mysqli_query($conn, "SELECT COUNT(*) as total FROM pembayaran WHERE status = 'terverifikasi'")->fetch_assoc()['total'];
                                                echo $terverifikasi;
                                                ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-check-circle fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-danger shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                                Ditolak</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php 
                                                $gagal = mysqli_query($conn, "SELECT COUNT(*) as total FROM pembayaran WHERE status = 'gagal'")->fetch_assoc()['total'];
                                                echo $gagal;
                                                ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-times-circle fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Payments List Card -->
                    <div class="card shadow mb-4 table-card">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-list"></i> Daftar Pembayaran
                            </h6>
                            <div class="dropdown no-arrow">
                                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in" aria-labelledby="dropdownMenuLink">
                                    <div class="dropdown-header">Ekspor Data:</div>
                                    <a class="dropdown-item" href="#" onclick="exportData()">
                                        <i class="fas fa-file-excel mr-2"></i>Export to Excel
                                    </a>
                                    <a class="dropdown-item" href="#" onclick="printTable()">
                                        <i class="fas fa-print mr-2"></i>Print
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>No</th>
                                            <th>Penyewa</th>
                                            <th>Kamar</th>
                                            <th>Jumlah</th>
                                            <th>Metode</th>
                                            <th>Tanggal Bayar</th>
                                            <th>Status</th>
                                            <th>Bukti Bayar</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $no = 1;
                                        while ($pembayaran = mysqli_fetch_assoc($pembayaran_result)) {
                                            $jumlah_format = 'Rp ' . number_format($pembayaran['jumlah'], 0, ',', '.');
                                            $tanggal_bayar = date('d M Y H:i', strtotime($pembayaran['tanggal_bayar']));
                                            
                                            $status_badge = '';
                                            switch ($pembayaran['status']) {
                                                case 'menunggu':
                                                    $status_badge = '<span class="badge badge-menunggu status-badge">Menunggu</span>';
                                                    break;
                                                case 'terverifikasi':
                                                    $status_badge = '<span class="badge badge-terverifikasi status-badge">Terverifikasi</span>';
                                                    break;
                                                case 'gagal':
                                                    $status_badge = '<span class="badge badge-gagal status-badge">Gagal</span>';
                                                    break;
                                            }
                                            
                                            echo "
                                            <tr data-status='{$pembayaran['status']}'>
                                                <td>$no</td>
                                                <td>
                                                    <div class='font-weight-bold'>{$pembayaran['nama_user']}</div>
                                                    <small class='text-muted'>{$pembayaran['email']}</small>
                                                    <br>
                                                    <small class='text-muted'>{$pembayaran['no_hp']}</small>
                                                </td>
                                                <td>
                                                    <div class='font-weight-bold'>{$pembayaran['nama_kamar']}</div>
                                                    <small class='text-muted'>Rp " . number_format($pembayaran['harga'], 0, ',', '.') . "/bulan</small>
                                                </td>
                                                <td class='font-weight-bold text-primary'>$jumlah_format</td>
                                                <td>
                                                    <span class='badge badge-info text-uppercase'>{$pembayaran['metode']}</span>
                                                </td>
                                                <td>
                                                    <div class='font-weight-bold'>$tanggal_bayar</div>
                                                </td>
                                                <td>$status_badge</td>
                                                <td>";
                                            
                                            if ($pembayaran['bukti_bayar']) {
                                                $bukti_path = "../assets/img/{$pembayaran['bukti_bayar']}";
                                                echo "
                                                <button type='button' 
                                                        class='btn btn-sm btn-outline-primary view-proof-btn action-btn'
                                                        data-image='$bukti_path' 
                                                        data-user='{$pembayaran['nama_user']}'
                                                        data-amount='$jumlah_format'
                                                        data-date='$tanggal_bayar'>
                                                    <i class='fas fa-eye'></i> Lihat Bukti
                                                </button>";
                                            } else {
                                                echo "<span class='text-muted'><i class='fas fa-times'></i> Tidak ada</span>";
                                            }
                                            
                                            echo "</td>
                                                <td>";
                                            
                                            if ($pembayaran['status'] == 'menunggu') {
                                                echo "
                                                <div class='btn-group-vertical btn-group-sm'>
                                                    <button type='button' 
                                                            class='btn btn-success btn-sm mb-1 action-btn verify-btn'
                                                            data-id='{$pembayaran['id']}'
                                                            data-user='{$pembayaran['nama_user']}'
                                                            data-amount='$jumlah_format'>
                                                        <i class='fas fa-check'></i> Verifikasi
                                                    </button>
                                                    <button type='button' 
                                                            class='btn btn-danger btn-sm action-btn reject-btn'
                                                            data-id='{$pembayaran['id']}'
                                                            data-user='{$pembayaran['nama_user']}'
                                                            data-amount='$jumlah_format'>
                                                        <i class='fas fa-times'></i> Tolak
                                                    </button>
                                                </div>";
                                            } else {
                                                echo "
                                                <button class='btn btn-outline-secondary btn-sm' disabled>
                                                    <i class='fas fa-check-double'></i> Selesai
                                                </button>";
                                            }
                                            
                                            echo "</td></tr>";
                                            $no++;
                                        }
                                        ?>
                                    </tbody>
                                </table>
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
                    title: 'Terjadi Kesalahan!',
                    text: '<?php echo $error_message; ?>',
                    timer: 5000,
                    showConfirmButton: true,
                    confirmButtonText: 'Mengerti',
                    confirmButtonColor: '#e74a3b'
                });
            <?php endif; ?>
        });

        // Verify payment with SweetAlert confirmation
        $('.verify-btn').on('click', function() {
            const paymentId = $(this).data('id');
            const userName = $(this).data('user');
            const amount = $(this).data('amount');
            
            Swal.fire({
                title: 'Verifikasi Pembayaran?',
                html: `Apakah Anda yakin ingin memverifikasi pembayaran dari <strong>${userName}</strong> sebesar <strong>${amount}</strong>?`,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#1cc88a',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Verifikasi!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return new Promise((resolve) => {
                        setTimeout(() => {
                            window.location.href = `?verifikasi=${paymentId}`;
                            resolve();
                        }, 1000);
                    });
                }
            });
        });

        // Reject payment with SweetAlert confirmation
        $('.reject-btn').on('click', function() {
            const paymentId = $(this).data('id');
            const userName = $(this).data('user');
            const amount = $(this).data('amount');
            
            Swal.fire({
                title: 'Tolak Pembayaran?',
                html: `Apakah Anda yakin ingin menolak pembayaran dari <strong>${userName}</strong> sebesar <strong>${amount}</strong>?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Tolak!',
                cancelButtonText: 'Batal',
                reverseButtons: true,
                showLoaderOnConfirm: true,
                preConfirm: () => {
                    return new Promise((resolve) => {
                        setTimeout(() => {
                            window.location.href = `?tolak=${paymentId}`;
                            resolve();
                        }, 1000);
                    });
                }
            });
        });

        // View payment proof with SweetAlert
        $('.view-proof-btn').on('click', function() {
            const imageUrl = $(this).data('image');
            const userName = $(this).data('user');
            const amount = $(this).data('amount');
            const date = $(this).data('date');
            
            Swal.fire({
                title: `Bukti Pembayaran - ${userName}`,
                html: `
                    <div class="text-left mb-3">
                        <p><strong>Nama:</strong> ${userName}</p>
                        <p><strong>Jumlah:</strong> ${amount}</p>
                        <p><strong>Tanggal:</strong> ${date}</p>
                    </div>
                    <img src="${imageUrl}" class="img-fluid payment-image" 
                         onerror="this.src='https://via.placeholder.com/400x300?text=Gambar+Tidak+Tersedia'"
                         style="max-height: 60vh; border-radius: 8px;">
                `,
                showCloseButton: true,
                showConfirmButton: true,
                confirmButtonText: 'Download',
                confirmButtonColor: '#4e73df',
                showCancelButton: true,
                cancelButtonText: 'Tutup',
                width: 'auto',
                padding: '2rem',
                didOpen: () => {
                    // Add download functionality
                    const confirmButton = Swal.getConfirmButton();
                    confirmButton.addEventListener('click', function() {
                        const link = document.createElement('a');
                        link.href = imageUrl;
                        link.download = `bukti-bayar-${userName}.jpg`;
                        link.click();
                    });
                }
            });
        });

        // Filter functionality with SweetAlert feedback
        $('.filter-option').on('click', function(e) {
            e.preventDefault();
            const filter = $(this).data('filter');
            
            let filterText = '';
            switch(filter) {
                case 'all':
                    filterText = 'Semua Pembayaran';
                    break;
                case 'menunggu':
                    filterText = 'Pembayaran Menunggu Verifikasi';
                    break;
                case 'terverifikasi':
                    filterText = 'Pembayaran Terverifikasi';
                    break;
                case 'gagal':
                    filterText = 'Pembayaran Ditolak';
                    break;
            }
            
            Swal.fire({
                title: 'Memfilter Data',
                text: `Menampilkan ${filterText.toLowerCase()}...`,
                icon: 'info',
                timer: 1500,
                showConfirmButton: false
            });
            
            // Apply filter
            $('#dataTable tbody tr').each(function() {
                const rowStatus = $(this).data('status');
                
                if (filter === 'all') {
                    $(this).show();
                } else if (rowStatus !== filter) {
                    $(this).hide();
                } else {
                    $(this).show();
                }
            });
        });

        // Refresh page function
        function refreshPage() {
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

        // Export data function
        function exportData() {
            Swal.fire({
                icon: 'info',
                title: 'Export Data',
                text: 'Fitur export data akan segera tersedia.',
                confirmButtonText: 'Mengerti',
                confirmButtonColor: '#4e73df'
            });
        }

        // Print table function
        function printTable() {
            Swal.fire({
                icon: 'info',
                title: 'Print Data',
                text: 'Fitur print akan segera tersedia.',
                confirmButtonText: 'Mengerti',
                confirmButtonColor: '#4e73df'
            });
        }

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

        // Auto refresh every 30 seconds to check for new payments
        setInterval(() => {
            // You can add real-time notification check here
            console.log('Checking for new payments...');
        }, 30000);

        // Row hover effects
        $('#dataTable tbody tr').on('mouseenter', function() {
            $(this).addClass('bg-light');
        }).on('mouseleave', function() {
            $(this).removeClass('bg-light');
        });

        // Quick status filter from URL parameters
        $(document).ready(function() {
            const urlParams = new URLSearchParams(window.location.search);
            const status = urlParams.get('status');
            
            if (status && status !== 'all') {
                // Trigger filter
                $(`.filter-option[data-filter="${status}"]`).trigger('click');
            }
        });
    </script>
</body>
</html>