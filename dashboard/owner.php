<?php
require '../config.php';
checkAccess('owner');

// --- 1. HITUNG PENDAPATAN (REVENUE) ---
$sqlRevenue = "SELECT SUM(t.price) 
               FROM bookings b 
               JOIN treatments t ON b.treatment_id = t.id 
               WHERE b.is_paid = 1 OR b.status IN ('confirmed', 'completed')";
$totalRevenue = $pdo->query($sqlRevenue)->fetchColumn() ?? 0;

// Revenue bulan ini
$sqlMonthlyRevenue = "SELECT SUM(t.price) 
                      FROM bookings b 
                      JOIN treatments t ON b.treatment_id = t.id 
                      WHERE (b.is_paid = 1 OR b.status IN ('confirmed', 'completed'))
                      AND MONTH(b.created_at) = MONTH(CURRENT_DATE())
                      AND YEAR(b.created_at) = YEAR(CURRENT_DATE())";
$monthlyRevenue = $pdo->query($sqlMonthlyRevenue)->fetchColumn() ?? 0;

// --- 2. STATISTIK UMUM ---
$totalBookings = $pdo->query("SELECT COUNT(*) FROM bookings")->fetchColumn();
$completedBookings = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status='completed'")->fetchColumn();
$totalMembers = $pdo->query("SELECT COUNT(*) FROM users WHERE role='member'")->fetchColumn();

// --- 3. DATA KEPUASAN PELANGGAN ---
$ratingAvg = $pdo->query("SELECT AVG(rating) FROM surveys")->fetchColumn() ?? 0;
$totalSurveys = $pdo->query("SELECT COUNT(*) FROM surveys")->fetchColumn();

// --- 4. DATA GRAFIK KINERJA TERAPIS ---
$therapistLabels = [];
$therapistData = [];

$sqlChart = "SELECT th.name, COUNT(b.id) as jobs 
             FROM bookings b 
             JOIN therapists th ON b.therapist_id = th.id 
             WHERE b.status IN ('confirmed', 'completed') 
             GROUP BY th.name 
             ORDER BY jobs DESC";

$qChart = $pdo->query($sqlChart);
while($r = $qChart->fetch()){
    $therapistLabels[] = $r['name'];
    $therapistData[] = $r['jobs'];
}

