<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "pustakita_db";

$koneksi = new mysqli($host, $user, $password, $dbname);

if ($koneksi->connect_error) {
    die("Koneksi gagal: " . $koneksi->connect_error);
}
?>