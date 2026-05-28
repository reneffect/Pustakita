<?php
// ==========================================
// KONEKSI DATABASE
// ==========================================
if (session_status() === PHP_SESSION_NONE) {
  session_start();
}

$host = "localhost";
$user = "root";
$pass = "";
$db   = "pustakita"; // Sesuaikan nama database-mu

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
  die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil ID admin yang login (default 1 jika belum ada session)
$id_admin = $_SESSION['id_admin'] ?? 1;
$message = "";

// ==========================================
// PROSES KONFIRMASI / ACC OLEH ADMIN
// ==========================================
if (isset($_POST['action'])) {
  $id_detail = (int)$_POST['id_detail'];
  $buku_id   = (int)$_POST['buku_id'];

  // 1. KONDISI: ACC PEMINJAMAN BARU (Otomatis 2 Minggu / 14 Hari)
  if ($_POST['action'] == 'acc_pinjam') {
    $tgl_pinjam       = date('Y-m-d');
    $tgl_pengembalian = date('Y-m-d', strtotime('+14 days')); // Otomatis 2 minggu

    $conn->begin_transaction();
    try {
      // Update status dan tanggal di detail_peminjaman
      $conn->query("UPDATE detail_peminjaman SET 
                            tgl_pinjam = '$tgl_pinjam', 
                            tgl_pengembalian = '$tgl_pengembalian', 
                            status = 'dipinjam',
                            id_admin = $id_admin 
                          WHERE id_detail = $id_detail");

      // Kurangi stok buku
      $conn->query("UPDATE buku SET stok = stok - 1 WHERE id_buku = $buku_id AND stok > 0");

      $conn->commit();
      $message = "Sukses: Peminjaman berhasil disetujui. Batas pengembalian: " . date('d M Y', strtotime($tgl_pengembalian));
    } catch (Exception $e) {
      $conn->rollback();
      $message = "Error: Gagal menyetujui peminjaman.";
    }
  }

  // 2. KONDISI: ACC PERPANJANGAN (Otomatis Tambah 1 Minggu / 7 Hari)
  if ($_POST['action'] == 'acc_perpanjangan') {
    // Ambil tanggal pengembalian lama terlebih dahulu
    $data_lama = $conn->query("SELECT tgl_pengembalian FROM detail_peminjaman WHERE id_detail = $id_detail")->fetch_assoc();
    $tgl_kembali_lama = $data_lama['tgl_pengembalian'];

    // Tambah 7 hari dari tanggal jatuh tempo sebelumnya
    $tgl_pengembalian_baru = date('Y-m-d', strtotime('+7 days', strtotime($tgl_kembali_lama)));

    // Update tanggal pengembalian baru dan kembalikan status ke 'dipinjam'
    $sql_update = "UPDATE detail_peminjaman SET 
                        tgl_pengembalian = '$tgl_pengembalian_baru', 
                        status = 'dipinjam',
                        id_admin = $id_admin 
                       WHERE id_detail = $id_detail";

    if ($conn->query($sql_update)) {
      $message = "Sukses: Perpanjangan disetujui. Batas waktu baru: " . date('d M Y', strtotime($tgl_pengembalian_baru));
    } else {
      $message = "Error: Gagal memproses perpanjangan.";
    }
  }

  // 3. KONDISI: TOLAK PERMINTAAN
  if ($_POST['action'] == 'tolak_pinjam') {
    // Jika ditolak, status diubah menjadi 'ditolak' atau bisa langsung dihapus
    if ($conn->query("UPDATE detail_peminjaman SET status = 'ditolak' WHERE id_detail = $id_detail")) {
      $message = "Sukses: Permintaan peminjaman telah ditolak.";
    }
  }
}

// ==========================================
// QUERY MENAMPILKAN PERMINTAAN (MENUGGU KONFIRMASI)
// ==========================================
$q_request = "
    SELECT dp.id_detail, dp.id_peminjaman, s.username, s.kelas, b.id_buku, b.judul, b.kode_buku, b.stok, dp.tgl_pinjam, dp.tgl_pengembalian, dp.status 
    FROM detail_peminjaman dp
    JOIN siswa s ON dp.id_siswa = s.id_siswa
    JOIN buku b ON dp.buku_id = b.id_buku
    WHERE dp.status IN ('menunggu', 'perpanjangan', 'menunggu perpanjangan')
    ORDER BY dp.id_detail ASC
";
$list_request = $conn->query($q_request);
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Peminjaman - Pustakita</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brandLight: '#C9D3F8',
            brandDark: '#0A0F5C',
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
      <a href="dashboard.php"class="flex items-center px-4 py-3 text-black hover:bg-white hover:bg-opacity-40 rounded-lg transition-colors">
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
      <a href="kelola_peminjaman.php" class="flex items-center px-4 py-3 bg-brandDark text-white rounded-lg">
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
      <h2 class="text-3xl font-bold text-black">Kelola Peminjaman</h2>
      <div class="flex flex-col items-center">
        <i class="fas fa-user-circle text-3xl text-brandDark"></i>
        <span class="text-sm font-medium mt-1">Admin</span>
      </div>
    </header>

    <div class="p-8 max-w-7xl w-full mx-auto">

      <?php if (!empty($message)): ?>
        <div class="mb-6 p-3 rounded <?php echo strpos(strtolower($message), 'error') !== false ? 'bg-red-100 text-red-700 border border-red-200' : 'bg-green-100 text-green-700 border border-green-200'; ?> text-sm font-medium shadow-sm">
          <?php echo $message; ?>
        </div>
      <?php endif; ?>

      <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <div class="p-5 border-b border-gray-100 bg-gray-50">
          <h3 class="font-bold text-brandDark">Konfirmasi Permintaan Member</h3>
          <p class="text-xs text-gray-500 mt-1">Daftar member yang menekan tombol pinjam atau meminta perpanjangan durasi buku.</p>
        </div>
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm border-collapse">
            <thead class="bg-brandDark text-white">
              <tr>
                <th class="py-3 px-5 font-semibold">Tipe Request</th>
                <th class="py-3 px-5 font-semibold">Nama Member</th>
                <th class="py-3 px-5 font-semibold">Buku Yang Dipilih</th>
                <th class="py-3 px-5 font-semibold text-center">Stok Buku</th>
                <th class="py-3 px-5 font-semibold text-center">Durasi Otomatis</th>
                <th class="py-3 px-5 font-semibold text-center">Aksi Konfirmasi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-gray-700 bg-white">
              <?php
              if ($list_request && $list_request->num_rows > 0):
                while ($row = $list_request->fetch_assoc()):
                  // Deteksi jenis request berdasarkan isi status dari member
                  $is_perpanjangan = ($row['status'] == 'perpanjangan' || $row['status'] == 'menunggu perpanjangan');
              ?>
                  <tr class="hover:bg-gray-50 transition-colors">
                    <td class="py-4 px-5">
                      <?php if ($is_perpanjangan): ?>
                        <span class="px-2.5 py-1 bg-yellow-100 text-yellow-800 rounded-md text-xs font-bold uppercase tracking-wide">
                          <i class="fas fa-history mr-1"></i> Perpanjangan
                        </span>
                      <?php else: ?>
                        <span class="px-2.5 py-1 bg-blue-100 text-blue-800 rounded-md text-xs font-bold uppercase tracking-wide">
                          <i class="fas fa-plus-circle mr-1"></i> Pinjam Baru
                        </span>
                      <?php endif; ?>
                    </td>

                    <td class="py-4 px-5">
                      <div class="font-semibold text-black"><?php echo htmlspecialchars($row['username']); ?></div>
                      <div class="text-[11px] text-gray-400">Kelas: <?php echo htmlspecialchars($row['kelas'] ?? '-'); ?></div>
                    </td>

                    <td class="py-4 px-5">
                      <div class="font-semibold text-gray-900 truncate max-w-[220px]"><?php echo htmlspecialchars($row['judul']); ?></div>
                      <div class="text-[11px] text-gray-400">Kode: <?php echo htmlspecialchars($row['kode_buku']); ?></div>
                    </td>

                    <td class="py-4 px-5 text-center font-semibold <?php echo $row['stok'] <= 0 ? 'text-red-500' : 'text-gray-700'; ?>">
                      <?php echo $row['stok']; ?>
                    </td>

                    <td class="py-4 px-5 text-center text-xs font-medium">
                      <?php if ($is_perpanjangan): ?>
                        <span class="text-amber-700 block bg-amber-50 rounded border border-amber-200 py-1 px-2">
                          + 1 Minggu (7 Hari)
                        </span>
                        <span class="text-[10px] text-gray-400 block mt-1">Dari jatuh tempo awal</span>
                      <?php else: ?>
                        <span class="text-blue-700 block bg-blue-50 rounded border border-blue-200 py-1 px-2">
                          2 Minggu (14 Hari)
                        </span>
                        <span class="text-[10px] text-gray-400 block mt-1">Terhitung sejak hari ini</span>
                      <?php endif; ?>
                    </td>

                    <td class="py-4 px-5">
                      <div class="flex justify-center gap-2">
                        <?php if ($is_perpanjangan): ?>
                          <form method="POST" action="" class="inline">
                            <input type="hidden" name="action" value="acc_perpanjangan">
                            <input type="hidden" name="id_detail" value="<?php echo $row['id_detail']; ?>">
                            <button type="submit" class="bg-green-600 text-white px-3 py-1.5 rounded text-xs font-bold hover:bg-green-700 shadow-sm transition-colors">
                              <i class="fas fa-check mr-1"></i> ACC Perpanjang
                            </button>
                          </form>
                        <?php else: ?>
                          <form method="POST" action="" class="inline">
                            <input type="hidden" name="action" value="acc_pinjam">
                            <input type="hidden" name="id_detail" value="<?php echo $row['id_detail']; ?>">
                            <input type="hidden" name="buku_id" value="<?php echo $row['id_buku']; ?>">
                            <button type="submit" <?php echo $row['stok'] <= 0 ? 'disabled class="bg-gray-300 text-gray-500 px-3 py-1.5 rounded text-xs font-bold cursor-not-allowed"' : 'class="bg-blue-600 text-white px-3 py-1.5 rounded text-xs font-bold hover:bg-blue-700 shadow-sm transition-colors"'; ?>>
                              <i class="fas fa-check mr-1"></i> ACC Pinjam
                            </button>
                          </form>
                        <?php endif; ?>

                        <form method="POST" action="" class="inline">
                          <input type="hidden" name="action" value="tolak_pinjam">
                          <input type="hidden" name="id_detail" value="<?php echo $row['id_detail']; ?>">
                          <button type="submit" onclick="return confirm('Yakin ingin menolak permintaan ini?')" class="bg-red-500 text-white px-3 py-1.5 rounded text-xs font-bold hover:bg-red-600 shadow-sm transition-colors">
                            Tolak
                          </button>
                        </form>
                      </div>
                    </td>
                  </tr>
                <?php
                endwhile;
              else:
                ?>
                <tr>
                  <td colspan="6" class="py-12 text-center text-gray-400 font-medium">
                    <i class="fas fa-bell-slash text-4xl mb-3 block text-gray-300"></i>
                    Belum ada permintaan peminjaman atau perpanjangan dari member.
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