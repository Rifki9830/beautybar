<?php
require '../config.php';
checkAccess('admin');

// ==========================================
// LOGIC CRUD CATEGORIES
// ==========================================

// ADD CATEGORY
if (isset($_POST['add_category'])) {
    $name = $_POST['name'];
    $description = $_POST['description'];

    $stmt = $pdo->prepare("INSERT INTO categories (name, description) VALUES (?, ?)");
    $stmt->execute([$name, $description]);
    echo "<script>alert('Kategori berhasil ditambahkan!'); window.location='categories.php';</script>";
}

// EDIT CATEGORY
if (isset($_POST['edit_category'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $description = $_POST['description'];

    $stmt = $pdo->prepare("UPDATE categories SET name=?, description=? WHERE id=?");
    $stmt->execute([$name, $description, $id]);
    echo "<script>alert('Kategori berhasil diupdate!'); window.location='categories.php';</script>";
}

// DELETE CATEGORY
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        // Set category_id menjadi NULL untuk treatments yang menggunakan kategori ini
        $pdo->prepare("UPDATE treatments SET category_id = NULL WHERE category_id = ?")->execute([$id]);
        // Hapus kategori
        $pdo->prepare("DELETE FROM categories WHERE id=?")->execute([$id]);
        echo "<script>alert('Kategori berhasil dihapus!'); window.location='categories.php';</script>";
    } catch (Exception $e) {
        echo "<script>alert('Gagal hapus kategori!'); window.location='categories.php';</script>";
    }
}

