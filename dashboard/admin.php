<?php
require '../config.php';
checkAccess('admin');

// 1. LOGIC MENANGANI BOOKING & PEMBAYARAN
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
        // Konfirmasi pembayaran dan auto-approve booking jika masih pending
        $pdo->prepare("UPDATE bookings SET is_paid=1, status=CASE WHEN status='pending' THEN 'confirmed' ELSE status END WHERE id=?")->execute([$id]);
        $pdo->prepare("UPDATE transactions SET payment_status='paid' WHERE booking_id=?")->execute([$id]);
    }

    header("Location: admin.php?page=bookings");
    exit;
}

// 2. LOGIC CRUD TREATMENT

if (isset($_POST['add_treatment'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $duration = $_POST['duration'];
    $category_id = !empty($_POST['category_id']) ? $_POST['category_id'] : null;
    $image = null;

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
        $image_name = time() . '_' . basename($_FILES['image']['name']);
        $image_path = '../assets/uploads/' . $image_name;

        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (in_array($_FILES['image']['type'], $allowed_types)) {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                $image = $image_name;
            }
        }
    }

    $stmt = $pdo->prepare("INSERT INTO treatments (name, price, duration, category_id, image) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$name, $price, $duration, $category_id, $image]);
    echo "<script>alert('Treatment berhasil ditambahkan!'); window.location='admin.php?page=treatments';</script>";
}

if (isset($_POST['edit_treatment'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $duration = $_POST['duration'];
    $category_id = !empty($_POST['category_id']) ? $_POST['category_id'] : null;

    // Get current image
    $stmt = $pdo->prepare("SELECT image FROM treatments WHERE id=?");
    $stmt->execute([$id]);
    $current = $stmt->fetch();
    $image = $current['image'];

    // Handle image upload
    if (isset($_FILES['image']) && $_FILES['image']['size'] > 0) {
        $image_name = time() . '_' . basename($_FILES['image']['name']);
        $image_path = '../assets/uploads/' . $image_name;

        $allowed_types = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        if (in_array($_FILES['image']['type'], $allowed_types)) {
            if (move_uploaded_file($_FILES['image']['tmp_name'], $image_path)) {
                if ($current['image'] && file_exists('../assets/uploads/' . $current['image'])) {
                    unlink('../assets/uploads/' . $current['image']);
                }
                $image = $image_name;
            }
        }
    }

    $stmt = $pdo->prepare("UPDATE treatments SET name=?, price=?, duration=?, category_id=?, image=? WHERE id=?");
    $stmt->execute([$name, $price, $duration, $category_id, $image, $id]);
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

// 3. LOGIC KELOLA MEMBER

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
    <script>
        function openModal() {
            document.getElementById('treatmentModal').classList.remove('hidden');
            document.getElementById('modalTitle').textContent = '➕ Tambah Treatment Baru';
            document.getElementById('treatmentForm').reset();
            document.getElementById('treatmentId').value = '';
            document.getElementById('submitBtn').name = 'add_treatment';
            document.getElementById('submitBtn').textContent = '➕ Tambah Treatment';
            document.getElementById('imagePreview').classList.add('hidden');
            document.getElementById('treatmentImage').value = '';
            document.getElementById('treatmentCategory').value = '';
        }

        function closeModal() {
            document.getElementById('treatmentModal').classList.add('hidden');
        }

        function editTreatment(id, name, price, duration, category_id) {
            document.getElementById('treatmentModal').classList.remove('hidden');
            document.getElementById('modalTitle').textContent = '✏️ Edit Treatment';
            document.getElementById('treatmentId').value = id;
            document.getElementById('treatmentName').value = name;
            document.getElementById('treatmentPrice').value = price;
            document.getElementById('treatmentDuration').value = duration;
            document.getElementById('treatmentCategory').value = category_id || '';
            document.getElementById('submitBtn').name = 'edit_treatment';
            document.getElementById('submitBtn').textContent = '💾 Update Treatment';
        }

        // Image preview handler
        document.addEventListener('DOMContentLoaded', function () {
            const imageInput = document.getElementById('treatmentImage');
            if (imageInput) {
                imageInput.addEventListener('change', function (e) {
                    const file = e.target.files[0];
                    if (file) {
                        const reader = new FileReader();
                        reader.onload = function (event) {
                            document.getElementById('previewImg').src = event.target.result;
                            document.getElementById('imagePreview').classList.remove('hidden');
                        }
                        reader.readAsDataURL(file);
                    }
                });
            }

            // Drag and drop
            const fileInput = document.getElementById('treatmentImage');
            const dropZone = fileInput?.parentElement;

            if (dropZone) {
                dropZone.addEventListener('dragover', (e) => {
                    e.preventDefault();
                    dropZone.parentElement.classList.add('bg-purple-100');
                });

                dropZone.addEventListener('dragleave', () => {
                    dropZone.parentElement.classList.remove('bg-purple-100');
                });

                dropZone.addEventListener('drop', (e) => {
                    e.preventDefault();
                    dropZone.parentElement.classList.remove('bg-purple-100');
                    const files = e.dataTransfer.files;
                    if (files.length > 0) {
                        fileInput.files = files;
                        const event = new Event('change', {
                            bubbles: true
                        });
                        fileInput.dispatchEvent(event);
                    }
                });
            }
        });

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
                    <?php echo $edit_treatment['duration']; ?>,
                    <?php echo $edit_treatment['category_id'] ?: 'null'; ?>
                );
            <?php endif; ?>
        });
    </script>
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
                <a href="dashboard.php"
                    class="flex items-center px-4 py-3 mb-2 rounded-lg <?php echo $page == 'dashboard' ? 'bg-purple-600 text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <span>Dashboard</span>
                </a>

                <a href="admin.php?page=bookings"
                    class="flex items-center px-4 py-3 mb-2 rounded-lg <?php echo $page == 'bookings' ? 'bg-purple-600 text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <span>Kelola Booking</span>
                </a>

                <a href="categories.php"
                    class="flex items-center px-4 py-3 mb-2 rounded-lg text-gray-700 hover:bg-gray-100">
                    <span>Kelola Kategori</span>
                </a>

                <a href="admin.php?page=treatments"
                    class="flex items-center px-4 py-3 mb-2 rounded-lg <?php echo $page == 'treatments' ? 'bg-purple-600 text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <span>Kelola Treatment</span>
                </a>

                <a href="therapists.php"
                    class="flex items-center px-4 py-3 mb-2 rounded-lg text-gray-700 hover:bg-gray-100">
                    <span>Kelola Terapis</span>
                </a>

                <a href="admin.php?page=members"
                    class="flex items-center px-4 py-3 mb-2 rounded-lg <?php echo $page == 'members' ? 'bg-purple-600 text-white' : 'text-gray-700 hover:bg-gray-100'; ?>">
                    <span>Kelola Member</span>
                </a>
            </nav>

            <div class="px-4 mt-8 pt-8 border-t">
                <a href="../index.php"
                    class="flex items-center px-4 py-3 mb-2 text-gray-700 rounded-lg hover:bg-gray-100">
                    <span>Halaman Utama</span>
                </a>
                <a href="../logout.php" class="flex items-center px-4 py-3 text-red-600 rounded-lg hover:bg-red-50">
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-4 md:p-8 pt-20 md:pt-8">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">
                    <?php
                    if ($page == 'bookings')
                        echo 'Kelola Booking';
                    elseif ($page == 'treatments')
                        echo 'Kelola Treatment';
                    elseif ($page == 'members')
                        echo 'Kelola Member';
                    ?>
                </h1>
                <p class="text-gray-500 mt-1 text-sm md:text-base">Kelola data dengan mudah</p>
            </div>

            <?php if ($page == 'bookings'): ?>

                <!-- Bookings Page -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <!-- Mobile Card View -->
                    <div class="block lg:hidden p-2 space-y-3">
                        <?php
                        $sql = "SELECT b.*, 
                    u.username, 
                    t.name as treat, 
                    tr.proof_image, 
                    tr.payment_status,
                    b.reschedule_count,
                    b.original_date,
                    b.original_time,
                    b.last_reschedule_at
                FROM bookings b
                JOIN users u ON b.user_id=u.id
                JOIN treatments t ON b.treatment_id=t.id
                LEFT JOIN transactions tr ON b.id=tr.booking_id
                ORDER BY b.created_at DESC";
                        $q = $pdo->query($sql);

                        while ($row = $q->fetch()) {
                            $st = $row['status'];
                            $paySt = $row['payment_status'];

                            // Warna status
                            $statusColors = [
                                'pending' => 'bg-yellow-100 text-yellow-800 border border-yellow-300',
                                'confirmed' => 'bg-green-100 text-green-800 border border-green-300',
                                'cancelled' => 'bg-red-100 text-red-800 border border-red-300',
                                'completed' => 'bg-blue-100 text-blue-800 border border-blue-300'
                            ];
                            $badgeClass = $statusColors[$st] ?? 'bg-gray-100 text-gray-800 border border-gray-300';

                            // Format tanggal
                            $formattedDate = date('d M Y', strtotime($row['booking_date']));
                            ?>

                            <!-- Mobile Card -->
                            <div
                                class="border-b border-gray-200 p-4 mb-3 hover:bg-purple-50 transition-colors bg-white rounded-lg shadow-sm mx-2">
                                <!-- Header: Member & Status -->
                                <div class="flex justify-between items-start mb-3">
                                    <div class="flex-1">
                                        <h3 class="font-bold text-gray-900 text-sm mb-1">
                                            <?= htmlspecialchars($row['username']) ?>
                                        </h3>
                                        <p class="text-xs text-gray-600">
                                            <?= htmlspecialchars($row['treat']) ?>
                                        </p>
                                    </div>
                                    <span
                                        class="px-3 py-1 text-xs font-bold rounded-full <?= $badgeClass ?> uppercase ml-2 whitespace-nowrap">
                                        <?= $st ?>
                                    </span>
                                </div>

                                <!-- Jadwal -->
                                <div class="flex items-center gap-2 mb-3 bg-purple-50 rounded-lg p-2.5">
                                    <svg class="w-4 h-4 text-purple-600 flex-shrink-0" fill="none" stroke="currentColor"
                                        viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                            d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                    </svg>
                                    <div class="flex-1">
                                        <span class="text-xs font-semibold text-gray-900"><?= $formattedDate ?></span>
                                        <span
                                            class="text-sm font-bold text-purple-600 ml-2"><?= substr($row['booking_time'], 0, 5) ?></span>
                                    </div>
                                </div>

                                <!-- Reschedule Info -->
                                <?php if ($row['reschedule_count'] > 0): ?>
                                    <div class="mb-3 bg-orange-50 border border-orange-200 rounded-lg p-2.5">
                                        <div class="flex items-start gap-2">
                                            <span class="text-lg">🔄</span>
                                            <div class="flex-1 text-xs">
                                                <div class="font-bold text-orange-800 mb-1">
                                                    Reschedule (<?= $row['reschedule_count'] ?>x)
                                                </div>
                                                <div class="text-orange-700">
                                                    <div class="mb-1">
                                                        <span class="text-gray-600">Jadwal Asli:</span>
                                                        <span class="font-semibold">
                                                            <?= date('d M Y', strtotime($row['original_date'])) ?>
                                                            <?= date('H:i', strtotime($row['original_time'])) ?>
                                                        </span>
                                                    </div>
                                                    <div>
                                                        <span class="text-gray-600">Diubah:</span>
                                                        <span class="font-semibold">
                                                            <?= date('d/m H:i', strtotime($row['last_reschedule_at'])) ?>
                                                        </span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>

                                <!-- Pembayaran -->
                                <div class="mb-3">
                                    <?php if ($paySt == 'paid'): ?>
                                        <div
                                            class="flex items-center justify-between bg-green-50 border border-green-200 rounded-lg p-2.5">
                                            <span class="flex items-center gap-2">
                                                <svg class="w-4 h-4 text-green-600" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                <span class="text-xs font-bold text-green-700">DP LUNAS</span>
                                            </span>
                                            <span class="text-xs font-semibold text-green-700">Rp 50.000</span>
                                        </div>
                                    <?php elseif ($row['proof_image']): ?>
                                        <div class="space-y-1.5">
                                            <a href="../assets/uploads/<?= $row['proof_image'] ?>" target="_blank"
                                                class="flex items-center justify-center gap-2 px-3 py-2 bg-blue-50 text-blue-700 text-xs font-semibold rounded-lg hover:bg-blue-100 border border-blue-200 transition-colors">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                        d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                                </svg>
                                                Lihat Bukti DP
                                            </a>
                                            <?php if ($paySt == 'pending'): ?>
                                                <div
                                                    class="flex items-center justify-center gap-1 px-2 py-1.5 bg-orange-50 text-orange-600 text-xs font-semibold rounded">
                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd"
                                                            d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z"
                                                            clip-rule="evenodd" />
                                                    </svg>
                                                    Perlu Validasi
                                                </div>
                                            <?php endif; ?>
                                        </div>
                                    <?php else: ?>
                                        <div class="text-center py-2 bg-gray-50 rounded-lg border border-gray-200">
                                            <span class="text-gray-400 text-xs block">Belum Upload DP</span>
                                            <span class="text-xs text-gray-500">DP: Rp 50.000</span>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <!-- Aksi -->
                                <div class="space-y-2">
                                    <?php if ($st == 'pending'): ?>
                                        <div class="grid grid-cols-2 gap-2">
                                            <a href="?page=bookings&action=approve&id=<?= $row['id'] ?>"
                                                class="flex items-center justify-center gap-1 px-3 py-2.5 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors text-xs font-semibold">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                Terima
                                            </a>
                                            <a href="?page=bookings&action=reject&id=<?= $row['id'] ?>"
                                                class="flex items-center justify-center gap-1 px-3 py-2.5 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors text-xs font-semibold"
                                                onclick="return confirm('Yakin tolak booking ini?')">
                                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd"
                                                        d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
                                                        clip-rule="evenodd" />
                                                </svg>
                                                Tolak
                                            </a>
                                        </div>
                                    <?php endif; ?>

                                    <?php if ($row['proof_image'] && $paySt == 'pending' && $st != 'cancelled' && $st != 'completed'): ?>
                                        <a href="?page=bookings&action=confirm_pay&id=<?= $row['id'] ?>"
                                            class="flex items-center justify-center gap-2 w-full px-3 py-2.5 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition-colors text-xs font-semibold"
                                            onclick="return confirm('Konfirmasi pembayaran DP Rp 50.000?')">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path
                                                    d="M8.433 7.418c.155-.103.346-.196.567-.267v1.698a2.305 2.305 0 01-.567-.267C8.07 8.34 8 8.114 8 8c0-.114.07-.34.433-.582zM11 12.849v-1.698c.22.071.412.164.567.267.364.243.433.468.433.582 0 .114-.07.34-.433.582a2.305 2.305 0 01-.567.267z" />
                                                <path fill-rule="evenodd"
                                                    d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-13a1 1 0 10-2 0v.092a4.535 4.535 0 00-1.676.662C6.602 6.234 6 7.009 6 8c0 .99.602 1.765 1.324 2.246.48.32 1.054.545 1.676.662v1.941c-.391-.127-.68-.317-.843-.504a1 1 0 10-1.51 1.31c.562.649 1.413 1.076 2.353 1.253V15a1 1 0 102 0v-.092a4.535 4.535 0 001.676-.662C13.398 13.766 14 12.991 14 12c0-.99-.602-1.765-1.324-2.246A4.535 4.535 0 0011 9.092V7.151c.391.127.68.317.843.504a1 1 0 101.511-1.31c-.563-.649-1.413-1.076-2.354-1.253V5z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            Validasi Bayar
                                        </a>
                                    <?php endif; ?>

                                    <?php if ($st == 'confirmed' && $row['is_paid'] == 1): ?>
                                        <a href="?page=bookings&action=complete&id=<?= $row['id'] ?>"
                                            class="flex items-center justify-center gap-2 w-full px-3 py-2.5 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors text-xs font-semibold"
                                            onclick="return confirm('Tandai booking ini sebagai selesai?')">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path fill-rule="evenodd"
                                                    d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            Tandai Selesai
                                        </a>
                                    <?php endif; ?>

                                    <?php if ($st == 'completed' || $st == 'cancelled'): ?>
                                        <div
                                            class="flex items-center justify-center gap-2 w-full px-3 py-2 bg-gray-100 text-gray-500 text-xs font-semibold rounded-lg border border-gray-200">
                                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                                <path d="M4 3a2 2 0 100 4h12a2 2 0 100-4H4z" />
                                                <path fill-rule="evenodd"
                                                    d="M3 8h14v7a2 2 0 01-2 2H5a2 2 0 01-2-2V8zm5 3a1 1 0 011-1h2a1 1 0 110 2H9a1 1 0 01-1-1z"
                                                    clip-rule="evenodd" />
                                            </svg>
                                            Arsip
                                        </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php } ?>
                    </div>

                    <!-- Desktop Table View -->
                    <div class="hidden lg:block overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gradient-to-r from-purple-50 to-pink-50 border-b-2 border-purple-200">
                                <tr>
                                    <th
                                        class="px-4 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider whitespace-nowrap">
                                        Member & Treatment
                                    </th>
                                    <th
                                        class="px-4 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider whitespace-nowrap">
                                        Jadwal
                                    </th>
                                    <th
                                        class="px-4 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider whitespace-nowrap">
                                        Status Booking
                                    </th>
                                    <th
                                        class="px-4 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider whitespace-nowrap">
                                        Pembayaran
                                    </th>
                                    <th
                                        class="px-4 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider whitespace-nowrap">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php
                                $q->execute(); // Re-execute query for desktop view
                                while ($row = $q->fetch()) {
                                    $st = $row['status'];
                                    $paySt = $row['payment_status'];

                                    $statusColors = [
                                        'pending' => 'bg-yellow-100 text-yellow-800 border border-yellow-300',
                                        'confirmed' => 'bg-green-100 text-green-800 border border-green-300',
                                        'cancelled' => 'bg-red-100 text-red-800 border border-red-300',
                                        'completed' => 'bg-blue-100 text-blue-800 border border-blue-300'
                                    ];
                                    $badgeClass = $statusColors[$st] ?? 'bg-gray-100 text-gray-800 border border-gray-300';
                                    $formattedDate = date('d M Y', strtotime($row['booking_date']));
                                    ?>
                                    <tr class="hover:bg-purple-50 transition-colors duration-150">
                                        <td class="px-4 py-4">
                                            <div class="flex flex-col gap-1">
                                                <span class="text-sm font-bold text-gray-900">
                                                    <?= htmlspecialchars($row['username']) ?>
                                                </span>
                                                <span class="text-xs text-gray-600">
                                                    <?= htmlspecialchars($row['treat']) ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4 whitespace-nowrap">
                                            <div class="flex flex-col gap-1">
                                                <span class="text-xs font-semibold text-gray-900">
                                                    <?= $formattedDate ?>
                                                </span>
                                                <span class="text-sm font-bold text-purple-600">
                                                    <?= substr($row['booking_time'], 0, 5) ?>
                                                </span>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="flex flex-col gap-2">
                                                <span
                                                    class="px-3 py-1.5 inline-flex text-xs font-bold rounded-full uppercase <?= $badgeClass ?> justify-center whitespace-nowrap">
                                                    <?= $st ?>
                                                </span>
                                                <?php if ($row['reschedule_count'] > 0): ?>
                                                    <div class="relative group">
                                                        <button type="button"
                                                            class="w-full px-2 py-1 bg-orange-100 text-orange-800 text-xs font-bold rounded-full border border-orange-300 hover:bg-orange-200 transition-colors cursor-help whitespace-nowrap">
                                                            🔄 Reschedule (<?= $row['reschedule_count'] ?>x)
                                                        </button>
                                                        <div
                                                            class="absolute left-0 top-full mt-1 w-56 p-3 bg-white border-2 border-orange-300 rounded-lg shadow-xl z-50 opacity-0 invisible group-hover:opacity-100 group-hover:visible transition-all duration-200 pointer-events-none">
                                                            <div class="text-xs">
                                                                <div
                                                                    class="font-bold text-orange-900 mb-2 pb-1 border-b border-orange-200">
                                                                    📅 Info Reschedule
                                                                </div>
                                                                <div class="space-y-2">
                                                                    <div>
                                                                        <div
                                                                            class="text-gray-500 text-[10px] uppercase font-semibold mb-0.5">
                                                                            Jadwal Asli
                                                                        </div>
                                                                        <div class="text-gray-900 font-semibold">
                                                                            <?= date('d M Y', strtotime($row['original_date'])) ?>
                                                                        </div>
                                                                        <div class="text-purple-600 font-bold">
                                                                            <?= date('H:i', strtotime($row['original_time'])) ?>
                                                                        </div>
                                                                    </div>
                                                                    <div class="pt-2 border-t border-orange-100">
                                                                        <div class="text-orange-600 font-semibold">
                                                                            ↻ Diubah:
                                                                            <?= date('d/m H:i', strtotime($row['last_reschedule_at'])) ?>
                                                                        </div>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                            <div
                                                                class="absolute -top-2 left-4 w-4 h-4 bg-white border-l-2 border-t-2 border-orange-300 transform rotate-45">
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                        <td class="px-4 py-4">
                                            <?php if ($paySt == 'paid'): ?>
                                                <div class="flex flex-col gap-1">
                                                    <span
                                                        class="px-3 py-1 bg-green-100 text-green-700 text-xs font-bold rounded-full border border-green-300 text-center whitespace-nowrap">
                                                        DP LUNAS
                                                    </span>
                                                    <span class="text-xs text-gray-500 text-center">Rp 50.000</span>
                                                </div>
                                            <?php elseif ($row['proof_image']): ?>
                                                <div class="flex flex-col gap-1">
                                                    <a href="../assets/uploads/<?= $row['proof_image'] ?>" target="_blank"
                                                        class="inline-flex items-center justify-center px-2 py-1 bg-blue-50 text-blue-700 text-xs font-semibold rounded-lg hover:bg-blue-100 border border-blue-200 transition-colors whitespace-nowrap">
                                                        Lihat Bukti DP
                                                    </a>
                                                    <?php if ($paySt == 'pending'): ?>
                                                        <span
                                                            class="px-2 py-1 bg-orange-50 text-orange-600 text-xs font-semibold text-center">
                                                            Perlu Validasi
                                                        </span>
                                                    <?php endif; ?>
                                                </div>
                                            <?php else: ?>
                                                <div class="flex flex-col gap-1 text-center">
                                                    <span class="text-gray-400 text-xs">Belum Upload DP</span>
                                                    <span class="text-xs text-gray-400">DP: Rp 50.000</span>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="flex flex-col gap-1.5">
                                                <?php if ($st == 'pending'): ?>
                                                    <div class="grid grid-cols-2 gap-1.5">
                                                        <a href="?page=bookings&action=approve&id=<?= $row['id'] ?>"
                                                            class="px-2 py-1.5 bg-green-500 text-white rounded-lg hover:bg-green-600 transition-colors text-xs font-semibold text-center whitespace-nowrap">
                                                            Terima
                                                        </a>
                                                        <a href="?page=bookings&action=reject&id=<?= $row['id'] ?>"
                                                            class="px-2 py-1.5 bg-red-500 text-white rounded-lg hover:bg-red-600 transition-colors text-xs font-semibold text-center whitespace-nowrap"
                                                            onclick="return confirm('Yakin tolak booking ini?')">
                                                            Tolak
                                                        </a>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($row['proof_image'] && $paySt == 'pending' && $st != 'cancelled' && $st != 'completed'): ?>
                                                    <a href="?page=bookings&action=confirm_pay&id=<?= $row['id'] ?>"
                                                        class="px-2 py-1.5 bg-purple-500 text-white rounded-lg hover:bg-purple-600 transition-colors text-xs font-semibold text-center whitespace-nowrap"
                                                        onclick="return confirm('Konfirmasi pembayaran DP Rp 50.000?')">
                                                        Validasi Bayar
                                                    </a>
                                                <?php endif; ?>
                                                <?php if ($st == 'confirmed' && $row['is_paid'] == 1): ?>
                                                    <a href="?page=bookings&action=complete&id=<?= $row['id'] ?>"
                                                        class="px-2 py-1.5 bg-blue-500 text-white rounded-lg hover:bg-blue-600 transition-colors text-xs font-semibold text-center whitespace-nowrap"
                                                        onclick="return confirm('Tandai booking ini sebagai selesai?')">
                                                        Selesai
                                                    </a>
                                                <?php endif; ?>
                                                <?php if ($st == 'completed' || $st == 'cancelled'): ?>
                                                    <span
                                                        class="px-2 py-1 bg-gray-100 text-gray-500 text-xs font-semibold rounded-lg border border-gray-200 text-center">
                                                        Arsip
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            <?php elseif ($page == 'treatments'): ?>

                <!-- Treatments Page -->
                <!-- Button Tambah Treatment -->
                <div class="mb-6">
                    <button onclick="openModal()"
                        class="w-full md:w-auto px-6 py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-semibold shadow-md transition-all duration-200">
                        Tambah Treatment Baru
                    </button>
                </div>

                <!-- Modal Tambah/Edit Treatment -->
                <div id="treatmentModal"
                    class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
                    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                        <div class="flex items-center justify-between p-6 border-b bg-white rounded-t-xl">
                            <h3 id="modalTitle" class="text-xl font-bold text-gray-800">
                                Tambah Treatment Baru
                            </h3>
                            <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600 transition-colors">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"></path>
                                </svg>
                            </button>
                        </div>

                        <form method="POST" id="treatmentForm" class="p-6" enctype="multipart/form-data">
                            <input type="hidden" id="treatmentId" name="id" value="">

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Nama Treatment</label>
                                    <input type="text" id="treatmentName" name="name"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                                        placeholder="Contoh: Facial Premium" required>
                                </div>
                                <div>
                                    <label class="block text-sm font-semibold text-gray-700 mb-2">Harga (Rp)</label>
                                    <input type="number" id="treatmentPrice" name="price"
                                        class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                                        placeholder="150000" required>
                                </div>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Durasi (Menit)</label>
                                <input type="number" id="treatmentDuration" name="duration" value="60"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all"
                                    placeholder="60" required>
                            </div>

                            <div class="mb-4">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Kategori Treatment</label>
                                <select id="treatmentCategory" name="category_id"
                                    class="w-full px-4 py-2.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent transition-all">
                                    <option value="">Tanpa Kategori</option>
                                    <?php
                                    $categories = $pdo->query("SELECT * FROM categories ORDER BY name");
                                    while ($c = $categories->fetch()) {
                                        echo "<option value='{$c['id']}'>{$c['name']}</option>";
                                    }
                                    ?>
                                </select>
                            </div>

                            <div class="mb-6">
                                <label class="block text-sm font-semibold text-gray-700 mb-2">Gambar Treatment</label>
                                <div class="flex items-center justify-center w-full">
                                    <label
                                        class="flex flex-col items-center justify-center w-full h-36 border-2 border-dashed border-gray-300 rounded-lg cursor-pointer bg-gray-50 hover:bg-gray-100 transition-colors">
                                        <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                            <svg class="w-10 h-10 text-gray-400 mb-3" fill="none" stroke="currentColor"
                                                viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                    d="M12 4v16m8-8H4"></path>
                                            </svg>
                                            <p class="text-sm text-gray-600 mb-1">
                                                <span class="font-semibold">Klik untuk upload</span> atau drag gambar
                                            </p>
                                            <p class="text-xs text-gray-500">JPG, PNG atau GIF (Max. 5MB)</p>
                                        </div>
                                        <input id="treatmentImage" name="image" type="file" class="hidden" accept="image/*">
                                    </label>
                                </div>
                                <div id="imagePreview" class="mt-4 hidden flex justify-center">
                                    <img id="previewImg" src="" alt="Preview"
                                        class="h-40 object-contain rounded-lg border-2 border-gray-200">
                                </div>
                            </div>

                            <div class="flex flex-col-reverse md:flex-row gap-3 justify-end">
                                <button type="button" onclick="closeModal()"
                                    class="w-full md:w-auto px-6 py-2.5 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-semibold transition-colors">
                                    Batal
                                </button>
                                <button type="submit" id="submitBtn" name="add_treatment"
                                    class="w-full md:w-auto px-6 py-2.5 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-semibold transition-colors">
                                    Tambah Treatment
                                </button>
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Daftar Treatment - Card View untuk Mobile -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 bg-purple-600 border-b">
                        <h3 class="text-lg font-bold text-white">Daftar Treatment</h3>
                    </div>

                    <?php
                    // Pagination setup
                    $limit = 10;
                    $page = isset($_GET['pg']) ? (int) $_GET['pg'] : 1;
                    $offset = ($page - 1) * $limit;

                    // Hitung total data
                    $total_query = $pdo->query("SELECT COUNT(*) as total FROM treatments");
                    $total_data = $total_query->fetch()['total'];
                    $total_pages = ceil($total_data / $limit);

                    // Query dengan limit dan offset
                    $treatments = $pdo->query("
        SELECT t.*, c.name AS category_name
        FROM treatments t
        LEFT JOIN categories c ON t.category_id = c.id
        ORDER BY t.id DESC
        LIMIT $limit OFFSET $offset
    ");
                    ?>

                    <!-- Mobile Card View (Hidden on Desktop) -->
                    <div class="block md:hidden">
                        <?php
                        $treatments->execute();
                        while ($t = $treatments->fetch()) {
                            $image_html = '';
                            if ($t['image']) {
                                $image_html = "<img src='../assets/uploads/{$t['image']}' alt='{$t['name']}' class='w-16 h-16 object-cover rounded-lg'>";
                            } else {
                                $image_html = "<div class='w-16 h-16 bg-purple-600 rounded-lg flex items-center justify-center text-white font-bold text-xl flex-shrink-0'>" . strtoupper(substr($t['name'], 0, 1)) . "</div>";
                            }

                            $category_display = $t['category_name']
                                ? "<span class='inline-block px-2 py-0.5 bg-purple-100 text-purple-700 text-xs font-semibold rounded-full'>{$t['category_name']}</span>"
                                : "<span class='text-xs text-gray-400'>Tanpa Kategori</span>";
                            ?>
                            <div class="border-b border-gray-200 p-4 hover:bg-gray-50">
                                <div class="flex gap-3 mb-3">
                                    <?= $image_html ?>
                                    <div class="flex-1 min-w-0">
                                        <h4 class="text-sm font-bold text-gray-900 mb-1"><?= htmlspecialchars($t['name']) ?>
                                        </h4>
                                        <div class="mb-1"><?= $category_display ?></div>
                                    </div>
                                </div>

                                <div class="grid grid-cols-2 gap-2 mb-3 text-sm">
                                    <div>
                                        <span class="text-gray-500 text-xs">Harga:</span>
                                        <div class="font-bold text-gray-900">Rp <?= number_format($t['price'], 0, ',', '.') ?>
                                        </div>
                                    </div>
                                    <div>
                                        <span class="text-gray-500 text-xs">Durasi:</span>
                                        <div class="font-semibold text-gray-700"><?= $t['duration'] ?> menit</div>
                                    </div>
                                </div>

                                <div class="flex gap-2">
                                    <button
                                        onclick='editTreatment(<?= $t['id'] ?>, "<?= addslashes($t['name']) ?>", <?= $t['price'] ?>, <?= $t['duration'] ?>, <?= ($t['category_id'] ?: 'null') ?>)'
                                        class="flex-1 px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded-lg hover:bg-blue-700 transition-colors">
                                        Edit
                                    </button>
                                    <a href='?page=treatments&delete_treatment=<?= $t['id'] ?>'
                                        onclick='return confirm("Yakin ingin menghapus treatment ini?")'
                                        class="flex-1 px-4 py-2 bg-red-600 text-white text-xs font-semibold rounded-lg hover:bg-red-700 transition-colors text-center">
                                        Hapus
                                    </a>
                                </div>
                            </div>
                        <?php } ?>
                    </div>

                    <!-- Desktop Table View (Hidden on Mobile) -->
                    <div class="hidden md:block overflow-x-auto">
                        <table class="w-full">
                            <thead class="bg-gray-50 border-b-2 border-gray-200">
                                <tr>
                                    <th
                                        class="px-4 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                        Gambar
                                    </th>
                                    <th
                                        class="px-4 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                        Nama & Kategori
                                    </th>
                                    <th
                                        class="px-4 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                        Harga
                                    </th>
                                    <th
                                        class="px-4 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                        Durasi
                                    </th>
                                    <th
                                        class="px-4 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200">
                                <?php
                                // Reset query untuk desktop table
                                $treatments = $pdo->query("
                    SELECT t.*, c.name AS category_name
                    FROM treatments t
                    LEFT JOIN categories c ON t.category_id = c.id
                    ORDER BY t.id DESC
                    LIMIT $limit OFFSET $offset
                ");

                                while ($t = $treatments->fetch()) {
                                    $image_html = '';
                                    if ($t['image']) {
                                        $image_html = "<img src='../assets/uploads/{$t['image']}' alt='{$t['name']}' class='h-16 w-16 object-cover rounded-lg'>";
                                    } else {
                                        $image_html = "<div class='h-16 w-16 bg-purple-600 rounded-lg flex items-center justify-center text-white font-bold text-xl'>" . strtoupper(substr($t['name'], 0, 1)) . "</div>";
                                    }

                                    $category_display = $t['category_name']
                                        ? "<span class='inline-block px-3 py-1 bg-purple-100 text-purple-700 text-xs font-semibold rounded-full'>{$t['category_name']}</span>"
                                        : "<span class='text-xs text-gray-400'>Tanpa Kategori</span>";

                                    echo "<tr class='hover:bg-gray-50 transition-colors duration-150'>
                        <td class='px-4 py-4'>{$image_html}</td>
                        <td class='px-4 py-4'>
                            <div class='text-sm font-bold text-gray-900 mb-1.5'>{$t['name']}</div>
                            <div>{$category_display}</div>
                        </td>
                        <td class='px-4 py-4'>
                            <div class='text-sm font-bold text-gray-900'>Rp " . number_format($t['price'], 0, ',', '.') . "</div>
                        </td>
                        <td class='px-4 py-4'>
                            <span class='text-sm text-gray-700'><span class='font-semibold'>{$t['duration']}</span> menit</span>
                        </td>
                        <td class='px-4 py-4'>
                            <div class='flex flex-wrap gap-2 justify-center'>
                                <button onclick='editTreatment({$t['id']}, \"" . addslashes($t['name']) . "\", {$t['price']}, {$t['duration']}, " . ($t['category_id'] ?: 'null') . ")' 
                                   class='px-4 py-2 bg-blue-600 text-white text-xs font-semibold rounded-lg hover:bg-blue-700 transition-colors'>
                                    Edit
                                </button>
                                <a href='?page=treatments&delete_treatment={$t['id']}' 
                                   onclick='return confirm(\"Yakin ingin menghapus treatment ini?\")' 
                                   class='px-4 py-2 bg-red-600 text-white text-xs font-semibold rounded-lg hover:bg-red-700 transition-colors'>
                                    Hapus
                                </a>
                            </div>
                        </td>
                    </tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination -->
                    <?php if ($total_pages > 1): ?>
                        <div class="px-4 md:px-6 py-4 bg-gray-50 border-t">
                            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                                <!-- Info Halaman -->
                                <div class="text-xs md:text-sm text-gray-600 text-center md:text-left">
                                    Halaman <span class="font-semibold"><?= $page ?></span> dari <span
                                        class="font-semibold"><?= $total_pages ?></span>
                                    <span class="hidden md:inline">(Total: <?= $total_data ?> treatment)</span>
                                </div>

                                <!-- Tombol Pagination -->
                                <div class="flex items-center gap-2">
                                    <!-- Previous Button -->
                                    <?php if ($page > 1): ?>
                                        <a href="?page=treatments&pg=<?= $page - 1 ?>"
                                            class="px-3 md:px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 font-semibold transition-colors text-sm">
                                            Previous
                                        </a>
                                    <?php else: ?>
                                        <span
                                            class="px-3 md:px-4 py-2 bg-gray-100 border border-gray-200 text-gray-400 rounded-lg cursor-not-allowed text-sm">
                                            Previous
                                        </span>
                                    <?php endif; ?>

                                    <!-- Page Numbers (Desktop Only) -->
                                    <div class="hidden md:flex items-center gap-1">
                                        <?php
                                        $start = max(1, $page - 2);
                                        $end = min($total_pages, $page + 2);

                                        if ($start > 1) {
                                            echo "<a href='?page=treatments&pg=1' class='px-3 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors text-sm'>1</a>";
                                            if ($start > 2) {
                                                echo "<span class='px-2 text-gray-500'>...</span>";
                                            }
                                        }

                                        for ($i = $start; $i <= $end; $i++) {
                                            if ($i == $page) {
                                                echo "<span class='px-3 py-2 bg-purple-600 text-white rounded-lg font-semibold text-sm'>$i</span>";
                                            } else {
                                                echo "<a href='?page=treatments&pg=$i' class='px-3 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors text-sm'>$i</a>";
                                            }
                                        }

                                        if ($end < $total_pages) {
                                            if ($end < $total_pages - 1) {
                                                echo "<span class='px-2 text-gray-500'>...</span>";
                                            }
                                            echo "<a href='?page=treatments&pg=$total_pages' class='px-3 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors text-sm'>$total_pages</a>";
                                        }
                                        ?>
                                    </div>

                                    <!-- Next Button -->
                                    <?php if ($page < $total_pages): ?>
                                        <a href="?page=treatments&pg=<?= $page + 1 ?>"
                                            class="px-3 md:px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-semibold transition-colors text-sm">
                                            Next
                                        </a>
                                    <?php else: ?>
                                        <span
                                            class="px-3 md:px-4 py-2 bg-gray-100 border border-gray-200 text-gray-400 rounded-lg cursor-not-allowed text-sm">
                                            Next
                                        </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

            <?php elseif ($page == 'members'): ?>

                <!-- Members Page -->
                <div class="bg-white rounded-lg shadow-md overflow-hidden">
                    <div class="px-6 py-4 bg-purple-600 border-b">
                        <h3 class="text-lg font-bold text-white">Daftar Member</h3>
                    </div>

                    <?php
                    // Pagination setup
                    $limit = 10;
                    $pg = isset($_GET['pg']) ? (int) $_GET['pg'] : 1;
                    $offset = ($pg - 1) * $limit;

                    // Hitung total data
                    $total_query = $pdo->query("SELECT COUNT(*) as total FROM users WHERE role='member'");
                    $total_data = $total_query->fetch()['total'];
                    $total_pages = ceil($total_data / $limit);

                    // Query dengan limit dan offset
                    $members = $pdo->query("SELECT * FROM users WHERE role='member' ORDER BY created_at DESC LIMIT $limit OFFSET $offset");
                    ?>

                    <?php if ($members->rowCount() > 0): ?>

                        <!-- Mobile Card View (Hidden on Desktop) -->
                        <div class="block md:hidden">
                            <?php
                            while ($m = $members->fetch()) {
                                ?>
                                <div class="border-b border-gray-200 p-4 hover:bg-gray-50">
                                    <div class="mb-3">
                                        <h4 class="text-sm font-bold text-gray-900 mb-1"><?= htmlspecialchars($m['username']) ?>
                                        </h4>
                                        <p class="text-xs text-gray-600 mb-1"><?= htmlspecialchars($m['email']) ?></p>
                                        <p class="text-xs text-gray-500"><?= $m['created_at'] ?></p>
                                    </div>

                                    <div class="flex gap-2">
                                        <a href='?page=members&reset_member=<?= $m['id'] ?>'
                                            onclick='return confirm("Reset password member ini menjadi: password ?")'
                                            class="flex-1 px-4 py-2 bg-yellow-500 text-white text-xs font-semibold rounded-lg hover:bg-yellow-600 transition-colors text-center">
                                            Reset Password
                                        </a>
                                        <a href='?page=members&delete_member=<?= $m['id'] ?>'
                                            onclick='return confirm("Yakin hapus member ini? Data tidak bisa kembali.")'
                                            class="flex-1 px-4 py-2 bg-red-600 text-white text-xs font-semibold rounded-lg hover:bg-red-700 transition-colors text-center">
                                            Hapus
                                        </a>
                                    </div>
                                </div>
                            <?php } ?>
                        </div>

                        <!-- Desktop Table View (Hidden on Mobile) -->
                        <div class="hidden md:block overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-50 border-b-2 border-gray-200">
                                    <tr>
                                        <th
                                            class="px-4 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                            Nama Member
                                        </th>
                                        <th
                                            class="px-4 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                            Email
                                        </th>
                                        <th
                                            class="px-4 py-4 text-left text-xs font-bold text-gray-700 uppercase tracking-wider">
                                            Tanggal Bergabung
                                        </th>
                                        <th
                                            class="px-4 py-4 text-center text-xs font-bold text-gray-700 uppercase tracking-wider">
                                            Aksi
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-200">
                                    <?php
                                    // Reset query untuk desktop table
                                    $members = $pdo->query("SELECT * FROM users WHERE role='member' ORDER BY created_at DESC LIMIT $limit OFFSET $offset");

                                    while ($m = $members->fetch()) {
                                        echo "<tr class='hover:bg-gray-50 transition-colors duration-150'>
                            <td class='px-4 py-4'>
                                <div class='text-sm font-bold text-gray-900'>" . htmlspecialchars($m['username']) . "</div>
                            </td>
                            <td class='px-4 py-4'>
                                <div class='text-sm text-gray-700'>" . htmlspecialchars($m['email']) . "</div>
                            </td>
                            <td class='px-4 py-4'>
                                <div class='text-sm text-gray-600'>{$m['created_at']}</div>
                            </td>
                            <td class='px-4 py-4'>
                                <div class='flex flex-wrap gap-2 justify-center'>
                                    <a href='?page=members&reset_member={$m['id']}' 
                                       onclick='return confirm(\"Reset password member ini menjadi: password ?\")' 
                                       class='px-4 py-2 bg-yellow-500 text-white text-xs font-semibold rounded-lg hover:bg-yellow-600 transition-colors'>
                                        Reset Password
                                    </a>
                                    <a href='?page=members&delete_member={$m['id']}' 
                                       onclick='return confirm(\"Yakin hapus member ini? Data tidak bisa kembali.\")' 
                                       class='px-4 py-2 bg-red-600 text-white text-xs font-semibold rounded-lg hover:bg-red-700 transition-colors'>
                                        Hapus
                                    </a>
                                </div>
                            </td>
                        </tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>

                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <div class="px-4 md:px-6 py-4 bg-gray-50 border-t">
                                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                                    <!-- Info Halaman -->
                                    <div class="text-xs md:text-sm text-gray-600 text-center md:text-left">
                                        Halaman <span class="font-semibold"><?= $pg ?></span> dari <span
                                            class="font-semibold"><?= $total_pages ?></span>
                                        <span class="hidden md:inline">(Total: <?= $total_data ?> member)</span>
                                    </div>

                                    <!-- Tombol Pagination -->
                                    <div class="flex items-center gap-2">
                                        <!-- Previous Button -->
                                        <?php if ($pg > 1): ?>
                                            <a href="?page=members&pg=<?= $pg - 1 ?>"
                                                class="px-3 md:px-4 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 font-semibold transition-colors text-sm">
                                                Previous
                                            </a>
                                        <?php else: ?>
                                            <span
                                                class="px-3 md:px-4 py-2 bg-gray-100 border border-gray-200 text-gray-400 rounded-lg cursor-not-allowed text-sm">
                                                Previous
                                            </span>
                                        <?php endif; ?>

                                        <!-- Page Numbers (Desktop Only) -->
                                        <div class="hidden md:flex items-center gap-1">
                                            <?php
                                            $start = max(1, $pg - 2);
                                            $end = min($total_pages, $pg + 2);

                                            if ($start > 1) {
                                                echo "<a href='?page=members&pg=1' class='px-3 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors text-sm'>1</a>";
                                                if ($start > 2) {
                                                    echo "<span class='px-2 text-gray-500'>...</span>";
                                                }
                                            }

                                            for ($i = $start; $i <= $end; $i++) {
                                                if ($i == $pg) {
                                                    echo "<span class='px-3 py-2 bg-purple-600 text-white rounded-lg font-semibold text-sm'>$i</span>";
                                                } else {
                                                    echo "<a href='?page=members&pg=$i' class='px-3 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors text-sm'>$i</a>";
                                                }
                                            }

                                            if ($end < $total_pages) {
                                                if ($end < $total_pages - 1) {
                                                    echo "<span class='px-2 text-gray-500'>...</span>";
                                                }
                                                echo "<a href='?page=members&pg=$total_pages' class='px-3 py-2 bg-white border border-gray-300 text-gray-700 rounded-lg hover:bg-gray-100 transition-colors text-sm'>$total_pages</a>";
                                            }
                                            ?>
                                        </div>

                                        <!-- Next Button -->
                                        <?php if ($pg < $total_pages): ?>
                                            <a href="?page=members&pg=<?= $pg + 1 ?>"
                                                class="px-3 md:px-4 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-semibold transition-colors text-sm">
                                                Next
                                            </a>
                                        <?php else: ?>
                                            <span
                                                class="px-3 md:px-4 py-2 bg-gray-100 border border-gray-200 text-gray-400 rounded-lg cursor-not-allowed text-sm">
                                                Next
                                            </span>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </div>
                        <?php endif; ?>

                    <?php else: ?>
                        <!-- Empty State -->
                        <div class="px-6 py-12 text-center">
                            <div class="text-gray-400 mb-4">
                                <svg class="mx-auto h-12 w-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z">
                                    </path>
                                </svg>
                            </div>
                            <h3 class="text-lg font-semibold text-gray-900 mb-2">Belum Ada Member</h3>
                            <p class="text-sm text-gray-500">Belum ada member yang terdaftar di sistem.</p>
                        </div>
                    <?php endif; ?>
                </div>

            <?php endif; ?>
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

        // Close sidebar when clicking nav link on mobile
        const navLinks = sidebar.querySelectorAll('a');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 768) {
                    sidebar.classList.remove('active');
                    overlay.classList.add('hidden');
                }
            });
        });
    </script>
</body>

</html>