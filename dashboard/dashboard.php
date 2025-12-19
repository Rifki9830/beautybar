<?php
require __DIR__ . '/../config.php';
checkAccess('admin');

// Ambil statistik
$stats = [];

// Total Members
$stmt = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role='member'");
$stats['total_members'] = $stmt->fetch()['total'];

// Total Treatments
$stmt = $pdo->query("SELECT COUNT(*) as total FROM treatments");
$stats['total_treatments'] = $stmt->fetch()['total'];

// Total Bookings
$stmt = $pdo->query("SELECT COUNT(*) as total FROM bookings");
$stats['total_bookings'] = $stmt->fetch()['total'];

// Pending Bookings
$stmt = $pdo->query("SELECT COUNT(*) as total FROM bookings WHERE status='pending'");
$stats['pending_bookings'] = $stmt->fetch()['total'];

// Completed Bookings
$stmt = $pdo->query("SELECT COUNT(*) as total FROM bookings WHERE status='completed'");
$stats['completed_bookings'] = $stmt->fetch()['total'];

// Total Revenue (dari bookings yang completed dan paid)
$stmt = $pdo->query("SELECT SUM(t.price) as total 
                     FROM bookings b 
                     JOIN treatments t ON b.treatment_id = t.id 
                     WHERE b.status='completed' AND b.is_paid=1");
$stats['total_revenue'] = $stmt->fetch()['total'] ?? 0;

// Revenue bulan ini
$stmt = $pdo->query("SELECT SUM(t.price) as total 
                     FROM bookings b 
                     JOIN treatments t ON b.treatment_id = t.id 
                     WHERE b.status='completed' 
                     AND b.is_paid=1 
                     AND MONTH(b.created_at) = MONTH(CURRENT_DATE())
                     AND YEAR(b.created_at) = YEAR(CURRENT_DATE())");
$stats['monthly_revenue'] = $stmt->fetch()['total'] ?? 0;

// Data untuk chart (7 hari terakhir)
$chartData = [];
for ($i = 6; $i >= 0; $i--) {
    $date = date('Y-m-d', strtotime("-$i days"));
    $stmt = $pdo->prepare("SELECT COUNT(*) as total FROM bookings WHERE DATE(created_at) = ?");
    $stmt->execute([$date]);
    $chartData[] = [
        'date' => date('D', strtotime($date)),
        'count' => $stmt->fetch()['total']
    ];
}

// Booking terbaru
$recentBookings = $pdo->query("SELECT b.*, u.username, t.name as treatment_name, t.price
                               FROM bookings b
                               JOIN users u ON b.user_id = u.id
                               JOIN treatments t ON b.treatment_id = t.id
                               ORDER BY b.created_at DESC
                               LIMIT 5")->fetchAll();

$page = 'dashboard';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - Beautybar Admin</title>
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

<body class="bg-gray-100">
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
                <p class="text-sm text-gray-500 mt-1">Admin Panel</p>
            </div>

            <div class="px-4 py-2">
                <div class="bg-purple-50 rounded-lg p-4 mb-4">
                    <p class="text-sm text-gray-600">Halo,</p>
                    <p class="font-semibold text-gray-800"><?php echo $_SESSION['name'] ?? 'Admin'; ?></p>
                </div>
            </div>

            <nav class="px-4">
                <a href="dashboard.php" class="flex items-center px-4 py-3 mb-2 rounded-lg bg-purple-600 text-white">
                    <span class="mr-3">📊</span>
                    <span>Dashboard</span>
                </a>

                <a href="admin.php?page=bookings"
                    class="flex items-center px-4 py-3 mb-2 rounded-lg text-gray-700 hover:bg-gray-100">
                    <span class="mr-3">📅</span>
                    <span>Kelola Booking</span>
                </a>

                <a href="categories.php"
                    class="flex items-center px-4 py-3 mb-2 rounded-lg text-gray-700 hover:bg-gray-100">
                    <span class="mr-3">🏷️</span>
                    <span>Kelola Kategori</span>
                </a>

                <a href="admin.php?page=treatments"
                    class="flex items-center px-4 py-3 mb-2 rounded-lg text-gray-700 hover:bg-gray-100">
                    <span class="mr-3">💆</span>
                    <span>Kelola Treatment</span>
                </a>

                <a href="therapists.php"
                    class="flex items-center px-4 py-3 mb-2 rounded-lg text-gray-700 hover:bg-gray-100">
                    <span class="mr-3">👨‍⚕️</span>
                    <span>Kelola Terapis</span>
                </a>

                <a href="admin.php?page=members"
                    class="flex items-center px-4 py-3 mb-2 rounded-lg text-gray-700 hover:bg-gray-100">
                    <span class="mr-3">👥</span>
                    <span>Kelola Member</span>
                </a>
            </nav>

            <div class="px-4 mt-8 pt-8 border-t">
                <a href="../index.php" class="flex items-center px-4 py-3 mb-2 text-gray-700 rounded-lg hover:bg-gray-100">
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
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Dashboard Overview</h1>
                <p class="text-gray-500 mt-1 text-sm md:text-base">Selamat datang di panel admin Beautybar</p>
            </div>

            <!-- Stats Cards Row 1 -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-6">
                <!-- Total Members -->
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-5 md:p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-blue-100 text-sm font-medium">Total Members</p>
                            <h3 class="text-2xl md:text-3xl font-bold mt-2"><?php echo $stats['total_members']; ?></h3>
                            <p class="text-blue-100 text-xs mt-1">Pengguna terdaftar</p>
                        </div>
                        <div class="bg-white bg-opacity-20 rounded-full p-3 md:p-4">
                            <svg class="w-6 h-6 md:w-8 md:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Total Treatments -->
                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-5 md:p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-green-100 text-sm font-medium">Total Treatments</p>
                            <h3 class="text-2xl md:text-3xl font-bold mt-2"><?php echo $stats['total_treatments']; ?></h3>
                            <p class="text-green-100 text-xs mt-1">Layanan tersedia</p>
                        </div>
                        <div class="bg-white bg-opacity-20 rounded-full p-3 md:p-4">
                            <svg class="w-6 h-6 md:w-8 md:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Total Revenue -->
                <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-5 md:p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-purple-100 text-sm font-medium">Total Revenue</p>
                            <h3 class="text-xl md:text-2xl font-bold mt-2">Rp
                                <?php echo number_format($stats['total_revenue'], 0, ',', '.'); ?></h3>
                            <p class="text-purple-100 text-xs mt-1">Pendapatan keseluruhan</p>
                        </div>
                        <div class="bg-white bg-opacity-20 rounded-full p-3 md:p-4">
                            <svg class="w-6 h-6 md:w-8 md:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z">
                                </path>
                            </svg>
                        </div>
                    </div>
                </div>

                <!-- Pending Bookings -->
                <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl shadow-lg p-5 md:p-6 text-white">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-orange-100 text-sm font-medium">Pending Bookings</p>
                            <h3 class="text-2xl md:text-3xl font-bold mt-2"><?php echo $stats['pending_bookings']; ?></h3>
                            <p class="text-orange-100 text-xs mt-1">Menunggu konfirmasi</p>
                        </div>
                        <div class="bg-white bg-opacity-20 rounded-full p-3 md:p-4">
                            <svg class="w-6 h-6 md:w-8 md:h-8" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Stats Cards Row 2 -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6 mb-6">
                <!-- Monthly Revenue -->
                <div class="bg-white rounded-xl shadow-md p-5 md:p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base md:text-lg font-semibold text-gray-800">Revenue Bulan Ini</h3>
                        <span class="text-2xl">💰</span>
                    </div>
                    <p class="text-2xl md:text-3xl font-bold text-purple-600">Rp
                        <?php echo number_format($stats['monthly_revenue'], 0, ',', '.'); ?></p>
                    <p class="text-sm text-gray-500 mt-2"><?php echo date('F Y'); ?></p>
                </div>

                <!-- Total Bookings -->
                <div class="bg-white rounded-xl shadow-md p-5 md:p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base md:text-lg font-semibold text-gray-800">Total Bookings</h3>
                        <span class="text-2xl">📅</span>
                    </div>
                    <p class="text-2xl md:text-3xl font-bold text-blue-600"><?php echo $stats['total_bookings']; ?></p>
                    <p class="text-sm text-gray-500 mt-2">Semua booking</p>
                </div>

                <!-- Completed Bookings -->
                <div class="bg-white rounded-xl shadow-md p-5 md:p-6">
                    <div class="flex items-center justify-between mb-4">
                        <h3 class="text-base md:text-lg font-semibold text-gray-800">Completed</h3>
                        <span class="text-2xl">✅</span>
                    </div>
                    <p class="text-2xl md:text-3xl font-bold text-green-600"><?php echo $stats['completed_bookings']; ?></p>
                    <p class="text-sm text-gray-500 mt-2">Booking selesai</p>
                </div>
            </div>

            <!-- Charts and Recent Activity -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4 md:gap-6 mb-6">
                <!-- Booking Chart -->
                <div class="bg-white rounded-xl shadow-md p-5 md:p-6">
                    <h3 class="text-base md:text-lg font-semibold text-gray-800 mb-4">Booking 7 Hari Terakhir</h3>
                    <div class="h-64">
                        <canvas id="bookingChart"></canvas>
                    </div>
                </div>

                <!-- Recent Bookings -->
                <div class="bg-white rounded-xl shadow-md p-5 md:p-6">
                    <h3 class="text-base md:text-lg font-semibold text-gray-800 mb-4">Booking Terbaru</h3>
                    <div class="space-y-3 max-h-80 overflow-y-auto">
                        <?php if (count($recentBookings) > 0): ?>
                            <?php foreach ($recentBookings as $booking): ?>
                                <div
                                    class="flex items-center justify-between p-3 bg-gray-50 rounded-lg hover:bg-gray-100 transition">
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-gray-800 text-sm truncate"><?php echo $booking['username']; ?></p>
                                        <p class="text-xs md:text-sm text-gray-600 truncate"><?php echo $booking['treatment_name']; ?></p>
                                        <p class="text-xs text-gray-500 mt-1">
                                            <?php echo date('d M Y, H:i', strtotime($booking['created_at'])); ?></p>
                                    </div>
                                    <div class="text-right ml-2">
                                        <?php
                                        $statusColors = [
                                            'pending' => 'bg-yellow-100 text-yellow-800',
                                            'confirmed' => 'bg-green-100 text-green-800',
                                            'cancelled' => 'bg-red-100 text-red-800',
                                            'completed' => 'bg-blue-100 text-blue-800'
                                        ];
                                        $badgeClass = $statusColors[$booking['status']] ?? 'bg-gray-100 text-gray-800';
                                        ?>
                                        <span class="px-2 py-1 text-xs font-semibold rounded-full <?php echo $badgeClass; ?>">
                                            <?php echo ucfirst($booking['status']); ?>
                                        </span>
                                        <p class="text-xs md:text-sm font-semibold text-purple-600 mt-1">Rp
                                            <?php echo number_format($booking['price'], 0, ',', '.'); ?></p>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <p class="text-center text-gray-500 py-4 text-sm">Belum ada booking</p>
                        <?php endif; ?>
                    </div>
                    <a href="admin.php?page=bookings"
                        class="block text-center mt-4 text-purple-600 hover:text-purple-700 font-medium text-sm">
                        Lihat Semua Booking →
                    </a>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="bg-white rounded-xl shadow-md p-5 md:p-6">
                <h3 class="text-base md:text-lg font-semibold text-gray-800 mb-4">Quick Actions</h3>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
                    <a href="admin.php?page=bookings"
                        class="flex flex-col items-center justify-center p-4 bg-purple-50 rounded-lg hover:bg-purple-100 transition">
                        <span class="text-2xl md:text-3xl mb-2">📅</span>
                        <span class="text-xs md:text-sm font-medium text-gray-700 text-center">Kelola Booking</span>
                    </a>
                    <a href="admin.php?page=treatments"
                        class="flex flex-col items-center justify-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition">
                        <span class="text-2xl md:text-3xl mb-2">💆</span>
                        <span class="text-xs md:text-sm font-medium text-gray-700 text-center">Kelola Treatment</span>
                    </a>
                    <a href="admin.php?page=members"
                        class="flex flex-col items-center justify-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition">
                        <span class="text-2xl md:text-3xl mb-2">👥</span>
                        <span class="text-xs md:text-sm font-medium text-gray-700 text-center">Kelola Member</span>
                    </a>
                    <a href="../index.php"
                        class="flex flex-col items-center justify-center p-4 bg-orange-50 rounded-lg hover:bg-orange-100 transition">
                        <span class="text-2xl md:text-3xl mb-2">🏠</span>
                        <span class="text-xs md:text-sm font-medium text-gray-700 text-center">Ke Website</span>
                    </a>
                </div>
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

        // Booking Chart
        const ctx = document.getElementById('bookingChart').getContext('2d');
        const bookingChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: <?php echo json_encode(array_column($chartData, 'date')); ?>,
                datasets: [{
                    label: 'Bookings',
                    data: <?php echo json_encode(array_column($chartData, 'count')); ?>,
                    borderColor: 'rgb(147, 51, 234)',
                    backgroundColor: 'rgba(147, 51, 234, 0.1)',
                    tension: 0.4,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: true,
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
    </script>
</body>

</html>