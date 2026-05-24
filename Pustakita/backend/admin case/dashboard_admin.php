<?php
include 'database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: Login_admin.php");
    exit();
}

class AuthCheck {
    private $db;
    public function __construct($db) { $this->db = $db; }
    public function check() {
        $stmt = $this->db->prepare("SELECT session_token FROM admin WHERE id_admin = ?");
        $stmt->bind_param("i", $_SESSION['user_id']);
        $stmt->execute();
        $admin = $stmt->get_result()->fetch_assoc();
        if (!$admin || $admin['session_token'] !== $_SESSION['token']) {
            session_destroy();
            header("Location: Login_admin.php?pesan=sesi_digantikan");
            exit();
        }
    }
}

class Dashboard {
    private $db;
    public function __construct($db) { $this->db = $db; }

    public function getTotalBuku() {
        $result = $this->db->query("SELECT COUNT(*) as total FROM buku");
        return $result->fetch_assoc()['total'];
    }
    public function getTotalMember() {
        $result = $this->db->query("SELECT COUNT(*) as total FROM siswa");
        return $result->fetch_assoc()['total'];
    }
    public function getPeminjamanAktif() {
        $result = $this->db->query("SELECT COUNT(*) as total FROM peminjaman WHERE status = 'dipinjam'");
        return $result->fetch_assoc()['total'];
    }
    public function getAktivitasTerbaru() {
        return $this->db->query("
            SELECT p.id_peminjaman, m.username, b.judul, p.tgl_pinjam
            FROM peminjaman p
            JOIN siswa m ON p.siswa_id = m.id_siswa
            JOIN buku b ON p.buku_id = b.id_buku
            ORDER BY p.tgl_pinjam DESC
            LIMIT 5
        ");
    }
    public function getBukuPopuler() {
        return $this->db->query("
            SELECT b.judul, b.penulis, COUNT(p.id_peminjaman) as dipinjam
            FROM peminjaman p
            JOIN buku b ON p.buku_id = b.id_buku
            GROUP BY b.id_buku, b.judul, b.penulis
            ORDER BY dipinjam DESC
            LIMIT 4
        ");
    }
}

$authCheck = new AuthCheck($koneksi);
$authCheck->check();

$dashboard        = new Dashboard($koneksi);
$total_buku       = $dashboard->getTotalBuku();
$total_member     = $dashboard->getTotalMember();
$peminjaman_aktif = $dashboard->getPeminjamanAktif();
$aktivitas        = $dashboard->getAktivitasTerbaru();
$populer          = $dashboard->getBukuPopuler();

// Hitung selisih hari untuk label tanggal
function tglLabel($tgl) {
    $diff = (int) floor((time() - strtotime($tgl)) / 86400);
    if ($diff === 0) return 'Hari Ini';
    if ($diff === 1) return 'Kemarin';
    return $diff . ' Hari lalu';
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: 'Inter', sans-serif;
    background: #f4f4f8;
    color: #111;
    min-height: 100vh;
    display: flex;
}

a { text-decoration: none; color: inherit; }

/* ── SIDEBAR ── */
.sidebar {
    width: 220px;
    min-height: 100vh;
    background: #0d0d1a;
    display: flex;
    flex-direction: column;
    position: fixed;
    top: 0; left: 0;
    height: 100vh;
}

.sidebar-logo {
    padding: 28px 24px 24px;
    border-bottom: 1px solid rgba(255,255,255,.07);
}

.sidebar-logo .logo {
    font-size: 1.15rem;
    font-weight: 700;
    color: #fff;
    letter-spacing: -.2px;
}

.sidebar-logo .logo span {
    font-style: italic;
    color: #fff;
}

.sidebar-nav {
    flex: 1;
    padding: 12px 0;
    overflow-y: auto;
}

.nav-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 11px 24px;
    font-size: .82rem;
    font-weight: 500;
    color: rgba(255,255,255,.5);
    transition: background .15s, color .15s;
    cursor: pointer;
}

.nav-item:hover {
    background: rgba(255,255,255,.06);
    color: rgba(255,255,255,.85);
}

.nav-item.active {
    background: #1a1a3e;
    color: #fff;
    font-weight: 600;
    border-left: 3px solid #4f46e5;
    padding-left: 21px;
}

.nav-icon { font-size: .9rem; width: 18px; text-align: center; }

.sidebar-footer {
    padding: 16px 0;
    border-top: 1px solid rgba(255,255,255,.07);
}

.logout-item {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 11px 24px;
    font-size: .82rem;
    font-weight: 500;
    color: rgba(255,255,255,.4);
    transition: background .15s, color .15s;
}

.logout-item:hover {
    background: rgba(239,68,68,.1);
    color: #f87171;
}

/* ── MAIN ── */
.main {
    margin-left: 220px;
    flex: 1;
    min-height: 100vh;
    display: flex;
    flex-direction: column;
}

/* ── TOPBAR ── */
.topbar {
    background: #fff;
    padding: 0 32px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    border-bottom: 1px solid #ebebf0;
    position: sticky;
    top: 0;
    z-index: 10;
}

.topbar-title {
    font-size: 1.3rem;
    font-weight: 700;
    color: #0d0d1a;
    letter-spacing: -.3px;
}

.topbar-user {
    display: flex;
    align-items: center;
    gap: 8px;
}

.topbar-avatar {
    width: 34px; height: 34px;
    border-radius: 50%;
    background: #0d0d1a;
    display: flex; align-items: center; justify-content: center;
    font-size: .8rem; font-weight: 700; color: #fff;
}

.topbar-username {
    font-size: .82rem;
    font-weight: 600;
    color: #0d0d1a;
}

/* ── CONTENT ── */
.content {
    padding: 28px 32px;
    flex: 1;
}

.greeting-title {
    font-size: 1.4rem;
    font-weight: 700;
    color: #0d0d1a;
    margin-bottom: 4px;
}

.greeting-sub {
    font-size: .82rem;
    color: #888;
    margin-bottom: 24px;
}

/* ── STAT CARDS ── */
.stats-row {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    margin-bottom: 28px;
}

.stat-card {
    background: #0d1b5e;
    border-radius: 10px;
    padding: 20px 22px;
    color: #fff;
    position: relative;
    overflow: hidden;
}

.stat-card::after {
    content: '';
    position: absolute;
    top: -20px; right: -20px;
    width: 80px; height: 80px;
    border-radius: 50%;
    background: rgba(255,255,255,.05);
}

.stat-card-label {
    font-size: .72rem;
    font-weight: 600;
    color: rgba(255,255,255,.6);
    text-transform: uppercase;
    letter-spacing: .06em;
    margin-bottom: 8px;
}

.stat-card-value {
    font-size: 2rem;
    font-weight: 800;
    color: #fff;
    letter-spacing: -1px;
    line-height: 1;
}

/* ── SECTION TITLE ── */
.section-title {
    font-size: .95rem;
    font-weight: 700;
    color: #0d0d1a;
    margin-bottom: 12px;
}

/* ── AKTIVITAS CARD ── */
.card {
    background: #fff;
    border-radius: 10px;
    border: 1px solid #ebebf0;
    overflow: hidden;
    margin-bottom: 24px;
}

.card-inner { padding: 20px 24px 4px; }

.act-table {
    width: 100%;
    border-collapse: collapse;
}

.act-table td {
    padding: 10px 0;
    font-size: .82rem;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
}

.act-table tr:last-child td { border-bottom: none; }

.act-id {
    color: #555;
    font-weight: 500;
    width: 130px;
}

.act-sep {
    color: #ccc;
    width: 30px;
    text-align: center;
}

.act-judul {
    color: #111;
    font-weight: 500;
}

.act-date {
    text-align: right;
    color: #aaa;
    font-size: .78rem;
    white-space: nowrap;
}

/* ── BUKU POPULER ── */
.pop-table {
    width: 100%;
    border-collapse: collapse;
}

.pop-table th {
    font-size: .7rem;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .06em;
    color: #aaa;
    padding: 10px 0 10px 0;
    border-bottom: 1px solid #ebebf0;
    text-align: left;
}

.pop-table th:first-child,
.pop-table td:first-child { width: 32px; }

.pop-table th:last-child,
.pop-table td:last-child { text-align: right; }

.pop-table td {
    padding: 11px 0;
    font-size: .82rem;
    color: #111;
    border-bottom: 1px solid #f3f4f6;
    vertical-align: middle;
}

.pop-table tbody tr:last-child td { border-bottom: none; }

.pop-table tbody tr:hover td { background: #fafafe; }

.pop-num {
    font-size: .78rem;
    font-weight: 700;
    color: #aaa;
}

.pop-penulis { color: #888; font-size: .78rem; }

.pop-count {
    font-size: .78rem;
    font-weight: 600;
    color: #4f46e5;
}

.empty-row td {
    padding: 32px 0;
    text-align: center;
    color: #bbb;
    font-size: .82rem;
}

/* ── Scrollbar ── */
::-webkit-scrollbar { width: 4px; }
::-webkit-scrollbar-thumb { background: #ddd; border-radius: 10px; }
    </style>
    <title>Dashboard Admin - PustaKita</title>
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
            ['href' => 'dashboard_admin.php', 'label' => 'Dashboard',          'icon' => '⊞'],
            ['href' => 'buku.php',            'label' => 'Pengelolaan Buku',    'icon' => '📖'],
            ['href' => 'member.php',          'label' => 'Pengelolaan Member',  'icon' => '👤'],
            ['href' => 'peminjaman.php',      'label' => 'Kelola Peminjaman',   'icon' => '🕐'],
            ['href' => 'pengembalian.php',    'label' => 'Kelola Pengembalian', 'icon' => '↩'],
            ['href' => 'denda.php',           'label' => 'Kelola Denda',        'icon' => '💰'],
            ['href' => 'laporan.php',         'label' => 'Laporan',             'icon' => '📊'],
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

<!-- MAIN -->
<div class="main">

    <!-- Topbar -->
    <div class="topbar">
        <div class="topbar-title">Dashboard</div>
        <div class="topbar-user">
            <div class="topbar-avatar"><?= strtoupper(substr($_SESSION['username'], 0, 1)) ?></div>
            <span class="topbar-username"><?= htmlspecialchars($_SESSION['username']) ?></span>
        </div>
    </div>

    <!-- Content -->
    <div class="content">

        <div class="greeting-title">Halo, <?= htmlspecialchars(ucfirst($_SESSION['username'])) ?>!</div>
        <div class="greeting-sub">Welcome Admin! Silakan kelola dan pantau aktivitas dengan bijak.</div>

        <!-- Stat Cards -->
        <div class="stats-row">
            <div class="stat-card">
                <div class="stat-card-label">Total Buku</div>
                <div class="stat-card-value"><?= number_format($total_buku) ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-card-label">Peminjaman Aktif</div>
                <div class="stat-card-value"><?= number_format($peminjaman_aktif) ?></div>
            </div>
            <div class="stat-card">
                <div class="stat-card-label">Total Member</div>
                <div class="stat-card-value"><?= number_format($total_member) ?></div>
            </div>
        </div>

        <!-- Aktivitas Terbaru -->
        <div class="section-title">Aktivitas Terbaru</div>
        <div class="card">
            <div class="card-inner">
                <table class="act-table">
                    <tbody>
                    <?php if ($aktivitas && $aktivitas->num_rows > 0): ?>
                        <?php while ($row = $aktivitas->fetch_assoc()): ?>
                        <tr>
                            <td class="act-id">Peminjaman #<?= str_pad($row['id_peminjaman'], 3, '0', STR_PAD_LEFT) ?></td>
                            <td class="act-sep">—</td>
                            <td class="act-judul"><?= htmlspecialchars($row['judul']) ?></td>
                            <td class="act-date"><?= tglLabel($row['tgl_pinjam']) ?></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr><td colspan="4" style="padding:28px 0;text-align:center;color:#bbb;font-size:.82rem">Belum ada aktivitas</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Buku Populer -->
        <div class="section-title">Buku Populer Minggu Ini</div>
        <div class="card">
            <div class="card-inner">
                <table class="pop-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Judul</th>
                            <th>Pengarang</th>
                            <th>Dipinjam</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if ($populer && $populer->num_rows > 0): ?>
                        <?php $no = 1; while ($row = $populer->fetch_assoc()): ?>
                        <tr>
                            <td><span class="pop-num"><?= $no++ ?></span></td>
                            <td><?= htmlspecialchars($row['judul']) ?></td>
                            <td class="pop-penulis"><?= htmlspecialchars($row['penulis']) ?></td>
                            <td><span class="pop-count"><?= $row['dipinjam'] ?>x</span></td>
                        </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr class="empty-row"><td colspan="4">Belum ada data</td></tr>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

    </div><!-- /content -->
</div><!-- /main -->

</body>
</html>