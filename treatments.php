<?php 
require 'config.php';

// Get selected category from URL
$selected_category = isset($_GET['category']) ? (int)$_GET['category'] : 0;

// Get all categories with treatment count
$categories = $pdo->query("
    SELECT c.*, COUNT(t.id) as treatment_count 
    FROM categories c 
    LEFT JOIN treatments t ON c.id = t.category_id 
    GROUP BY c.id 
    ORDER BY c.name
")->fetchAll();

// Get treatments based on category
if ($selected_category > 0) {
    $stmt = $pdo->prepare("
        SELECT t.*, c.name as category_name 
        FROM treatments t 
        LEFT JOIN categories c ON t.category_id = c.id 
        WHERE t.category_id = ? 
        ORDER BY t.name
    ");
    $stmt->execute([$selected_category]);
    $treatments = $stmt->fetchAll();
    
    // Get category name
    $cat_stmt = $pdo->prepare("SELECT name FROM categories WHERE id = ?");
    $cat_stmt->execute([$selected_category]);
    $category_info = $cat_stmt->fetch();
    $page_title = $category_info ? $category_info['name'] : "Semua Treatment";
} else {
    // Get all treatments
    $treatments = $pdo->query("
        SELECT t.*, c.name as category_name 
        FROM treatments t 
        LEFT JOIN categories c ON t.category_id = c.id 
        ORDER BY t.category_id, t.name
    ")->fetchAll();
    $page_title = "Semua Treatment";
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Beautybar.bync</title>
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
                <a href="index.php">Home</a>
                <a href="treatments.php" class="active">Treatment</a>

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

    <!-- Hero Section (konsisten dengan home) -->
    <section class="hero" style="min-height: 60vh;">
        <div class="container">
            <div class="hero-content">
                <h1 class="hero-title"><?= htmlspecialchars($page_title) ?></h1>
                <p class="hero-subtitle">
                    Pilih treatment terbaik untuk kecantikan Anda dengan harga terjangkau
                </p>

                <div class="hero-info" style="margin-top: 30px;">
                    <div class="info-item">
                        <i class="fas fa-map-marker-alt"></i>
                        <span>JL. Mayor Sukardi Hamdani Palapa 10, Rajabasa</span>
                    </div>
                    <div class="info-item">
                        <i class="fas fa-clock"></i>
                        <span>Buka Setiap Hari: 09.00 - 21.00 WIB</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Category Filter Section -->
    <section class="features" style="padding: 60px 0 40px;">
        <div class="container">
            <div class="section-header">
                <h2 class="section-title">Kategori Treatment</h2>
                <p class="section-subtitle">Pilih kategori sesuai kebutuhan kecantikan Anda</p>
            </div>

            <div style="display: flex; gap: 15px; flex-wrap: wrap; justify-content: center; margin-top: 40px;">
                <a href="treatments.php"
                    class="filter-btn btn <?= $selected_category == 0 ? 'btn-primary' : 'btn-outline' ?>"
                    data-category="0" style="border-radius: 50px; padding: 12px 30px;">
                    <i class="fas fa-th"></i>
                    Semua
                    <span
                        style="background: rgba(255,255,255,0.2); padding: 2px 10px; border-radius: 12px; margin-left: 8px; font-size: 0.85rem;">
                        <?= count($pdo->query("SELECT * FROM treatments")->fetchAll()) ?>
                    </span>
                </a>

                <?php foreach ($categories as $cat): ?>
                <?php if ($cat['treatment_count'] > 0): ?>
                <a href="treatments.php?category=<?= $cat['id'] ?>"
                    class="filter-btn btn <?= $selected_category == $cat['id'] ? 'btn-primary' : 'btn-outline' ?>"
                    data-category="<?= $cat['id'] ?>" style="border-radius: 50px; padding: 12px 30px;">
                    <i class="fas fa-tag"></i>
                    <?= htmlspecialchars($cat['name']) ?>
                    <span
                        style="background: rgba(255,255,255,0.2); padding: 2px 10px; border-radius: 12px; margin-left: 8px; font-size: 0.85rem;">
                        <?= $cat['treatment_count'] ?>
                    </span>
                </a>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Treatments List (menggunakan style yang sama dengan home) -->
    <section class="treatments" style="padding: 40px 0 80px;">
        <div class="container">
            <?php if (count($treatments) > 0): ?>
            <div class="treatments-grid">
                <?php foreach ($treatments as $treatment): ?>
                <?php
                        $initial = substr($treatment['name'], 0, 1);
                        $image_src = $treatment['image'] 
                            ? 'assets/uploads/' . htmlspecialchars($treatment['image']) 
                            : '';
                        ?>
                <div class="treatment-card" data-category-id="<?= (int)$treatment['category_id'] ?>">
                    <div class="treatment-image">
                        <?php if ($image_src): ?>
                        <img src="<?= $image_src ?>" alt="<?= htmlspecialchars($treatment['name']) ?>"
                            class="treatment-img">
                        <?php else: ?>
                        <div class="treatment-icon"><?= $initial ?></div>
                        <?php endif; ?>

                        <?php if ($treatment['category_name']): ?>
                        <div
                            style="position: absolute; top: 15px; right: 15px; background: rgba(255,255,255,0.95); color: #667eea; padding: 6px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600;">
                            <?= htmlspecialchars($treatment['category_name']) ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="treatment-content">
                        <h3><?= htmlspecialchars($treatment['name']) ?></h3>

                        <div class="treatment-meta">
                            <span class="price">Rp <?= number_format($treatment['price'], 0, ',', '.') ?></span>
                            <span class="duration">
                                <i class="fas fa-hourglass-half"></i>
                                <?= $treatment['duration'] ?> Menit
                            </span>
                        </div>

                        <a href="login.php" class="btn btn-primary btn-block">
                            <i class="fas fa-calendar-check"></i> Booking Sekarang
                        </a>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div style="text-align: center; padding: 80px 20px;">
                <i class="fas fa-inbox" style="font-size: 5rem; color: #cbd5e0; margin-bottom: 20px;"></i>
                <h3 style="font-size: 1.5rem; color: #4a5568; margin-bottom: 10px;">Belum Ada Treatment</h3>
                <p style="color: #718096; margin-bottom: 30px;">Kategori ini belum memiliki treatment. Silakan pilih
                    kategori lain.</p>
                <a href="treatments.php" class="btn btn-primary">
                    Lihat Semua Treatment
                </a>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- CTA (konsisten dengan home) -->
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

    <!-- Mobile Menu Script -->
    <script>
    document.querySelector('.mobile-toggle').addEventListener('click', function() {
        document.querySelector('.navbar-menu').classList.toggle('active');
    });
    </script>

    <!-- Client-side filter (no reload) -->
    <script>
    (function() {
        const filterButtons = document.querySelectorAll('.filter-btn');
        const cards = document.querySelectorAll('.treatment-card');

        function setActiveButton(activeBtn) {
            filterButtons.forEach(btn => {
                if (btn === activeBtn) {
                    btn.classList.remove('btn-outline');
                    btn.classList.add('btn-primary');
                } else {
                    btn.classList.remove('btn-primary');
                    btn.classList.add('btn-outline');
                }
            });
        }

        function filterByCategory(catId) {
            const id = parseInt(catId, 10);
            cards.forEach(card => {
                const c = parseInt(card.getAttribute('data-category-id') || 0, 10);
                if (id === 0 || c === id) {
                    card.style.display = '';
                } else {
                    card.style.display = 'none';
                }
            });
        }

        filterButtons.forEach(btn => {
            btn.addEventListener('click', function(e) {
                // if JS is enabled, prevent full page navigation
                e.preventDefault();
                const cat = this.getAttribute('data-category');
                setActiveButton(this);
                filterByCategory(cat);
            });
        });

        // Initialize: if page loaded with ?category= param, keep that active (server already set classes)
        // But still run filter to ensure cards match initial state
        (function initFromServer() {
            const active = Array.from(filterButtons).find(b => b.classList.contains('btn-primary'));
            if (active) {
                const cat = active.getAttribute('data-category');
                filterByCategory(cat);
            }
        })();
    })();
    </script>

</body>

</html>