<?php
require '../config.php';
checkAccess('admin');

$editData = null; // Variabel penampung data edit

// --- 1. LOGIC TAMBAH DATA ---
if (isset($_POST['add'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $duration = $_POST['duration'];
    
    try {
        $pdo->prepare("INSERT INTO treatments (name, price, duration) VALUES (?,?,?)")
            ->execute([$name, $price, $duration]);
        echo "<script>alert('Berhasil menambah treatment!'); window.location='treatments.php';</script>";
    } catch (Exception $e) {
        echo "<script>alert('Gagal menambah data.');</script>";
    }
}

// --- 2. LOGIC UPDATE DATA (FITUR BARU) ---
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $duration = $_POST['duration'];

    try {
        $sql = "UPDATE treatments SET name=?, price=?, duration=? WHERE id=?";
        $pdo->prepare($sql)->execute([$name, $price, $duration, $id]);
        echo "<script>alert('Data berhasil diperbarui!'); window.location='treatments.php';</script>";
    } catch (Exception $e) {
        echo "<script>alert('Gagal update data.');</script>";
    }
}

// --- 3. LOGIC HAPUS DATA (DENGAN PERBAIKAN ERROR 1451) ---
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];
    try {
        $pdo->prepare("DELETE FROM treatments WHERE id=?")->execute([$id]);
        header("Location: treatments.php");
    } catch (PDOException $e) {
        // Menangkap error jika data sedang dipakai di booking
        if ($e->getCode() == 23000) {
            echo "<script>
                alert('GAGAL HAPUS: Treatment ini sudah ada di riwayat booking member! \\n\\nSolusi: Cukup edit nama/harganya, jangan dihapus agar riwayat transaksi aman.');
                window.location='treatments.php';
            </script>";
        } else {
            echo "Error: " . $e->getMessage();
        }
    }
}

// --- 4. LOGIC AMBIL DATA EDIT ---
if (isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM treatments WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editData = $stmt->fetch();
}
?>

<!DOCTYPE html>
<html>

<head>
    <title>Kelola Treatment</title>
    <link rel="stylesheet" href="../style.css">
</head>

<body>
    <div class="dash-container">
        <div class="sidebar">
            <h3>Admin Panel</h3>
            <a href="admin.php">← Kembali Dashboard</a>
            <a href="treatments.php" style="background:var(--primary); color:white;">Kelola Treatment</a>
            <hr>
            <a href="../logout.php">Logout</a>
        </div>

        <div class="main">
            <h2>Kelola Daftar Treatment</h2>

            <div class="card">
                <h3><?php echo $editData ? 'Edit Treatment' : 'Tambah Treatment Baru'; ?></h3>

                <form method="POST">
                    <?php if($editData): ?>
                    <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
                    <?php endif; ?>

                    <label>Nama Treatment</label>
                    <input type="text" name="name" value="<?php echo $editData['name'] ?? ''; ?>"
                        placeholder="Contoh: Eyelash Extension" required>

                    <label>Harga (Rp)</label>
                    <input type="number" name="price" value="<?php echo $editData['price'] ?? ''; ?>"
                        placeholder="Contoh: 150000" required>

                    <label>Durasi (Menit)</label>
                    <input type="number" name="duration" value="<?php echo $editData['duration'] ?? ''; ?>"
                        placeholder="Contoh: 60" required>

                    <?php if($editData): ?>
                    <button type="submit" name="update" style="background: #f39c12;">Update Perubahan</button>
                    <a href="treatments.php"
                        style="display:inline-block; margin-top:10px; text-decoration:underline;">Batal Edit</a>
                    <?php else: ?>
                    <button type="submit" name="add">Simpan Treatment</button>
                    <?php endif; ?>
                </form>
            </div>

            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Harga</th>
                            <th>Durasi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $stmt = $pdo->query("SELECT * FROM treatments ORDER BY id DESC");
                        while ($row = $stmt->fetch()) {
                            echo "<tr>
                                <td>{$row['name']}</td>
                                <td>Rp " . number_format($row['price']) . "</td>
                                <td>{$row['duration']} Menit</td>
                                <td>
                                    <a href='?edit={$row['id']}' class='badge' style='background:#f39c12; margin-right:5px;'>✎ Edit</a>
                                    
                                    <a href='?delete={$row['id']}' class='badge' style='background:#c0392b;' 
                                       onclick='return confirm(\"Yakin hapus? Data tidak bisa dihapus jika sudah pernah dibooking.\")'>🗑 Hapus</a>
                                </td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>