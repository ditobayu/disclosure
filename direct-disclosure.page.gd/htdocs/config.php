<?php
// config.php
session_start();

$DB_HOST = 'db';
$DB_NAME = 'penilaian_akademik';
$DB_USER = 'user';
$DB_PASS = 'password'; // isi sesuai environment

try {
    $pdo = new PDO("mysql:host={$DB_HOST};dbname={$DB_NAME};charset=utf8mb4", $DB_USER, $DB_PASS, [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
} catch (Exception $e) {
    die("DB connection failed: " . $e->getMessage());
}
?>
