<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>PustaKita - Teman Membaca dan Sumber Ilmu</title>
  <link rel="stylesheet" href="pustakita.css">
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
      <svg width="16" height="16" fill="none" stroke="#a0aec0" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35" stroke-linecap="round"/></svg>
      <input type="text" placeholder="Cari Produk...Judul Buku atau Nama Penulis">
    </div>
  </div>
  <div class="nav-right">
    <div class="nav-links">
      <a href="home.php" class="active">Home</a>
      <a href="catalog.php">Catalog</a>
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

<!-- ALERT BANNER -->
<div class="alert-banner">
  <div class="alert-icon">
    <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.962-.833-2.732 0L3.072 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
  </div>
  <div class="alert-text">
    Ayo Membaca, langkah kecil hari ini untuk masa depan besar<br>
    yang penuh prestasi.
  </div>
</div>

<!-- HERO SECTION -->
<div class="hero-section">
  <div class="hero-main">
    <div class="hero-bg-img"></div>
    <!-- Decorative school building silhouette -->
    <svg style="position:absolute;right:0;top:0;bottom:0;height:100%;opacity:0.15" viewBox="0 0 300 220" fill="white">
      <rect x="50" y="80" width="200" height="140" rx="4"/>
      <rect x="80" y="60" width="140" height="30" rx="2"/>
      <rect x="100" y="40" width="100" height="25" rx="2"/>
      <rect x="130" y="20" width="40" height="25" rx="2"/>
      <rect x="70" y="100" width="35" height="50" rx="2"/>
      <rect x="120" y="110" width="60" height="110" rx="2"/>
      <rect x="195" y="100" width="35" height="50" rx="2"/>
    </svg>
    <div class="hero-content">
      <h1>WELCOME<br>TO<br>PUSTAKITA</h1>
      <div class="hero-school">SMK NEGERI 6 MALANG</div>
      <div class="hero-badge">SMKN 6 MALANG</div>
    </div>
  </div>
  <div class="hero-right">
    <div class="hero-card hero-card-1">
      <div class="hero-card-text">TEMUKAN DUNIA DALAM SETIAP HALAMAN</div>
      <div class="hero-card-logo">PustaKita</div>
    </div>
    <div class="hero-card hero-card-2">
      <div class="hero-card-text">Ayo membaca, karena ilmu adalah kunci menuju kesuksesan</div>
      <div class="hero-card-logo" style="color:#059669">PustaKita</div>
    </div>
  </div>
</div>

<!-- KATEGORI JENIS BUKU -->
<div class="section">
  <div class="section-title">Kategori Jenis – Jenis Buku</div>
  <div class="kategori-grid">
    <div class="kategori-card">
      <div class="kategori-bg cat-cerita" style="display:flex;align-items:center;justify-content:center;">
        <div style="font-size:50px;">📚</div>
      </div>
      <div class="kategori-label">Cerita Rakyat</div>
    </div>
    <div class="kategori-card">
      <div class="kategori-bg cat-pelajaran" style="display:flex;align-items:center;justify-content:center;">
        <div style="font-size:50px;">📖</div>
      </div>
      <div class="kategori-label">Buku Paket</div>
    </div>
    <div class="kategori-card">
      <div class="kategori-bg cat-novel" style="display:flex;align-items:center;justify-content:center;">
        <div style="font-size:50px;">📕</div>
      </div>
      <div class="kategori-label">Novel</div>
    </div>
    <div class="kategori-card">
      <div class="kategori-bg cat-kamus" style="display:flex;align-items:center;justify-content:center;">
        <div style="font-size:50px;">📘</div>
      </div>
      <div class="kategori-label">Kamus</div>
    </div>
  </div>
</div>

