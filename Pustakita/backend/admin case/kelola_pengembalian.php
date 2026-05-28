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
$stat_aktif = $conn->query("SELECT COUNT(*) as total FROM detail_peminjaman WHERE status = 'dipinjam'")->fetch_assoc()['total'];
$stat_terlambat = $conn->query("SELECT COUNT(*) as total FROM detail_peminjaman WHERE status = 'dipinjam' AND tgl_pengembalian < CURDATE()")->fetch_assoc()['total'];
$stat_dikembalikan_hari_ini = $conn->query("SELECT COUNT(*) as total FROM detail_peminjaman WHERE status = 'dikembalikan' AND tgl_dikembalikan = CURDATE()")->fetch_assoc()['total'];
$stat_menunggu = $conn->query("SELECT COUNT(*) as total FROM detail_peminjaman WHERE req_kembali = 'menunggu' AND status = 'dipinjam'")->fetch_assoc()['total'];

// Pencarian Filter
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

// 1. QUERY MENUNGGU KONFIRMASI PENGEMBALIAN
$q_menunggu = "
    SELECT p.id_peminjaman, s.username as anggota, s.id_siswa, b.judul, p.tgl_pinjam, p.tgl_pengembalian 
    FROM detail_peminjaman p 
    JOIN siswa s ON p.id_siswa = s.id_siswa 
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
    FROM detail_peminjaman p 
    JOIN siswa s ON p.id_siswa = s.id_siswa 
    JOIN buku b ON p.buku_id = b.id_buku 
    WHERE p.status = 'dikembalikan'
    ORDER BY p.tgl_dikembalikan DESC LIMIT 10
";
$list_riwayat = $conn->query($q_riwayat);

$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';

$q_pengembalian = "
    SELECT 
        dp.id_detail, 
        p.id_peminjaman, 
        s.username AS nama_siswa, 
        s.kelas, 
        b.judul AS judul_buku, 
        b.kode_buku, 
        dp.tgl_pinjam, 
        dp.tgl_pengembalian, 
        dp.status, 
        dp.req_kembali
    FROM detail_peminjaman dp
    JOIN peminjaman p ON dp.id_peminjaman = p.id_peminjaman
    JOIN siswa s ON dp.id_siswa = s.id_siswa
    JOIN buku b ON dp.buku_id = b.id_buku
    WHERE dp.status = 'dipinjam' OR dp.req_kembali = 'menunggu'
";

// Jika admin melakukan pencarian nama siswa atau judul buku
if (!empty($search)) {
    $q_pengembalian .= " AND (s.username LIKE '%$search%' OR b.judul LIKE '%$search%')";
}

$q_pengembalian .= " ORDER BY dp.req_kembali DESC, dp.id_detail DESC";
$list_pengembalian = $conn->query($q_pengembalian);
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
            <a href="kelola_pengembalian.php" class="flex items-center px-4 py-3 bg-brandDark text-white rounded-lg">
                <i class="fas fa-exchange-alt w-6 text-center mr-3"></i>
                Kelola Pengembalian
            </a>
            <a href="kelola_denda.php" class="flex items-center px-4 py-3 text-black hover:bg-white hover:bg-opacity-40 rounded-lg transition-colors">
                <i class="fas fa-exclamation-circle w-6 text-center mr-3"></i>
                Kelola Denda
            </a>
            <a href="laporan.php" class="flex items-center px-4 py-3 text-black hover:bg-white hover:bg-opacity-40 rounded-lg transition-colors">
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
                        <tbody class="divide-y divide-gray-100 text-gray-700 bg-white">
                            <?php
                            if ($list_pengembalian && $list_pengembalian->num_rows > 0):
                                while ($row = $list_pengembalian->fetch_assoc()):
                                    $is_req_kembali = ($row['req_kembali'] === 'menunggu');
                            ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="py-4 px-5 font-mono text-xs text-gray-500 bg-gray-100 rounded-lg inline-block m-2 mt-3 font-semibold">
                                            #<?php echo str_pad($row['id_peminjaman'], 4, '0', STR_PAD_LEFT); ?>
                                        </td>
                                        <td class="py-4 px-5 font-semibold text-black">
                                            <?php echo htmlspecialchars($row['nama_siswa']); ?> <span class="text-xs text-gray-400 font-normal">(<?php echo htmlspecialchars($row['kelas']); ?>)</span>
                                        </td>
                                        <td class="py-4 px-5">
                                            <div class="font-semibold text-brandDark"><?php echo htmlspecialchars($row['judul_buku']); ?></div>
                                            <div class="text-[11px] text-gray-400">Kode: <?php echo htmlspecialchars($row['kode_buku']); ?></div>
                                        </td>
                                        <td class="py-4 px-5"><?php echo date('d M Y', strtotime($row['tgl_pinjam'])); ?></td>
                                        <td class="py-4 px-5 text-red-500 font-medium"><?php echo date('d M Y', strtotime($row['tgl_pengembalian'])); ?></td>
                                        <td class="py-4 px-5 text-center">
                                            <?php if ($is_req_kembali): ?>
                                                <span class="px-2 py-1 bg-yellow-100 text-yellow-800 rounded text-xs font-bold uppercase">Butuh Konfirmasi</span>
                                            <?php else: ?>
                                                <span class="px-2 py-1 bg-blue-100 text-blue-800 rounded text-xs font-bold uppercase">Belum Kembali</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-4 px-5 text-center">
                                            <a href="proses_kembali.php?id=<?php echo $row['id_detail']; ?>" class="bg-green-600 text-white px-3 py-1.5 rounded text-xs font-bold hover:bg-green-700 transition-colors">
                                                <i class="fas fa-check-circle mr-1"></i> Terima Buku
                                            </a>
                                        </td>
                                    </tr>
                                <?php
                                endwhile;
                            else:
                                ?>
                                <tr>
                                    <td colspan="7" class="py-10 text-center text-gray-400">
                                        <i class="fas fa-clipboard-check text-4xl mb-3 block text-gray-300"></i>
                                        Tidak ada data buku yang perlu dikembalikan saat ini.
                                    </td>
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