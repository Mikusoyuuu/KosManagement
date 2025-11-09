<?php
include '../includes/auth.php';
redirectIfNotAdmin();

include '../includes/db.php';

// Initialize variables
$success_message = '';
$error_message = '';
$kamar = null;

// Get room ID from URL
if (!isset($_GET['id']) || empty($_GET['id'])) {
    $_SESSION['error_message'] = "ID kamar tidak valid!";
    header('Location: kamar.php');
    exit();
}

$id = mysqli_real_escape_string($conn, $_GET['id']);

// Get room data
$query = "SELECT * FROM kamar WHERE id = '$id'";
$result = mysqli_query($conn, $query);

if (mysqli_num_rows($result) == 0) {
    $_SESSION['error_message'] = "Kamar tidak ditemukan!";
    header('Location: kamar.php');
    exit();
}

$kamar = mysqli_fetch_assoc($result);

// Update room data
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_kamar'])) {
    $nama_kamar = mysqli_real_escape_string($conn, $_POST['nama_kamar']);
    $deskripsi = mysqli_real_escape_string($conn, $_POST['deskripsi']);
    $harga = mysqli_real_escape_string($conn, $_POST['harga']);
    $status = mysqli_real_escape_string($conn, $_POST['status']);

    // Handle file upload
    $foto = $kamar['foto']; // Keep existing photo by default
    
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        // Validasi tipe file
        $allowed_types = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif'];
        $file_type = $_FILES['foto']['type'];
        
        if (!in_array($file_type, $allowed_types)) {
            $error_message = "Format file tidak didukung. Gunakan JPG, PNG, atau GIF.";
        } elseif ($_FILES['foto']['size'] > 2 * 1024 * 1024) {
            $error_message = "Ukuran file terlalu besar. Maksimal 2MB.";
        } else {
            $target_dir = "../assets/img/";
            if (!is_dir($target_dir)) {
                mkdir($target_dir, 0777, true);
            }
            
            // Delete old photo if exists
            if (!empty($kamar['foto'])) {
                $old_foto_path = $target_dir . $kamar['foto'];
                if (file_exists($old_foto_path)) {
                    unlink($old_foto_path);
                }
            }
            
            $file_extension = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
            $foto = uniqid() . '_' . time() . '.' . $file_extension;
            $target_file = $target_dir . $foto;
            
            if (!move_uploaded_file($_FILES['foto']['tmp_name'], $target_file)) {
                $error_message = "Gagal mengupload file gambar.";
            }
        }
    }

    // Validasi data
    if (empty($nama_kamar) || empty($harga)) {
        $error_message = "Nama kamar dan harga harus diisi!";
    } elseif (!is_numeric($harga) || $harga < 0) {
        $error_message = "Harga harus berupa angka positif!";
    }

    // If no errors, update data
    if (empty($error_message)) {
        $query = "UPDATE kamar SET 
                  nama_kamar = '$nama_kamar', 
                  deskripsi = '$deskripsi', 
                  harga = '$harga', 
                  status = '$status', 
                  foto = '$foto' 
                  WHERE id = '$id'";
        
        if (mysqli_query($conn, $query)) {
            $_SESSION['success_message'] = "Kamar berhasil diperbarui!";
            header('Location: kamar.php');
            exit();
        } else {
            $error_message = "Gagal memperbarui kamar: " . mysqli_error($conn);
        }
    }
}

