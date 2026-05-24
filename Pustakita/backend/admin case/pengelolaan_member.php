<?php
include 'database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: Login_admin.php");
    exit();
}

class Member {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getSemuaMember($keyword = '') {
        if ($keyword) {
            $stmt = $this->db->prepare("SELECT * FROM siswa WHERE username LIKE ? OR no_anggota LIKE ? ORDER BY id_siswa DESC");
            $like = "%$keyword%";
            $stmt->bind_param("ss", $like, $like);
            $stmt->execute();
            return $stmt->get_result();
        }
        return $this->db->query("SELECT * FROM siswa ORDER BY id_siswa DESC");
    }

    public function getMemberById($id) {
        $stmt = $this->db->prepare("SELECT * FROM siswa WHERE id_siswa = ?");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        return $stmt->get_result()->fetch_assoc();
    }

    public function tambahMember($username, $email, $no_anggota, $no_telepon, $alamat, $status) {
        $password = password_hash('password123', PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO siswa (username, email, password, no_anggota, no_telepon, alamat, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("sssssss", $username, $email, $password, $no_anggota, $no_telepon, $alamat, $status);
        if ($stmt->execute()) return "Member berhasil ditambahkan! Password default: password123";
        return "Gagal: " . $this->db->error;
    }

    public function editMember($id, $username, $email, $no_anggota, $no_telepon, $alamat, $status) {
        $stmt = $this->db->prepare("UPDATE siswa SET username=?, email=?, no_anggota=?, no_telepon=?, alamat=?, status=? WHERE id_siswa=?");
        $stmt->bind_param("ssssssi", $username, $email, $no_anggota, $no_telepon, $alamat, $status, $id);
        if ($stmt->execute()) return "Member berhasil diupdate!";
        return "Gagal: " . $this->db->error;
    }

    public function hapusMember($id) {
        $stmt = $this->db->prepare("DELETE FROM siswa WHERE id_siswa = ?");
        $stmt->bind_param("i", $id);
        if ($stmt->execute()) return "Member berhasil dihapus!";
        return "Gagal: " . $this->db->error;
    }

    public function getTotalMember() {
        return $this->db->query("SELECT COUNT(*) as total FROM siswa")->fetch_assoc()['total'];
    }

    public function getTotalAktif() {
        return $this->db->query("SELECT COUNT(*) as total FROM siswa WHERE status='aktif'")->fetch_assoc()['total'];
    }

    public function getTotalNonaktif() {
        return $this->db->query("SELECT COUNT(*) as total FROM siswa WHERE status='nonaktif'")->fetch_assoc()['total'];
    }
}

$member  = new Member($koneksi);
$keyword = trim($_GET['cari'] ?? '');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action     = $_POST['action'] ?? '';
    $username   = trim($_POST['username'] ?? '');
    $email      = trim($_POST['email'] ?? '');
    $no_anggota = trim($_POST['no_anggota'] ?? '');
    $no_telepon = trim($_POST['no_telepon'] ?? '');
    $alamat     = trim($_POST['alamat'] ?? '');
    $status     = $_POST['status'] ?? 'aktif';

    if ($action === 'tambah') {
        $pesan = $member->tambahMember($username, $email, $no_anggota, $no_telepon, $alamat, $status);
    } elseif ($action === 'edit') {
        $pesan = $member->editMember((int)$_POST['id_siswa'], $username, $email, $no_anggota, $no_telepon, $alamat, $status);
    } elseif ($action === 'hapus') {
        $pesan = $member->hapusMember((int)$_POST['id_siswa']);
    }

    header("Location: pengelolaan_member.php?pesan=" . urlencode($pesan));
    exit();
}

if (isset($_GET['hapus'])) {
    $pesan = $member->hapusMember((int)$_GET['hapus']);
    header("Location: pengelolaan_member.php?pesan=" . urlencode($pesan));
    exit();
}

$pesan        = $_GET['pesan'] ?? '';
$daftarMember = $member->getSemuaMember($keyword);
$editData     = isset($_GET['edit']) ? $member->getMemberById((int)$_GET['edit']) : null;
$showTambah   = isset($_GET['tambah']);
$totalMember  = $member->getTotalMember();
$totalAktif   = $member->getTotalAktif();
$totalNonaktif = $member->getTotalNonaktif();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengelolaan Member - PustaKita</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #f4f4f8; display: flex; min-height: 100vh; }
        a { text-decoration: none; color: inherit; }

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

