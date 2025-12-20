<?php
require '../config.php';
checkAccess('member');

if (isset($_POST['upload'])) {
    $booking_id = $_POST['booking_id'];
    $amount = $_POST['amount'];
    $method = $_POST['method'];

    $targetDir = __DIR__ . "/../assets/uploads/";

    if (!file_exists($targetDir)) {
        if (!mkdir($targetDir, 0777, true)) {
            die("Gagal membuat folder 'assets/uploads'. Silakan buat folder ini secara manual.");
        }
    }

    $foto = $_FILES['proof'];
    $fileExt = strtolower(pathinfo($foto['name'], PATHINFO_EXTENSION));
    
    $newFileName = time() . '_' . rand(100,999) . '.' . $fileExt;
    $targetFile = $targetDir . $newFileName;

    if ($foto['error'] === 4) {
        echo "<script>alert('Pilih gambar terlebih dahulu!'); window.history.back();</script>";
        exit;
    }

    $allowed = ['jpg', 'jpeg', 'png'];
    if (!in_array($fileExt, $allowed)) {
        echo "<script>alert('Hanya file JPG, JPEG, & PNG yang diperbolehkan.'); window.history.back();</script>";
        exit;
    }

    if ($foto['size'] > 2 * 1024 * 1024) {
        echo "<script>alert('Ukuran file terlalu besar! Maksimal 2MB.'); window.history.back();</script>";
        exit;
    }

    if (move_uploaded_file($foto['tmp_name'], $targetFile)) {
        
        $check = $pdo->prepare("SELECT id FROM transactions WHERE booking_id = ?");
        $check->execute([$booking_id]);

        if ($check->rowCount() > 0) {
            $sql = "UPDATE transactions SET proof_image = ?, payment_method = ?, payment_status = 'pending' WHERE booking_id = ?";
            $pdo->prepare($sql)->execute([$newFileName, $method, $booking_id]);
        } else {
            $sql = "INSERT INTO transactions (booking_id, amount, payment_method, payment_status, proof_image) VALUES (?, ?, ?, 'pending', ?)";
            $pdo->prepare($sql)->execute([$booking_id, $amount, $method, $newFileName]);
        }

        echo "<script>alert('Bukti pembayaran berhasil dikirim! Mohon tunggu konfirmasi Admin.'); window.location='member.php';</script>";
        
    } else {
        echo "<script>alert('Gagal menyimpan file ke folder server. Pastikan folder assets/uploads ada.'); window.history.back();</script>";
    }
}
?>