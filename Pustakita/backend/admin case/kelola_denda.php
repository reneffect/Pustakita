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
$dbname   = "pustakita"; // Sesuaikan jika nama database-mu "pustakita"

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
  die("Koneksi gagal: " . $conn->connect_error);
}

// ==========================================
// PROSES AKSI DENDA
// ==========================================
$message = "";

if (isset($_POST['action'])) {
  // Aksi 1: Bayar Lunas (Cepat)
  if ($_POST['action'] == 'bayar') {
    $id_detail = (int)$_POST['id_detail'];
    $sql = "UPDATE detail_peminjaman SET status_denda = 'lunas' WHERE id_detail = $id_detail";
    if ($conn->query($sql)) {
      $message = "Sukses: Denda berhasil dilunasi.";
    }
  }
  // Aksi 2: Update Data Denda Manual (Dari Form Bawah)
  elseif ($_POST['action'] == 'update_denda') {
    $id_detail = (int)$_POST['id_detail'];
    $jumlah_denda = (int)$_POST['jumlah_denda'];
    $status_denda = $conn->real_escape_string($_POST['status_denda']);

    $sql = "UPDATE detail_peminjaman SET denda = $jumlah_denda, status_denda = '$status_denda' WHERE id_detail = $id_detail";
    if ($conn->query($sql)) {
      $message = "Sukses: Data denda berhasil diperbarui.";
    }
  }
}

// ==========================================
// STATISTIK & QUERY UTAMA
// ==========================================
// Query Statistik atas (Sesuai Wireframe)
$stat_aktif = $conn->query("SELECT COUNT(*) as total FROM peminjaman WHERE status = 'dipinjam'")->fetch_assoc()['total'] ?? 0;
$stat_terlambat = $conn->query("SELECT COUNT(*) as total FROM peminjaman WHERE status = 'dipinjam' AND tgl_pengembalian < CURDATE()")->fetch_assoc()['total'] ?? 0;
$stat_dikembalikan = $conn->query("SELECT COUNT(*) as total FROM peminjaman WHERE status = 'dikembalikan' AND tgl_dikembalikan = CURDATE()")->fetch_assoc()['total'] ?? 0;
$stat_menunggu = $conn->query("SELECT COUNT(*) as total FROM peminjaman WHERE req_kembali = 'menunggu' OR status = 'menunggu'")->fetch_assoc()['total'] ?? 0;

// Pencarian Tabel Denda
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$q_denda = "
    SELECT dp.id_detail, p.id_peminjaman, s.username AS nama_anggota, s.id_siswa, b.judul AS judul_buku, 
           p.tgl_pinjam, p.tgl_pengembalian, dp.denda, dp.status_denda, p.status as status_pinjam
    FROM detail_peminjaman dp
    JOIN peminjaman p ON dp.id_peminjaman = p.id_peminjaman
    JOIN siswa s ON p.siswa_id = s.id_siswa
    JOIN buku b ON p.buku_id = b.id_buku
";
if (!empty($search)) {
  $q_denda .= " WHERE s.username LIKE '%$search%' OR b.judul LIKE '%$search%' ";
}
$q_denda .= " ORDER BY dp.status_denda ASC, dp.id_detail DESC";
$list_denda = $conn->query($q_denda);

