<?php
session_start();
include 'database.php';

if (!isset($_SESSION['siswa'])) {
    header("Location: login.php");
    exit();
}

$id_siswa = $_SESSION['siswa'];
$username = $_SESSION['username'] ?? 'Siswa';
$id_buku = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id_buku == 0) {
    header("Location: catalog.php");
    exit();
}

// Handle Peminjaman
$pinjam_success = false;
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['pinjam_buku'])) {
    // Cek apakah sudah pinjam dan belum dikembalikan/ditolak
    $cek_pinjam = mysqli_query($koneksi, "SELECT * FROM peminjaman WHERE siswa_id = $id_siswa AND buku_id = $id_buku AND status IN ('menunggu', 'dipinjam')");
    if (mysqli_num_rows($cek_pinjam) == 0) {
        $tgl_pinjam = date('Y-m-d');
        $tgl_kembali = date('Y-m-d', strtotime('+7 days'));
        // id_admin diisi 1 sementara sebagai default jika perlu admin penanggung jawab
        $query_insert = "INSERT INTO peminjaman (id_admin, siswa_id, buku_id, tgl_pinjam, tgl_kembali, status) VALUES (1, $id_siswa, $id_buku, '$tgl_pinjam', '$tgl_kembali', 'menunggu')";
        if (mysqli_query($koneksi, $query_insert)) {
            $pinjam_success = true;
        }
    } else {
        $error_pinjam = "Anda sudah meminjam buku ini dan belum dikembalikan.";
    }
}

// Ambil data buku
$queryBuku = "SELECT b.*, k.nama_kategori FROM buku b LEFT JOIN kategori k ON b.kategori_id = k.id_kategori WHERE b.id_buku = $id_buku";
$resultBuku = mysqli_query($koneksi, $queryBuku);

if (mysqli_num_rows($resultBuku) == 0) {
    header("Location: catalog.php");
    exit();
}
$buku = mysqli_fetch_assoc($resultBuku);

// Determine gradient cover based on id
$gradients = [
    'linear-gradient(135deg,#1e3a5f,#2d6a9f)',
    'linear-gradient(135deg,#7f1d1d,#b91c1c)',
    'linear-gradient(135deg,#1e4d3e,#059669)',
    'linear-gradient(135deg,#78350f,#d97706)',
    'linear-gradient(135deg,#4a044e,#9333ea)',
    'linear-gradient(135deg,#0c4a6e,#0284c7)'
];
$grad = $gradients[$id_buku % count($gradients)];
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PustaKita - Deskripsi Buku</title>
  <link rel="stylesheet" href="pustakita.css">
  <style>
    .toast-popup {
      position: fixed;
      top: 16px;
      left: 50%;
      transform: translateX(-50%);
      z-index: 2000;
      min-width: calc(100% - 32px);
      max-width: 420px;
      background: rgba(15, 23, 42, 0.95);
      color: #f8fafc;
      border: 1px solid rgba(255, 255, 255, 0.14);
      border-radius: 16px;
      box-shadow: 0 28px 80px rgba(15, 23, 42, 0.35);
      padding: 18px 20px;
      display: none;
      gap: 10px;
      flex-direction: column;
      font-family: system-ui, sans-serif;
    }
    .toast-popup.show {
      display: flex;
      animation: slideDown 0.3s ease-out;
    }
    .toast-popup strong {
      font-size: 1rem;
      letter-spacing: 0.02em;
    }
    .toast-popup .toast-subtext {
      opacity: 0.88;
      font-size: 0.94rem;
    }
    .toast-popup .toast-name {
      margin-top: 8px;
      padding: 12px;
      border-radius: 12px;
      background: rgba(255, 255, 255, 0.08);
      border: 1px solid rgba(255, 255, 255, 0.1);
      font-size: 0.95rem;
    }
    @keyframes slideDown {
      from { opacity: 0; transform: translate(-50%, -20px); }
      to { opacity: 1; transform: translate(-50%, 0); }
    }
  </style>
</head>
<body>

<!-- NAVBAR -->
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
      <svg fill="none" stroke="#a0aec0" viewBox="0 0 24 24" width="16" height="16"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35" stroke-linecap="round"/></svg>
      <input type="text" placeholder="Cari Produk...Judul Buku atau Nama Penulis">
    </div>
  </div>
  <div class="nav-right">
    <div class="nav-links">
      <a href="home.php">Home</a>
      <a href="catalog.php" class="active">Catalog</a>
      <a href="history.php">History</a>
      <a href="profile.php">Profil</a>
    </div>
    <div class="nav-cart">
      <a href="catalog.php">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 016.364 0L12 7.636l1.318-1.318a4.5 4.5 0 016.364 6.364L12 21.382 4.318 12.682a4.5 4.5 0 010-6.364z"/></svg>
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

