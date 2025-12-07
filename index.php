<?php require 'config.php'; ?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beautybar.bync - Salon Kecantikan Terbaik</title>
    <link rel="stylesheet" href="style.css">
</head>

<body>
    <nav class="navbar">
        <div class="brand">Beautybar.bync</div>
        <div>
            <a href="index.php">Home</a>
            <a href="#treatments">Treatment</a>
            <?php if(isset($_SESSION['user_id'])): ?>
            <a href="dashboard/member.php" style="color:var(--primary);">Dashboard Saya</a>
            <a href="logout.php" class="btn-login" style="background: #555;">Logout</a>
            <?php else: ?>
            <a href="login.php">Login</a>
            <a href="register.php" class="btn-login">Daftar</a>
            <?php endif; ?>
        </div>
    </nav>

    <header class="hero">
        <h1>Welcome to Beautybar.bync</h1>
        <p>Manjakan dirimu dengan sentuhan profesional dan treatment terbaik di Bandar Lampung.</p>
        <p style="font-size: 1rem; opacity: 0.9;">
            📍 JL. Mayor Sukardi Hamdani Palapa 10, Rajabasa <br>
            ⏰ Buka Setiap Hari: 09.00 - 21.00 WIB
        </p>
        <a href="login.php" class="btn-main">Booking Sekarang</a>
    </header>

    <div class="container" id="treatments">
        <h2 class="section-title">Pilihan Treatment Kami</h2>

        <div class="grid">
            <?php
            $stmt = $pdo->query("SELECT * FROM treatments");
            while ($row = $stmt->fetch()) {
                // Mengambil inisial nama treatment untuk gambar dummy
                $initial = substr($row['name'], 0, 1);
            ?>
            <div class='card'>
                <div class="card-img">
                    <?php echo $initial; ?>
                </div>

                <div class="card-body">
                    <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                    <span class="price">Rp <?php echo number_format($row['price']); ?></span>
                    <br>
                    <span class="duration">⏱ <?php echo $row['duration']; ?> Menit</span>

                    <div style="margin-top: 20px;">
                        <a href="login.php" style="color: var(--primary); font-weight:600; font-size:0.9rem;">
                            Booking Layanan Ini &rarr;
                        </a>
                    </div>
                </div>
            </div>
            <?php } ?>
        </div>
    </div>

    <footer class="footer">
        <h3>Beautybar.bync</h3>
        <p>Your Beauty, Our Priority.</p>
        <br>
        <p>&copy; <?php echo date('Y'); ?> Beautybar.bync. All Rights Reserved.</p>
    </footer>

</body>

</html>