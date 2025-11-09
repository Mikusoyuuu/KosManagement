<?php
session_start();
include 'includes/db.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = mysqli_real_escape_string($conn, $_POST['nama']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    $no_hp = mysqli_real_escape_string($conn, $_POST['no_hp']);
    $alamat = mysqli_real_escape_string($conn, $_POST['alamat']);

    // Validation flags
    $is_valid = true;
    $error_messages = [];

    // Check if passwords match
    if ($password !== $confirm_password) {
        $is_valid = false;
        $error_messages[] = "Password dan konfirmasi password tidak sama!";
    }

    // Check password strength
    if (strlen($password) < 6) {
        $is_valid = false;
        $error_messages[] = "Password harus minimal 6 karakter!";
    }

    // Check if email already exists in users table
    if ($is_valid) {
        $check_user = "SELECT email FROM users WHERE email = '$email'";
        $user_result = mysqli_query($conn, $check_user);
        
        // Check if email already exists in admin table
        $check_admin = "SELECT email FROM admin WHERE email = '$email'";
        $admin_result = mysqli_query($conn, $check_admin);
        
        if (mysqli_num_rows($user_result) > 0 || mysqli_num_rows($admin_result) > 0) {
            $is_valid = false;
            $error_messages[] = "Email sudah terdaftar!";
        }
    }

    if ($is_valid) {
        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        
        // Hanya bisa mendaftar sebagai user (penyewa)
        $query = "INSERT INTO users (nama, email, password, no_hp, alamat) VALUES ('$nama', '$email', '$hashed_password', '$no_hp', '$alamat')";
        
        if (mysqli_query($conn, $query)) {
            $_SESSION['success'] = "Registrasi berhasil! Silakan login.";
            header('Location: login.php');
            exit();
        } else {
            $error_messages[] = "Terjadi kesalahan: " . mysqli_error($conn);
        }
    }

    // Store error messages in session for SweetAlert
    if (!empty($error_messages)) {
        $_SESSION['error_messages'] = $error_messages;
        $_SESSION['form_data'] = $_POST;
        header('Location: register.php');
        exit();
    }
}