<!-- MAIN CONTENT -->
<div class="main">

  <!-- PAGE TITLE -->
  <div class="page-title-wrap">
    <div class="title-line"></div>
    <div class="page-title">Deskripsi Buku</div>
    <div class="title-line"></div>
  </div>

  <!-- BOOK DETAIL CARD -->
  <div class="book-detail-card">
    <div class="book-cover-wrap">
      <div class="book-cover-img" style="background:<?php echo $grad; ?>; color:white; display:flex; align-items:center; justify-content:center; padding:0; text-align:center; font-weight:bold; height:100%; border-radius: 12px; overflow: hidden;">
          <?php if (!empty($buku['foto'])): ?>
              <img src="<?php echo htmlspecialchars($buku['foto']); ?>" alt="Cover" style="width: 100%; height: 100%; object-fit: cover;">
          <?php else: ?>
              <span style="padding: 20px;"><?php echo htmlspecialchars($buku['judul']); ?></span>
          <?php endif; ?>
      </div>
    </div>

    <div class="book-divider"></div>

    <div class="book-info">
      <div>
        <div class="book-author-name"><?php echo htmlspecialchars($buku['penulis']); ?></div>
        <div class="book-title-name"><?php echo htmlspecialchars($buku['judul']); ?></div>
        <div class="book-desc-label">Deskripsi</div>
        <div class="book-desc-text">
          <?php echo nl2br(htmlspecialchars($buku['deskripsi'] ?: 'Tidak ada deskripsi untuk buku ini.')); ?>
        </div>
      </div>
      <div class="book-actions">
        <form method="POST" action="" style="display: flex; gap: 10px; width: 100%;">
            <button type="button" class="btn-favorit" style="flex: 1;">
              <div class="heart-icon">❤️</div>
              Tambahkan Favorit
            </button>
            <button type="submit" name="pinjam_buku" class="btn-pinjam-action" style="flex: 1;">Pinjam</button>
        </form>
      </div>
      <?php if (isset($error_pinjam)): ?>
        <div style="color: #dc2626; margin-top: 15px; font-size: 14px; font-weight: 600; text-align: center; background: #fee2e2; padding: 10px; border-radius: 8px;">
            <?php echo $error_pinjam; ?>
        </div>
      <?php endif; ?>
    </div>
  </div>

  <!-- REKOMENDASI -->
  <div class="section-title">Rekomendasi</div>
  <div class="rekomendasi-wrapper">
    <div class="rekomendasi-left">
      <div class="rekomendasi-badge">RECOMMENDED</div>
      <div class="rekomendasi-title">BUKU<br>TERLARIS</div>
      <div class="rekomendasi-mascot">🌟</div>
    </div>
    <div class="rekomendasi-books">
      <div class="book-card">
        <div class="book-cover" style="background:linear-gradient(135deg,#1e3a5f,#2d6a9f);">
          <div class="book-badge">BEST SELLER</div>
          <div style="font-size:28px;margin-top:10px;">🏔️</div>
          <div class="book-title-card">3726</div>
        </div>
        <div class="book-meta">
          <div class="book-author">Nurwita Sari</div>
          <div class="book-name">3726 MDPL</div>
        </div>
      </div>
      <div class="book-card">
        <div class="book-cover" style="background:linear-gradient(135deg,#8b4513,#d2691e);">
          <div class="book-badge">BEST SELLER</div>
          <div style="font-size:28px;margin-top:10px;">🏠</div>
          <div class="book-title-card">Pulang</div>
        </div>
        <div class="book-meta">
          <div class="book-author">Tere liye</div>
          <div class="book-name">Pulang</div>
        </div>
      </div>
      <div class="book-card">
        <div class="book-cover" style="background:linear-gradient(135deg,#1a472a,#2d6a4f);">
          <div class="book-badge">BEST SELLER</div>
          <div style="font-size:28px;margin-top:10px;">🌧️</div>
          <div class="book-title-card">Bandung After Rain</div>
        </div>
        <div class="book-meta">
          <div class="book-author">Ndji</div>
          <div class="book-name">Bandung After Rain</div>
        </div>
      </div>
      <div class="book-card">
        <div class="book-cover" style="background:linear-gradient(135deg,#7c3aed,#db2777,#f97316);">
          <div class="book-badge">BEST SELLER</div>
          <div style="font-size:28px;margin-top:10px;">🌈</div>
          <div class="book-title-card">Laskar Pelangi</div>
        </div>
        <div class="book-meta">
          <div class="book-author">Andrea Hirata</div>
          <div class="book-name">Laskar Pelangi</div>
        </div>
      </div>
      <div class="book-card">
        <div class="book-cover" style="background:linear-gradient(135deg,#0f172a,#1e40af);">
          <div class="book-badge">BEST SELLER</div>
          <div style="font-size:28px;margin-top:10px;">💙</div>
          <div class="book-title-card">Dilan</div>
        </div>
        <div class="book-meta">
          <div class="book-author">Pidi Baiq</div>
          <div class="book-name">Dilan Milea 1990</div>
        </div>
      </div>
    </div>
  </div>

</div>

<!-- FOOTER -->
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

<script>
  document.addEventListener('DOMContentLoaded', function() {
    var toast = document.createElement('div');
    toast.className = 'toast-popup';
    toast.innerHTML = '<strong>Selamat Peminjaman Anda, Berhasil</strong>' +
                      '<div class="toast-subtext">Peminjam Atas Nama :</div>' +
                      '<div class="toast-name"><?php echo htmlspecialchars($username); ?></div>';
    document.body.appendChild(toast);

    function showToast() {
      toast.classList.add('show');
      setTimeout(function() {
        toast.classList.remove('show');
        window.location.href = 'history.php'; // redirect to history after success
      }, 3000);
    }

    <?php if ($pinjam_success): ?>
    showToast();
    <?php endif; ?>
  });
</script>
</body>
</html>