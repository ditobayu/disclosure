<?php
// config.php
session_start();

$DB_HOST = 'sql204.infinityfree.com';
$DB_NAME = 'if0_40090928_penilaian_akademik';
$DB_USER = 'if0_40090928';
$DB_PASS = 'Nazwanaz22'; // isi sesuai environment

try {
    $pdo = new PDO("mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4", $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (Exception $e) {
    die("DB connection failed: " . $e->getMessage());
}
?>
