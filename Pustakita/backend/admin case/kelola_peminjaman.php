<?php
include '../database.php';

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

$member    = new Member($koneksi);
$keyword   = trim($_GET['cari'] ?? '');

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

$pesan         = $_GET['pesan'] ?? '';
$daftarMember  = $member->getSemuaMember($keyword);
$editData      = isset($_GET['edit']) ? $member->getMemberById((int)$_GET['edit']) : null;
$showTambah    = isset($_GET['tambah']);
$totalMember   = $member->getTotalMember();
$totalAktif    = $member->getTotalAktif();
$totalNonaktif = $member->getTotalNonaktif();
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengelolaan Member - PustaKita</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/remixicon@3.5.0/fonts/remixicon.css" rel="stylesheet">
    <style>
        :root {
            --primary: #1a3c5e; --primary-hover: #164e7a; --primary-light: #e6f1fb; --primary-text: #185fa5;
            --danger: #993c1d; --danger-light: #faece7;
            --success: #3b6d11; --success-light: #eaf3de;
            --warning: #854f0b; --warning-light: #faeeda;
            --gray: #5f5e5a; --gray-light: #f1efe8;
            --bg: #f5f7fa; --surface: #ffffff; --border: rgba(0,0,0,.09); --border-strong: rgba(0,0,0,.15);
            --text-primary: #1c1c1a; --text-secondary: #6b6b68; --text-muted: #9b9b98;
            --radius-sm: 6px; --radius-md: 10px; --radius-lg: 14px;
            --shadow-sm: 0 1px 3px rgba(0,0,0,.08); --shadow-md: 0 4px 16px rgba(0,0,0,.10);
            --sidebar-w: 240px; --transition: .18s ease;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Plus Jakarta Sans', sans-serif; background: var(--bg); color: var(--text-primary); font-size: 14px; line-height: 1.6; min-height: 100vh; }
        a { text-decoration: none; color: inherit; }
        button { cursor: pointer; font-family: inherit; }

        /* LAYOUT */
        .app-layout { display: flex; min-height: 100vh; }

        /* SIDEBAR */
        .sidebar { width: var(--sidebar-w); background: #0f1e2e; color: #fff; display: flex; flex-direction: column; position: fixed; top: 0; left: 0; bottom: 0; z-index: 100; overflow-y: auto; }
        .sidebar-brand { padding: 22px 20px 16px; font-size: 19px; font-weight: 700; letter-spacing: -.3px; border-bottom: 1px solid rgba(255,255,255,.07); }
        .sidebar-brand span { color: #60a5fa; }
        .sidebar-nav { padding: 12px 10px; flex: 1; }
        .nav-item { display: flex; align-items: center; gap: 10px; padding: 9px 12px; border-radius: var(--radius-md); color: rgba(255,255,255,.65); font-size: 13.5px; font-weight: 500; transition: background var(--transition), color var(--transition); margin-bottom: 2px; }
        .nav-item:hover { background: rgba(255,255,255,.07); color: #fff; }
        .nav-item.active { background: rgba(96,165,250,.18); color: #93c5fd; }
        .nav-item i { font-size: 17px; width: 20px; text-align: center; }
        .sidebar-footer { padding: 14px 10px; border-top: 1px solid rgba(255,255,255,.07); }

        /* MAIN */
        .main { margin-left: var(--sidebar-w); flex: 1; display: flex; flex-direction: column; }

        /* TOPBAR */
        .topbar { background: var(--surface); border-bottom: 1px solid var(--border); padding: 0 28px; height: 60px; display: flex; align-items: center; justify-content: space-between; position: sticky; top: 0; z-index: 50; }
        .topbar-title { font-size: 18px; font-weight: 700; }
        .topbar-user { display: flex; align-items: center; gap: 10px; font-size: 13px; color: var(--text-secondary); }
        .avatar-circle { width: 36px; height: 36px; border-radius: 50%; background: var(--primary-light); color: var(--primary-text); font-weight: 600; font-size: 12px; display: flex; align-items: center; justify-content: center; }

        /* PAGE CONTENT */
        .page-content { padding: 28px 32px; flex: 1; }

        /* STAT CARDS */
        .stats-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(130px, 1fr)); gap: 14px; margin-bottom: 26px; }
        .stat-card { border-radius: var(--radius-lg); padding: 16px 20px; position: relative; overflow: hidden; }
        .stat-card.blue  { background: var(--primary); color: #fff; }
        .stat-card.red   { background: #b34022; color: #fff; }
        .stat-card.gray  { background: var(--gray-light); color: var(--text-primary); }
        .stat-card-val   { font-size: 30px; font-weight: 800; line-height: 1; }
        .stat-card-label { font-size: 11.5px; margin-top: 5px; opacity: .8; }
        .stat-card::after { content: ''; position: absolute; width: 70px; height: 70px; border-radius: 50%; background: rgba(255,255,255,.07); bottom: -20px; right: -10px; }

        /* SEARCH */
        .toolbar { display: flex; align-items: center; justify-content: space-between; margin-bottom: 14px; flex-wrap: wrap; gap: 10px; }
        .search-wrap { position: relative; }
        .search-wrap i { position: absolute; left: 11px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 16px; pointer-events: none; }
        .search-input { padding: 9px 14px 9px 36px; border: 1px solid var(--border-strong); border-radius: var(--radius-md); background: var(--surface); font-size: 13px; color: var(--text-primary); outline: none; width: 260px; }
        .search-input:focus { border-color: var(--primary-text); box-shadow: 0 0 0 3px rgba(24,95,165,.12); }

        /* TABLE */
        .table-wrap { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); overflow: hidden; margin-bottom: 24px; box-shadow: var(--shadow-sm); }
        .data-table { width: 100%; font-size: 13px; border-collapse: collapse; }
        .data-table thead tr { background: #f8f9fb; border-bottom: 1px solid var(--border); }
        .data-table th { padding: 11px 16px; text-align: left; font-weight: 600; color: var(--text-secondary); font-size: 11.5px; text-transform: uppercase; letter-spacing: .4px; white-space: nowrap; }
        .data-table td { padding: 12px 16px; border-bottom: 1px solid var(--border); vertical-align: middle; }
        .data-table tbody tr:last-child td { border-bottom: none; }
        .data-table tbody tr:hover { background: #fafbfc; }
        .td-id { color: var(--text-muted); font-size: 11.5px; font-family: monospace; }
        .no-data { text-align: center; padding: 28px; color: var(--text-muted); }

        /* STATUS BADGE */
        .status-badge { display: inline-flex; align-items: center; gap: 5px; padding: 3px 10px; border-radius: 99px; font-size: 11.5px; font-weight: 600; white-space: nowrap; }
        .status-badge::before { content: ''; width: 6px; height: 6px; border-radius: 50%; background: currentColor; opacity: .6; }
        .status-aktif    { background: var(--success-light); color: var(--success); }
        .status-nonaktif { background: var(--danger-light);  color: var(--danger); }

        /* BUTTONS */
        .btn { display: inline-flex; align-items: center; gap: 5px; padding: 6px 14px; border: none; border-radius: var(--radius-sm); font-size: 12.5px; font-weight: 600; transition: opacity var(--transition); }
        .btn:hover { opacity: .86; }
        .btn-primary { background: var(--primary); color: #fff; }
        .btn-danger  { background: var(--danger);  color: #fff; }
        .btn-ghost   { background: var(--gray-light); color: var(--gray); }
        .btn-sm { padding: 4px 11px; font-size: 12px; }

        /* FORM CARD */
        .form-card { background: var(--surface); border: 1px solid var(--border); border-radius: var(--radius-lg); overflow: hidden; margin-bottom: 24px; box-shadow: var(--shadow-sm); }
        .form-card-header { padding: 16px 24px; border-bottom: 1px solid var(--border); font-size: 15px; font-weight: 600; }
        .form-card-body { padding: 20px 24px; }
        .form-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
        .form-group { display: flex; flex-direction: column; gap: 5px; }
        .form-label { font-size: 12px; font-weight: 600; color: var(--text-secondary); }
        .form-input { padding: 8px 12px; border: 1px solid var(--border-strong); border-radius: var(--radius-sm); font-size: 13px; outline: none; font-family: inherit; }
        .form-input:focus { border-color: var(--primary-text); box-shadow: 0 0 0 3px rgba(24,95,165,.12); }
        .form-actions { display: flex; gap: 10px; margin-top: 18px; }

        /* ALERT */
        .alert { padding: 11px 16px; border-radius: var(--radius-md); font-size: 13px; margin-bottom: 18px; display: flex; align-items: center; gap: 8px; }
        .alert-success { background: var(--success-light); color: var(--success); }
        .alert-danger  { background: var(--danger-light);  color: var(--danger); }

        /* SECTION TITLE */
        .section-title { font-size: 15px; font-weight: 600; margin-bottom: 12px; }
    </style>
</head>
<body>
<div class="app-layout">

    <!-- SIDEBAR -->
    <aside class="sidebar">
        <div class="sidebar-brand"><span>P</span>ustaKita</div>
        <nav class="sidebar-nav">
            <?php
            $menus = [
                ['href' => 'dashboard_admin.php',  'label' => 'Dashboard',           'icon' => '⊞'],
                ['href' => 'pengelolaan_buku.php', 'label' => 'Pengelolaan Buku',    'icon' => '📖'],
                ['href' => 'pengelolaan_member.php',           'label' => 'Pengelolaan Member',  'icon' => '👤'],
                ['href' => 'kelola_peminjaman.php',       'label' => 'Kelola Peminjaman',   'icon' => '🕐'],
                ['href' => 'pengembalian.php',     'label' => 'Kelola Pengembalian', 'icon' => '↩'],
                ['href' => 'denda.php',            'label' => 'Kelola Denda',        'icon' => '💰'],
                ['href' => 'laporan.php',          'label' => 'Laporan',              'icon' => 'ri-bar-chart-line'],
            ];
            foreach ($menus as $menu):
                $active = basename($_SERVER['PHP_SELF']) === $menu['href'] ? 'active' : '';
            ?>
            <a href="<?= $menu['href'] ?>" class="nav-item <?= $active ?>">
                <i class="<?= $menu['icon'] ?>"></i>
                <?= $menu['label'] ?>
            </a>
            <?php endforeach; ?>
        </nav>
        <div class="sidebar-footer">
            <a href="Logout.php" class="nav-item">
                <i class="ri-logout-box-line"></i> Log Out
            </a>
        </div>
    </aside>

    <!-- MAIN -->
    <div class="main">
        <div class="topbar">
            <div class="topbar-title">Pengelolaan Member</div>
            <div class="topbar-user">
                <div class="avatar-circle"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
                <span><?= htmlspecialchars($_SESSION['username']) ?></span>
            </div>
        </div>

        <div class="page-content">

            <?php if ($pesan): ?>
            <div class="alert <?= str_contains($pesan, 'Gagal') ? 'alert-danger' : 'alert-success' ?>">
                <i class="ri-<?= str_contains($pesan, 'Gagal') ? 'error-warning' : 'checkbox-circle' ?>-line"></i>
                <?= htmlspecialchars($pesan) ?>
            </div>
            <?php endif; ?>

            <!-- STAT CARDS -->
            <div class="stats-grid">
                <div class="stat-card blue">
                    <div class="stat-card-val"><?= $totalMember ?></div>
                    <div class="stat-card-label">Total Member</div>
                </div>
                <div class="stat-card gray">
                    <div class="stat-card-val"><?= $totalAktif ?></div>
                    <div class="stat-card-label">Member Aktif</div>
                </div>
                <div class="stat-card red">
                    <div class="stat-card-val"><?= $totalNonaktif ?></div>
                    <div class="stat-card-label">Member Nonaktif</div>
                </div>
            </div>

            <!-- TOOLBAR -->
            <div class="toolbar">
                <div class="section-title">Daftar Member</div>
                <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap">
                    <form method="GET" action="">
                        <div class="search-wrap">
                            <i class="ri-search-line"></i>
                            <input type="text" name="cari" class="search-input"
                                placeholder="Cari nama / no. anggota..."
                                value="<?= htmlspecialchars($keyword) ?>">
                        </div>
                    </form>
                    <a href="?tambah=1" class="btn btn-primary">
                        <i class="ri-user-add-line"></i> Tambah Member
                    </a>
                </div>
            </div>

            <!-- TABEL -->
            <div class="table-wrap">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Nama Member</th>
                            <th>No. Anggota</th>
                            <th>No. Telepon</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($daftarMember && $daftarMember->num_rows > 0):
                        while ($row = $daftarMember->fetch_assoc()): ?>
                        <tr>
                            <td class="td-id">#<?= str_pad($row['id_siswa'], 3, '0', STR_PAD_LEFT) ?></td>
                            <td>
                                <div style="display:flex;align-items:center;gap:10px">
                                    <div class="avatar-circle" style="width:30px;height:30px;font-size:11px">
                                        <?= strtoupper(substr($row['username'], 0, 1)) ?>
                                    </div>
                                    <span style="font-weight:500"><?= htmlspecialchars($row['username']) ?></span>
                                </div>
                            </td>
                            <td><?= htmlspecialchars($row['no_anggota'] ?? '-') ?></td>
                            <td><?= htmlspecialchars($row['no_telepon'] ?? '-') ?></td>
                            <td>
                                <span class="status-badge <?= ($row['status'] ?? 'aktif') === 'aktif' ? 'status-aktif' : 'status-nonaktif' ?>">
                                    <?= ucfirst($row['status'] ?? 'aktif') ?>
                                </span>
                            </td>
                            <td>
                                <div style="display:flex;gap:6px">
                                    <a href="?edit=<?= $row['id_siswa'] ?>" class="btn btn-primary btn-sm">
                                        <i class="ri-edit-line"></i> Edit
                                    </a>
                                    <a href="?hapus=<?= $row['id_siswa'] ?>" class="btn btn-danger btn-sm"
                                        onclick="return confirm('Yakin hapus member ini?')">
                                        <i class="ri-delete-bin-line"></i> Hapus
                                    </a>
                                </div>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="6" class="no-data">Belum ada data member</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- FORM EDIT -->
            <?php if ($editData): ?>
            <div class="form-card">
                <div class="form-card-header"><i class="ri-edit-line"></i> Edit Member</div>
                <div class="form-card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="edit">
                        <input type="hidden" name="id_siswa" value="<?= $editData['id_siswa'] ?>">
                        <div class="form-grid">
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
                                    <option value="aktif"    <?= ($editData['status'] ?? '') === 'aktif'    ? 'selected' : '' ?>>Aktif</option>
                                    <option value="nonaktif" <?= ($editData['status'] ?? '') === 'nonaktif' ? 'selected' : '' ?>>Nonaktif</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line"></i> Simpan Perubahan
                            </button>
                            <a href="pengelolaan_member.php" class="btn btn-ghost">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

            <!-- FORM TAMBAH -->
            <?php if ($showTambah && !$editData): ?>
            <div class="form-card">
                <div class="form-card-header"><i class="ri-user-add-line"></i> Tambah Member Baru</div>
                <div class="form-card-body">
                    <form method="POST" action="">
                        <input type="hidden" name="action" value="tambah">
                        <div class="form-grid">
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
                            <button type="submit" class="btn btn-primary">
                                <i class="ri-save-line"></i> Simpan Member
                            </button>
                            <a href="pengelolaan_member.php" class="btn btn-ghost">Batal</a>
                        </div>
                    </form>
                </div>
            </div>
            <?php endif; ?>

        </div>
    </div>
</div>
</body>
</html>