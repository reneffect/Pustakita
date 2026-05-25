<?php
session_start();
include 'database.php';

if (!isset($_SESSION['siswa'])) {
    header("Location: login.php");
    exit();
}

$id_siswa = $_SESSION['siswa'];

$pesan = '';

// Handle update form submission
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $email = mysqli_real_escape_string($koneksi, $_POST['email']);
    $kelas = mysqli_real_escape_string($koneksi, $_POST['kelas']);
    $jurusan = mysqli_real_escape_string($koneksi, $_POST['jurusan']);

    // Check if new username exists for another user
    $cek_username = mysqli_query($koneksi, "SELECT * FROM siswa WHERE username = '$username' AND id_siswa != $id_siswa");
    if (mysqli_num_rows($cek_username) > 0) {
        $pesan = '<div style="color: #dc2626; background: #fee2e2; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight: bold;">Username sudah digunakan. Silakan pilih username lain.</div>';
    } else {
        $update_query = "UPDATE siswa SET username = '$username', email = '$email', kelas = '$kelas', jurusan = '$jurusan' WHERE id_siswa = $id_siswa";
        if (mysqli_query($koneksi, $update_query)) {
            $_SESSION['username'] = $username; // update session
            $pesan = '<div style="color: #059669; background: #d1fae5; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight: bold;">Profil berhasil diperbarui.</div>';
        } else {
            $pesan = '<div style="color: #dc2626; background: #fee2e2; padding: 12px; border-radius: 8px; margin-bottom: 20px; text-align: center; font-weight: bold;">Gagal memperbarui profil: ' . mysqli_error($koneksi) . '</div>';
        }
    }
}

