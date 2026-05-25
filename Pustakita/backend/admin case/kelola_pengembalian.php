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
// PROSES AKSI PENGEMBALIAN
// ==========================================
$message = "";

if (isset($_POST['action']) && $_POST['action'] == 'konfirmasi_kembali') {
    $id_peminjaman = (int)$_POST['id_peminjaman'];
    $kondisi_buku  = $conn->real_escape_string($_POST['kondisi_buku']); // Akan bernilai: normal, rusak ringan, rusak berat
    $tgl_sekarang  = date('Y-m-d');

    // Ambil data tgl_pengembalian (Jatuh Tempo) untuk cek keterlambatan
    $cek_pinjam = $conn->query("SELECT tgl_pengembalian FROM peminjaman WHERE id_peminjaman = $id_peminjaman")->fetch_assoc();
    $jatuh_tempo = strtotime($cek_pinjam['tgl_pengembalian']);
    $hari_ini    = strtotime($tgl_sekarang);

    $denda_total = 0;

    // 1. Hitung Denda Keterlambatan (Misal: Rp 1.000 / hari)
    if ($hari_ini > $jatuh_tempo) {
        $selisih_hari = floor(($hari_ini - $jatuh_tempo) / (60 * 60 * 24));
        $denda_total += $selisih_hari * 1000;
    }

    // 2. Hitung Denda Kerusakan Buku Sesuai ENUM
    if ($kondisi_buku == 'rusak ringan') {
        $denda_total += 20000;
    } elseif ($kondisi_buku == 'rusak berat') {
        $denda_total += 50000;
    }

    // UPDATE tabel peminjaman
    $sql_update = "UPDATE peminjaman SET 
                   status = 'dikembalikan', 
                   tgl_dikembalikan = '$tgl_sekarang', 
                   kondisi_buku = '$kondisi_buku',
                   req_kembali = 'belum' 
                   WHERE id_peminjaman = $id_peminjaman";

    if ($conn->query($sql_update)) {
        // Jika ada denda, catat ke tabel detail_peminjaman
        if ($denda_total > 0) {
            $conn->query("INSERT INTO detail_peminjaman (id_peminjaman, denda) VALUES ($id_peminjaman, $denda_total)");
        }
        $message = "Sukses: Pengembalian buku berhasil dikonfirmasi.";
    } else {
        $message = "Error: " . $conn->error;
    }
}

// ==========================================
// STATISTIK & QUERY UTAMA
// ==========================================
$stat_aktif = $conn->query("SELECT COUNT(*) as total FROM peminjaman WHERE status = 'dipinjam'")->fetch_assoc()['total'];
$stat_terlambat = $conn->query("SELECT COUNT(*) as total FROM peminjaman WHERE status = 'dipinjam' AND tgl_pengembalian < CURDATE()")->fetch_assoc()['total'];
$stat_dikembalikan_hari_ini = $conn->query("SELECT COUNT(*) as total FROM peminjaman WHERE status = 'dikembalikan' AND tgl_dikembalikan = CURDATE()")->fetch_assoc()['total'];
$stat_menunggu = $conn->query("SELECT COUNT(*) as total FROM peminjaman WHERE req_kembali = 'menunggu' AND status = 'dipinjam'")->fetch_assoc()['total'];

// Pencarian Filter
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// 1. QUERY MENUNGGU KONFIRMASI PENGEMBALIAN
$q_menunggu = "
    SELECT p.id_peminjaman, s.username as anggota, s.id_siswa, b.judul, p.tgl_pinjam, p.tgl_pengembalian 
    FROM peminjaman p 
    JOIN siswa s ON p.siswa_id = s.id_siswa 
    JOIN buku b ON p.buku_id = b.id_buku 
    WHERE p.status = 'dipinjam' 
";
if (!empty($search)) {
    $q_menunggu .= " AND (s.username LIKE '%$search%' OR b.judul LIKE '%$search%') ";
} else {
    $q_menunggu .= " AND p.req_kembali = 'menunggu' ";
}
$q_menunggu .= " ORDER BY p.tgl_pengembalian ASC LIMIT 10";
$list_menunggu = $conn->query($q_menunggu);

// 2. QUERY RIWAYAT PENGEMBALIAN
$q_riwayat = "
    SELECT p.id_peminjaman, s.username as anggota, b.judul, p.tgl_dikembalikan, p.kondisi_buku, 
           (SELECT COUNT(*) FROM detail_peminjaman dp WHERE dp.id_peminjaman = p.id_peminjaman) as ada_denda
    FROM peminjaman p 
    JOIN siswa s ON p.siswa_id = s.id_siswa 
    JOIN buku b ON p.buku_id = b.id_buku 
    WHERE p.status = 'dikembalikan'
    ORDER BY p.tgl_dikembalikan DESC LIMIT 10