// --- 5. DATA REVENUE CHART (7 hari terakhir) ---
$revenueChartData = [];
for($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $stmt = $pdo->prepare("SELECT SUM(t.price) as total 
                           FROM bookings b 
                           JOIN treatments t ON b.treatment_id = t.id 
                           WHERE DATE(b.created_at) = ? 
                           AND (b.is_paid = 1 OR b.status IN ('confirmed', 'completed'))");
    $stmt->execute([$date]);
    $revenueChartData[] = [
        'date' => date('D', strtotime($date)),
        'revenue' => $stmt->fetch()['total'] ?? 0
    ];
}

// --- 6. FEEDBACK TERBARU ---
$recentFeedback = $pdo->query("SELECT * FROM surveys 
                               ORDER BY id DESC 
                               LIMIT 5")->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner Dashboard - Beautybar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @media (max-width: 768px) {
            .sidebar {
                transform: translateX(-100%);
                transition: transform 0.3s;
                position: fixed;
                z-index: 50;
                height: 100vh;
            }
            .sidebar.active {
                transform: translateX(0);
            }
        }
    </style>
</head>
<body class="bg-gray-50">
    <!-- Mobile Menu Button -->
    <button id="menuBtn" class="md:hidden fixed top-4 left-4 z-50 bg-purple-600 text-white p-3 rounded-lg shadow-lg">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
        </svg>
    </button>

    <!-- Overlay -->
    <div id="overlay" class="hidden md:hidden fixed inset-0 bg-black bg-opacity-50 z-40"></div>

    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside id="sidebar" class="sidebar w-64 bg-white shadow-lg">
            <div class="p-6">
                <h2 class="text-2xl font-bold text-purple-600">Beautybar</h2>
                <p class="text-sm text-gray-500 mt-1">Owner Panel</p>
            </div>
            
            <div class="px-4 py-2">
                <div class="bg-purple-50 rounded-lg p-4 mb-4 border border-purple-100">
                    <p class="text-sm text-gray-600">Halo,</p>
                    <p class="font-semibold text-gray-800"><?php echo $_SESSION['name'] ?? 'Owner'; ?></p>
                    <p class="text-xs text-purple-600 mt-1">Executive Dashboard</p>
                </div>
            </div>

            <nav class="px-4">
                <a href="owner.php" 
                   class="flex items-center px-4 py-3 mb-2 rounded-lg bg-purple-600 text-white">
                    <span class="mr-3">📊</span>
                    <span>Laporan & Statistik</span>
                </a>
            </nav>

            <div class="px-4 mt-8 pt-8 border-t">
                <a href="../index.php" class="flex items-center px-4 py-3 mb-2 text-gray-700 rounded-lg hover:bg-gray-50">
                    <span class="mr-3">🏠</span>
                    <span>Halaman Utama</span>
                </a>
                <a href="../logout.php" class="flex items-center px-4 py-3 text-red-600 rounded-lg hover:bg-red-50">
                    <span class="mr-3">🚪</span>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-4 md:p-8 pt-20 md:pt-8">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Laporan Eksekutif</h1>
                <p class="text-gray-500 mt-1 text-sm md:text-base">Overview performa bisnis Beautybar</p>
            </div>

            <!-- Stats Cards - Main KPIs -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-6">
                <!-- Total Revenue -->
                <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-5 md:p-6 text-white">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-purple-100 text-sm font-medium">Total Pendapatan</p>
                        <div class="bg-white bg-opacity-20 rounded-full p-2">
                            <span class="text-xl">💰</span>
                        </div>
                    </div>
                    <h3 class="text-2xl md:text-3xl font-bold">Rp <?php echo number_format($totalRevenue, 0, ',', '.'); ?></h3>
                    <p class="text-xs text-purple-100 mt-2">Revenue keseluruhan</p>
                </div>

                <!-- Monthly Revenue -->
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-5 md:p-6 text-white">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-blue-100 text-sm font-medium">Revenue Bulan Ini</p>
                        <div class="bg-white bg-opacity-20 rounded-full p-2">
                            <span class="text-xl">📊</span>
                        </div>
                    </div>
                    <h3 class="text-2xl md:text-3xl font-bold">Rp <?php echo number_format($monthlyRevenue, 0, ',', '.'); ?></h3>
                    <p class="text-xs text-blue-100 mt-2"><?php echo date('F Y'); ?></p>
                </div>

                <!-- Customer Satisfaction -->
                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-5 md:p-6 text-white">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-green-100 text-sm font-medium">Kepuasan Pelanggan</p>
                        <div class="bg-white bg-opacity-20 rounded-full p-2">
                            <span class="text-xl">⭐</span>
                        </div>
                    </div>
                    <h3 class="text-2xl md:text-3xl font-bold"><?php echo number_format($ratingAvg, 1); ?> <span class="text-lg text-green-100">/5.0</span></h3>
                    <p class="text-xs text-green-100 mt-2"><?php echo $totalSurveys; ?> survei masuk</p>
                </div>

                <!-- Total Members -->
                <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl shadow-lg p-5 md:p-6 text-white">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-orange-100 text-sm font-medium">Total Members</p>
                        <div class="bg-white bg-opacity-20 rounded-full p-2">
                            <span class="text-xl">👥</span>
                        </div>
                    </div>
                    <h3 class="text-2xl md:text-3xl font-bold"><?php echo $totalMembers; ?></h3>
                    <p class="text-xs text-orange-100 mt-2">Pelanggan terdaftar</p>
                </div>
            </div>

            <!-- Stats Cards - Additional -->
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 md:gap-6 mb-6">
                <!-- Total Bookings -->
                <div class="bg-white rounded-xl shadow-md p-5 md:p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base md:text-lg font-semibold text-gray-800">Total Bookings</h3>
                        <span class="text-2xl">📅</span>
                    </div>
                    <p class="text-2xl md:text-3xl font-bold text-purple-600"><?php echo $totalBookings; ?></p>
                    <p class="text-sm text-gray-500 mt-2">Semua booking yang masuk</p>
                </div>

                <!-- Completed Bookings -->
                <div class="bg-white rounded-xl shadow-md p-5 md:p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base md:text-lg font-semibold text-gray-800">Completed Bookings</h3>
                        <span class="text-2xl">✅</span>
                    </div>
                    <p class="text-2xl md:text-3xl font-bold text-green-600"><?php echo $completedBookings; ?></p>
                    <p class="text-sm text-gray-500 mt-2">Treatment yang sudah selesai</p>
                </div>
            </div>

            <!-- Charts Section -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6 mb-6">
                <!-- Revenue Chart -->
                <div class="bg-white rounded-xl shadow-md p-5 md:p-6">
                    <h3 class="text-base md:text-lg font-semibold text-gray-800 mb-4">Pendapatan 7 Hari Terakhir</h3>
                    <div class="h-64">
                        <canvas id="revenueChart"></canvas>
                    </div>
                </div>

                <!-- Therapist Performance Chart -->
                <div class="bg-white rounded-xl shadow-md p-5 md:p-6">
                    <h3 class="text-base md:text-lg font-semibold text-gray-800 mb-4">Kinerja Terapis</h3>
                    <?php if(count($therapistLabels) > 0): ?>
                        <div class="h-64">
                            <canvas id="therapistChart"></canvas>
                        </div>
                    <?php else: ?>
                        <div class="flex items-center justify-center h-64">
                            <p class="text-gray-400">Belum ada data booking</p>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Recent Feedback -->
            <div class="bg-white rounded-xl shadow-md p-5 md:p-6">
                <h3 class="text-base md:text-lg font-semibold text-gray-800 mb-4">Feedback Pelanggan Terbaru</h3>
                <?php if(count($recentFeedback) > 0): ?>
                    <div class="space-y-4">
                        <?php foreach($recentFeedback as $feedback): ?>
                            <div class="border-b border-gray-100 pb-4 last:border-0">
                                <div class="flex items-start justify-between">
                                    <div class="flex-1">
                                        <div class="flex items-center gap-2 mb-2">
                                            <?php 
                                            $rating = $feedback['rating'];
                                            for($i = 1; $i <= 5; $i++) {
                                                if($i <= $rating) {
                                                    echo '<span class="text-yellow-400 text-base md:text-lg">★</span>';
                                                } else {
                                                    echo '<span class="text-gray-300 text-base md:text-lg">★</span>';
                                                }
                                            }
                                            ?>
                                            <span class="text-sm font-semibold text-gray-700 ml-1"><?php echo $rating; ?>/5</span>
                                        </div>
                                        <p class="text-sm md:text-base text-gray-600"><?php echo htmlspecialchars($feedback['feedback']); ?></p>
                                        <p class="text-xs text-gray-400 mt-2">Survey #<?php echo $feedback['id']; ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="text-center py-8">
                        <p class="text-gray-400">Belum ada feedback dari pelanggan</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <script>
        // Mobile Menu Toggle
        const menuBtn = document.getElementById('menuBtn');
        const sidebar = document.getElementById('sidebar');
        const overlay = document.getElementById('overlay');

        menuBtn.addEventListener('click', () => {
            sidebar.classList.toggle('active');
            overlay.classList.toggle('hidden');
        });

        overlay.addEventListener('click', () => {
            sidebar.classList.remove('active');
            overlay.classList.add('hidden');
        });

        // Revenue Chart
        const revenueCtx = document.getElementById('revenueChart').getContext('2d');
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($revenueChartData, 'date')); ?>,
                datasets: [{
                    label: 'Pendapatan (Rp)',
                    data: <?php echo json_encode(array_column($revenueChartData, 'revenue')); ?>,
                    borderColor: 'rgb(147, 51, 234)',
                    backgroundColor: 'rgba(147, 51, 234, 0.1)',
                    tension: 0.4,
                    fill: true,
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    },
                    tooltip: {
                        callbacks: {
                            label: function(context) {
                                return 'Rp ' + context.parsed.y.toLocaleString('id-ID');
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            callback: function(value) {
                                return 'Rp ' + value.toLocaleString('id-ID');
                            }
                        }
                    }
                }
            }
        });

        // Therapist Performance Chart
        <?php if(count($therapistLabels) > 0): ?>
        const therapistCtx = document.getElementById('therapistChart').getContext('2d');
        new Chart(therapistCtx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($therapistLabels); ?>,
                datasets: [{
                    label: 'Jumlah Pekerjaan',
                    data: <?php echo json_encode($therapistData); ?>,
                    backgroundColor: 'rgba(147, 51, 234, 0.8)',
                    borderColor: 'rgb(147, 51, 234)',
                    borderWidth: 2
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: false
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>