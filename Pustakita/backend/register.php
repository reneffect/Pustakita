<?php
include 'database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_post['password'];

    $stmt = $koneksi-> prepare("SELECT id, FROM user WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rowa > 0){
        echo "<script> alret('Username telah dipakai');</script>";
    }else{
        $hased_password = password_hash($password,PASSWORD_DEFAULT);

        $insert = $koneksi->prepare("INSERT INTO user (username, password) VALUES(?, ?)");
        $insert->bind_param("ss", $username, $hased_password);

        if ($insert->execute()) {
            echo "<script> alret('regristasi berhasil'); window.location.herf='login.php';</script>";
        }else{
            echo "<script> alret('regristasi gagal');</script>";
        }
        $insert_>close();
    }
    $stmt->close();
}
$koneksi->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
</head>
<body>
    
</body>
</html>