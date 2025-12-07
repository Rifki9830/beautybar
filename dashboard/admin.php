<?php
require '../config.php';
checkAccess('admin');

// --- 1. LOGIC MENANGANI BOOKING & PEMBAYARAN (DARI UPDATE BARU) ---
if(isset($_GET['action']) && isset($_GET['id'])) {
    $id = $_GET['id'];
    $act = $_GET['action'];
    
    // Konfirmasi Booking (Terima)
    if($act == 'approve') {
        $pdo->prepare("UPDATE bookings SET status='confirmed' WHERE id=?")->execute([$id]);
    }
    // Tolak Booking
    elseif($act == 'reject') {
        $pdo->prepare("UPDATE bookings SET status='cancelled' WHERE id=?")->execute([$id]);
    }
    // Selesai Treatment
    elseif($act == 'complete') {
        $pdo->prepare("UPDATE bookings SET status='completed' WHERE id=?")->execute([$id]);
    }
    // VALIDASI PEMBAYARAN (FITUR BARU)
    elseif($act == 'confirm_pay') {
        // Update di bookings (is_paid = 1) DAN transactions (payment_status = paid)
        $pdo->prepare("UPDATE bookings SET is_paid=1 WHERE id=?")->execute([$id]);
        $pdo->prepare("UPDATE transactions SET payment_status='paid' WHERE booking_id=?")->execute([$id]);
    }
    
    // Redirect agar URL bersih
    header("Location: admin.php?page=bookings");
    exit;
}

// --- 2. LOGIC CRUD TREATMENT (DARI KODE ANDA) ---

