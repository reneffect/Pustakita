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
    <button class="btn-masuk">Login</button>
    <button class="btn-login">Logout</button>
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
      <div class="book-cover-img">
          <img src="images.jpg" alt="Laskar Pelangi cover">
        </div>
    </div>

    <div class="book-divider"></div>

    <div class="book-info">
      <div>
        <div class="book-author-name">Andrea Hirata</div>
        <div class="book-title-name">Laskar Pelangi</div>
        <div class="book-desc-label">Deskripsi</div>
        <div class="book-desc-text">
          Novel karya Andrea Hirata ini menceritakan perjuangan sekelompok anak dari keluarga sederhana di Belitung dalam meraih pendidikan. Dengan segala keterbatasan, mereka tetap semangat belajar berkat bimbingan guru yang penuh dedikasi. Kisah ini mengajarkan tentang harapan, persahabatan, dan pentingnya pendidikan untuk mengubah masa depan.
        </div>
      </div>
      <div class="book-actions">
        <button class="btn-favorit">
          <div class="heart-icon">❤️</div>
          Tambahkan Favorit
        </button>
        <button class="btn-pinjam-action">Pinjam</button>
      </div>
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
    var pinjamBtn = document.querySelector('.btn-pinjam-action');
    var toast = document.createElement('div');
    toast.className = 'toast-popup';
    toast.innerHTML = '<strong>Selamat Peminjaman Anda, Berhasil</strong>' +
                      '<div class="toast-subtext">Peminjam Atas Nama :</div>' +
                      '<div class="toast-name">-</div>';
    document.body.appendChild(toast);

    function showToast() {
      toast.classList.add('show');
      clearTimeout(window.toastTimeout);
      window.toastTimeout = setTimeout(function() {
        toast.classList.remove('show');
      }, 4000);
    }

    if (pinjamBtn) {
      pinjamBtn.addEventListener('click', function() {
        showToast();
      });
    }
  });
</script>
</body>
</html>