<?php
require '../config.php';
checkAccess('member');

// Pastikan ada ID booking dan Jumlah bayar di URL
if (isset($_GET['id']) && isset($_GET['amount'])) {
    $booking_id = $_GET['id'];
    $amount = $_GET['amount'];

    try {
        // 1. Update status 'is_paid' di tabel bookings menjadi 1 (Sudah Bayar)
        $pdo->prepare("UPDATE bookings SET is_paid = 1 WHERE id = ?")->execute([$booking_id]);

        // 2. Masukkan data ke tabel transactions (Agar laporan Owner bertambah)
        // Kita cek dulu apakah sudah ada transaksi untuk booking ini agar tidak dobel
        $check = $pdo->prepare("SELECT id FROM transactions WHERE booking_id = ?");
        $check->execute([$booking_id]);
        
        if ($check->rowCount() == 0) {
            $sql = "INSERT INTO transactions (booking_id, amount, payment_status, payment_method) VALUES (?, ?, 'paid', 'Transfer')";
            $pdo->prepare($sql)->execute([$booking_id, $amount]);
        }

        // 3. Tampilkan pesan sukses dan kembali ke dashboard
        echo "<script>
            alert('Pembayaran Berhasil sebesar Rp " . number_format($amount) . "!'); 
            window.location='member.php';
        </script>";
        
    } catch (Exception $e) {
        echo "Error: " . $e->getMessage();
    }

} else {
    // Jika diakses langsung tanpa ID, kembalikan ke member area
    header("Location: member.php");
    exit;
}
?>