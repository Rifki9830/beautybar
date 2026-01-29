<?php

require_once 'config.php';
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
    html {
        scroll-behavior: smooth;
    }

    * {
        transition-timing-function: cubic-bezier(0.22, 1, 0.36, 1);
    }

    .navbar-blur {
        backdrop-filter: blur(10px);
        background-color: rgba(255, 255, 255, 0.95);
    }

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
        animation: fadeInUp 0.6s cubic-bezier(0.22, 1, 0.36, 1) forwards;
        opacity: 0;
    }

    .line-clamp-2 {
        display: -webkit-box;
        -webkit-line-clamp: 2;
        -webkit-box-orient: vertical;
        overflow: hidden;
    }

    .faq-answer {
        transition: max-height 0.3s ease;
    }

    .faq-icon {
        transition: transform 0.3s ease;
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
                    <a href="index.php"
                        class="text-gray-600 hover:text-primary font-medium text-sm transition-colors relative group">
                        Home
                        <span
                            class="absolute -bottom-2 left-0 w-full h-0.5 bg-accent scale-x-100 group-hover:scale-x-100 transition-transform origin-left"></span>
                    </a>
                    <a href="treatments.php"
                        class="text-gray-600 hover:text-primary font-medium text-sm transition-colors">Treatment</a>

                    <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="dashboard/member.php"
                        class="text-gray-600 hover:text-primary font-medium text-sm transition-colors">Dashboard</a>
                    <a href="logout.php"
                        class="px-6 py-2.5 border border-primary text-primary hover:bg-primary hover:text-white transition-all text-sm font-medium">
                        Logout
                    </a>
                    <?php else: ?>
                    <a href="login.php"
                        class="text-gray-600 hover:text-primary font-medium text-sm transition-colors">Login</a>
                    <a href="register.php"
                        class="px-6 py-2.5 bg-primary text-white hover:bg-black transition-all text-sm font-medium">
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
                    <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="dashboard/member.php"
                        class="text-gray-600 hover:text-primary font-medium text-sm">Dashboard</a>
                    <a href="logout.php"
                        class="px-6 py-2.5 border border-primary text-primary text-center text-sm font-medium">Logout</a>
                    <?php else: ?>
                    <a href="login.php" class="text-gray-600 hover:text-primary font-medium text-sm">Login</a>
                    <a href="register.php"
                        class="px-6 py-2.5 bg-primary text-white text-center text-sm font-medium">Daftar</a>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative pt-24 pb-16 lg:pt-28 lg:pb-24 min-h-[85vh] flex items-center overflow-hidden">
        <!-- Background Image with Parallax Effect -->
        <div class="absolute inset-0 z-0">
            <img src="https://images.unsplash.com/photo-1522337360788-8b13dee7a37e?auto=format&fit=crop&w=1600&q=80"
                alt="Beauty Treatment" class="w-full h-full object-cover scale-105">
            <div class="absolute inset-0 bg-gradient-to-br from-black/75 via-black/40 to-black/20"></div>
        </div>

        <!-- Decorative Elements -->
        <div class="absolute top-20 right-10 w-32 h-32 bg-accent/10 rounded-full blur-3xl"></div>
        <div class="absolute bottom-20 left-10 w-40 h-40 bg-accent/5 rounded-full blur-3xl"></div>

        <!-- Content -->
        <div class="max-w-7xl mx-auto px-6 lg:px-8 relative z-10 w-full">
            <div class="max-w-2xl">
                <!-- Badge -->
                <div
                    class="inline-flex items-center gap-2 px-4 py-2 bg-white/10 backdrop-blur-sm border border-white/20 rounded-full mb-6 animate-fade-in-up">
                    <span class="w-2 h-2 bg-accent rounded-full animate-pulse"></span>
                    <span class="text-white/90 text-sm font-medium">Premium Beauty Salon</span>
                </div>

                <!-- Main Heading -->
                <h1 class="text-4xl sm:text-5xl lg:text-6xl xl:text-7xl font-light tracking-tight leading-[1.1] mb-6 text-white animate-fade-in-up"
                    style="animation-delay: 0.1s">
                    Transform Your<br>
                    <span class="text-accent font-normal">Beauty</span> Experience
                </h1>

                <!-- Description -->
                <p class="text-base sm:text-lg text-white/80 mb-8 leading-relaxed max-w-xl animate-fade-in-up"
                    style="animation-delay: 0.2s">
                    Nikmati perawatan kecantikan premium dengan sentuhan profesional di Bandar Lampung
                </p>

                <!-- Info Cards -->
                <div class="grid sm:grid-cols-2 gap-4 mb-10 animate-fade-in-up" style="animation-delay: 0.3s">
                    <!-- Location Card -->
                    <div
                        class="flex items-start gap-3 p-4 bg-white/5 backdrop-blur-sm border border-white/10 rounded-lg hover:bg-white/10 transition-all group">
                        <div
                            class="w-10 h-10 flex items-center justify-center bg-accent/20 rounded-lg flex-shrink-0 group-hover:bg-accent/30 transition-colors">
                            <i class="fas fa-map-marker-alt text-accent"></i>
                        </div>
                        <div>
                            <p class="text-white/60 text-xs font-medium mb-1">Lokasi</p>
                            <p class="text-white text-sm leading-relaxed">JL. Mayor Sukardi Hamdani Palapa 10, Rajabasa
                            </p>
                        </div>
                    </div>

                    <!-- Schedule Card -->
                    <div
                        class="flex items-start gap-3 p-4 bg-white/5 backdrop-blur-sm border border-white/10 rounded-lg hover:bg-white/10 transition-all group">
                        <div
                            class="w-10 h-10 flex items-center justify-center bg-accent/20 rounded-lg flex-shrink-0 group-hover:bg-accent/30 transition-colors">
                            <i class="fas fa-clock text-accent"></i>
                        </div>
                        <div>
                            <p class="text-white/60 text-xs font-medium mb-1">Jam Operasional</p>
                            <p class="text-white text-sm">Setiap Hari: 09.00 - 21.00 WIB</p>
                        </div>
                    </div>
                </div>

                <!-- CTA Buttons -->
                <div class="flex flex-wrap gap-4 animate-fade-in-up" style="animation-delay: 0.4s">
                    <a href="login.php"
                        class="group inline-flex items-center gap-2 px-8 py-4 bg-white text-primary hover:bg-accent hover:text-white transition-all font-medium text-sm shadow-xl rounded-full hover:shadow-2xl hover:scale-105 transform">
                        <span>Booking Sekarang</span>
                        <i class="fas fa-arrow-right group-hover:translate-x-1 transition-transform"></i>
                    </a>
                    <a href="#treatments"
                        class="inline-flex items-center gap-2 px-8 py-4 bg-white/10 backdrop-blur-sm border-2 border-white/30 text-white hover:bg-white hover:text-primary transition-all font-medium text-sm rounded-full hover:border-white">
                        <i class="fas fa-sparkles"></i>
                        <span>Lihat Layanan</span>
                    </a>
                </div>

                <!-- Stats -->
                <div class="flex flex-wrap items-center gap-8 mt-12 pt-8 border-t border-white/20 animate-fade-in-up"
                    style="animation-delay: 0.5s">
                    <div>
                        <div class="text-3xl font-light text-white mb-1">5<span class="text-accent">+</span></div>
                        <div class="text-white/60 text-xs">Tahun Pengalaman</div>
                    </div>
                    <div class="w-px h-8 bg-white/20"></div>
                    <div>
                        <div class="text-3xl font-light text-white mb-1">1000<span class="text-accent">+</span></div>
                        <div class="text-white/60 text-xs">Pelanggan Puas</div>
                    </div>
                    <div class="w-px h-8 bg-white/20"></div>
                    <div>
                        <div class="text-3xl font-light text-white mb-1">20<span class="text-accent">+</span></div>
                        <div class="text-white/60 text-xs">Treatment Tersedia</div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scroll Indicator -->
        <div class="absolute bottom-8 left-1/2 -translate-x-1/2 animate-bounce z-10">
            <a href="#treatments"
                class="flex flex-col items-center gap-2 text-white/60 hover:text-white transition-colors">
                <span class="text-xs font-medium">Scroll Down</span>
                <i class="fas fa-chevron-down text-sm"></i>
            </a>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-20 lg:py-32 bg-secondary">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="text-center mb-16 lg:mb-20">
                <h2 class="text-4xl lg:text-5xl font-light tracking-tight mb-4">Mengapa Memilih Kami</h2>
                <p class="text-lg text-gray-600 max-w-2xl mx-auto">Kami memberikan pengalaman kecantikan terbaik untuk
                    Anda</p>
            </div>

            <div class="grid md:grid-cols-3 gap-8 lg:gap-12">
                <div class="bg-white p-10 text-center hover:-translate-y-2 transition-transform duration-300">
                    <div class="w-16 h-16 mx-auto mb-6 flex items-center justify-center">
                        <i class="fas fa-user-tie text-3xl text-accent"></i>
                    </div>
                    <h3 class="text-xl font-medium mb-3">Therapis Profesional</h3>
                    <p class="text-gray-600 leading-relaxed">Dilayani oleh tenaga ahli berpengalaman di bidang
                        kecantikan</p>
                </div>

                <div class="bg-white p-10 text-center hover:-translate-y-2 transition-transform duration-300">
                    <div class="w-16 h-16 mx-auto mb-6 flex items-center justify-center">
                        <i class="fas fa-spa text-3xl text-accent"></i>
                    </div>
                    <h3 class="text-xl font-medium mb-3">Perawatan Premium</h3>
                    <p class="text-gray-600 leading-relaxed">Produk berkualitas tinggi untuk hasil terbaik bagi kulit
                        Anda</p>
                </div>

                <div class="bg-white p-10 text-center hover:-translate-y-2 transition-transform duration-300">
                    <div class="w-16 h-16 mx-auto mb-6 flex items-center justify-center">
                        <i class="fas fa-heart text-3xl text-accent"></i>
                    </div>
                    <h3 class="text-xl font-medium mb-3">Pelayanan Terbaik</h3>
                    <p class="text-gray-600 leading-relaxed">Kenyamanan dan kepuasan pelanggan adalah prioritas utama
                        kami</p>
                </div>
            </div>
        </div>
    </section>

    <!-- Treatments Section -->
    <section class="py-16 lg:py-24 bg-gradient-to-b from-white to-secondary/30" id="treatments">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <!-- Header -->
            <div class="text-center mb-12 lg:mb-16">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-accent/10 rounded-full mb-4">
                    <i class="fas fa-sparkles text-accent text-sm"></i>
                    <span class="text-accent text-sm font-medium">Our Services</span>
                </div>
                <h2 class="text-3xl lg:text-5xl font-light tracking-tight mb-4">
                    Pilihan <span class="text-accent font-normal">Treatment</span> Kami
                </h2>
                <p class="text-base lg:text-lg text-gray-600 max-w-2xl mx-auto leading-relaxed">
                    Nikmati berbagai perawatan kecantikan dengan harga terjangkau dan hasil maksimal
                </p>
            </div>

            <!-- Treatments Grid - 4 Columns -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 lg:gap-8">
                <?php
                try {
                    $stmt = $pdo->query("SELECT t.*, c.name as category_name 
                                     FROM treatments t 
                                     LEFT JOIN categories c ON t.category_id = c.id
                                     ORDER BY t.id DESC 
                                     LIMIT 4");
                    $treatments = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    if (count($treatments) > 0) {
                        foreach ($treatments as $index => $row) {
                            $initial = substr($row['name'], 0, 1);
                            $image_src = $row['image']
                                ? 'assets/uploads/' . htmlspecialchars($row['image'])
                                : '';

                            // Stagger animation delay
                            $delay = ($index % 4) * 0.1;
                            ?>
                <div class="group bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-2xl transition-all duration-500 hover:-translate-y-2 animate-fade-in-up"
                    style="animation-delay: <?= $delay ?>s">

                    <!-- Image Container -->
                    <div class="relative h-56 bg-gradient-to-br from-gray-100 to-gray-200 overflow-hidden">
                        <?php if ($image_src): ?>
                        <img src="<?= $image_src ?>" alt="<?= htmlspecialchars($row['name']) ?>"
                            class="w-full h-full object-cover group-hover:scale-110 transition-transform duration-700">
                        <div class="absolute inset-0 bg-black/0 group-hover:bg-black/20 transition-colors duration-300">
                        </div>
                        <?php else: ?>
                        <div
                            class="w-full h-full flex items-center justify-center text-5xl font-light text-accent/80 group-hover:text-accent transition-colors">
                            <?= $initial ?>
                        </div>
                        <?php endif; ?>

                        <!-- Category Badge -->
                        <?php if ($row['category_name']): ?>
                        <div
                            class="absolute top-3 right-3 backdrop-blur-md bg-white/90 text-accent px-3 py-1.5 rounded-full text-xs font-semibold shadow-lg">
                            <?= htmlspecialchars($row['category_name']) ?>
                        </div>
                        <?php endif; ?>

                        <!-- Hover Overlay -->
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-5">
                        <h3 class="text-lg font-medium mb-3 line-clamp-2 group-hover:text-accent transition-colors">
                            <?= htmlspecialchars($row['name']) ?>
                        </h3>

                        <!-- Price & Duration -->
                        <div class="flex items-center justify-between mb-4 pb-4 border-b border-gray-100">
                            <div>
                                <p class="text-xs text-gray-500 mb-0.5">Mulai dari</p>
                                <p class="text-xl font-bold text-accent">
                                    Rp <?= number_format($row['price'], 0, ',', '.') ?>
                                </p>
                            </div>
                            <div class="flex items-center gap-1.5 text-gray-600 bg-gray-50 px-3 py-2 rounded-lg">
                                <i class="fas fa-clock text-xs"></i>
                                <span class="text-xs font-medium"><?= $row['duration'] ?>'</span>
                            </div>
                        </div>

                        <!-- CTA Button -->
                        <a href="login.php"
                            class="group/btn w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-primary text-white rounded-xl hover:bg-accent transition-all text-sm font-medium shadow-sm hover:shadow-md">
                            <i class="fas fa-calendar-check text-xs"></i>
                            <span>Book Now</span>
                            <i
                                class="fas fa-arrow-right text-xs opacity-0 -translate-x-2 group-hover/btn:opacity-100 group-hover/btn:translate-x-0 transition-all"></i>
                        </a>
                    </div>
                </div>
                <?php
                        }
                    } else {
                        echo "<div class='col-span-full text-center py-16'>
                            <i class='fas fa-spa text-4xl text-gray-300 mb-4'></i>
                            <p class='text-gray-600'>Belum ada treatment yang tersedia saat ini.</p>
                          </div>";
                    }
                } catch (PDOException $e) {
                    echo "<div class='col-span-full text-center py-16'>
                        <i class='fas fa-exclamation-circle text-4xl text-red-300 mb-4'></i>
                        <p class='text-red-600'>Error: " . htmlspecialchars($e->getMessage()) . "</p>
                      </div>";
                }
                ?>
            </div>

            <!-- View All Button -->
            <?php
            try {
                $total_treatments_stmt = $pdo->query("SELECT COUNT(*) AS total FROM treatments");
                $total_treatments = $total_treatments_stmt->fetchColumn();

                if ($total_treatments > 8) {
                    ?>
            <div class="text-center mt-12 lg:mt-16">
                <a href="treatments.php"
                    class="group inline-flex items-center gap-3 px-8 py-4 border-2 border-primary text-primary hover:bg-primary hover:text-white rounded-full transition-all font-medium text-sm shadow-sm hover:shadow-lg">
                    <i class="fas fa-th-large"></i>
                    <span>Lihat Semua Treatment</span>
                    <span
                        class="inline-flex items-center justify-center w-6 h-6 bg-primary text-white group-hover:bg-white group-hover:text-primary rounded-full text-xs font-bold transition-all">
                        <?= $total_treatments ?>
                    </span>
                </a>
            </div>
            <?php
                }
            } catch (PDOException $e) {
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
                                    <?php for ($i = 0; $i < $row['rating']; $i++): ?>
                                    <i class="fas fa-star"></i>
                                    <?php endfor; ?>
                                </div>

                                <p class="text-lg italic text-primary leading-relaxed mb-8">
                                    "<?= htmlspecialchars($row['feedback']) ?>"
                                </p>

                                <div class="flex items-center gap-4">
                                    <div class="w-12 h-12 rounded-full overflow-hidden border-2 border-gray-200">
                                        <img src="<?= $avatar_url ?>" alt="<?= htmlspecialchars($row['name']) ?>"
                                            class="w-full h-full object-cover">
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


    <!-- FAQ Section -->
    <section class="py-20 lg:py-32 bg-white">
        <div class="max-w-4xl mx-auto px-6 lg:px-8">
            <!-- Header -->
            <div class="text-center mb-12">
                <h2 class="text-4xl lg:text-5xl font-light tracking-tight mb-4">
                    Pertanyaan <span class="text-accent font-normal">Umum</span>
                </h2>
                <p class="text-lg text-gray-600">
                    Jawaban cepat untuk pertanyaan yang sering ditanyakan
                </p>
            </div>

            <!-- FAQ List -->
            <div class="space-y-4">
                <!-- FAQ Item 1 -->
                <div class="faq-item border border-gray-200 rounded-xl overflow-hidden">
                    <button
                        class="faq-question w-full flex items-center justify-between p-6 text-left hover:bg-gray-50 transition-colors">
                        <span class="text-lg font-medium pr-4">Bagaimana cara booking treatment?</span>
                        <i class="fas fa-chevron-down text-accent transition-transform duration-300 faq-icon"></i>
                    </button>
                    <div class="faq-answer max-h-0 overflow-hidden transition-all duration-300">
                        <div class="px-6 pb-6 text-gray-600 leading-relaxed">
                            Login ke website, pilih treatment yang diinginkan, pilih tanggal dan waktu yang tersedia,
                            lalu konfirmasi booking Anda. Anda juga bisa booking melalui WhatsApp atau datang langsung.
                        </div>
                    </div>
                </div>

                <!-- FAQ Item 2 -->
                <div class="faq-item border border-gray-200 rounded-xl overflow-hidden">
                    <button
                        class="faq-question w-full flex items-center justify-between p-6 text-left hover:bg-gray-50 transition-colors">
                        <span class="text-lg font-medium pr-4">Metode pembayaran apa yang tersedia?</span>
                        <i class="fas fa-chevron-down text-accent transition-transform duration-300 faq-icon"></i>
                    </button>
                    <div class="faq-answer max-h-0 overflow-hidden transition-all duration-300">
                        <div class="px-6 pb-6 text-gray-600 leading-relaxed">
                            Kami menerima pembayaran tunai, transfer bank (BCA), dan QRIS. Pembayaran dilakukan setelah
                            treatment selesai.
                        </div>
                    </div>
                </div>

                <!-- FAQ Item 3 -->
                <div class="faq-item border border-gray-200 rounded-xl overflow-hidden">
                    <button
                        class="faq-question w-full flex items-center justify-between p-6 text-left hover:bg-gray-50 transition-colors">
                        <span class="text-lg font-medium pr-4">Berapa lama durasi treatment?</span>
                        <i class="fas fa-chevron-down text-accent transition-transform duration-300 faq-icon"></i>
                    </button>
                    <div class="faq-answer max-h-0 overflow-hidden transition-all duration-300">
                        <div class="px-6 pb-6 text-gray-600 leading-relaxed">
                            Durasi bervariasi tergantung treatment: Eyelash Extensions (90-120 menit), Lash Lift (60-90
                            menit), Brow Bomber (45-60 menit), Nailart (60-90 menit), Remove Nailart (30-45 menit).
                        </div>
                    </div>
                </div>

                <!-- FAQ Item 4 -->
                <div class="faq-item border border-gray-200 rounded-xl overflow-hidden">
                    <button
                        class="faq-question w-full flex items-center justify-between p-6 text-left hover:bg-gray-50 transition-colors">
                        <span class="text-lg font-medium pr-4">Bagaimana jika ingin reschedule atau cancel?</span>
                        <i class="fas fa-chevron-down text-accent transition-transform duration-300 faq-icon"></i>
                    </button>
                    <div class="faq-answer max-h-0 overflow-hidden transition-all duration-300">
                        <div class="px-6 pb-6 text-gray-600 leading-relaxed">
                            Reschedule atau pembatalan gratis jika dilakukan minimal H-2. Pembatalan H-1 dikenakan biaya
                            25%, dan H-0 atau no-show dikenakan biaya 50%. Hubungi kami via WhatsApp atau dashboard
                            untuk reschedule.
                        </div>
                    </div>
                </div>

                <!-- FAQ Item 5 -->
                <div class="faq-item border border-gray-200 rounded-xl overflow-hidden">
                    <button
                        class="faq-question w-full flex items-center justify-between p-6 text-left hover:bg-gray-50 transition-colors">
                        <span class="text-lg font-medium pr-4">Apakah produk yang digunakan aman?</span>
                        <i class="fas fa-chevron-down text-accent transition-transform duration-300 faq-icon"></i>
                    </button>
                    <div class="faq-answer max-h-0 overflow-hidden transition-all duration-300">
                        <div class="px-6 pb-6 text-gray-600 leading-relaxed">
                            Semua produk telah tersertifikasi BPOM dan menggunakan brand internasional terpercaya.
                            Peralatan disterilkan setelah setiap penggunaan. Patch test tersedia untuk kulit sensitif.
                        </div>
                    </div>
                </div>

                <!-- FAQ Item 6 -->
                <div class="faq-item border border-gray-200 rounded-xl overflow-hidden">
                    <button
                        class="faq-question w-full flex items-center justify-between p-6 text-left hover:bg-gray-50 transition-colors">
                        <span class="text-lg font-medium pr-4">Apakah ada promo atau membership?</span>
                        <i class="fas fa-chevron-down text-accent transition-transform duration-300 faq-icon"></i>
                    </button>
                    <div class="faq-answer max-h-0 overflow-hidden transition-all duration-300">
                        <div class="px-6 pb-6 text-gray-600 leading-relaxed">
                            Ya! Kami punya diskon 20% untuk member baru, loyalty program dengan sistem poin, dan promo
                            bulanan. Follow Instagram kami @beautybar.bync untuk info promo terkini.
                        </div>
                    </div>
                </div>
            </div>

            <!-- Contact CTA -->
            <div class="mt-12 text-center p-8 bg-secondary rounded-2xl">
                <p class="text-gray-600 mb-4">Masih ada pertanyaan lain?</p>
                <a href="https://wa.me/+6288268195618"
                    class="inline-flex items-center gap-2 px-8 py-3 bg-green-500 text-white rounded-full hover:bg-green-600 transition-all font-medium">
                    <i class="fab fa-whatsapp"></i>
                    <span>Hubungi Kami</span>
                </a>
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
                    &copy; Beautybar <?= date('Y') ?>, All rights Reserved. Bandar Lampung - Indonesia
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
                dot.className = i === 0 ?
                    'w-6 h-2 bg-accent transition-all cursor-pointer' :
                    'w-2 h-2 bg-gray-300 rounded-full transition-all cursor-pointer';
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
            const endX = e.type.includes('mouse') ? e.pageX : (e.changedTouches ? e.changedTouches[0].clientX :
                startX);
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

        wrapper.addEventListener('touchstart', startDrag, {
            passive: true
        });
        wrapper.addEventListener('mousedown', startDrag);
        wrapper.addEventListener('touchmove', dragMove, {
            passive: true
        });
        wrapper.addEventListener('mousemove', dragMove);
        wrapper.addEventListener('touchend', endDrag);
        wrapper.addEventListener('mouseup', endDrag);
        wrapper.addEventListener('mouseleave', endDrag);

        window.addEventListener('resize', () => {
            calculateSizes();
            updateSlider(false);
        });

        document.addEventListener('DOMContentLoaded', function() {
            const faqItems = document.querySelectorAll('.faq-item');

            faqItems.forEach(item => {
                const question = item.querySelector('.faq-question');
                const answer = item.querySelector('.faq-answer');
                const icon = item.querySelector('.faq-icon');

                question.addEventListener('click', () => {
                    const isOpen = answer.style.maxHeight && answer.style.maxHeight !==
                        '0px';

                    // Close all others
                    faqItems.forEach(otherItem => {
                        if (otherItem !== item) {
                            otherItem.querySelector('.faq-answer').style.maxHeight =
                                '0';
                            otherItem.querySelector('.faq-icon').style.transform =
                                'rotate(0deg)';
                        }
                    });

                    // Toggle current
                    if (isOpen) {
                        answer.style.maxHeight = '0';
                        icon.style.transform = 'rotate(0deg)';
                    } else {
                        answer.style.maxHeight = answer.scrollHeight + 'px';
                        icon.style.transform = 'rotate(180deg)';
                    }
                });
            });
        });
    })();
    </script>

</body>

</html>