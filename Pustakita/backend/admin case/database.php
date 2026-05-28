<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "pustakita";

$conn = mysqli_connect($host, $user, $pass, $dbname);

if (!$conn) {
    die("Koneksi ke database gagal: " . mysqli_connect_error());
}

// Set charset ke UTF-8
mysqli_set_charset($conn, "utf8mb4");
