<?php
require '../config.php';
checkAccess('owner');

// 1. Hitung Pendapatan
$rev = $pdo->query("SELECT SUM(t.price) FROM bookings b JOIN treatments t ON b.treatment_id=t.id WHERE b.status='confirmed'")->fetchColumn();

// 2. Data Grafik Kinerja Terapis (Untuk Chart.js)
$therapistLabels = [];
$therapistData = [];
$qChart = $pdo->query("SELECT th.name, COUNT(b.id) as jobs FROM bookings b JOIN therapists th ON b.therapist_id=th.id WHERE b.status='confirmed' GROUP BY th.name");
while($r = $qChart->fetch()){
    $therapistLabels[] = $r['name'];
    $therapistData[] = $r['jobs'];
}

// 3. Rata-rata Kepuasan
$ratingAvg = $pdo->query("SELECT AVG(rating) FROM surveys")->fetchColumn();
?>
<!DOCTYPE html>
<html>

<head>
    <title>Owner Dashboard</title>
    <link rel="stylesheet" href="../style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <div class="dash-container">
        <div class="sidebar">
            <h3>Owner Panel</h3>
            <p>Laporan & Statistik</p>
            <hr>
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
                    <h3>Kepuasan Pelanggan</h3>
                    <h1><?php echo number_format($ratingAvg, 1); ?> / 5.0</h1>
                    <small>Dari seluruh survei</small>
                </div>
            </div>

            <div class="grid" style="margin-top:20px;">
                <div class="card">
                    <h3>Statistik Order Terapis</h3>
                    <canvas id="therapistChart"></canvas>
                </div>

                <div class="card">
                    <h3>Feedback Pelanggan Terbaru</h3>
                    <table>
                        <tr>
                            <th>Rating</th>
                            <th>Komentar</th>
                        </tr>
                        <?php
                        $feed = $pdo->query("SELECT * FROM surveys ORDER BY id DESC LIMIT 5");
                        while($f = $feed->fetch()){
                            echo "<tr>
                                <td><span class='badge bg-confirmed'>★ {$f['rating']}</span></td>
                                <td>{$f['feedback']}</td>
                            </tr>";
                        }
                        ?>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
    const ctx = document.getElementById('therapistChart').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($therapistLabels); ?>,
            datasets: [{
                label: 'Jumlah Pekerjaan Selesai',
                data: <?php echo json_encode($therapistData); ?>,
                backgroundColor: ['#d63384', '#333'],
                borderWidth: 1
            }]
        }
    });
    </script>
</body>

</html>