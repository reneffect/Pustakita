<?php
// ==========================================
// KONEKSI DATABASE
// ==========================================
$host = "localhost";
$user = "root";
$pass = "";
$dbname   = "pustakita";

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// ==========================================
// FILTER PENCARIAN & BULAN
// ==========================================
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$bulan_filter = isset($_GET['bulan']) ? $conn->real_escape_string($_GET['bulan']) : date('Y-m');
$filter_sql = " AND DATE_FORMAT(dp.tgl_pinjam, '%Y-%m') = '$bulan_filter' ";

if (!empty($search)) {
    $filter_sql .= " AND (s.username LIKE '%$search%' OR b.judul LIKE '%$search%') ";
}

// ==========================================
// STATISTIK KARTU ATAS (Diperbaiki ke detail_peminjaman)
// ==========================================
$stat_aktif = $conn->query("SELECT COUNT(*) as total FROM detail_peminjaman WHERE status = 'dipinjam'")->fetch_assoc()['total'];
$stat_terlambat = $conn->query("SELECT COUNT(*) as total FROM detail_peminjaman WHERE status = 'dipinjam' AND tgl_pengembalian < CURDATE()")->fetch_assoc()['total'];
$stat_dikembalikan = $conn->query("SELECT COUNT(*) as total FROM detail_peminjaman WHERE status = 'dikembalikan'")->fetch_assoc()['total'];
$stat_menunggu = $conn->query("SELECT COUNT(*) as total FROM detail_peminjaman WHERE req_kembali = 'menunggu' OR status IN ('menunggu', 'menunggu perpanjangan')")->fetch_assoc()['total'];

// ==========================================
// PAGINATION UNTUK TABEL UTAMA
// ==========================================
$limit = 10; // Jumlah data per halaman
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $limit;

// Hitung total data untuk pagination
$total_query = $conn->query("
    SELECT COUNT(*) as total 
    FROM detail_peminjaman dp 
    JOIN siswa s ON dp.id_siswa = s.id_siswa 
    JOIN buku b ON dp.buku_id = b.id_buku 
    WHERE 1=1 $filter_sql
");
$total_rows = $total_query->fetch_assoc()['total'];
$total_pages = ceil($total_rows / $limit);

// ==========================================
// 1. QUERY LAPORAN PEMINJAMAN (Tabel Utama + Pagination)
// ==========================================
$q_peminjaman = "
    SELECT dp.id_detail, p.id_peminjaman, s.username as anggota, s.id_siswa, b.judul, dp.tgl_pinjam, dp.tgl_pengembalian, dp.status 
    FROM detail_peminjaman dp 
    JOIN peminjaman p ON dp.id_peminjaman = p.id_peminjaman
    JOIN siswa s ON dp.id_siswa = s.id_siswa 
    JOIN buku b ON dp.buku_id = b.id_buku 
    WHERE 1=1 $filter_sql
    ORDER BY dp.id_detail DESC 
    LIMIT $limit OFFSET $offset
";
$list_peminjaman = $conn->query($q_peminjaman);

// ==========================================
// 2. QUERY LAPORAN PENGEMBALIAN (Tabel Kiri Bawah)
// ==========================================
$q_pengembalian = "
    SELECT dp.id_detail, p.id_peminjaman, s.username as anggota, s.id_siswa, b.judul, dp.status 
    FROM detail_peminjaman dp 
    JOIN peminjaman p ON dp.id_peminjaman = p.id_peminjaman
    JOIN siswa s ON dp.id_siswa = s.id_siswa 
    JOIN buku b ON dp.buku_id = b.id_buku 
    WHERE dp.status = 'dikembalikan' AND DATE_FORMAT(dp.tgl_pinjam, '%Y-%m') = '$bulan_filter'
    ORDER BY dp.id_detail DESC LIMIT 5
";
$list_pengembalian = $conn->query($q_pengembalian);

// ==========================================
// 3. QUERY LAPORAN DENDA (Tabel Kanan Bawah)
// ==========================================
$q_denda = "
    SELECT dp.id_detail, p.id_peminjaman, s.username as anggota, s.id_siswa, b.judul, p.denda, p.status_denda 
    FROM detail_peminjaman dp
    JOIN peminjaman p ON dp.id_peminjaman = p.id_peminjaman
    JOIN siswa s ON dp.id_siswa = s.id_siswa
    JOIN buku b ON dp.buku_id = b.id_buku
    WHERE p.denda > 0 AND DATE_FORMAT(dp.tgl_pinjam, '%Y-%m') = '$bulan_filter'
    ORDER BY dp.id_detail DESC LIMIT 5
";
$list_denda = $conn->query($q_denda);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan - Pustakita</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    colors: {
                        brandLight: '#C9D3F8',
                        brandDark: '#0A0F5C',
                        brandDanger: '#D32F2F',
                        brandCardBg: '#DCE1F9'
                    }
                }
            }
        }
    </script>
    <style>
        @media print {
            .no-print {
                display: none !important;
            }

            .print-full-width {
                width: 100% !important;
                max-width: 100% !important;
                margin: 0 !important;
                padding: 0 !important;
            }

            body {
                background-color: white !important;
            }

            .shadow-sm {
                box-shadow: none !important;
                border: 1px solid #e5e7eb !important;
            }
        }
    </style>
