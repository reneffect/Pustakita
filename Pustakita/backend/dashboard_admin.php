<?php
include 'database.php';
session_start(); // fix typo

class Dashboard {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function getTotalBuku() {
        $result = $this->db->query("SELECT COUNT(*) as total FROM books");
        return $result->fetch_assoc()['total'];
    }

    public function getTotalMember() {
        $result = $this->db->query("SELECT COUNT(*) as total FROM members");
        return $result->fetch_assoc()['total'];
    }

    public function getPeminjamanAktif() {
        $result = $this->db->query("SELECT COUNT(*) as total FROM loans WHERE status='aktif'");
        return $result->fetch_assoc()['total'];
    }

    public function getAktivitasTerbaru() {
        return $this->db->query("
            SELECT l.id, m.nama, b.judul, l.tanggal_pinjam 
            FROM loans l
            JOIN members m ON l.member_id = m.id
            JOIN books b ON l.book_id = b.id
            ORDER BY l.tanggal_pinjam DESC LIMIT 5
        ");
    }

    public function getBukuPopuler() {
        return $this->db->query("
            SELECT b.judul, b.pengarang, COUNT(l.id) as dipinjam
            FROM loans l
            JOIN books b ON l.book_id = b.id
            GROUP BY b.id
            ORDER BY dipinjam DESC LIMIT 4
        ");
    }
}

// Cek login
if (!isset($_SESSION['user'])) {
    header('Location: login.php');
    exit;
}

$dashboard = new Dashboard($conn);

$total_buku        = $dashboard->getTotalBuku();
$total_member      = $dashboard->getTotalMember();
$peminjaman_aktif  = $dashboard->getPeminjamanAktif();
$aktivitas         = $dashboard->getAktivitasTerbaru();
$populer           = $dashboard->getBukuPopuler();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Dashboard - Pustakita</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100">

<?php include 'sidebar.php'; ?>

<main class="ml-64 p-8">
    <!-- Header -->
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-2xl font-bold">Dashboard</h1>
        <div class="flex items-center gap-2 text-gray-600">
            👤 <span><?= htmlspecialchars($_SESSION['user']) ?></span>
        </div>
    </div>

    <!-- Greeting -->
    <p class="text-xl font-semibold mb-1">Halo, <?= htmlspecialchars($_SESSION['user']) ?>!</p>
    <p class="text-gray-500 mb-6">Silakan kelola dan pantau aktivitas dengan bijak.</p>

    <!-- Statistik -->
    <div class="grid grid-cols-3 gap-4 mb-8">
        <div class="bg-indigo-600 text-white rounded-xl p-5">
            <p class="text-sm">Total Buku</p>
            <p class="text-3xl font-bold"><?= number_format($total_buku) ?></p>
        </div>
        <div class="bg-indigo-600 text-white rounded-xl p-5">
            <p class="text-sm">Peminjaman Aktif</p>
            <p class="text-3xl font-bold"><?= $peminjaman_aktif ?></p>
        </div>
        <div class="bg-indigo-600 text-white rounded-xl p-5">
            <p class="text-sm">Total Member</p>
            <p class="text-3xl font-bold"><?= number_format($total_member) ?></p>
        </div>
    </div>

    <!-- Aktivitas Terbaru -->
    <div class="bg-white rounded-xl p-6 mb-6 shadow-sm">
        <h2 class="font-semibold text-lg mb-4">Aktivitas Terbaru</h2>
        <table class="w-full text-sm">
            <tbody>
            <?php while ($row = $aktivitas->fetch_assoc()): ?>
                <tr class="border-b last:border-0">
                    <td class="py-2 text-gray-500">Peminjaman #<?= str_pad($row['id'], 3, '0', STR_PAD_LEFT) ?></td>
                    <td class="py-2">—</td>
                    <td class="py-2"><?= htmlspecialchars($row['judul']) ?></td>
                    <td class="py-2 text-right text-gray-400"><?= date('d M Y', strtotime($row['tanggal_pinjam'])) ?></td>
                </tr>
            <?php endwhile; ?>
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
            <?php $no = 1; while ($row = $populer->fetch_assoc()): ?>
                <tr class="border-b last:border-0">
                    <td class="py-2"><?= $no++ ?></td>
                    <td class="py-2"><?= htmlspecialchars($row['judul']) ?></td>
                    <td class="py-2"><?= htmlspecialchars($row['pengarang']) ?></td>
                    <td class="py-2"><?= $row['dipinjam'] ?>x</td>
                </tr>
            <?php endwhile; ?>
            </tbody>
        </table>
    </div>
</main>

</body>
</html>