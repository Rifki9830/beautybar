<?php
require '../config.php';
checkAccess('admin');

// ==========================================
// LOGIC CRUD THERAPISTS
// ==========================================

// ADD THERAPIST
if (isset($_POST['add_therapist'])) {
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $specialization = $_POST['specialization'];
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

    $stmt = $pdo->prepare("INSERT INTO therapists (name, phone, specialization, image) VALUES (?, ?, ?, ?)");
    $stmt->execute([$name, $phone, $specialization, $image]);
    echo "<script>alert('Terapis berhasil ditambahkan!'); window.location='therapists.php';</script>";
}

// EDIT THERAPIST
if (isset($_POST['edit_therapist'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $phone = $_POST['phone'];
    $specialization = $_POST['specialization'];

    // Get current image
    $stmt = $pdo->prepare("SELECT image FROM therapists WHERE id=?");
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
                // Delete old image
                if ($current['image'] && file_exists('../assets/uploads/' . $current['image'])) {
                    unlink('../assets/uploads/' . $current['image']);
                }
                $image = $image_name;
            }
        }
    }

    $stmt = $pdo->prepare("UPDATE therapists SET name=?, phone=?, specialization=?, image=? WHERE id=?");
    $stmt->execute([$name, $phone, $specialization, $image, $id]);
    echo "<script>alert('Terapis berhasil diupdate!'); window.location='therapists.php';</script>";
}

// DELETE THERAPIST
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        // Get image before delete
        $stmt = $pdo->prepare("SELECT image FROM therapists WHERE id=?");
        $stmt->execute([$id]);
        $therapist = $stmt->fetch();
        
        // Delete from database
        $pdo->prepare("DELETE FROM therapists WHERE id=?")->execute([$id]);
        
        // Delete image file
        if ($therapist['image'] && file_exists('../assets/uploads/' . $therapist['image'])) {
            unlink('../assets/uploads/' . $therapist['image']);
        }
        
        echo "<script>alert('Terapis berhasil dihapus!'); window.location='therapists.php';</script>";
    } catch (Exception $e) {
        echo "<script>alert('Gagal hapus: Terapis ini masih memiliki booking aktif!'); window.location='therapists.php';</script>";
    }
}

