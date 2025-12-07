<?php
require '../config.php';
checkAccess('admin');

// ==========================================
// 1. LOGIC MENANGANI BOOKING & PEMBAYARAN
// ==========================================
if (isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $act = $_GET['action'];

    if ($act == 'approve') {
        $pdo->prepare("UPDATE bookings SET status='confirmed' WHERE id=?")->execute([$id]);
    } elseif ($act == 'reject') {
        $pdo->prepare("UPDATE bookings SET status='cancelled' WHERE id=?")->execute([$id]);
    } elseif ($act == 'complete') {
        $pdo->prepare("UPDATE bookings SET status='completed' WHERE id=?")->execute([$id]);
    } elseif ($act == 'confirm_pay') {
        $pdo->prepare("UPDATE bookings SET is_paid=1 WHERE id=?")->execute([$id]);
        $pdo->prepare("UPDATE transactions SET payment_status='paid' WHERE booking_id=?")->execute([$id]);
    }

    header("Location: admin.php?page=bookings");
    exit;
}

// ==========================================
// 2. LOGIC CRUD TREATMENT
// ==========================================

if (isset($_POST['add_treatment'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $duration = $_POST['duration'];

    $stmt = $pdo->prepare("INSERT INTO treatments (name, price, duration) VALUES (?, ?, ?)");
    $stmt->execute([$name, $price, $duration]);
    echo "<script>alert('Treatment berhasil ditambahkan!'); window.location='admin.php?page=treatments';</script>";
}

if (isset($_POST['edit_treatment'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $duration = $_POST['duration'];

    $stmt = $pdo->prepare("UPDATE treatments SET name=?, price=?, duration=? WHERE id=?");
    $stmt->execute([$name, $price, $duration, $id]);
    echo "<script>alert('Treatment berhasil diupdate!'); window.location='admin.php?page=treatments';</script>";
}

if (isset($_GET['delete_treatment'])) {
    $id = $_GET['delete_treatment'];
    try {
        $pdo->prepare("DELETE FROM treatments WHERE id=?")->execute([$id]);
        echo "<script>alert('Treatment berhasil dihapus!'); window.location='admin.php?page=treatments';</script>";
    } catch (Exception $e) {
        echo "<script>alert('Gagal hapus: Treatment sedang digunakan dalam riwayat booking!'); window.location='admin.php?page=treatments';</script>";
    }
}

$edit_treatment = null;
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM treatments WHERE id=?");
    $stmt->execute([$_GET['edit']]);
    $edit_treatment = $stmt->fetch();
}

// ==========================================
// 3. LOGIC KELOLA MEMBER
// ==========================================

if (isset($_GET['delete_member'])) {
    $mid = $_GET['delete_member'];
    try {
        $pdo->prepare("DELETE FROM users WHERE id=? AND role='member'")->execute([$mid]);
        echo "<script>alert('Member berhasil dihapus!'); window.location='admin.php?page=members';</script>";
    } catch (Exception $e) {
        echo "<script>alert('Gagal hapus: Member ini memiliki riwayat transaksi. Data tidak bisa dihapus demi arsip.'); window.location='admin.php?page=members';</script>";
    }
}

if (isset($_GET['reset_member'])) {
    $mid = $_GET['reset_member'];
    $new_pass = password_hash('password', PASSWORD_DEFAULT);
    $pdo->prepare("UPDATE users SET password=? WHERE id=?")->execute([$new_pass, $mid]);
    echo "<script>alert('Password berhasil direset menjadi: password'); window.location='admin.php?page=members';</script>";
}

$page = isset($_GET['page']) ? $_GET['page'] : 'bookings';
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Beautybar</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        function openModal() {
            document.getElementById('treatmentModal').classList.remove('hidden');
            document.getElementById('modalTitle').textContent = '➕ Tambah Treatment Baru';
            document.getElementById('treatmentForm').reset();
            document.getElementById('treatmentId').value = '';
            document.getElementById('submitBtn').name = 'add_treatment';
            document.getElementById('submitBtn').textContent = '➕ Tambah Treatment';
        }

        function closeModal() {
            document.getElementById('treatmentModal').classList.add('hidden');
        }

        function editTreatment(id, name, price, duration) {
            document.getElementById('treatmentModal').classList.remove('hidden');
            document.getElementById('modalTitle').textContent = '✏️ Edit Treatment';
            document.getElementById('treatmentId').value = id;
            document.getElementById('treatmentName').value = name;
            document.getElementById('treatmentPrice').value = price;
            document.getElementById('treatmentDuration').value = duration;
            document.getElementById('submitBtn').name = 'edit_treatment';
            document.getElementById('submitBtn').textContent = '💾 Update Treatment';
        }

        // Close modal when clicking outside
        document.addEventListener('click', function (event) {
            const modal = document.getElementById('treatmentModal');
            if (event.target === modal) {
                closeModal();
            }
        });

        // Auto open modal if edit parameter exists
        window.addEventListener('load', function () {
            <?php if ($edit_treatment): ?>
                editTreatment(
                    <?php echo $edit_treatment['id']; ?>,
                    "<?php echo addslashes($edit_treatment['name']); ?>",
                    <?php echo $edit_treatment['price']; ?>,
                    <?php echo $edit_treatment['duration']; ?>
                );
            <?php endif; ?>
        });
    </script>
</head>

<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-white shadow-lg">
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
                <a href="dashboard.php"
                    class="flex items-center px-4 py-3 mb-2 rounded-lg <?php echo $page == 'dashboard' ? 'bg-purple-600 text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <span class="mr-3">📊</span>
                    <span>Dashboard</span>
                </a>
                
                <a href="admin.php?page=bookings"
                    class="flex items-center px-4 py-3 mb-2 rounded-lg <?php echo $page == 'bookings' ? 'bg-purple-600 text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <span class="mr-3">📅</span>
                    <span>Kelola Booking</span>
                </a>

                <a href="admin.php?page=treatments"
                    class="flex items-center px-4 py-3 mb-2 rounded-lg <?php echo $page == 'treatments' ? 'bg-purple-600 text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <span class="mr-3">💆</span>
                    <span>Kelola Treatment</span>
                </a>

                <a href="admin.php?page=members"
                    class="flex items-center px-4 py-3 mb-2 rounded-lg <?php echo $page == 'members' ? 'bg-purple-600 text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <span class="mr-3">👥</span>
                    <span>Kelola Member</span>
                </a>
            </nav>

            <div class="px-4 mt-8 pt-8 border-t">
                <a href="../index.php"
                    class="flex items-center px-4 py-3 mb-2 text-gray-700 rounded-lg hover:bg-gray-100">
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
        <main class="flex-1 p-8">
            <!-- Header -->
            <div class="mb-8">
                <h1 class="text-3xl font-bold text-gray-800">
                    <?php
                    if ($page == 'bookings')
                        echo 'Kelola Booking';
                    elseif ($page == 'treatments')
                        echo 'Kelola Treatment';
                    elseif ($page == 'members')
                        echo 'Kelola Member';
                    ?>
                </h1>
                <p class="text-gray-500 mt-1">Kelola data dengan mudah</p>
            </div>

            <?php if ($page == 'bookings'): ?>

                <!-- Bookings Page -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b">
                                <tr>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">ID</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Member &
                                        Treatment</th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Jadwal
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Status
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Pembayaran
                                    </th>
                                    <th class="px-6 py-4 text-left text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php
                                $sql = "SELECT b.*, u.username, t.name as treat, tr.proof_image, tr.payment_status 
                                    FROM bookings b
                                    JOIN users u ON b.user_id=u.id
                                    JOIN treatments t ON b.treatment_id=t.id
                                    LEFT JOIN transactions tr ON b.id=tr.booking_id
                                    ORDER BY b.created_at DESC";
                                $q = $pdo->query($sql);

                                while ($row = $q->fetch()) {
                                    $st = $row['status'];
                                    $paySt = $row['payment_status'];

                                    $statusColors = [
                                        'pending' => 'bg-yellow-100 text-yellow-800',
                                        'confirmed' => 'bg-green-100 text-green-800',
                                        'cancelled' => 'bg-red-100 text-red-800',
                                        'completed' => 'bg-blue-100 text-blue-800'
                                    ];
                                    $badgeClass = $statusColors[$st] ?? 'bg-gray-100 text-gray-800';

                                    echo "<tr class='hover:bg-gray-50'>
                                    <td class='px-6 py-4 text-sm font-medium text-gray-900'>#{$row['id']}</td>
                                    <td class='px-6 py-4'>
                                        <div class='text-sm font-medium text-gray-900'>{$row['username']}</div>
                                        <div class='text-sm text-gray-500'>{$row['treat']}</div>
                                    </td>
                                    <td class='px-6 py-4'>
                                        <div class='text-sm text-gray-900'>{$row['booking_date']}</div>
                                        <div class='text-sm font-semibold text-purple-600'>{$row['booking_time']}</div>
                                    </td>
                                    <td class='px-6 py-4'>
                                        <span class='px-3 py-1 inline-flex text-xs leading-5 font-semibold rounded-full $badgeClass'>
                                            $st
                                        </span>
                                    </td>
                                    <td class='px-6 py-4'>";
                                    if ($paySt == 'paid') {
                                        echo "<span class='text-green-600 font-semibold'>✔ LUNAS</span>";
                                    } elseif ($row['proof_image']) {
                                        echo "<a href='../assets/uploads/{$row['proof_image']}' target='_blank' class='text-blue-600 hover:underline'>📷 Lihat Bukti</a>";
                                        if ($paySt == 'pending')
                                            echo "<br><span class='text-xs text-orange-500'>⏳ Perlu Validasi</span>";
                                    } else {
                                        echo "<span class='text-gray-400'>-</span>";
                                    }
                                    echo "</td>
                                    <td class='px-6 py-4 text-sm'>";
                                    if ($st == 'pending') {
                                        echo "<a href='?page=bookings&action=approve&id={$row['id']}' class='inline-block px-3 py-1 mb-1 bg-green-500 text-white rounded hover:bg-green-600 mr-1'>✓ Terima</a>";
                                        echo "<a href='?page=bookings&action=reject&id={$row['id']}' class='inline-block px-3 py-1 mb-1 bg-red-500 text-white rounded hover:bg-red-600'>✗ Tolak</a>";
                                    }
                                    if ($row['proof_image'] && $paySt == 'pending' && $st == 'confirmed') {
                                        echo "<a href='?page=bookings&action=confirm_pay&id={$row['id']}' class='inline-block px-3 py-1 mb-1 bg-purple-500 text-white rounded hover:bg-purple-600'>💰 Validasi</a>";
                                    }
                                    if ($st == 'confirmed' && $row['is_paid'] == 1) {
                                        echo "<a href='?page=bookings&action=complete&id={$row['id']}' class='inline-block px-3 py-1 mb-1 bg-blue-500 text-white rounded hover:bg-blue-600'>✓ Selesai</a>";
                                    }
                                    if ($st == 'completed' || $st == 'cancelled') {
                                        echo "<span class='text-gray-400 text-xs'>Arsip</span>";
                                    }
                                    echo "</td>
                                </tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php elseif ($page == 'treatments'): ?>

                <!-- Treatments Page -->
                <!-- Button Tambah Treatment -->
                <div class="mb-6">
                    <button onclick="openModal()"
                        class="px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium shadow-md">
                        ➕ Tambah Treatment Baru
                    </button>
                </div>

                <!-- Modal Tambah/Edit Treatment -->
                <div id="treatmentModal"
                    class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center">
                    <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl mx-4">
                        <div class="flex items-center justify-between p-6 border-b">
                            <h3 id="modalTitle" class="text-xl font-semibold text-gray-800">
                                ➕ Tambah Treatment Baru
                            </h3>
                            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <form method="POST" id="treatmentForm" class="p-6">
                            <input type="hidden" id="treatmentId" name="id" value="">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Treatment</label>
                                    <input type="text" id="treatmentName" name="name"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                        required>
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700 mb-2">Harga (Rp)</label>
                                    <input type="number" id="treatmentPrice" name="price"
                                        class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                        required>
                                </div>
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-medium text-gray-700 mb-2">Durasi (Menit)</label>
                                <input type="number" id="treatmentDuration" name="duration" value="60"
                                    class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent"
                                    required>
                            </div>

                            <div class="flex gap-2 justify-end">
                                <button type="button" onclick="closeModal()"
                                    class="px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium">
                                    Batal
                                </button>
                                <button type="submit" id="submitBtn" name="add_treatment"
                                    class="px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium">
                                    ➕ Tambah Treatment
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 border-b">
                        <h3 class="text-lg font-semibold text-gray-800">Daftar Treatment</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Nama
                                        Treatment</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Harga</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Durasi
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php
                                $treatments = $pdo->query("SELECT * FROM treatments ORDER BY id DESC");
                                while ($t = $treatments->fetch()) {
                                    echo "<tr class='hover:bg-gray-50'>
                                    <td class='px-6 py-4 text-sm text-gray-900'>#{$t['id']}</td>
                                    <td class='px-6 py-4 text-sm font-medium text-gray-900'>{$t['name']}</td>
                                    <td class='px-6 py-4 text-sm text-gray-900'>Rp " . number_format($t['price']) . "</td>
                                    <td class='px-6 py-4 text-sm text-gray-900'>{$t['duration']} menit</td>
                                    <td class='px-6 py-4 text-sm'>
                                        <button onclick='editTreatment({$t['id']}, \"{$t['name']}\", {$t['price']}, {$t['duration']})' 
                                           class='inline-block px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 mr-2'>
                                            ✏️ Edit
                                        </button>
                                        <a href='?page=treatments&delete_treatment={$t['id']}' 
                                           onclick='return confirm(\"Yakin hapus?\")' 
                                           class='inline-block px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600'>
                                            🗑️ Hapus
                                        </a>
                                    </td>
                                </tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php elseif ($page == 'members'): ?>

                <!-- Members Page -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 border-b">
                        <h3 class="text-lg font-semibold text-gray-800">Daftar Member</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">ID</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Nama
                                        Member</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Email</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Tanggal
                                        Bergabung</th>
                                    <th class="px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php
                                $members = $pdo->query("SELECT * FROM users WHERE role='member' ORDER BY created_at DESC");

                                if ($members->rowCount() > 0) {
                                    while ($m = $members->fetch()) {
                                        echo "<tr class='hover:bg-gray-50'>
                                        <td class='px-6 py-4 text-sm text-gray-900'>#{$m['id']}</td>
                                        <td class='px-6 py-4 text-sm font-medium text-gray-900'>{$m['username']}</td>
                                        <td class='px-6 py-4 text-sm text-gray-600'>{$m['email']}</td>
                                        <td class='px-6 py-4 text-sm text-gray-600'>{$m['created_at']}</td>
                                        <td class='px-6 py-4 text-sm'>
                                            <a href='?page=members&reset_member={$m['id']}' 
                                               onclick='return confirm(\"Reset password member ini menjadi: password ?\")' 
                                               class='inline-block px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600 mr-2'>
                                                🔑 Reset Pass
                                            </a>
                                            <a href='?page=members&delete_member={$m['id']}' 
                                               onclick='return confirm(\"Yakin hapus member ini? Data tidak bisa kembali.\")' 
                                               class='inline-block px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600'>
                                                🗑️ Hapus
                                            </a>
                                        </td>
                                    </tr>";
                                    }
                                } else {
                                    echo "<tr>
                                    <td colspan='5' class='px-6 py-8 text-center text-gray-500'>
                                        Belum ada member yang terdaftar.
                                    </td>
                                </tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php endif; ?>
        </main>
    </div>
</body>

</html>