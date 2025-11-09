<?php
session_start();
include 'includes/db.php';

// Check untuk logout message
$logout_message = '';
if (isset($_SESSION['logout_message'])) {
    $logout_message = $_SESSION['logout_message'];
    unset($_SESSION['logout_message']);
}

// Check untuk success message (registrasi berhasil)
$success_message = '';
if (isset($_SESSION['success'])) {
    $success_message = $_SESSION['success'];
    unset($_SESSION['success']);
}

// Check untuk login success message
$login_success = '';
if (isset($_SESSION['login_success'])) {
    $login_success = $_SESSION['login_success'];
    unset($_SESSION['login_success']);
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];

    // Show loading state
    $_SESSION['login_loading'] = true;

    // Check in admin table
    $admin_query = "SELECT * FROM admin WHERE email = '$email'";
    $admin_result = mysqli_query($conn, $admin_query);
    
    if (mysqli_num_rows($admin_result) == 1) {
        $admin = mysqli_fetch_assoc($admin_result);
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_nama'] = $admin['nama'];
            $_SESSION['admin_email'] = $admin['email'];
            $_SESSION['login_success'] = "Selamat datang, " . $admin['nama'] . "!";
            header('Location: admin/dashboard.php');
            exit();
        }
    }

    // Check in users table
    $user_query = "SELECT * FROM users WHERE email = '$email'";
    $user_result = mysqli_query($conn, $user_query);
    
    if (mysqli_num_rows($user_result) == 1) {
        $user = mysqli_fetch_assoc($user_result);
        if (password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_nama'] = $user['nama'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['login_success'] = "Selamat datang, " . $user['nama'] . "!";
            header('Location: user/dashboard.php');
            exit();
        }
    }

    $error_message = "Email atau password salah!";
    unset($_SESSION['login_loading']);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Kos Management</title>
    
    <!-- SB Admin 2 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/startbootstrap-sb-admin-2/4.1.4/css/sb-admin-2.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <!-- Custom CSS -->
    <style>
        .bg-login-image {
            background: url('https://source.unsplash.com/featured/?apartment,room') center center;
            background-size: cover;
            position: relative;
        }
        .bg-login-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(78, 115, 223, 0.8) 0%, rgba(34, 74, 190, 0.8) 100%);
        }
        .login-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1) !important;
        }
        .btn-login {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .btn-login:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(78, 115, 223, 0.4);
        }
        .btn-login:active {
            transform: translateY(0);
        }
        .form-control {
            transition: all 0.3s ease;
        }
        .form-control:focus {
            box-shadow: 0 0 0 0.2rem rgba(78, 115, 223, 0.25);
            border-color: #4e73df;
        }
        .floating-label {
            position: relative;
        }
        .floating-label label {
            position: absolute;
            top: 50%;
            left: 15px;
            transform: translateY(-50%);
            transition: all 0.3s ease;
            pointer-events: none;
            color: #6e707e;
            background: white;
            padding: 0 5px;
        }
        .floating-label input:focus + label,
        .floating-label input:not(:placeholder-shown) + label {
            top: 0;
            font-size: 12px;
            color: #4e73df;
            font-weight: 600;
        }
    </style>
