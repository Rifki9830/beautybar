<?php
require 'config.php';

if (isset($_POST['register'])) {
    $name  = $_POST['name'];
    $email = $_POST['email'];
    $pass  = password_hash($_POST['password'], PASSWORD_DEFAULT);

    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'member')");
        $stmt->execute([$name, $email, $pass]);
        echo "<script>alert('Registrasi Berhasil! Silakan Login.'); window.location='login.php';</script>";
    } catch (Exception $e) {
        $error = "Email sudah terdaftar!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Daftar - Beautybar.bync</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
    tailwind.config = {
        theme: {
            extend: {
                colors: {
                    primary: '#1a1a1a',
                    secondary: '#f5f5f5',
                    accent: '#d4a574',
                },
                fontFamily: {
                    sans: ['-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif'],
                },
            }
        }
    }
    </script>
    <style>
    html {
        scroll-behavior: smooth;
    }

    @keyframes fadeInUp {
        from {
            opacity: 0;
            transform: translateY(30px);
        }

        to {
            opacity: 1;
            transform: translateY(0);
        }
    }

    .animate-fade-in-up {
        animation: fadeInUp 0.6s ease-out;
    }

    .input-group {
        position: relative;
    }

    .input-group input:focus+.input-icon {
        color: #d4a574;
    }

    .input-group input:focus {
        border-color: #d4a574;
    }
    </style>
</head>

<body class="font-sans text-primary bg-secondary overflow-x-hidden">

    <!-- Navigation -->
    <nav class="bg-white border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Brand -->
                <a href="index.php" class="flex items-center gap-3">
                    <i class="fas fa-spa text-2xl text-accent"></i>
                    <span class="text-xl font-semibold tracking-tight">Beautybar.bync</span>
                </a>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center gap-8">
                    <a href="index.php"
                        class="text-gray-600 hover:text-primary font-medium text-sm transition-colors">Home</a>
                    <a href="treatments.php"
                        class="text-gray-600 hover:text-primary font-medium text-sm transition-colors">Treatment</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Register Section -->
    <section class="min-h-screen flex items-center justify-center py-20 px-4">
        <div class="w-full max-w-6xl grid md:grid-cols-2 gap-0 bg-white shadow-2xl overflow-hidden">

            <!-- Left Side - Image & Info -->
            <div class="hidden md:block relative bg-gradient-to-br from-primary to-gray-800 p-12 text-white">
                <!-- Background Pattern -->
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute inset-0"
                        style="background-image: url('https://images.unsplash.com/photo-1487412947147-5cebf100ffc2?auto=format&fit=crop&w=800&q=80'); background-size: cover; background-position: center;">
                    </div>
                </div>

                <div class="relative z-10 h-full flex flex-col justify-between">
                    <!-- Logo -->
                    <div class="flex items-center gap-3 mb-12">
                        <i class="fas fa-spa text-3xl text-accent"></i>
                        <span class="text-2xl font-semibold tracking-tight">Beautybar.bync</span>
                    </div>

                    <!-- Info Content -->
                    <div class="space-y-8">
                        <div>
                            <h2 class="text-4xl font-light tracking-tight leading-tight mb-4">
                                Bergabung dengan Kami
                            </h2>
                            <p class="text-lg text-white/80 leading-relaxed">
                                Daftar sekarang dan nikmati berbagai keuntungan eksklusif
                            </p>
                        </div>

                        <!-- Features -->
                        <div class="space-y-4">
                            <div class="flex items-start gap-3">
                                <div
                                    class="w-10 h-10 rounded-full bg-accent/20 flex items-center justify-center flex-shrink-0 mt-1">
                                    <i class="fas fa-gift text-accent"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium mb-1">Bonus Member Baru</h4>
                                    <p class="text-sm text-white/70">Dapatkan diskon spesial untuk treatment pertama</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div
                                    class="w-10 h-10 rounded-full bg-accent/20 flex items-center justify-center flex-shrink-0 mt-1">
                                    <i class="fas fa-percent text-accent"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium mb-1">Promo Eksklusif</h4>
                                    <p class="text-sm text-white/70">Akses ke penawaran khusus member</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div
                                    class="w-10 h-10 rounded-full bg-accent/20 flex items-center justify-center flex-shrink-0 mt-1">
                                    <i class="fas fa-crown text-accent"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium mb-1">Loyalty Rewards</h4>
                                    <p class="text-sm text-white/70">Kumpulkan poin setiap kunjungan</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Info -->
                    <div class="pt-8 border-t border-white/20">
                        <p class="text-sm text-white/60">
                            <i class="fas fa-users mr-2 text-accent"></i>
                            Bergabung dengan 10,000+ member lainnya
                        </p>
                    </div>
                </div>
            </div>

            <!-- Right Side - Register Form -->
            <div class="p-8 md:p-12 lg:p-16 flex items-center">
                <div class="w-full animate-fade-in-up">
                    <!-- Mobile Logo -->
                    <div class="md:hidden text-center mb-8">
                        <div class="flex items-center justify-center gap-3 mb-4">
                            <i class="fas fa-spa text-3xl text-accent"></i>
                            <span class="text-2xl font-semibold tracking-tight">Beautybar.bync</span>
                        </div>
                    </div>

                    <!-- Header -->
                    <div class="mb-8">
                        <h1 class="text-3xl lg:text-4xl font-light tracking-tight mb-2">Daftar Akun</h1>
                        <p class="text-gray-600">Buat akun baru untuk memulai</p>
                    </div>

                    <!-- Error Message -->
                    <?php if(isset($error)): ?>
                    <div class="mb-6 p-4 bg-red-50 border-l-4 border-red-500 rounded-r-lg animate-fade-in-up">
                        <div class="flex items-center">
                            <i class="fas fa-exclamation-circle text-red-500 mr-3"></i>
                            <p class="text-red-700 text-sm font-medium"><?php echo $error; ?></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- Register Form -->
                    <form method="POST" class="space-y-6">
                        <!-- Name Field -->
                        <div class="input-group">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Nama Lengkap
                            </label>
                            <div class="relative">
                                <input type="text" name="name" required placeholder="Masukkan nama lengkap Anda"
                                    class="w-full px-4 py-3.5 pl-12 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-0 transition-colors text-sm">
                                <i
                                    class="input-icon fas fa-user absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 transition-colors"></i>
                            </div>
                        </div>

                        <!-- Email Field -->
                        <div class="input-group">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Alamat Email
                            </label>
                            <div class="relative">
                                <input type="email" name="email" required placeholder="contoh@email.com"
                                    class="w-full px-4 py-3.5 pl-12 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-0 transition-colors text-sm">
                                <i
                                    class="input-icon fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 transition-colors"></i>
                            </div>
                        </div>

                        <!-- Password Field -->
                        <div class="input-group">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Password
                            </label>
                            <div class="relative">
                                <input type="password" name="password" required placeholder="Masukkan password Anda"
                                    class="w-full px-4 py-3.5 pl-12 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-0 transition-colors text-sm">
                                <i
                                    class="input-icon fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 transition-colors"></i>
                            </div>
                            <p class="text-xs text-gray-500 mt-2">Minimal 6 karakter</p>
                        </div>

                        <!-- Terms & Conditions -->
                        <div class="flex items-start gap-2">
                            <input type="checkbox" required
                                class="w-4 h-4 mt-0.5 rounded border-gray-300 text-accent focus:ring-accent focus:ring-offset-0">
                            <label class="text-sm text-gray-600">
                                Saya menyetujui
                                <a href="#" class="text-accent hover:text-accent/80 font-medium">Syarat & Ketentuan</a>
                                dan
                                <a href="#" class="text-accent hover:text-accent/80 font-medium">Kebijakan Privasi</a>
                            </label>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" name="register"
                            class="w-full py-3.5 bg-primary text-white font-semibold rounded-lg hover:bg-black transition-all shadow-lg hover:shadow-xl text-sm">
                            <i class="fas fa-user-plus mr-2"></i>
                            Daftar Sekarang
                        </button>

                        <!-- Divider -->
                        <div class="relative my-8">
                            <div class="absolute inset-0 flex items-center">
                                <div class="w-full border-t border-gray-300"></div>
                            </div>
                            <div class="relative flex justify-center text-sm">
                                <span class="px-4 bg-white text-gray-500">atau</span>
                            </div>
                        </div>

                        <!-- Login Link -->
                        <div class="text-center space-y-4">
                            <p class="text-sm text-gray-600">
                                Sudah punya akun?
                                <a href="login.php"
                                    class="text-accent font-semibold hover:text-accent/80 transition-colors ml-1">
                                    Login Sekarang
                                </a>
                            </p>

                            <a href="index.php"
                                class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 transition-colors">
                                <i class="fas fa-arrow-left"></i>
                                Kembali ke Beranda
                            </a>
                        </div>
                    </form>

                    <!-- Footer Note -->
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <div class="flex items-center justify-center gap-6 text-xs text-gray-500">
                            <span class="flex items-center gap-2">
                                <i class="fas fa-shield-alt text-accent"></i>
                                Data Aman
                            </span>
                            <span class="flex items-center gap-2">
                                <i class="fas fa-lock text-accent"></i>
                                Terenkripsi
                            </span>
                            <span class="flex items-center gap-2">
                                <i class="fas fa-check-circle text-accent"></i>
                                Gratis
                            </span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </section>

    <!-- Footer -->
    <footer class="bg-primary text-white py-8">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-3">
                    <i class="fas fa-spa text-xl text-accent"></i>
                    <span class="text-lg font-semibold tracking-tight">Beautybar.bync</span>
                </div>

                <p class="text-white/50 text-sm text-center md:text-left">
                    &copy; 2013-<?= date('Y') ?>, All rights Reserved. Bandar Lampung - Indonesia
                </p>
            </div>
        </div>
    </footer>

</body>

</html>