<?php 
include 'database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Force logout jika ada parameter ?force=1
if (isset($_GET['force'])) {
    session_unset();
    session_destroy();
    session_start();
}

// Validasi session admin masih valid di database
if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin') {
    $stmt = $koneksi->prepare("SELECT session_token FROM admin WHERE id_admin = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $admin = $stmt->get_result()->fetch_assoc();

    // Kalau token masih cocok, redirect ke dashboard
    if ($admin && $admin['session_token'] === $_SESSION['token']) {
        header("Location: dashboard_admin.php");
        exit();
    } else {
        // Token tidak cocok, hapus session lama
        session_unset();
        session_destroy();
        session_start();
    }
}

class Auth {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    private function generateToken($id) {
        $token = bin2hex(random_bytes(32));
        $stmt = $this->db->prepare("UPDATE admin SET session_token = ? WHERE id_admin = ?");
        $stmt->bind_param("si", $token, $id);
        $stmt->execute();
        return $token;
    }

    public function login($username, $password) {
        $stmt = $this->db->prepare("SELECT * FROM admin WHERE username = ?");
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();

        if (!$admin) {
            return "Login gagal! Username atau password salah.";
        }

        if (!password_verify($password, $admin['password'])) {
            return "Login gagal! Username atau password salah.";
        }

        $token = $this->generateToken($admin['id_admin']);
        $_SESSION['role']          = 'admin';
        $_SESSION['user_id']       = $admin['id_admin'];
        $_SESSION['username']      = $admin['username'];
        $_SESSION['token']         = $token;
        $_SESSION['login_success'] = true;

        header("Location: dashboard_admin.php");
        exit();
    }
}

class LoginPage {
    private $auth;
    private $koneksi;
    public $error_message = "";

    public function __construct($koneksi) {
        $this->koneksi = $koneksi;
        $this->auth    = new Auth($koneksi);
    }

    public function handleRequest() {
        if ($_SERVER["REQUEST_METHOD"] === "POST") {
            if (empty($_POST['username']) || empty($_POST['password'])) {
                $this->error_message = "Username dan password tidak boleh kosong.";
                return;
            }

            $username = trim($_POST['username']);
            $password = $_POST['password'];

            $result = $this->auth->login($username, $password);
            if (is_string($result)) {
                $this->error_message = $result;
            }
        }
    }

    public function render() {
        $error = $this->error_message;
        // Ambil pesan dari URL jika ada
        $pesan = $_GET['pesan'] ?? '';
        ?>
        <!DOCTYPE html>
        <html lang="id">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Login Admin - PustaKita</title>
            <script src="https://cdn.tailwindcss.com"></script>
            <link rel="stylesheet" href="/Pustakita_upk_team.1/Pustakita/style css/tampilan.css">
        </head>
        <body class="bg-[url('../../asset/bg-asset.jpeg')] bg-cover bg-center bg-no-repeat min-h-screen">
            <div class="flex justify-center items-center h-screen">
                <div class="bg-gray-100 p-6 rounded shadow-md w-80">

                    <div class="flex flex-col items-center mb-4">
                        <div class="flex items-center gap-2 mb-1">
                            <svg viewBox="0 0 24 24" width="32" height="32" stroke="#04005c" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
                            </svg>
                            <h1 class="text-xl font-bold text-[#04005c]">
                                <span class="text-blue-900">P</span>ustaKita
                            </h1>
                        </div>
                        <p class="text-sm text-gray-500">Sistem Manajemen Perpustakaan SMKN 6 Malang</p>
                    </div>

                    <hr class="mb-4 border-gray-300">
                    <h2 class="text-base font-semibold text-center mb-4">Login Admin</h2>

                    <?php if ($pesan === 'sesi_digantikan'): ?>
                        <div class="bg-yellow-100 border border-yellow-400 text-yellow-700 text-sm px-4 py-2 rounded mb-3">
                            Sesi Anda telah digantikan oleh login lain.
                        </div>
                    <?php endif; ?>

                    <?php if (!empty($error)): ?>
                        <div class="bg-red-100 border border-red-400 text-red-700 text-sm px-4 py-2 rounded mb-3">
                            <?= htmlspecialchars($error) ?>
                        </div>
                    <?php endif; ?>

                    <form action="" method="POST" id="loginForm">
                        <div class="mb-4">
                            <label for="username" class="block text-sm font-medium mb-1">Username</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg viewBox="0 0 24 24" width="18" height="18" stroke="#888" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                        <circle cx="12" cy="7" r="4"></circle>
                                    </svg>
                                </span>
                                <input type="text" id="username" name="username"
                                    placeholder="Masukkan username"
                                    value="<?= isset($_POST['username']) ? htmlspecialchars($_POST['username']) : '' ?>"
                                    class="w-full pl-10 pr-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="block text-sm font-medium mb-1">Password</label>
                            <div class="relative">
                                <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                                    <svg viewBox="0 0 24 24" width="18" height="18" stroke="#888" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                        <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                                    </svg>
                                </span>
                                <input type="password" id="password" name="password"
                                    placeholder="••••••••"
                                    class="w-full pl-10 pr-10 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                                <span class="absolute inset-y-0 right-0 flex items-center pr-3 cursor-pointer" id="togglePassword">
                                    <svg viewBox="0 0 24 24" width="18" height="18" stroke="#888" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                        <circle cx="12" cy="12" r="3"></circle>
                                    </svg>
                                </span>
                            </div>
                        </div>

                        <button type="submit"
                            class="bg-blue-900 text-white w-full py-2 rounded hover:bg-blue-600 transition duration-200">
                            Masuk
                        </button>
                    </form>
                </div>
            </div>
            <script src="../frontend/friontend_login.js"></script>
        </body>
        </html>
        <?php
    }
}

$page = new LoginPage($koneksi);
$page->handleRequest();
$page->render();
?>