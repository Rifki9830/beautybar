<?php
session_start();
$host = 'localhost';
$db   = 'beautybar_db';
$user = 'root';
$pass = ''; // Sesuaikan password DB Anda (XAMPP biasanya kosong)

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Koneksi Error: " . $e->getMessage());
}

// Fungsi Cek Role
function checkAccess($role) {
    if (!isset($_SESSION['role']) || $_SESSION['role'] !== $role) {
        header("Location: ../login.php");
        exit;
    }
}
?>