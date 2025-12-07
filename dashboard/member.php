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
        $msg = "<script>alert('Maaf, Terapis sudah dibooking pada jam tersebut. Pilih jam lain!');</script>";
    } else {
        $sql = "INSERT INTO bookings (user_id, treatment_id, therapist_id, booking_date, booking_time) VALUES (?,?,?,?,?)";
        $pdo->prepare($sql)->execute([$uid, $treat_id, $ther_id, $date, $time]);
        $msg = "<script>alert('Booking Berhasil! Menunggu konfirmasi admin.');</script>";
    }
}
?>
<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="../style.css">
</head>

<body>
    <div class="dash-container">
        <div class="sidebar">
            <h3>Member Area</h3>
            <p>Halo, <?php echo $_SESSION['name']; ?></p>
            <hr style="border:1px solid #444;">
            <a href="../index.php">Halaman Utama</a>
            <a href="../logout.php">Logout</a>
        </div>
        <div class="main">
            <h2>Buat Booking Baru</h2>
            <?php echo $msg; ?>

            <div class="card">
                <form method="POST">
                    <div class="grid">
                        <div>
                            <label>Pilih Treatment</label>
                            <select name="treatment" required>
                                <?php
                                $t = $pdo->query("SELECT * FROM treatments");
                                while($r = $t->fetch()){ echo "<option value='{$r['id']}'>{$r['name']} - Rp ".number_format($r['price'])."</option>"; }
                                ?>
                            </select>
                        </div>
                        <div>
                            <label>Pilih Terapis (Nesya/Putri)</label>
                            <select name="therapist" required>
                                <?php
                                $th = $pdo->query("SELECT * FROM therapists");
                                while($r = $th->fetch()){ echo "<option value='{$r['id']}'>{$r['name']}</option>"; }
                                ?>
                            </select>
                        </div>
                    </div>

                    <div class="grid">
                        <div>
                            <label>Tanggal</label>
                            <input type="date" name="date" min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                        <div>
                            <label>Jam (09:00 - 21:00)</label>
                            <select name="time" required>
                                <?php
                                for($i=9; $i<=21; $i++) {
                                    $jam = str_pad($i, 2, '0', STR_PAD_LEFT).":00";
                                    echo "<option value='$jam'>$jam</option>";
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <button type="submit" name="booking">Booking Sekarang</button>
                </form>
            </div>

            <h3>Riwayat Booking Saya</h3>
            <table>
                <tr>
                    <th>Tanggal</th>
                    <th>Jam</th>
                    <th>Treatment</th>
                    <th>Terapis</th>
                    <th>Status</th>
                </tr>
                <?php
                $hist = $pdo->prepare("SELECT b.*, t.name as tname, th.name as thname FROM bookings b 
                                       JOIN treatments t ON b.treatment_id=t.id 
                                       JOIN therapists th ON b.therapist_id=th.id 
                                       WHERE b.user_id=? ORDER BY b.id DESC");
                $hist->execute([$_SESSION['user_id']]);
                while($h = $hist->fetch()) {
                    $badge = $h['status'] == 'confirmed' ? 'bg-confirmed' : 'bg-pending';
                    echo "<tr>
                        <td>{$h['booking_date']}</td>
                        <td>{$h['booking_time']}</td>
                        <td>{$h['tname']}</td>
                        <td>{$h['thname']}</td>
                        <td><span class='badge $badge'>{$h['status']}</span></td>
                    </tr>";
                }
                ?>
            </table>
        </div>
    </div>
</body>

</html>