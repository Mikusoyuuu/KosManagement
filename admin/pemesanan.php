<?php
include '../includes/auth.php';
redirectIfNotAdmin();

include '../includes/db.php';

// Update booking status
if (isset($_GET['setujui'])) {
    $id = mysqli_real_escape_string($conn, $_GET['setujui']);
    $query = "UPDATE pemesanan SET status = 'diterima' WHERE id = '$id'";

    if (mysqli_query($conn, $query)) {
        $success = "Pemesanan berhasil disetujui!";

        // Update room status
        $pemesanan = mysqli_query($conn, "SELECT id_kamar FROM pemesanan WHERE id = '$id'")->fetch_assoc();
        mysqli_query($conn, "UPDATE kamar SET status = 'terisi' WHERE id = '{$pemesanan['id_kamar']}'");
    }
}

if (isset($_GET['tolak'])) {
    $id = mysqli_real_escape_string($conn, $_GET['tolak']);
    $query = "UPDATE pemesanan SET status = 'ditolak' WHERE id = '$id'";

    if (mysqli_query($conn, $query)) {
        $success = "Pemesanan berhasil ditolak!";
    }
}

// Get all bookings - PERBAIKI QUERY INI
$pemesanan_query = "
    SELECT p.*, u.nama as nama_user, u.email, u.no_hp, k.nama_kamar, k.harga, k.foto
    FROM pemesanan p 
    JOIN users u ON p.id_user = u.id 
    JOIN kamar k ON p.id_kamar = k.id 
    ORDER BY p.created_at DESC
