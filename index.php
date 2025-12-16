<?php 
require 'config.php';
?>

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
                <a href="treatments.php">Treatment</a>

                <?php if(isset($_SESSION['user_id'])): ?>
                <a href="dashboard/member.php">Dashboard</a>
                <a href="logout.php" class="btn btn-outline">Logout</a>
                <?php else: ?>
                <a href="login.php">Login</a>
                <a href="register.php" class="btn btn-primary">Daftar</a>
                <?php endif; ?>
            </div>

            <div class="mobile-toggle">
                <span></span><span></span><span></span>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title">Transform Your Beauty Experience</h1>
                <p class="hero-subtitle">
                    Nikmati perawatan kecantikan premium dengan sentuhan profesional di Bandar Lampung
                </p>

                <div class="hero-buttons">
                    <a href="login.php" class="btn btn-primary">Booking Sekarang</a>
                    <a href="#treatments" class="btn btn-outline">Lihat Layanan</a>
                </div>
            </div>

            <div class="hero-image">
                <div class="hero-image-container">
                    <img src="https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?auto=format&fit=crop&w=600&q=80"
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
                    <div class="feature-icon"><i class="fas fa-user-tie"></i></div>
                    <h3>Therapis Profesional</h3>
                    <p>Dilayani oleh tenaga ahli berpengalaman di bidang kecantikan</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-spa"></i></div>
                    <h3>Perawatan Premium</h3>
                    <p>Produk berkualitas tinggi untuk hasil terbaik bagi kulit Anda</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon"><i class="fas fa-heart"></i></div>
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
                // Query untuk mengambil MAKSIMAL 9 treatment
                $stmt = $pdo->query("SELECT t.*, c.name as category_name 
                                     FROM treatments t 
                                     LEFT JOIN categories c ON t.category_id = c.id
                                     ORDER BY t.id DESC 
                                     LIMIT 9");
                $treatments = $stmt->fetchAll();
                
                if (count($treatments) > 0) {
                    foreach ($treatments as $row) {

                        $initial = substr($row['name'], 0, 1);
                        $image_src = $row['image'] 
                            ? 'assets/uploads/' . htmlspecialchars($row['image']) 
                            : '';
                ?>
                <div class="treatment-card">
                    <div class="treatment-image">
                        <?php if ($image_src): ?>
                        <img src="<?= $image_src ?>" alt="<?= htmlspecialchars($row['name']) ?>" class="treatment-img">
                        <?php else: ?>
                        <div class="treatment-icon"><?= $initial ?></div>
                        <?php endif; ?>

                        <?php if ($row['category_name']): ?>
                        <div
                            style="position: absolute; top: 15px; right: 15px; background: rgba(255,255,255,0.95); color: #667eea; padding: 6px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600;">
                            <?= htmlspecialchars($row['category_name']) ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="treatment-content">
                        <h3><?= htmlspecialchars($row['name']) ?></h3>

                        <div class="treatment-meta">
                            <span class="price">Rp <?= number_format($row['price'], 0, ',', '.') ?></span>
                            <span class="duration">
                                <i class="fas fa-hourglass-half"></i>
                                <?= $row['duration'] ?> Menit
                            </span>
                        </div>

                        <a href="login.php" class="btn btn-primary btn-block">
                            <i class="fas fa-calendar-check"></i> Booking Sekarang
                        </a>
                    </div>
                </div>
                <?php 
                    } 
                } else {
                    echo "<p style='text-align: center; color: #718096; padding: 40px;'>Belum ada treatment yang tersedia saat ini.</p>";
                }
                ?>
            </div>

            <?php
            // Hitung total treatments
            $total_treatments_stmt = $pdo->query("SELECT COUNT(*) AS total FROM treatments");
            $total_treatments = $total_treatments_stmt->fetchColumn();
            
            // Tampilkan tombol "Lihat Semua Treatment" jika ada lebih dari 9
            if ($total_treatments > 9) {
            ?>
            <div style="text-align: center; margin-top: 40px;">
                <a href="treatments.php" class="btn btn-outline" style="min-width: 250px;">
                    <i class="fas fa-eye"></i> Lihat Semua Treatment (<?= $total_treatments ?>)
                </a>
            </div>
            <?php
            }
            ?>
        </div>
    </section>

    <!-- Testimonials (Auto Slide + Swipe) -->
    <section class="testimonials">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Apa Kata Mereka</h2>
                <p class="section-subtitle">Testimoni asli dari pelanggan setia kami</p>
            </div>

            <div class="testimonials-slider">
                <div class="testimonial-wrapper">

                    <?php
                    $query = "SELECT s.feedback, s.rating, u.username AS name 
                                FROM surveys s
                                JOIN bookings b ON s.booking_id = b.id
                                JOIN users u ON b.user_id = u.id
                                ORDER BY s.id DESC
                                LIMIT 5";

                    $stmt = $pdo->query($query);

                    if ($stmt->rowCount() > 0) {
                        while ($row = $stmt->fetch()) {

                            $avatar_url = "https://ui-avatars.com/api/?name=" 
                            . urlencode($row['name']) 
                            . "&background=random&color=fff";
                    ?>

                    <div class="testimonial-card">
                        <div class="testimonial-inner">
                            <div class="testimonial-content">
                                <div style="color:#ffc107;margin-bottom:8px;">
                                    <?php for ($i=0; $i < $row['rating']; $i++): ?>
                                    <i class="fas fa-star"></i>
                                    <?php endfor; ?>
                                </div>
                                <p>"<?= htmlspecialchars($row['feedback']) ?>"</p>
                            </div>

                            <div class="testimonial-author">
                                <div class="author-avatar">
                                    <img src="<?= $avatar_url ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                                </div>
                                <div class="author-info">
                                    <h4><?= htmlspecialchars($row['name']) ?></h4>
                                    <p>Pelanggan Terverifikasi</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <?php 
                        }
                    } else {
                        echo "<p>Belum ada ulasan.</p>";
                    }
                    ?>

                </div>

                <div class="slider-dots"></div>
            </div>
        </div>
    </section>

    <!-- CTA -->
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
                </div>

                <div class="footer-col">
                    <h3>Quick Links</h3>
                    <ul>
                        <li><a href="index.php">Home</a></li>
                        <li><a href="treatments.php">Treatment</a></li>
                        <li><a href="login.php">Login</a></li>
                        <li><a href="register.php">Register</a></li>
                    </ul>
                </div>

                <div class="footer-col">
                    <h3>Contact Us</h3>
                    <ul>
                        <li><i class="fas fa-map-marker-alt"></i> Rajabasa, Bandar Lampung</li>
                        <li><i class="fas fa-phone"></i> (0721) 123456</li>
                        <li><i class="fas fa-envelope"></i> info@beautybar.bync</li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> Beautybar.bync. All Rights Reserved.</p>
            </div>
        </div>
    </footer>


    <!-- Mobile Menu -->
    <script>
    document.querySelector('.mobile-toggle').addEventListener('click', function() {
        document.querySelector('.navbar-menu').classList.toggle('active');
    });
    </script>

    <!-- AUTO SLIDE + SWIPE TESTIMONIAL -->
    <script>
    (function() {
        // Robust slider: pixel-based transform, resize handling, and guards
        const wrapper = document.querySelector('.testimonial-wrapper');
        const dotsContainer = document.querySelector('.slider-dots');

        if (!wrapper) {
            if (dotsContainer) dotsContainer.classList.add('hidden');
            return;
        }

        const cards = wrapper.querySelectorAll('.testimonial-card');
        const totalSlides = cards.length;

        if (totalSlides === 0) {
            if (dotsContainer) dotsContainer.classList.add('hidden');
            return;
        }

        let currentSlide = 0;
        let isDragging = false;
        let startX = 0;
        let slideWidth = 0;
        let slideInterval = null;

        const gap = parseFloat(getComputedStyle(wrapper).gap) || 0;

        function calculateSizes() {
            // Calculate slide width using the visible slider container width
            const slider = wrapper.closest('.testimonials-slider');
            // Use clientWidth to exclude potential fractional scrollbar area
            const containerWidth = slider ? slider.clientWidth : wrapper.clientWidth;

            // Keep each slide as flex: 0 0 100% (CSS) and compute slideWidth for transforms
            slideWidth = containerWidth + gap;
        }

        function createDots() {
            if (!dotsContainer) return;
            dotsContainer.innerHTML = '';
            if (totalSlides <= 1) {
                dotsContainer.classList.add('hidden');
                return;
            }
            dotsContainer.classList.remove('hidden');
            for (let i = 0; i < totalSlides; i++) {
                const dot = document.createElement('span');
                if (i === 0) dot.classList.add('active');
                dot.addEventListener('click', () => goToSlide(i));
                dotsContainer.appendChild(dot);
            }
        }

        function updateDots() {
            if (!dotsContainer) return;
            const dots = dotsContainer.querySelectorAll('span');
            dots.forEach(d => d.classList.remove('active'));
            if (dots[currentSlide]) dots[currentSlide].classList.add('active');
        }

        function updateSlider(animate = true) {
            if (animate) wrapper.style.transition = 'transform 0.6s cubic-bezier(0.2, 0.9, 0.2, 1)';
            else wrapper.style.transition = 'none';
            const x = -currentSlide * slideWidth;
            wrapper.style.transform = `translateX(${x}px)`;
            updateDots();
        }

        function goToSlide(n) {
            currentSlide = Math.max(0, Math.min(n, totalSlides - 1));
            updateSlider();
            resetAutoSlide();
        }

        function autoSlide() {
            currentSlide = (currentSlide + 1) % totalSlides;
            updateSlider();
        }

        function resetAutoSlide() {
            if (slideInterval) clearInterval(slideInterval);
            slideInterval = setInterval(autoSlide, 4000);
        }

        // Drag / Swipe
        function startDrag(e) {
            if (e.type === 'mousedown') e.preventDefault();
            clearInterval(slideInterval);
            isDragging = true;
            startX = (e.type.includes('mouse')) ? e.pageX : e.touches[0].clientX;
            wrapper.style.transition = 'none';
            calculateSizes();
        }

        function dragMove(e) {
            if (!isDragging) return;
            const currentX = (e.type.includes('mouse')) ? e.pageX : e.touches[0].clientX;
            const diff = currentX - startX;
            const x = -currentSlide * slideWidth + diff;
            wrapper.style.transform = `translateX(${x}px)`;
        }

        function endDrag(e) {
            if (!isDragging) return;
            isDragging = false;
            const endX = (e.type.includes('mouse')) ? e.pageX : (e.changedTouches ? e.changedTouches[0].clientX : startX);
            const diff = endX - startX;
            // threshold
            if (diff > 70 && currentSlide > 0) currentSlide--;
            else if (diff < -70 && currentSlide < totalSlides - 1) currentSlide++;
            updateSlider(true);
            resetAutoSlide();
        }

        // init
        calculateSizes();
        createDots();
        updateSlider(false);
        resetAutoSlide();

        // events
        // support both touch and mouse
        wrapper.addEventListener('touchstart', startDrag, {passive: true});
        wrapper.addEventListener('mousedown', startDrag);

        wrapper.addEventListener('touchmove', dragMove, {passive: true});
        wrapper.addEventListener('mousemove', dragMove);

        wrapper.addEventListener('touchend', endDrag);
        wrapper.addEventListener('mouseup', endDrag);
        wrapper.addEventListener('mouseleave', endDrag);

        // recalc on resize (cards widths change)
        window.addEventListener('resize', () => {
            calculateSizes();
            updateSlider(false);
        });
    })();
    </script>

</body>

</html>