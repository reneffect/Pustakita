<?php
include 'database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek login & role
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: Login_admin.php");
    exit();
}

// Cek session token
class AuthCheck {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function check() {
        $stmt = $this->db->prepare("SELECT session_token FROM admin WHERE id = ?");
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

// Ambil data statistik
class Dashboard {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getTotalBuku() {
        $result = $this->db->query("SELECT COUNT(*) as total FROM buku");
        return $result->fetch_assoc()['total'];
    }

    public function getTotalMember() {
        $result = $this->db->query("SELECT COUNT(*) as total FROM members");
        return $result->fetch_assoc()['total'];
    }

    public function getPeminjamanAktif() {
        $result = $this->db->query("SELECT COUNT(*) as total FROM peminjaman WHERE status = 'aktif'");
        return $result->fetch_assoc()['total'];
    }

    public function getAktivitasTerbaru() {
        return $this->db->query("
            SELECT p.id, m.nama, b.judul, p.tanggal_pinjam 
            FROM peminjaman p
            JOIN members m ON p.member_id = m.id
            JOIN buku b ON p.buku_id = b.id
            ORDER BY p.tanggal_pinjam DESC 
            LIMIT 5
        ");
    }

    public function getBukuPopuler() {
        return $this->db->query("
            SELECT b.judul, b.pengarang, COUNT(p.id) as dipinjam
            FROM peminjaman p
            JOIN buku b ON p.buku_id = b.id
            GROUP BY b.id
            ORDER BY dipinjam DESC 
            LIMIT 4
        ");
    }
}

// Jalankan
$authCheck = new AuthCheck($koneksi);
$authCheck->check();

$dashboard = new Dashboard($koneksi);
$total_buku       = $dashboard->getTotalBuku();
$total_member     = $dashboard->getTotalMember();
$peminjaman_aktif = $dashboard->getPeminjamanAktif();
$aktivitas        = $dashboard->getAktivitasTerbaru();
$populer          = $dashboard->getBukuPopuler();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="/Pustakita_upk_team.1/Pustakita/style_css/tampilan.css">
    <title>Dashboard Admin - PustaKita</title>
</head>
<body class="bg-gray-100">

    <!-- Sidebar -->
    <aside class="w-64 bg-white h-screen fixed shadow-md flex flex-col">
        <div class="p-6 flex items-center gap-2">
            <svg viewBox="0 0 24 24" width="24" height="24" stroke="#04005c" stroke-width="2" fill="none" stroke-linecap="round" stroke-linejoin="round">
                <path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"></path>
                <path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"></path>
            </svg>
            <span class="font-bold text-lg text-[#04005c]"><span class="text-blue-900">P</span>ustaKita</span>
        </div>

        <nav class="flex-1 px-4 space-y-1">
            <?php
            $menus = [
                ['href' => 'dashboard_admin.php', 'label' => 'Dashboard', 'icon' => '🏠'],
                ['href' => 'buku.php',            'label' => 'Pengelolaan Buku', 'icon' => '📖'],
                ['href' => 'member.php',          'label' => 'Pengelolaan Member', 'icon' => '👤'],
                ['href' => 'peminjaman.php',      'label' => 'Kelola Peminjaman', 'icon' => '🕐'],
                ['href' => 'pengembalian.php',    'label' => 'Kelola Pengembalian', 'icon' => '↩️'],
                ['href' => 'denda.php',           'label' => 'Kelola Denda', 'icon' => '💰'],
                ['href' => 'laporan.php',         'label' => 'Laporan', 'icon' => '📊'],
            ];
            foreach ($menus as $menu):
                $active = basename($_SERVER['PHP_SELF']) === $menu['href']
                          ? 'bg-indigo-600 text-white'
                          : 'text-gray-600 hover:bg-indigo-50';
            ?>
            <a href="<?= $menu['href'] ?>" class="flex items-center gap-3 px-4 py-2 rounded-lg <?= $active ?>">
                <span><?= $menu['icon'] ?></span>
                <span class="text-sm"><?= $menu['label'] ?></span>
            </a>
            <?php endforeach; ?>
        </nav>

        <div class="p-4 border-t">
            <div class="flex items-center gap-2 px-4 py-2 text-sm text-gray-600 mb-2">
                👤 <span><?= htmlspecialchars($_SESSION['username']) ?></span>
            </div>
            <a href="logout.php" class="flex items-center gap-3 px-4 py-2 text-red-500 hover:bg-red-50 rounded-lg text-sm">
                🚪 Log Out
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <main class="ml-64 p-8">

        <!-- Header -->
        <div class="flex justify-between items-center mb-6">
            <h1 class="text-2xl font-bold">Dashboard</h1>
            <div class="flex items-center gap-2 text-gray-600 text-sm">
                👤 <span><?= htmlspecialchars($_SESSION['username']) ?></span>
            </div>
        </div>

        <!-- Greeting -->
        <p class="text-xl font-semibold mb-1">Halo, <?= htmlspecialchars($_SESSION['username']) ?>!</p>
        <p class="text-gray-500 mb-6 text-sm">Silakan kelola dan pantau aktivitas dengan bijak.</p>

        <!-- Statistik -->
        <div class="grid grid-cols-3 gap-4 mb-8">
            <div class="bg-indigo-600 text-white rounded-xl p-5">
                <p class="text-sm mb-1">Total Buku</p>
                <p class="text-3xl font-bold"><?= number_format($total_buku) ?></p>
            </div>
            <div class="bg-indigo-600 text-white rounded-xl p-5">
                <p class="text-sm mb-1">Peminjaman Aktif</p>
                <p class="text-3xl font-bold"><?= number_format($peminjaman_aktif) ?></p>
            </div>
            <div class="bg-indigo-600 text-white rounded-xl p-5">
                <p class="text-sm mb-1">Total Member</p>
                <p class="text-3xl font-bold"><?= number_format($total_member) ?></p>
            </div>
        </div>

        <!-- Aktivitas Terbaru -->
        <div class="bg-white rounded-xl p-6 mb-6 shadow-sm">
            <h2 class="font-semibold text-lg mb-4">Aktivitas Terbaru</h2>
            <table class="w-full text-sm">
                <tbody>
                <?php if ($aktivitas && $aktivitas->num_rows > 0): ?>
                    <?php while ($row = $aktivitas->fetch_assoc()): ?>
                    <tr class="border-b last:border-0">
                        <td class="py-2 text-gray-500">Peminjaman #<?= str_pad($row['id'], 3, '0', STR_PAD_LEFT) ?></td>
                        <td class="py-2 text-gray-400">—</td>
                        <td class="py-2"><?= htmlspecialchars($row['judul']) ?></td>
                        <td class="py-2 text-right text-gray-400"><?= date('d M Y', strtotime($row['tanggal_pinjam'])) ?></td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="py-4 text-center text-gray-400">Belum ada aktivitas</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Buku Populer -->
        <div class="bg-white rounded-xl p-6 shadow-sm">
            <h2 class="font-semibold text-lg mb-4">Buku Populer Minggu Ini</h2>
            <table class="w-full text-sm">
                <thead class="text-gray-400 border-b">
                    <tr>
                        <th class="text-left py-2">#</th>
                        <th class="text-left py-2">Judul</th>
                        <th class="text-left py-2">Pengarang</th>
                        <th class="text-left py-2">Dipinjam</th>
                    </tr>
                </thead>
                <tbody>
                <?php if ($populer && $populer->num_rows > 0): ?>
                    <?php $no = 1; while ($row = $populer->fetch_assoc()): ?>
                    <tr class="border-b last:border-0">
                        <td class="py-2"><?= $no++ ?></td>
                        <td class="py-2"><?= htmlspecialchars($row['judul']) ?></td>
                        <td class="py-2"><?= htmlspecialchars($row['pengarang']) ?></td>
                        <td class="py-2"><?= $row['dipinjam'] ?>x</td>
                    </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" class="py-4 text-center text-gray-400">Belum ada data</td>
                    </tr>
                <?php endif; ?>
                </tbody>
            </table>
        </div>

    </main>

</body>
</html>