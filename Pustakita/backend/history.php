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
      <a href="history.php" class="active">History</a>
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

<div class="history-page">
  <div class="history-title">History Peminjaman</div>
  <div class="history-line"></div>
  <div class="history-section">
    <div class="history-header">
      <h1>Riwayat Peminjaman</h1>
      <div class="history-line"></div>
    </div>
    <div class="history-card">
      <div class="history-card-left">
        <img src="images/timun-mas.jpg" alt="Timun Mas cover">
      </div>
      <div class="history-card-right">
        <h2>Detail Peminjaman</h2>
        <div class="history-item"><strong>Judul Buku :</strong> Timun Mas</div>
        <div class="history-item"><strong>Tanggal Pinjam :</strong> 1 April 2026</div>
        <div class="history-item"><strong>Tanggal Kembali :</strong> 5 April 2026</div>
        <div class="history-status"><strong>Status :</strong> Selesai</div>
      </div>
    </div>
    <div class="history-footer">
      <a href="#" class="history-page-number active">1</a>
      <a href="#" class="history-page-number">2</a>
      <a href="#" class="history-page-number">3</a>
    </div>
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
