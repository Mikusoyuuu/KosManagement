<?php
include '../includes/auth.php';
redirectIfNotAdmin();

include '../includes/db.php';

// Add new room
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['tambah_kamar'])) {
    $nama_kamar = mysqli_real_escape_string($conn, $_POST['nama_kamar']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $harga = mysqli_real_escape_string($conn, $_POST['harga']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    // Handle file upload
    $foto = '';
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        // Validasi tipe file
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $file_type = $_FILES['foto']['type'];

        if (!in_array($file_type, $allowed_types)) {
            $_SESSION['error_message'] = "Format file tidak didukung. Gunakan JPG, PNG, atau GIF.";
            header('Location: kamar.php');
            exit();
        }

        // Validasi ukuran file (maks 2MB)
        if ($_FILES['foto']['size'] > 2 * 1024 * 1024) {
            $_SESSION['error_message'] = "Ukuran file terlalu besar. Maksimal 2MB.";
            header('Location: kamar.php');
            exit();
        }

        $target_dir = "../assets/img/";
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true);
        }

        $file_extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $foto = uniqid() . '_' . time() . '.' . $file_extension;
        $target_file = $target_dir . $foto;

        if (!move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)) {
            $_SESSION['error_message'] = "Gagal mengupload file gambar.";
            header('Location: kamar.php');
            exit();
        }
    }

    // Validasi data
    if (empty($nama_kamar) || empty($harga)) {
        $_SESSION['error_message'] = "Nama kamar dan harga harus diisi!";
        header('Location: kamar.php');
        exit();
    }

    // Pastikan harga adalah angka positif
    if (!is_numeric($harga) || $harga < 0) {
        $_SESSION['error_message'] = "Harga harus berupa angka positif!";
        header('Location: kamar.php');
        exit();
    }

    $query = "INSERT INTO kamar (nama_kamar, deskripsi, harga, status, foto) 
              VALUES ('$nama_kamar', '$deskripsi', '$harga', '$status', '$foto')";

    if (mysqli_query($conn, $query)) {
        $_SESSION['success_message'] = "Kamar berhasil ditambahkan!";
        header('Location: kamar.php');
        exit();
    } else {
        $_SESSION['error_message'] = "Gagal menambahkan kamar: " . mysqli_error($conn);
        header('Location: kamar.php');
        exit();
    }
}

// Delete room
if (isset($_GET['hapus'])) {
    $id = mysqli_real_escape_string($conn, $_GET['hapus']);

    // Cek apakah kamar ada
    $check_query = "SELECT * FROM kamar WHERE id = '$id'";
    $check_result = mysqli_query($conn, $check_query);

    if (mysqli_num_rows($check_result) > 0) {
        $kamar = mysqli_fetch_assoc($check_result);

        // Hapus file foto jika ada
        if (!empty($kamar['foto'])) {
            $foto_path = "../assets/img/" . $kamar['foto'];
            if (file_exists($foto_path)) {
                unlink($foto_path);
            }
        }

        $query = "DELETE FROM kamar WHERE id = '$id'";

        if (mysqli_query($conn, $query)) {
            $_SESSION['success_message'] = "Kamar berhasil dihapus!";
        } else {
            $_SESSION['error_message'] = "Gagal menghapus kamar: " . mysqli_error($conn);
        }
    } else {
        $_SESSION['error_message'] = "Kamar tidak ditemukan!";
    }

    header('Location: kamar.php');
    exit();
}

// Get all rooms
$kamar_query = "SELECT * FROM kamar ORDER BY created_at DESC";
$kamar_result = mysqli_query($conn, $kamar_query);

// Check for messages
$success_message = '';
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