</head>
<body class="bg-gradient-primary">
    <div class="container">
        <!-- Outer Row -->
        <div class="row justify-content-center">
            <div class="col-xl-10 col-lg-12 col-md-9">
                <div class="card o-hidden border-0 shadow-lg my-5 login-card">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="row">
                            <div class="col-lg-6 d-none d-lg-block bg-login-image">
                                <div class="position-relative h-100 d-flex align-items-center justify-content-center text-white">
                                    <div class="text-center p-5" style="z-index: 1;">
                                        <h2 class="font-weight-bold mb-3">Kos Management System</h2>
                                        <p class="mb-4">Sistem manajemen kos modern dan terintegrasi</p>
                                        <div class="row text-center">
                                            <div class="col-6">
                                                <i class="fas fa-bed fa-2x mb-2"></i>
                                                <p>Kamar Nyaman</p>
                                            </div>
                                            <div class="col-6">
                                                <i class="fas fa-shield-alt fa-2x mb-2"></i>
                                                <p>Aman & Terjamin</p>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-4">
                                            <i class="fas fa-home text-primary"></i> Kos Management
                                        </h1>
                                        <h2 class="h5 text-gray-900 mb-4">Selamat Datang Kembali!</h2>
                                        <p class="text-muted mb-4">Silakan login untuk mengakses sistem</p>
                                    </div>

                                    <form class="user" method="POST" action="" id="loginForm">
                                        <div class="form-group floating-label">
                                            <input type="email" class="form-control form-control-user" 
                                                id="email" name="email" required 
                                                placeholder=" "
                                                autocomplete="email">
                                            <label for="email">Email Address</label>
                                        </div>
                                        <div class="form-group floating-label">
                                            <input type="password" class="form-control form-control-user" 
                                                id="password" name="password" required 
                                                placeholder=" "
                                                autocomplete="current-password">
                                            <label for="password">Password</label>
                                        </div>
                                        <div class="form-group">
                                            <div class="custom-control custom-checkbox small">
                                                <input type="checkbox" class="custom-control-input" id="rememberMe">
                                                <label class="custom-control-label" for="rememberMe">
                                                    Ingat saya
                                                </label>
                                            </div>
                                        </div>
                                        <button type="submit" class="btn btn-primary btn-user btn-block btn-login" id="loginBtn">
                                            <span class="login-text">
                                                <i class="fas fa-sign-in-alt"></i> Login
                                            </span>
                                            <span class="loading-text" style="display: none;">
                                                <i class="fas fa-spinner fa-spin"></i> Memproses...
                                            </span>
                                        </button>
                                    </form>

                                    <hr>
                                    
                                    <div class="text-center">
                                        <a class="small" href="register.php">
                                            <i class="fas fa-user-plus"></i> Belum punya akun? Daftar di sini!
                                        </a>
                                    </div>

                                    <div class="text-center mt-4">
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle"></i> 
                                            Gunakan email dan password yang telah terdaftar
                                        </small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

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
            <?php if (!empty($logout_message)): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Logout Berhasil!',
                    text: '<?php echo $logout_message; ?>',
                    timer: 4000,
                    showConfirmButton: true,
                    confirmButtonText: 'Mengerti',
                    confirmButtonColor: '#1cc88a',
                    position: 'top-end',
                    toast: true
                });
            <?php endif; ?>

            <?php if (!empty($success_message)): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Registrasi Berhasil!',
                    text: '<?php echo $success_message; ?>',
                    timer: 5000,
                    showConfirmButton: true,
                    confirmButtonText: 'Login Sekarang',
                    confirmButtonColor: '#1cc88a'
                });
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                Swal.fire({
                    icon: 'error',
                    title: 'Login Gagal!',
                    html: `<?php echo $error_message; ?><br><small class="text-muted">Periksa kembali email dan password Anda</small>`,
                    timer: 5000,
                    showConfirmButton: true,
                    confirmButtonText: 'Coba Lagi',
                    confirmButtonColor: '#e74a3b'
                });
            <?php endif; ?>

            <?php if (!empty($login_success)): ?>
                Swal.fire({
                    icon: 'success',
                    title: 'Login Berhasil!',
                    text: '<?php echo $login_success; ?>',
                    timer: 3000,
                    showConfirmButton: false,
                    position: 'top-end',
                    toast: true
                });
            <?php endif; ?>

            // Focus on email field
            $('#email').focus();

            // Floating label functionality
            $('.floating-label input').on('focus blur', function() {
                $(this).siblings('label').toggleClass('active', this.value !== '' || this === document.activeElement);
            });

            // Trigger initial state
            $('.floating-label input').trigger('blur');
        });

        // Form submission with SweetAlert and loading state
        $('#loginForm').on('submit', function(e) {
            e.preventDefault();
            
            const email = $('#email').val().trim();
            const password = $('#password').val();
            
            // Validation
            if (!email || !password) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Data Tidak Lengkap',
                    text: 'Harap isi email dan password!',
                    confirmButtonText: 'Mengerti',
                    confirmButtonColor: '#f6c23e'
                });
                return;
            }

            // Email validation
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email)) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Format Email Salah',
                    text: 'Harap masukkan alamat email yang valid!',
                    confirmButtonText: 'Mengerti',
                    confirmButtonColor: '#f6c23e'
                });
                return;
            }

            // Show loading state
            const loginBtn = $('#loginBtn');
            const loginText = loginBtn.find('.login-text');
            const loadingText = loginBtn.find('.loading-text');
            
            loginText.hide();
            loadingText.show();
            loginBtn.prop('disabled', true);

            // Show processing modal
            Swal.fire({
                title: 'Memproses Login',
                text: 'Sedang memverifikasi data...',
                icon: 'info',
                showConfirmButton: false,
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                    
                    // Submit the form
                    setTimeout(() => {
                        this.submit();
                    }, 1500);
                }
            });
        });

        // Input animations
        $('.form-control').on('focus', function() {
            $(this).parent().addClass('focused');
        });

        $('.form-control').on('blur', function() {
            if (!this.value) {
                $(this).parent().removeClass('focused');
            }
        });

        // Enter key navigation
        $('.form-control').on('keypress', function(e) {
            if (e.which === 13) {
                e.preventDefault();
                const inputs = $('.form-control');
                const currentIndex = inputs.index(this);
                const nextInput = inputs.eq(currentIndex + 1);
                
                if (nextInput.length) {
                    nextInput.focus();
                } else {
                    $('#loginBtn').click();
                }
            }
        });
    </script>
</body>
</html>