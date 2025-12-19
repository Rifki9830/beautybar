<?php 
// Pastikan config.php sudah ada dan benar
require_once 'config.php';

// Cek koneksi database
if (!isset($pdo)) {
    die("Error: Database connection not established. Please check config.php");
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Beautybar.bync - Salon Kecantikan Terbaik</title>
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
        /* Custom smooth scroll */
        html { scroll-behavior: smooth; }
        
        /* Navbar backdrop blur */
        .navbar-blur {
            backdrop-filter: blur(10px);
            background-color: rgba(255, 255, 255, 0.95);
        }
        
        /* Animation */
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
    </style>
</head>

<body class="font-sans text-primary bg-white overflow-x-hidden">

    <!-- Navigation -->
    <nav class="navbar-blur fixed top-0 left-0 right-0 border-b border-gray-200 z-50">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Brand -->
                <div class="flex items-center gap-3">
                    <i class="fas fa-spa text-2xl text-accent"></i>
                    <span class="text-xl font-semibold tracking-tight">Beautybar.bync</span>
                </div>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center gap-8">
                    <a href="index.php" class="text-gray-600 hover:text-primary font-medium text-sm transition-colors relative group">
                        Home
                        <span class="absolute -bottom-2 left-0 w-full h-0.5 bg-accent scale-x-100 group-hover:scale-x-100 transition-transform origin-left"></span>
                    </a>
                    <a href="treatments.php" class="text-gray-600 hover:text-primary font-medium text-sm transition-colors">Treatment</a>
                    
                    <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="dashboard/member.php" class="text-gray-600 hover:text-primary font-medium text-sm transition-colors">Dashboard</a>
                    <a href="logout.php" class="px-6 py-2.5 border border-primary text-primary hover:bg-primary hover:text-white transition-all text-sm font-medium">
                        Logout
                    </a>
                    <?php else: ?>
                    <a href="login.php" class="text-gray-600 hover:text-primary font-medium text-sm transition-colors">Login</a>
                    <a href="register.php" class="px-6 py-2.5 bg-primary text-white hover:bg-black transition-all text-sm font-medium">
                        Daftar
                    </a>
                    <?php endif; ?>
                </div>

                <!-- Mobile Toggle -->
                <button class="md:hidden flex flex-col gap-1.5 mobile-toggle">
                    <span class="w-6 h-0.5 bg-primary transition-all"></span>
                    <span class="w-6 h-0.5 bg-primary transition-all"></span>
                    <span class="w-6 h-0.5 bg-primary transition-all"></span>
                </button>
            </div>

            <!-- Mobile Menu -->
            <div class="md:hidden hidden mobile-menu pb-6">
                <div class="flex flex-col gap-4">
                    <a href="index.php" class="text-gray-600 hover:text-primary font-medium text-sm">Home</a>
                    <a href="treatments.php" class="text-gray-600 hover:text-primary font-medium text-sm">Treatment</a>
                    <?php if(isset($_SESSION['user_id'])): ?>
                    <a href="dashboard/member.php" class="text-gray-600 hover:text-primary font-medium text-sm">Dashboard</a>
                    <a href="logout.php" class="px-6 py-2.5 border border-primary text-primary text-center text-sm font-medium">Logout</a>
                    <?php else: ?>
                    <a href="login.php" class="text-gray-600 hover:text-primary font-medium text-sm">Login</a>
                    <a href="register.php" class="px-6 py-2.5 bg-primary text-white text-center text-sm font-medium">Daftar</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative pt-32 pb-20 lg:pb-32 min-h-[600px] lg:min-h-[700px] flex items-center">
        <!-- Background Image -->
        <div class="absolute inset-0 z-0">
            <img src="https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?auto=format&fit=crop&w=1600&q=80" 
                 alt="Beauty Treatment" 
                 class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-r from-black/70 via-black/50 to-transparent"></div>
        </div>

        <!-- Content -->
        <div class="max-w-7xl mx-auto px-6 lg:px-8 relative z-10">
            <div class="max-w-2xl animate-fade-in-up">
                <h1 class="text-5xl lg:text-6xl font-light tracking-tight leading-tight mb-6 text-white">
                    Transform Your Beauty Experience
                </h1>
                <p class="text-lg text-white/90 mb-8 leading-relaxed">
                    Nikmati perawatan kecantikan premium dengan sentuhan profesional di Bandar Lampung
                </p>
                
                <!-- Info dalam Hero -->
                <div class="space-y-4 mb-10 pb-8 border-b border-white/20">
                    <div class="flex items-start gap-3">
                        <i class="fas fa-map-marker-alt text-accent mt-1 flex-shrink-0"></i>
                        <span class="text-white/80 text-sm leading-relaxed">JL. Mayor Sukardi Hamdani Palapa 10, Rajabasa, Bandar Lampung</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <i class="fas fa-clock text-accent flex-shrink-0"></i>
                        <span class="text-white/80 text-sm">Buka Setiap Hari: 09.00 - 21.00 WIB</span>
                    </div>
                </div>
                
                <div class="flex flex-wrap gap-4">
                    <a href="login.php" class="inline-flex items-center gap-2 px-8 py-3.5 bg-white text-primary hover:bg-gray-100 transition-all font-medium text-sm shadow-lg">
                        Booking Sekarang
                    </a>
                    <a href="#treatments" class="inline-flex items-center gap-2 px-8 py-3.5 border-2 border-white text-white hover:bg-white hover:text-primary transition-all font-medium text-sm">
                        Lihat Layanan
                    </a>
                </div>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-20 lg:py-32 bg-secondary">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="text-center mb-16 lg:mb-20">
                <h2 class="text-4xl lg:text-5xl font-light tracking-tight mb-4">Mengapa Memilih Kami</h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">Kami memberikan pengalaman kecantikan terbaik untuk Anda</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8 lg:gap-12">
                <div class="bg-white p-10 text-center hover:-translate-y-2 transition-transform duration-300">
                    <div class="w-16 h-16 mx-auto mb-6 flex items-center justify-center">
                        <i class="fas fa-user-tie text-3xl text-accent"></i>
                    </div>
                    <h3 class="text-xl font-medium mb-3">Therapis Profesional</h3>
                    <p class="text-gray-600 leading-relaxed">Dilayani oleh tenaga ahli berpengalaman di bidang kecantikan</p>
                </div>

                <div class="bg-white p-10 text-center hover:-translate-y-2 transition-transform duration-300">
                    <div class="w-16 h-16 mx-auto mb-6 flex items-center justify-center">
                        <i class="fas fa-spa text-3xl text-accent"></i>
                    </div>
                    <h3 class="text-xl font-medium mb-3">Perawatan Premium</h3>
                    <p class="text-gray-600 leading-relaxed">Produk berkualitas tinggi untuk hasil terbaik bagi kulit Anda</p>
                </div>

                <div class="bg-white p-10 text-center hover:-translate-y-2 transition-transform duration-300">
                    <div class="w-16 h-16 mx-auto mb-6 flex items-center justify-center">
                        <i class="fas fa-heart text-3xl text-accent"></i>
                    </div>
                    <h3 class="text-xl font-medium mb-3">Pelayanan Terbaik</h3>
                    <p class="text-gray-600 leading-relaxed">Kenyamanan dan kepuasan pelanggan adalah prioritas utama kami</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Treatments Section -->
    <section class="py-20 lg:py-32" id="treatments">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="text-center mb-16 lg:mb-20">
                <h2 class="text-4xl lg:text-5xl font-light tracking-tight mb-4">Pilihan Treatment Kami</h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">Nikmati berbagai perawatan kecantikan dengan harga terjangkau</p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <?php
                try {
                    $stmt = $pdo->query("SELECT t.*, c.name as category_name 
                                         FROM treatments t 
                                         LEFT JOIN categories c ON t.category_id = c.id
                                         ORDER BY t.id DESC 
                                         LIMIT 9");
                    $treatments = $stmt->fetchAll(PDO::FETCH_ASSOC);
                    
                    if (count($treatments) > 0) {
                        foreach ($treatments as $row) {
                            $initial = substr($row['name'], 0, 1);
                            $image_src = $row['image'] 
                                ? 'assets/uploads/' . htmlspecialchars($row['image']) 
                                : '';
                ?>
                <div class="bg-white border border-gray-200 overflow-hidden hover:border-primary transition-all duration-300 group">
                    <div class="relative h-72 bg-gradient-to-br from-gray-100 to-gray-200 overflow-hidden">
                        <?php if ($image_src): ?>
                        <img src="<?= $image_src ?>" alt="<?= htmlspecialchars($row['name']) ?>" 
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500">
                        <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center text-6xl font-light text-accent">
                            <?= $initial ?>
                        </div>
                        <?php endif; ?>

                        <?php if ($row['category_name']): ?>
                        <div class="absolute top-4 right-4 bg-white/95 text-accent px-3 py-1.5 text-xs font-semibold">
                            <?= htmlspecialchars($row['category_name']) ?>
                        </div>
                        <?php endif; ?>
                    </div>

                    <div class="p-7">
                        <h3 class="text-xl font-medium mb-4"><?= htmlspecialchars($row['name']) ?></h3>

                        <div class="flex items-center justify-between mb-6 pb-6 border-b border-gray-200">
                            <span class="text-2xl font-semibold text-accent">Rp <?= number_format($row['price'], 0, ',', '.') ?></span>
                            <span class="flex items-center gap-2 text-gray-600 text-sm">
                                <i class="fas fa-hourglass-half"></i>
                                <?= $row['duration'] ?> Menit
                            </span>
                        </div>

                        <a href="login.php" class="w-full inline-flex items-center justify-center gap-2 px-6 py-3 bg-primary text-white hover:bg-black transition-all text-sm font-medium">
                            <i class="fas fa-calendar-check"></i> Booking Sekarang
                        </a>
                    </div>
                </div>
                <?php 
                    } 
                } else {
                    echo "<p class='col-span-full text-center text-gray-600 py-12'>Belum ada treatment yang tersedia saat ini.</p>";
                }
                } catch (PDOException $e) {
                    echo "<p class='col-span-full text-center text-red-600 py-12'>Error: " . htmlspecialchars($e->getMessage()) . "</p>";
                }
                ?>
            </div>

            <?php
            try {
                $total_treatments_stmt = $pdo->query("SELECT COUNT(*) AS total FROM treatments");
                $total_treatments = $total_treatments_stmt->fetchColumn();
                
                if ($total_treatments > 9) {
            ?>
            <div class="text-center mt-16">
                <a href="treatments.php" class="inline-flex items-center gap-2 px-8 py-3.5 border border-primary text-primary hover:bg-primary hover:text-white transition-all font-medium text-sm">
                    <i class="fas fa-eye"></i> Lihat Semua Treatment (<?= $total_treatments ?>)
                </a>
            </div>
            <?php
                }
            } catch (PDOException $e) {
                // Silently handle error or log it
            }
            ?>
        </div>
    </section>

    <!-- Testimonials -->
    <section class="py-20 lg:py-32 bg-secondary">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="text-center mb-16 lg:mb-20">
                <h2 class="text-4xl lg:text-5xl font-light tracking-tight mb-4">Apa Kata Mereka</h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">Testimoni asli dari pelanggan setia kami</p>
            </div>

            <div class="max-w-4xl mx-auto">
                <div class="testimonials-slider overflow-hidden">
                    <div class="testimonial-wrapper flex gap-8 transition-transform duration-600">
                        <?php
                        try {
                            $query = "SELECT s.feedback, s.rating, u.username AS name 
                                        FROM surveys s
                                        JOIN bookings b ON s.booking_id = b.id
                                        JOIN users u ON b.user_id = u.id
                                        ORDER BY s.id DESC
                                        LIMIT 5";

                            $stmt = $pdo->query($query);

                            if ($stmt->rowCount() > 0) {
                                while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                    $avatar_url = "https://ui-avatars.com/api/?name=" 
                                    . urlencode($row['name']) 
                                    . "&background=random&color=fff";
                        ?>

                        <div class="testimonial-card flex-shrink-0 w-full">
                            <div class="bg-white border border-gray-200 p-12">
                                <div class="mb-6 text-yellow-500">
                                    <?php for ($i=0; $i < $row['rating']; $i++): ?>
                                    <i class="fas fa-star"></i>
                                    <?php endfor; ?>
                                </div>
                                
                                <p class="text-lg italic text-primary leading-relaxed mb-8">
                                    "<?= htmlspecialchars($row['feedback']) ?>"
                                </p>

                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-full overflow-hidden border-2 border-gray-200">
                                        <img src="<?= $avatar_url ?>" alt="<?= htmlspecialchars($row['name']) ?>" class="w-full h-full object-cover">
                                    </div>
                                    <div>
                                        <h4 class="font-semibold"><?= htmlspecialchars($row['name']) ?></h4>
                                        <p class="text-sm text-gray-600">Pelanggan Terverifikasi</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <?php 
                            }
                        } else {
                            echo "<p class='text-center text-gray-600'>Belum ada ulasan.</p>";
                        }
                        } catch (PDOException $e) {
                            echo "<p class='text-center text-red-600'>Error loading testimonials.</p>";
                        }
                        ?>
                    </div>
                </div>

                <div class="slider-dots flex justify-center gap-3 mt-8"></div>
            </div>
        </div>
    </section>

    <!-- CTA -->
    <section class="py-20 lg:py-32 bg-primary text-white">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 text-center">
            <h2 class="text-4xl lg:text-5xl font-light tracking-tight mb-6">Siap untuk Tampil Cantik?</h2>
            <p class="text-lg mb-10 opacity-90 max-w-2xl mx-auto">Booking treatment sekarang dan dapatkan penawaran spesial untuk kunjungan pertama Anda</p>
            <a href="login.php" class="inline-flex items-center gap-2 px-8 py-3.5 bg-white text-primary hover:bg-gray-100 transition-all font-medium text-sm">
                Booking Sekarang
            </a>
        </div>
    </section>

    <!-- Newsletter Section -->
    <section class="py-20 bg-secondary">
        <div class="max-w-4xl mx-auto px-6 lg:px-8 text-center">
            <h2 class="text-2xl lg:text-3xl font-light tracking-tight mb-4">
                Bergabunglah dengan Newsletter kami & dapatkan informasi terkini
            </h2>
            
            <form class="max-w-2xl mx-auto mt-8 mb-4">
                <div class="flex gap-3">
                    <input type="email" 
                           placeholder="Tuliskan Email Anda" 
                           class="flex-1 px-6 py-4 border border-gray-300 focus:outline-none focus:border-accent text-sm"
                           required>
                    <button type="submit" 
                            class="px-8 py-4 bg-accent text-white hover:bg-accent/90 transition-all font-medium text-sm whitespace-nowrap">
                        SUBSCRIBE
                    </button>
                </div>
            </form>
            
            <p class="text-sm text-gray-500 mt-4">Anda dapat unsubscribe kapan saja</p>
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

    <!-- Scripts -->
    <script>
        // Mobile Menu Toggle
        document.querySelector('.mobile-toggle').addEventListener('click', function() {
            document.querySelector('.mobile-menu').classList.toggle('hidden');
        });

        // Testimonial Slider
        (function() {
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

            function calculateSizes() {
                const slider = wrapper.closest('.testimonials-slider');
                const containerWidth = slider ? slider.clientWidth : wrapper.clientWidth;
                slideWidth = containerWidth + 32; // 32px gap
            }

            function createDots() {
                if (!dotsContainer) return;
                dotsContainer.innerHTML = '';
                if (totalSlides <= 1) {
                    dotsContainer.classList.add('hidden');
                    return;
                }
                for (let i = 0; i < totalSlides; i++) {
                    const dot = document.createElement('span');
                    dot.className = i === 0 
                        ? 'w-6 h-2 bg-accent transition-all cursor-pointer' 
                        : 'w-2 h-2 bg-gray-300 rounded-full transition-all cursor-pointer';
                    dot.addEventListener('click', () => goToSlide(i));
                    dotsContainer.appendChild(dot);
                }
            }

            function updateDots() {
                if (!dotsContainer) return;
                const dots = dotsContainer.querySelectorAll('span');
                dots.forEach((dot, i) => {
                    if (i === currentSlide) {
                        dot.className = 'w-6 h-2 bg-accent transition-all cursor-pointer';
                    } else {
                        dot.className = 'w-2 h-2 bg-gray-300 rounded-full transition-all cursor-pointer';
                    }
                });
            }

            function updateSlider(animate = true) {
                wrapper.style.transition = animate ? 'transform 0.6s cubic-bezier(0.2, 0.9, 0.2, 1)' : 'none';
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

            function startDrag(e) {
                if (e.type === 'mousedown') e.preventDefault();
                clearInterval(slideInterval);
                isDragging = true;
                startX = e.type.includes('mouse') ? e.pageX : e.touches[0].clientX;
                wrapper.style.transition = 'none';
                calculateSizes();
            }

            function dragMove(e) {
                if (!isDragging) return;
                const currentX = e.type.includes('mouse') ? e.pageX : e.touches[0].clientX;
                const diff = currentX - startX;
                const x = -currentSlide * slideWidth + diff;
                wrapper.style.transform = `translateX(${x}px)`;
            }

            function endDrag(e) {
                if (!isDragging) return;
                isDragging = false;
                const endX = e.type.includes('mouse') ? e.pageX : (e.changedTouches ? e.changedTouches[0].clientX : startX);
                const diff = endX - startX;
                if (diff > 70 && currentSlide > 0) currentSlide--;
                else if (diff < -70 && currentSlide < totalSlides - 1) currentSlide++;
                updateSlider(true);
                resetAutoSlide();
            }

            calculateSizes();
            createDots();
            updateSlider(false);
            resetAutoSlide();

            wrapper.addEventListener('touchstart', startDrag, {passive: true});
            wrapper.addEventListener('mousedown', startDrag);
            wrapper.addEventListener('touchmove', dragMove, {passive: true});
            wrapper.addEventListener('mousemove', dragMove);
            wrapper.addEventListener('touchend', endDrag);
            wrapper.addEventListener('mouseup', endDrag);
            wrapper.addEventListener('mouseleave', endDrag);

            window.addEventListener('resize', () => {
                calculateSizes();
                updateSlider(false);
            });
        })();
    </script>

</body>
</html>