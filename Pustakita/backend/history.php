<?php
session_start();
include 'database.php';

if (!isset($_SESSION['siswa'])) {
  header("Location: login.php");
  exit();
}

$id_siswa = $_SESSION['siswa'];
$searchQuery = "";

if (isset($_GET['q'])) {
  $searchQuery = mysqli_real_escape_string($koneksi, $_GET['q']);
  $queryHistory = "SELECT p.*, b.judul, b.penulis, b.foto FROM detail_peminjaman p 
                     JOIN buku b ON p.buku_id = b.id_buku 
                     WHERE p.id_siswa = $id_siswa AND (b.judul LIKE '%$searchQuery%' OR b.penulis LIKE '%$searchQuery%')
                     ORDER BY p.tgl_pinjam DESC";
} else {
  $queryHistory = "SELECT p.*, b.judul, b.penulis, b.cover FROM detail_peminjaman p 
                     JOIN buku b ON p.buku_id = b.id_buku 
                     WHERE p.id_siswa = $id_siswa
                     ORDER BY p.tgl_pinjam DESC";
}
$resultHistory = mysqli_query($koneksi, $queryHistory);
?>
<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PustaKita - History Peminjaman</title>
  <link rel="stylesheet" href="pustakita.css">
  <style>
    .history-page {
      max-width: 1200px;
      margin: 40px auto 80px;
      padding: 0 40px;
    }

    .history-title {
      text-align: center;
      color: #475569;
      font-size: 18px;
      font-weight: 700;
      letter-spacing: 0.6px;
      margin-bottom: 18px;
    }

    .history-line {
      width: 100%;
      height: 1px;
      background: #d1d5db;
      margin: 14px auto;
    }

    .history-section {
      background: white;
      border-radius: 24px;
      padding: 28px;
      box-shadow: 0 20px 40px rgba(15, 23, 42, 0.08);
      display: grid;
      gap: 28px;
    }

    .history-header {
      display: flex;
      flex-direction: column;
      align-items: center;
      gap: 10px;
    }

    .history-header h1 {
      font-size: 24px;
      color: #111827;
      margin: 0;
    }

    .history-card {
      display: flex;
      gap: 28px;
      align-items: center;
      justify-content: space-between;
      flex-wrap: wrap;
    }

    .history-card-left {
      min-width: 180px;
      width: 180px;
      border-radius: 18px;
      overflow: hidden;
      background: #f8fafc;
      box-shadow: 0 16px 35px rgba(15, 23, 42, 0.08);
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 14px;
    }

    .history-card-left img {
      width: 100%;
      height: auto;
      display: block;
      object-fit: cover;
    }

    .history-card-right {
      flex: 1;
      min-width: 260px;
      background: #fef2f2;
      border-radius: 22px;
      padding: 28px;
      box-shadow: inset 0 0 0 1px rgba(226, 232, 240, 0.8);
    }

    .history-card-right h2 {
      font-size: 18px;
      color: #111827;
      margin-bottom: 18px;
    }

    .history-item {
      margin-bottom: 12px;
      color: #334155;
      font-size: 14px;
      line-height: 1.8;
    }

    .history-item strong {
      color: #1f2937;
      display: inline-block;
      width: 132px;
    }

    .history-status {
      margin-top: 20px;
      font-size: 14px;
      font-weight: 700;
      color: #1f2937;
    }

    .history-footer {
      display: flex;
      justify-content: center;
      gap: 10px;
      padding-top: 10px;
    }

    .history-page-number {
      width: 34px;
      height: 34px;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      border-radius: 10px;
      background: #e2e8f0;
      color: #475569;
      font-weight: 700;
      text-decoration: none;
    }

    .history-page-number.active {
      background: #4338ca;
      color: white;
    }

    @media (max-width: 860px) {
      .history-card {
        flex-direction: column;
      }

      .history-card-left {
        width: 100%;
      }

      .history-card-right {
        width: 100%;
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
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
        </svg>
        Kategori
      </div>
      <form action="history.php" method="GET" class="nav-search">
        <svg width="16" height="16" fill="none" stroke="#a0aec0" viewBox="0 0 24 24">
          <circle cx="11" cy="11" r="8" />
          <path d="M21 21l-4.35-4.35" stroke-linecap="round" />
        </svg>
        <input type="text" name="q" placeholder="Cari Riwayat Peminjaman..." value="<?php echo htmlspecialchars($searchQuery); ?>" style="border:none; outline:none; background:transparent; width:100%;">
      </form>
    </div>
    <div class="nav-right">
      <div class="nav-links">
        <a href="home.php">Home</a>
        <a href="catalog.php">Catalog</a>
        <a href="favorit.php">Favorit</a>
        <a href="history.php" class="active">History</a>
        <a href="profile.php">Profil</a>
      </div>
      <div class="nav-cart">
        <a href="catalog.php">
          <svg fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 016.364 0L12 7.636l1.318-1.318a4.5 4.5 0 016.364 6.364L12 21.382 4.318 12.682a4.5 4.5 0 010-6.364z" />
          </svg>
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

  <div class="history-page">
    <div class="history-title">History Peminjaman</div>
    <div class="history-line"></div>
    <div class="history-section">
      <div class="history-header">
        <h1>Riwayat Peminjaman</h1>
        <div class="history-line"></div>
      </div>
      <?php
      $gradients = [
        'linear-gradient(135deg,#1e3a5f,#2d6a9f)',
        'linear-gradient(135deg,#7f1d1d,#b91c1c)',
        'linear-gradient(135deg,#1e4d3e,#059669)',
        'linear-gradient(135deg,#78350f,#d97706)',
        'linear-gradient(135deg,#4a044e,#9333ea)',
        'linear-gradient(135deg,#0c4a6e,#0284c7)'
      ];
      $j = 0;
      if (mysqli_num_rows($resultHistory) > 0) {
        while ($row = mysqli_fetch_assoc($resultHistory)) {
          $grad = $gradients[$j % count($gradients)];
          $status = $row['status'];
          $status_color = '#1f2937';
          if ($status == 'dipinjam') $status_color = '#d97706';
          else if ($status == 'dikembalikan') $status_color = '#059669';
          else if ($status == 'ditolak') $status_color = '#b91c1c';
          else if ($status == 'menunggu') $status_color = '#2d6a9f';

          // Format dates
          $tgl_pinjam = date('d F Y', strtotime($row['tgl_pinjam']));
          $tgl_pengembalian = $row['tgl_pengembalian'] ? date('d F Y', strtotime($row['tgl_pengembalian'])) : '-';

          echo '<div class="history-card" style="margin-bottom: 20px;">';
          echo '  <div class="history-card-left" style="background:' . $grad . '; color:white; font-size:14px; font-weight:bold; text-align:center; padding:0; border-radius:18px; min-height:180px; overflow: hidden; display: flex; align-items: center; justify-content: center;">';
          if (!empty($row['foto'])) {
            echo '    <img src="' . htmlspecialchars($row['foto']) . '" alt="Cover" style="width: 100%; height: 100%; object-fit: cover;">';
          } else {
            echo '    <span style="padding: 20px;">' . htmlspecialchars($row['judul']) . '</span>';
          }
          echo '  </div>';
          echo '  <div class="history-card-right">';
          echo '    <h2>Detail Peminjaman</h2>';
          echo '    <div class="history-item"><strong>Judul Buku :</strong> ' . htmlspecialchars($row['judul']) . '</div>';
          echo '    <div class="history-item"><strong>Tanggal Pinjam :</strong> ' . $tgl_pinjam . '</div>';
          echo '    <div class="history-item"><strong>Tanggal Kembali :</strong> ' . $tgl_pengembalian . '</div>';
          echo '    <div class="history-status"><strong>Status :</strong> <span style="color:' . $status_color . ';">' . ucfirst($status) . '</span></div>';
          echo '  </div>';
          echo '</div>';
          $j++;
        }
      } else {
        echo "<p style='text-align:center; color:#666;'>Belum ada riwayat peminjaman.</p>";
      }
      ?>
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