<!-- REKOMENDASI -->
<div class="section">
  <div class="section-title">Rekomendasi</div>
  <div class="rekomendasi-wrapper">
    <div class="rekomendasi-left">
      <div class="rekomendasi-badge">RECOMMENDED</div>
      <div class="rekomendasi-title">BUKU<br>TERLARIS</div>
      <div class="rekomendasi-mascot">🌟</div>
    </div>
    <div class="rekomendasi-books">
      <!-- Book 1 -->
      <div class="book-card">
        <div class="book-cover" style="background:linear-gradient(135deg,#1e3a5f,#2d6a9f);">
          <div class="book-badge">BEST SELLER</div>
          <div class="book-cover-inner">
              <img src="images/3726-mdpl.jpg" alt="3726 MDPL cover">
          </div>
        </div>
        <div class="book-meta">
          <div class="book-author" style="color:#a78bfa;font-size:9px;">Nuannisa Sar</div>
          <div class="book-name" style="color:white;font-size:10px;font-weight:700;">5726 MDPL</div>
        </div>
      </div>
      <!-- Book 2 -->
      <div class="book-card">
        <div class="book-cover" style="background:linear-gradient(135deg,#7f1d1d,#b91c1c);">
          <div class="book-badge">BEST SELLER</div>
          <div class="book-cover-inner">
              <img src="images/pulang.jpg" alt="Pulang cover">
          </div>
        </div>
        <div class="book-meta">
          <div class="book-author" style="color:#a78bfa;font-size:9px;">Tere Liye</div>
          <div class="book-name" style="color:white;font-size:10px;font-weight:700;">Pulang</div>
        </div>
      </div>
      <!-- Book 3 -->
      <div class="book-card">
        <div class="book-cover" style="background:linear-gradient(135deg,#1e4d3e,#059669);">
          <div class="book-badge">BEST SELLER</div>
          <div class="book-cover-inner">
              <img src="images/bandung-after-rain.jpg" alt="Bandung After Rain cover">
          </div>
        </div>
        <div class="book-meta">
          <div class="book-author" style="color:#a78bfa;font-size:9px;">Rully</div>
          <div class="book-name" style="color:white;font-size:10px;font-weight:700;">Bandung After Rain</div>
        </div>
      </div>
      <!-- Book 4 -->
      <div class="book-card">
        <div class="book-cover" style="background:linear-gradient(135deg,#78350f,#d97706);">
          <div class="book-badge">BEST SELLER</div>
          <div class="book-cover-inner">
              <img src="images/laskar-pelangi.jpg" alt="Laskar Pelangi cover">
          </div>
        </div>
        <div class="book-meta">
          <div class="book-author" style="color:#a78bfa;font-size:9px;">Andrea Hirata</div>
          <div class="book-name" style="color:white;font-size:10px;font-weight:700;">Laskar Pelangi</div>
        </div>
      </div>
      <!-- Book 5 -->
      <div class="book-card">
        <div class="book-cover" style="background:linear-gradient(135deg,#4a044e,#9333ea);">
          <div class="book-badge">BEST SELLER</div>
          <div class="book-cover-inner">
              <img src="images/dilan-milea-1990.jpg" alt="Dilan Milea 1990 cover">
          </div>
        </div>
        <div class="book-meta">
          <div class="book-author" style="color:#a78bfa;font-size:9px;">Pidi Baiq</div>
          <div class="book-name" style="color:white;font-size:10px;font-weight:700;">Dilan Milea 1990</div>
        </div>
      </div>
    </div>
  </div>
</div>

<!-- BUKU FAVORIT -->
<div class="section">
  <div class="section-title">Buku Favorit</div>
  <div class="favorit-wrapper">
    <div class="favorit-left">
      <div class="favorit-title">BUKU<br>FAVORIT<br>ANDA !!</div>
      <div class="favorit-mascot">🌻</div>
    </div>
    <div class="favorit-books">
      <div class="favorit-book-card">
        <div class="favorit-book-cover" style="background:linear-gradient(135deg,#1e3a5f,#2d6a9f);">
          <img src="images/5-cm.jpg" alt="5 cm cover">
        </div>
        <div class="favorit-book-title">Nuannisa Sar</div>
        <div style="font-size:10px;font-weight:700;color:#1a202c;text-align:center;">5726 MDPL</div>
      </div>
      <div class="favorit-book-card">
        <div class="favorit-book-cover" style="background:linear-gradient(135deg,#7f1d1d,#b91c1c);">
          <img src="images/pulang.jpg" alt="Pulang cover">
        </div>
        <div class="favorit-book-title">Tere Liye</div>
        <div style="font-size:10px;font-weight:700;color:#1a202c;text-align:center;">Pulang</div>
      </div>
      <div class="favorit-book-card">
        <div class="favorit-book-cover" style="background:linear-gradient(135deg,#1e4d3e,#059669);">
          <img src="images/bandung-after-rain.jpg" alt="Bandung After Rain cover">
        </div>
        <div class="favorit-book-title">Rully</div>
        <div style="font-size:10px;font-weight:700;color:#1a202c;text-align:center;">Bandung After Rain</div>
      </div>
      <div class="favorit-book-card">
        <div class="favorit-book-cover" style="background:linear-gradient(135deg,#78350f,#d97706);">
          <img src="images/laskar-pelangi.jpg" alt="Laskar Pelangi cover">
        </div>
        <div class="favorit-book-title">Andrea Hirata</div>
        <div style="font-size:10px;font-weight:700;color:#1a202c;text-align:center;">Laskar Pelangi</div>
      </div>
      <div class="favorit-book-card">
        <div class="favorit-book-cover" style="background:linear-gradient(135deg,#4a044e,#9333ea);">
          <img src="images/dilan-milea-1990.jpg" alt="Dilan Milea 1990 cover">
        </div>
        <div class="favorit-book-title">Pidi Baiq</div>
        <div style="font-size:10px;font-weight:700;color:#1a202c;text-align:center;">Dilan Milea 1990</div>
      </div>
      <div class="favorit-book-card">
        <div class="favorit-book-cover" style="background:linear-gradient(135deg,#0c4a6e,#0284c7);">
          <img src="images/perahu-kertas.jpg" alt="Perahu Kertas cover">
        </div>
        <div class="favorit-book-title">Dee Lestari</div>
        <div style="font-size:10px;font-weight:700;color:#1a202c;text-align:center;">Perahu Kertas</div>
      </div>
    </div>
  </div>
  <div class="btn-lihat"><a href="#">Lihat Selengkapnya</a></div>
