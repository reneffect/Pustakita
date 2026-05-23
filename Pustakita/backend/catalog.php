<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PustaKita - Catalog</title>
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
      <input type="text" placeholder="Cari Judul Buku atau Nama Penulis">
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

<main class="catalog-main">
  <div class="catalog-page-header">
    <div class="catalog-title-line"></div>
    <div class="catalog-page-title">Catalog</div>
    <div class="catalog-title-line"></div>
  </div>

  <section class="section catalog-section">
    <div class="catalog-grid">
      <article class="catalog-card">
        <div class="catalog-card-ribbon">BEST SELLER</div>
        <div class="catalog-card-cover">
          <div class="catalog-cover-thumb">
            <img src="images.jpg" alt="Laskar Pelangi cover">
          </div>
        </div>
        <div class="catalog-card-body">
          <div class="catalog-book-author">Andrea Hirata</div>
          <div class="catalog-book-name">Laskar Pelangi</div>
          <div class="catalog-status available">Tersedia</div>
          <a href="peminjaman.html" class="btn-detail">Lihat Detail</a>
        </div>
      </article>

      <article class="catalog-card">
        <div class="catalog-card-cover">
          <div class="catalog-cover-thumb">
            <img src="images/ayahku-bukan-pembohong.jpg" alt="Ayahku Bukan Pembohong cover">
          </div>
        </div>
        <div class="catalog-card-body">
          <div class="catalog-book-author">Tere Liye</div>
          <div class="catalog-book-name">Ayahku Bukan Pembohong</div>
          <div class="catalog-status unavailable">Tidak Tersedia</div>
          <a href="#" class="btn-detail">Lihat Detail</a>
        </div>
      </article>

      <article class="catalog-card">
        <div class="catalog-card-cover">
          <div class="catalog-cover-thumb">
            <img src="images/perahu-kertas.jpg" alt="Perahu Kertas cover">
          </div>
        </div>
        <div class="catalog-card-body">
          <div class="catalog-book-author">Dee Lestari</div>
          <div class="catalog-book-name">Perahu Kertas</div>
          <div class="catalog-status available">Tersedia</div>
          <a href="#" class="btn-detail">Lihat Detail</a>
        </div>
      </article>

      <article class="catalog-card">
        <div class="catalog-card-ribbon">BEST SELLER</div>
        <div class="catalog-card-cover">
          <div class="catalog-cover-thumb">
            <img src="images/pulang.jpg" alt="Pulang cover">
          </div>
        </div>
        <div class="catalog-card-body">
          <div class="catalog-book-author">Tere Liye</div>
          <div class="catalog-book-name">Pulang</div>
          <div class="catalog-status available">Tersedia</div>
          <a href="#" class="btn-detail">Lihat Detail</a>
        </div>
      </article>

      <article class="catalog-card">
        <div class="catalog-card-ribbon">BEST SELLER</div>
        <div class="catalog-card-cover">
          <div class="catalog-cover-thumb">
            <img src="dilan.jpg" alt="Dilan Milea 1990 cover">
          </div>
        </div>
        <div class="catalog-card-body">
          <div class="catalog-book-author">Pidi Baiq</div>
          <div class="catalog-book-name">Dilan Milea 1990</div>
          <div class="catalog-status available">Tersedia</div>
          <a href="#" class="btn-detail">Lihat Detail</a>
        </div>
      </article>

      <article class="catalog-card">
        <div class="catalog-card-cover">
          <div class="catalog-cover-thumb">
            <img src="images/bandung-after-rain.jpg" alt="Bandung After Rain cover">
          </div>
        </div>
        <div class="catalog-card-body">
          <div class="catalog-book-author">Rully</div>
          <div class="catalog-book-name">Bandung After Rain</div>
          <div class="catalog-status available">Tersedia</div>
          <a href="#" class="btn-detail">Lihat Detail</a>
        </div>
      </article>

      <article class="catalog-card">
        <div class="catalog-card-cover">
          <div class="catalog-cover-thumb">
            <img src="images/jendela-hitam.jpg" alt="Jendela Hitam cover">
          </div>
        </div>
        <div class="catalog-card-body">
          <div class="catalog-book-author">Zura Sasaka</div>
          <div class="catalog-book-name">Jendela Hitam</div>
          <div class="catalog-status unavailable">Tidak Tersedia</div>
          <a href="#" class="btn-detail">Lihat Detail</a>
        </div>
      </article>

      <article class="catalog-card">
        <div class="catalog-card-cover">
          <div class="catalog-cover-thumb">
            <img src="images/ayah-ini-arahnya-kemana.jpg" alt="Ayah Ini Arahnya Kemana cover">
          </div>
        </div>
        <div class="catalog-card-body">
          <div class="catalog-book-author">Khalid Trian</div>
          <div class="catalog-book-name">Ayah Ini Arahnya Kemana</div>
          <div class="catalog-status unavailable">Tidak Tersedia</div>
          <a href="#" class="btn-detail">Lihat Detail</a>
        </div>
      </article>

      <article class="catalog-card">
        <div class="catalog-card-ribbon">BEST SELLER</div>
        <div class="catalog-card-cover">
          <div class="catalog-cover-thumb">
            <img src="images/3726-mdpl.jpg" alt="3726 MDPL cover">
          </div>
        </div>
        <div class="catalog-card-body">
          <div class="catalog-book-author">Nunviva Sari</div>
          <div class="catalog-book-name">3726 MDPL</div>
          <div class="catalog-status available">Tersedia</div>
          <a href="#" class="btn-detail">Lihat Detail</a>
        </div>
      </article>

      <article class="catalog-card">
        <div class="catalog-card-cover">
          <div class="catalog-cover-thumb">
            <img src="images/o-mdpl.jpg" alt="O MDPL cover">
          </div>
        </div>
        <div class="catalog-card-body">
          <div class="catalog-book-author">Nunviva Sari</div>
          <div class="catalog-book-name">O MDPL</div>
          <div class="catalog-status unavailable">Tidak Tersedia</div>
          <a href="#" class="btn-detail">Lihat Detail</a>
        </div>
      </article>

      <article class="catalog-card">
        <div class="catalog-card-cover">
          <div class="catalog-cover-thumb">
            <img src="images/secret-high-school.jpg" alt="Secret High School cover">
          </div>
        </div>
        <div class="catalog-card-body">
          <div class="catalog-book-author">Queens</div>
          <div class="catalog-book-name">Secret High School</div>
          <div class="catalog-status available">Tersedia</div>
          <a href="#" class="btn-detail">Lihat Detail</a>
        </div>
      </article>

      <article class="catalog-card">
        <div class="catalog-card-cover">
          <div class="catalog-cover-thumb">
            <img src="images/berandalan-vs-osis-rese.jpg" alt="Berandalan VS Osis Rese cover">
          </div>
        </div>
        <div class="catalog-card-body">
          <div class="catalog-book-author">Delvina Febrikanti</div>
          <div class="catalog-book-name">Berandalan VS Osis Rese</div>
          <div class="catalog-status available">Tersedia</div>
          <a href="#" class="btn-detail">Lihat Detail</a>
        </div>
      </article>

      <article class="catalog-card">
        <div class="catalog-card-cover">
          <div class="catalog-cover-thumb">
            <img src="images/misteri-kota-penari-topeng.jpg" alt="Misteri Kota Penari Topeng cover">
          </div>
        </div>
        <div class="catalog-card-body">
          <div class="catalog-book-author">Yovita Siswanti</div>
          <div class="catalog-book-name">Misteri Kota Penari Topeng</div>
          <div class="catalog-status unavailable">Tidak Tersedia</div>
          <a href="#" class="btn-detail">Lihat Detail</a>
        </div>
      </article>

      <article class="catalog-card">
        <div class="catalog-card-cover">
          <div class="catalog-cover-thumb">
            <img src="images/negeri-5-menara.jpg" alt="Negeri 5 Menara cover">
          </div>
        </div>
        <div class="catalog-card-body">
          <div class="catalog-book-author">Ahmad Fuadi</div>
          <div class="catalog-book-name">Negeri 5 Menara</div>
          <div class="catalog-status available">Tersedia</div>
          <a href="#" class="btn-detail">Lihat Detail</a>
        </div>
      </article>

      <article class="catalog-card">
        <div class="catalog-card-cover">
          <div class="catalog-cover-thumb">
            <img src="images/desa.jpg" alt="Desa cover">
          </div>
        </div>
        <div class="catalog-card-body">
          <div class="catalog-book-author">Maura Mauzon</div>
          <div class="catalog-book-name">Desa</div>
          <div class="catalog-status unavailable">Tidak Tersedia</div>
          <a href="#" class="btn-detail">Lihat Detail</a>
        </div>
      </article>

      <article class="catalog-card">
        <div class="catalog-card-cover">
          <div class="catalog-cover-thumb">
            <img src="images/hujan.jpg" alt="Hujan cover">
          </div>
        </div>
        <div class="catalog-card-body">
          <div class="catalog-book-author">Tere Liye</div>
          <div class="catalog-book-name">Hujan</div>
          <div class="catalog-status available">Tersedia</div>
          <a href="#" class="btn-detail">Lihat Detail</a>
        </div>
      </article>
    </div>

    <div class="catalog-page-number">
      <a href="#" class="active">1</a>
      <a href="#">2</a>
      <a href="#">3</a>
    </div>
  </section>
</main>

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
