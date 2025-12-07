<?php require 'config.php'; ?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beautybar.bync - Salon Kecantikan Terbaik</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>

<body>
    <!-- Navigation -->
    <nav class="navbar">
        <div class="container">
            <div class="navbar-brand">
                <i class="fas fa-spa"></i>
                <span>Beautybar.bync</span>
            </div>
            <div class="navbar-menu">
                <a href="index.php" class="active">Home</a>
                <a href="#treatments">Treatment</a>
                <?php if(isset($_SESSION['user_id'])): ?>
                <a href="dashboard/member.php">Dashboard</a>
                <a href="logout.php" class="btn btn-outline">Logout</a>
                <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php" class="btn btn-primary">Daftar</a>
                <?php endif; ?>
            </div>
            <div class="mobile-toggle">
                <span></span>
                <span></span>
                <span></span>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">Transform Your Beauty Experience</h1>
                <p class="hero-subtitle">Nikmati perawatan kecantikan premium dengan sentuhan profesional di Bandar
                    Lampung</p>
                <div class="hero-buttons">
                    <a href="login.php" class="btn btn-primary">Booking Sekarang</a>
                    <a href="#treatments" class="btn btn-outline">Lihat Layanan</a>
                </div>
            </div>
            <div class="hero-image">
                <div class="hero-image-container">
                    <img src="https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?ixlib=rb-4.0.3&auto=format&fit=crop&w=600&q=80"
                        alt="Beauty Treatment">
                </div>
            </div>
        </div>
        <div class="hero-info">
            <div class="info-item">
                <i class="fas fa-map-marker-alt"></i>
                <span>JL. Mayor Sukardi Hamdani Palapa 10, Rajabasa</span>
            </div>
            <div class="info-item">
                <i class="fas fa-clock"></i>
                <span>Buka Setiap Hari: 09.00 - 21.00 WIB</span>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Mengapa Memilih Kami</h2>
                <p class="section-subtitle">Kami memberikan pengalaman kecantikan terbaik untuk Anda</p>
            </div>
            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-user-tie"></i>
                    </div>
                    <h3>Therapis Profesional</h3>
                    <p>Dilayani oleh tenaga ahli berpengalaman di bidang kecantikan</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-spa"></i>
                    </div>
                    <h3>Perawatan Premium</h3>
                    <p>Produk berkualitas tinggi untuk hasil terbaik bagi kulit Anda</p>
                </div>
                <div class="feature-card">
                    <div class="feature-icon">
                        <i class="fas fa-heart"></i>
                    </div>
                    <h3>Pelayanan Terbaik</h3>
                    <p>Kenyamanan dan kepuasan pelanggan adalah prioritas utama kami</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Treatments Section -->
    <section class="treatments" id="treatments">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Pilihan Treatment Kami</h2>
                <p class="section-subtitle">Nikmati berbagai perawatan kecantikan dengan harga terjangkau</p>
            </div>
            <div class="treatments-grid">
                <?php
                $stmt = $pdo->query("SELECT * FROM treatments");
                while ($row = $stmt->fetch()) {
                    // Mengambil inisial nama treatment untuk gambar fallback
                    $initial = substr($row['name'], 0, 1);
                    $image_src = $row['image'] ? 'assets/uploads/' . htmlspecialchars($row['image']) : '';
                ?>
                <div class="treatment-card">
                    <div class="treatment-image">
                        <?php if ($image_src): ?>
                            <img src="<?php echo $image_src; ?>" alt="<?php echo htmlspecialchars($row['name']); ?>" class="treatment-img">
                        <?php else: ?>
                            <div class="treatment-icon"><?php echo $initial; ?></div>
                        <?php endif; ?>
                    </div>
                    <div class="treatment-content">
                        <h3><?php echo htmlspecialchars($row['name']); ?></h3>
                        <div class="treatment-meta">
                            <span class="price">Rp <?php echo number_format($row['price']); ?></span>
                            <span class="duration"><i class="fas fa-hourglass-half"></i> <?php echo $row['duration']; ?>
                                Menit</span>
                        </div>
                        <a href="login.php" class="btn btn-primary btn-block">Booking Sekarang</a>
                    </div>
                </div>
                <?php } ?>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="testimonials">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Apa Kata Mereka</h2>
                <p class="section-subtitle">Testimoni asli dari pelanggan setia kami</p>
            </div>
            <div class="testimonials-slider">
                <?php
                // PERBAIKAN: Menggunakan u.username karena di database kolomnya adalah 'username'
                $query = "SELECT s.feedback, s.rating, u.username as name 
                          FROM surveys s 
                          JOIN bookings b ON s.booking_id = b.id 
                          JOIN users u ON b.user_id = u.id 
                          ORDER BY s.id DESC 
                          LIMIT 3";
                
                try {
                    $stmt = $pdo->query($query);
                    
                    if ($stmt->rowCount() > 0) {
                        while ($row = $stmt->fetch()) {
                            // Avatar dari inisial username
                            $avatar_url = "https://ui-avatars.com/api/?name=" . urlencode($row['name']) . "&background=random&color=fff";
                ?>
                            <div class="testimonial-card">
                                <div class="testimonial-content">
                                    <div style="color: #ffc107; margin-bottom: 0.5rem;">
                                        <?php for($i=0; $i < $row['rating']; $i++): ?>
                                            <i class="fas fa-star"></i>
                                        <?php endfor; ?>
                                    </div>
                                    <p>"<?php echo htmlspecialchars($row['feedback']); ?>"</p>
                                </div>
                                <div class="testimonial-author">
                                    <div class="author-avatar">
                                        <img src="<?php echo $avatar_url; ?>" alt="<?php echo htmlspecialchars($row['name']); ?>">
                                    </div>
                                    <div class="author-info">
                                        <h4><?php echo htmlspecialchars($row['name']); ?></h4>
                                        <p>Pelanggan Terverifikasi</p>
                                    </div>
                                </div>
                            </div>
                <?php 
                        }
                    } else {
                        // Tampilan jika belum ada data
                        echo '<div class="col-span-3 text-center" style="width:100%;"><p>Belum ada ulasan saat ini.</p></div>';
                    }
                } catch (PDOException $e) {
                    // Tampilkan error spesifik jika masih gagal (Hapus ini saat production)
                    echo '<p style="color:red; text-align:center;">Error: ' . $e->getMessage() . '</p>';
                }
                ?>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="cta">
        <div class="container">
            <div class="cta-content">
                <h2>Siap untuk Tampil Cantik?</h2>
                <p>Booking treatment sekarang dan dapatkan penawaran spesial untuk kunjungan pertama Anda</p>
                <a href="login.php" class="btn btn-light">Booking Sekarang</a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-col">
                    <div class="footer-logo">
                        <i class="fas fa-spa"></i>
                        <span>Beautybar.bync</span>
                    </div>
                    <p>Your Beauty, Our Priority.</p>
                    <div class="social-links">
                        <a href="#"><i class="fab fa-facebook-f"></i></a>
                        <a href="#"><i class="fab fa-instagram"></i></a>
                        <a href="#"><i class="fab fa-twitter"></i></a>
                        <a href="#"><i class="fab fa-whatsapp"></i></a>
                    </div>
                </div>
                <div class="footer-col">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="#treatments">Treatment</a></li>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                    </ul>
                </div>
                <div class="footer-col">
                    <h3>Contact Us</h3>
                    <ul>
                        <li><i class="fas fa-map-marker-alt"></i> JL. Mayor Sukardi Hamdani Palapa 10, Rajabasa</li>
                        <li><i class="fas fa-phone"></i> (0721) 123456</li>
                        <li><i class="fas fa-envelope"></i> info@beautybar.bync</li>
                    </ul>
                </div>
            </div>
            <div class="footer-bottom">
                <p>&copy; <?php echo date('Y'); ?> Beautybar.bync. All Rights Reserved.</p>
            </div>
        </div>
    </footer>

    <script>
    // Mobile Menu Toggle
    document.querySelector('.mobile-toggle').addEventListener('click', function() {
        document.querySelector('.navbar-menu').classList.toggle('active');
    });
    </script>
</body>

</html>