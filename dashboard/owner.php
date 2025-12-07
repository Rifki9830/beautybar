<?php
require '../config.php';
checkAccess('owner');

// Hitung Total Pendapatan
$rev = $pdo->query("SELECT SUM(t.price) FROM bookings b JOIN treatments t ON b.treatment_id=t.id WHERE b.status='confirmed'")->fetchColumn();
// Hitung Total Booking
$cnt = $pdo->query("SELECT COUNT(*) FROM bookings WHERE status='confirmed'")->fetchColumn();
?>
<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="../style.css">
</head>

<body>
    <div class="dash-container">
        <div class="sidebar">
            <h3>Owner Panel</h3>
            <a href="../logout.php">Logout</a>
        </div>
        <div class="main">
            <h2>Laporan Eksekutif</h2>
            <div class="grid">
                <div class="card" style="background:#d63384; color:white;">
                    <h3>Total Pendapatan</h3>
                    <h1>Rp <?php echo number_format($rev); ?></h1>
                </div>
                <div class="card">
                    <h3>Total Pelanggan Terlayani</h3>
                    <h1><?php echo $cnt; ?> Orang</h1>
                </div>
            </div>

            <h3>Kinerja Terapis</h3>
            <div class="card">
                <table>
                    <tr>
                        <th>Nama Terapis</th>
                        <th>Jumlah Pekerjaan</th>
                    </tr>
                    <?php
                    $perf = $pdo->query("SELECT th.name, COUNT(b.id) as jobs FROM bookings b JOIN therapists th ON b.therapist_id=th.id WHERE b.status='confirmed' GROUP BY th.name");
                    while($p = $perf->fetch()){
                        echo "<tr><td>{$p['name']}</td><td>{$p['jobs']} Booking</td></tr>";
                    }
                    ?>
                </table>
            </div>
        </div>
    </div>
</body>

</html>