<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "pustakita";

$koneksi = mysqli_connect($host, $user, $pass, $dbname);

if (!$koneksi) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

// Set charset ke UTF-8
mysqli_set_charset($koneksi, "utf8mb4");
?>