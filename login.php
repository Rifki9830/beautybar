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
<html>

<head>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <div class="container" style="max-width:400px; margin-top:100px;">
        <div class="card">
            <h2 style="text-align:center; color:#d63384;">Login Beautybar</h2>
            <?php if(isset($error)) echo "<p style='color:red;text-align:center;'>$error</p>"; ?>
            <form method="POST">
                <label>Email</label>
                <input type="email" name="email" required>
                <label>Password</label>
                <input type="password" name="password" required>
                <button type="submit" name="login">Masuk</button>
            </form>
            <p style="text-align:center;">Belum punya akun? <a href="register.php" style="color:#d63384;">Daftar</a></p>
            <p style="text-align:center;"><a href="index.php">Kembali ke Beranda</a></p>
        </div>
    </div>
</body>

</html>