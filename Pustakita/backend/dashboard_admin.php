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
    <link rel="stylesheet" herf="/Pustakita_upk_team.1/Pustakita/style_css/dashboard_admin.css">
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