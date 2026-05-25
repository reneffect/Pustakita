<?php
// ==========================================
// KONEKSI DATABASE
// ==========================================
include 'database.php';
session_start();

// ==========================================
// QUERY STATISTIK DASHBOARD
// ==========================================

// 1. Total Buku (Menghitung dari tabel buku)
$res_buku = $conn->query("SELECT COUNT(*) as total FROM buku");
$tot_buku = $res_buku->fetch_assoc()['total'];

// 2. Peminjaman Aktif (Status 'dipinjam')
$res_pinjam = $conn->query("SELECT COUNT(*) as total FROM peminjaman WHERE status = 'dipinjam'");
$tot_pinjam = $res_pinjam->fetch_assoc()['total'];

// 3. Total Member (Menghitung dari tabel siswa)
$res_member = $conn->query("SELECT COUNT(*) as total FROM siswa");
$tot_member = $res_member->fetch_assoc()['total'];

// 4. Aktivitas Terbaru (Menampilkan 3 peminjaman terakhir)
$q_aktivitas = "SELECT p.id_peminjaman, b.judul, p.tgl_pinjam 
                FROM peminjaman p 
                JOIN buku b ON p.buku_id = b.id_buku 
                ORDER BY p.tgl_pinjam DESC LIMIT 3";
$aktivitas = $conn->query($q_aktivitas);

// 5. Buku Populer Minggu Ini (Buku yang paling sering dipinjam)
$q_populer = "SELECT b.judul, b.penulis, COUNT(p.buku_id) as jumlah_pinjam 
              FROM peminjaman p 
              JOIN buku b ON p.buku_id = b.id_buku 
              GROUP BY p.buku_id 
              ORDER BY jumlah_pinjam DESC LIMIT 4";
$populer = $conn->query($q_populer);
?>

<!DOCTYPE html>
<html lang="id">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Dashboard Admin - Pustakita</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script>
    tailwind.config = {
      theme: {
        extend: {
          colors: {
            brandLight: '#C9D3F8', // Warna Sidebar/Accent terang
            brandDark: '#0A0F5C', // Warna Biru Tua (Tombol aktif & Kartu)
          }
        }
      }
    }
  </script>
</head>