</head>

<body class="bg-gray-50 flex h-screen font-sans text-gray-800 overflow-hidden">

    <aside class="w-64 bg-brandLight flex flex-col border-r border-gray-300">
        <div class="h-20 flex items-center px-6 border-b border-gray-300 border-opacity-50">
            <h1 class="text-2xl font-bold tracking-wide text-black">
                <span class="text-3xl">P</span>ustaKita
            </h1>
        </div>

        <nav class="flex-1 py-6 px-4 space-y-2 overflow-y-auto font-medium">
            <a href="dashboard.php" class="flex items-center px-4 py-3 text-black hover:bg-white hover:bg-opacity-40 rounded-lg transition-colors">
                <i class="fas fa-home w-6 text-center mr-3"></i>
                Dashboard
            </a>
            <a href="pglbuku.php" class="flex items-center px-4 py-3 text-black hover:bg-white hover:bg-opacity-40 rounded-lg transition-colors">
                <i class="fas fa-book w-6 text-center mr-3"></i>
                Pengelolaan Buku
            </a>
            <a href="pglmember.php" class="flex items-center px-4 py-3 text-black hover:bg-white hover:bg-opacity-40 rounded-lg transition-colors">
                <i class="fas fa-user-friends w-6 text-center mr-3"></i>
                Pengelolaan Member
            </a>
            <a href="kelola_peminjaman.php" class="flex items-center px-4 py-3 text-black hover:bg-white hover:bg-opacity-40 rounded-lg transition-colors">
                <i class="fas fa-clock w-6 text-center mr-3"></i>
                Kelola Peminjaman
            </a>
            <a href="kelola_pengembalian.php" class="flex items-center px-4 py-3 text-black hover:bg-white hover:bg-opacity-40 rounded-lg transition-colors">
                <i class="fas fa-exchange-alt w-6 text-center mr-3"></i>
                Kelola Pengembalian
            </a>
            <a href="kelola_denda.php" class="flex items-center px-4 py-3 text-black hover:bg-white hover:bg-opacity-40 rounded-lg transition-colors">
                <i class="fas fa-exclamation-circle w-6 text-center mr-3"></i>
                Kelola Denda
            </a>
            <a href="laporan.php" class="flex items-center px-4 py-3 bg-brandDark text-white rounded-lg">
                <i class="fas fa-clipboard-list w-6 text-center mr-3"></i>
                Laporan
            </a>

            <div class="pt-8">
                <a href="logout.php" class="flex items-center px-4 py-3 text-black hover:bg-white hover:bg-opacity-40 rounded-lg transition-colors">
                    <i class="fas fa-sign-out-alt w-6 text-center mr-3"></i>
                    Log Out
                </a>
            </div>
        </nav>
    </aside>

    <main class="flex-1 flex flex-col bg-white overflow-y-auto print-full-width">
        <header class="h-20 flex justify-between items-center px-8 border-b border-gray-200 bg-white sticky top-0 z-10 no-print">
            <h2 class="text-3xl font-bold text-black">Laporan</h2>
            <div class="flex flex-col items-center">
                <i class="fas fa-user-circle text-3xl text-brandDark"></i>
                <span class="text-sm font-medium mt-1">Admin</span>
            </div>
        </header>

        <div class="p-8 max-w-6xl w-full mx-auto print-full-width">

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8 no-print">
                <div class="bg-brandDark rounded-md p-5 text-white shadow-sm flex flex-col justify-center">
                    <p class="text-xs text-gray-300 mb-1">Total Buku Dipinjam</p>
                    <p class="text-4xl font-bold"><?php echo $stat_aktif; ?></p>
                </div>
                <div class="bg-brandDanger rounded-md p-5 text-white shadow-sm flex flex-col justify-center">
                    <p class="text-xs text-red-200 mb-1">Total Terlambat</p>
                    <p class="text-4xl font-bold"><?php echo $stat_terlambat; ?></p>
                </div>
                <div class="bg-brandCardBg rounded-md p-5 text-brandDark shadow-sm flex flex-col justify-center">
                    <p class="text-xs font-semibold mb-1">Total Selesai/Kembali</p>
                    <p class="text-4xl font-bold"><?php echo $stat_dikembalikan; ?></p>
                </div>
                <div class="bg-brandCardBg rounded-md p-5 text-brandDark shadow-sm flex flex-col justify-center">
                    <p class="text-xs font-semibold mb-1">Menunggu Konfirmasi</p>
                    <p class="text-4xl font-bold"><?php echo $stat_menunggu; ?></p>
                </div>
            </div>

            <div class="flex flex-col md:flex-row justify-between items-center mb-6 gap-4 no-print">
                <form method="GET" class="flex flex-1 gap-4 items-center">
                    <div class="flex items-center border border-gray-300 rounded px-3 py-1.5 focus-within:border-brandDark bg-white w-64 shadow-sm">
                        <i class="fas fa-search text-gray-400 mr-2 text-sm"></i>
                        <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Cari anggota atau buku..." class="bg-transparent outline-none w-full text-sm">
                    </div>
                    <div class="flex items-center border border-gray-300 rounded px-3 py-1.5 bg-white shadow-sm">
                        <i class="far fa-calendar-alt text-gray-400 mr-2 text-sm"></i>
                        <input type="month" name="bulan" value="<?php echo $bulan_filter; ?>" class="bg-transparent outline-none text-sm text-gray-600" onchange="this.form.submit()">
                    </div>
                </form>
                <button onclick="window.print()" class="bg-brandDark text-white px-5 py-2.5 rounded-md text-sm font-semibold hover:bg-opacity-90 flex items-center shadow-sm transition-colors">
                    <i class="fas fa-print mr-2"></i> Cetak Laporan
                </button>
            </div>

            <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden mb-6">
                <div class="px-5 py-4 border-b border-gray-200 bg-gray-50 print:bg-white flex justify-between items-center">
                    <h3 class="text-sm font-bold text-brandDark">Laporan Riwayat Peminjaman Lengkap</h3>
                    <span class="text-xs text-gray-500 font-medium no-print">Total Data: <?php echo $total_rows; ?></span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-white border-b border-gray-200 text-gray-500 text-xs uppercase">
                            <tr>
                                <th class="py-3 px-5 font-semibold">ID</th>
                                <th class="py-3 px-5 font-semibold">Anggota</th>
                                <th class="py-3 px-5 font-semibold">Buku Dipinjam</th>
                                <th class="py-3 px-5 font-semibold">Tgl Pinjam</th>
                                <th class="py-3 px-5 font-semibold">Tgl Kembali</th>
                                <th class="py-3 px-5 font-semibold text-center">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700">
                            <?php
                            if ($list_peminjaman && $list_peminjaman->num_rows > 0):
                                while ($row = $list_peminjaman->fetch_assoc()):
                                    $formatted_id = "#" . str_pad($row['id_peminjaman'], 4, '0', STR_PAD_LEFT);
                                    $is_late = ($row['status'] == 'dipinjam' && strtotime(date('Y-m-d')) > strtotime($row['tgl_pengembalian']));
                            ?>
                                    <tr class="hover:bg-gray-50">
                                        <td class="py-3 px-5 text-gray-500 font-mono text-xs"><?php echo $formatted_id; ?></td>
                                        <td class="py-3 px-5 font-medium text-black flex items-center gap-2">
                                            <div class="w-6 h-6 rounded-full bg-brandLight text-brandDark flex items-center justify-center font-bold text-[10px] no-print">
                                                <?php echo strtoupper(substr($row['anggota'], 0, 2)); ?>
                                            </div>
                                            <div class="flex flex-col">
                                                <span><?php echo htmlspecialchars($row['anggota']); ?></span>
                                            </div>
                                        </td>
                                        <td class="py-3 px-5 text-gray-600 truncate max-w-xs"><?php echo htmlspecialchars($row['judul']); ?></td>
                                        <td class="py-3 px-5"><?php echo date('d M Y', strtotime($row['tgl_pinjam'])); ?></td>
                                        <td class="py-3 px-5 <?php echo $is_late ? 'text-red-500 font-bold' : ''; ?>">
                                            <?php echo $row['tgl_pengembalian'] ? date('d M Y', strtotime($row['tgl_pengembalian'])) : '-'; ?>
                                        </td>
                                        <td class="py-3 px-5 text-center">
                                            <?php if ($row['status'] == 'dikembalikan'): ?>
                                                <span class="px-3 py-1 bg-green-100 text-green-700 rounded-full text-[10px] font-bold">Selesai</span>
                                            <?php elseif ($is_late): ?>
                                                <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-[10px] font-bold">Terlambat</span>
                                            <?php elseif ($row['status'] == 'dipinjam'): ?>
                                                <span class="px-3 py-1 bg-blue-100 text-blue-700 rounded-full text-[10px] font-bold">Dipinjam</span>
                                            <?php else: ?>
                                                <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-[10px] font-bold">Menunggu</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php
                                endwhile;
                            else:
                                ?>
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-gray-400 text-sm">Tidak ada data peminjaman pada periode ini.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

                <?php if ($total_pages > 1): ?>
                    <div class="px-5 py-3 border-t border-gray-200 bg-gray-50 flex items-center justify-between no-print">
                        <span class="text-xs text-gray-500">Halaman <?php echo $page; ?> dari <?php echo $total_pages; ?></span>
                        <div class="flex gap-1">
                            <?php
                            // Menyimpan parameter GET agar search & filter bulan tidak hilang saat pindah halaman
                            $query_string = "";
                            if (!empty($search)) $query_string .= "&search=" . urlencode($search);
                            if (!empty($bulan_filter)) $query_string .= "&bulan=" . urlencode($bulan_filter);

                            // Tombol Previous
                            if ($page > 1): ?>
                                <a href="?page=<?php echo $page - 1; ?><?php echo $query_string; ?>" class="px-3 py-1 border border-gray-300 rounded bg-white text-gray-600 hover:bg-gray-100 text-xs font-medium">Prev</a>
                            <?php endif; ?>

                            <?php
                            // Nomor Halaman
                            for ($i = 1; $i <= $total_pages; $i++): ?>
                                <a href="?page=<?php echo $i; ?><?php echo $query_string; ?>" class="px-3 py-1 border <?php echo ($i == $page) ? 'border-brandDark bg-brandDark text-white' : 'border-gray-300 bg-white text-gray-600 hover:bg-gray-100'; ?> rounded text-xs font-medium"><?php echo $i; ?></a>
                            <?php endfor; ?>

                            <?php
                            // Tombol Next
                            if ($page < $total_pages): ?>
                                <a href="?page=<?php echo $page + 1; ?><?php echo $query_string; ?>" class="px-3 py-1 border border-gray-300 rounded bg-white text-gray-600 hover:bg-gray-100 text-xs font-medium">Next</a>
                            <?php endif; ?>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 pb-10">

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-200 bg-gray-50 print:bg-white">
                        <h3 class="text-sm font-bold text-brandDark">Buku Selesai (Bulan Ini)</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-white border-b border-gray-200 text-gray-500 text-xs">
                                <tr>
                                    <th class="py-3 px-4 font-semibold">Anggota</th>
                                    <th class="py-3 px-4 font-semibold">Buku</th>
                                    <th class="py-3 px-4 font-semibold text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-gray-700">
                                <?php
                                if ($list_pengembalian && $list_pengembalian->num_rows > 0):
                                    while ($row = $list_pengembalian->fetch_assoc()):
                                ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="py-3 px-4 font-medium flex items-center gap-2">
                                                <div class="w-5 h-5 rounded-full bg-brandLight text-brandDark flex items-center justify-center font-bold text-[8px] no-print">
                                                    <?php echo strtoupper(substr($row['anggota'], 0, 2)); ?>
                                                </div>
                                                <span class="truncate w-24 text-xs"><?php echo htmlspecialchars($row['anggota']); ?></span>
                                            </td>
                                            <td class="py-3 px-4 truncate max-w-[120px] text-xs text-gray-500"><?php echo htmlspecialchars($row['judul']); ?></td>
                                            <td class="py-3 px-4 text-center">
                                                <i class="fas fa-check-circle text-green-500 text-sm"></i>
                                            </td>
                                        </tr>
                                    <?php endwhile;
                                else: ?>
                                    <tr>
                                        <td colspan="3" class="py-6 text-center text-gray-400 text-xs">Belum ada buku dikembalikan bulan ini.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-gray-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-gray-200 bg-gray-50 print:bg-white">
                        <h3 class="text-sm font-bold text-brandDark">Catatan Denda (Bulan Ini)</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-white border-b border-gray-200 text-gray-500 text-xs">
                                <tr>
                                    <th class="py-3 px-4 font-semibold">Anggota</th>
                                    <th class="py-3 px-4 font-semibold">Buku</th>
                                    <th class="py-3 px-4 font-semibold">Denda</th>
                                    <th class="py-3 px-4 font-semibold text-center">Status</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-100 text-gray-700">
                                <?php
                                if ($list_denda && $list_denda->num_rows > 0):
                                    while ($row = $list_denda->fetch_assoc()):
                                ?>
                                        <tr class="hover:bg-gray-50">
                                            <td class="py-3 px-4 font-medium flex items-center gap-2">
                                                <span class="truncate w-24 text-xs"><?php echo htmlspecialchars($row['anggota']); ?></span>
                                            </td>
                                            <td class="py-3 px-4 truncate max-w-[100px] text-xs text-gray-500"><?php echo htmlspecialchars($row['judul']); ?></td>
                                            <td class="py-3 px-4 text-xs font-bold text-red-600">
                                                Rp<?php echo number_format($row['denda'], 0, ',', '.'); ?>
                                            </td>
                                            <td class="py-3 px-4 text-center">
                                                <?php if ($row['status_denda'] == 'lunas'): ?>
                                                    <span class="px-2 py-0.5 bg-green-100 text-green-600 rounded text-[10px] font-bold">Lunas</span>
                                                <?php else: ?>
                                                    <span class="px-2 py-0.5 bg-red-100 text-red-600 rounded text-[10px] font-bold">Belum</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile;
                                else: ?>
                                    <tr>
                                        <td colspan="4" class="py-6 text-center text-gray-400 text-xs">Tidak ada data pelanggaran/denda.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>
    </main>

</body>

</html>