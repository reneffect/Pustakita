<?php
include 'database.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'];
    $password = $_POST['password'];

    $stmt = $koneksi->prepare("SELECT id FROM user WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        echo "<script>alert('Username telah dipakai');</script>";
    } else {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        $insert = $koneksi->prepare("INSERT INTO user (username, password) VALUES(?, ?)");
        $insert->bind_param("ss", $username, $hashed_password);

        if ($insert->execute()) {
            echo "<script>alert('Registrasi berhasil'); window.location.href='login.php';</script>";
        } else {
            echo "<script>alert('Registrasi gagal');</script>";
        }
        $insert->close();
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
    <title>Register - Pustakakita</title>
</head>
<body class="bg-white">
    <div class="flex items-center justify-center h-screen">
        <form action="homepage.php" method="POST" class="bg-gray-100 p-6 rounded shadow-md w-80">
            <fieldset>
                <legend class="text-center mt-10">Pustakakita</legend>
                <p class="text-center text-sm mb-4">Sistem manajemen Perpustakaan SMKN 6 Malang</p>

                <?php if (!empty($error_message)): ?>
                    <p class="text-red-500 text-sm mb-2"><?php echo $error_message; ?></p>
                <?php endif; ?>

                <div class="mb-4">
                    <label for="username" class="block text-sm font-medium">Username</label>
                    <input type="text" id="username" name="username" class="w-full p-2border rounded" required>
                </div>

                <div class="mb-4">
                    <label for="Email" class="block text-sm font-medium">Email</label>
                    <input type="email" id="Email" name="email" class="w-full p-2border rounded" required>
                </div>

                <div class="mb-4">
                    <label for="password" class="black text-sm font-medium">password</div>
                    <input type="password" id="password" name="password" class="w-full p-2border rounded" required>
                </div>

                <div class="mb-4">
                    <label for="No Handphone" class="black text-sm font-medium">No Handphone</label>
                    <input type="number" id="No Handphone" name="No Handphone" class="w-full p-2border rounded" required>
                </div>

                <div class="mb-4">
                    <label for="Alamat" class="black text-sm font-medium">Alamat</label>
                    <textarea id="alamat" name="alamat" class="w-full p-2border rounded" required></textarea>
                </div>

                <button type="submit" class="bg-blue-900 text-white w-full py-2 rounded hovering:bg-blue-600">Masuk</button>
            </fieldset>
        </form>
    </div>
</body>
</html>