<?php
require '../config.php';
checkAccess('admin');

// Action Approve/Reject Booking
if(isset($_GET['action']) && isset($_GET['id'])) {
    $status = $_GET['action'] == 'approve' ? 'confirmed' : 'cancelled';
    $pdo->prepare("UPDATE bookings SET status=? WHERE id=?")->execute([$status, $_GET['id']]);
    header("Location: admin.php");
}

// CRUD Treatment - Create
if(isset($_POST['add_treatment'])) {
    $name = $_POST['name'];
    $price = $_POST['price'];
    $duration = $_POST['duration'];
    
    $stmt = $pdo->prepare("INSERT INTO treatments (name, price, duration) VALUES (?, ?, ?)");
    $stmt->execute([$name, $price, $duration]);
    echo "<script>alert('Treatment berhasil ditambahkan!'); window.location='admin.php?page=treatments';</script>";
}

// CRUD Treatment - Update
if(isset($_POST['edit_treatment'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $duration = $_POST['duration'];
    
    $stmt = $pdo->prepare("UPDATE treatments SET name=?, price=?, duration=? WHERE id=?");
    $stmt->execute([$name, $price, $duration, $id]);
    echo "<script>alert('Treatment berhasil diupdate!'); window.location='admin.php?page=treatments';</script>";
}

// CRUD Treatment - Delete
if(isset($_GET['delete_treatment'])) {
    $id = $_GET['delete_treatment'];
    $pdo->prepare("DELETE FROM treatments WHERE id=?")->execute([$id]);
    echo "<script>alert('Treatment berhasil dihapus!'); window.location='admin.php?page=treatments';</script>";
}

// Ambil data treatment untuk edit
$edit_treatment = null;
if(isset($_GET['edit'])) {
    $stmt = $pdo->prepare("SELECT * FROM treatments WHERE id=?");
    $stmt->execute([$_GET['edit']]);
    $edit_treatment = $stmt->fetch();
}

// Tentukan halaman aktif
$page = isset($_GET['page']) ? $_GET['page'] : 'bookings';
?>
<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="../style.css">
    <style>
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
        .btn-small {
            padding: 6px 12px;
            background: var(--primary);
            color: white;
            border-radius: 5px;
            font-size: 0.85rem;
            display: inline-block;
            margin-right: 5px;
        }
        .btn-danger {
            background: #dc3545;
        }
        .btn-success {
            background: #28a745;
        }
        .btn-small:hover {
            opacity: 0.8;
        }
    </style>
</head>

<body>
    <div class="dash-container">
        <div class="sidebar">
            <h3>Admin Panel</h3>
            <p>Halo, <?php echo $_SESSION['name']; ?></p>
            <hr>
            <a href="admin.php?page=bookings">Kelola Booking</a>
            <a href="admin.php?page=treatments">Kelola Treatment</a>
            <a href="admin.php?page=members">Kelola Member</a>
            <a href="../index.php">Halaman Utama</a>
            <a href="../logout.php">Logout</a>
        </div>
        
        <div class="main">
            <!-- Tab Menu -->
            <div class="tab-menu">
                <a href="admin.php?page=bookings" class="<?php echo $page=='bookings'?'active':''; ?>">Daftar Booking</a>
                <a href="admin.php?page=treatments" class="<?php echo $page=='treatments'?'active':''; ?>">Kelola Treatment</a>
                <a href="admin.php?page=members" class="<?php echo $page=='members'?'active':''; ?>">Kelola Member</a>
            </div>

            <?php if($page == 'bookings'): ?>
            <!-- HALAMAN BOOKING -->
            <h2>Daftar Booking Masuk</h2>
            <div class="card">
                <table>
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Member</th>
                            <th>Treatment</th>
                            <th>Terapis</th>
                            <th>Jadwal</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        $sql = "SELECT b.*, u.username, t.name as treat, th.name as ther 
                                FROM bookings b
                                JOIN users u ON b.user_id=u.id
                                JOIN treatments t ON b.treatment_id=t.id
                                JOIN therapists th ON b.therapist_id=th.id
                                ORDER BY b.created_at DESC";
                        $q = $pdo->query($sql);
                        while($row = $q->fetch()){
                            $badge_class = '';
                            if($row['status'] == 'confirmed') $badge_class = 'bg-confirmed';
                            else if($row['status'] == 'cancelled') $badge_class = 'bg-cancelled';
                            else $badge_class = 'bg-pending';
                            
                            echo "<tr>
                                <td>#{$row['id']}</td>
                                <td>{$row['username']}</td>
                                <td>{$row['treat']}</td>
                                <td>{$row['ther']}</td>
                                <td>{$row['booking_date']} <br> <b>{$row['booking_time']}</b></td>
                                <td><span class='badge $badge_class'>{$row['status']}</span></td>
                                <td>";
                                if($row['status'] == 'pending'){
                                    echo "<a href='?action=approve&id={$row['id']}' class='btn-small btn-success'>✓ Terima</a>
                                          <a href='?action=reject&id={$row['id']}' class='btn-small btn-danger'>✗ Tolak</a>";
                                } else {
                                    echo "<small style='color:#999;'>Selesai</small>";
                                }
                            echo "</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <?php elseif($page == 'treatments'): ?>
            <!-- HALAMAN CRUD TREATMENT -->
            <h2>Kelola Treatment</h2>
            
            <!-- Form Tambah/Edit Treatment -->
            <div class="card">
                <h3><?php echo $edit_treatment ? 'Edit Treatment' : 'Tambah Treatment Baru'; ?></h3>
                <form method="POST">
                    <?php if($edit_treatment): ?>
                        <input type="hidden" name="id" value="<?php echo $edit_treatment['id']; ?>">
                    <?php endif; ?>
                    
                    <div class="form-inline">
                        <div>
                            <label>Nama Treatment</label>
                            <input type="text" name="name" value="<?php echo $edit_treatment ? htmlspecialchars($edit_treatment['name']) : ''; ?>" required>
                        </div>
                        <div>
                            <label>Harga (Rp)</label>
                            <input type="number" name="price" value="<?php echo $edit_treatment ? $edit_treatment['price'] : ''; ?>" required>
                        </div>
                    </div>
                    
                    <label>Durasi (Menit)</label>
                    <input type="number" name="duration" value="<?php echo $edit_treatment ? $edit_treatment['duration'] : '60'; ?>" required>
                    
                    <button type="submit" name="<?php echo $edit_treatment ? 'edit_treatment' : 'add_treatment'; ?>">
                        <?php echo $edit_treatment ? '💾 Update Treatment' : '➕ Tambah Treatment'; ?>
                    </button>
                    
                    <?php if($edit_treatment): ?>
                        <a href="admin.php?page=treatments" style="display:block; text-align:center; margin-top:10px; color:var(--gray);">Batal Edit</a>
                    <?php endif; ?>
                </form>
            </div>

            <!-- Daftar Treatment -->
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
                                    <a href='?page=treatments&edit={$t['id']}' class='btn-small'>✏️ Edit</a>
                                    <a href='?delete_treatment={$t['id']}' class='btn-small btn-danger' 
                                       onclick='return confirm(\"Yakin hapus treatment ini?\")'>🗑️ Hapus</a>
                                </td>
                            </tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>

            <?php elseif($page == 'members'): ?>
            <!-- HALAMAN KELOLA MEMBER (Coming Soon) -->
            <h2>Kelola Member</h2>
            <div class="card">
                <p style="text-align:center; padding:40px; color:var(--gray);">
                    🚧 Fitur ini akan segera hadir! 🚧
                </p>
            </div>

            <?php endif; ?>
        </div>
    </div>
</body>

</html>