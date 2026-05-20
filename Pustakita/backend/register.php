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
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="tampilan.css">
    <title>Document</title>
</head>
<body class="bg-white">
    <div class="flex items-center justify-center h-screen">
        <from action="homepage.php" method="POST" class="bg-gray-100 p-6 rounded shadow-md w-80">
            <fieldset>
                <legend class="text-center mt-10"></legend>
            </fiedset>
    </div>
    
</body>
</html>