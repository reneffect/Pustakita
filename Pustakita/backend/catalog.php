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
    $queryBuku = "SELECT b.*, k.nama_kategori FROM buku b LEFT JOIN kategori k ON b.kategori_id = k.id_kategori WHERE b.judul LIKE '%$searchQuery%' OR b.penulis LIKE '%$searchQuery%'";
} else {
    $queryBuku = "SELECT b.*, k.nama_kategori FROM buku b LEFT JOIN kategori k ON b.kategori_id = k.id_kategori";
}
$resultBuku = mysqli_query($koneksi, $queryBuku);

// Fetch user's favorited books to check which ones are already favorited
$favorit_array = [];
$queryFavorit = mysqli_query($koneksi, "SELECT id_buku FROM favorit WHERE id_siswa = $id_siswa");
if ($queryFavorit) {
    while ($fav = mysqli_fetch_assoc($queryFavorit)) {
        $favorit_array[] = $fav['id_buku'];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Catalog - PustaKita</title>
  <link rel="stylesheet" href="pustakita.css">
  <style>
    .catalog-container {
        padding: 40px;
        max-width: 1200px;
        margin: 0 auto;
        min-height: 70vh;
    }
    .catalog-title {
        font-size: 24px;
        font-weight: 800;
        color: #04005c; /* var(--blue) fallback */
        margin-bottom: 20px;
    }
    .catalog-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(180px, 1fr));
        gap: 20px;
    }
    .book-card {
        background: #fff;
        border-radius: 12px;
        padding: 15px;
        box-shadow: 0 4px 6px rgba(0,0,0,0.05);
        display: flex;
        flex-direction: column;
        align-items: center;
        text-align: center;
        transition: transform 0.2s;
        position: relative;
    }
    .book-card:hover {
        transform: translateY(-5px);
    }
    .book-cover {
        width: 120px;
        height: 160px;
        border-radius: 8px;
        margin-bottom: 15px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 12px;
        padding: 10px;
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }
    .book-title {
        font-size: 14px;
        font-weight: 700;
        color: #1a202c;
        margin-bottom: 5px;
    }
    .book-author {
        font-size: 12px;
        color: #718096;
        margin-bottom: 5px;
    }
    .book-category {
        font-size: 10px;
        color: #fff;
        background: #04005c;
        padding: 4px 8px;
        border-radius: 10px;
    }
    .btn-detail {
        margin-top: 15px;
        padding: 8px 16px;
        background: #04005c;
        color: white;
        border: none;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
        text-decoration: none;
        width: 100%;
    }
    .btn-detail:hover {
        background: #030040;
    }
    .btn-love {
        position: absolute;
        top: 20px;
        right: 20px;
        background: white;
        border-radius: 50%;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        box-shadow: 0 2px 6px rgba(0,0,0,0.15);
        cursor: pointer;
        transition: transform 0.2s;
        z-index: 10;
    }
    .btn-love:hover {
        transform: scale(1.1);
    }
    .btn-love svg {
        width: 18px;
        height: 18px;
        stroke: #ef4444;
        fill: none;
        stroke-width: 2;
        stroke-linecap: round;
        stroke-linejoin: round;
    }
    .btn-love.favorited svg {
        fill: #ef4444;
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
    <form action="catalog.php" method="GET" class="nav-search">
      <svg width="16" height="16" fill="none" stroke="#a0aec0" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path d="M21 21l-4.35-4.35" stroke-linecap="round"/></svg>
      <input type="text" name="q" placeholder="Cari Produk...Judul Buku atau Nama Penulis" value="<?php echo htmlspecialchars($searchQuery); ?>" style="border:none; outline:none; background:transparent; width:100%;">
    </form>
  </div>
  <div class="nav-right">
    <div class="nav-links">
      <a href="home.php">Home</a>
      <a href="catalog.php" class="active">Catalog</a>
      <a href="favorit.php">Favorit</a>
      <a href="history.php">History</a>
      <a href="profile.php">Profil</a>
    </div>
    <div class="nav-cart">
      <a href="catalog.php">
        <svg fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 016.364 0L12 7.636l1.318-1.318a4.5 4.5 0 016.364 6.364L12 21.382 4.318 12.682a4.5 4.5 0 010-6.364z"/></svg>
      </a>
    </div>
    <?php if (isset($_SESSION['siswa'])): ?>
      <span style="color:#fff;font-weight:600;font-size:14px;">👤 <?php echo htmlspecialchars($_SESSION['username'] ?? 'User'); ?></span>
      <a href="logout.php"><button class="btn-login">Logout</button></a>
    <?php else: ?>
      <a href="login.php"><button class="btn-masuk">Login</button></a>
    <?php endif; ?>
  </div>
</nav>

<div class="catalog-container">
    <div class="catalog-title">
        <?php if (!empty($searchQuery)): ?>
            Hasil Pencarian: "<?php echo htmlspecialchars($searchQuery); ?>"
        <?php else: ?>
            Katalog Buku
        <?php endif; ?>
    </div>

    <div class="catalog-grid">
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
        if (mysqli_num_rows($resultBuku) > 0) {
            while ($row = mysqli_fetch_assoc($resultBuku)) {
                $grad = $gradients[$j % count($gradients)];
                $is_favorited = in_array($row['id_buku'], $favorit_array);
                $heart_class = $is_favorited ? 'btn-love favorited' : 'btn-love';
                
                echo '<div class="book-card">';
                echo '  <a href="favorit.php?add=' . $row['id_buku'] . '" class="' . $heart_class . '" title="Tambahkan ke Favorit">';
                echo '    <svg viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 0 0-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 0 0-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 0 0 0-7.78z"></path></svg>';
                echo '  </a>';
                
                echo '  <div class="book-cover" style="background:' . $grad . '; padding: 0; overflow: hidden;">';
                if (!empty($row['foto'])) {
                    echo '    <img src="' . htmlspecialchars($row['foto']) . '" alt="Cover" style="width: 100%; height: 100%; object-fit: cover;">';
                } else {
                    echo '    <span style="opacity:0.8; text-align:center; padding: 10px;">' . htmlspecialchars($row['judul']) . '</span>';
                }
                echo '  </div>';
                echo '  <div class="book-title">' . htmlspecialchars($row['judul']) . '</div>';
                echo '  <div class="book-author">' . htmlspecialchars($row['penulis']) . '</div>';
                if (!empty($row['nama_kategori'])) {
                    echo '  <div class="book-category">' . htmlspecialchars($row['nama_kategori']) . '</div>';
                }
                echo '  <a href="peminjaman.php?id=' . $row['id_buku'] . '" class="btn-detail">Pinjam Buku</a>';
                echo '</div>';
                $j++;
            }
        } else {
            echo "<p style='color:#666;'>Buku tidak ditemukan.</p>";
        }
        ?>
    </div>
</div>

</body>
</html>
