<?php
require '../config.php';
checkAccess('admin');

// Action Approve/Reject
if(isset($_GET['action']) && isset($_GET['id'])) {
    $status = $_GET['action'] == 'approve' ? 'confirmed' : 'cancelled';
    $pdo->prepare("UPDATE bookings SET status=? WHERE id=?")->execute([$status, $_GET['id']]);
    header("Location: admin.php");
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
            <h3>Admin Panel</h3>
            <a href="../logout.php">Logout</a>
        </div>
        <div class="main">
            <h2>Daftar Booking Masuk</h2>
            <div class="card">
                <table>
                    <thead>
                        <tr>
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
                            echo "<tr>
                                <td>{$row['username']}</td>
                                <td>{$row['treat']}</td>
                                <td>{$row['ther']}</td>
                                <td>{$row['booking_date']} <br> {$row['booking_time']}</td>
                                <td><span class='badge ".($row['status']=='confirmed'?'bg-confirmed':'bg-pending')."'>{$row['status']}</span></td>
                                <td>";
                                if($row['status'] == 'pending'){
                                    echo "<a href='?action=approve&id={$row['id']}' style='color:green; font-weight:bold;'>✔ Terima</a> | 
                                          <a href='?action=reject&id={$row['id']}' style='color:red;'>✖ Tolak</a>";
                                }
                            echo "</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>