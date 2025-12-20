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
        $msg = "error|Maaf! Terapis sudah dibooking pada jam tersebut. Pilih jam lain!";
    } else {
        $sql = "INSERT INTO bookings (user_id, treatment_id, therapist_id, booking_date, booking_time) VALUES (?,?,?,?,?)";
        $pdo->prepare($sql)->execute([$uid, $treat_id, $ther_id, $date, $time]);
        $msg = "success|Booking Anda telah dibuat. Menunggu konfirmasi admin.";
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
    <title>Member Dashboard - Beautybar.bync</title>
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
        html { scroll-behavior: smooth; }
        
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

        /* Modal Styles */
        .modal-backdrop {
            backdrop-filter: blur(4px);
            animation: fadeIn 0.3s ease-out;
        }

        @keyframes fadeIn {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        @keyframes modalSlide {
            from {
                opacity: 0;
                transform: translateY(-50px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }

        .modal-content {
            animation: modalSlide 0.3s ease-out;
        }

        /* Stat Card Hover Effects */
        .stat-card {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }

        /* Table Row Hover */
        .table-row {
            transition: background-color 0.2s;
        }

        .table-row:hover {
            background-color: rgba(212, 165, 116, 0.05);
        }
    </style>
</head>

<body class="font-sans text-primary bg-secondary overflow-x-hidden">

    <!-- Navigation -->
    <nav class="navbar-blur fixed top-0 left-0 right-0 border-b border-gray-200 z-50">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                <!-- Brand -->
                <a href="../index.php" class="flex items-center gap-3">
                    <i class="fas fa-spa text-2xl text-accent"></i>
                    <span class="text-xl font-semibold tracking-tight">Beautybar.bync</span>
                </a>

                <!-- Desktop Menu -->
                <div class="hidden md:flex items-center gap-8">
                    <a href="../index.php" class="text-gray-600 hover:text-primary font-medium text-sm transition-colors">Home</a>
                    <a href="../treatments.php" class="text-gray-600 hover:text-primary font-medium text-sm transition-colors">Treatment</a>
                    <a href="member.php" class="text-gray-600 hover:text-primary font-medium text-sm transition-colors relative group">
                        Dashboard
                        <span class="absolute -bottom-2 left-0 w-full h-0.5 bg-accent scale-x-100 transition-transform origin-left"></span>
                    </a>
                    <div class="flex items-center gap-3 pl-4 border-l border-gray-200">
                        <div class="text-right">
                            <p class="text-xs text-gray-500">Welcome,</p>
                            <p class="text-sm font-semibold text-primary"><?php echo $_SESSION['name']; ?></p>
                        </div>
                        <a href="../logout.php" class="px-6 py-2.5 border border-primary text-primary hover:bg-primary hover:text-white transition-all text-sm font-medium">
                            Logout
                        </a>
                    </div>
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
                    <div class="bg-secondary p-4 rounded-lg mb-2">
                        <p class="text-xs text-gray-500">Welcome,</p>
                        <p class="text-sm font-semibold text-primary"><?php echo $_SESSION['name']; ?></p>
                    </div>
                    <a href="../index.php" class="text-gray-600 hover:text-primary font-medium text-sm">Home</a>
                    <a href="../treatments.php" class="text-gray-600 hover:text-primary font-medium text-sm">Treatment</a>
                    <a href="member.php" class="text-gray-600 hover:text-primary font-medium text-sm">Dashboard</a>
                    <a href="../logout.php" class="px-6 py-2.5 border border-primary text-primary text-center text-sm font-medium">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="relative pt-32 pb-12 lg:pb-16 bg-gradient-to-br from-primary via-gray-800 to-primary">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="text-center text-white animate-fade-in-up">
                <h1 class="text-4xl lg:text-5xl font-light tracking-tight mb-4">Member Dashboard</h1>
                <p class="text-lg text-white/80 max-w-2xl mx-auto">
                    Kelola booking dan pantau riwayat treatment Anda
                </p>
            </div>
        </div>
    </section>

    <!-- Alert Message -->
    <?php if($msg): 
        list($type, $text) = explode('|', $msg);
        $bgColor = $type == 'success' ? 'bg-green-50 border-green-500 text-green-700' : 'bg-red-50 border-red-500 text-red-700';
        $icon = $type == 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    ?>
    <div class="max-w-7xl mx-auto px-6 lg:px-8 -mt-8 relative z-10">
        <div class="alert-animate <?php echo $bgColor; ?> border-l-4 p-4 rounded-lg shadow-lg">
            <div class="flex items-center">
                <i class="fas <?php echo $icon; ?> mr-3 text-xl"></i>
                <p class="font-medium"><?php echo $text; ?></p>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Stats Cards -->
    <section class="py-8 lg:py-12 <?php echo $msg ? '' : '-mt-16'; ?> relative z-10">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Total Bookings -->
                <div class="stat-card bg-white rounded-xl shadow-md p-6 border-l-4 border-blue-500">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-blue-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-calendar-alt text-2xl text-blue-600"></i>
                        </div>
                        <span class="text-3xl font-bold text-blue-600"><?php echo $totalBookings; ?></span>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Total Booking</h3>
                    <p class="text-xs text-gray-500 mt-1">Keseluruhan booking</p>
                </div>

                <!-- Completed -->
                <div class="stat-card bg-white rounded-xl shadow-md p-6 border-l-4 border-green-500">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-green-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-check-circle text-2xl text-green-600"></i>
                        </div>
                        <span class="text-3xl font-bold text-green-600"><?php echo $completedBookings; ?></span>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Selesai</h3>
                    <p class="text-xs text-gray-500 mt-1">Treatment completed</p>
                </div>

                <!-- Pending -->
                <div class="stat-card bg-white rounded-xl shadow-md p-6 border-l-4 border-orange-500">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-orange-100 rounded-lg flex items-center justify-center">
                            <i class="fas fa-hourglass-half text-2xl text-orange-600"></i>
                        </div>
                        <span class="text-3xl font-bold text-orange-600"><?php echo $pendingBookings; ?></span>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide">Menunggu</h3>
                    <p class="text-xs text-gray-500 mt-1">Pending confirmation</p>
                </div>

                <!-- Total Spent -->
                <div class="stat-card bg-white rounded-xl shadow-md p-6 border-l-4 border-accent">
                    <div class="flex items-center justify-between mb-4">
                        <div class="w-12 h-12 bg-accent/10 rounded-lg flex items-center justify-center">
                            <i class="fas fa-wallet text-2xl text-accent"></i>
                        </div>
                    </div>
                    <h3 class="text-sm font-semibold text-gray-600 uppercase tracking-wide mb-1">Total Belanja</h3>
                    <p class="text-2xl font-bold text-accent">Rp <?php echo number_format($totalSpent, 0, ',', '.'); ?></p>
                </div>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <section class="py-8 lg:py-12">
        <div class="max-w-7xl mx-auto px-6 lg:px-8">
            <!-- Action Button -->
            <div class="mb-8">
                <button id="openModalBtn" class="inline-flex items-center gap-2 px-8 py-3.5 bg-primary text-white hover:bg-black transition-all text-sm font-medium shadow-lg hover:shadow-xl">
                    <i class="fas fa-plus-circle"></i>
                    Buat Booking Baru
                </button>
            </div>

            <!-- Booking History -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-200">
                    <h2 class="text-xl font-semibold text-primary">Riwayat Booking & Pembayaran</h2>
                    <p class="text-sm text-gray-600 mt-1">Pantau semua booking dan status pembayaran Anda</p>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-secondary">
                            <tr>
                                <th class="text-left py-4 px-6 text-sm font-semibold text-gray-700 uppercase tracking-wide">Treatment</th>
                                <th class="text-left py-4 px-6 text-sm font-semibold text-gray-700 uppercase tracking-wide">Jadwal</th>
                                <th class="text-left py-4 px-6 text-sm font-semibold text-gray-700 uppercase tracking-wide">Status Booking</th>
                                <th class="text-left py-4 px-6 text-sm font-semibold text-gray-700 uppercase tracking-wide">Status Bayar</th>
                                <th class="text-left py-4 px-6 text-sm font-semibold text-gray-700 uppercase tracking-wide">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100">
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
                                    $statusConfig = [
                                        'pending' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-800', 'icon' => 'fa-clock', 'label' => 'Pending'],
                                        'confirmed' => ['bg' => 'bg-green-100', 'text' => 'text-green-800', 'icon' => 'fa-check', 'label' => 'Confirmed'],
                                        'cancelled' => ['bg' => 'bg-red-100', 'text' => 'text-red-800', 'icon' => 'fa-times', 'label' => 'Cancelled'],
                                        'completed' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-800', 'icon' => 'fa-check-double', 'label' => 'Completed']
                                    ];
                                    $status = $statusConfig[$h['status']] ?? $statusConfig['pending'];
                                    
                                    echo "<tr class='table-row'>
                                        <td class='py-4 px-6'>
                                            <p class='font-semibold text-gray-800'>{$h['tname']}</p>
                                            <p class='text-sm font-bold text-accent mt-1'>Rp ".number_format($h['price'], 0, ',', '.')."</p>
                                        </td>
                                        <td class='py-4 px-6'>
                                            <div class='flex items-center gap-2 text-gray-700'>
                                                <i class='fas fa-calendar text-accent text-sm'></i>
                                                <span class='text-sm font-medium'>".date('d M Y', strtotime($h['booking_date']))."</span>
                                            </div>
                                            <div class='flex items-center gap-2 text-gray-600 mt-1'>
                                                <i class='fas fa-clock text-accent text-xs'></i>
                                                <span class='text-sm'>{$h['booking_time']}</span>
                                            </div>
                                        </td>
                                        <td class='py-4 px-6'>
                                            <span class='inline-flex items-center gap-2 px-3 py-1.5 text-xs font-semibold rounded-full {$status['bg']} {$status['text']}'>
                                                <i class='fas {$status['icon']}'></i>
                                                {$status['label']}
                                            </span>
                                        </td>
                                        <td class='py-4 px-6'>";
                                        
                                        // Status Pembayaran
                                        if($h['is_paid'] == 1) {
                                            echo "<span class='inline-flex items-center gap-2 text-green-600 font-semibold text-sm'>
                                                    <i class='fas fa-check-circle'></i> Lunas
                                                  </span>";
                                        } elseif ($h['payment_status'] == 'pending') {
                                            echo "<span class='inline-flex items-center gap-2 text-orange-600 font-semibold text-sm'>
                                                    <i class='fas fa-hourglass-half'></i> Menunggu Konfirmasi
                                                  </span>";
                                        } else {
                                            echo "<span class='inline-flex items-center gap-2 text-red-600 font-semibold text-sm'>
                                                    <i class='fas fa-times-circle'></i> Belum Bayar
                                                  </span>";
                                        }
                                
                                echo "</td>
                                        <td class='py-4 px-6'>
                                            <div class='flex flex-col gap-2'>";
                                            
                                            // Tombol Aksi
                                            if($h['status'] == 'confirmed' && $h['is_paid'] == 0) {
                                                if($h['payment_status'] == 'pending') {
                                                    echo "<span class='text-xs text-gray-500 italic'>
                                                            <i class='fas fa-info-circle'></i> Sedang dicek admin
                                                          </span>";
                                                } else {
                                                    echo "<a href='payment.php?id={$h['id']}&amount={$h['price']}' 
                                                            class='inline-flex items-center justify-center gap-2 px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded-lg hover:bg-blue-700 transition-all'>
                                                            <i class='fas fa-credit-card'></i> Bayar Sekarang
                                                          </a>";
                                                }
                                            }
                                            
                                            // Tombol Survei
                                            if(($h['status'] == 'completed' || $h['is_paid'] == 1) && !$h['survey_id']) {
                                                echo "<a href='survey.php?id={$h['id']}' 
                                                        class='inline-flex items-center justify-center gap-2 px-4 py-2 bg-accent text-white text-xs font-semibold rounded-lg hover:bg-accent/90 transition-all'>
                                                        <i class='fas fa-star'></i> Isi Survei
                                                      </a>";
                                            }
                                    echo "</div>
                                        </td>
                                    </tr>";
                                }
                            } else {
                                echo "<tr>
                                        <td colspan='5' class='text-center py-12'>
                                            <i class='fas fa-inbox text-5xl text-gray-300 mb-4'></i>
                                            <p class='text-gray-500 font-medium'>Belum ada riwayat booking</p>
                                            <p class='text-sm text-gray-400 mt-2'>Mulai booking treatment pertama Anda sekarang</p>
                                        </td>
                                      </tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </section>

    <!-- Modal Booking -->
    <div id="bookingModal" class="hidden fixed inset-0 bg-black/60 modal-backdrop z-50 flex items-center justify-center p-4">
        <div class="modal-content bg-white rounded-2xl shadow-2xl w-full max-w-3xl max-h-[90vh] overflow-y-auto">
            <!-- Modal Header -->
            <div class="bg-gradient-to-r from-primary to-gray-800 px-6 py-5 rounded-t-2xl flex items-center justify-between sticky top-0 z-10">
                <div class="flex items-center gap-3">
                    <i class="fas fa-calendar-plus text-2xl text-accent"></i>
                    <h2 class="text-xl font-semibold text-white">Buat Booking Baru</h2>
                </div>
                <button id="closeModalBtn" class="text-white hover:text-accent transition-colors">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <form method="POST" class="p-6 space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Treatment -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-spa text-accent mr-2"></i>Pilih Treatment
                        </label>
                        <select name="treatment" required 
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-accent focus:outline-none transition-colors">
                            <option value="">-- Pilih Treatment --</option>
                            <?php
                            $t = $pdo->query("SELECT * FROM treatments ORDER BY name");
                            while($r = $t->fetch()){ 
                                echo "<option value='{$r['id']}'>{$r['name']} - Rp ".number_format($r['price'], 0, ',', '.')."</option>"; 
                            }
                            ?>
                        </select>
                    </div>

                    <!-- Therapist -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-user-nurse text-accent mr-2"></i>Pilih Terapis
                        </label>
                        <select name="therapist" required 
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-accent focus:outline-none transition-colors">
                            <option value="">-- Pilih Terapis --</option>
                            <?php
                            $th = $pdo->query("SELECT * FROM therapists ORDER BY name");
                            while($r = $th->fetch()){ 
                                echo "<option value='{$r['id']}'>{$r['name']}</option>"; 
                            }
                            ?>
                        </select>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Date -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-calendar-day text-accent mr-2"></i>Tanggal
                        </label>
                        <input type="date" name="date" min="<?php echo date('Y-m-d'); ?>" required 
                               class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-accent focus:outline-none transition-colors">
                    </div>

                    <!-- Time -->
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-2">
                            <i class="fas fa-clock text-accent mr-2"></i>Jam (09:00 - 21:00)
                        </label>
                        <select name="time" required 
                                class="w-full px-4 py-3 border-2 border-gray-300 rounded-lg focus:border-accent focus:outline-none transition-colors">
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

                <!-- Info Box -->
                <div class="bg-blue-50 border-l-4 border-blue-500 p-4 rounded-lg">
                    <div class="flex items-start">
                        <i class="fas fa-info-circle text-blue-600 mt-1 mr-3"></i>
                        <div class="text-sm text-blue-800">
                            <p class="font-semibold mb-1">Catatan Penting:</p>
                            <ul class="list-disc list-inside space-y-1 text-xs">
                                <li>Pastikan memilih jadwal yang sesuai dengan ketersediaan Anda</li>
                                <li>Booking akan dikonfirmasi oleh admin dalam 1x24 jam</li>
                                <li>Pembayaran dapat dilakukan setelah booking dikonfirmasi</li>
                            </ul>
                        </div>
                    </div>
                </div>

                <!-- Modal Footer -->
                <div class="flex gap-3 pt-4 border-t border-gray-200">
                    <button type="button" id="cancelBtn" 
                            class="flex-1 px-6 py-3 bg-gray-200 text-gray-700 font-semibold rounded-lg hover:bg-gray-300 transition-all">
                        <i class="fas fa-times mr-2"></i>Batal
                    </button>
                    <button type="submit" name="booking" 
                            class="flex-1 px-6 py-3 bg-primary text-white font-semibold rounded-lg hover:bg-black transition-all shadow-lg">
                        <i class="fas fa-calendar-check mr-2"></i>Booking Sekarang
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-primary text-white py-8 mt-12">
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

        // Modal Toggle
        const openModalBtn = document.getElementById('openModalBtn');
        const closeModalBtn = document.getElementById('closeModalBtn');
        const cancelBtn = document.getElementById('cancelBtn');
        const bookingModal = document.getElementById('bookingModal');

        openModalBtn.addEventListener('click', () => {
            bookingModal.classList.remove('hidden');
            document.body.style.overflow = 'hidden';
        });

        function closeModal() {
            bookingModal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        closeModalBtn.addEventListener('click', closeModal);
        cancelBtn.addEventListener('click', closeModal);

        // Close modal when clicking outside
        bookingModal.addEventListener('click', (e) => {
            if (e.target === bookingModal) {
                closeModal();
            }
        });

        // Close modal with Escape key
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape' && !bookingModal.classList.contains('hidden')) {
                closeModal();
            }
        });

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
    </script>

</body>
</html>