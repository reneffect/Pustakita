<?php
// ==========================================
// KONEKSI DATABASE
// ==========================================
$host = "localhost";
$user = "root";       // Sesuaikan dengan username database Anda
$pass = "";           // Sesuaikan dengan password database Anda
$dbname   = "pustakita";  // Nama database

$conn = new mysqli($host, $user, $pass, $dbname);

if ($conn->connect_error) {
  die("Koneksi gagal: " . $conn->connect_error);
}

// Ambil admin default (Sebagai pencatat siapa yang mengonfirmasi)
$res_admin = $conn->query("SELECT id_admin FROM admin LIMIT 1");
if ($res_admin->num_rows == 0) {
  $conn->query("INSERT INTO admin (username, password) VALUES ('admin', 'admin123')");
  $id_admin = $conn->insert_id;
} else {
  $id_admin = $res_admin->fetch_assoc()['id_admin'];
}

// ==========================================
// PROSES AKSI KONTROL
// (Sesuai ENUM peminjaman: menunggu, dipinjam, dikembalikan, ditolak)
// ==========================================
$message = "";

if (isset($_POST['action'])) {
  $action = $_POST['action'];
  $id_peminjaman = (int)$_POST['id_peminjaman'];

  if ($action == 'kembalikan') {
    // Update ke status 'dikembalikan'
    $sql = "UPDATE peminjaman SET status = 'dikembalikan' WHERE id_peminjaman = $id_peminjaman";
    if ($conn->query($sql)) {
      $message = "Sukses: Buku telah dikembalikan.";
    }
  } elseif ($action == 'setuju_pinjam') {
    // Update dari 'menunggu' ke 'dipinjam'
    // Jika diperlukan, set juga tgl_pinjam menjadi hari ini & tgl_pengembalian +7 hari
    $tgl_pinjam = date('Y-m-d');
    $tgl_pengembalian = date('Y-m-d', strtotime('+7 days'));

    $sql = "UPDATE peminjaman SET 
                status = 'dipinjam', 
                id_admin = $id_admin, 
                tgl_pinjam = '$tgl_pinjam', 
                tgl_pengembalian = '$tgl_pengembalian' 
                WHERE id_peminjaman = $id_peminjaman";
    if ($conn->query($sql)) {
      $message = "Sukses: Permintaan peminjaman disetujui.";
    }
  } elseif ($action == 'tolak_pinjam') {
    // Update dari 'menunggu' ke 'ditolak'
    $sql = "UPDATE peminjaman SET status = 'ditolak', id_admin = $id_admin WHERE id_peminjaman = $id_peminjaman";
    if ($conn->query($sql)) {
      $message = "Info: Permintaan peminjaman telah ditolak.";
    }
  }
}

// ==========================================
// QUERY STATISTIK DASHBOARD ATAS
// ==========================================
$stat_aktif = $conn->query("SELECT COUNT(*) as total FROM peminjaman WHERE status = 'dipinjam'")->fetch_assoc()['total'];
$stat_terlambat = $conn->query("SELECT COUNT(*) as total FROM peminjaman WHERE status = 'dipinjam' AND tgl_pengembalian < CURDATE()")->fetch_assoc()['total'];
$stat_dikembalikan = $conn->query("SELECT COUNT(*) as total FROM peminjaman WHERE status = 'dikembalikan'")->fetch_assoc()['total'];
$stat_menunggu = $conn->query("SELECT COUNT(*) as total FROM peminjaman WHERE status = 'menunggu'")->fetch_assoc()['total'];

// ==========================================
// PENCARIAN & QUERY TABEL UTAMA (Hanya yg sedang dipinjam atau selesai)
// ==========================================
$search = isset($_GET['search']) ? $conn->real_escape_string($_GET['search']) : '';
$q_peminjaman = "
    SELECT p.id_peminjaman, s.username as anggota, b.judul, p.tgl_pinjam, p.tgl_pengembalian, p.status 
    FROM peminjaman p 
    JOIN siswa s ON p.siswa_id = s.id_siswa 
    JOIN buku b ON p.buku_id = b.id_buku 
    WHERE p.status IN ('dipinjam', 'dikembalikan') 
";
if (!empty($search)) {
  $q_peminjaman .= " AND (s.username LIKE '%$search%' OR b.judul LIKE '%$search%') ";
}
$q_peminjaman .= " ORDER BY p.id_peminjaman DESC LIMIT 5";
$list_peminjaman = $conn->query($q_peminjaman);

