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
        $pdo->prepare("UPDATE bookings SET is_paid=1 WHERE id=?")->execute([$id]);
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
    document.addEventListener('DOMContentLoaded', function() {
        const imageInput = document.getElementById('treatmentImage');
        if (imageInput) {
            imageInput.addEventListener('change', function(e) {
                const file = e.target.files[0];
                if (file) {
                    const reader = new FileReader();
                    reader.onload = function(event) {
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
    document.addEventListener('click', function(event) {
        const modal = document.getElementById('treatmentModal');
        if (event.target === modal) {
            closeModal();
        }
    });

    // Auto open modal if edit parameter exists
    window.addEventListener('load', function() {
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
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50 border-b">
                            <tr>
                                <th
                                    class="px-3 md:px-6 py-3 md:py-4 text-left text-xs font-semibold text-gray-600 uppercase">
                                    ID</th>
                                <th
                                    class="px-3 md:px-6 py-3 md:py-4 text-left text-xs font-semibold text-gray-600 uppercase">
                                    Member & Treatment</th>
                                <th
                                    class="px-3 md:px-6 py-3 md:py-4 text-left text-xs font-semibold text-gray-600 uppercase">
                                    Jadwal</th>
                                <th
                                    class="px-3 md:px-6 py-3 md:py-4 text-left text-xs font-semibold text-gray-600 uppercase">
                                    Status</th>
                                <th
                                    class="px-3 md:px-6 py-3 md:py-4 text-left text-xs font-semibold text-gray-600 uppercase">
                                    Pembayaran</th>
                                <th
                                    class="px-3 md:px-6 py-3 md:py-4 text-left text-xs font-semibold text-gray-600 uppercase">
                                    Aksi</th>
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
                                    <td class='px-3 md:px-6 py-3 md:py-4 text-xs md:text-sm font-medium text-gray-900'>#{$row['id']}</td>
                                    <td class='px-3 md:px-6 py-3 md:py-4'>
                                        <div class='text-xs md:text-sm font-medium text-gray-900'>{$row['username']}</div>
                                        <div class='text-xs text-gray-500'>{$row['treat']}</div>
                                    </td>
                                    <td class='px-3 md:px-6 py-3 md:py-4'>
                                        <div class='text-xs md:text-sm text-gray-900'>{$row['booking_date']}</div>
                                        <div class='text-xs md:text-sm font-semibold text-purple-600'>{$row['booking_time']}</div>
                                    </td>
                                    <td class='px-3 md:px-6 py-3 md:py-4'>
                                        <span class='px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full $badgeClass'>
                                            $st
                                        </span>
                                    </td>
                                    <td class='px-3 md:px-6 py-3 md:py-4 text-xs md:text-sm'>";
                                    if ($paySt == 'paid') {
                                        echo "<span class='text-green-600 font-semibold'>✓ LUNAS</span>";
                                    } elseif ($row['proof_image']) {
                                        echo "<a href='../assets/uploads/{$row['proof_image']}' target='_blank' class='text-blue-600 hover:underline'>📷 Lihat</a>";
                                        if ($paySt == 'pending')
                                            echo "<br><span class='text-xs text-orange-500'>⏳ Perlu Validasi</span>";
                                    } else {
                                        echo "<span class='text-gray-400'>-</span>";
                                    }
                                    echo "</td>
                                    <td class='px-3 md:px-6 py-3 md:py-4 text-xs'>";
                                    if ($st == 'pending') {
                                        echo "<a href='?page=bookings&action=approve&id={$row['id']}' class='inline-block px-2 py-1 mb-1 bg-green-500 text-white rounded hover:bg-green-600 mr-1 text-xs'>✓</a>";
                                        echo "<a href='?page=bookings&action=reject&id={$row['id']}' class='inline-block px-2 py-1 mb-1 bg-red-500 text-white rounded hover:bg-red-600 text-xs'>✗</a>";
                                    }
                                    if ($row['proof_image'] && $paySt == 'pending' && $st == 'confirmed') {
                                        echo "<a href='?page=bookings&action=confirm_pay&id={$row['id']}' class='inline-block px-2 py-1 mb-1 bg-purple-500 text-white rounded hover:bg-purple-600 text-xs'>💰</a>";
                                    }
                                    if ($st == 'confirmed' && $row['is_paid'] == 1) {
                                        echo "<a href='?page=bookings&action=complete&id={$row['id']}' class='inline-block px-2 py-1 mb-1 bg-blue-500 text-white rounded hover:bg-blue-600 text-xs'>✓</a>";
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
            <div class="mb-4 md:mb-6">
                <button onclick="openModal()"
                    class="w-full md:w-auto px-4 md:px-6 py-2 md:py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium shadow-md text-sm md:text-base">
                    Tambah Treatment Baru
                </button>
            </div>

            <!-- Modal Tambah/Edit Treatment -->
            <div id="treatmentModal"
                class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
                <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                    <div class="flex items-center justify-between p-4 md:p-6 border-b sticky top-0 bg-white">
                        <h3 id="modalTitle" class="text-lg md:text-xl font-semibold text-gray-800">
                            Tambah Treatment Baru
                        </h3>
                        <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <form method="POST" id="treatmentForm" class="p-4 md:p-6" enctype="multipart/form-data">
                        <input type="hidden" id="treatmentId" name="id" value="">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Treatment</label>
                                <input type="text" id="treatmentName" name="name"
                                    class="w-full px-3 md:px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm md:text-base"
                                    required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Harga (Rp)</label>
                                <input type="number" id="treatmentPrice" name="price"
                                    class="w-full px-3 md:px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm md:text-base"
                                    required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Durasi (Menit)</label>
                            <input type="number" id="treatmentDuration" name="duration" value="60"
                                class="w-full px-3 md:px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm md:text-base"
                                required>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Kategori Treatment</label>
                            <select id="treatmentCategory" name="category_id"
                                class="w-full px-3 md:px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm md:text-base">
                                <option value="">-- Tanpa Kategori --</option>
                                <?php
                                    $categories = $pdo->query("SELECT * FROM categories ORDER BY name");
                                    while ($c = $categories->fetch()) {
                                        echo "<option value='{$c['id']}'>{$c['name']}</option>";
                                    }
                                    ?>
                            </select>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Gambar Treatment</label>
                            <div class="flex items-center justify-center w-full">
                                <label
                                    class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-purple-300 rounded-lg cursor-pointer bg-purple-50 hover:bg-purple-100">
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <svg class="w-8 h-8 text-purple-500 mb-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        <p class="text-xs md:text-sm text-gray-600"><span class="font-semibold">Klik
                                                untuk
                                                upload</span> atau drag gambar</p>
                                        <p class="text-xs text-gray-500 mt-1">JPG, PNG atau GIF (Max. 5MB)</p>
                                    </div>
                                    <input id="treatmentImage" name="image" type="file" class="hidden" accept="image/*">
                                </label>
                            </div>
                            <div id="imagePreview" class="mt-4 hidden flex justify-center">
                                <img id="previewImg" src="" alt="Preview" class="h-32 object-contain rounded-lg">
                            </div>
                        </div>

                        <div class="flex flex-col md:flex-row gap-2 justify-end">
                            <button type="button" onclick="closeModal()"
                                class="w-full md:w-auto px-4 md:px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium text-sm md:text-base">
                                Batal
                            </button>
                            <button type="submit" id="submitBtn" name="add_treatment"
                                class="w-full md:w-auto px-4 md:px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium text-sm md:text-base">
                                ➕ Tambah Treatment
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow-md overflow-hidden">
                <div class="px-4 md:px-6 py-3 md:py-4 border-b">
                    <h3 class="text-base md:text-lg font-semibold text-gray-800">Daftar Treatment</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 md:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">ID
                                </th>
                                <th class="px-3 md:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">
                                    Gambar</th>
                                <th class="px-3 md:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">
                                    Nama Treatment</th>
                                <th class="px-3 md:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">
                                    Harga</th>
                                <th
                                    class="px-3 md:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase hidden md:table-cell">
                                    Durasi</th>
                                <th class="px-3 md:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php
                                $treatments = $pdo->query("
                                    SELECT t.*, c.name AS category_name
                                    FROM treatments t
                                    LEFT JOIN categories c ON t.category_id = c.id
                                    ORDER BY t.id DESC
                                ");

                                while ($t = $treatments->fetch()) {
                                    $image_html = '';
                                    if ($t['image']) {
                                        $image_html = "<img src='../assets/uploads/{$t['image']}' alt='{$t['name']}' class='h-12 w-12 md:h-16 md:w-16 object-cover rounded'>";
                                    } else {
                                        $image_html = "<div class='h-12 w-12 md:h-16 md:w-16 bg-purple-100 rounded flex items-center justify-center text-purple-600 font-bold text-sm md:text-base'>" . substr($t['name'], 0, 1) . "</div>";
                                    }

                                    $category_display = $t['category_name']
                                        ? "<span class='text-xs bg-purple-100 text-purple-700 px-2 py-1 rounded-full'>{$t['category_name']}</span>"
                                        : "<span class='text-xs text-gray-400'>-</span>";

                                    echo "<tr class='hover:bg-gray-50'>
                                        <td class='px-3 md:px-6 py-3 md:py-4 text-xs md:text-sm text-gray-900'>#{$t['id']}</td>
                                        <td class='px-3 md:px-6 py-3 md:py-4 text-sm'>{$image_html}</td>
                                        <td class='px-3 md:px-6 py-3 md:py-4'>
                                            <div class='text-xs md:text-sm font-medium text-gray-900'>{$t['name']}</div>
                                            <div class='text-xs md:text-sm mt-1'>{$category_display}</div>
                                            <div class='text-xs text-gray-500 mt-1 md:hidden'>{$t['duration']} menit</div>
                                        </td>
                                        <td class='px-3 md:px-6 py-3 md:py-4 text-xs md:text-sm text-gray-900'>Rp " . number_format($t['price']) . "</td>
                                        <td class='px-3 md:px-6 py-3 md:py-4 text-xs md:text-sm text-gray-900 hidden md:table-cell'>{$t['duration']} menit</td>
                                        <td class='px-3 md:px-6 py-3 md:py-4 text-xs'>
                                            <button onclick='editTreatment({$t['id']}, \"" . addslashes($t['name']) . "\", {$t['price']}, {$t['duration']}, " . ($t['category_id'] ?: 'null') . ")' 
                                               class='inline-block px-2 md:px-3 py-1 bg-blue-500 text-white rounded hover:bg-blue-600 mr-1 mb-1'>
                                                Edit
                                            </button>
                                            <a href='?page=treatments&delete_treatment={$t['id']}' 
                                               onclick='return confirm(\"Yakin hapus?\")' 
                                               class='inline-block px-2 md:px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600'>
                                                Hapus
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
                <div class="px-4 md:px-6 py-3 md:py-4 border-b">
                    <h3 class="text-base md:text-lg font-semibold text-gray-800">Daftar Member</h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-3 md:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">ID
                                </th>
                                <th class="px-3 md:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">
                                    Nama Member</th>
                                <th
                                    class="px-3 md:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase hidden md:table-cell">
                                    Email</th>
                                <th
                                    class="px-3 md:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase hidden lg:table-cell">
                                    Tanggal Bergabung</th>
                                <th class="px-3 md:px-6 py-3 text-left text-xs font-semibold text-gray-600 uppercase">
                                    Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php
                                $members = $pdo->query("SELECT * FROM users WHERE role='member' ORDER BY created_at DESC");

                                if ($members->rowCount() > 0) {
                                    while ($m = $members->fetch()) {
                                        echo "<tr class='hover:bg-gray-50'>
                                            <td class='px-3 md:px-6 py-3 md:py-4 text-xs md:text-sm text-gray-900'>#{$m['id']}</td>
                                            <td class='px-3 md:px-6 py-3 md:py-4'>
                                                <div class='text-xs md:text-sm font-medium text-gray-900'>{$m['username']}</div>
                                                <div class='text-xs text-gray-600 md:hidden'>{$m['email']}</div>
                                            </td>
                                            <td class='px-3 md:px-6 py-3 md:py-4 text-xs md:text-sm text-gray-600 hidden md:table-cell'>{$m['email']}</td>
                                            <td class='px-3 md:px-6 py-3 md:py-4 text-xs md:text-sm text-gray-600 hidden lg:table-cell'>{$m['created_at']}</td>
                                            <td class='px-3 md:px-6 py-3 md:py-4 text-xs'>
                                                <a href='?page=members&reset_member={$m['id']}' 
                                                   onclick='return confirm(\"Reset password member ini menjadi: password ?\")' 
                                                   class='inline-block px-2 md:px-3 py-1 bg-yellow-500 text-white rounded hover:bg-yellow-600 mr-1 mb-1'>
                                                    Reset Pw
                                                </a>
                                                <a href='?page=members&delete_member={$m['id']}' 
                                                   onclick='return confirm(\"Yakin hapus member ini? Data tidak bisa kembali.\")' 
                                                   class='inline-block px-2 md:px-3 py-1 bg-red-500 text-white rounded hover:bg-red-600'>
                                                    Hapus
                                                </a>
                                            </td>
                                        </tr>";
                                    }
                                } else {
                                    echo "<tr>
                                        <td colspan='5' class='px-4 md:px-6 py-6 md:py-8 text-center text-gray-500 text-sm md:text-base'>
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