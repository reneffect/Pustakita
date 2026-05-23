<?php
include 'database.php';
session_start();

class User
{
  private $db;

  public function __construct($db)
  {
    $this->db = $db;
  }

  public function login($username, $password)
  {
    $stmt = $this->db->prepare("SELECT id, username, password FROM users WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      $row = $result->fetch_assoc();
      if (password_verify($password, $row['password'])) {
        $_SESSION['user_id'] = $row['id'];
        $_SESSION['username'] = $row['username'];
        header("Location: home.php");
        exit();
      } else {
        return "Password yang Anda masukkan salah.";
      }
    } else {
      return "Username tidak ditemukan.";
    }
  }
}

$user = new User($koneksi);
$error_message = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $username = mysqli_real_escape_string($koneksi, $_POST['username']);
  $password = $_POST['password'];
  $error_message = $user->login($username, $password);
}
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - PustaKita SMKN 6 Malang</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="style.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>

<body>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/Pustakita_upk_team.1/Pustakita/style_css/tampilan.css">
    <title>Login - PustaKita</title>
</head>
<body class="bg-white">
    <div class="flex justify-center items-center h-screen">
        <div class="bg-gray-100 p-6 rounded shadow-md w-80">

            <!-- Header -->
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
                <p class="text-sm text-gray-500">Perpustakaan SMKN 6 Malang</p>
            </div>

            <hr class="mb-4 border-gray-300">

            <!-- Form -->
            <h2 class="text-base font-semibold text-center mb-4">Masuk ke Akun Anda</h2>

            <?php if (!empty($error_message)): ?>
                <p class="text-red-500 text-sm mb-3"><?php echo $error_message; ?></p>
            <?php endif; ?>

            <form action="" method="POST" id="loginForm">

                <!-- Username -->
                <div class="mb-4">
                    <label for="username" class="block text-sm font-medium mb-1">Username / Email</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg viewBox="0 0 24 24" width="18" height="18" stroke="#888" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                                <circle cx="12" cy="7" r="4"></circle>
                            </svg>
                        </span>
                        <input type="text" id="username" name="username" placeholder="Masukkan username"
                            class="w-full pl-10 pr-3 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                    </div>
                </div>

                <!-- Password -->
                <div class="mb-4">
                    <label for="password" class="block text-sm font-medium mb-1">Password</label>
                    <div class="relative">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 pointer-events-none">
                            <svg viewBox="0 0 24 24" width="18" height="18" stroke="#888" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
                            </svg>
                        </span>
                        <input type="password" id="password" name="password" placeholder="••••••••"
                            class="w-full pl-10 pr-10 py-2 border rounded focus:outline-none focus:ring-2 focus:ring-blue-400" required>
                        <span class="absolute inset-y-0 right-0 flex items-center pr-3 cursor-pointer" id="togglePassword" title="Tampilkan/Sembunyikan">
                            <svg viewBox="0 0 24 24" width="18" height="18" stroke="#888" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                                <circle cx="12" cy="12" r="3"></circle>
                            </svg>
                        </span>
                    </div>
                </div>

                <button type="submit" class="bg-blue-900 text-white w-full py-2 rounded hover:bg-blue-600 mb-3">Masuk</button>
            </form>
        </div>
    </div>
    <script src="../frontend/friontend_login.js"></script>
</body>
</html>
</body>

</html>