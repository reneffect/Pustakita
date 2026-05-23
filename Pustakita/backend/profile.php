<?php

?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PustaKita - Profil</title>
  <link rel="stylesheet" href="pustakita.css">
  <style>
    body {
      background: #f8fafc;
    }
    .profile-section {
      max-width: 1000px;
      margin: 40px auto 80px;
      padding: 0 40px;
    }
    .profile-card {
      background: white;
      border-radius: 24px;
      box-shadow: 0 20px 50px rgba(15, 23, 42, 0.08);
      display: grid;
      grid-template-columns: 240px 1fr;
      gap: 32px;
      padding: 36px;
      align-items: center;
    }
    .profile-photo {
      width: 220px;
      height: 220px;
      border-radius: 50%;
      background: linear-gradient(180deg, #dbeafe 0%, #bfdbfe 100%);
      display: flex;
      align-items: center;
      justify-content: center;
      margin: 0 auto;
      box-shadow: 0 16px 35px rgba(15, 23, 42, 0.12);
      cursor: pointer;
      position: relative;
      overflow: hidden;
    }
    .profile-photo::before {
      content: "";
      position: absolute;
      inset: 0;
      background: radial-gradient(circle at top left, rgba(59, 130, 246, 0.22), transparent 40%), radial-gradient(circle at bottom right, rgba(99, 102, 241, 0.18), transparent 35%);
    }
    .profile-photo span {
      position: relative;
      font-size: 72px;
      color: #1e3a8a;
      font-weight: 800;
    }
    .profile-photo-label {
      position: absolute;
      bottom: 12px;
      left: 50%;
      transform: translateX(-50%);
      background: rgba(255,255,255,0.9);
      color: #1f2937;
      font-size: 12px;
      font-weight: 600;
      padding: 8px 14px;
      border-radius: 999px;
      box-shadow: 0 4px 12px rgba(15, 23, 42, 0.12);
      pointer-events: none;
    }
    .profile-photo.small {
      width: 96px;
      height: 96px;
      font-size: 36px;
      box-shadow: 0 10px 24px rgba(15,23,42,0.08);
    }
    /* hide detailed member card on page, show only in modal */
    .profile-info-list { display: none; }
    /* Peminjaman aktif styles */
    .loan-section {
      max-width: 1000px;
      margin: 28px auto 60px;
      padding: 0 40px;
    }
    .loan-card {
      display: flex;
      gap: 28px;
      align-items: flex-start;
      background: white;
      border-radius: 18px;
      padding: 20px;
      box-shadow: 0 10px 30px rgba(15,23,42,0.06);
    }
    .loan-cover {
      width: 96px;
      min-width: 96px;
      height: 132px;
      border-radius: 8px;
      overflow: hidden;
      background: #f3f4f6;
      display:flex;align-items:center;justify-content:center;
      padding:6px;
    }
    .loan-cover img { width:100%; height:100%; object-fit:cover; display:block }
    .loan-details { flex:1 }
    .loan-meta { background:#fef2f2; padding:18px; border-radius:12px }
    .loan-meta .meta-row { margin-bottom:10px; color:#334155 }
    .loan-actions { display:flex; gap:12px; align-items:center; margin-top:12px }
    .btn-perpanjang { background:#16a34a; color:white; border:none; padding:10px 14px; border-radius:10px; font-weight:700; cursor:pointer }
    .btn-perpanjang[disabled] { opacity:0.6; cursor:default }
    .profile-details {
      display: flex;
      flex-direction: column;
      gap: 16px;
    }
    .profile-details h1 {
      font-size: 34px;
      color: #1f2937;
      margin-bottom: 6px;
    }
    .profile-details p {
      color: #475569;
      line-height: 1.8;
      max-width: 700px;
    }
    .profile-info-list {
      display: grid;
      grid-template-columns: repeat(2, minmax(0, 1fr));
      gap: 16px;
      margin: 24px 0;
    }
    .profile-info-item {
      background: #eef2ff;
      border-radius: 16px;
      padding: 18px 20px;
      color: #1f2937;
      font-weight: 600;
      box-shadow: inset 0 0 0 1px rgba(99, 102, 241, 0.08);
    }
    .profile-info-item span {
      display: block;
      margin-top: 6px;
      color: #475569;
      font-weight: 400;
    }
    .btn-edit-profile {
      width: fit-content;
      background: #4338ca;
      color: white;
      padding: 14px 24px;
      border-radius: 999px;
      border: none;
      font-size: 14px;
      font-weight: 700;
      cursor: pointer;
      transition: transform 0.2s, background 0.2s;
    }
    .btn-edit-profile:hover {
      transform: translateY(-2px);
      background: #4f46e5;
    }
    .modal-overlay {
      position: fixed;
      inset: 0;
      background: rgba(15, 23, 42, 0.45);
      display: none;
      align-items: center;
      justify-content: center;
      padding: 28px;
      z-index: 200;
    }
    .modal-overlay.active {
      display: flex;
    }
    .modal-card {
      background: white;
      border-radius: 28px;
      width: min(600px, 100%);
      padding: 32px;
      box-shadow: 0 34px 80px rgba(15, 23, 42, 0.18);
      position: relative;
    }
    .modal-close {
      position: absolute;
      top: 18px;
      right: 18px;
      width: 34px;
      height: 34px;
      border-radius: 50%;
      border: none;
      background: #e5e7eb;
      color: #1f2937;
      font-size: 18px;
      cursor: pointer;
    }
    .modal-header {
      margin-bottom: 24px;
    }
    .modal-header h2 {
      font-size: 24px;
      margin-bottom: 6px;
      color: #111827;
    }
    .modal-header p {
      color: #475569;
      line-height: 1.7;
    }
    .modal-row {
      display: grid;
      grid-template-columns: 1fr;
      gap: 14px;
      margin: 18px 0 24px;
    }
    .modal-row div {
      background: #f8fafc;
      border-radius: 16px;
      padding: 16px 18px;
      border: 1px solid #e2e8f0;
      color: #334155;
    }
    .modal-row div label {
      font-size: 12px;
      color: #64748b;
      display: block;
      margin-bottom: 4px;
    }
    .modal-row div span {
      font-weight: 700;
      display: block;
      margin-top: 4px;
    }
    .modal-actions {
      display: flex;
      justify-content: flex-end;
      gap: 12px;
      flex-wrap: wrap;
    }
    .modal-button {
      border: none;
      border-radius: 999px;
      cursor: pointer;
      padding: 12px 22px;
      font-weight: 700;
    }
    .modal-button.primary {
      background: #4338ca;
      color: white;
    }
    .modal-button.secondary {
      background: #f1f5f9;
      color: #334155;
    }
    @media (max-width: 860px) {
      .profile-card {
        grid-template-columns: 1fr;
        text-align: center;
      }
      .profile-info-list {
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
      <a href="history.php">History</a>
      <a href="profile.php" class="active">Profil</a>
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

<!-- PROFILE SUMMARY (minimal) -->
<div class="profile-section">
  <div class="profile-card" style="padding-left:120px;">
    <div class="profile-photo small" id="profilePhoto">
      <span>IR</span>
    </div>
    <div class="profile-details">
      <h1>Isabella Rosalyta</h1>
      <p>Selamat datang kembali di PustaKita.</p>
      <button class="btn-edit-profile" id="openProfileData">Lihat Data Profil</button>
    </div>
  </div>
</div>

<div class="modal-overlay" id="profileModalOverlay">
  <div class="modal-card" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <button class="modal-close" id="closeProfileModal" aria-label="Tutup popup">×</button>
    <div class="modal-header">
      <h2 id="modalTitle">Data Profil</h2>
      <p>Informasi profil lengkap Anda. Tekan Edit Profile untuk mengelola data.</p>
    </div>
    <div class="modal-row">
      <div>
        <label>Nama</label>
        <span>Isabella Rosalyta</span>
      </div>
      <div>
        <label>ID Anggota</label>
        <span>123456</span>
      </div>
      <div>
        <label>Email</label>
        <span>user@email.com</span>
      </div>
      <div>
        <label>No. HP</label>
        <span>0812xxxxxxx</span>
      </div>
    </div>
    <div class="modal-actions">
      <button class="modal-button secondary" id="closeProfileButton">Tutup</button>
      <a class="modal-button primary" href="edit-profile.html">Edit Profile</a>
    </div>
  </div>
</div>

<!-- PEMINJAMAN AKTIF (moved above profile card for visibility) -->
<div class="loan-section">
  <div class="section-title">Peminjaman Aktif</div>
  <div class="loan-card">
    <div class="loan-cover">
      <img src="images/laskar-pelangi.jpg" alt="Laskar Pelangi">
    </div>
    <div class="loan-details">
      <div class="loan-meta">
        <div class="meta-row"><strong>Nama Peminjam :</strong> Isabella Rosalyta</div>
        <div class="meta-row"><strong>Judul Buku :</strong> Laskar Pelangi</div>
        <div class="meta-row"><strong>Tanggal Pinjam :</strong> 1 Mei 2026</div>
        <div class="meta-row"><strong>Batas Kembali :</strong> 7 Mei 2026</div>
        <div class="loan-actions">
          <button class="btn-perpanjang" id="reqPerpanjang">Minta Perpanjangan</button>
          <div style="margin-left:auto;color:#6b7280;font-weight:700">Status: Aktif</div>
        </div>
      </div>
      <div style="height:14px"></div>
      <div class="loan-meta" style="background:#fee2e2">
        <div style="display:flex;align-items:center;gap:10px;margin-bottom:8px;color:#b91c1c">
          <svg width="22" height="22" viewBox="0 0 24 24" fill="none" stroke="#b91c1c">
            <circle cx="12" cy="12" r="9" stroke-width="2"/>
            <path d="M12 7v6" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
            <path d="M12 17h.01" stroke-linecap="round" stroke-linejoin="round" stroke-width="2"/>
          </svg>
          <strong>Denda</strong>
        </div>
        <div class="meta-row">Terlambat 2 Hari</div>
        <div class="meta-row">Total Denda = Rp 10.000</div>
      </div>
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

<script>
  const profilePhoto = document.getElementById('profilePhoto');
  const profileModalOverlay = document.getElementById('profileModalOverlay');
  const closeProfileModal = document.getElementById('closeProfileModal');
  const closeProfileButton = document.getElementById('closeProfileButton');
  const openProfileData = document.getElementById('openProfileData');

  function openModal() {
    profileModalOverlay.classList.add('active');
  }
  function closeModal() {
    profileModalOverlay.classList.remove('active');
  }

  profilePhoto.addEventListener('click', openModal);
  openProfileData.addEventListener('click', openModal);
  closeProfileModal.addEventListener('click', closeModal);
  closeProfileButton.addEventListener('click', closeModal);
  profileModalOverlay.addEventListener('click', function(event) {
    if (event.target === profileModalOverlay) {
      closeModal();
    }
  });

  // Perpanjangan request handler
  const reqPerpanjang = document.getElementById('reqPerpanjang');
  if (reqPerpanjang) {
    reqPerpanjang.addEventListener('click', function() {
      // disable button and show confirmation text
      reqPerpanjang.disabled = true;
      reqPerpanjang.textContent = 'Permintaan Dikirim';
      reqPerpanjang.style.background = '#059669';
      // optional: brief toast
      const toast = document.createElement('div');
      toast.textContent = 'Permintaan perpanjangan dikirim.';
      toast.style.position = 'fixed';
      toast.style.right = '20px';
      toast.style.bottom = '20px';
      toast.style.background = 'rgba(15,23,42,0.9)';
      toast.style.color = 'white';
      toast.style.padding = '10px 14px';
      toast.style.borderRadius = '8px';
      toast.style.zIndex = 300;
      document.body.appendChild(toast);
      setTimeout(() => toast.remove(), 3000);
    });
  }
</script>
</body>
</html>
