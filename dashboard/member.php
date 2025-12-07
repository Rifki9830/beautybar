<?php
require '../config.php';
checkAccess('member');

$msg = "";
if (isset($_POST['booking'])) {
    $treat_id = $_POST['treatment'];
    $ther_id  = $_POST['therapist'];
    $date     = $_POST['date'];
    $time     = $_POST['time'];
    $uid      = $_SESSION['user_id'];

    // Validasi: Cek apakah Terapis sibuk di jam & tanggal itu
    $check = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE therapist_id=? AND booking_date=? AND booking_time=? AND status != 'cancelled'");
    $check->execute([$ther_id, $date, $time]);
    
    if ($check->fetchColumn() > 0) {
        $msg = "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg mb-4' role='alert'>
                    <strong>Maaf!</strong> Terapis sudah dibooking pada jam tersebut. Pilih jam lain!
                </div>";
    } else {
        $sql = "INSERT INTO bookings (user_id, treatment_id, therapist_id, booking_date, booking_time) VALUES (?,?,?,?,?)";
        $pdo->prepare($sql)->execute([$uid, $treat_id, $ther_id, $date, $time]);
        $msg = "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg mb-4' role='alert'>
                    <strong>Berhasil!</strong> Booking Anda telah dibuat. Menunggu konfirmasi admin.
                </div>";
    }
}

// Statistik Member
$totalBookings = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id=?");
$totalBookings->execute([$_SESSION['user_id']]);
$totalBookings = $totalBookings->fetchColumn();

$completedBookings = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id=? AND status='completed'");
$completedBookings->execute([$_SESSION['user_id']]);
$completedBookings = $completedBookings->fetchColumn();

$pendingBookings = $pdo->prepare("SELECT COUNT(*) FROM bookings WHERE user_id=? AND status='pending'");
$pendingBookings->execute([$_SESSION['user_id']]);
$pendingBookings = $pendingBookings->fetchColumn();

