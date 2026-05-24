<?php
include 'database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: Login_admin.php");
    exit();
}

class Buku {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getSemuaBuku($keyword = '') {
        if ($keyword) {
            $stmt = $this->db->prepare("SELECT * FROM buku WHERE judul LIKE ? OR pengarang LIKE ? ORDER BY id_buku DESC");
            $like = "%$keyword%";
            $stmt->bind_param("ss", $like, $like);
            $stmt->execute();
            return $stmt->get_result();
        }
        return $this->db->query("SELECT * FROM buku ORDER BY id_buku DESC");
    }

    public function tambahBuku($judul, $pengarang, $penerbit, $tahun, $stok) {
        $stmt = $this->db->prepare("INSERT INTO buku (judul, pengarang, penerbit, tahun, stok) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssii", $judul, $pengarang, $penerbit, $tahun, $stok);
        if ($stmt->execute()) return "Buku berhasil ditambahkan!";
        return "Gagal: " . $this->db->error;
    }

    public function getBukuById($id) {
        $stmt = $this->db->prepare("SELECT * FROM buku WHERE id_buku = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function editBuku($id, $judul, $pengarang, $penerbit, $tahun, $stok) {
        $stmt = $this->db->prepare("UPDATE buku SET judul=?, pengarang=?, penerbit=?, tahun=?, stok=? WHERE id_buku=?");
        $stmt->bind_param("sssiii", $judul, $pengarang, $penerbit, $tahun, $stok, $id);
        if ($stmt->execute()) return "Buku berhasil diupdate!";
        return "Gagal: " . $this->db->error;
    }

    public function hapusBuku($id) {
        $stmt = $this->db->prepare("DELETE FROM buku WHERE id_buku = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) return "Buku berhasil dihapus!";
        return "Gagal: " . $this->db->error;
    }
}

$buku    = new Buku($koneksi);
$keyword = trim($_GET['cari'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action    = $_POST['action'] ?? '';
    $judul     = trim($_POST['judul'] ?? '');
    $pengarang = trim($_POST['pengarang'] ?? '');
    $penerbit  = trim($_POST['penerbit'] ?? '');
    $tahun     = (int)($_POST['tahun'] ?? 0);
    $stok      = (int)($_POST['stok'] ?? 0);

    if ($action === 'tambah') {
        $pesan = $buku->tambahBuku($judul, $pengarang, $penerbit, $tahun, $stok);
    } elseif ($action === 'edit') {
        $pesan = $buku->editBuku((int)$_POST['id_buku'], $judul, $pengarang, $penerbit, $tahun, $stok);
    } elseif ($action === 'hapus') {
        $pesan = $buku->hapusBuku((int)$_POST['id_buku']);
    }

    header("Location: pengelolaan_buku.php?pesan=" . urlencode($pesan));
    exit();
}

if (isset($_GET['hapus'])) {
    $pesan = $buku->hapusBuku((int)$_GET['hapus']);
    header("Location: pengelolaan_buku.php?pesan=" . urlencode($pesan));
    exit();
}

$pesan      = $_GET['pesan'] ?? '';
$daftarBuku = $buku->getSemuaBuku($keyword);
$editData   = isset($_GET['edit']) ? $buku->getBukuById((int)$_GET['edit']) : null;
$showTambah = isset($_GET['tambah']);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengelolaan Buku - PustaKita</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #f4f4f8; display: flex; min-height: 100vh; }
        a { text-decoration: none; color: inherit; }

        /* SIDEBAR */
        .sidebar { width: 220px; min-height: 100vh; background: #0d0d1a; display: flex; flex-direction: column; position: fixed; top: 0; left: 0; height: 100vh; }
        .sidebar-logo { padding: 28px 24px 24px; border-bottom: 1px solid rgba(255,255,255,.07); }
        .sidebar-logo .logo { font-size: 1.15rem; font-weight: 700; color: #fff; }
        .sidebar-logo .logo span { font-style: italic; }
        .sidebar-nav { flex: 1; padding: 12px 0; overflow-y: auto; }
        .nav-item { display: flex; align-items: center; gap: 10px; padding: 11px 24px; font-size: .82rem; font-weight: 500; color: rgba(255,255,255,.5); transition: background .15s, color .15s; }
        .nav-item:hover { background: rgba(255,255,255,.06); color: rgba(255,255,255,.85); }
        .nav-item.active { background: #1a1a3e; color: #fff; font-weight: 600; border-left: 3px solid #4f46e5; padding-left: 21px; }
        .nav-icon { font-size: .9rem; width: 18px; text-align: center; }
        .sidebar-footer { padding: 16px 0; border-top: 1px solid rgba(255,255,255,.07); }
        .logout-item { display: flex; align-items: center; gap: 10px; padding: 11px 24px; font-size: .82rem; color: rgba(255,255,255,.4); transition: background .15s, color .15s; }
        .logout-item:hover { background: rgba(239,68,68,.1); color: #f87171; }

        /* MAIN */
        .main { margin-left: 220px; flex: 1; display: flex; flex-direction: column; }
        .topbar { background: #fff; padding: 0 32px; height: 60px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #ebebf0; position: sticky; top: 0; z-index: 10; }
        .topbar-title { font-size: 1.3rem; font-weight: 700; color: #0d0d1a; }
        .topbar-user { display: flex; align-items: center; gap: 8px; }
        .topbar-avatar { width: 34px; height: 34px; border-radius: 50%; background: #0d0d1a; display: flex; align-items: center; justify-content: center; font-size: .8rem; font-weight: 700; color: #fff; }
        .topbar-username { font-size: .82rem; font-weight: 600; color: #0d0d1a; }

        /* CONTENT */
        .content { padding: 28px 32px; flex: 1; }

        /* CARD */
        .card { background: #fff; border-radius: 10px; border: 1px solid #ebebf0; overflow: hidden; margin-bottom: 24px; }
        .card-header { padding: 16px 24px; border-bottom: 1px solid #ebebf0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; }
        .card-title { font-size: .95rem; font-weight: 700; color: #0d0d1a; }
        .card-body { padding: 20px 24px; }

        /* TABLE */
        table { width: 100%; border-collapse: collapse; }
        thead th { font-size: .72rem; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: #aaa; padding: 10px 14px; border-bottom: 1px solid #ebebf0; text-align: left; }
        tbody td { padding: 12px 14px; font-size: .82rem; color: #111; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover td { background: #fafafe; }

        /* BUTTONS */
        .btn { padding: 7px 14px; border-radius: 6px; font-size: .78rem; font-weight: 600; cursor: pointer; border: none; display: inline-block; }
        .btn-primary { background: #0d1b5e; color: #fff; }
        .btn-primary:hover { background: #1a2d8f; }
        .btn-edit { background: #4f46e5; color: #fff; }
        .btn-edit:hover { background: #4338ca; }
        .btn-hapus { background: #ef4444; color: #fff; }
        .btn-hapus:hover { background: #dc2626; }
        .btn-batal { background: #e5e7eb; color: #374151; }
        .btn-batal:hover { background: #d1d5db; }

        /* SEARCH */
        .search-box { display: flex; gap: 8px; align-items: center; }
        .search-input { padding: 8px 14px; border: 1px solid #e5e7eb; border-radius: 6px; font-size: .82rem; outline: none; width: 220px; }
        .search-input:focus { border-color: #4f46e5; }

        /* FORM */
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-size: .78rem; font-weight: 600; color: #374151; margin-bottom: 6px; }
        .form-input { width: 100%; padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 6px; font-size: .82rem; outline: none; }
        .form-input:focus { border-color: #4f46e5; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .form-actions { display: flex; gap: 10px; margin-top: 8px; }

        /* ALERT */
        .alert { padding: 10px 16px; border-radius: 6px; font-size: .82rem; margin-bottom: 16px; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
        .alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

        .aksi-col { display: flex; gap: 6px; }
        .empty-cell { text-align: center; padding: 28px; color: #bbb; font-size: .82rem; }
    </style>
</head>
<body>

<!-- SIDEBAR -->
<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="logo"><span>P</span>ustaKita</div>
    </div>
    <nav class="sidebar-nav">
        <?php
        $menus = [
            ['href' => 'dashboard_admin.php',    'label' => 'Dashboard',           'icon' => '⊞'],
            ['href' => 'pengelolaan_buku.php',   'label' => 'Pengelolaan Buku',    'icon' => '📖'],
            ['href' => 'pengelolaan_member.php', 'label' => 'Pengelolaan Member',  'icon' => '👤'],
            ['href' => 'kelola_peminjaman.php',  'label' => 'Kelola Peminjaman',   'icon' => '🕐'],
            ['href' => 'pengembalian.php',       'label' => 'Kelola Pengembalian', 'icon' => '↩'],
            ['href' => 'denda.php',              'label' => 'Kelola Denda',        'icon' => '💰'],
            ['href' => 'laporan.php',            'label' => 'Laporan',             'icon' => '📊'],
        ];
        foreach ($menus as $menu):
            $active = basename($_SERVER['PHP_SELF']) === $menu['href'] ? 'active' : '';
        ?>
        <a href="<?= $menu['href'] ?>" class="nav-item <?= $active ?>">
            <span class="nav-icon"><?= $menu['icon'] ?></span>
            <?= $menu['label'] ?>
        </a>
        <?php endforeach; ?>
    </nav>
    <div class="sidebar-footer">
        <!-- FIX: konsisten pakai logout.php huruf kecil -->
        <a href="logout.php" class="logout-item">
            <span class="nav-icon">↗</span> Log Out
        </a>
    </div>
</aside>

<!-- MAIN -->
<div class="main">

    <div class="topbar">
        <div class="topbar-title">Pengelolaan Buku</div>
        <div class="topbar-user">
            <div class="topbar-avatar"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
            <span class="topbar-username"><?= htmlspecialchars($_SESSION['username']) ?></span>
        </div>
    </div>

    <div class="content">

        <?php if ($pesan): ?>
            <div class="alert <?= str_contains($pesan, 'Gagal') ? 'alert-danger' : 'alert-success' ?>">
                <?= htmlspecialchars($pesan) ?>
            </div>
        <?php endif; ?>

        <!-- TABEL BUKU -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">Daftar Buku</span>
                <div class="search-box">
                    <form method="GET" action="" style="display:flex;gap:8px">
                        <input type="text" name="cari" class="search-input"
                            placeholder="Cari judul / pengarang..."
                            value="<?= htmlspecialchars($keyword) ?>">
                        <button type="submit" class="btn btn-batal">Cari</button>
                    </form>
                    <a href="?tambah=1" class="btn btn-primary">+ Tambah Buku</a>
                </div>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Judul</th>
                        <th>Pengarang</th>
                        <th>Stok</th>
                        <th>Tahun Penerbit</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($daftarBuku && $daftarBuku->num_rows > 0):
                    $no = 1;
                    while ($row = $daftarBuku->fetch_assoc()): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($row['judul']) ?></td>
                        <td><?= htmlspecialchars($row['pengarang']) ?></td>
                        <td><?= $row['stok'] ?></td>
                        <td><?= $row['tahun'] ?></td>
                        <td>
                            <div class="aksi-col">
                                <a href="?edit=<?= $row['id_buku'] ?>" class="btn btn-edit">Edit</a>
                                <a href="?hapus=<?= $row['id_buku'] ?>" class="btn btn-hapus"
                                    onclick="return confirm('Yakin hapus buku ini?')">Hapus</a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="empty-cell">Belum ada data buku</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- FORM EDIT -->
        <?php if ($editData): ?>
        <div class="card">
            <div class="card-header">
                <span class="card-title">Edit Buku</span>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id_buku" value="<?= $editData['id_buku'] ?>">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Judul Buku *</label>
                            <input type="text" name="judul" class="form-input"
                                value="<?= htmlspecialchars($editData['judul']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Stok</label>
                            <input type="number" name="stok" class="form-input"
                                value="<?= $editData['stok'] ?>" min="0" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Pengarang *</label>
                            <input type="text" name="pengarang" class="form-input"
                                value="<?= htmlspecialchars($editData['pengarang']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tahun Terbit</label>
                            <input type="number" name="tahun" class="form-input"
                                value="<?= $editData['tahun'] ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Penerbit</label>
                            <input type="text" name="penerbit" class="form-input"
                                value="<?= htmlspecialchars($editData['penerbit']) ?>" required>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Simpan Buku</button>
                        <a href="pengelolaan_buku.php" class="btn btn-batal">Batal</a>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- FORM TAMBAH -->
        <?php if ($showTambah && !$editData): ?>
        <div class="card">
            <div class="card-header">
                <span class="card-title">Tambah Data Buku Baru</span>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="tambah">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Judul Buku *</label>
                            <input type="text" name="judul" class="form-input"
                                placeholder="Judul Lengkap Buku" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Stok *</label>
                            <input type="number" name="stok" class="form-input"
                                placeholder="1" min="0" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Pengarang *</label>
                            <input type="text" name="pengarang" class="form-input"
                                placeholder="Nama Pengarang" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Tahun Penerbit</label>
                            <input type="number" name="tahun" class="form-input"
                                placeholder="<?= date('Y') ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Penerbit</label>
                            <input type="text" name="penerbit" class="form-input"
                                placeholder="Nama Penerbit" required>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Simpan Buku</button>
                        <a href="pengelolaan_buku.php" class="btn btn-batal">Batal</a>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

</body>
</html>