<body class="bg-white flex h-screen font-sans text-gray-800 overflow-hidden">

  <aside class="w-64 bg-brandLight flex flex-col border-r border-gray-300">
    <div class="h-20 flex items-center px-6 border-b border-gray-300 border-opacity-50">
      <h1 class="text-2xl font-bold tracking-wide text-black">
        <span class="text-3xl">P</span>ustaKita
      </h1>
    </div>

    <nav class="flex-1 py-6 px-4 space-y-2 overflow-y-auto font-medium">
      <a href="dashboard.php" class="flex items-center px-4 py-3 bg-brandDark text-white rounded-lg">
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
      <a href="kelola_pemijaman.php" class="flex items-center px-4 py-3 text-black hover:bg-white hover:bg-opacity-40 rounded-lg transition-colors">
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
        <a href="Login.php" class="flex items-center px-4 py-3 text-black hover:bg-white hover:bg-opacity-40 rounded-lg transition-colors">
          <i class="fas fa-sign-out-alt w-6 text-center mr-3"></i>
          Log Out
        </a>
      </div>
    </nav>
  </aside>

  <main class="flex-1 flex flex-col bg-white overflow-y-auto">
    <header class="h-20 flex justify-between items-center px-8 border-b border-gray-200 bg-white sticky top-0 z-10">
      <h2 class="text-3xl font-bold text-black">Dashboard</h2>
      <div class="flex flex-col items-center cursor-pointer">
        <i class="fas fa-user-circle text-3xl text-black"></i>
        <span class="text-sm font-medium mt-1">Admin</span>
      </div>
    </header>

    <div class="p-8 max-w-6xl">
      <div class="mb-8">
        <h3 class="text-2xl font-bold text-black mb-1">Halo, Admin!</h3>
        <p class="text-gray-600">Welcome Admin! Silakan kelola dan pantau aktivitas dengan bijak.</p>
      </div>

      <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-brandDark rounded-xl p-6 text-white shadow-md">
          <p class="text-sm text-gray-200 mb-2">Total Buku</p>
          <p class="text-4xl font-bold"><?php echo number_format($tot_buku); ?></p>
        </div>
        <div class="bg-brandDark rounded-xl p-6 text-white shadow-md">
          <p class="text-sm text-gray-200 mb-2">Peminjaman Aktif</p>
          <p class="text-4xl font-bold"><?php echo number_format($tot_pinjam); ?></p>
        </div>
        <div class="bg-brandDark rounded-xl p-6 text-white shadow-md">
          <p class="text-sm text-gray-200 mb-2">Total Member</p>
          <p class="text-4xl font-bold"><?php echo number_format($tot_member); ?></p>
        </div>
      </div>

      <div class="bg-[#EAEFFD] rounded-xl p-6 mb-8 shadow-sm">
        <h4 class="text-lg font-bold text-brandDark mb-4">Aktivitas Terbaru</h4>
        <div class="space-y-4">
          <?php
          if ($aktivitas && $aktivitas->num_rows > 0):
            while ($row = $aktivitas->fetch_assoc()):
              // Format ID Peminjaman agar terlihat seperti #004
              $formatted_id = str_pad($row['id_peminjaman'], 3, '0', STR_PAD_LEFT);

              // Logika sederhana untuk keterangan waktu (Bisa disesuaikan dengan fungsi waktu yang lebih kompleks)
              $tgl_pinjam = strtotime($row['tgl_pinjam']);
              $hari_ini = strtotime(date('Y-m-d'));
              $selisih = ($hari_ini - $tgl_pinjam) / (60 * 60 * 24);

              if ($selisih == 0) $waktu = "Hari Ini";
              elseif ($selisih == 1) $waktu = "Kemarin";
              else $waktu = $selisih . " Hari lalu";
          ?>
              <div class="flex justify-between items-center border-b border-gray-400 pb-2">
                <span class="text-black font-medium w-1/4">Peminjaman #<?php echo $formatted_id; ?></span>
                <span class="text-gray-500 mx-2">—</span>
                <span class="text-black flex-1"><?php echo htmlspecialchars($row['judul']); ?></span>
                <span class="text-gray-600 text-sm w-1/4 text-right"><?php echo $waktu; ?></span>
              </div>
          <?php
            endwhile;
          else:
            echo "<p class='text-gray-500'>Belum ada aktivitas terbaru.</p>";
          endif;
          ?>
        </div>
      </div>

      <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
        <h4 class="text-sm font-bold text-brandDark p-5 border-b border-gray-200">Buku Populer Minggu Ini</h4>
        <div class="overflow-x-auto">
          <table class="w-full text-left text-sm">
            <thead class="bg-[#F3F4F8] text-gray-600">
              <tr>
                <th class="py-3 px-5 font-semibold">#</th>
                <th class="py-3 px-5 font-semibold">Judul</th>
                <th class="py-3 px-5 font-semibold">Pengarang</th>
                <th class="py-3 px-5 font-semibold">Dipinjam</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-gray-100 text-gray-800">
              <?php
              if ($populer && $populer->num_rows > 0):
                $no = 1;
                while ($row = $populer->fetch_assoc()):
              ?>
                  <tr class="hover:bg-gray-50">
                    <td class="py-3 px-5"><?php echo $no++; ?></td>
                    <td class="py-3 px-5 text-black font-medium"><?php echo htmlspecialchars($row['judul']); ?></td>
                    <td class="py-3 px-5"><?php echo htmlspecialchars($row['penulis']); ?></td>
                    <td class="py-3 px-5 text-gray-600"><?php echo $row['jumlah_pinjam']; ?>x</td>
                  </tr>
                <?php
                endwhile;
              else:
                ?>
                <tr>
                  <td colspan="4" class="py-4 px-5 text-center text-gray-500">Data buku populer tidak tersedia.</td>
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