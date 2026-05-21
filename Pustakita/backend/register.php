<?php
// filepath: c:\laragon\www\Pustakita_upk_team.1\Pustakita\backend\register.php

include 'database.php';
session_start();

class User {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function register($username, $password, $email, $phone, $address) {
        // Periksa apakah username sudah ada
        $stmt = $this->db->prepare("SELECT id FROM user WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            return "Username telah dipakai.";
        } else {
            // Hash password
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);

            // Masukkan data ke database
            $insert = $this->db->prepare("INSERT INTO user (username, password, email, phone, address) VALUES(?, ?, ?, ?, ?)");
            $insert->bind_param("sssss", $username, $hashed_password, $email, $phone, $address);

            if ($insert->execute()) {
                header("Location: login.php");
                exit();
            } else {
                return "Registrasi gagal. Silakan coba lagi.";
            }
        }
    }
}

// Inisialisasi
$user = new User($koneksi);
$error_message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];
    $email = $_POST['email'];
    $phone = $_POST['No_Handphone'];
    $address = $_POST['alamat'];

    $error_message = $user->register($username, $password, $email, $phone, $address);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/Pustakita_upk_team.1/Pustakita/style_css/tampilan.css">
    <title>Register - Pustakakita</title>
</head>
<body class="bg-white">
    <div class="flex items-center justify-center h-screen">
        <form action="" method="POST" class="bg-gray-100 p-6 rounded shadow-md w-80">
            <fieldset>
                <legend class="text-center mt-10">Pustakakita</legend>
                <p class="text-center text-sm mb-4">Sistem manajemen Perpustakaan SMKN 6 Malang</p>

                <?php if (!empty($error_message)): ?>
                    <p class="text-red-500 text-sm mb-2"><?php echo $error_message; ?></p>
                <?php endif; ?>

                <div class="mb-4">
                    <label for="username" class="block text-sm font-medium">Username</label>
                    <input type="text" id="username" name="username" class="w-full p-2 border rounded" required>
                </div>

                <div class="mb-4">
                    <label for="email" class="block text-sm font-medium">Email</label>
                    <input type="email" id="email" name="email" class="w-full p-2 border rounded" required>
                </div>

                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium">Password</label>
                    <input type="password" id="password" name="password" class="w-full p-2 border rounded" required>
                </div>

                <div class="mb-4">
                    <label for="No_Handphone" class="block text-sm font-medium">No Handphone</label>
                    <input type="number" id="No_Handphone" name="No_Handphone" class="w-full p-2 border rounded" required>
                </div>

                <div class="mb-4">
                    <label for="alamat" class="block text-sm font-medium">Alamat</label>
                    <textarea id="alamat" name="alamat" class="w-full p-2 border rounded" required></textarea>
                </div>

                <button type="submit" class="bg-blue-900 text-white w-full py-2 rounded hover:bg-blue-600">Daftar</button>
            </fieldset>
        </form>
    </div>
</body>
</html>