<?php 
include 'database.php';
session_start();

class Auth {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function login($username, $password) {
        // validasi login admin
        $stmt = $this->db->prepare("SELECT * FROM admin WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();

        if ($admin && password_verify($password, $admin['password'])) {
            $_SESSION['role'] = 'admin';
            $_SESSION['user_id'] = $admin['id'];
            $_SESSION['login_success'] = true;
            header("Location: dashboard_admin.php");
            exit();
        }

        // validasi login member
        $stmt = $this->db->prepare("SELECT * FROM member WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $member = $stmt->get_result()->fetch_assoc();

        if ($member && password_verify($password, $member['password'])) {
            $_SESSION['role'] = 'member';
            $_SESSION['user_id'] = $member['id'];
            $_SESSION['login_success'] = true;
            header("Location: dashboard_member.php");
            exit();
        }

        return "Login gagal! Username atau password salah.";
    }
}

$auth = new Auth($koneksi);
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];

    $error_message = $auth->login($username, $password);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/Pustakita_upk_team.1/Pustakita/style_css/tampilan.css">
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
                    <input type="text" id="username" name="username" placeholder="Masukkan username" class="w-full p-2 border rounded" required>
                </div>

                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium">Password</label>
                    <input type="password" id="password" name="password" placeholder="Masukkan password" class="w-full p-2 border rounded" required>
                </div>

                <button type="submit" class="bg-blue-900 text-white w-full py-2 rounded hover:bg-blue-600 mb-3">Login</button>
            </fieldset>
        </form>
    </div>
    <script src="../frontend/friontend_login.js"></script>
</body>
</html>