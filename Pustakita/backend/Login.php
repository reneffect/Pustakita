<?php
include 'database.php';
session_start();
//OOP Login page
class User {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function login($username, $password) {
        $stmt = $this->db->prepare("SELECT id, username, password FROM users WHERE username = ?");
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
                return "Password yang Anda masukkan salah.";
            }
        } else {
            return "Username tidak ditemukan.";
        }
    }
}

// Inisialisasi
$user = new User($koneksi);
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];

    $error_message = $user->login($username, $password);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/Pustakita_upk_team.1/Pustakita/style css/tampilan.css">
    <title>Login - Pustakakita</title>
</head>
<body class="bg-white">
    <div class="flex justify-center items-center h-screen">
        <form action="" method="POST" class="bg-gray-100 p-6 rounded shadow-md w-80">
            <fieldset>
                <legend class="text-lg font-bold text-center mb-2">Pustakakita</legend>
                <p class="text-center text-sm mb-4">Sistem manajemen perpustakaan SMKN 6 MALANG</p>

                <?php if (!empty($error_message)): ?>
                    <p class="text-red-500 text-sm mb-2"><?php echo $error_message; ?></p>
                <?php endif; ?>

                <div class="mb-4">
                    <label for="username" class="block text-sm font-medium">Username</label>
                    <input type="text" id="username" name="username" placeholder="Username" class="w-full p-2 border rounded" required>
                </div>

                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium">Password</label>
                    <input type="password" id="password" name="password" placeholder="Password" class="w-full p-2 border rounded" required>
                </div>

                <button type="submit" class="bg-blue-900 text-white w-full py-2 rounded hover:bg-blue-600">Login</button>

                <div class="mb-4">
                    <p class="text-center text-sm mb-4">Belum punya akun?</p>
                </div>

                <button type="button" class="bg-blue-500 text-white w-full py-2 rounded hover:bg-blue-600" onclick="window.location.href='register.php'">Register</button>
            </fieldset>
        </form>
    </div>
    <script src="friontend_login.js"></script>
</body>
</html>