// Check for session messages
if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}

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
    <title>Edit Kamar - Admin</title>
    
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
            width: 150px;
            height: 120px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #e3e6f0;
        }
        .room-image-preview {
            max-width: 200px;
            max-height: 150px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #e3e6f0;
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
            box-shadow: 0 8px 25px rgba(0,0,0,0.15) !important;
        }
        .required-field::after {
            content: " *";
            color: #e74a3b;
        }
        .current-photo {
            background: #f8f9fc;
            border-radius: 8px;
            padding: 15px;
            border: 1px solid #e3e6f0;
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
                        <h1 class="h3 mb-0 text-gray-800">Edit Kamar</h1>
                        <a href="kamar.php" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Kembali ke Daftar Kamar
                        </a>
                    </div>

                    <!-- Edit Room Card -->
                    <div class="card shadow mb-4 form-card">
                        <div class="card-header py-3">
                            <h6 class="m-0 font-weight-bold text-primary">
                                <i class="fas fa-edit"></i> Edit Data Kamar
                            </h6>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="" enctype="multipart/form-data" id="editKamarForm">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="nama_kamar" class="font-weight-bold text-gray-700 required-field">Nama Kamar</label>
                                            <input type="text" name="nama_kamar" id="nama_kamar" required 
                                                class="form-control" placeholder="Masukkan nama kamar"
                                                value="<?php echo htmlspecialchars($kamar['nama_kamar']); ?>">
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
                                                    value="<?php echo htmlspecialchars($kamar['harga']); ?>">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <div class="form-group">
                                    <label for="deskripsi" class="font-weight-bold text-gray-700">Deskripsi</label>
                                    <textarea name="deskripsi" id="deskripsi" rows="4"
                                        class="form-control" placeholder="Masukkan deskripsi kamar"><?php echo htmlspecialchars($kamar['deskripsi']); ?></textarea>
                                    <small class="form-text text-muted">Deskripsi fasilitas dan spesifikasi kamar</small>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="status" class="font-weight-bold text-gray-700 required-field">Status</label>
                                            <select name="status" id="status" class="form-control" required>
                                                <option value="tersedia" <?php echo $kamar['status'] == 'tersedia' ? 'selected' : ''; ?>>Tersedia</option>
                                                <option value="terisi" <?php echo $kamar['status'] == 'terisi' ? 'selected' : ''; ?>>Terisi</option>
                                            </select>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="foto" class="font-weight-bold text-gray-700">Foto Kamar</label>
                                            <div class="custom-file">
                                                <input type="file" name="foto" id="foto" class="custom-file-input" accept="image/*">
                                                <label class="custom-file-label" for="foto">Pilih file gambar baru...</label>
                                            </div>
                                            <small class="form-text text-muted">Format: JPG, PNG, JPEG (Maks. 2MB). Kosongkan jika tidak ingin mengubah foto.</small>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Current Photo Preview -->
                                <div class="form-group">
                                    <label class="font-weight-bold text-gray-700">Foto Saat Ini</label>
                                    <div class="current-photo">
                                        <?php if (!empty($kamar['foto'])): ?>
                                            <?php
                                            $foto_path = "../assets/img/{$kamar['foto']}";
                                            $foto_exists = file_exists($foto_path);
                                            ?>
                                            <img src="<?php echo $foto_exists ? $foto_path : 'https://via.placeholder.com/200x150?text=Foto+Tidak+Ditemukan'; ?>" 
                                                 alt="<?php echo htmlspecialchars($kamar['nama_kamar']); ?>" 
                                                 class="room-image-preview mb-2"
                                                 onerror="this.src='https://via.placeholder.com/200x150?text=Foto+Tidak+Ditemukan'">
                                            <div>
                                                <small class="text-muted"><?php echo $kamar['foto']; ?></small>
                                            </div>
                                        <?php else: ?>
                                            <div class="text-center py-3">
                                                <i class="fas fa-image fa-3x text-gray-300 mb-2"></i>
                                                <p class="text-muted mb-0">Tidak ada foto</p>
                                            </div>
                                        <?php endif; ?>
                                    </div>
                                </div>
                                
                                <div class="mt-4">
                                    <button type="submit" name="update_kamar" 
                                        class="btn btn-success btn-icon-split action-btn">
                                        <span class="icon text-white-50">
                                            <i class="fas fa-save"></i>
                                        </span>
                                        <span class="text">Update Kamar</span>
                                    </button>
                                    <button type="button" class="btn btn-secondary btn-icon-split action-btn" onclick="resetForm()">
                                        <span class="icon text-white-50">
                                            <i class="fas fa-undo"></i>
                                        </span>
                                        <span class="text">Reset Form</span>
                                    </button>
                                    <a href="kamar.php" class="btn btn-light btn-icon-split action-btn">
                                        <span class="icon text-gray-600">
                                            <i class="fas fa-times"></i>
                                        </span>
                                        <span class="text">Batal</span>
                                    </a>
                                </div>
                            </form>
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
        $(document).ready(function() {
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
            if (fileName) {
                $(this).next('.custom-file-label').addClass("selected").html(fileName);
            } else {
                $(this).next('.custom-file-label').removeClass("selected").html('Pilih file gambar baru...');
            }
        });

        // Form validation
        $('#editKamarForm').on('submit', function(e) {
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
                text: 'Semua perubahan yang belum disimpan akan hilang.',
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#6c757d',
                cancelButtonColor: '#1cc88a',
                confirmButtonText: 'Ya, Reset!',
                cancelButtonText: 'Batal'
            }).then((result) => {
                if (result.isConfirmed) {
                    // Reset form to original values
                    document.getElementById('editKamarForm').reset();
                    $('.custom-file-label').removeClass("selected").html('Pilih file gambar baru...');
                    Swal.fire({
                        icon: 'success',
                        title: 'Form Direset!',
                        text: 'Semua perubahan telah dibatalkan.',
                        timer: 2000,
                        showConfirmButton: false
                    });
                }
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

        // Input validation untuk harga
        $('#harga').on('input', function() {
            let value = $(this).val();
            if (value < 0) {
                $(this).val(0);
            }
        });

        // Preview image before upload
        $('#foto').on('change', function() {
            const file = this.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    Swal.fire({
                        title: 'Preview Gambar Baru',
                        html: `<img src="${e.target.result}" class="img-fluid" style="max-height: 300px; border-radius: 8px;">`,
                        showCancelButton: true,
                        confirmButtonText: 'Gunakan Gambar Ini',
                        cancelButtonText: 'Pilih Ulang',
                        showCloseButton: true,
                        confirmButtonColor: '#1cc88a'
                    }).then((result) => {
                        if (result.dismiss === Swal.DismissReason.cancel) {
                            $('#foto').val('');
                            $('.custom-file-label').removeClass("selected").html('Pilih file gambar baru...');
                        }
                    });
                }
                reader.readAsDataURL(file);
            }
        });
    </script>
</body>
</html>