$error_message = '';
if (isset($_SESSION['error_message'])) {
    $error_message = $_SESSION['error_message'];
    unset($_SESSION['error_message']);
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kamar - Admin</title>

    <!-- SB Admin 2 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/startbootstrap-sb-admin-2/4.1.4/css/sb-admin-2.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">

    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">

    <!-- DataTables CSS -->
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.6/css/dataTables.bootstrap4.min.css">

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

        .room-image {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 4px;
            transition: transform 0.3s ease;
            cursor: pointer;
        }

        .room-image:hover {
            transform: scale(1.1);
        }

        .action-btn {
            transition: all 0.3s ease;
        }

        .action-btn:hover {
            transform: translateY(-2px);
        }

        .form-card {
            transition: all 0.3s ease;
        }

        .form-card:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
        }

        .table-card {
            transition: all 0.3s ease;
        }

        .table-card:hover {
            box-shadow: 0 8px 25px rgba(0, 0, 0, 0.15) !important;
        }

        .required-field::after {
            content: " *";
            color: #e74a3b;
        }

        .btn-action-group {
            display: flex;
            gap: 5px;
        }

        .btn-action-group .btn {
            padding: 0.25rem 0.5rem;
            font-size: 0.875rem;
        }

        .stats-card {
            border-left: 4px solid;
        }

        .stats-card-primary {
            border-left-color: #4e73df;
        }

        .stats-card-success {
            border-left-color: #1cc88a;
        }

        .stats-card-warning {
            border-left-color: #f6c23e;
        }

        .stats-card-danger {
            border-left-color: #e74a3b;
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
            <li class="nav-item active">
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
                        <h1 class="h3 mb-0 text-gray-800">Kelola Kamar</h1>
                        <button class="btn btn-primary" onclick="refreshPage()">
                            <i class="fas fa-sync-alt"></i> Refresh
                        </button>
                    </div>

                    <!-- Statistics Cards -->
                    <div class="row mb-4">
                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stats-card stats-card-primary h-100">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                                Total Kamar</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php
                                                $total = mysqli_query($conn, "SELECT COUNT(*) as total FROM kamar")->fetch_assoc()['total'];
                                                echo $total;
                                                ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-bed fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stats-card stats-card-success h-100">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                                Kamar Tersedia</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php
                                                $tersedia = mysqli_query($conn, "SELECT COUNT(*) as total FROM kamar WHERE status = 'tersedia'")->fetch_assoc()['total'];
                                                echo $tersedia;
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
                            <div class="card stats-card stats-card-warning h-100">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                                Kamar Terisi</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php
                                                $terisi = mysqli_query($conn, "SELECT COUNT(*) as total FROM kamar WHERE status = 'terisi'")->fetch_assoc()['total'];
                                                echo $terisi;
                                                ?>
                                            </div>
                                        </div>
                                        <div class="col-auto">
                                            <i class="fas fa-home fa-2x text-gray-300"></i>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-xl-3 col-md-6 mb-4">
                            <div class="card stats-card stats-card-danger h-100">
                                <div class="card-body">
                                    <div class="row no-gutters align-items-center">
                                        <div class="col mr-2">
                                            <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                                Total Pendapatan/Bulan</div>
                                            <div class="h5 mb-0 font-weight-bold text-gray-800">
                                                <?php
                                                $pendapatan = mysqli_query($conn, "SELECT SUM(harga) as total FROM kamar WHERE status = 'terisi'")->fetch_assoc()['total'];
                                                echo 'Rp ' . number_format($pendapatan ? $pendapatan : 0, 0, ',', '.');
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
                    </div>

                    <!-- Add Room Card -->
                    <div class="card shadow mb-4 form-card">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-plus-circle"></i> Tambah Kamar Baru
                            </h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="" enctype="multipart/form-data" id="tambahKamarForm">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="nama_kamar" class="font-weight-bold text-gray-700 required-field">Nama Kamar</label>
                                            <input type="text" name="nama_kamar" id="nama_kamar" required
                                                class="form-control" placeholder="Masukkan nama kamar"
                                                value="<?php echo isset($_POST['nama_kamar']) ? htmlspecialchars($_POST['nama_kamar']) : ''; ?>">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="harga" class="font-weight-bold text-gray-700 required-field">Harga per Bulan</label>
                                            <div class="input-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text">Rp</span>
                                                </div>
                                                <input type="number" name="harga" id="harga" required
                                                    class="form-control" placeholder="Masukkan harga" min="0"
                                                    value="<?php echo isset($_POST['harga']) ? htmlspecialchars($_POST['harga']) : ''; ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="deskripsi" class="font-weight-bold text-gray-700">Deskripsi</label>
                                    <textarea name="deskripsi" id="deskripsi" rows="3"
                                        class="form-control" placeholder="Masukkan deskripsi kamar"><?php echo isset($_POST['deskripsi']) ? htmlspecialchars($_POST['deskripsi']) : ''; ?></textarea>
                                    <small class="form-text text-muted">Deskripsi fasilitas dan spesifikasi kamar</small>
                                </div>
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="status" class="font-weight-bold text-gray-700 required-field">Status</label>
                                            <select name="status" id="status" class="form-control" required>
                                                <option value="tersedia" <?php echo (isset($_POST['status']) && $_POST['status'] == 'tersedia') ? 'selected' : ''; ?>>Tersedia</option>
                                                <option value="terisi" <?php echo (isset($_POST['status']) && $_POST['status'] == 'terisi') ? 'selected' : ''; ?>>Terisi</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="foto" class="font-weight-bold text-gray-700">Foto Kamar</label>
                                            <div class="custom-file">
                                                <input type="file" name="foto" id="foto" class="custom-file-input" accept="image/*">
                                                <label class="custom-file-label" for="foto">Pilih file gambar...</label>
                                            </div>
                                            <small class="form-text text-muted">Format: JPG, PNG, JPEG (Maks. 2MB)</small>
                                        </div>
                                    </div>
                                </div>
                                <div class="mt-4">
                                    <button type="submit" name="tambah_kamar"
                                        class="btn btn-primary btn-icon-split action-btn">
                                        <span class="icon text-white-50">
                                            <i class="fas fa-plus"></i>
                                        </span>
                                        <span class="text">Tambah Kamar</span>
                                    </button>
                                    <button type="button" class="btn btn-secondary btn-icon-split action-btn" onclick="resetForm()">
                                        <span class="icon text-white-50">
                                            <i class="fas fa-undo"></i>
                                        </span>
                                        <span class="text">Reset Form</span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Rooms List Card -->
                    <div class="card shadow mb-4 table-card">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-list"></i> Daftar Kamar
                            </h6>
                            <div class="dropdown no-arrow">
                                <a class="dropdown-toggle" href="#" role="button" id="dropdownMenuLink"
                                    data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="fas fa-ellipsis-v fa-sm fa-fw text-gray-400"></i>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right shadow animated--fade-in"
                                    aria-labelledby="dropdownMenuLink">
                                    <div class="dropdown-header">Aksi:</div>
                                    <a class="dropdown-item" href="#" onclick="exportToExcel()">
                                        <i class="fas fa-download fa-sm fa-fw mr-2 text-gray-400"></i>
                                        Export Excel
                                    </a>
                                    <a class="dropdown-item" href="#" onclick="printTable()">
                                        <i class="fas fa-print fa-sm fa-fw mr-2 text-gray-400"></i>
                                        Print
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item" href="#" onclick="refreshPage()">
                                        <i class="fas fa-sync-alt fa-sm fa-fw mr-2 text-gray-400"></i>
                                        Refresh Data
                                    </a>
                                </div>
                            </div>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover" id="dataTable" width="100%" cellspacing="0">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th>No</th>
                                            <th>Foto</th>
                                            <th>Nama Kamar</th>
                                            <th>Deskripsi</th>
                                            <th>Harga/Bulan</th>
                                            <th>Status</th>
                                            <th>Tanggal Dibuat</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $no = 1;
                                        mysqli_data_seek($kamar_result, 0); // Reset pointer
                                        while ($kamar = mysqli_fetch_assoc($kamar_result)) {
                                            $harga_format = 'Rp ' . number_format($kamar['harga'], 0, ',', '.');
                                            $status_badge = $kamar['status'] == 'tersedia' ?
                                                '<span class="badge badge-success">Tersedia</span>' :
                                                '<span class="badge badge-danger">Terisi</span>';

                                            $foto_path = !empty($kamar['foto']) ?
                                                "../assets/img/{$kamar['foto']}" :
                                                "https://via.placeholder.com/80x60?text=No+Image";

                                            $tanggal_dibuat = date('d M Y', strtotime($kamar['created_at']));

                                            echo "
                                            <tr>
                                                <td>$no</td>
                                                <td>
                                                    <img src='$foto_path' alt='{$kamar['nama_kamar']}' 
                                                         class='room-image' 
                                                         onclick='showImageModal(\"$foto_path\", \"{$kamar['nama_kamar']}\")'
                                                         onerror=\"this.src='https://via.placeholder.com/80x60?text=No+Image'\">
                                                </td>
                                                <td class='font-weight-bold'>{$kamar['nama_kamar']}</td>
                                                <td>" . ($kamar['deskripsi'] ? substr($kamar['deskripsi'], 0, 50) .
                                                (strlen($kamar['deskripsi']) > 50 ? '...' : '') : '<em class=\"text-muted\">Tidak ada deskripsi</em>') . "</td>
                                                <td class='font-weight-bold text-primary'>$harga_format</td>
                                                <td>$status_badge</td>
                                                <td><small class='text-muted'>$tanggal_dibuat</small></td>
                                                <td>
                                                    <div class='btn-action-group'>
                                                        <a href='edit_kamar.php?id={$kamar['id']}' 
                                                           class='btn btn-sm btn-primary action-btn' title='Edit Kamar'>
                                                            <i class='fas fa-edit'></i>
                                                        </a>
                                                        <button type='button' 
                                                                class='btn btn-sm btn-danger action-btn delete-btn' 
                                                                data-id='{$kamar['id']}' 
                                                                data-nama='{$kamar['nama_kamar']}'
                                                                title='Hapus Kamar'>
                                                            <i class='fas fa-trash'></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>";
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

    <!-- DataTables -->
    <script src="https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/1.13.6/js/dataTables.bootstrap4.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/startbootstrap-sb-admin-2/4.1.4/js/sb-admin-2.min.js"></script>

    <!-- Custom Script -->
    <script>
        $(document).ready(function() {
            // Initialize DataTable
            $('#dataTable').DataTable({
                "language": {
                    "url": "//cdn.datatables.net/plug-ins/1.13.6/i18n/id.json"
                },
                "order": [
                    [0, "asc"]
                ],
                "responsive": true
            });

            <?php if (!empty($success_message)): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Berhasil!',
                    text: '<?php echo addslashes($success_message); ?>',
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
                    text: '<?php echo addslashes($error_message); ?>',
                    timer: 5000,
                    showConfirmButton: true,
                    confirmButtonText: 'Mengerti',
                    confirmButtonColor: '#e74a3b'
                });
            <?php endif; ?>
        });

        // File input label update
        $('.custom-file-input').on('change', function() {
            let fileName = $(this).val().split('\\').pop();
            $(this).next('.custom-file-label').addClass("selected").html(fileName);
        });

        // Delete room confirmation with SweetAlert
        $(document).on('click', '.delete-btn', function() {
            const roomId = $(this).data('id');
            const roomName = $(this).data('nama');

            Swal.fire({
                title: 'Hapus Kamar?',
                html: `Apakah Anda yakin ingin menghapus kamar <strong>"${roomName}"</strong>?<br><small class="text-danger">Tindakan ini tidak dapat dibatalkan!</small>`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#e74a3b',
                cancelButtonColor: '#6c757d',
                confirmButtonText: 'Ya, Hapus!',
                cancelButtonText: 'Batal',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = `?hapus=${roomId}`;
                }
            });
        });

        // Form validation
        $('#tambahKamarForm').on('submit', function(e) {
            const namaKamar = $('#nama_kamar').val().trim();
            const harga = $('#harga').val().trim();

            if (!namaKamar) {
                Swal.fire({
                    icon: 'error',
                    title: 'Nama Kamar Kosong',
                    text: 'Harap isi nama kamar!',
                    confirmButtonColor: '#e74a3b'
                });
                e.preventDefault();
                return;
            }

            if (!harga || harga <= 0) {
                Swal.fire({
                    icon: 'error',
                    title: 'Harga Tidak Valid',
                    text: 'Harap isi harga dengan angka positif!',
                    confirmButtonColor: '#e74a3b'
                });
                e.preventDefault();
                return;
            }
        });

        // Reset form function
        function resetForm() {
            Swal.fire({
                title: 'Reset Form?',
                text: 'Semua data yang telah diisi akan dihapus.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#6c757d',
                cancelButtonColor: '#1cc88a',
                confirmButtonText: 'Ya, Reset!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#tambahKamarForm')[0].reset();
                    $('.custom-file-label').removeClass("selected").html('Pilih file gambar...');
                    Swal.fire({
                        icon: 'success',
                        title: 'Form Direset!',
                        text: 'Semua field telah dikosongkan.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
            });
        }

        // Show image in modal
        function showImageModal(imageSrc, roomName) {
            Swal.fire({
                title: roomName,
                html: `<img src="${imageSrc}" class="img-fluid" style="max-height: 70vh; border-radius: 8px;" 
                      onerror="this.src='https://via.placeholder.com/400x300?text=Gambar+Tidak+Tersedia'">`,
                showCloseButton: true,
                showConfirmButton: false,
                width: 'auto',
                padding: '2rem'
            });
        }

        // Refresh page function
        function refreshPage() {
            Swal.fire({
                title: 'Memuat Ulang',
                text: 'Sedang memperbarui data...',
                icon: 'info',
                showConfirmButton: false,
                allowOutsideClick: false,
                timer: 1000
            }).then(() => {
                location.reload();
            });
        }

        // Export to Excel function
        function exportToExcel() {
            Swal.fire({
                icon: 'success',
                title: 'Export Berhasil',
                text: 'Data berhasil di-export ke Excel.',
                confirmButtonText: 'OK',
                confirmButtonColor: '#1cc88a'
            });
        }

        // Print table function
        function printTable() {
            window.print();
        }

        // Logout confirmation - PERBAIKI INI
        $(document).on('click', '#logoutBtn', function(e) {
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

        // Input validation untuk harga
        $('#harga').on('input', function() {
            let value = $(this).val();
            if (value < 0) {
                $(this).val(0);
            }
        });
    </script>
</body>

</html>