// GET ALL CATEGORIES WITH TREATMENT COUNT
$categories = $pdo->query("
    SELECT c.*, COUNT(t.id) as treatment_count 
    FROM categories c 
    LEFT JOIN treatments t ON c.id = t.category_id 
    GROUP BY c.id 
    ORDER BY c.id DESC
")->fetchAll();
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Kategori - Beautybar Admin</title>
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
                    <!-- <span class="mr-3">📊</span> -->
                    <span>Dashboard</span>
                </a>

                <a href="admin.php?page=bookings" class="flex items-center px-4 py-3 mb-2 rounded-lg text-gray-700 hover:bg-gray-100">
                    <!-- <span class="mr-3">📅</span> -->
                    <span>Kelola Booking</span>
                </a>

                <a href="categories.php" class="flex items-center px-4 py-3 mb-2 rounded-lg bg-purple-600 text-white">
                    <!-- <span class="mr-3">🏷️</span> -->
                    <span>Kelola Kategori</span>
                </a>

                <a href="admin.php?page=treatments" class="flex items-center px-4 py-3 mb-2 rounded-lg text-gray-700 hover:bg-gray-100">
                    <!-- <span class="mr-3">💆</span> -->
                    <span>Kelola Treatment</span>
                </a>

                <a href="therapists.php"
                    class="flex items-center px-4 py-3 mb-2 rounded-lg text-gray-700 hover:bg-gray-100">
                    <!-- <span class="mr-3">👨‍⚕️</span> -->
                    <span>Kelola Terapis</span>
                </a>

                <a href="admin.php?page=members" class="flex items-center px-4 py-3 mb-2 rounded-lg text-gray-700 hover:bg-gray-100">
                    <!-- <span class="mr-3">👥</span> -->
                    <span>Kelola Member</span>
                </a>
            </nav>

            <div class="px-4 mt-8 pt-8 border-t">
                <a href="../index.php" class="flex items-center px-4 py-3 mb-2 text-gray-700 rounded-lg hover:bg-gray-100">
                    <!-- <span class="mr-3">🏠</span> -->
                    <span>Halaman Utama</span>
                </a>
                <a href="../logout.php" class="flex items-center px-4 py-3 text-red-600 rounded-lg hover:bg-red-50">
                    <!-- <span class="mr-3">🚪</span> -->
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 p-4 md:p-8 pt-20 md:pt-8">
            <!-- Header -->
            <div class="mb-6">
                <h1 class="text-2xl md:text-3xl font-bold text-gray-800">Kelola Kategori Treatment</h1>
                <p class="text-gray-500 mt-1 text-sm md:text-base">Organisir treatment berdasarkan kategori</p>
            </div>

            <!-- Form Add Category -->
            <div class="bg-white rounded-xl shadow-md p-4 md:p-6 mb-4 md:mb-6">
                <h2 class="text-lg md:text-xl font-bold text-gray-800 mb-4">➕ Tambah Kategori Baru</h2>

                <form method="POST" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Nama Kategori</label>
                            <input type="text" name="name" placeholder="Contoh: Perawatan Wajah" class="w-full px-3 md:px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm md:text-base" required>
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                            <input type="text" name="description" placeholder="Deskripsi singkat kategori" class="w-full px-3 md:px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-purple-500 focus:border-transparent text-sm md:text-base">
                        </div>
                    </div>

                    <div class="flex gap-2">
                        <button type="submit" name="add_category" class="w-full md:w-auto px-4 md:px-6 py-2 bg-purple-600 text-white rounded-lg hover:bg-purple-700 font-medium text-sm md:text-base">
                            ➕ Tambah Kategori
                        </button>
                    </div>
                </form>
            </div>

            <!-- Categories List -->
            <div class="bg-white rounded-xl shadow-md overflow-hidden">
                <div class="px-4 md:px-6 py-3 md:py-4 border-b bg-gradient-to-r from-purple-50 to-purple-100">
                    <h2 class="text-lg md:text-xl font-bold text-gray-800">📋 Daftar Kategori</h2>
                </div>

                <?php if (count($categories) > 0): ?>
                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3 md:gap-4 p-4 md:p-6">
                        <?php foreach ($categories as $cat): ?>
                            <div class="border border-gray-200 rounded-lg p-4 md:p-5 hover:shadow-lg transition-shadow">
                                <div class="flex items-start justify-between mb-3">
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-bold text-gray-800 text-base md:text-lg truncate"><?php echo htmlspecialchars($cat['name']); ?></h3>
                                        <p class="text-xs md:text-sm text-gray-500 line-clamp-2"><?php echo htmlspecialchars($cat['description']); ?></p>
                                    </div>
                                </div>

                                <div class="flex items-center justify-between pt-3 border-t border-gray-100">
                                    <span class="text-xs md:text-sm text-gray-600">
                                        💆 <strong><?php echo $cat['treatment_count']; ?></strong> Treatment
                                    </span>
                                    <div class="flex gap-1 md:gap-2">
                                        <button onclick="openEditModal(<?php echo $cat['id']; ?>, '<?php echo htmlspecialchars($cat['name'], ENT_QUOTES); ?>', '<?php echo htmlspecialchars($cat['description'], ENT_QUOTES); ?>')" class="px-2 md:px-3 py-1 bg-blue-500 text-white text-xs md:text-sm rounded hover:bg-blue-600">
                                            ✏️
                                        </button>
                                        <a href="?delete=<?php echo $cat['id']; ?>" onclick="return confirm('Yakin hapus kategori ini?\nTreatment yang menggunakan kategori ini akan menjadi tanpa kategori.')" class="px-2 md:px-3 py-1 bg-red-500 text-white text-xs md:text-sm rounded hover:bg-red-600">
                                            🗑️
                                        </a>
                                    </div>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                <?php else: ?>
                    <div class="p-6 md:p-8 text-center text-gray-500">
                        <div class="text-4xl md:text-6xl mb-4">📦</div>
                        <p class="text-base md:text-lg">Belum ada kategori yang dibuat.</p>
                        <p class="text-xs md:text-sm mt-2">Tambahkan kategori pertama Anda di form di atas!</p>
                    </div>
                <?php endif; ?>
            </div>
        </main>
    </div>

    <!-- Edit Modal -->
    <div id="editModal" class="hidden fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-xl shadow-2xl p-6 md:p-8 max-w-md w-full">
            <div class="flex justify-between items-center mb-4 md:mb-6">
                <h2 class="text-xl md:text-2xl font-bold text-gray-800">✏️ Edit Kategori</h2>
                <button onclick="closeEditModal()" class="text-gray-500 hover:text-gray-700 text-2xl">
                    &times;
                </button>
            </div>

            <form method="POST" class="space-y-4">
                <input type="hidden" name="id" id="edit_id">

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Nama Kategori</label>
                    <input type="text" name="name" id="edit_name" class="w-full px-3 md:px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm md:text-base" required>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-2">Deskripsi</label>
                    <input type="text" name="description" id="edit_description" class="w-full px-3 md:px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent text-sm md:text-base">
                </div>

                <div class="flex flex-col md:flex-row gap-2 pt-4">
                    <button type="submit" name="edit_category" class="flex-1 px-4 md:px-6 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 font-medium text-sm md:text-base">
                        💾 Update Kategori
                    </button>
                    <button type="button" onclick="closeEditModal()" class="px-4 md:px-6 py-2 bg-gray-200 text-gray-700 rounded-lg hover:bg-gray-300 font-medium text-sm md:text-base">
                        Batal
                    </button>
                </div>
            </form>
        </div>
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

        // Edit Modal Functions
        function openEditModal(id, name, description) {
            document.getElementById('edit_id').value = id;
            document.getElementById('edit_name').value = name;
            document.getElementById('edit_description').value = description;
            document.getElementById('editModal').classList.remove('hidden');
        }

        function closeEditModal() {
            document.getElementById('editModal').classList.add('hidden');
        }

        // Close modal when clicking outside
        document.getElementById('editModal').addEventListener('click', function(e) {
            if (e.target === this) {
                closeEditModal();
            }
        });

        // Close modal with ESC key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                closeEditModal();
            }
        });
    </script>
</body>

</html>