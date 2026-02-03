<?php
require __DIR__ . '/../config.php';
checkAccess('member');

$msg = "";
$booking = null;

// Ambil data booking
if (isset($_GET['id'])) {
    $booking_id = $_GET['id'];
    $stmt = $pdo->prepare("SELECT b.*, t.name as treatment_name, t.price, th.name as therapist_name 
                           FROM bookings b 
                           JOIN treatments t ON b.treatment_id = t.id 
                           JOIN therapists th ON b.therapist_id = th.id
                           WHERE b.id = ? AND b.user_id = ?");
    $stmt->execute([$booking_id, $_SESSION['user_id']]);
    $booking = $stmt->fetch();

    if (!$booking) {
        header("Location: member.php");
        exit;
    }

    // Cek apakah booking bisa di-reschedule
    if ($booking['status'] == 'cancelled' || $booking['status'] == 'completed') {
        header("Location: member.php");
        exit;
    }
}

// Proses reschedule
if (isset($_POST['reschedule'])) {
    $booking_id = $_POST['booking_id'];
    $new_date = $_POST['new_date'];
    $new_time = $_POST['new_time'];
    $therapist_id = $_POST['therapist_id'];

    // Validasi: Cek apakah tanggal dan waktu baru tidak mundur dari sekarang
    $newDateTime = $new_date . ' ' . $new_time;
    $currentDateTime = date('Y-m-d H:i');

    if ($newDateTime < $currentDateTime) {
        $msg = "error|Tidak bisa memilih waktu yang sudah lewat! Pilih tanggal/jam yang masih tersedia.";
    } else {
        // Validasi: Cek apakah terapis sibuk di jam & tanggal baru
        $check = $pdo->prepare("SELECT COUNT(*) FROM bookings 
                               WHERE therapist_id = ? 
                               AND booking_date = ? 
                               AND booking_time = ? 
                               AND status != 'cancelled' 
                               AND id != ?");
        $check->execute([$therapist_id, $new_date, $new_time, $booking_id]);

        if ($check->fetchColumn() > 0) {
            $msg = "error|Maaf! Terapis sudah dibooking pada jam tersebut. Pilih jam lain!";
        } else {

            // Cek status pembayaran saat ini DAN ambil data jadwal
            $checkPayment = $pdo->prepare("SELECT is_paid, booking_date, booking_time, reschedule_count, original_date, original_time FROM bookings WHERE id = ?");
            $checkPayment->execute([$booking_id]);
            $currentBooking = $checkPayment->fetch();

            // Tentukan status baru berdasarkan status pembayaran
            $newStatus = ($currentBooking['is_paid'] == 1) ? 'confirmed' : 'pending';

            // 🆕 SIMPAN JADWAL ORIGINAL (jika ini reschedule pertama kali)
            if ($currentBooking['reschedule_count'] == 0) {
                // Belum pernah reschedule, simpan jadwal asli
                $originalDate = $currentBooking['booking_date'];
                $originalTime = $currentBooking['booking_time'];
            } else {
                // Sudah pernah reschedule, gunakan original yang sudah tersimpan
                $originalDate = $currentBooking['original_date'];
                $originalTime = $currentBooking['original_time'];
            }

            // 🆕 UPDATE booking dengan TRACKING RESCHEDULE
            $stmt = $pdo->prepare("UPDATE bookings 
                                  SET booking_date = ?, 
                                      booking_time = ?, 
                                      therapist_id = ?,
                                      status = ?,
                                      reschedule_count = reschedule_count + 1,
                                      original_date = ?,
                                      original_time = ?,
                                      last_reschedule_at = NOW()
                                  WHERE id = ? AND user_id = ?");
            $stmt->execute([
                $new_date,
                $new_time,
                $therapist_id,
                $newStatus,
                $originalDate,
                $originalTime,
                $booking_id,
                $_SESSION['user_id']
            ]);

            if ($currentBooking['is_paid'] == 1) {
                $msg = "success|Jadwal berhasil diubah! Pembayaran Anda tetap valid. Menunggu konfirmasi admin untuk jadwal baru.";
            } else {
                $msg = "success|Jadwal berhasil diubah! Menunggu konfirmasi admin untuk jadwal baru.";
            }

            // Refresh data booking
            $stmt = $pdo->prepare("SELECT b.*, t.name as treatment_name, t.price, th.name as therapist_name 
                                   FROM bookings b 
                                   JOIN treatments t ON b.treatment_id = t.id 
                                   JOIN therapists th ON b.therapist_id = th.id
                                   WHERE b.id = ? AND b.user_id = ?");
            $stmt->execute([$booking_id, $_SESSION['user_id']]);
            $booking = $stmt->fetch();
        }
    }
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reschedule Booking - Beautybar.bync</title>
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

        @keyframes slideDown {
            from {
                opacity: 0;
                transform: translateY(-10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .alert-animate {
            animation: slideDown 0.3s ease-out;
        }

        /* Mobile Navigation Improvements */
        @media (max-width: 768px) {
            .mobile-nav-link {
                font-size: 0.875rem;
                white-space: nowrap;
            }

            .mobile-user-info {
                display: none;
            }
        }
    </style>
</head>

<body class="font-sans text-primary bg-secondary">

    <!-- Navigation - Mobile Optimized -->
    <nav class="navbar-blur fixed top-0 left-0 right-0 border-b border-gray-200 z-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 sm:h-20">
                <!-- Brand -->
                <a href="member.php" class="flex items-center gap-2">
                    <i class="fas fa-spa text-xl sm:text-2xl text-accent"></i>
                    <span class="text-base sm:text-xl font-semibold tracking-tight">Beautybar</span>
                </a>

                <!-- Desktop & Mobile Actions -->
                <div class="flex items-center gap-2 sm:gap-4">
                    <!-- Back Button -->
                    <a href="member.php"
                        class="mobile-nav-link text-gray-600 hover:text-primary font-medium text-xs sm:text-sm transition-colors">
                        <i class="fas fa-arrow-left mr-1"></i>
                        <span class="hidden sm:inline">Kembali</span>
                    </a>

                    <!-- User Info - Hidden on Mobile -->
                    <div class="mobile-user-info flex items-center gap-3 pl-4 border-l border-gray-200">
                        <div class="text-right">
                            <p class="text-xs text-gray-500">Welcome,</p>
                            <p class="text-sm font-semibold text-primary"><?php echo $_SESSION['name']; ?></p>
                        </div>
                    </div>

                    <!-- Logout Button -->
                    <a href="../logout.php"
                        class="px-3 sm:px-6 py-2 sm:py-2.5 border border-primary text-primary hover:bg-primary hover:text-white transition-all text-xs sm:text-sm font-medium rounded">
                        <i class="fas fa-sign-out-alt sm:mr-1"></i>
                        <span class="hidden sm:inline">Logout</span>
                    </a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section - Mobile Optimized -->
    <section
        class="relative pt-20 sm:pt-32 pb-8 sm:pb-12 lg:pb-16 bg-gradient-to-br from-primary via-gray-800 to-primary">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center text-white animate-fade-in-up">
                <h1 class="text-2xl sm:text-4xl lg:text-5xl font-light tracking-tight mb-2 sm:mb-4">Reschedule Booking
                </h1>
                <p class="text-sm sm:text-lg text-white/80 max-w-2xl mx-auto px-4">
                    Ubah jadwal booking Anda tanpa perlu membatalkan
                </p>
            </div>
        </div>
    </section>

    <!-- Alert Message - Mobile Optimized -->
    <?php if ($msg):
        list($type, $text) = explode('|', $msg);
        $bgColor = $type == 'success' ? 'bg-green-50 border-green-500 text-green-700' : 'bg-red-50 border-red-500 text-red-700';
        $icon = $type == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
        ?>
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 -mt-6 sm:-mt-8 relative z-10">
            <div class="alert-animate <?php echo $bgColor; ?> border-l-4 p-3 sm:p-4 rounded-lg shadow-lg">
                <div class="flex items-start sm:items-center">
                    <i class="fas <?php echo $icon; ?> mr-2 sm:mr-3 text-lg sm:text-xl flex-shrink-0 mt-0.5 sm:mt-0"></i>
                    <p class="font-medium text-xs sm:text-sm"><?php echo $text; ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Main Content - Mobile Optimized -->
    <section class="py-6 sm:py-8 lg:py-12 <?php echo $msg ? '' : '-mt-12 sm:-mt-16'; ?> relative z-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">

            <?php if ($booking): ?>

                <!-- Informasi Booking Saat Ini - Mobile Optimized -->
                <div class="bg-white rounded-lg sm:rounded-xl shadow-md p-4 sm:p-6 mb-4 sm:mb-6">
                    <h2 class="text-lg sm:text-xl font-semibold text-primary mb-3 sm:mb-4 flex items-center gap-2">
                        <i class="fas fa-info-circle text-accent text-base sm:text-lg"></i>
                        <span class="text-sm sm:text-xl">Info Booking Saat Ini</span>
                    </h2>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 sm:gap-4">
                        <div class="bg-gray-50 p-3 sm:p-4 rounded-lg">
                            <p class="text-xs sm:text-sm text-gray-600 mb-1">Treatment</p>
                            <p class="font-semibold text-primary text-sm sm:text-base">
                                <?php echo $booking['treatment_name']; ?></p>
                            <p class="text-xs sm:text-sm font-bold text-accent mt-1">Rp
                                <?php echo number_format($booking['price'], 0, ',', '.'); ?></p>
                        </div>
                        <div class="bg-gray-50 p-3 sm:p-4 rounded-lg">
                            <p class="text-xs sm:text-sm text-gray-600 mb-1">Terapis</p>
                            <p class="font-semibold text-primary text-sm sm:text-base">
                                <?php echo $booking['therapist_name']; ?></p>
                        </div>
                        <div class="bg-gray-50 p-3 sm:p-4 rounded-lg">
                            <p class="text-xs sm:text-sm text-gray-600 mb-1">Jadwal Saat Ini</p>
                            <p class="font-semibold text-primary text-sm sm:text-base">
                                <i class="fas fa-calendar text-accent mr-1 sm:mr-2 text-xs sm:text-sm"></i>
                                <?php echo date('d M Y', strtotime($booking['booking_date'])); ?>
                            </p>
                        </div>
                        <div class="bg-gray-50 p-3 sm:p-4 rounded-lg">
                            <p class="text-xs sm:text-sm text-gray-600 mb-1">Jam</p>
                            <p class="font-semibold text-primary text-sm sm:text-base">
                                <i class="fas fa-clock text-accent mr-1 sm:mr-2 text-xs sm:text-sm"></i>
                                <?php echo $booking['booking_time']; ?>
                            </p>
                        </div>

                        <!-- Status Pembayaran -->
                        <div class="bg-gray-50 p-3 sm:p-4 rounded-lg col-span-1 sm:col-span-2">
                            <p class="text-xs sm:text-sm text-gray-600 mb-1">Status Pembayaran</p>
                            <?php if ($booking['is_paid'] == 1): ?>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-check-circle text-green-600 text-sm sm:text-base"></i>
                                    <span class="font-semibold text-green-600 text-sm sm:text-base">LUNAS - Tidak perlu bayar
                                        lagi</span>
                                </div>
                            <?php else: ?>
                                <div class="flex items-center gap-2">
                                    <i class="fas fa-exclamation-circle text-orange-600 text-sm sm:text-base"></i>
                                    <span class="font-semibold text-orange-600 text-sm sm:text-base">Belum Bayar</span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Info Box - Mobile Optimized -->
                <div class="bg-yellow-50 border-l-4 border-yellow-500 p-3 sm:p-4 rounded-lg mb-4 sm:mb-6">
                    <div class="flex items-start">
                        <i
                            class="fas fa-exclamation-triangle text-yellow-600 mt-0.5 sm:mt-1 mr-2 sm:mr-3 text-sm sm:text-base flex-shrink-0"></i>
                        <div class="text-xs sm:text-sm text-yellow-800">
                            <p class="font-semibold mb-1 text-xs sm:text-sm">Kebijakan Reschedule:</p>
                            <ul class="list-disc list-inside space-y-0.5 sm:space-y-1 text-xs">
                                <li>Pembatalan booking <strong>TIDAK</strong> tersedia</li>
                                <li>Anda dapat mengubah jadwal berkali-kali</li>
                                <li><strong>Hanya bisa pilih tanggal hari ini atau ke depan</strong></li>
                                <li>Tidak bisa mundur ke tanggal yang sudah lewat</li>
                                <?php if ($booking['is_paid'] == 1): ?>
                                    <li><strong class="text-green-700">✓ Pembayaran Anda tetap valid, tidak perlu bayar
                                            lagi</strong></li>
                                    <li>Admin hanya perlu konfirmasi jadwal baru saja</li>
                                <?php else: ?>
                                    <li>Perlu konfirmasi ulang dari admin</li>
                                    <li>Status booking berubah jadi "Pending"</li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Form Reschedule - Mobile Optimized -->
                <div class="bg-white rounded-lg sm:rounded-xl shadow-md overflow-hidden">
                    <div class="bg-gradient-to-r from-primary to-gray-800 px-4 sm:px-6 py-4 sm:py-5">
                        <h2 class="text-base sm:text-xl font-semibold text-white flex items-center gap-2 sm:gap-3">
                            <i class="fas fa-calendar-alt text-accent text-sm sm:text-base"></i>
                            <span>Pilih Jadwal Baru</span>
                        </h2>
                    </div>

                    <form method="POST" class="p-4 sm:p-6 space-y-4 sm:space-y-6">
                        <input type="hidden" name="booking_id" value="<?php echo $booking['id']; ?>">

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 sm:gap-6">
                            <!-- Therapist -->
                            <div>
                                <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-user-nurse text-accent mr-1 sm:mr-2 text-xs sm:text-sm"></i>Pilih
                                    Terapis
                                </label>
                                <select name="therapist_id" required
                                    class="w-full px-3 sm:px-4 py-2.5 sm:py-3 text-sm sm:text-base border-2 border-gray-300 rounded-lg focus:border-accent focus:outline-none transition-colors">
                                    <option value="">Pilih Terapis</option>
                                    <?php
                                    $therapists = $pdo->query("SELECT * FROM therapists ORDER BY name");
                                    while ($t = $therapists->fetch()) {
                                        $selected = ($t['id'] == $booking['therapist_id']) ? 'selected' : '';
                                        echo "<option value='{$t['id']}' $selected>{$t['name']}</option>";
                                    }
                                    ?>
                                </select>
                                <p class="text-xs text-gray-500 mt-1">Boleh pilih terapis yang sama atau beda</p>
                            </div>

                            <!-- Date -->
                            <div>
                                <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-2">
                                    <i class="fas fa-calendar-day text-accent mr-1 sm:mr-2 text-xs sm:text-sm"></i>Tanggal
                                    Baru
                                </label>
                                <input type="date" name="new_date" min="<?php echo date('Y-m-d'); ?>"
                                    value="<?php echo $booking['booking_date']; ?>" required
                                    class="w-full px-3 sm:px-4 py-2.5 sm:py-3 text-sm sm:text-base border-2 border-gray-300 rounded-lg focus:border-accent focus:outline-none transition-colors">
                                <p class="text-xs text-gray-500 mt-1">Minimal hari ini</p>
                            </div>
                        </div>

                        <!-- Time -->
                        <div>
                            <label class="block text-xs sm:text-sm font-semibold text-gray-700 mb-2">
                                <i class="fas fa-clock text-accent mr-1 sm:mr-2 text-xs sm:text-sm"></i>Jam Baru (09:00 -
                                21:00)
                            </label>
                            <select name="new_time" required
                                class="w-full px-3 sm:px-4 py-2.5 sm:py-3 text-sm sm:text-base border-2 border-gray-300 rounded-lg focus:border-accent focus:outline-none transition-colors">
                                <option value="">Pilih Jam</option>
                                <?php
                                for ($i = 9; $i <= 21; $i++) {
                                    $jam = str_pad($i, 2, '0', STR_PAD_LEFT) . ":00";
                                    $selected = ($jam == $booking['booking_time']) ? 'selected' : '';
                                    echo "<option value='$jam' $selected>$jam</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <!-- Buttons - Mobile Optimized -->
                        <div class="flex flex-col sm:flex-row gap-3 pt-4 border-t border-gray-200">
                            <a href="member.php"
                                class="w-full sm:flex-1 px-4 sm:px-6 py-2.5 sm:py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition-all text-center text-sm sm:text-base">
                                <i class="fas fa-times mr-2"></i>Batal
                            </a>
                            <button type="submit" name="reschedule"
                                class="w-full sm:flex-1 px-4 sm:px-6 py-2.5 sm:py-3 bg-primary text-white font-semibold rounded-lg hover:bg-black transition-all shadow-lg text-sm sm:text-base">
                                <i class="fas fa-calendar-check mr-2"></i>Simpan Jadwal Baru
                            </button>
                        </div>
                    </form>
                </div>

            <?php else: ?>

                <div class="bg-white rounded-lg sm:rounded-xl shadow-md p-8 sm:p-12 text-center">
                    <i class="fas fa-exclamation-triangle text-4xl sm:text-5xl text-gray-300 mb-3 sm:mb-4"></i>
                    <p class="text-gray-600 font-medium text-base sm:text-lg">Booking tidak ditemukan</p>
                    <a href="member.php"
                        class="inline-block mt-3 sm:mt-4 text-accent hover:text-primary font-semibold text-sm sm:text-base">
                        <i class="fas fa-arrow-left mr-2"></i>Kembali ke Dashboard
                    </a>
                </div>

            <?php endif; ?>

        </div>
    </section>

    <!-- Footer - Mobile Optimized -->
    <footer class="bg-primary text-white py-6 sm:py-8 mt-8 sm:mt-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row items-center justify-between gap-3 sm:gap-4">
                <div class="flex items-center gap-2 sm:gap-3">
                    <i class="fas fa-spa text-lg sm:text-xl text-accent"></i>
                    <span class="text-base sm:text-lg font-semibold tracking-tight">Beautybar.bync</span>
                </div>

                <p class="text-white/50 text-xs sm:text-sm text-center md:text-left">
                    &copy; 2013-<?= date('Y') ?>, All rights Reserved. Bandar Lampung - Indonesia
                </p>
            </div>
        </div>
    </footer>

    <script>
        // Auto-hide alert after 5 seconds
        const alert = document.querySelector('.alert-animate');
        if (alert) {
            setTimeout(() => {
                alert.style.opacity = '0';
                alert.style.transform = 'translateY(-20px)';
                alert.style.transition = 'all 0.3s ease-out';
                setTimeout(() => alert.remove(), 300);
            }, 5000);
        }

        // Validasi tanggal - Pastikan tidak bisa pilih tanggal mundur
        const dateInput = document.querySelector('input[name="new_date"]');
        if (dateInput) {
            // Set minimum date ke hari ini
            const today = new Date().toISOString().split('T')[0];
            dateInput.setAttribute('min', today);

            // Validasi saat user mengubah tanggal
            dateInput.addEventListener('change', function () {
                const selectedDate = new Date(this.value);
                const currentDate = new Date(today);

                if (selectedDate < currentDate) {
                    alert('⚠️ Tidak bisa memilih tanggal yang sudah lewat!');
                    this.value = today;
                }
            });

            // Validasi saat form disubmit
            const form = dateInput.closest('form');
            form.addEventListener('submit', function (e) {
                const selectedDate = new Date(dateInput.value);
                const currentDate = new Date(today);

                if (selectedDate < currentDate) {
                    e.preventDefault();
                    alert('⚠️ Tidak bisa memilih tanggal yang sudah lewat! Pilih tanggal hari ini atau ke depan.');
                    dateInput.focus();
                    return false;
                }

                // Validasi jika form kosong
                const therapistId = document.querySelector('select[name="therapist_id"]').value;
                const newTime = document.querySelector('select[name="new_time"]').value;

                if (!therapistId || !newTime || !dateInput.value) {
                    e.preventDefault();
                    alert('⚠️ Mohon lengkapi semua field yang diperlukan!');
                    return false;
                }
            });
        }
    </script>

</body>

</html>