// Get form data from session if exists
$form_data = [];
if (isset($_SESSION['form_data'])) {
    $form_data = $_SESSION['form_data'];
    unset($_SESSION['form_data']);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - Kos Management</title>
    
    <!-- SB Admin 2 CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/startbootstrap-sb-admin-2/4.1.4/css/sb-admin-2.min.css" rel="stylesheet">
    
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet">
    
    <!-- SweetAlert2 CSS -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
    
    <!-- Custom CSS -->
    <style>
        .bg-register-image {
            background: url('https://source.unsplash.com/featured/?apartment,room') center center;
            background-size: cover;
            position: relative;
        }
        .bg-register-image::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(78, 115, 223, 0.8) 0%, rgba(34, 74, 190, 0.8) 100%);
        }
        .register-card {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .register-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 35px rgba(0,0,0,0.1) !important;
        }
        .btn-register {
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }
        .btn-register:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(78, 115, 223, 0.4);
        }
        .btn-register:active {
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
            margin-bottom: 1rem;
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
            font-size: 14px;
        }
        .floating-label input:focus + label,
        .floating-label input:not(:placeholder-shown) + label,
        .floating-label textarea:focus + label,
        .floating-label textarea:not(:placeholder-shown) + label {
            top: 0;
            font-size: 12px;
            color: #4e73df;
            font-weight: 600;
        }
        .floating-label textarea + label {
            top: 25px;
        }
        .floating-label textarea:focus + label,
        .floating-label textarea:not(:placeholder-shown) + label {
            top: 0;
        }
        .password-strength {
            height: 4px;
            border-radius: 2px;
            margin-top: 5px;
            transition: all 0.3s ease;
        }
        .strength-weak { background-color: #e74a3b; width: 25%; }
        .strength-fair { background-color: #f6c23e; width: 50%; }
        .strength-good { background-color: #1cc88a; width: 75%; }
        .strength-strong { background-color: #1cc88a; width: 100%; }
        .form-section {
            margin-bottom: 1.5rem;
        }
        .form-section-title {
            font-size: 14px;
            font-weight: 600;
            color: #4e73df;
            margin-bottom: 1rem;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #eaecf4;
        }
        .info-text {
            font-size: 12px;
            color: #6e707e;
            margin-top: 0.25rem;
        }
    </style>
</head>
<body class="bg-gradient-primary">
    <div class="container">
        <!-- Outer Row -->
        <div class="row justify-content-center">
            <div class="col-xl-10 col-lg-12 col-md-9">
                <div class="card o-hidden border-0 shadow-lg my-5 register-card">
                    <div class="card-body p-0">
                        <!-- Nested Row within Card Body -->
                        <div class="row">
                            <div class="col-lg-6 d-none d-lg-block bg-register-image">
                                <div class="position-relative h-100 d-flex align-items-center justify-content-center text-white">
                                    <div class="text-center p-5" style="z-index: 1;">
                                        <h2 class="font-weight-bold mb-3">Kos Management System</h2>
                                        <p class="mb-4">Bergabunglah dengan komunitas penyewa kos terpercaya</p>
                                        <div class="row text-center">
                                            <div class="col-6 mb-4">
                                                <i class="fas fa-user-check fa-2x mb-2"></i>
                                                <p>Registrasi Mudah</p>
                                            </div>
                                            <div class="col-6 mb-4">
                                                <i class="fas fa-shield-alt fa-2x mb-2"></i>
                                                <p>Aman & Terjamin</p>
                                            </div>
                                        </div>
                                        <div class="mt-4">
                                            <small><i class="fas fa-info-circle"></i> Proses pendaftaran hanya 2 menit</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="col-lg-6">
                                <div class="p-5">
                                    <div class="text-center">
                                        <h1 class="h4 text-gray-900 mb-2">
                                            <i class="fas fa-user-plus text-primary"></i> Daftar Akun Baru
                                        </h1>
                                        <p class="text-muted mb-4">Buat akun untuk mulai mencari kos</p>
                                    </div>

                                    <form class="user" method="POST" action="" id="registerForm">
                                        <!-- Personal Information Section -->
                                        <div class="form-section">
                                            <div class="form-section-title">
                                                <i class="fas fa-user mr-2"></i>Informasi Pribadi
                                            </div>
                                            
                                            <div class="floating-label">
                                                <input type="text" class="form-control form-control-user" 
                                                    id="nama" name="nama" required 
                                                    placeholder=" "
                                                    value="<?php echo isset($form_data['nama']) ? htmlspecialchars($form_data['nama']) : ''; ?>">
                                                <label for="nama">Nama Lengkap *</label>
                                            </div>

                                            <div class="floating-label">
                                                <input type="email" class="form-control form-control-user" 
                                                    id="email" name="email" required 
                                                    placeholder=" "
                                                    value="<?php echo isset($form_data['email']) ? htmlspecialchars($form_data['email']) : ''; ?>">
                                                <label for="email">Alamat Email *</label>
                                            </div>

                                            <div class="floating-label">
                                                <input type="tel" class="form-control form-control-user" 
                                                    id="no_hp" name="no_hp" required 
                                                    placeholder=" "
                                                    value="<?php echo isset($form_data['no_hp']) ? htmlspecialchars($form_data['no_hp']) : ''; ?>">
                                                <label for="no_hp">Nomor Handphone *</label>
                                            </div>

                                            <div class="floating-label">
                                                <textarea class="form-control form-control-user" 
                                                    id="alamat" name="alamat" required 
                                                    placeholder=" "
                                                    rows="2"><?php echo isset($form_data['alamat']) ? htmlspecialchars($form_data['alamat']) : ''; ?></textarea>
                                                <label for="alamat">Alamat Lengkap *</label>
                                            </div>
                                        </div>

                                        <!-- Account Information Section -->
                                        <div class="form-section">
                                            <div class="form-section-title">
                                                <i class="fas fa-lock mr-2"></i>Informasi Akun
                                            </div>

                                            <div class="floating-label">
                                                <input type="password" class="form-control form-control-user" 
                                                    id="password" name="password" required 
                                                    placeholder=" "
                                                    minlength="6">
                                                <label for="password">Password *</label>
                                                <div class="password-strength" id="passwordStrength"></div>
                                                <div class="info-text">Minimal 6 karakter</div>
                                            </div>

                                            <div class="floating-label">
                                                <input type="password" class="form-control form-control-user" 
                                                    id="confirm_password" name="confirm_password" required 
                                                    placeholder=" ">
                                                <label for="confirm_password">Konfirmasi Password *</label>
                                                <div class="text-danger small mt-1" id="passwordMatch"></div>
                                            </div>
                                        </div>

                                        <!-- Terms and Conditions -->
                                        <div class="form-group">
                                            <div class="custom-control custom-checkbox small">
                                                <input type="checkbox" class="custom-control-input" id="agreeTerms" required>
                                                <label class="custom-control-label" for="agreeTerms">
                                                    Saya menyetujui <a href="#" class="text-primary">Syarat & Ketentuan</a> 
                                                    dan <a href="#" class="text-primary">Kebijakan Privasi</a>
                                                </label>
                                            </div>
                                        </div>

                                        <button type="submit" class="btn btn-primary btn-user btn-block btn-register" id="registerBtn">
                                            <span class="register-text">
                                                <i class="fas fa-user-plus"></i> Daftar Sekarang
                                            </span>
                                            <span class="loading-text" style="display: none;">
                                                <i class="fas fa-spinner fa-spin"></i> Membuat Akun...
                                            </span>
                                        </button>
                                    </form>

                                    <hr>
                                    
                                    <div class="text-center">
                                        <a class="small" href="login.php">
                                            <i class="fas fa-sign-in-alt"></i> Sudah punya akun? Login di sini!
                                        </a>
                                    </div>

                                    <div class="text-center mt-3">
                                        <small class="text-muted">
                                            <i class="fas fa-info-circle"></i> 
                                            Dengan mendaftar, Anda menyetujui semua ketentuan yang berlaku
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
            <?php if (isset($_SESSION['error_messages'])): ?>
                const errorMessages = <?php echo json_encode($_SESSION['error_messages']); ?>;
                Swal.fire({
                    icon: 'error',
                    title: 'Registrasi Gagal!',
                    html: errorMessages.join('<br>'),
                    confirmButtonText: 'Perbaiki Data',
                    confirmButtonColor: '#e74a3b'
                });
                <?php unset($_SESSION['error_messages']); ?>
            <?php endif; ?>

            // Focus on first field
            $('#nama').focus();

            // Floating label functionality
            $('.floating-label input, .floating-label textarea').on('focus blur', function() {
                const label = $(this).siblings('label');
                const hasValue = this.value !== '';
                const isFocused = this === document.activeElement;
                
                label.toggleClass('active', hasValue || isFocused);
            });

            // Trigger initial state
            $('.floating-label input, .floating-label textarea').each(function() {
                if (this.value !== '') {
                    $(this).siblings('label').addClass('active');
                }
            });
        });

        // Password strength indicator
        $('#password').on('input', function() {
            const password = $(this).val();
            const strengthBar = $('#passwordStrength');
            
            if (password.length === 0) {
                strengthBar.attr('class', 'password-strength').css('width', '0%');
                return;
            }

            let strength = 0;
            
            // Length check
            if (password.length >= 6) strength += 25;
            if (password.length >= 8) strength += 25;
            
            // Complexity checks
            if (password.match(/([a-z].*[A-Z])|([A-Z].*[a-z])/)) strength += 25;
            if (password.match(/([0-9])/)) strength += 15;
            if (password.match(/([!,%,&,@,#,$,^,*,?,_,~])/)) strength += 10;

            // Determine strength level
            let strengthClass = '';
            if (strength < 50) {
                strengthClass = 'strength-weak';
            } else if (strength < 75) {
                strengthClass = 'strength-fair';
            } else if (strength < 90) {
                strengthClass = 'strength-good';
            } else {
                strengthClass = 'strength-strong';
            }

            strengthBar.attr('class', 'password-strength ' + strengthClass);
        });

        // Password match validation
        function checkPasswordMatch() {
            const password = $('#password').val();
            const confirmPassword = $('#confirm_password').val();
            const matchIndicator = $('#passwordMatch');

            if (confirmPassword === '') {
                matchIndicator.text('');
            } else if (password === confirmPassword) {
                matchIndicator.text('✓ Password cocok').removeClass('text-danger').addClass('text-success');
            } else {
                matchIndicator.text('✗ Password tidak cocok').removeClass('text-success').addClass('text-danger');
            }
        }

        $('#confirm_password').on('input', checkPasswordMatch);
        $('#password').on('input', checkPasswordMatch);

        // Form submission with SweetAlert and validation
        $('#registerForm').on('submit', function(e) {
            e.preventDefault();
            
            const formData = {
                nama: $('#nama').val().trim(),
                email: $('#email').val().trim(),
                password: $('#password').val(),
                confirm_password: $('#confirm_password').val(),
                no_hp: $('#no_hp').val().trim(),
                alamat: $('#alamat').val().trim()
            };

            // Validation
            const errors = [];

            // Required fields
            if (!formData.nama) errors.push('Nama lengkap harus diisi');
            if (!formData.email) errors.push('Email harus diisi');
            if (!formData.no_hp) errors.push('Nomor handphone harus diisi');
            if (!formData.alamat) errors.push('Alamat harus diisi');
            if (!formData.password) errors.push('Password harus diisi');
            if (!formData.confirm_password) errors.push('Konfirmasi password harus diisi');

            // Email validation
            if (formData.email && !isValidEmail(formData.email)) {
                errors.push('Format email tidak valid');
            }

            // Password validation
            if (formData.password && formData.password.length < 6) {
                errors.push('Password harus minimal 6 karakter');
            }

            if (formData.password !== formData.confirm_password) {
                errors.push('Password dan konfirmasi password tidak sama');
            }

            // Terms agreement
            if (!$('#agreeTerms').is(':checked')) {
                errors.push('Anda harus menyetujui syarat dan ketentuan');
            }

            if (errors.length > 0) {
                Swal.fire({
                    icon: 'warning',
                    title: 'Data Tidak Lengkap',
                    html: errors.join('<br>'),
                    confirmButtonText: 'Perbaiki',
                    confirmButtonColor: '#f6c23e'
                });
                return;
            }

            // Show loading state
            const registerBtn = $('#registerBtn');
            const registerText = registerBtn.find('.register-text');
            const loadingText = registerBtn.find('.loading-text');
            
            registerText.hide();
            loadingText.show();
            registerBtn.prop('disabled', true);

            // Show processing modal
            Swal.fire({
                title: 'Membuat Akun',
                text: 'Sedang memproses pendaftaran...',
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

        // Email validation function
        function isValidEmail(email) {
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return emailRegex.test(email);
        }

        // Input animations
        $('.form-control').on('focus', function() {
            $(this).parent().addClass('focused');
        });

        $('.form-control').on('blur', function() {
            if (!this.value) {
                $(this).parent().removeClass('focused');
            }
        });

        // Auto-format phone number
        $('#no_hp').on('input', function() {
            this.value = this.value.replace(/\D/g, '');
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
                    $('#registerBtn').click();
                }
            }
        });
    </script>
</body>
</html>