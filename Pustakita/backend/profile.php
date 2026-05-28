<?php
session_start();
include 'database.php';

if (!isset($_SESSION['siswa'])) {
  header("Location: login.php");
  exit();
}

$id_siswa = $_SESSION['siswa'];

// Handle Permintaan Perpanjangan
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['req_perpanjang'])) {
  $id_detail = (int)$_POST['id_detail'];

  // FIX: siswa ada di detail_peminjaman.id_siswa
  $cek_milik = mysqli_query($koneksi, "
    SELECT status, status_perpanjangan 
    FROM detail_peminjaman
    WHERE id_detail = $id_detail AND id_siswa = $id_siswa
  ");

  if ($cek_milik && mysqli_num_rows($cek_milik) > 0) {
    $data_pinjam = mysqli_fetch_assoc($cek_milik);
    if ($data_pinjam['status_perpanjangan'] == 'belum') {
      mysqli_query($koneksi, "UPDATE detail_peminjaman SET status_perpanjangan = 'menunggu' WHERE id_detail = $id_detail");
    }
  }

  header("Location: profile.php");
  exit();
}

// Fetch user data
$query = mysqli_query($koneksi, "SELECT * FROM siswa WHERE id_siswa = $id_siswa");
$user  = mysqli_fetch_assoc($query);

if (!$user) {
  $user = ['username' => 'User', 'email' => '-', 'kelas' => '-', 'jurusan' => '-'];
}

// FIX: filter langsung dari detail_peminjaman.id_siswa, tidak perlu JOIN ke peminjaman untuk filter
$query_pinjam = mysqli_query($koneksi, "
    SELECT dp.*, b.judul, b.penulis, b.id_buku 
    FROM detail_peminjaman dp
    JOIN buku b ON dp.buku_id = b.id_buku 
    WHERE dp.id_siswa = $id_siswa
    AND dp.status IN ('menunggu', 'dipinjam')
    ORDER BY dp.id_detail DESC
");

$peminjaman_aktif = [];
if ($query_pinjam) {
  while ($row = mysqli_fetch_assoc($query_pinjam)) {
    $peminjaman_aktif[] = $row;
  }
}

// Path foto profil dengan encode spasi
$foto_path = '';
if (!empty($user['foto'])) {
  $foto_path = strpos($user['foto'], '/') === false
    ? 'admin%20case/uploads/' . htmlspecialchars($user['foto'])
    : str_replace(' ', '%20', htmlspecialchars($user['foto']));
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>PustaKita - Profil</title>
  <link rel="stylesheet" href="pustakita.css">
  <style>
    body { background: #f8fafc; }

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
      width: 160px;
      height: 160px;
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
      background: radial-gradient(circle at top left, rgba(59,130,246,0.22), transparent 40%),
                  radial-gradient(circle at bottom right, rgba(99,102,241,0.18), transparent 35%);
      pointer-events: none;
    }

    .profile-photo span {
      position: relative;
      font-size: 52px;
      color: #1e3a8a;
      font-weight: 800;
      text-transform: uppercase;
    }

    .profile-photo img {
      width: 100%;
      height: 100%;
      object-fit: cover;
      display: block;
    }

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

    .profile-details p { color: #475569; line-height: 1.8; }

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
      margin-bottom: 20px;
    }

    .loan-cover {
      width: 96px;
      min-width: 96px;
      height: 132px;
      border-radius: 8px;
      overflow: hidden;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 6px;
      color: white;
      font-weight: bold;
      text-align: center;
      font-size: 14px;
    }

    .loan-details { flex: 1; }

    .loan-meta {
      padding: 18px;
      border-radius: 12px;
      margin-bottom: 14px;
    }

    .loan-meta.status-menunggu { background: #fffbeb; }
    .loan-meta.status-dipinjam { background: #f0fdf4; }
    .loan-meta.status-proses   { background: #eff6ff; }

    .loan-meta .meta-row { margin-bottom: 10px; color: #334155; }

    .loan-actions {
      display: flex;
      gap: 12px;
      align-items: center;
      margin-top: 12px;
    }

    .btn-perpanjang {
      background: #16a34a;
      color: white;
      border: none;
      padding: 10px 14px;
      border-radius: 10px;
      font-weight: 700;
      cursor: pointer;
      transition: 0.2s;
    }

    .btn-perpanjang:hover:not([disabled]) { background: #15803d; }
    .btn-perpanjang[disabled] { cursor: not-allowed; opacity: 0.9; }

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

    .modal-overlay.active { display: flex; }

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

    .modal-header h2 { font-size: 24px; margin-bottom: 6px; color: #111827; }
    .modal-header p  { color: #475569; line-height: 1.7; }

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

    .modal-row div label { font-size: 12px; color: #64748b; display: block; margin-bottom: 4px; }
    .modal-row div span  { font-weight: 700; display: block; }

    .modal-actions {
      display: flex;
      justify-content: flex-end;
      gap: 12px;
    }

    .modal-button {
      border: none;
      border-radius: 999px;
      cursor: pointer;
      padding: 12px 22px;
      font-weight: 700;
    }

    .modal-button.primary   { background: #4338ca; color: white; text-decoration: none; }
    .modal-button.secondary { background: #f1f5f9; color: #334155; }

    .empty-state {
      text-align: center;
      padding: 40px;
      background: white;
      border-radius: 18px;
      color: #64748b;
      font-style: italic;
    }

    .section-title {
      font-size: 24px;
      font-weight: 700;
      color: #1f2937;
      margin-bottom: 20px;
    }

    @media (max-width: 860px) {
      .profile-card {
        grid-template-columns: 1fr;
        text-align: center;
        justify-items: center;
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
  </div>
  <div class="nav-right">
    <div class="nav-links">
      <a href="home.php">Home</a>
      <a href="catalog.php">Catalog</a>
      <a href="favorit.php">Favorit</a>
      <a href="history.php">History</a>
      <a href="profile.php" class="active">Profil</a>
    </div>
    <?php if (isset($_SESSION['siswa'])): ?>
      <span style="color:#111827;font-weight:600;font-size:14px;margin-right:15px;">
        👤 <?php echo htmlspecialchars($user['username'] ?? 'User'); ?>
      </span>
      <a href="logout.php"><button class="btn-login">Logout</button></a>
    <?php endif; ?>
  </div>
</nav>

<!-- Profile Card -->
<div class="profile-section">
  <div class="profile-card">

    <div class="profile-photo" id="profilePhoto">
      <?php if (!empty($foto_path)): ?>
        <img src="<?php echo $foto_path; ?>" alt="Foto Profil">
      <?php else: ?>
        <span><?php echo strtoupper(substr(($user['username'] ?? 'U'), 0, 2)); ?></span>
      <?php endif; ?>
    </div>

    <div class="profile-details">
      <h1><?php echo htmlspecialchars($user['username'] ?? 'User'); ?></h1>
      <p>Selamat datang kembali di PustaKita.</p>
      <button class="btn-edit-profile" id="openProfileData">Lihat Data Profil</button>
    </div>

  </div>
</div>

<!-- Modal -->
<div class="modal-overlay" id="profileModalOverlay">
  <div class="modal-card">
    <button class="modal-close" id="closeProfileModal">×</button>
    <div class="modal-header">
      <h2>Data Profil</h2>
      <p>Informasi profil lengkap Anda.</p>
    </div>
    <div class="modal-row">
      <div><label>Username</label><span><?php echo htmlspecialchars($user['username'] ?? '-'); ?></span></div>
      <div><label>Email</label><span><?php echo htmlspecialchars($user['email'] ?? '-'); ?></span></div>
      <div><label>Kelas</label><span><?php echo htmlspecialchars($user['kelas'] ?? '-'); ?></span></div>
      <div><label>Jurusan</label><span><?php echo htmlspecialchars($user['jurusan'] ?? '-'); ?></span></div>
    </div>
    <div class="modal-actions">
      <button class="modal-button secondary" id="closeProfileButton">Tutup</button>
      <a class="modal-button primary" href="edit-profile.php">Edit Profile</a>
    </div>
  </div>
</div>

<!-- Peminjaman Aktif -->
<div class="loan-section">
  <div class="section-title">Peminjaman Aktif</div>

  <?php if (empty($peminjaman_aktif)): ?>
    <div class="empty-state">Tidak ada peminjaman buku yang sedang berlangsung.</div>
  <?php else: ?>
    <?php foreach ($peminjaman_aktif as $pinjam):
      $gradients = [
        'linear-gradient(135deg,#1e3a5f,#2d6a9f)',
        'linear-gradient(135deg,#7f1d1d,#b91c1c)',
        'linear-gradient(135deg,#1e4d3e,#059669)',
        'linear-gradient(135deg,#78350f,#d97706)',
        'linear-gradient(135deg,#4a044e,#9333ea)',
        'linear-gradient(135deg,#0c4a6e,#0284c7)'
      ];
      $grad = $gradients[$pinjam['id_buku'] % count($gradients)];

      $status_class = 'status-dipinjam';
      $status_text  = 'Aktif (Dipinjam)';

      if ($pinjam['status'] == 'menunggu') {
        $status_class = 'status-menunggu';
        $status_text  = 'Menunggu Konfirmasi';
      } elseif (($pinjam['status_perpanjangan'] ?? '') == 'menunggu') {
        $status_class = 'status-proses';
        $status_text  = 'Perpanjangan Diproses';
      }
    ?>
      <div class="loan-card">
        <div class="loan-cover" style="background:<?php echo $grad; ?>;">
          <?php echo htmlspecialchars($pinjam['judul']); ?>
        </div>
        <div class="loan-details">
          <div class="loan-meta <?php echo $status_class; ?>">
            <div class="meta-row"><strong>Nama Peminjam :</strong> <?php echo htmlspecialchars($user['username'] ?? 'User'); ?></div>
            <div class="meta-row"><strong>Judul Buku :</strong> <?php echo htmlspecialchars($pinjam['judul']); ?></div>
            <div class="meta-row"><strong>Tanggal Pinjam :</strong> <?php echo !empty($pinjam['tgl_pinjam']) ? date('d M Y', strtotime($pinjam['tgl_pinjam'])) : '-'; ?></div>
            <?php if (!empty($pinjam['tgl_pengembalian'])): ?>
              <div class="meta-row"><strong>Batas Kembali :</strong> <?php echo date('d M Y', strtotime($pinjam['tgl_pengembalian'])); ?></div>
            <?php endif; ?>

            <div class="loan-actions">
              <?php if ($pinjam['status'] == 'dipinjam' && ($pinjam['status_perpanjangan'] ?? 'belum') == 'belum'): ?>
                <form method="POST" action="">
                  <input type="hidden" name="id_detail" value="<?php echo $pinjam['id_detail']; ?>">
                  <button type="submit" name="req_perpanjang" class="btn-perpanjang"
                    onclick="return confirm('Minta perpanjangan untuk buku ini?')">
                    Minta Perpanjangan
                  </button>
                </form>
              <?php elseif (($pinjam['status_perpanjangan'] ?? '') == 'menunggu'): ?>
                <button class="btn-perpanjang" disabled style="background:#0284c7;">Permintaan Dikirim</button>
              <?php endif; ?>
              <div style="margin-left:auto;color:#6b7280;font-weight:700">
                Status: <?php echo $status_text; ?>
              </div>
            </div>
          </div>
        </div>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
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
  const profileModalOverlay = document.getElementById('profileModalOverlay');
  const openProfileData     = document.getElementById('openProfileData');
  const closeProfileModal   = document.getElementById('closeProfileModal');
  const closeProfileButton  = document.getElementById('closeProfileButton');
  const profilePhoto        = document.getElementById('profilePhoto');

  function openModal()  { profileModalOverlay.classList.add('active'); }
  function closeModal() { profileModalOverlay.classList.remove('active'); }

  if (profilePhoto)       profilePhoto.addEventListener('click', openModal);
  if (openProfileData)    openProfileData.addEventListener('click', openModal);
  if (closeProfileModal)  closeProfileModal.addEventListener('click', closeModal);
  if (closeProfileButton) closeProfileButton.addEventListener('click', closeModal);
  if (profileModalOverlay) {
    profileModalOverlay.addEventListener('click', function(e) {
      if (e.target === profileModalOverlay) closeModal();
    });
  }
</script>
</body>
</html>