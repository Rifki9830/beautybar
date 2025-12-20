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
    <title><?= htmlspecialchars($page_title) ?> - Beautybar.bync</title>
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
        animation: fadeInUp 0.6s ease-out;
    }

    @keyframes shimmer {
        0% {
            background-position: -1000px 0;
        }

        100% {
            background-position: 1000px 0;
        }
    }

    .card-shimmer {
        animation: shimmer 2s infinite;
        background: linear-gradient(to right, #f6f7f8 0%, #edeef1 20%, #f6f7f8 40%, #f6f7f8 100%);
        background-size: 1000px 100%;
    }

    .treatment-card {
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    }

    .treatment-card:hover {
        transform: translateY(-8px);
        box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
    }

    .category-pill {
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }

    .category-pill::before {
        content: '';
        position: absolute;
        top: 0;
        left: -100%;
        width: 100%;
        height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255, 255, 255, 0.3), transparent);
        transition: left 0.5s;
    }

    .category-pill:hover::before {
        left: 100%;
    }

    /* View Toggle Styles */
    .view-btn {
        transition: all 0.2s;
    }

    .view-btn.active {
        background: #1a1a1a;
        color: white;
    }

    /* Grid View Transitions */
    .treatments-grid.list-view {
        display: flex;
        flex-direction: column;
        gap: 1rem;
    }

    .treatments-grid.list-view .treatment-card {
        display: flex;
        flex-direction: row;
        max-width: 100%;
    }

    .treatments-grid.list-view .treatment-image {
        width: 200px;
        min-height: 160px;
        flex-shrink: 0;
    }

    .treatments-grid.list-view .treatment-content {
        flex: 1;
        display: flex;
        flex-direction: row;
        align-items: center;
        gap: 1.5rem;
        padding: 1.25rem 1.5rem;
    }

    .treatments-grid.list-view .treatment-info {
        flex: 1;
        display: flex;
        flex-direction: column;
        gap: 0.5rem;
    }

    .treatments-grid.list-view .treatment-meta-wrapper {
        display: flex;
        gap: 2rem;
        align-items: center;
    }

    .treatments-grid.list-view .treatment-price-duration {
        display: flex;
        gap: 2rem;
        align-items: center;
    }

    .treatments-grid.list-view .treatment-actions {
        flex-shrink: 0;
    }

    .treatments-grid.list-view h3 {
        font-size: 1.125rem;
        margin-bottom: 0.5rem;
    }

    .treatments-grid.list-view .border-b {
        border: none;
        margin: 0;
        padding: 0;
    }

    @media (max-width: 768px) {
        .treatments-grid.list-view .treatment-card {
            flex-direction: column;
        }

        .treatments-grid.list-view .treatment-image {
            width: 100%;
            min-height: 180px;
        }

        .treatments-grid.list-view .treatment-content {
            flex-direction: column;
            align-items: flex-start;
            gap: 1rem;
        }

        .treatments-grid.list-view .treatment-actions {
            width: 100%;
        }

        .treatments-grid.list-view .treatment-meta-wrapper {
            flex-direction: column;
            gap: 1rem;
            align-items: flex-start;
            width: 100%;
        }

        .treatments-grid.list-view .treatment-price-duration {
            width: 100%;
            justify-content: space-between;
        }
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
                        class="text-gray-600 hover:text-primary font-medium text-sm transition-colors">Home</a>
                    <a href="treatments.php"
                        class="text-gray-600 hover:text-primary font-medium text-sm transition-colors relative group">
                        Treatment
                        <span
                            class="absolute -bottom-2 left-0 w-full h-0.5 bg-accent scale-x-100 transition-transform origin-left"></span>
                    </a>

                    <?php if(isset($_SESSION['user_id'])): ?>
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
                    <?php if(isset($_SESSION['user_id'])): ?>
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

    <!-- Hero Section dengan Search -->
    <section class="relative pt-32 pb-20 lg:pb-28 min-h-[450px] flex items-center">
        <!-- Background Image -->
        <div class="absolute inset-0 z-0">
            <img src="https://images.unsplash.com/photo-1560750588-73207b1ef5b8?auto=format&fit=crop&w=1600&q=80"
                alt="Beauty Treatments" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-r from-black/75 via-black/60 to-black/40"></div>
        </div>

        <!-- Content -->
        <div class="max-w-7xl mx-auto px-6 lg:px-8 relative z-10 w-full">
            <div class="max-w-3xl mx-auto text-center animate-fade-in-up">
                <h1 class="text-5xl lg:text-6xl font-light tracking-tight leading-tight mb-4 text-white">
                    <?= htmlspecialchars($page_title) ?>
                </h1>
                <p class="text-lg text-white/90 mb-10 leading-relaxed">
                    Temukan perawatan kecantikan yang sempurna untuk Anda
                </p>

                <!-- Search Bar -->
                <div class="max-w-2xl mx-auto mb-8">
                    <div class="relative">
                        <input type="text" id="searchInput"
                            placeholder="Cari treatment (contoh: facial, hair spa, massage...)"
                            class="w-full px-6 py-4 pr-12 rounded-full bg-white/95 backdrop-blur-sm border-2 border-white/20 focus:border-accent focus:outline-none text-gray-800 placeholder-gray-500 text-sm">
                        <i class="fas fa-search absolute right-6 top-1/2 -translate-y-1/2 text-gray-400"></i>
                    </div>
                    <p class="text-white/70 text-xs mt-3">
                        <span id="resultCount"><?= count($treatments) ?></span> treatment tersedia
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Category Pills - Horizontal Scroll -->
    <section class="bg-white py-6 sticky top-20 z-40 border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="flex items-center justify-between gap-4 mb-4 md:mb-0">
                <div class="flex-1 overflow-x-auto scrollbar-hide">
                    <div class="flex gap-3 pb-2">
                        <a href="treatments.php"
                            class="category-pill inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-medium whitespace-nowrap transition-all <?= $selected_category == 0 ? 'bg-primary text-white shadow-lg' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>"
                            data-category="0">
                            <i class="fas fa-th-large"></i>
                            Semua
                            <span
                                class="px-2 py-0.5 rounded-full text-xs font-semibold <?= $selected_category == 0 ? 'bg-white/20' : 'bg-white' ?>">
                                <?= count($pdo->query("SELECT * FROM treatments")->fetchAll()) ?>
                            </span>
                        </a>

                        <?php foreach ($categories as $cat): ?>
                        <?php if ($cat['treatment_count'] > 0): ?>
                        <a href="treatments.php?category=<?= $cat['id'] ?>"
                            class="category-pill inline-flex items-center gap-2 px-5 py-2.5 rounded-full text-sm font-medium whitespace-nowrap transition-all <?= $selected_category == $cat['id'] ? 'bg-primary text-white shadow-lg' : 'bg-gray-100 text-gray-700 hover:bg-gray-200' ?>"
                            data-category="<?= $cat['id'] ?>">
                            <i class="fas fa-tag"></i>
                            <?= htmlspecialchars($cat['name']) ?>
                            <span
                                class="px-2 py-0.5 rounded-full text-xs font-semibold <?= $selected_category == $cat['id'] ? 'bg-white/20' : 'bg-white' ?>">
                                <?= $cat['treatment_count'] ?>
                            </span>
                        </a>
                        <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- View Toggle -->
                <div class="hidden md:flex items-center gap-2 bg-gray-100 rounded-lg p-1">
                    <button class="view-btn active px-3 py-2 rounded-md" data-view="grid">
                        <i class="fas fa-th"></i>
                    </button>
                    <button class="view-btn px-3 py-2 rounded-md" data-view="list">
                        <i class="fas fa-list"></i>
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Treatments Grid -->
    <section class="py-16 lg:py-20 bg-secondary">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <?php if (count($treatments) > 0): ?>
            <div class="treatments-grid grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-6">
                <?php foreach ($treatments as $treatment): ?>
                <?php
                    $initial = substr($treatment['name'], 0, 1);
                    $image_src = $treatment['image'] 
                        ? 'assets/uploads/' . htmlspecialchars($treatment['image']) 
                        : '';
                ?>
                <div class="treatment-card bg-white rounded-xl overflow-hidden shadow-sm hover:shadow-2xl group"
                    data-category-id="<?= (int)$treatment['category_id'] ?>"
                    data-name="<?= strtolower(htmlspecialchars($treatment['name'])) ?>">

                    <div
                        class="treatment-image relative h-48 bg-gradient-to-br from-gray-100 to-gray-200 overflow-hidden">
                        <?php if ($image_src): ?>
                        <img src="<?= $image_src ?>" alt="<?= htmlspecialchars($treatment['name']) ?>"
                            class="w-full h-full object-cover">
                        <?php else: ?>
                        <div class="w-full h-full flex items-center justify-center">
                            <div class="text-5xl font-light text-accent/30"><?= $initial ?></div>
                        </div>
                        <?php endif; ?>

                        <!-- Gradient Overlay -->
                        <div
                            class="absolute inset-0 bg-gradient-to-t from-black/60 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        </div>

                        <?php if ($treatment['category_name']): ?>
                        <div
                            class="absolute top-3 left-3 bg-white/95 backdrop-blur-sm text-accent px-2.5 py-1 rounded-full text-xs font-bold shadow-lg">
                            <i class="fas fa-tag mr-0.5 text-xs"></i>
                            <?= htmlspecialchars($treatment['category_name']) ?>
                        </div>
                        <?php endif; ?>
<<<<<<< HEAD
=======

                        <!-- Quick View Button -->
                        <div
                            class="absolute bottom-4 right-4 opacity-0 group-hover:opacity-100 transform translate-y-2 group-hover:translate-y-0 transition-all duration-300">
                            <button
                                class="bg-white text-primary px-4 py-2 rounded-full text-sm font-semibold shadow-xl hover:bg-accent hover:text-white transition-colors">
                                <i class="fas fa-eye mr-1"></i> Lihat Detail
                            </button>
                        </div>
>>>>>>> 01ffab3acc2e020e72f7a794aeb9b7332ca16afd
                    </div>

                    <div class="treatment-content p-5">
                        <div class="treatment-info">
                            <h3
                                class="text-base font-semibold mb-2 text-gray-800 group-hover:text-accent transition-colors line-clamp-2">
                                <?= htmlspecialchars($treatment['name']) ?>
                            </h3>

                            <div class="treatment-meta-wrapper">
                                <div
                                    class="treatment-price-duration flex items-center justify-between mb-4 pb-3 border-b border-gray-100">
                                    <div>
                                        <p class="text-xs text-gray-500 mb-0.5">Mulai dari</p>
                                        <span class="text-lg font-bold text-accent">
                                            Rp <?= number_format($treatment['price'], 0, ',', '.') ?>
                                        </span>
                                    </div>
                                    <div class="text-right">
                                        <p class="text-xs text-gray-500 mb-0.5">Durasi</p>
                                        <span class="flex items-center gap-1 text-gray-700 text-sm font-medium">
                                            <i class="fas fa-clock text-accent text-xs"></i>
                                            <?= $treatment['duration'] ?>'
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="treatment-actions">
                            <a href="login.php" class="w-full inline-flex items-center justify-center gap-2 px-4 py-2.5 bg-primary text-white rounded-lg hover:bg-black transition-all text-xs font-semibold shadow-md hover:shadow-xl group">
                                <i class="fas fa-calendar-check"></i> 
                                Booking Sekarang
                            </a>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- No Results Message -->
            <div id="noResults" class="hidden text-center py-20">
                <i class="fas fa-search text-7xl text-gray-300 mb-6"></i>
                <h3 class="text-2xl font-medium text-gray-700 mb-3">Tidak Ada Hasil</h3>
                <p class="text-gray-600 mb-8">Coba kata kunci lain atau lihat semua treatment</p>
                <button onclick="document.getElementById('searchInput').value=''; searchTreatments();"
                    class="inline-flex items-center gap-2 px-8 py-3.5 bg-primary text-white hover:bg-black transition-all text-sm font-medium rounded-lg">
                    <i class="fas fa-redo"></i> Reset Pencarian
                </button>
            </div>

            <?php else: ?>
            <div class="text-center py-20">
                <i class="fas fa-inbox text-7xl text-gray-300 mb-6"></i>
                <h3 class="text-2xl font-medium text-gray-700 mb-3">Belum Ada Treatment</h3>
                <p class="text-gray-600 mb-8">Kategori ini belum memiliki treatment. Silakan pilih kategori lain.</p>
                <a href="treatments.php"
                    class="inline-flex items-center gap-2 px-8 py-3.5 bg-primary text-white hover:bg-black transition-all text-sm font-medium rounded-lg">
                    <i class="fas fa-th"></i> Lihat Semua Treatment
                </a>
            </div>
            <?php endif; ?>
        </div>
    </section>

<<<<<<< HEAD
=======
    <!-- CTA -->
    <section class="py-20 lg:py-32 bg-primary text-white">
        <div class="max-w-7xl mx-auto px-6 lg:px-8 text-center">
            <h2 class="text-4xl lg:text-5xl font-light tracking-tight mb-6">Siap untuk Tampil Cantik?</h2>
            <p class="text-lg mb-10 opacity-90 max-w-2xl mx-auto">Booking treatment sekarang dan dapatkan penawaran
                spesial untuk kunjungan pertama Anda</p>
            <a href="login.php"
                class="inline-flex items-center gap-2 px-8 py-3.5 bg-white text-primary hover:bg-gray-100 transition-all font-medium text-sm rounded-lg shadow-xl">
                <i class="fas fa-calendar-check"></i>
                Booking Sekarang
            </a>
        </div>
    </section>

>>>>>>> 01ffab3acc2e020e72f7a794aeb9b7332ca16afd
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

    // Search Functionality
    const searchInput = document.getElementById('searchInput');
    const cards = document.querySelectorAll('.treatment-card');
    const resultCount = document.getElementById('resultCount');
    const noResults = document.getElementById('noResults');
    const treatmentsGrid = document.querySelector('.treatments-grid');

    function searchTreatments() {
        const searchTerm = searchInput.value.toLowerCase().trim();
        // Ambil ID kategori yang sedang aktif dari tombol kategori yang berwarna 'primary'
        const activeCategoryBtn = Array.from(filterButtons).find(btn => btn.classList.contains('bg-primary'));
        const activeCategoryId = activeCategoryBtn ? parseInt(activeCategoryBtn.getAttribute('data-category'), 10) : 0;

        let visibleCount = 0;

        cards.forEach(card => {
            const name = card.getAttribute('data-name');
            const cardCategoryId = parseInt(card.getAttribute('data-category-id') || 0, 10);

            // Cek apakah kartu sesuai dengan kategori yang aktif
            const matchesCategory = (activeCategoryId === 0 || cardCategoryId === activeCategoryId);
            // Cek apakah kartu sesuai dengan kata kunci pencarian
            const matchesSearch = (searchTerm === '' || name.includes(searchTerm));

            if (matchesCategory && matchesSearch) {
                card.style.display = '';
                card.classList.remove('search-hidden', 'category-hidden');
                visibleCount++;
            } else {
                card.style.display = 'none';
                // Tandai alasan disembunyikan untuk keperluan debugging/logika lain
                if (!matchesSearch) card.classList.add('search-hidden');
                if (!matchesCategory) card.classList.add('category-hidden');
            }
        });

        resultCount.textContent = visibleCount;

        // Tampilkan/Sembunyikan pesan "Tidak Ada Hasil"
        if (visibleCount === 0) {
            treatmentsGrid.style.display = 'none';
            noResults.classList.remove('hidden');
        } else {
            treatmentsGrid.style.display = '';
            noResults.classList.add('hidden');
        }
    }

    searchInput.addEventListener('input', searchTreatments);

    // Category Filter
    const filterButtons = document.querySelectorAll('.category-pill');

    function setActiveButton(activeBtn) {
        filterButtons.forEach(btn => {
            if (btn === activeBtn) {
                btn.classList.remove('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
                btn.classList.add('bg-primary', 'text-white', 'shadow-lg');
            } else {
                btn.classList.remove('bg-primary', 'text-white', 'shadow-lg');
                btn.classList.add('bg-gray-100', 'text-gray-700', 'hover:bg-gray-200');
            }
        });
    }

    function filterByCategory(catId) {
        const id = parseInt(catId, 10);
        let visibleCount = 0;

        cards.forEach(card => {
            const c = parseInt(card.getAttribute('data-category-id') || 0, 10);
            const searchHidden = card.classList.contains('search-hidden');

            if (id === 0 || c === id) {
                card.classList.remove('category-hidden');
                if (!searchHidden) {
                    card.style.display = '';
                    visibleCount++;
                }
            } else {
                card.style.display = 'none';
                card.classList.add('category-hidden');
            }
        });

        resultCount.textContent = visibleCount;

        if (visibleCount === 0) {
            treatmentsGrid.style.display = 'none';
            noResults.classList.remove('hidden');
        } else {
            treatmentsGrid.style.display = '';
            noResults.classList.add('hidden');
        }
    }

    filterButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const cat = this.getAttribute('data-category');
            setActiveButton(this);
            filterByCategory(cat);

            // Clear search
            searchInput.value = '';
            cards.forEach(card => card.classList.remove('search-hidden'));

            // Update URL without reload
            const url = cat == 0 ? 'treatments.php' : `treatments.php?category=${cat}`;
            window.history.pushState({}, '', url);
        });
    });

    // View Toggle (Grid/List)
    const viewButtons = document.querySelectorAll('.view-btn');
    const grid = document.querySelector('.treatments-grid');

    viewButtons.forEach(btn => {
        btn.addEventListener('click', function() {
            const view = this.getAttribute('data-view');

            viewButtons.forEach(b => b.classList.remove('active'));
            this.classList.add('active');

            if (view === 'list') {
                grid.classList.add('list-view');
                grid.classList.remove('grid', 'grid-cols-1', 'sm:grid-cols-2', 'lg:grid-cols-4',
                    'xl:grid-cols-5');
            } else {
                grid.classList.remove('list-view');
                grid.classList.add('grid', 'grid-cols-1', 'sm:grid-cols-2', 'lg:grid-cols-4',
                    'xl:grid-cols-5');
            }
        });
    });

    // Initialize filter on page load
    (function initFromServer() {
        const active = Array.from(filterButtons).find(b => b.classList.contains('bg-primary'));
        if (active) {
            const cat = active.getAttribute('data-category');
            filterByCategory(cat);
        }
    })();
    </script>

</body>

</html>