</div>

<!-- TENTANG KAMI -->
<div class="tentang-section">
  <div class="tentang-inner">
    <div class="tentang-title">Tentang Kami</div>
    <div class="tentang-grid">
      <div class="tentang-text">
        <p>Perpustakaan kami menyediakan layanan pinjam meminjam buku untuk membantu kebutuhan belajar dan menambah wawasan para pengguna. Dengan koleksi buku yang beragam dan suasana yang nyaman, kami berupaya menjadi tempat yang mendukung kegiatan membaca dan belajar secara efektif.</p>
      </div>
      <div class="tentang-img-placeholder">
        <div style="font-size:100px;">📚</div>
      </div>
    </div>

    <div style="display:grid;grid-template-columns:200px 1fr;gap:40px;align-items:start;margin-top:30px;">
      <div>
        <!-- Stack of books -->
        <div style="display:flex;flex-direction:column;gap:0;align-items:center;">
          <div style="width:140px;height:22px;background:#e53e3e;border-radius:4px;margin-bottom:-2px;box-shadow:0 3px 6px rgba(0,0,0,0.15);"></div>
          <div style="width:140px;height:22px;background:#3182ce;border-radius:4px;margin-bottom:-2px;box-shadow:0 3px 6px rgba(0,0,0,0.15);"></div>
          <div style="width:140px;height:22px;background:#38a169;border-radius:4px;margin-bottom:-2px;box-shadow:0 3px 6px rgba(0,0,0,0.15);"></div>
          <div style="width:140px;height:22px;background:#f6ad55;border-radius:4px;margin-bottom:-2px;box-shadow:0 3px 6px rgba(0,0,0,0.15);"></div>
          <div style="width:140px;height:22px;background:#9f7aea;border-radius:4px;margin-bottom:-2px;box-shadow:0 3px 6px rgba(0,0,0,0.15);"></div>
          <div style="width:140px;height:22px;background:#ed8936;border-radius:4px;margin-bottom:-2px;box-shadow:0 3px 6px rgba(0,0,0,0.15);"></div>
          <div style="width:140px;height:22px;background:#48bb78;border-radius:4px;margin-bottom:-2px;box-shadow:0 3px 6px rgba(0,0,0,0.15);"></div>
          <div style="width:140px;height:22px;background:#4299e1;border-radius:4px;margin-bottom:-2px;box-shadow:0 3px 6px rgba(0,0,0,0.15);"></div>
          <div style="width:150px;height:16px;background:#8b5e3c;border-radius:0 0 6px 6px;box-shadow:0 4px 10px rgba(0,0,0,0.2);"></div>
        </div>
      </div>
      <div class="aturan-text">
        <h3>Aturan Peminjaman</h3>
        <p>Setiap pengunjung diperbolehkan meminjam buku sesuai dengan aturan yang berlaku. Buku yang dipinjam wajib dikembalikan tepat waktu sesuai dengan batas peminjaman yang telah ditentukan.</p>
        <p>Apabila terjadi keterlambatan dalam pengembalian buku, maka akan dikenakan sanksi berupa denda sebagai berikut:</p>
        <ul>
          <li>Keterlambatan 1 hari: Rp5.000</li>
          <li>Keterlambatan 2 hari: Rp10.000</li>
          <li>Keterlambatan 3 hari: Rp15.000</li>
          <li>Dan seterusnya akan bertambah Rp5.000 setiap harinya</li>
        </ul>
        <p>Peraturan ini dibuat untuk menjaga kedisiplinan serta memberikan kesempatan kepada pengunjung lain agar dapat memanfaatkan koleksi buku yang tersedia.</p>
        <p>Mari bersama-sama menjaga dan memanfaatkan fasilitas perpustakaan dengan baik demi terciptanya lingkungan belajar yang nyaman dan tertib.</p>
      </div>
    </div>
  </div>
</div>

<!-- CTA SECTION -->
<div class="cta-section">
  <div class="cta-inner">
    <div class="cta-brand">
      <div style="display:flex;align-items:center;gap:12px;">
        <div style="font-size:60px;">👧</div>
        <div>
          <div class="cta-brand-logo">
            <div class="p-box">P</div>
            <span style="font-size:20px;color:var(--blue);font-weight:900;">ustaKita</span>
          </div>
          <div class="cta-brand-tagline">Ilmu di Ujung Jari</div>
        </div>
      </div>
      <div class="cta-brand-desc">Nikmati kemudahan membaca dan meminjam buku secara online kapan saja dan di mana saja.</div>
    </div>
    <div class="cta-right">
      <div class="cta-card-pinjam">
        <div>
          <h3>AYO PINJAM BUKU<br>SEKARANG JUGA</h3>
          <div class="btn-pinjam">
            PINJAM →
          </div>
        </div>
        <div style="font-size:55px;">👨‍💻</div>
      </div>
      <div class="cta-card-kembali">
        <h3>KEMBALIKAN TEPAT<br>WAKTU AGAR<br>TERHINDAR<br>DARI DENDA.</h3>
        <div style="font-size:50px;">⏰</div>
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

</body>
</html>