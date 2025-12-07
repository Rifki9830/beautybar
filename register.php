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
<html>

<head>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container" style="max-width:400px; margin-top:50px;">
        <div class="card">
            <h2 style="text-align:center; color:#d63384;">Daftar Member</h2>
            <?php if(isset($error)) echo "<p style='color:red;'>$error</p>"; ?>
            <form method="POST">
                <label>Nama Lengkap</label>
                <input type="text" name="name" required>
                <label>Email</label>
                <input type="email" name="email" required>
                <label>Password</label>
                <input type="password" name="password" required>
                <button type="submit" name="register">Daftar</button>
            </form>
            <p style="text-align:center;">Sudah punya akun? <a href="login.php">Login</a></p>
        </div>
    </div>
</body>

</html>