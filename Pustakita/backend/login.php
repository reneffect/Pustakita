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
    $stmt = $this->db->prepare("SELECT id_siswa, username, password FROM siswa WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
      $row = $result->fetch_assoc();
      if (password_verify($password, $row['password']) || $password === $row['password']) {
        $_SESSION['siswa'] = $row['id_siswa'];
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
  <link rel="stylesheet" href="style.css">
  <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700&display=swap" rel="stylesheet">
</head>

<body>
  <div class="login-card">
    <!-- Header / Logo Area -->
    <div class="header">
      <h1 class="logo">
        <!-- Tambahan ikon buku kecil di sebelah logo -->
        <svg viewBox="0 0 24 24" width="32" height="32" stroke="#04005c" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round" style="margin-right: 8px;">
          <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
          <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
        </svg>
        <span class="logo-p">P</span>ustaKita
      </h1>
      <p class="subtitle">Perpustakaan SMKN 6 Malang</p>
    </div>

    <hr class="divider">

    <!-- Form Area -->
    <div class="form-section">
      <h2>Masuk ke Akun Anda</h2>

      <?php if (!empty($error_message)): ?>
        <div class="error-message">
          <p><?php echo $error_message; ?></p>
        </div>
      <?php endif; ?>

      <form action="" method="POST" id="loginForm">

        <div class="input-group">
          <label for="username">Username / Email</label>
          <div class="input-wrapper">
            <!-- Ikon User -->
            <span class="input-icon left-icon">
              <svg viewBox="0 0 24 24" width="18" height="18" stroke="#888" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"></path>
                <circle cx="12" cy="7" r="4"></circle>
              </svg>
            </span>
            <input type="text" id="username" name="username" placeholder="Masukkan username" required>
          </div>
        </div>

        <div class="input-group">
          <label for="password">Password</label>
          <div class="input-wrapper">
            <!-- Ikon Gembok -->
            <span class="input-icon left-icon">
              <svg viewBox="0 0 24 24" width="18" height="18" stroke="#888" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <rect x="3" y="11" width="18" height="11" rx="2" ry="2"></rect>
                <path d="M7 11V7a5 5 0 0 1 10 0v4"></path>
              </svg>
            </span>

            <input type="password" id="password" name="password" placeholder="••••••••" required>

            <!-- Ikon Mata (Toggle) -->
            <span class="input-icon right-icon toggle-password" id="togglePassword" title="Tampilkan/Sembunyikan">
              <svg viewBox="0 0 24 24" width="18" height="18" stroke="#888" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"></path>
                <circle cx="12" cy="12" r="3"></circle>
              </svg>
            </span>
          </div>
        </div>

        <button type="submit" class="btn-login">Masuk</button>
      </form>

      
  </div>
  <script src="script.js"></script>
</body>

</html>