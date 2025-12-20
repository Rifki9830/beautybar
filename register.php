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
    <title>Daftar - Beautybar</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>

<body class="bg-gray-50 min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-md">
        <!-- Logo & Title -->
        <div class="text-center mb-8">
            <div class="inline-block bg-purple-600 rounded-full p-4 mb-4">
                <svg class="w-12 h-12 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                        d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-gray-800 mb-2">Daftar Akun Baru</h1>
            <p class="text-gray-600">Bergabung dengan Beautybar sekarang</p>
        </div>

        <!-- Register Card -->
        <div class="bg-white rounded-2xl shadow-2xl p-8">
            <!-- Error Message -->
            <?php if(isset($error)): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-lg mb-6" role="alert">
                <div class="flex items-center">
                    <svg class="w-5 h-5 mr-2" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd"
                            d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                            clip-rule="evenodd"></path>
                    </svg>
                    <span><?php echo $error; ?></span>
                </div>
            </div>
            <?php endif; ?>

            <!-- Form -->
            <form method="POST" class="space-y-5">
                <!-- Name Field -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Lengkap</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path>
                            </svg>
                        </div>
                        <input type="text" name="name" required placeholder="Masukkan nama lengkap Anda"
                            class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                    </div>
                </div>

                <!-- Email Field -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Email</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M16 12a4 4 0 10-8 0 4 4 0 008 0zm0 0v1.5a2.5 2.5 0 005 0V12a9 9 0 10-9 9m4.5-1.206a8.959 8.959 0 01-4.5 1.207">
                                </path>
                            </svg>
                        </div>
                        <input type="email" name="email" required placeholder="contoh@email.com"
                            class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                    </div>
                </div>

                <!-- Password Field -->
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-2">Password</label>
                    <div class="relative">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z">
                                </path>
                            </svg>
                        </div>
                        <input type="password" name="password" required placeholder="Masukkan password Anda"
                            class="w-full pl-10 pr-4 py-3 border-2 border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition">
                    </div>
                </div>

                <!-- Submit Button -->
                <button type="submit" name="register"
                    class="w-full py-3 bg-purple-600 text-white font-semibold rounded-lg hover:bg-purple-700 transition shadow-lg">
                    Daftar Sekarang
                </button>
            </form>

            <!-- Links -->
            <div class="mt-6 text-center space-y-2">
                <p class="text-sm text-gray-600">
                    Sudah punya akun?
                    <a href="login.php" class="text-purple-600 font-semibold hover:text-purple-700">Login Sekarang</a>
                </p>
                <a href="index.php" class="block text-sm text-gray-500 hover:text-gray-700">
                    ← Kembali ke Beranda
                </a>
            </div>
        </div>

        <!-- Footer Info -->
        <div class="mt-6 text-center">
            <p class="text-xs text-gray-500">
                Dengan mendaftar, Anda menyetujui syarat & ketentuan kami
            </p>
        </div>
    </div>
</body>

</html>