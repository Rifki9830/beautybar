<?php
require 'config.php';

if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $pass  = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($pass, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role']    = $user['role'];
        $_SESSION['name']    = $user['username'];

        // Redirect sesuai Role
        if ($user['role'] == 'admin') header("Location: dashboard/admin.php");
        else if ($user['role'] == 'owner') header("Location: dashboard/owner.php");
        else header("Location: dashboard/member.php");
        exit;
    } else {
        $error = "Email atau Password salah!";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Beautybar.bync</title>
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
        html { scroll-behavior: smooth; }
        
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

        .input-group input:focus + .input-icon {
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
                    <a href="index.php" class="text-gray-600 hover:text-primary font-medium text-sm transition-colors">Home</a>
                    <a href="treatments.php" class="text-gray-600 hover:text-primary font-medium text-sm transition-colors">Treatment</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Login Section -->
    <section class="min-h-screen flex items-center justify-center py-20 px-4">
        <div class="w-full max-w-6xl grid md:grid-cols-2 gap-0 bg-white shadow-2xl overflow-hidden">
            
            <!-- Left Side - Image & Info -->
            <div class="hidden md:block relative bg-gradient-to-br from-primary to-gray-800 p-12 text-white">
                <!-- Background Pattern -->
                <div class="absolute inset-0 opacity-10">
                    <div class="absolute inset-0" style="background-image: url('https://images.unsplash.com/photo-1560066984-138dadb4c035?auto=format&fit=crop&w=800&q=80'); background-size: cover; background-position: center;"></div>
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
                                Selamat Datang Kembali
                            </h2>
                            <p class="text-lg text-white/80 leading-relaxed">
                                Login untuk melanjutkan perjalanan kecantikan Anda bersama kami
                            </p>
                        </div>

                        <!-- Features -->
                        <div class="space-y-4">
                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-full bg-accent/20 flex items-center justify-center flex-shrink-0 mt-1">
                                    <i class="fas fa-calendar-check text-accent"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium mb-1">Booking Mudah</h4>
                                    <p class="text-sm text-white/70">Pesan treatment favorit kapan saja</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-full bg-accent/20 flex items-center justify-center flex-shrink-0 mt-1">
                                    <i class="fas fa-history text-accent"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium mb-1">Riwayat Lengkap</h4>
                                    <p class="text-sm text-white/70">Pantau semua treatment Anda</p>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <div class="w-10 h-10 rounded-full bg-accent/20 flex items-center justify-center flex-shrink-0 mt-1">
                                    <i class="fas fa-star text-accent"></i>
                                </div>
                                <div>
                                    <h4 class="font-medium mb-1">Promo Eksklusif</h4>
                                    <p class="text-sm text-white/70">Dapatkan penawaran khusus member</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Footer Info -->
                    <div class="pt-8 border-t border-white/20">
                        <p class="text-sm text-white/60">
                            <i class="fas fa-shield-alt mr-2 text-accent"></i>
                            Data Anda aman dan terenkripsi
                        </p>
                    </div>
                </div>
            </div>

            <!-- Right Side - Login Form -->
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
                        <h1 class="text-3xl lg:text-4xl font-light tracking-tight mb-2">Login</h1>
                        <p class="text-gray-600">Masuk ke akun Anda untuk melanjutkan</p>
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

                    <!-- Login Form -->
                    <form method="POST" class="space-y-6">
                        <!-- Email Field -->
                        <div class="input-group">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Alamat Email
                            </label>
                            <div class="relative">
                                <input type="email" 
                                       name="email" 
                                       required 
                                       placeholder="contoh@email.com"
                                       class="w-full px-4 py-3.5 pl-12 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-0 transition-colors text-sm">
                                <i class="input-icon fas fa-envelope absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 transition-colors"></i>
                            </div>
                        </div>

                        <!-- Password Field -->
                        <div class="input-group">
                            <label class="block text-sm font-semibold text-gray-700 mb-2">
                                Password
                            </label>
                            <div class="relative">
                                <input type="password" 
                                       name="password" 
                                       required 
                                       placeholder="Masukkan password Anda"
                                       class="w-full px-4 py-3.5 pl-12 border-2 border-gray-300 rounded-lg focus:outline-none focus:ring-0 transition-colors text-sm">
                                <i class="input-icon fas fa-lock absolute left-4 top-1/2 -translate-y-1/2 text-gray-400 transition-colors"></i>
                            </div>
                        </div>

                        <!-- Remember & Forgot -->
                        <div class="flex items-center justify-between text-sm">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" class="w-4 h-4 rounded border-gray-300 text-accent focus:ring-accent focus:ring-offset-0">
                                <span class="text-gray-600">Ingat saya</span>
                            </label>
                            <a href="#" class="text-accent hover:text-accent/80 font-medium transition-colors">
                                Lupa Password?
                            </a>
                        </div>

                        <!-- Submit Button -->
                        <button type="submit" 
                                name="login" 
                                class="w-full py-3.5 bg-primary text-white font-semibold rounded-lg hover:bg-black transition-all shadow-lg hover:shadow-xl text-sm">
                            <i class="fas fa-sign-in-alt mr-2"></i>
                            Masuk
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

                        <!-- Register Link -->
                        <div class="text-center space-y-4">
                            <p class="text-sm text-gray-600">
                                Belum punya akun?
                                <a href="register.php" class="text-accent font-semibold hover:text-accent/80 transition-colors ml-1">
                                    Daftar Sekarang
                                </a>
                            </p>
                            
                            <a href="index.php" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-gray-700 transition-colors">
                                <i class="fas fa-arrow-left"></i>
                                Kembali ke Beranda
                            </a>
                        </div>
                    </form>

                    <!-- Footer Note -->
                    <div class="mt-8 pt-6 border-t border-gray-200">
                        <p class="text-xs text-center text-gray-500">
                            Dengan login, Anda menyetujui 
                            <a href="#" class="text-accent hover:underline">Syarat & Ketentuan</a> 
                            dan 
                            <a href="#" class="text-accent hover:underline">Kebijakan Privasi</a> kami
                        </p>
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