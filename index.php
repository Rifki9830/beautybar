<?php require 'config.php'; ?>
<!DOCTYPE html>
<html>

<head>
    <title>Beautybar.bync</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <nav class="navbar">
        <div class="brand">Beautybar.bync</div>
        <div>
            <?php if(isset($_SESSION['user_id'])): ?>
            <a href="dashboard/member.php">Dashboard Saya</a>
            <a href="logout.php">Logout</a>
            <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php" class="btn-login">Daftar</a>
            <?php endif; ?>
        </div>
    </nav>

    <div class="hero">
        <h1>Welcome to Beautybar.bync</h1>
        <p>Manjakan dirimu dengan treatment terbaik di Bandar Lampung</p>
        <p>JL. Mayor Sukardi Hamdani Palapa 10, Rajabasa | 09.00 - 21.00</p>
        <a href="login.php" class="btn-main">Booking Sekarang</a>
    </div>

    <div class="container">
        <h2 style="text-align:center; margin-bottom:30px;">Daftar Treatment</h2>
        <div class="grid">
            <?php
            $stmt = $pdo->query("SELECT * FROM treatments");
            while ($row = $stmt->fetch()) {
                echo "
                <div class='card' style='text-align:center;'>
                    <h3>{$row['name']}</h3>
                    <p style='color:#d63384; font-weight:bold; font-size:1.2rem;'>Rp " . number_format($row['price']) . "</p>
                    <p>Durasi: Est. {$row['duration']} Menit</p>
                </div>";
            }
            ?>
        </div>
    </div>
</body>

</html>