// Create
if(isset($_POST['add_treatment'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $duration = $_POST['duration'];
    
    $stmt = $pdo->prepare("INSERT INTO treatments (name, price, duration) VALUES (?, ?, ?)");
    $stmt->execute([$name, $price, $duration]);
    echo "<script>alert('Treatment berhasil ditambahkan!'); window.location='admin.php?page=treatments';</script>";
}

// Update
if(isset($_POST['edit_treatment'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $duration = $_POST['duration'];
    
    $stmt = $pdo->prepare("UPDATE treatments SET name=?, price=?, duration=? WHERE id=?");
    $stmt->execute([$name, $price, $duration, $id]);
    echo "<script>alert('Treatment berhasil diupdate!'); window.location='admin.php?page=treatments';</script>";
}

// Delete
if(isset($_GET['delete_treatment'])) {
    $id = $_GET['delete_treatment'];
    try {
        $pdo->prepare("DELETE FROM treatments WHERE id=?")->execute([$id]);
        echo "<script>alert('Treatment berhasil dihapus!'); window.location='admin.php?page=treatments';</script>";
    } catch (Exception $e) {
        echo "<script>alert('Gagal hapus: Treatment sedang digunakan dalam riwayat booking!'); window.location='admin.php?page=treatments';</script>";
    }
}

// Ambil data treatment untuk edit
$edit_treatment = null;
if(isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM treatments WHERE id=?");
    $stmt->execute([$_GET['edit']]);
    $edit_treatment = $stmt->fetch();
}

// Tentukan halaman aktif (Default: bookings)
$page = isset($_GET['page']) ? $_GET['page'] : 'bookings';
?>

<!DOCTYPE html>
<html>

<head>
    <title>Admin Dashboard - Beautybar</title>
    <link rel="stylesheet" href="../style.css">
    <style>
    /* Style Tambahan dari Desain Anda */
    .tab-menu {
        display: flex;
        gap: 10px;
        margin-bottom: 30px;
        border-bottom: 2px solid #eee;
        padding-bottom: 10px;
    }

    .tab-menu a {
        padding: 10px 20px;
        background: #f8f9fa;
        border-radius: 8px 8px 0 0;
        font-weight: 500;
        color: var(--gray);
        text-decoration: none;
    }

    .tab-menu a.active {
        background: var(--primary);
        color: white;
    }

    .tab-menu a:hover {
        background: var(--primary-dark);
        color: white;
    }

    .form-inline {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 15px;
    }

    /* Button Styles */
    .btn-small {
        padding: 6px 12px;
        border-radius: 5px;
        font-size: 0.85rem;
        display: inline-block;
        margin-right: 5px;
        color: white;
        text-decoration: none;
        cursor: pointer;
        border: none;
    }

    .btn-primary {
        background: var(--primary);
    }

    .btn-danger {
        background: #dc3545;
    }

    .btn-success {
        background: #28a745;
    }

    .btn-purple {
        background: #8e44ad;
    }

    /* Untuk Validasi Bayar */
    .btn-info {
        background: #17a2b8;
    }

    /* Untuk Selesai */

    .btn-small:hover {
        opacity: 0.9;
    }
    </style>
</head>

<body>
    <div class="dash-container">
        <div class="sidebar">
            <h3>Admin Panel</h3>
            <p>Halo, <?php echo $_SESSION['name'] ?? 'Admin'; ?></p>
            <hr>
            <a href="admin.php?page=bookings" class="<?php echo $page=='bookings'?'active':''; ?>">Kelola Booking</a>
            <a href="admin.php?page=treatments" class="<?php echo $page=='treatments'?'active':''; ?>">Kelola
                Treatment</a>
            <a href="admin.php?page=members" class="<?php echo $page=='members'?'active':''; ?>">Kelola Member</a>
            <hr>
            <a href="../index.php">Halaman Utama</a>
            <a href="../logout.php">Logout</a>
        </div>

        <div class="main">
            <div class="tab-menu">
                <a href="admin.php?page=bookings" class="<?php echo $page=='bookings'?'active':''; ?>">Daftar
                    Booking</a>
                <a href="admin.php?page=treatments" class="<?php echo $page=='treatments'?'active':''; ?>">Kelola
                    Treatment</a>
                <a href="admin.php?page=members" class="<?php echo $page=='members'?'active':''; ?>">Kelola Member</a>
            </div>

            <?php if($page == 'bookings'): ?>

            <h2>Daftar Booking & Validasi Pembayaran</h2>
            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Member & Treatment</th>
                            <th>Jadwal</th>
                            <th>Status Booking</th>
                            <th>Bukti Bayar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        // QUERY UPDATE: JOIN KE TRANSACTIONS
                        $sql = "SELECT b.*, u.username, t.name as treat, tr.proof_image, tr.payment_status 
                                FROM bookings b
                                JOIN users u ON b.user_id=u.id
                                JOIN treatments t ON b.treatment_id=t.id
                                LEFT JOIN transactions tr ON b.id=tr.booking_id
                                ORDER BY b.created_at DESC";
                        $q = $pdo->query($sql);
                        
                        while($row = $q->fetch()){
                            // Logic Badge Status Booking
                            $st = $row['status'];
                            $paySt = $row['payment_status'];
                            $badgeClr = ($st=='confirmed')?'bg-confirmed':(($st=='cancelled')?'bg-cancelled':'bg-pending');

                            echo "<tr>
                                <td>#{$row['id']}</td>
                                <td>
                                    <strong>{$row['username']}</strong><br>
                                    <small>{$row['treat']}</small>
                                </td>
                                <td>{$row['booking_date']} <br> <b>{$row['booking_time']}</b></td>
                                
                                <td><span class='badge $badgeClr'>$st</span></td>
                                
                                <td>";
                                    if($paySt == 'paid') {
                                        echo "<span style='color:green; font-weight:bold;'>LUNAS ✔</span>";
                                    } elseif($row['proof_image']) {
                                        echo "<a href='../assets/uploads/{$row['proof_image']}' target='_blank' 
                                              style='color:blue; text-decoration:underline;'>Lihat Foto</a>";
                                        if($paySt == 'pending') echo "<br><small style='color:orange;'>Perlu Validasi</small>";
                                    } else {
                                        echo "-";
                                    }
                            echo "</td>

                                <td>";
                                    
                                    // 1. Terima/Tolak Booking (Saat Pending)
                                    if($st == 'pending'){
                                        echo "<a href='?page=bookings&action=approve&id={$row['id']}' class='btn-small btn-success'>✓ Terima</a>
                                              <a href='?page=bookings&action=reject&id={$row['id']}' class='btn-small btn-danger'>✗ Tolak</a>";
                                    }
                                    
                                    // 2. Validasi Pembayaran (Saat Confirmed & Ada Bukti)
                                    if($row['proof_image'] && $paySt == 'pending' && $st == 'confirmed'){
                                        echo "<a href='?page=bookings&action=confirm_pay&id={$row['id']}' class='btn-small btn-purple'>💰 Validasi Bayar</a>";
                                    }

                                    // 3. Selesai Treatment (Saat Confirmed & Lunas)
                                    if($st == 'confirmed' && $row['is_paid'] == 1){
                                        echo "<a href='?page=bookings&action=complete&id={$row['id']}' class='btn-small btn-info'>✓ Selesai</a>";
                                    }

                                    // 4. Jika Selesai/Batal
                                    if($st == 'completed' || $st == 'cancelled') {
                                        echo "<small style='color:#999;'>Arsip</small>";
                                    }
                                    
                            echo "</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <?php elseif($page == 'treatments'): ?>

            <h2>Kelola Treatment</h2>

            <div class="card">
                <h3><?php echo $edit_treatment ? 'Edit Treatment' : 'Tambah Treatment Baru'; ?></h3>
                <form method="POST">
                    <?php if($edit_treatment): ?>
                    <input type="hidden" name="id" value="<?php echo $edit_treatment['id']; ?>">
                    <?php endif; ?>

                    <div class="form-inline">
                        <div>
                            <label>Nama Treatment</label>
                            <input type="text" name="name"
                                value="<?php echo $edit_treatment ? htmlspecialchars($edit_treatment['name']) : ''; ?>"
                                required>
                        </div>
                        <div>
                            <label>Harga (Rp)</label>
                            <input type="number" name="price"
                                value="<?php echo $edit_treatment ? $edit_treatment['price'] : ''; ?>" required>
                        </div>
                    </div>

                    <label>Durasi (Menit)</label>
                    <input type="number" name="duration"
                        value="<?php echo $edit_treatment ? $edit_treatment['duration'] : '60'; ?>" required>

                    <button type="submit" name="<?php echo $edit_treatment ? 'edit_treatment' : 'add_treatment'; ?>"
                        class="btn-primary" style="margin-top:15px;">
                        <?php echo $edit_treatment ? '💾 Update Treatment' : '➕ Tambah Treatment'; ?>
                    </button>

                    <?php if($edit_treatment): ?>
                    <a href="admin.php?page=treatments"
                        style="display:block; text-align:center; margin-top:10px; color:var(--gray);">Batal Edit</a>
                    <?php endif; ?>
                </form>
            </div>

            <h3 style="margin-top:40px;">Daftar Treatment</h3>
            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Treatment</th>
                            <th>Harga</th>
                            <th>Durasi</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $treatments = $pdo->query("SELECT * FROM treatments ORDER BY id DESC");
                        while($t = $treatments->fetch()){
                            echo "<tr>
                                <td>#{$t['id']}</td>
                                <td><b>{$t['name']}</b></td>
                                <td>Rp " . number_format($t['price']) . "</td>
                                <td>{$t['duration']} menit</td>
                                <td>
                                    <a href='?page=treatments&edit={$t['id']}' class='btn-small btn-primary'>✏️ Edit</a>
                                    <a href='?page=treatments&delete_treatment={$t['id']}' class='btn-small btn-danger' 
                                       onclick='return confirm(\"Yakin hapus treatment ini?\")'>🗑️ Hapus</a>
                                </td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <?php elseif($page == 'members'): ?>

            <h2>Kelola Member</h2>
            <div class="card">
                <p style="text-align:center; padding:40px; color:var(--gray);">
                    🚧 Fitur Kelola Member akan segera hadir! 🚧
                </p>
            </div>

            <?php endif; ?>
        </div>
    </div>
</body>

</html>