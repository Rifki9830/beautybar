<?php
require '../config.php';
checkAccess('owner');

// --- 1. HITUNG PENDAPATAN (REVENUE) ---
// Logika: Hitung jika lunas (is_paid=1) ATAU statusnya confirmed/completed
$sqlRevenue = "SELECT SUM(t.price) 
               FROM bookings b 
               JOIN treatments t ON b.treatment_id = t.id 
               WHERE b.is_paid = 1 OR b.status IN ('confirmed', 'completed')";

$rev = $pdo->query($sqlRevenue)->fetchColumn();


// --- 2. DATA GRAFIK KINERJA TERAPIS ---
$therapistLabels = [];
$therapistData = [];

$sqlChart = "SELECT th.name, COUNT(b.id) as jobs 
             FROM bookings b 
             JOIN therapists th ON b.therapist_id = th.id 
             WHERE b.status IN ('confirmed', 'completed') 
             GROUP BY th.name";

$qChart = $pdo->query($sqlChart);
while($r = $qChart->fetch()){
    $therapistLabels[] = $r['name'];
    $therapistData[] = $r['jobs'];
}


// --- 3. DATA KEPUASAN PELANGGAN ---
$ratingAvg = $pdo->query("SELECT AVG(rating) FROM surveys")->fetchColumn();
?>

<!DOCTYPE html>
<html>

<head>
    <title>Owner Dashboard - Beautybar</title>
    <link rel="stylesheet" href="../style.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body>
    <div class="dash-container">
        <div class="sidebar">
            <h3>Owner Panel</h3>
            <p>Halo, <?php echo $_SESSION['name'] ?? 'Owner'; ?></p>
            <hr>
            <a href="owner.php" style="background:var(--primary); color:white;">Laporan & Statistik</a>
            <hr>
            <a href="../logout.php">Logout</a>
        </div>

        <div class="main">
            <h2>Laporan Eksekutif</h2>

            <div class="grid">
                <div class="card" style="background: linear-gradient(135deg, #d63384, #a61e61); color:white;">
                    <h3 style="color:white; opacity:0.9;">Total Pendapatan</h3>
                    <h1 style="font-size: 2.5rem;">Rp <?php echo number_format($rev ?? 0); ?></h1>
                    <small style="opacity:0.8;">Dari Booking Lunas / Dikonfirmasi</small>
                </div>

                <div class="card">
                    <h3>Kepuasan Pelanggan</h3>
                    <h1 style="color:var(--primary); font-size: 2.5rem;">
                        <?php echo number_format($ratingAvg ?? 0, 1); ?> <span style="font-size:1rem; color:#777;">/
                            5.0</span>
                    </h1>
                    <small style="color:#777;">Berdasarkan survei masuk</small>
                </div>
            </div>

            <div class="grid" style="margin-top:30px; align-items:start;">

                <div class="card">
                    <h3>Statistik Order Terapis</h3>
                    <?php if(count($therapistLabels) > 0): ?>
                    <canvas id="therapistChart"></canvas>
                    <?php else: ?>
                    <p style="text-align:center; padding:30px; color:#aaa;">Belum ada data booking.</p>
                    <?php endif; ?>
                </div>

                <div class="card">
                    <h3>Feedback Terbaru</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Rating</th>
                                <th>Komentar</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $feed = $pdo->query("SELECT * FROM surveys ORDER BY id DESC LIMIT 5");
                            if($feed->rowCount() > 0) {
                                while($f = $feed->fetch()){
                                    echo "<tr>
                                        <td><span class='badge bg-confirmed'>★ {$f['rating']}</span></td>
                                        <td style='font-size:0.9rem;'>{$f['feedback']}</td>
                                    </tr>";
                                }
                            } else {
                                echo "<tr><td colspan='2' style='text-align:center; color:#aaa;'>Belum ada ulasan</td></tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
    const ctx = document.getElementById('therapistChart');
    if (ctx) {
        new Chart(ctx, {
            type: 'bar',
            data: {
                labels: <?php echo json_encode($therapistLabels); ?>,
                datasets: [{
                    label: 'Jumlah Pekerjaan Selesai',
                    data: <?php echo json_encode($therapistData); ?>,
                    backgroundColor: [
                        'rgba(214, 51, 132, 0.7)', // Pink
                        'rgba(54, 162, 235, 0.7)', // Blue
                        'rgba(255, 206, 86, 0.7)', // Yellow
                        'rgba(75, 192, 192, 0.7)' // Teal
                    ],
                    borderColor: [
                        'rgba(214, 51, 132, 1)',
                        'rgba(54, 162, 235, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(75, 192, 192, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    }
    </script>
</body>

</html>