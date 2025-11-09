<?php
session_start();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kos-Kosan Management System</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <style>
        * {
            font-family: 'Inter', sans-serif;
        }
        
        .nav-scrolled {
            background-color: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        }
        
        .nav-scrolled .nav-logo,
        .nav-scrolled .nav-link {
            color: #1f2937 !important; /* gray-800 */
        }
        
        .nav-scrolled .nav-cta {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            color: white !important;
        }
        
        .nav-scrolled .nav-cta:hover {
            background: linear-gradient(135deg, #1d4ed8, #1e40af);
        }
        
        .hero-bg {
            background-image: linear-gradient(rgba(0, 0, 0, 0.7), rgba(0, 0, 0, 0.7)), url('https://images.unsplash.com/photo-1564013799919-ab600027ffc6?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
        }
        
        .feature-card {
            transition: all 0.3s ease;
        }
        
        .feature-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
        }
        
        .testimonial-slide {
            transition: opacity 0.5s ease;
        }
        
        .fade-in {
            animation: fadeIn 0.8s ease forwards;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .btn-cta {
            background: linear-gradient(135deg, #3b82f6, #1d4ed8);
            transition: all 0.3s ease;
        }
        
        .btn-cta:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(59, 130, 246, 0.4);
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Navigation -->
    <nav id="navbar" class="fixed w-full z-50 transition-all duration-500 py-4 text-white">
        <div class="container mx-auto px-4">
            <div class="flex justify-between items-center">
                <div class="text-2xl font-bold nav-logo flex items-center">
                    <i class="fas fa-home mr-2"></i>
                    <span>KosManagement</span>
                </div>
                <div class="space-x-6">
                    <a href="login.php" class="nav-link hover:text-blue-200 transition duration-300 font-medium">Login</a>
                    <a href="register.php" class="nav-cta bg-white text-blue-600 px-5 py-2 rounded-full font-semibold hover:bg-blue-50 transition duration-300 shadow-lg">
                        Daftar Sekarang
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero-bg min-h-screen flex items-center justify-center pt-16">
        <div class="container mx-auto px-4 text-center text-white fade-in">
            <h1 class="text-5xl md:text-6xl font-bold mb-6 leading-tight">Tempat Tinggal Nyaman, Pengelolaan Mudah</h1>
            <p class="text-xl md:text-2xl mb-10 max-w-3xl mx-auto leading-relaxed">Sistem manajemen kos-kosan terintegrasi untuk memudahkan pengelolaan properti dan meningkatkan pengalaman penyewa.</p>
            <div class="flex flex-col sm:flex-row justify-center gap-4">
                <a href="register.php" class="btn-cta text-white px-8 py-4 rounded-full font-semibold text-lg inline-flex items-center justify-center">
                    <span>Coba Gratis</span>
                    <i class="fas fa-arrow-right ml-2"></i>
                </a>
                <a href="#features" class="bg-transparent border-2 border-white text-white px-8 py-4 rounded-full font-semibold text-lg hover:bg-white hover:text-blue-700 transition duration-300 inline-flex items-center justify-center">
                    <span>Pelajari Fitur</span>
                    <i class="fas fa-info-circle ml-2"></i>
                </a>
            </div>
        </div>
        
        <!-- Scroll indicator -->
        <div class="absolute bottom-10 left-1/2 transform -translate-x-1/2 animate-bounce">
            <a href="#features" class="text-white text-2xl">
                <i class="fas fa-chevron-down"></i>
            </a>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-20 bg-white">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16 fade-in">
                <h2 class="text-4xl font-bold mb-4 text-gray-800">Solusi Lengkap untuk Pengelolaan Kos</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">Dengan sistem kami, mengelola kos-kosan menjadi lebih efisien, terorganisir, dan menguntungkan.</p>
            </div>
            
            <div class="grid md:grid-cols-3 gap-10">
                <div class="feature-card bg-white p-8 rounded-2xl shadow-lg border border-gray-100 fade-in">
                    <div class="bg-blue-50 w-20 h-20 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-door-open text-blue-500 text-3xl"></i>
                    </div>
                    <h3 class="text-2xl font-semibold mb-4 text-center text-gray-800">Manajemen Kamar</h3>
                    <p class="text-gray-600 text-center">Kelola ketersediaan kamar, fasilitas, dan informasi dengan antarmuka yang intuitif dan mudah digunakan.</p>
                </div>
                
                <div class="feature-card bg-white p-8 rounded-2xl shadow-lg border border-gray-100 fade-in" style="animation-delay: 0.2s">
                    <div class="bg-green-50 w-20 h-20 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-credit-card text-green-500 text-3xl"></i>
                    </div>
                    <h3 class="text-2xl font-semibold mb-4 text-center text-gray-800">Pembayaran Digital</h3>
                    <p class="text-gray-600 text-center">Sistem pembayaran terintegrasi yang aman, cepat, dan dapat diakses kapan saja.</p>
                </div>
                
                <div class="feature-card bg-white p-8 rounded-2xl shadow-lg border border-gray-100 fade-in" style="animation-delay: 0.4s">
                    <div class="bg-purple-50 w-20 h-20 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-bell text-purple-500 text-3xl"></i>
                    </div>
                    <h3 class="text-2xl font-semibold mb-4 text-center text-gray-800">Notifikasi Real-time</h3>
                    <p class="text-gray-600 text-center">Tetap terinformasi dengan notifikasi instan untuk pembayaran, pemeliharaan, dan update penting.</p>
                </div>
            </div>
            
            <div class="grid md:grid-cols-3 gap-10 mt-10">
                <div class="feature-card bg-white p-8 rounded-2xl shadow-lg border border-gray-100 fade-in" style="animation-delay: 0.6s">
                    <div class="bg-yellow-50 w-20 h-20 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-chart-line text-yellow-500 text-3xl"></i>
                    </div>
                    <h3 class="text-2xl font-semibold mb-4 text-center text-gray-800">Laporan Keuangan</h3>
                    <p class="text-gray-600 text-center">Pantai kesehatan keuangan properti Anda dengan laporan yang detail dan mudah dipahami.</p>
                </div>
                
                <div class="feature-card bg-white p-8 rounded-2xl shadow-lg border border-gray-100 fade-in" style="animation-delay: 0.8s">
                    <div class="bg-red-50 w-20 h-20 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-tools text-red-500 text-3xl"></i>
                    </div>
                    <h3 class="text-2xl font-semibold mb-4 text-center text-gray-800">Pemeliharaan</h3>
                    <p class="text-gray-600 text-center">Kelola permintaan perbaikan dan jadwal pemeliharaan dengan sistem yang terorganisir.</p>
                </div>
                
                <div class="feature-card bg-white p-8 rounded-2xl shadow-lg border border-gray-100 fade-in" style="animation-delay: 1s">
                    <div class="bg-indigo-50 w-20 h-20 rounded-2xl flex items-center justify-center mx-auto mb-6">
                        <i class="fas fa-mobile-alt text-indigo-500 text-3xl"></i>
                    </div>
                    <h3 class="text-2xl font-semibold mb-4 text-center text-gray-800">Akses Mobile</h3>
                    <p class="text-gray-600 text-center">Kelola kos-kosan Anda dari mana saja dengan aplikasi yang responsif dan mudah digunakan.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-20 bg-gradient-to-br from-blue-50 to-indigo-100">
        <div class="container mx-auto px-4">
            <div class="text-center mb-16 fade-in">
                <h2 class="text-4xl font-bold mb-4 text-gray-800">Apa Kata Pengguna Kami</h2>
                <p class="text-xl text-gray-600 max-w-3xl mx-auto">Dengarkan pengalaman langsung dari pemilik kos dan penyewa yang telah menggunakan sistem kami.</p>
            </div>
            
            <div class="max-w-6xl mx-auto">
                <div class="bg-white rounded-2xl shadow-xl p-8 md:p-12">
                    <div id="testimonial-slider" class="relative overflow-hidden">
                        <!-- Testimonial content will be loaded by JavaScript -->
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-gradient-to-r from-blue-600 to-indigo-700 text-white">
        <div class="container mx-auto px-4 text-center">
            <h2 class="text-4xl font-bold mb-6">Siap Mengelola Kos dengan Lebih Baik?</h2>
            <p class="text-xl mb-10 max-w-2xl mx-auto">Bergabunglah dengan ratusan pengelola kos yang telah merasakan kemudahan sistem kami.</p>
            <a href="register.php" class="bg-white text-blue-600 px-10 py-4 rounded-full font-bold text-lg hover:bg-blue-50 transition duration-300 inline-flex items-center shadow-lg">
                <span>Mulai Sekarang - Gratis</span>
                <i class="fas fa-rocket ml-2"></i>
            </a>
            <p class="mt-4 text-blue-200">Tidak perlu kartu kredit. Daftar dan gunakan gratis selama 30 hari.</p>
        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-gray-900 text-white pt-16 pb-8">
        <div class="container mx-auto px-4">
            <div class="grid md:grid-cols-4 gap-8 mb-12">
                <div>
                    <div class="text-2xl font-bold flex items-center mb-6">
                        <i class="fas fa-home mr-2"></i>
                        <span>KosManagement</span>
                    </div>
                    <p class="text-gray-400 mb-6">Sistem manajemen kos-kosan terdepan yang memudahkan pengelolaan properti dan meningkatkan pengalaman penyewa.</p>
                    <div class="flex space-x-4">
                        <a href="#" class="text-gray-400 hover:text-white transition duration-300">
                            <i class="fab fa-facebook-f text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition duration-300">
                            <i class="fab fa-twitter text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition duration-300">
                            <i class="fab fa-instagram text-xl"></i>
                        </a>
                        <a href="#" class="text-gray-400 hover:text-white transition duration-300">
                            <i class="fab fa-linkedin-in text-xl"></i>
                        </a>
                    </div>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-6">Tautan Cepat</h3>
                    <ul class="space-y-3">
                        <li><a href="#" class="text-gray-400 hover:text-white transition duration-300">Tentang Kami</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition duration-300">Fitur</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition duration-300">Harga</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition duration-300">Blog</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition duration-300">Kontak</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-6">Dukungan</h3>
                    <ul class="space-y-3">
                        <li><a href="#" class="text-gray-400 hover:text-white transition duration-300">Bantuan</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition duration-300">FAQ</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition duration-300">Kebijakan Privasi</a></li>
                        <li><a href="#" class="text-gray-400 hover:text-white transition duration-300">Syarat & Ketentuan</a></li>
                    </ul>
                </div>
                
                <div>
                    <h3 class="text-lg font-semibold mb-6">Kontak</h3>
                    <ul class="space-y-3">
                        <li class="flex items-start">
                            <i class="fas fa-map-marker-alt mt-1 mr-3 text-blue-400"></i>
                            <span class="text-gray-400">Jl. Contoh No. 123, Jakarta</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-phone mr-3 text-blue-400"></i>
                            <span class="text-gray-400">+62 21 1234 5678</span>
                        </li>
                        <li class="flex items-center">
                            <i class="fas fa-envelope mr-3 text-blue-400"></i>
                            <span class="text-gray-400">info@kosmanagement.com</span>
                        </li>
                    </ul>
                </div>
            </div>
            
            <div class="border-t border-gray-800 pt-8 text-center">
                <p class="text-gray-400">&copy; 2024 Kos Management System. All rights reserved.</p>
            </div>
        </div>
    </footer>

    <script>
        // Navbar scroll effect
        window.addEventListener('scroll', function() {
            const navbar = document.getElementById('navbar');
            if (window.scrollY > 50) {
                navbar.classList.add('nav-scrolled');
            } else {
                navbar.classList.remove('nav-scrolled');
            }
        });
        
        // Fade in animation on scroll
        const fadeElements = document.querySelectorAll('.fade-in');
        
        const fadeInOnScroll = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = 1;
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, { threshold: 0.1 });
        
        fadeElements.forEach(element => {
            element.style.opacity = 0;
            element.style.transform = 'translateY(20px)';
            element.style.transition = 'opacity 0.8s ease, transform 0.8s ease';
            fadeInOnScroll.observe(element);
        });
        
        // Simple testimonial slider
        let currentTestimonial = 0;
        const testimonials = [
            {
                name: "Sarah Wijaya",
                role: "Pemilik Kos 'Rumah Bahagia', Bandung",
                text: "Sejak menggunakan sistem ini, pengelolaan kos saya menjadi jauh lebih mudah. Pembayaran otomatis dan notifikasi yang tepat waktu sangat membantu.",
                image: "https://images.unsplash.com/photo-1494790108755-2616b612b786?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1887&q=80"
            },
            {
                name: "Budi Santoso",
                role: "Penyewa, Jakarta",
                text: "Sangat nyaman menggunakan aplikasi ini untuk pembayaran sewa kos. Interface-nya user friendly dan tidak ribet.",
                image: "https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=1887&q=80"
            },
            {
                name: "Maya Sari",
                role: "Pemilik Kos 'Sejahtera', Surabaya",
                text: "Laporan keuangan yang detail membantu saya memantau kesehatan bisnis kos dengan lebih baik. Sangat recommended!",
                image: "https://images.unsplash.com/photo-1438761681033-6461ffad8d80?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80"
            }
        ];
        
        function updateTestimonial(index) {
            const testimonial = testimonials[index];
            const slider = document.getElementById('testimonial-slider');
            
            slider.innerHTML = `
                <div class="testimonial-slide flex transition-transform duration-500 ease-in-out">
                    <div class="min-w-full flex flex-col md:flex-row items-center">
                        <div class="md:w-1/3 flex justify-center mb-6 md:mb-0">
                            <div class="w-40 h-40 rounded-full overflow-hidden border-4 border-blue-200 shadow-lg">
                                <img src="${testimonial.image}" alt="Testimonial" class="w-full h-full object-cover">
                            </div>
                        </div>
                        <div class="md:w-2/3 text-center md:text-left">
                            <div class="text-yellow-400 text-2xl mb-4">
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                                <i class="fas fa-star"></i>
                            </div>
                            <p class="text-xl text-gray-700 italic mb-6">"${testimonial.text}"</p>
                            <div>
                                <h4 class="font-bold text-gray-800 text-lg">${testimonial.name}</h4>
                                <p class="text-gray-600">${testimonial.role}</p>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex justify-center mt-8 space-x-2">
                    ${testimonials.map((_, i) => 
                        `<button class="testimonial-indicator w-3 h-3 rounded-full ${i === index ? 'bg-blue-600' : 'bg-blue-300'}" onclick="changeTestimonial(${i})"></button>`
                    ).join('')}
                </div>
            `;
        }
        
        function changeTestimonial(index) {
            currentTestimonial = index;
            updateTestimonial(currentTestimonial);
        }
        
        // Auto-rotate testimonials
        setInterval(() => {
            currentTestimonial = (currentTestimonial + 1) % testimonials.length;
            updateTestimonial(currentTestimonial);
        }, 5000);
        
        // Initialize
        document.addEventListener('DOMContentLoaded', function() {
            updateTestimonial(0);
        });
    </script>
</body>
</html>