";
$list_riwayat = $conn->query($q_riwayat);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pengembalian - Pustakita</title>
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
</head>

<body class="bg-gray-50 flex h-screen font-sans text-gray-800 overflow-hidden">

    <aside class="w-64 bg-brandLight flex flex-col border-r border-gray-300">
        <div class="h-20 flex items-center px-6 border-b border-gray-300 border-opacity-50">
            <h1 class="text-2xl font-bold tracking-wide text-black"><span class="text-3xl">P</span>usataKita</h1>
        </div>
        <nav class="flex-1 py-6 px-4 space-y-2 overflow-y-auto font-medium text-sm">
            <a href="dashboard.php" class="flex items-center px-4 py-3 text-black hover:bg-white hover:bg-opacity-40 rounded-lg transition-colors">
                <i class="fas fa-home w-6 text-center mr-3"></i> Dashboard
            </a>
            <a href="pgl_buku.php" class="flex items-center px-4 py-3 text-black hover:bg-white hover:bg-opacity-40 rounded-lg transition-colors">
                <i class="fas fa-book w-6 text-center mr-3"></i> Pengelolaan Buku
            </a>
            <a href="pengelolaan_member.php" class="flex items-center px-4 py-3 text-black hover:bg-white hover:bg-opacity-40 rounded-lg transition-colors">
                <i class="fas fa-user-friends w-6 text-center mr-3"></i> Pengelolaan Member
            </a>
            <a href="kelola_peminjaman.php" class="flex items-center px-4 py-3 text-black hover:bg-white hover:bg-opacity-40 rounded-lg transition-colors">
                <i class="fas fa-clock w-6 text-center mr-3"></i> Kelola Peminjaman
            </a>
            <a href="#" class="flex items-center px-4 py-3 bg-brandDark text-white rounded-lg shadow-md">
                <i class="fas fa-exchange-alt w-6 text-center mr-3"></i> Kelola Pengembalian
            </a>
            <a href="kelola_denda.php" class="flex items-center px-4 py-3 text-black hover:bg-white hover:bg-opacity-40 rounded-lg transition-colors">
                <i class="fas fa-exclamation-circle w-6 text-center mr-3"></i> Kelola Denda
            </a>
            <a href="laporan.php" class="flex items-center px-4 py-3 text-black hover:bg-white hover:bg-opacity-40 rounded-lg transition-colors">
                <i class="fas fa-clipboard-list w-6 text-center mr-3"></i> Laporan
            </a>
            <div class="pt-8">
                <a href="logout.php" class="flex items-center px-4 py-3 text-black hover:bg-white hover:bg-opacity-40 rounded-lg transition-colors">
                    <i class="fas fa-sign-out-alt w-6 text-center mr-3"></i> Log Out
                </a>
            </div>
        </nav>
    </aside>

    <main class="flex-1 flex flex-col bg-white overflow-y-auto">
        <header class="h-20 flex justify-between items-center px-8 border-b border-gray-200 bg-white sticky top-0 z-10">
            <h2 class="text-3xl font-bold text-black">Kelola Pengembalian</h2>
            <div class="flex flex-col items-center">
                <i class="fas fa-user-circle text-3xl text-black"></i>
                <span class="text-sm font-medium mt-1">Admin</span>
            </div>
        </header>

        <div class="p-8 max-w-6xl w-full mx-auto">

            <?php if (!empty($message)): ?>
                <div class="mb-4 p-3 rounded bg-green-100 text-green-700 border border-green-200 text-sm font-medium">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-8">
                <div class="bg-brandDark rounded-md p-5 text-white shadow-sm flex flex-col justify-center">
                    <p class="text-xs text-gray-300 mb-1">Total Aktif</p>
                    <p class="text-4xl font-bold"><?php echo $stat_aktif; ?></p>
                </div>
                <div class="bg-brandDanger rounded-md p-5 text-white shadow-sm flex flex-col justify-center">
                    <p class="text-xs text-red-200 mb-1">Terlambat</p>
                    <p class="text-4xl font-bold"><?php echo $stat_terlambat; ?></p>
                </div>
                <div class="bg-brandCardBg rounded-md p-5 text-brandDark shadow-sm flex flex-col justify-center">
                    <p class="text-xs font-semibold mb-1">Dikembalikan Hari Ini</p>
                    <p class="text-4xl font-bold"><?php echo $stat_dikembalikan_hari_ini; ?></p>
                </div>
                <div class="bg-brandCardBg rounded-md p-5 text-brandDark shadow-sm flex flex-col justify-center">
                    <p class="text-xs font-semibold mb-1">Menunggu Konfirmasi</p>
                    <p class="text-4xl font-bold"><?php echo $stat_menunggu; ?></p>
                </div>
            </div>

            <div class="mb-6">
                <form method="GET" class="w-full md:w-1/3 flex items-center border border-gray-300 rounded px-3 py-1.5 focus-within:border-brandDark transition-colors">
                    <i class="fas fa-search text-gray-400 mr-2 text-sm"></i>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Cari anggota atau buku..." class="bg-transparent outline-none w-full text-sm">
                </form>
            </div>

            <div class="mb-4">
                <h3 class="text-lg font-bold text-gray-800">Menunggu Konfirmasi Pengembalian</h3>
            </div>
            <div class="space-y-4 mb-10">
                <?php
                if ($list_menunggu && $list_menunggu->num_rows > 0):
                    while ($row = $list_menunggu->fetch_assoc()):
                        $inisial = strtoupper(substr($row['anggota'], 0, 2));

                        // Cek keterlambatan
                        $tgl_pengembalian_time = strtotime($row['tgl_pengembalian']);
                        $hari_ini_time = strtotime(date('Y-m-d'));
                        $is_late = ($hari_ini_time > $tgl_pengembalian_time);

                        $selisih_hari = 0;
                        if ($is_late) {
                            $selisih_hari = floor(($hari_ini_time - $tgl_pengembalian_time) / (60 * 60 * 24));
                        }
                ?>
                        <form method="POST" action="" class="flex flex-col md:flex-row items-center justify-between bg-white border border-gray-200 p-5 rounded-lg shadow-sm gap-4 md:gap-0">
                            <input type="hidden" name="action" value="konfirmasi_kembali">
                            <input type="hidden" name="id_peminjaman" value="<?php echo $row['id_peminjaman']; ?>">

                            <div class="flex items-center gap-4 w-full md:w-1/3">
                                <div class="w-10 h-10 shrink-0 rounded-full bg-brandLight text-brandDark flex items-center justify-center font-bold text-sm"><?php echo $inisial; ?></div>
                                <div>
                                    <p class="font-bold text-sm text-black"><?php echo htmlspecialchars($row['anggota']); ?> (ID: <?php echo $row['id_siswa']; ?>)</p>
                                    <p class="text-xs text-gray-500 truncate w-48"><?php echo htmlspecialchars($row['judul']); ?></p>
                                </div>
                            </div>

                            <div class="w-full md:w-1/3 text-xs flex flex-col gap-1 border border-gray-100 rounded-md p-2 bg-gray-50 <?php echo $is_late ? 'border-red-200 bg-red-50 text-red-600' : 'text-gray-500'; ?>">
                                <div class="flex justify-between items-center px-1">
                                    <span><i class="far fa-calendar-alt mr-1"></i> Tgl Pinjam: <?php echo date('d M Y', strtotime($row['tgl_pinjam'])); ?></span>
                                    <?php if (!$is_late): ?>
                                        <span class="text-gray-400">Jatuh Tempo: <?php echo date('d M Y', strtotime($row['tgl_pengembalian'])); ?></span>
                                    <?php endif; ?>
                                </div>
                                <?php if ($is_late): ?>
                                    <div class="px-1 font-semibold flex items-center">
                                        <i class="fas fa-exclamation-triangle mr-1 text-red-500"></i>
                                        Jatuh Tempo: <?php echo date('d M Y', strtotime($row['tgl_pengembalian'])); ?> (Terlambat <?php echo $selisih_hari; ?> hari)
                                    </div>
                                    <div class="px-1 text-[10px] text-red-500 mt-0.5">
                                        Terlambat — denda akan dihitung otomatis
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="flex flex-col w-full md:w-auto items-end gap-2">
                                <div class="flex items-center gap-2">
                                    <label class="text-[10px] text-gray-500 uppercase font-semibold">Kondisi Buku:</label>
                                    <select name="kondisi_buku" class="border border-gray-300 rounded px-2 py-1 text-xs focus:outline-none focus:border-brandDark bg-white">
                                        <option value="normal">Normal</option>
                                        <option value="rusak ringan">Rusak Ringan</option>
                                        <option value="rusak berat">Rusak Berat</option>
                                    </select>
                                </div>
                                <div class="flex gap-2 w-full justify-end">
                                    <button type="button" class="px-4 py-1.5 border border-gray-300 text-gray-600 rounded text-xs font-semibold hover:bg-gray-50 transition-colors">Batal</button>
                                    <button type="submit" class="px-4 py-1.5 bg-brandDark text-white rounded text-xs font-semibold hover:bg-opacity-90 shadow-sm flex items-center">
                                        Konfirmasi <i class="fas fa-check ml-1"></i>
                                    </button>
                                </div>
                            </div>
                        </form>
                    <?php
                    endwhile;
                else:
                    ?>
                    <div class="text-center py-6 text-gray-400 text-sm border border-gray-200 rounded-lg bg-white shadow-sm">
                        <?php echo empty($search) ? 'Tidak ada permintaan pengembalian yang menunggu konfirmasi.' : 'Tidak ditemukan data buku yang sedang dipinjam.'; ?>
                    </div>
                <?php endif; ?>
            </div>

            <div class="mb-3">
                <h3 class="text-lg font-bold text-gray-800">Riwayat Pengembalian</h3>
            </div>
            <div class="bg-white rounded shadow-sm overflow-hidden border border-gray-100">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-brandDark text-white">
                            <tr>
                                <th class="py-3 px-4 font-semibold text-center w-16">ID</th>
                                <th class="py-3 px-4 font-semibold">Anggota</th>
                                <th class="py-3 px-4 font-semibold">Buku</th>
                                <th class="py-3 px-4 font-semibold">Tgl Kembali</th>
                                <th class="py-3 px-4 font-semibold text-center">Kondisi</th>
                                <th class="py-3 px-4 font-semibold text-center">Status Denda</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700">
                            <?php
                            if ($list_riwayat && $list_riwayat->num_rows > 0):
                                while ($row = $list_riwayat->fetch_assoc()):
                                    $formatted_id = "#" . str_pad($row['id_peminjaman'], 3, '0', STR_PAD_LEFT);

                                    // Set data kondisi
                                    $kondisi = $row['kondisi_buku'] ?: 'normal';
                                    $kondisi_label = ucwords(str_replace('_', ' ', $kondisi)); // Membuatnya jadi title case ("Rusak Ringan")

                                    // Visual Badges Setup
                                    $kondisi_color = ($kondisi == 'normal') ? 'bg-green-100 text-green-700 border-green-200' : 'bg-red-100 text-red-700 border-red-200';
                                    $denda_status = ($row['ada_denda'] > 0) ? 'Ada Denda' : 'Tidak Ada';
                                    $denda_color = ($row['ada_denda'] > 0) ? 'bg-red-100 text-red-700 border-red-200' : 'bg-green-100 text-green-700 border-green-200';
                            ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="py-3 px-4 text-center font-mono text-xs text-gray-500 bg-gray-100 rounded-full inline-block m-2">
                                            <?php echo $formatted_id; ?>
                                        </td>
                                        <td class="py-3 px-4 text-black font-medium flex items-center gap-2 mt-1">
                                            <div class="w-6 h-6 rounded-full bg-brandLight text-brandDark flex items-center justify-center font-bold text-[10px]">
                                                <?php echo strtoupper(substr($row['anggota'], 0, 2)); ?>
                                            </div>
                                            <?php echo htmlspecialchars($row['anggota']); ?>
                                        </td>
                                        <td class="py-3 px-4 text-gray-600 truncate max-w-[200px]"><?php echo htmlspecialchars($row['judul']); ?></td>
                                        <td class="py-3 px-4 text-gray-500">
                                            <?php echo $row['tgl_dikembalikan'] ? date('d M Y', strtotime($row['tgl_dikembalikan'])) : '-'; ?>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold border <?php echo $kondisi_color; ?>">
                                                <?php echo htmlspecialchars($kondisi_label); ?>
                                            </span>
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            <span class="px-2 py-0.5 rounded text-[10px] font-bold border <?php echo $denda_color; ?>">
                                                <?php echo $denda_status; ?>
                                            </span>
                                        </td>
                                    </tr>
                                <?php
                                endwhile;
                            else:
                                ?>
                                <tr>
                                    <td colspan="6" class="py-6 text-center text-gray-400 text-sm">Belum ada riwayat pengembalian buku.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </main>

</body>

</html>