// Ambil Data untuk Form Update Denda (Jika tombol Edit ditekan)
$editData = null;
if (isset($_GET['edit'])) {
  $id_edit = (int)$_GET['edit'];
  $q_edit = "
        SELECT dp.*, s.username, s.id_siswa, b.judul, p.tgl_pinjam, p.tgl_pengembalian 
        FROM detail_peminjaman dp
        JOIN peminjaman p ON dp.id_peminjaman = p.id_peminjaman
        JOIN siswa s ON p.siswa_id = s.id_siswa
        JOIN buku b ON p.buku_id = b.id_buku
        WHERE dp.id_detail = $id_edit
    ";
  $editData = $conn->query($q_edit)->fetch_assoc();
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Kelola Denda - Pustakita</title>
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
      <a href="pengelolaan_buku.php" class="flex items-center px-4 py-3 text-black hover:bg-white hover:bg-opacity-40 rounded-lg transition-colors">
        <i class="fas fa-book w-6 text-center mr-3"></i> Pengelolaan Buku
      </a>
      <a href="pengelolaan_member.php" class="flex items-center px-4 py-3 text-black hover:bg-white hover:bg-opacity-40 rounded-lg transition-colors">
        <i class="fas fa-user-friends w-6 text-center mr-3"></i> Pengelolaan Member
      </a>
      <a href="kelola_peminjaman.php" class="flex items-center px-4 py-3 text-black hover:bg-white hover:bg-opacity-40 rounded-lg transition-colors">
        <i class="fas fa-clock w-6 text-center mr-3"></i> Kelola Peminjaman
      </a>
      <a href="kelola_pengembalian.php" class="flex items-center px-4 py-3 text-black hover:bg-white hover:bg-opacity-40 rounded-lg transition-colors">
        <i class="fas fa-exchange-alt w-6 text-center mr-3"></i> Kelola Pengembalian
      </a>
      <a href="kelola_denda.php" class="flex items-center px-4 py-3 bg-brandDark text-white rounded-lg shadow-md">
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
      <h2 class="text-3xl font-bold text-black">Kelola Denda</h2>
      <div class="flex flex-col items-center">
        <i class="fas fa-user-circle text-3xl text-black text-brandDark"></i>
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
          <p class="text-4xl font-bold"><?php echo $stat_dikembalikan; ?></p>
        </div>
        <div class="bg-brandCardBg rounded-md p-5 text-brandDark shadow-sm flex flex-col justify-center">
          <p class="text-xs font-semibold mb-1">Menunggu Konfirmasi</p>
          <p class="text-4xl font-bold"><?php echo $stat_menunggu; ?></p>
        </div>
      </div>

      <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden mb-8">
        <div class="p-5 border-b border-gray-100 flex justify-between items-center bg-gray-50">
          <h3 class="font-bold text-gray-800">Daftar Tagihan & Denda</h3>
          <form method="GET" class="w-1/3 flex items-center border border-gray-300 rounded px-3 py-1.5 focus-within:border-brandDark transition-colors bg-white">
            <i class="fas fa-search text-gray-400 mr-2 text-sm"></i>
            <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Cari Anggota / Buku..." class="bg-transparent outline-none w-full text-sm">
          </form>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm border-collapse">
            <thead class="bg-brandDark text-white">
              <tr>
                <th class="py-3 px-5 font-semibold">ID</th>
                <th class="py-3 px-5 font-semibold">Anggota</th>
                <th class="py-3 px-5 font-semibold">Buku Dipinjam</th>
                <th class="py-3 px-5 font-semibold">Tgl Pinjam</th>
                <th class="py-3 px-5 font-semibold">Tgl Kembali</th>
                <th class="py-3 px-5 font-semibold text-center">Jumlah Denda</th>
                <th class="py-3 px-5 font-semibold text-center">Status</th>
                <th class="py-3 px-5 font-semibold text-center">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-gray-700">
              <?php
              if ($list_denda && $list_denda->num_rows > 0):
                while ($row = $list_denda->fetch_assoc()):
                  $is_lunas = ($row['status_denda'] == 'lunas');
                  $formatted_id = "#" . str_pad($row['id_peminjaman'], 3, '0', STR_PAD_LEFT);
              ?>
                  <tr class="hover:bg-gray-50 transition-colors <?php echo $is_lunas ? 'opacity-70' : ''; ?>">
                    <td class="py-4 px-5 font-mono text-xs text-gray-500 bg-gray-100 rounded-lg inline-block m-2 mt-3">
                      <?php echo $formatted_id; ?>
                    </td>
                    <td class="py-4 px-5 text-black font-medium">
                      <?php echo htmlspecialchars($row['nama_anggota']); ?>
                    </td>
                    <td class="py-4 px-5 text-gray-600 truncate max-w-[150px]">
                      <?php echo htmlspecialchars($row['judul_buku']); ?>
                    </td>
                    <td class="py-4 px-5 text-gray-500">
                      <?php echo date('d M Y', strtotime($row['tgl_pinjam'])); ?>
                    </td>
                    <td class="py-4 px-5 text-red-500 font-medium">
                      <?php echo $row['tgl_pengembalian'] ? date('d M Y', strtotime($row['tgl_pengembalian'])) : '-'; ?>
                    </td>
                    <td class="py-4 px-5 text-center font-bold text-gray-800">
                      Rp <?php echo number_format($row['denda'], 0, ',', '.'); ?>
                    </td>
                    <td class="py-4 px-5 text-center">
                      <span class="px-3 py-1 rounded-full text-[10px] font-bold uppercase tracking-wider <?php echo $is_lunas ? 'bg-green-100 text-green-600' : 'bg-red-100 text-red-600'; ?>">
                        <?php echo htmlspecialchars($row['status_denda']); ?>
                      </span>
                    </td>
                    <td class="py-4 px-5 text-center">
                      <div class="flex justify-center gap-2">
                        <?php if (!$is_lunas): ?>
                          <form method="POST" action="" class="inline">
                            <input type="hidden" name="action" value="bayar">
                            <input type="hidden" name="id_detail" value="<?php echo $row['id_detail']; ?>">
                            <button type="submit" class="bg-green-500 text-white px-3 py-1.5 rounded text-xs font-semibold hover:bg-green-600 shadow-sm" title="Tandai Lunas">Lunas</button>
                          </form>
                        <?php endif; ?>
                        <a href="?edit=<?php echo $row['id_detail']; ?>" class="bg-brandDark text-white px-3 py-1.5 rounded text-xs font-semibold hover:bg-opacity-90 shadow-sm">Detail/Edit</a>
                      </div>
                    </td>
                  </tr>
                <?php
                endwhile;
              else:
                ?>
                <tr>
                  <td colspan="8" class="py-8 text-center text-gray-400 text-sm">Tidak ada data denda / pelanggaran saat ini.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <?php if ($editData): ?>
        <div class="bg-white p-6 rounded-xl shadow-sm border border-gray-100" id="form-update">
          <div class="flex justify-between items-center mb-4 border-b border-gray-100 pb-2">
            <h3 class="font-bold text-brandDark">Update Data Denda</h3>
            <a href="kelola_denda.php" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times"></i></a>
          </div>

          <form method="POST" action="" class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <input type="hidden" name="action" value="update_denda">
            <input type="hidden" name="id_detail" value="<?php echo $editData['id_detail']; ?>">

            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1 uppercase">Nama Anggota</label>
              <input type="text" class="w-full p-2.5 border border-gray-200 bg-gray-50 rounded-lg text-sm text-gray-500" value="<?php echo htmlspecialchars($editData['username']); ?>" readonly>
            </div>
            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1 uppercase">ID Peminjaman</label>
              <input type="text" class="w-full p-2.5 border border-gray-200 bg-gray-50 rounded-lg text-sm text-gray-500" value="#<?php echo str_pad($editData['id_peminjaman'], 3, '0', STR_PAD_LEFT); ?>" readonly>
            </div>
            <div>
              <label class="block text-xs font-bold text-brandDark mb-1 uppercase">Jumlah Denda (Rp) *</label>
              <input type="number" name="jumlah_denda" class="w-full p-2.5 border border-gray-300 rounded-lg text-sm font-bold text-brandDark focus:outline-none focus:border-brandDark focus:ring-1 focus:ring-brandDark" value="<?php echo $editData['denda']; ?>" min="0" required>
            </div>
            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1 uppercase">Buku di Pinjam</label>
              <input type="text" class="w-full p-2.5 border border-gray-200 bg-gray-50 rounded-lg text-sm text-gray-500" value="<?php echo htmlspecialchars($editData['judul']); ?>" readonly>
            </div>
            <div>
              <label class="block text-xs font-bold text-gray-700 mb-1 uppercase">Tanggal Pinjam</label>
              <input type="text" class="w-full p-2.5 border border-gray-200 bg-gray-50 rounded-lg text-sm text-gray-500" value="<?php echo date('d M Y', strtotime($editData['tgl_pinjam'])); ?>" readonly>
            </div>
            <div>
              <label class="block text-xs font-bold text-brandDark mb-1 uppercase">Status Pembayaran *</label>
              <select name="status_denda" class="w-full p-2.5 border border-gray-300 rounded-lg text-sm font-medium focus:outline-none focus:border-brandDark bg-white">
                <option value="belum dibayar" <?php echo $editData['status_denda'] == 'belum dibayar' ? 'selected' : ''; ?>>Belum Dibayar</option>
                <option value="lunas" <?php echo $editData['status_denda'] == 'lunas' ? 'selected' : ''; ?>>Lunas</option>
              </select>
            </div>

            <div class="col-span-1 md:col-span-3 flex justify-end gap-3 mt-2">
              <a href="kelola_denda.php" class="px-6 py-2 border border-gray-300 text-gray-600 font-medium rounded-lg hover:bg-gray-50 text-sm transition-colors">Batal</a>
              <button type="submit" class="px-6 py-2 bg-brandDark text-white font-medium rounded-lg hover:bg-opacity-90 shadow-sm text-sm transition-colors">Simpan Perubahan</button>
            </div>
          </form>
        </div>
        <script>
          window.onload = function() {
            document.getElementById('form-update').scrollIntoView({
              behavior: 'smooth'
            });
          }
        </script>
      <?php endif; ?>

    </div>
  </main>

</body>

</html>