// GET ALL THERAPISTS WITH BOOKING COUNT
$therapists = $pdo->query("
    SELECT t.*, COUNT(b.id) as booking_count 
    FROM therapists t 
    LEFT JOIN bookings b ON t.id = b.therapist_id 
    GROUP BY t.id 
    ORDER BY t.id DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Terapis - Beautybar Admin</title>
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
            document.getElementById('therapistModal').classList.remove('hidden');
            document.getElementById('modalTitle').textContent = '➕ Tambah Terapis Baru';
            document.getElementById('therapistForm').reset();
            document.getElementById('therapistId').value = '';
            document.getElementById('submitBtn').name = 'add_therapist';
            document.getElementById('submitBtn').textContent = '➕ Tambah Terapis';
            document.getElementById('imagePreview').classList.add('hidden');
        }

        function closeModal() {
            document.getElementById('therapistModal').classList.add('hidden');
        }

        function editTherapist(id, name, phone, specialization, image) {
            document.getElementById('therapistModal').classList.remove('hidden');
            document.getElementById('modalTitle').textContent = '✏️ Edit Terapis';
            document.getElementById('therapistId').value = id;
            document.getElementById('therapistName').value = name;
            document.getElementById('therapistPhone').value = phone;
            document.getElementById('therapistSpecialization').value = specialization;
            document.getElementById('submitBtn').name = 'edit_therapist';
            document.getElementById('submitBtn').textContent = '💾 Update Terapis';
            
            if (image) {
                document.getElementById('previewImg').src = '../assets/uploads/' + image;
                document.getElementById('imagePreview').classList.remove('hidden');
            }
        }

        // Image preview handler
        document.addEventListener('DOMContentLoaded', function () {
            const imageInput = document.getElementById('therapistImage');
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
        });

        // Close modal when clicking outside
        document.addEventListener('click', function (event) {
            const modal = document.getElementById('therapistModal');
            if (event.target === modal) {
                closeModal();
            }
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
                <a href="dashboard.php" class="flex items-center px-4 py-3 mb-2 rounded-lg text-gray-700 hover:bg-gray-100">
                    <span class="mr-3">📊</span>
                    <span>Dashboard</span>
                </a>

                <a href="admin.php?page=bookings" class="flex items-center px-4 py-3 mb-2 rounded-lg text-gray-700 hover:bg-gray-100">
                    <span class="mr-3">📅</span>
                    <span>Kelola Booking</span>
                </a>

                <a href="categories.php" class="flex items-center px-4 py-3 mb-2 rounded-lg text-gray-700 hover:bg-gray-100">
                    <span class="mr-3">🏷️</span>
                    <span>Kelola Kategori</span>
                </a>

                <a href="admin.php?page=treatments" class="flex items-center px-4 py-3 mb-2 rounded-lg text-gray-700 hover:bg-gray-100">
                    <span class="mr-3">💆</span>
                    <span>Kelola Treatment</span>
                </a>

                <a href="therapists.php" class="flex items-center px-4 py-3 mb-2 rounded-lg bg-purple-600 text-white">
                    <span class="mr-3">👨‍⚕️</span>
                    <span>Kelola Terapis</span>
                </a>

                <a href="admin.php?page=members" class="flex items-center px-4 py-3 mb-2 rounded-lg text-gray-700 hover:bg-gray-100">
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
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Kelola Terapis</h1>
                <p class="text-gray-500 mt-1 text-sm md:text-base">Manajemen data terapis Beautybar</p>
            </div>

            <!-- Button Tambah Terapis -->
            <div class="mb-4 md:mb-6">
                <button onclick="openModal()"
                    class="w-full md:w-auto px-4 md:px-6 py-2 md:py-3 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium shadow-md text-sm md:text-base">
                    ➕ Tambah Terapis Baru
                </button>
            </div>

            <!-- Modal Tambah/Edit Terapis -->
            <div id="therapistModal"
                class="hidden fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center p-4">
                <div class="bg-white rounded-lg shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto">
                    <div class="flex items-center justify-between p-4 md:p-6 border-b sticky top-0 bg-white">
                        <h3 id="modalTitle" class="text-lg md:text-xl font-semibold text-gray-800">
                            ➕ Tambah Terapis Baru
                        </h3>
                        <button onclick="closeModal()" class="text-gray-400 hover:text-gray-600">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                    d="M6 18L18 6M6 6l12 12"></path>
                            </svg>
                        </button>
                    </div>

                    <form method="POST" id="therapistForm" class="p-4 md:p-6" enctype="multipart/form-data">
                        <input type="hidden" id="therapistId" name="id" value="">

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">Nama Terapis</label>
                                <input type="text" id="therapistName" name="name" placeholder="Contoh: Nesya"
                                    class="w-full px-3 md:px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm md:text-base"
                                    required>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">No. Telepon</label>
                                <input type="text" id="therapistPhone" name="phone" placeholder="081234567890"
                                    class="w-full px-3 md:px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm md:text-base"
                                    required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Spesialisasi</label>
                            <textarea id="therapistSpecialization" name="specialization" rows="3"
                                placeholder="Contoh: Eyelash Extensions, Nailart, Brow Bomber"
                                class="w-full px-3 md:px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm md:text-base"
                                required></textarea>
                        </div>

                        <div class="mb-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">Foto Terapis</label>
                            <div class="flex items-center justify-center w-full">
                                <label
                                    class="flex flex-col items-center justify-center w-full h-32 border-2 border-dashed border-purple-300 rounded-lg cursor-pointer bg-purple-50 hover:bg-purple-100">
                                    <div class="flex flex-col items-center justify-center pt-5 pb-6">
                                        <svg class="w-8 h-8 text-purple-500 mb-2" fill="none" stroke="currentColor"
                                            viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                                                d="M12 4v16m8-8H4"></path>
                                        </svg>
                                        <p class="text-xs md:text-sm text-gray-600"><span class="font-semibold">Klik untuk
                                                upload</span> atau drag gambar</p>
                                        <p class="text-xs text-gray-500 mt-1">JPG, PNG atau GIF (Max. 5MB)</p>
                                    </div>
                                    <input id="therapistImage" name="image" type="file" class="hidden" accept="image/*">
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
                            <button type="submit" id="submitBtn" name="add_therapist"
                                class="w-full md:w-auto px-4 md:px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium text-sm md:text-base">
                                ➕ Tambah Terapis
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Therapists List -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="px-4 md:px-6 py-3 md:py-4 border-b bg-gradient-to-r from-purple-50 to-purple-100">
                    <h2 class="text-lg md:text-xl font-bold text-gray-800">👨‍⚕️ Daftar Terapis</h2>
                </div>

                <?php if (count($therapists) > 0): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 md:gap-6 p-4 md:p-6">
                        <?php foreach ($therapists as $therapist): ?>
                            <div class="border border-gray-200 rounded-lg overflow-hidden hover:shadow-xl transition-shadow">
                                <!-- Therapist Image -->
                                <div class="h-40 md:h-48 bg-gradient-to-br from-purple-100 to-purple-200 flex items-center justify-center">
                                    <?php if ($therapist['image']): ?>
                                        <img src="../assets/uploads/<?php echo $therapist['image']; ?>" 
                                             alt="<?php echo htmlspecialchars($therapist['name']); ?>"
                                             class="h-full w-full object-cover">
                                    <?php else: ?>
                                        <div class="text-5xl md:text-6xl">👨‍⚕️</div>
                                    <?php endif; ?>
                                </div>

                                <!-- Therapist Info -->
                                <div class="p-4 md:p-5">
                                    <h3 class="font-bold text-gray-800 text-lg md:text-xl mb-2 truncate">
                                        <?php echo htmlspecialchars($therapist['name']); ?>
                                    </h3>
                                    
                                    <div class="space-y-2 mb-4">
                                        <div class="flex items-center text-xs md:text-sm text-gray-600">
                                            <span class="mr-2">📱</span>
                                            <span class="truncate"><?php echo htmlspecialchars($therapist['phone']); ?></span>
                                        </div>
                                        
                                        <div class="flex items-start text-xs md:text-sm text-gray-600">
                                            <span class="mr-2 mt-1">💼</span>
                                            <span class="flex-1 line-clamp-2"><?php echo htmlspecialchars($therapist['specialization']); ?></span>
                                        </div>
                                    </div>

                                    <div class="flex items-center justify-between pt-3 md:pt-4 border-t border-gray-100">
                                        <span class="text-xs md:text-sm text-gray-600">
                                            📅 <strong><?php echo $therapist['booking_count']; ?></strong> Booking
                                        </span>
                                        <div class="flex gap-1 md:gap-2">
                                            <button onclick='editTherapist(
                                                <?php echo $therapist['id']; ?>, 
                                                "<?php echo htmlspecialchars($therapist['name'], ENT_QUOTES); ?>",
                                                "<?php echo htmlspecialchars($therapist['phone'], ENT_QUOTES); ?>",
                                                "<?php echo htmlspecialchars($therapist['specialization'], ENT_QUOTES); ?>",
                                                "<?php echo $therapist['image']; ?>"
                                            )' class="px-2 md:px-3 py-1 bg-blue-500 text-white text-xs md:text-sm rounded hover:bg-blue-600">
                                                ✏️
                                            </button>
                                            <a href="?delete=<?php echo $therapist['id']; ?>" 
                                               onclick="return confirm('Yakin hapus terapis ini?\nData booking terkait akan terpengaruh.')" 
                                               class="px-2 md:px-3 py-1 bg-red-500 text-white text-xs md:text-sm rounded hover:bg-red-600">
                                                🗑️
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="p-8 md:p-12 text-center text-gray-500">
                        <div class="text-5xl md:text-6xl mb-4">👨‍⚕️</div>
                        <p class="text-base md:text-lg font-medium">Belum ada terapis yang terdaftar</p>
                        <p class="text-xs md:text-sm mt-2">Klik tombol "Tambah Terapis Baru" untuk menambahkan terapis pertama</p>
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