        .main { margin-left: 220px; flex: 1; display: flex; flex-direction: column; }
        .topbar { background: #fff; padding: 0 32px; height: 60px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid #ebebf0; position: sticky; top: 0; z-index: 10; }
        .topbar-title { font-size: 1.3rem; font-weight: 700; color: #0d0d1a; }
        .topbar-user { display: flex; align-items: center; gap: 8px; }
        .topbar-avatar { width: 34px; height: 34px; border-radius: 50%; background: #0d0d1a; display: flex; align-items: center; justify-content: center; font-size: .8rem; font-weight: 700; color: #fff; }
        .topbar-username { font-size: .82rem; font-weight: 600; color: #0d0d1a; }

        .content { padding: 28px 32px; flex: 1; }

        .stats-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; margin-bottom: 24px; }
        .stat-card { background: #0d1b5e; border-radius: 10px; padding: 20px 22px; color: #fff; }
        .stat-card-label { font-size: .72rem; font-weight: 600; color: rgba(255,255,255,.6); text-transform: uppercase; letter-spacing: .06em; margin-bottom: 8px; }
        .stat-card-value { font-size: 2rem; font-weight: 800; color: #fff; letter-spacing: -1px; line-height: 1; }

        .card { background: #fff; border-radius: 10px; border: 1px solid #ebebf0; overflow: hidden; margin-bottom: 24px; }
        .card-header { padding: 16px 24px; border-bottom: 1px solid #ebebf0; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 10px; }
        .card-title { font-size: .95rem; font-weight: 700; color: #0d0d1a; }
        .card-body { padding: 20px 24px; }

        table { width: 100%; border-collapse: collapse; }
        thead th { font-size: .72rem; font-weight: 600; text-transform: uppercase; letter-spacing: .06em; color: #aaa; padding: 10px 14px; border-bottom: 1px solid #ebebf0; text-align: left; }
        tbody td { padding: 12px 14px; font-size: .82rem; color: #111; border-bottom: 1px solid #f3f4f6; vertical-align: middle; }
        tbody tr:last-child td { border-bottom: none; }
        tbody tr:hover td { background: #fafafe; }

        .btn { padding: 7px 14px; border-radius: 6px; font-size: .78rem; font-weight: 600; cursor: pointer; border: none; display: inline-block; }
        .btn-primary { background: #0d1b5e; color: #fff; }
        .btn-primary:hover { background: #1a2d8f; }
        .btn-edit { background: #4f46e5; color: #fff; }
        .btn-edit:hover { background: #4338ca; }
        .btn-hapus { background: #ef4444; color: #fff; }
        .btn-hapus:hover { background: #dc2626; }
        .btn-batal { background: #e5e7eb; color: #374151; }
        .btn-batal:hover { background: #d1d5db; }

        .search-box { display: flex; gap: 8px; align-items: center; }
        .search-input { padding: 8px 14px; border: 1px solid #e5e7eb; border-radius: 6px; font-size: .82rem; outline: none; width: 220px; }
        .search-input:focus { border-color: #4f46e5; }

        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-size: .78rem; font-weight: 600; color: #374151; margin-bottom: 6px; }
        .form-input { width: 100%; padding: 8px 12px; border: 1px solid #e5e7eb; border-radius: 6px; font-size: .82rem; outline: none; }
        .form-input:focus { border-color: #4f46e5; }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .form-actions { display: flex; gap: 10px; margin-top: 8px; }

        .alert { padding: 10px 16px; border-radius: 6px; font-size: .82rem; margin-bottom: 16px; }
        .alert-success { background: #d1fae5; color: #065f46; border: 1px solid #6ee7b7; }
        .alert-danger { background: #fee2e2; color: #991b1b; border: 1px solid #fca5a5; }

        .badge { padding: 3px 10px; border-radius: 20px; font-size: .72rem; font-weight: 600; }
        .badge-aktif { background: #d1fae5; color: #065f46; }
        .badge-nonaktif { background: #fee2e2; color: #991b1b; }

        .aksi-col { display: flex; gap: 6px; }
        .empty-cell { text-align: center; padding: 28px; color: #bbb; font-size: .82rem; }
    </style>
</head>
<body>

<aside class="sidebar">
    <div class="sidebar-logo">
        <div class="logo"><span>P</span>ustaKita</div>
    </div>
    <nav class="sidebar-nav">
        <?php
        $menus = [
            ['href' => 'dashboard_admin.php',   'label' => 'Dashboard',           'icon' => '⊞'],
            ['href' => 'pengelolaan_buku.php',  'label' => 'Pengelolaan Buku',    'icon' => '📖'],
            ['href' => 'pengelolaan_member.php','label' => 'Pengelolaan Member',  'icon' => '👤'],
            ['href' => 'kelola_peminjaman.php',        'label' => 'Kelola Peminjaman',   'icon' => '🕐'],
            ['href' => 'pengembalian.php',      'label' => 'Kelola Pengembalian', 'icon' => '↩'],
            ['href' => 'denda.php',             'label' => 'Kelola Denda',        'icon' => '💰'],
            ['href' => 'laporan.php',           'label' => 'Laporan',             'icon' => '📊'],
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
        <a href="logout.php" class="logout-item">
            <span class="nav-icon">↗</span> Log Out
        </a>
    </div>
</aside>

<div class="main">
    <div class="topbar">
        <div class="topbar-title">Pengelolaan Member</div>
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

        <!-- STAT CARDS -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-card-label">Total Member</div>
                <div class="stat-card-value"><?= $totalMember ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-card-label">Member Aktif</div>
                <div class="stat-card-value"><?= $totalAktif ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-card-label">Member Nonaktif</div>
                <div class="stat-card-value"><?= $totalNonaktif ?></div>
            </div>
        </div>

        <!-- TABEL MEMBER -->
        <div class="card">
            <div class="card-header">
                <span class="card-title">Daftar Member</span>
                <div class="search-box">
                    <form method="GET" action="" style="display:flex;gap:8px">
                        <input type="text" name="cari" class="search-input"
                            placeholder="Cari nama / no. anggota..."
                            value="<?= htmlspecialchars($keyword) ?>">
                        <button type="submit" class="btn btn-batal">Cari</button>
                    </form>
                    <a href="?tambah=1" class="btn btn-primary">+ Tambah Member</a>
                </div>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>#</th>
                        <th>Nama</th>
                        <th>No. Anggota</th>
                        <th>No. Telepon</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($daftarMember && $daftarMember->num_rows > 0):
                    $no = 1;
                    while ($row = $daftarMember->fetch_assoc()): ?>
                    <tr>
                        <td><?= $no++ ?></td>
                        <td><?= htmlspecialchars($row['username']) ?></td>
                        <td><?= htmlspecialchars($row['no_anggota'] ?? '-') ?></td>
                        <td><?= htmlspecialchars($row['no_telepon'] ?? '-') ?></td>
                        <td>
                            <span class="badge <?= ($row['status'] ?? '') === 'aktif' ? 'badge-aktif' : 'badge-nonaktif' ?>">
                                <?= ucfirst($row['status'] ?? 'aktif') ?>
                            </span>
                        </td>
                        <td>
                            <div class="aksi-col">
                                <a href="?edit=<?= $row['id_siswa'] ?>" class="btn btn-edit">Edit</a>
                                <a href="?hapus=<?= $row['id_siswa'] ?>" class="btn btn-hapus"
                                    onclick="return confirm('Yakin hapus member ini?')">Hapus</a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="6" class="empty-cell">Belum ada data member</td></tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- FORM EDIT -->
        <?php if ($editData): ?>
        <div class="card">
            <div class="card-header">
                <span class="card-title">Edit Member</span>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id_siswa" value="<?= $editData['id_siswa'] ?>">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Nama / Username *</label>
                            <input type="text" name="username" class="form-input"
                                value="<?= htmlspecialchars($editData['username']) ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-input"
                                value="<?= htmlspecialchars($editData['email'] ?? '') ?>" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">No. Anggota</label>
                            <input type="text" name="no_anggota" class="form-input"
                                value="<?= htmlspecialchars($editData['no_anggota'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">No. Telepon</label>
                            <input type="text" name="no_telepon" class="form-input"
                                value="<?= htmlspecialchars($editData['no_telepon'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Alamat</label>
                            <input type="text" name="alamat" class="form-input"
                                value="<?= htmlspecialchars($editData['alamat'] ?? '') ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-input">
                                <option value="aktif" <?= ($editData['status'] ?? '') === 'aktif' ? 'selected' : '' ?>>Aktif</option>
                                <option value="nonaktif" <?= ($editData['status'] ?? '') === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                        <a href="pengelolaan_member.php" class="btn btn-batal">Batal</a>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

        <!-- FORM TAMBAH -->
        <?php if ($showTambah && !$editData): ?>
        <div class="card">
            <div class="card-header">
                <span class="card-title">Tambah Member Baru</span>
            </div>
            <div class="card-body">
                <form method="POST" action="">
                    <input type="hidden" name="action" value="tambah">
                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Nama / Username *</label>
                            <input type="text" name="username" class="form-input" placeholder="Nama lengkap" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Email *</label>
                            <input type="email" name="email" class="form-input" placeholder="email@example.com" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">No. Anggota</label>
                            <input type="text" name="no_anggota" class="form-input" placeholder="Nomor anggota">
                        </div>
                        <div class="form-group">
                            <label class="form-label">No. Telepon</label>
                            <input type="text" name="no_telepon" class="form-input" placeholder="08xxxxxxxxxx">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Alamat</label>
                            <input type="text" name="alamat" class="form-input" placeholder="Alamat lengkap">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-input">
                                <option value="aktif">Aktif</option>
                                <option value="nonaktif">Nonaktif</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Simpan Member</button>
                        <a href="pengelolaan_member.php" class="btn btn-batal">Batal</a>
                    </div>
                </form>
            </div>
        </div>
        <?php endif; ?>

    </div>
</div>

</body>
</html>