// QUERY Peminjaman Baru (Yg statusnya 'menunggu')
$q_menunggu = "
    SELECT p.id_peminjaman, p.tgl_pinjam, s.username, s.id_siswa, b.judul 
    FROM peminjaman p 
    JOIN siswa s ON p.siswa_id = s.id_siswa 
    JOIN buku b ON p.buku_id = b.id_buku 
    WHERE p.status = 'menunggu' 
    ORDER BY p.id_peminjaman ASC
";
$list_menunggu = $conn->query($q_menunggu);
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
      <a href="#" class="flex items-center px-4 py-3 bg-brandDark text-white rounded-lg shadow-md">
        <i class="fas fa-clock w-6 text-center mr-3"></i> Kelola Peminjaman
      </a>
      <a href="kelola_pengembalian.php" class="flex items-center px-4 py-3 text-black hover:bg-white hover:bg-opacity-40 rounded-lg transition-colors">
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
      <h2 class="text-3xl font-bold text-black">Kelola Peminjaman</h2>
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
          <p class="text-xs font-semibold mb-1">Dikembalikan</p>
          <p class="text-4xl font-bold"><?php echo $stat_dikembalikan; ?></p>
        </div>
        <div class="bg-brandCardBg rounded-md p-5 text-brandDark shadow-sm flex flex-col justify-center">
          <p class="text-xs font-semibold mb-1">Menunggu Konfirmasi</p>
          <p class="text-4xl font-bold"><?php echo $stat_menunggu; ?></p>
        </div>
      </div>

      <div class="mb-4">
        <form method="GET" class="w-full md:w-1/3 flex items-center border border-gray-300 rounded px-3 py-1.5 focus-within:border-brandDark transition-colors">
          <i class="fas fa-search text-gray-400 mr-2 text-sm"></i>
          <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Cari Member atau Buku..." class="bg-transparent outline-none w-full text-sm">
        </form>
      </div>

      <div class="mb-2">
        <h3 class="text-lg font-bold text-gray-800 mb-3">Daftar Peminjaman Aktif</h3>
      </div>
      <div class="bg-white rounded shadow-sm overflow-hidden mb-10 border border-gray-100">
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm">
            <thead class="bg-brandDark text-white">
              <tr>
                <th class="py-3 px-4 font-semibold text-center w-16">ID</th>
                <th class="py-3 px-4 font-semibold">Anggota</th>
                <th class="py-3 px-4 font-semibold">Buku Dipinjam</th>
                <th class="py-3 px-4 font-semibold">Tgl Pinjam</th>
                <th class="py-3 px-4 font-semibold">Tgl Kembali</th>
                <th class="py-3 px-4 font-semibold text-center">Status</th>
                <th class="py-3 px-4 font-semibold text-center">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-gray-700">
              <?php
              if ($list_peminjaman && $list_peminjaman->num_rows > 0):
                while ($row = $list_peminjaman->fetch_assoc()):
                  $is_late = false;
                  if ($row['status'] == 'dipinjam') {
                    $tgl_pengembalian_time = strtotime($row['tgl_pengembalian']);
                    $hari_ini_time = strtotime(date('Y-m-d'));
                    if ($hari_ini_time > $tgl_pengembalian_time) {
                      $is_late = true;
                    }
                  }
                  $formatted_id = "#" . str_pad($row['id_peminjaman'], 3, '0', STR_PAD_LEFT);
              ?>
                  <tr class="hover:bg-gray-50">
                    <td class="py-3 px-4 text-center font-mono text-xs text-gray-500 bg-gray-100 rounded-full inline-block m-2">
                      <?php echo $formatted_id; ?>
                    </td>
                    <td class="py-3 px-4 text-black font-medium"><?php echo htmlspecialchars($row['anggota']); ?></td>
                    <td class="py-3 px-4 text-gray-600"><?php echo htmlspecialchars($row['judul']); ?></td>
                    <td class="py-3 px-4 text-gray-500"><?php echo date('d M Y', strtotime($row['tgl_pinjam'])); ?></td>
                    <td class="py-3 px-4 text-gray-500 <?php echo $is_late ? 'text-red-500 font-bold' : ''; ?>">
                      <?php echo $row['tgl_pengembalian'] ? date('d M Y', strtotime($row['tgl_pengembalian'])) : '-'; ?>
                    </td>
                    <td class="py-3 px-4 text-center">
                      <?php if ($row['status'] == 'dikembalikan'): ?>
                        <span class="px-3 py-0.5 bg-gray-100 text-gray-600 rounded-full text-[10px] font-bold uppercase tracking-wider">Dikembalikan</span>
                      <?php elseif ($is_late): ?>
                        <span class="px-3 py-0.5 bg-red-100 text-red-600 rounded-full text-[10px] font-bold uppercase tracking-wider">Terlambat</span>
                      <?php else: ?>
                        <span class="px-3 py-0.5 bg-green-100 text-green-600 rounded-full text-[10px] font-bold uppercase tracking-wider">Dipinjam</span>
                      <?php endif; ?>
                    </td>
                    <td class="py-3 px-4 text-center">
                      <?php if ($row['status'] == 'dikembalikan'): ?>
                        <button disabled class="px-3 py-1 bg-gray-200 text-gray-500 text-xs rounded-full font-medium cursor-not-allowed">Selesai</button>
                      <?php elseif ($is_late): ?>
                        <button onclick="window.location.href='#'" class="px-3 py-1 bg-brandDanger text-white text-xs rounded-full font-medium hover:bg-red-700">Denda</button>
                      <?php else: ?>
                        <form method="POST" action="" class="inline">
                          <input type="hidden" name="action" value="kembalikan">
                          <input type="hidden" name="id_peminjaman" value="<?php echo $row['id_peminjaman']; ?>">
                          <button type="submit" class="px-3 py-1 bg-brandDark text-white text-xs rounded-full font-medium hover:bg-opacity-90">Kembalikan</button>
                        </form>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php
                endwhile;
              else:
                ?>
                <tr>
                  <td colspan="7" class="py-6 text-center text-gray-400 text-sm">Tidak ada data peminjaman aktif.</td>
                </tr>
              <?php endif; ?>
            </tbody>
          </table>
        </div>
      </div>

      <div class="mb-4 flex justify-between items-end border-b border-gray-200 pb-2">
        <h3 class="text-lg font-bold text-gray-800">Konfirmasi Peminjaman Baru</h3>
        <span class="bg-yellow-500 text-white text-[10px] font-bold px-2 py-0.5 rounded-full"><?php echo $stat_menunggu; ?> Pending</span>
      </div>
      <div class="space-y-3 mb-10">
        <?php
        if ($list_menunggu && $list_menunggu->num_rows > 0):
          while ($row = $list_menunggu->fetch_assoc()):
            $inisial = strtoupper(substr($row['username'], 0, 2));
        ?>
            <div class="flex items-center justify-between bg-white border border-gray-200 p-4 rounded-lg shadow-sm">
              <div class="flex items-center gap-4">
                <div class="w-10 h-10 rounded-full bg-brandLight text-brandDark flex items-center justify-center font-bold text-sm"><?php echo $inisial; ?></div>
                <div>
                  <p class="font-bold text-sm text-black"><?php echo htmlspecialchars($row['username']); ?> (ID: <?php echo $row['id_siswa']; ?>)</p>
                  <p class="text-xs text-gray-500">Buku: <?php echo htmlspecialchars($row['judul']); ?></p>
                </div>
              </div>
              <div class="text-xs text-gray-400 mr-auto ml-10 hidden md:block">
                Diminta: <?php echo date('d M Y', strtotime($row['tgl_pinjam'])); ?>
              </div>
              <div class="flex gap-2">
                <form method="POST" action="" class="inline">
                  <input type="hidden" name="action" value="setuju_pinjam">
                  <input type="hidden" name="id_peminjaman" value="<?php echo $row['id_peminjaman']; ?>">
                  <button type="submit" class="px-4 py-1.5 border border-green-500 text-green-600 rounded text-xs font-semibold hover:bg-green-50 transition-colors"><i class="fas fa-check mr-1"></i> Setuju</button>
                </form>
                <form method="POST" action="" class="inline">
                  <input type="hidden" name="action" value="tolak_pinjam">
                  <input type="hidden" name="id_peminjaman" value="<?php echo $row['id_peminjaman']; ?>">
                  <button type="submit" class="px-4 py-1.5 border border-red-500 text-red-600 rounded text-xs font-semibold hover:bg-red-50 transition-colors"><i class="fas fa-times mr-1"></i> Tolak</button>
                </form>
              </div>
            </div>
          <?php
          endwhile;
        else:
          ?>
          <div class="text-center py-5 text-gray-400 text-sm border border-gray-200 rounded-lg bg-gray-50">
            Tidak ada permintaan peminjaman yang menunggu.
          </div>
        <?php endif; ?>
      </div>

      <div class="mb-4 border-b border-gray-200 pb-2">
        <h3 class="text-lg font-bold text-gray-800">Konfirmasi Perpanjangan</h3>
      </div>
      <div class="text-center py-5 text-gray-400 text-sm border border-gray-200 rounded-lg bg-gray-50 mb-10">
        Fitur perpanjangan buku tidak tersedia dalam struktur database saat ini.
      </div>

    </div>
  </main>

</body>

</html>