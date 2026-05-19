<?php
$host = 'localhost';
$username = 'root';
$password = '';
$database = 'pustakita_db';

$koneksi - mysqli_connect($host, $username, $password, $database);

if (!$koneksi) {
    die("conncetion failed:" . mysqli_connect_error());
}

mysql_set_charset($koneksi, 'utf8');

$conn = $koneksi;
?>