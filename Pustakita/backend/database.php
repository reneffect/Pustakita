<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "pustakita_db"; // ganti dengan nama database kamu

$koneksi = new mysqli($host, $user, $password, $dbname);

if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}
?>