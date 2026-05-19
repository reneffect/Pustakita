<?php
include 'database.php';
session_start();

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];

    $stmt = $koneksi->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username); 
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (password_verify($password, $row['password'])) {
            $_SESSION['user_id'] = $row['id'];
            $_SESSION['username'] = $row['username'];
            header("Location: dashboard.php");
            exit();
        } else {
            echo "Password notmatch.";
        }
    } else {
        echo "no username here.";
    }

    $stmt->close();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <title>Document</title>
</head>
<body bg-white>
    <fildset>
        <legend>Login</legend>
        <p>
            <label for="username" id="username">Username</label>
            <input type="text" name="usename" placeholder="username" required>
        </p>

        <p>
            <label for="password" id="password"></label>
            <input type="password" name="password" placeholder="password" required>
        </p>
    </fildset>
</body>
</html>