// Fetch current user data
$query = mysqli_query($koneksi, "SELECT * FROM siswa WHERE id_siswa = $id_siswa");
$user = mysqli_fetch_assoc($query);
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PustaKita - Edit Profile</title>
  <link rel="stylesheet" href="pustakita.css">
  <style>
    .edit-profile-section {
      max-width: 980px;
      margin: 40px auto 80px;
      padding: 0 40px;
    }
    .edit-profile-card {
      background: white;
      border-radius: 24px;
      box-shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
      padding: 36px;
    }
    .edit-profile-header {
      margin-bottom: 28px;
    }
    .edit-profile-header h1 {
      font-size: 30px;
      color: #111827;
      margin-bottom: 10px;
    }
    .edit-profile-header p {
      color: #475569;
      line-height: 1.8;
    }
    .profile-form {
      display: grid;
      gap: 22px;
    }
    .profile-form label {
      font-size: 13px;
      color: #475569;
      font-weight: 600;
      margin-bottom: 6px;
      display: inline-block;
    }
    .profile-form input,
    .profile-form textarea {
      width: 100%;
      border-radius: 16px;
      border: 1px solid #e2e8f0;
      background: #f8fafc;
      padding: 14px 16px;
      font-size: 14px;
      color: #0f172a;
      font-family: 'Poppins', sans-serif;
      outline: none;
    }
    .profile-form textarea {
      min-height: 120px;
      resize: vertical;
    }
    .form-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 20px;
    }
    .form-actions {
      display: flex;
      justify-content: flex-end;
      gap: 14px;
      flex-wrap: wrap;
      margin-top: 16px;
    }
    .btn-save {
      background: #4338ca;
      color: white;
      padding: 14px 24px;
      border-radius: 999px;
      border: none;
      font-weight: 700;
      cursor: pointer;
      transition: transform 0.2s, background 0.2s;
    }
    .btn-save:hover {
      background: #4f46e5;
      transform: translateY(-2px);
    }
    .btn-secondary {
      background: #f1f5f9;
      color: #334155;
      border: none;
      padding: 14px 24px;
      border-radius: 999px;
      cursor: pointer;
      text-decoration: none;
    }
    .edit-avatar-wrapper {
      display: flex;
      gap: 24px;
      align-items: center;
      margin-bottom: 26px;
      flex-wrap: wrap;
    }
    .avatar-circle {
      width: 96px;
      height: 96px;
      border-radius: 50%;
      background: linear-gradient(180deg, #dbeafe 0%, #bfdbfe 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 38px;
      font-weight: 800;
      color: #1e3a8a;
      box-shadow: 0 16px 30px rgba(15, 23, 42, 0.12);
      text-transform: uppercase;
    }
    .avatar-text {
      display: flex;
      flex-direction: column;
      gap: 6px;
    }
    .avatar-text strong {
      color: #111827;
      font-size: 18px;
    }
    .avatar-text span {
      color: #475569;
    }
    @media (max-width: 760px) {
      .form-grid {
        grid-template-columns: 1fr;
      }
    }
  </style>
</head>
<body>
<nav>
  <div class="nav-left">
    <div class="logo">
      <div class="logo-p">P</div>
      <div class="logo-text">usta<span>Kita</span></div>
    </div>
    <div class="nav-kategori">
      <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
      Kategori
    </div>
    <div class="nav-search">
      <svg width="16" height="16" fill="none" stroke="#a0aec0" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35" stroke-linecap="round"/></svg>
      <input type="text" placeholder="Cari Judul Buku atau Nama Penulis">
    </div>
  </div>
  <div class="nav-right">
    <div class="nav-links">
      <a href="home.php">Home</a>
      <a href="catalog.php">Catalog</a>
      <a href="favorit.php">Favorit</a>
      <a href="history.php">History</a>
      <a href="profile.php" class="active">Profil</a>
    </div>
    <div class="nav-cart">
      <a href="catalog.php">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
      </a>
    </div>
    <?php if (isset($_SESSION['siswa'])): ?>
      <span style="color:#111827;font-weight:600;font-size:14px;margin-right:15px;">👤 <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span>
      <a href="logout.php"><button class="btn-login">Logout</button></a>
    <?php else: ?>
      <a href="login.php"><button class="btn-masuk">Login</button></a>
    <?php endif; ?>
  </div>
</nav>

<div class="edit-profile-section">
  <div class="edit-profile-card">
    <div class="edit-profile-header">
      <h1>Edit Profile</h1>
      <p>Perbarui informasi profil Anda di sini. Setelah selesai, tekan Simpan untuk menyimpan perubahan.</p>
    </div>

    <?php echo $pesan; ?>

    <div class="edit-avatar-wrapper">
      <div class="avatar-circle"><?php echo strtoupper(substr($user['username'], 0, 2)); ?></div>
      <div class="avatar-text">
        <strong><?php echo htmlspecialchars($user['username']); ?></strong>
        <span>Anggota PustaKita</span>
      </div>
    </div>

    <form class="profile-form" action="" method="POST">
      <div class="form-grid">
        <div>
          <label for="username">Username</label>
          <input id="username" name="username" type="text" value="<?php echo htmlspecialchars($user['username']); ?>" required>
        </div>
        <div>
          <label for="email">Email</label>
          <input id="email" name="email" type="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
        </div>
      </div>
      <div class="form-grid">
        <div>
          <label for="kelas">Kelas</label>
          <input id="kelas" name="kelas" type="text" value="<?php echo htmlspecialchars($user['kelas'] ?? ''); ?>">
        </div>
        <div>
          <label for="jurusan">Jurusan</label>
          <input id="jurusan" name="jurusan" type="text" value="<?php echo htmlspecialchars($user['jurusan'] ?? ''); ?>">
        </div>
      </div>
      <div class="form-actions">
        <a class="btn-secondary" href="profile.php">Kembali</a>
        <button class="btn-save" type="submit">Simpan</button>
      </div>
    </form>
  </div>
</div>

<footer>
  <div class="footer-main">
    <div class="footer-col">
      <div class="footer-brand">
        <div class="footer-brand-title">Pustakita</div>
        <div class="footer-brand-tagline">Ilmu di Ujung Jari</div>
        <p>Nikmati kemudahan membaca dan meminjam buku secara online kapan saja dan di mana saja.</p>
      </div>
    </div>
    <div class="footer-col">
      <h4>Jenis Buku</h4>
      <ul>
        <li>Cerita Rakyat</li>
        <li>Buku paket</li>
        <li>Novel</li>
        <li>Kamus</li>
      </ul>
    </div>
    <div class="footer-col">
      <h4>Informasi Peminjaman</h4>
      <ul>
        <li>Aturan</li>
        <li>Peminjaman</li>
      </ul>
    </div>
    <div class="footer-logo">
      <div class="footer-logo-mark">
        <div class="footer-logo-p">P</div>
        <div class="footer-logo-text">PustaKita</div>
      </div>
    </div>
  </div>
  <div class="footer-bottom">
    © 2026 PustakaKita | Teman Membaca dan Sumber Ilmu
  </div>
</footer>
</body>
</html>