";
$pemesanan_result = mysqli_query($conn, $pemesanan_query);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pemesanan - Admin</title>

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

        .room-image {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
        }

        .badge-menunggu {
            background-color: #f6c23e;
            color: #fff;
        }

        .badge-diterima {
            background-color: #1cc88a;
            color: #fff;
        }

        .badge-ditolak {
            background-color: #e74a3b;
            color: #fff;
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
            <li class="nav-item active">
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
                                <!-- PERBAIKAN: Ganti href dan tambahkan id="logoutBtn" -->
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
                        <h1 class="h3 mb-0 text-gray-800">Kelola Pemesanan</h1>
                        <div class="d-flex">
                            <div class="dropdown mr-2">
                                <button class="btn btn-outline-primary dropdown-toggle" type="button" id="filterDropdown" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-filter"></i> Filter Status
                                </button>
                                <div class="dropdown-menu" aria-labelledby="filterDropdown">
                                    <a class="dropdown-item" href="?status=all">Semua</a>
                                    <a class="dropdown-item" href="?status=menunggu">Menunggu</a>
                                    <a class="dropdown-item" href="?status=diterima">Diterima</a>
                                    <a class="dropdown-item" href="?status=ditolak">Ditolak</a>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Alert Messages -->
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i> <?php echo $success; ?>
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    <?php endif; ?>

                    <!-- Bookings List Card -->
                    <div class="card shadow mb-4">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-list"></i> Daftar Pemesanan
                            </h6>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered" id="dataTable" width="100%" cellspacing="0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>No</th>
                                            <th>Kamar</th>
                                            <th>Penyewa</th>
                                            <th>Kontak</th>
                                            <th>Tanggal Masuk</th>
                                            <th>Durasi</th>
                                            <th>Total</th>
                                            <th>Status</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $no = 1;
                                        mysqli_data_seek($pemesanan_result, 0); // Reset pointer
                                        while ($pemesanan = mysqli_fetch_assoc($pemesanan_result)) {
                                            // PERBAIKAN: Handle null dates
                                            $tanggal_masuk = !empty($pemesanan['tanggal_masuk']) ?
                                                date('d M Y', strtotime($pemesanan['tanggal_masuk'])) : 'Belum ditentukan';
                                            $tanggal_keluar = !empty($pemesanan['tanggal_keluar']) ?
                                                date('d M Y', strtotime($pemesanan['tanggal_keluar'])) : 'Belum ditentukan';

                                            // PERBAIKAN: Handle durasi dengan nilai default
                                            $durasi = isset($pemesanan['durasi']) && $pemesanan['durasi'] !== null ?
                                                $pemesanan['durasi'] . ' bulan' : '0 bulan';

                                            // PERBAIKAN: Handle total harga dengan nilai default
                                            $harga_kamar = $pemesanan['harga'] ?? 0;
                                            $durasi_num = $pemesanan['durasi'] ?? 0;
                                            $total_harga = 'Rp ' . number_format($harga_kamar * $durasi_num, 0, ',', '.');

                                            $foto_path = !empty($pemesanan['foto']) ?
                                                "../assets/img/{$pemesanan['foto']}" :
                                                "https://via.placeholder.com/60x60?text=No+Image";

                                            $status_badge = '';
                                            switch ($pemesanan['status']) {
                                                case 'menunggu':
                                                    $status_badge = '<span class="badge badge-menunggu">Menunggu</span>';
                                                    break;
                                                case 'diterima':
                                                    $status_badge = '<span class="badge badge-diterima">Diterima</span>';
                                                    break;
                                                case 'ditolak':
                                                    $status_badge = '<span class="badge badge-ditolak">Ditolak</span>';
                                                    break;
                                                default:
                                                    $status_badge = '<span class="badge badge-secondary">Unknown</span>';
                                            }

                                            // PERBAIKAN: Handle telepon dengan nilai default
                                            $telepon = isset($pemesanan['telepon']) ? $pemesanan['telepon'] : (isset($pemesanan['no_hp']) ? $pemesanan['no_hp'] : 'Tidak ada');

                                            echo "
                                            <tr>
                                                <td>$no</td>
                                                <td>
                                                    <div class='d-flex align-items-center'>
                                                        <img src='$foto_path' alt='{$pemesanan['nama_kamar']}' 
                                                             class='room-image mr-2' onerror=\"this.src='https://via.placeholder.com/60x60?text=No+Image'\">
                                                        <div>
                                                            <div class='font-weight-bold'>{$pemesanan['nama_kamar']}</div>
                                                            <small class='text-muted'>Rp " . number_format($pemesanan['harga'] ?? 0, 0, ',', '.') . "/bulan</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class='font-weight-bold'>{$pemesanan['nama_user']}</div>
                                                    <small class='text-muted'>ID: {$pemesanan['id_user']}</small>
                                                </td>
                                                <td>
                                                    <div><small>{$pemesanan['email']}</small></div>
                                                    <div><small>$telepon</small></div>
                                                </td>
                                                <td>
                                                    <div class='font-weight-bold'>$tanggal_masuk</div>
                                                    <small class='text-muted'>sampai $tanggal_keluar</small>
                                                </td>
                                                <td class='text-center'>
                                                    <span class='badge badge-primary'>$durasi</span>
                                                </td>
                                                <td class='font-weight-bold text-primary'>$total_harga</td>
                                                <td>$status_badge</td>
                                                <td>";

                                            if ($pemesanan['status'] == 'menunggu') {
                                                echo "
                                                <div class='btn-group-vertical btn-group-sm'>
                                                    <a href='?setujui={$pemesanan['id']}' 
                                                       class='btn btn-success btn-sm mb-1 setujui-btn'
                                                       data-id='{$pemesanan['id']}'
                                                       data-nama='{$pemesanan['nama_user']}'
                                                       data-kamar='{$pemesanan['nama_kamar']}'>
                                                        <i class='fas fa-check'></i> Setujui
                                                    </a>
                                                    <a href='?tolak={$pemesanan['id']}' 
                                                       class='btn btn-danger btn-sm tolak-btn'
                                                       data-id='{$pemesanan['id']}'
                                                       data-nama='{$pemesanan['nama_user']}'
                                                       data-kamar='{$pemesanan['nama_kamar']}'>
                                                        <i class='fas fa-times'></i> Tolak
                                                    </a>
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

                                        // PERBAIKAN: Handle jika tidak ada data
                                        if ($no == 1) {
                                            echo "
                                            <tr>
                                                <td colspan='9' class='text-center py-4'>
                                                    <i class='fas fa-inbox fa-3x text-gray-300 mb-3'></i>
                                                    <h5 class='text-gray-500'>Belum ada pemesanan</h5>
                                                    <p class='text-muted'>Semua pemesanan yang masuk akan muncul di sini</p>
                                                </td>
                                            </tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card border-left-primary shadow h-100 py-2">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Total Pemesanan</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php
                                                $total = mysqli_query($conn, "SELECT COUNT(*) as total FROM pemesanan")->fetch_assoc()['total'];
                                                echo $total;
                                                ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-clipboard-list fa-2x text-gray-300"></i>
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
                                                Menunggu Konfirmasi</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php
                                                $menunggu = mysqli_query($conn, "SELECT COUNT(*) as total FROM pemesanan WHERE status = 'menunggu'")->fetch_assoc()['total'];
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
                                                Diterima</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php
                                                $diterima = mysqli_query($conn, "SELECT COUNT(*) as total FROM pemesanan WHERE status = 'diterima'")->fetch_assoc()['total'];
                                                echo $diterima;
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
                                            <div class="h5 mb-0 font-weight-bold text-gray-300">
                                                <?php
                                                $ditolak = mysqli_query($conn, "SELECT COUNT(*) as total FROM pemesanan WHERE status = 'ditolak'")->fetch_assoc()['total'];
                                                echo $ditolak;
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

    <!-- SweetAlert2 JS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/startbootstrap-sb-admin-2/4.1.4/js/sb-admin-2.min.js"></script>

    <!-- Custom Script -->
    <script>
        $(document).ready(function() {
            // Auto-dismiss alerts after 5 seconds
            setTimeout(function() {
                $('.alert').alert('close');
            }, 5000);

            // SweetAlert for approval
            $(document).on('click', '.setujui-btn', function(e) {
                e.preventDefault();

                const id = $(this).data('id');
                const nama = $(this).data('nama');
                const kamar = $(this).data('kamar');
                const url = $(this).attr('href');

                Swal.fire({
                    title: 'Setujui Pemesanan?',
                    html: `Apakah Anda yakin ingin menyetujui pemesanan dari <strong>${nama}</strong> untuk kamar <strong>${kamar}</strong>?`,
                    icon: 'question',
                    showCancelButton: true,
                    confirmButtonColor: '#1cc88a',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Setujui!',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = url;
                    }
                });
            });

            // SweetAlert for rejection
            $(document).on('click', '.tolak-btn', function(e) {
                e.preventDefault();

                const id = $(this).data('id');
                const nama = $(this).data('nama');
                const kamar = $(this).data('kamar');
                const url = $(this).attr('href');

                Swal.fire({
                    title: 'Tolak Pemesanan?',
                    html: `Apakah Anda yakin ingin menolak pemesanan dari <strong>${nama}</strong> untuk kamar <strong>${kamar}</strong>?`,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#e74a3b',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Tolak!',
                    cancelButtonText: 'Batal',
                    reverseButtons: true
                }).then((result) => {
                    if (result.isConfirmed) {
                        window.location.href = url;
                    }
                });
            });

            // Logout confirmation - PERBAIKAN: Pindah ke dalam document ready
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
        });
    </script>
</body>

</html>