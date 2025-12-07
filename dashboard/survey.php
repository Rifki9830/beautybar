<?php
require '../config.php';
checkAccess('member');

if(isset($_POST['submit_survey'])) {
    $bid = $_POST['booking_id'];
    $rate = $_POST['rating'];
    $msg = $_POST['feedback'];
    
    // Simpan Survei
    $pdo->prepare("INSERT INTO surveys (booking_id, rating, feedback) VALUES (?,?,?)")->execute([$bid, $rate, $msg]);
    header("Location: member.php?msg=terimakasih");
}

$booking_id = $_GET['id'] ?? 0;
?>
<!DOCTYPE html>
<html>

<head>
    <link rel="stylesheet" href="../style.css">
</head>

<body>
    <div class="container" style="max-width:500px; margin-top:50px;">
        <div class="card">
            <h2 style="text-align:center;">Survei Kepuasan</h2>
            <p>Bagaimana pengalaman treatment Anda?</p>
            <form method="POST">
                <input type="hidden" name="booking_id" value="<?php echo $booking_id; ?>">

                <label>Rating (1 - 5 Bintang)</label>
                <select name="rating" required>
                    <option value="5">★★★★★ - Sangat Puas</option>
                    <option value="4">★★★★ - Puas</option>
                    <option value="3">★★★ - Cukup</option>
                    <option value="2">★★ - Kurang</option>
                    <option value="1">★ - Kecewa</option>
                </select>

                <label>Kritik & Saran</label>
                <textarea name="feedback" rows="4" style="width:100%; border:1px solid #ddd; padding:10px;"
                    required></textarea>

                <button type="submit" name="submit_survey" style="margin-top:10px;">Kirim Ulasan</button>
            </form>
            <a href="member.php" style="display:block; text-align:center; margin-top:10px;">Kembali</a>
        </div>
    </div>
</body>

</html>