$totalSpent = $pdo->prepare("SELECT SUM(t.price) FROM bookings b JOIN treatments t ON b.treatment_id=t.id WHERE b.user_id=? AND b.is_paid=1");
$totalSpent->execute([$_SESSION['user_id']]);
$totalSpent = $totalSpent->fetchColumn() ?? 0;
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Member Dashboard - Beautybar</title>
    <script src="https://cdn.tailwindcss.com"></script>
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
                <p class="text-sm text-gray-500 mt-1">Member Area</p>
            </div>
            
            <div class="px-4 py-2">
                <div class="bg-purple-50 rounded-lg p-4 mb-4 border border-purple-100">
                    <p class="text-sm text-gray-600">Halo,</p>
                    <p class="font-semibold text-gray-800"><?php echo $_SESSION['name']; ?></p>
                    <p class="text-xs text-purple-600 mt-1">Member Dashboard</p>
                </div>
            </div>

            <nav class="px-4">
                <a href="member.php" 
                   class="flex items-center px-4 py-3 mb-2 rounded-lg bg-purple-600 text-white">
                    <span class="mr-3">📅</span>
                    <span>Dashboard & Booking</span>
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
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Dashboard Member</h1>
                <p class="text-gray-500 mt-1 text-sm md:text-base">Kelola booking dan transaksi Anda</p>
            </div>

            <!-- Stats Cards -->
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6 mb-6">
                <!-- Total Bookings -->
                <div class="bg-gradient-to-br from-blue-500 to-blue-600 rounded-xl shadow-lg p-5 text-white">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-blue-100 text-sm font-medium">Total Booking</p>
                        <div class="bg-white bg-opacity-20 rounded-full p-2">
                            <span class="text-xl">📅</span>
                        </div>
                    </div>
                    <h3 class="text-3xl font-bold"><?php echo $totalBookings; ?></h3>
                    <p class="text-xs text-blue-100 mt-2">Booking keseluruhan</p>
                </div>

                <!-- Completed -->
                <div class="bg-gradient-to-br from-green-500 to-green-600 rounded-xl shadow-lg p-5 text-white">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-green-100 text-sm font-medium">Selesai</p>
                        <div class="bg-white bg-opacity-20 rounded-full p-2">
                            <span class="text-xl">✅</span>
                        </div>
                    </div>
                    <h3 class="text-3xl font-bold"><?php echo $completedBookings; ?></h3>
                    <p class="text-xs text-green-100 mt-2">Treatment selesai</p>
                </div>

                <!-- Pending -->
                <div class="bg-gradient-to-br from-orange-500 to-orange-600 rounded-xl shadow-lg p-5 text-white">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-orange-100 text-sm font-medium">Menunggu</p>
                        <div class="bg-white bg-opacity-20 rounded-full p-2">
                            <span class="text-xl">⏳</span>
                        </div>
                    </div>
                    <h3 class="text-3xl font-bold"><?php echo $pendingBookings; ?></h3>
                    <p class="text-xs text-orange-100 mt-2">Booking pending</p>
                </div>

                <!-- Total Spent -->
                <div class="bg-gradient-to-br from-purple-500 to-purple-600 rounded-xl shadow-lg p-5 text-white">
                    <div class="flex items-center justify-between mb-3">
                        <p class="text-purple-100 text-sm font-medium">Total Belanja</p>
                        <div class="bg-white bg-opacity-20 rounded-full p-2">
                            <span class="text-xl">💰</span>
                        </div>
                    </div>
                    <h3 class="text-2xl font-bold">Rp <?php echo number_format($totalSpent, 0, ',', '.'); ?></h3>
                    <p class="text-xs text-purple-100 mt-2">Total pembayaran</p>
                </div>
            </div>

            <!-- Alert Message -->
            <?php echo $msg; ?>

            <!-- Button to Open Modal -->
            <div class="mb-6">
                <button id="openModalBtn" class="px-6 py-3 bg-purple-600 text-white font-semibold rounded-lg hover:bg-purple-700 transition shadow-lg">
                    ➕ Buat Booking Baru
                </button>
            </div>

            <!-- Modal Popup -->
            <div id="bookingModal" class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                    <!-- Modal Header -->
                    <div class="bg-gradient-to-r from-purple-600 to-purple-700 px-6 py-4 rounded-t-2xl flex items-center justify-between">
                        <h2 class="text-xl font-bold text-white">📅 Buat Booking Baru</h2>
                        <button id="closeModalBtn" class="text-white hover:text-gray-200">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <!-- Modal Body -->
                    <form method="POST" class="p-6 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Treatment</label>
                                <select name="treatment" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                    <option value="">-- Pilih Treatment --</option>
                                    <?php
                                    $t = $pdo->query("SELECT * FROM treatments");
                                    while($r = $t->fetch()){ 
                                        echo "<option value='{$r['id']}'>{$r['name']} - Rp ".number_format($r['price'], 0, ',', '.')."</option>"; 
                                    }
                                    ?>
                                </select>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Pilih Terapis</label>
                                <select name="therapist" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                    <option value="">-- Pilih Terapis --</option>
                                    <?php
                                    $th = $pdo->query("SELECT * FROM therapists");
                                    while($r = $th->fetch()){ 
                                        echo "<option value='{$r['id']}'>{$r['name']}</option>"; 
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Tanggal</label>
                                <input type="date" name="date" min="<?php echo date('Y-m-d'); ?>" required 
                                       class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Jam (09:00 - 21:00)</label>
                                <select name="time" required class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent">
                                    <option value="">-- Pilih Jam --</option>
                                    <?php
                                    for($i=9; $i<=21; $i++) {
                                        $jam = str_pad($i, 2, '0', STR_PAD_LEFT).":00";
                                        echo "<option value='$jam'>$jam</option>";
                                    }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <!-- Modal Footer -->
                        <div class="flex gap-3 pt-4">
                            <button type="button" id="cancelBtn" class="flex-1 px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition">
                                Batal
                            </button>
                            <button type="submit" name="booking" class="flex-1 px-6 py-3 bg-purple-600 text-white font-semibold rounded-lg hover:bg-purple-700 transition">
                                📅 Booking Sekarang
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Booking History -->
            <div class="bg-white rounded-xl shadow-md p-5 md:p-6">
                <h2 class="text-xl font-bold text-gray-800 mb-4">Riwayat & Pembayaran</h2>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-gray-200">
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Treatment</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Jadwal</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Status Booking</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Status Bayar</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-gray-700">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $hist = $pdo->prepare("SELECT b.*, t.name as tname, t.price, 
                                                   tr.payment_status, tr.proof_image, s.id as survey_id 
                                                   FROM bookings b 
                                                   JOIN treatments t ON b.treatment_id=t.id 
                                                   LEFT JOIN transactions tr ON b.id = tr.booking_id
                                                   LEFT JOIN surveys s ON b.id = s.booking_id
                                                   WHERE b.user_id=? ORDER BY b.id DESC");
                            $hist->execute([$_SESSION['user_id']]);
                            
                            if($hist->rowCount() > 0) {
                                while($h = $hist->fetch()) {
                                    // Badge Status Booking
                                    $statusColors = [
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'confirmed' => 'bg-green-100 text-green-800',
                                        'cancelled' => 'bg-red-100 text-red-800',
                                        'completed' => 'bg-blue-100 text-blue-800'
                                    ];
                                    $badgeClass = $statusColors[$h['status']] ?? 'bg-gray-100 text-gray-800';
                                    
                                    echo "<tr class='border-b border-gray-100 hover:bg-gray-50'>
                                        <td class='py-3 px-4'>
                                            <p class='font-medium text-gray-800'>{$h['tname']}</p>
                                            <p class='text-sm font-semibold text-purple-600'>Rp ".number_format($h['price'], 0, ',', '.')."</p>
                                        </td>
                                        <td class='py-3 px-4'>
                                            <p class='text-sm text-gray-800'>".date('d M Y', strtotime($h['booking_date']))."</p>
                                            <p class='text-sm text-gray-600'>{$h['booking_time']}</p>
                                        </td>
                                        <td class='py-3 px-4'>
                                            <span class='px-2 py-1 text-xs font-semibold rounded-full $badgeClass'>".ucfirst($h['status'])."</span>
                                        </td>
                                        <td class='py-3 px-4'>";
                                        
                                        // LOGIKA STATUS PEMBAYARAN
                                        if($h['is_paid'] == 1) {
                                            echo "<span class='text-green-600 font-semibold text-sm'>✔ Lunas</span>";
                                        } elseif ($h['payment_status'] == 'pending') {
                                            echo "<span class='text-orange-600 font-semibold text-sm'>⏳ Menunggu Konfirmasi</span>";
                                        } else {
                                            echo "<span class='text-red-600 font-semibold text-sm'>Belum Bayar</span>";
                                        }
                                
                                echo "</td>
                                        <td class='py-3 px-4'>
                                            <div class='flex flex-col gap-2'>";
                                            
                                            // TOMBOL AKSI
                                            if($h['status'] == 'confirmed' && $h['is_paid'] == 0) {
                                                if($h['payment_status'] == 'pending') {
                                                    echo "<span class='text-xs text-gray-500'>Sedang dicek admin</span>";
                                                } else {
                                                    echo "<a href='payment.php?id={$h['id']}&amount={$h['price']}' class='inline-block px-3 py-1 bg-blue-600 text-white text-xs font-semibold rounded hover:bg-blue-700 text-center'>Bayar Sekarang</a>";
                                                }
                                            }
                                            
                                            // Tombol Survei
                                            if(($h['status'] == 'completed' || $h['is_paid'] == 1) && !$h['survey_id']) {
                                                echo "<a href='survey.php?id={$h['id']}' class='inline-block px-3 py-1 bg-purple-600 text-white text-xs font-semibold rounded hover:bg-purple-700 text-center'>Isi Survei</a>";
                                            }
                                    echo "</div>
                                        </td>
                                    </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='5' class='text-center py-8 text-gray-400'>Belum ada riwayat booking</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
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

        // Modal Toggle
        const openModalBtn = document.getElementById('openModalBtn');
        const closeModalBtn = document.getElementById('closeModalBtn');
        const cancelBtn = document.getElementById('cancelBtn');
        const bookingModal = document.getElementById('bookingModal');

        openModalBtn.addEventListener('click', () => {
            bookingModal.classList.remove('hidden');
        });

        closeModalBtn.addEventListener('click', () => {
            bookingModal.classList.add('hidden');
        });

        cancelBtn.addEventListener('click', () => {
            bookingModal.classList.add('hidden');
        });

        // Close modal when clicking outside
        bookingModal.addEventListener('click', (e) => {
            if (e.target === bookingModal) {
                bookingModal.classList.add('hidden');
            }
        });
    </script>
</body>
</html>