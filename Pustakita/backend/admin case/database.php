<?php
// ==========================================
// KONEKSI DATABASE
// ==========================================
$host   = "localhost";
$user   = "root";
$pass   = "";
$dbname = "pustakita";

// Konek ke MySQL dulu tanpa pilih database
$conn = new mysqli($host, $user, $pass);

if ($conn->connect_error) {
    die("Koneksi MySQL gagal: " . $conn->connect_error);
}

// Buat database otomatis kalau belum ada
$conn->query("CREATE DATABASE IF NOT EXISTS `$dbname` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

// Pilih database
$conn->select_db($dbname);
$conn->set_charset("utf8mb4");