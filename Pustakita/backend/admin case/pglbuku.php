<?php
$host   = "localhost";
$user   = "root";
$pass   = "";
$dbname = "pustakita";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

// Pastikan folder uploads/covers ada
$upload_dir = __DIR__ . '/uploads/covers/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Dummy admin & kategori jika kosong
$check_admin = $conn->query("SELECT id_admin FROM admin LIMIT 1");
if ($check_admin->num_rows == 0) {
    $conn->query("INSERT INTO admin (username, password) VALUES ('admin', '" . password_hash('admin123', PASSWORD_DEFAULT) . "')");
}
$admin_data = $conn->query("SELECT id_admin FROM admin LIMIT 1")->fetch_assoc();
$id_admin   = $admin_data['id_admin'];

$check_kat = $conn->query("SELECT id_kategori FROM kategori LIMIT 1");
if ($check_kat->num_rows == 0) {
    $conn->query("INSERT INTO kategori (id_admin, nama_kategori) VALUES ($id_admin, 'Sains'), ($id_admin, 'Fiksi'), ($id_admin, 'Sejarah'), ($id_admin, 'Bahasa')");
}

// ==========================================
// HELPER: Upload Cover
// ==========================================
function uploadCover($file, $upload_dir, $old_file = null) {
    if (empty($file['name'])) return $old_file; // tidak ada file baru

    $allowed = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp'];
    $max_size = 2 * 1024 * 1024; // 2MB

    if (!in_array($file['type'], $allowed)) {
        return ['error' => 'Format file harus JPG, PNG, atau WEBP.'];
    }
    if ($file['size'] > $max_size) {
        return ['error' => 'Ukuran file maksimal 2MB.'];
    }

    $ext      = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = 'cover_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . strtolower($ext);
    $dest     = $upload_dir . $filename;

    if (!move_uploaded_file($file['tmp_name'], $dest)) {
        return ['error' => 'Gagal menyimpan file.'];
    }

    // Hapus cover lama jika ada
    if ($old_file && file_exists($upload_dir . $old_file)) {
        unlink($upload_dir . $old_file);
    }

    return $filename;
}

// ==========================================
// PROSES CRUD
// ==========================================
$message = "";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {

    // ---- CREATE ----
    if ($_POST['action'] === 'create') {
        $kode_buku    = $conn->real_escape_string(trim($_POST['kode_buku']));
        $judul        = $conn->real_escape_string(trim($_POST['judul']));
        $penulis      = $conn->real_escape_string(trim($_POST['penulis']));
        $penerbit     = $conn->real_escape_string(trim($_POST['penerbit']));
        $tahun_terbit = (int)$_POST['tahun_terbit'];
        $kategori_id  = (int)$_POST['kategori_id'];
        $stok         = (int)$_POST['stok'];
        $deskripsi    = $conn->real_escape_string(trim($_POST['deskripsi']));

        $cek = $conn->query("SELECT id_buku FROM buku WHERE kode_buku = '$kode_buku'");
        if ($cek->num_rows > 0) {
            $message = "Error: Kode buku sudah digunakan!";
        } else {
            // Upload cover
            $foto = null;
            if (!empty($_FILES['foto']['name'])) {
                $result = uploadCover($_FILES['foto'], $upload_dir);
                if (is_array($result) && isset($result['error'])) {
                    $message = "Error: " . $result['error'];
                } else {
                    $foto = $conn->real_escape_string($result);
                }
            }

            if (empty($message)) {
                $foto_sql = $foto ? "'$foto'" : "NULL";
                $sql = "INSERT INTO buku (id_admin, kode_buku, judul, penulis, penerbit, tahun_terbit, kategori_id, stok, deskripsi, foto)
                        VALUES ($id_admin, '$kode_buku', '$judul', '$penulis', '$penerbit', $tahun_terbit, $kategori_id, $stok, '$deskripsi', $foto_sql)";
                if ($conn->query($sql)) {
                    $message = "Sukses: Buku berhasil ditambahkan!";
                } else {
                    $message = "Error: " . $conn->error;
                }
            }
        }
    }

    // ---- UPDATE ----
    elseif ($_POST['action'] === 'update') {
        $id_buku      = (int)$_POST['id_buku'];
        $kode_buku    = $conn->real_escape_string(trim($_POST['kode_buku']));
        $judul        = $conn->real_escape_string(trim($_POST['judul']));
        $penulis      = $conn->real_escape_string(trim($_POST['penulis']));
        $penerbit     = $conn->real_escape_string(trim($_POST['penerbit']));
        $tahun_terbit = (int)$_POST['tahun_terbit'];
        $kategori_id  = (int)$_POST['kategori_id'];
        $stok         = (int)$_POST['stok'];
        $deskripsi    = $conn->real_escape_string(trim($_POST['deskripsi']));
        $foto_lama    = $conn->real_escape_string($_POST['foto_lama'] ?? '');

        // Upload cover baru jika ada
        $foto_sql = $foto_lama ? "'$foto_lama'" : "NULL";
        if (!empty($_FILES['foto']['name'])) {
            $result = uploadCover($_FILES['foto'], $upload_dir, $foto_lama ?: null);
            if (is_array($result) && isset($result['error'])) {
                $message = "Error: " . $result['error'];
            } else {
                $foto_baru = $conn->real_escape_string($result);
                $foto_sql  = "'$foto_baru'";
            }
        }

        // Hapus cover jika centang hapus
        if (!empty($_POST['hapus_foto']) && $foto_lama) {
            if (file_exists($upload_dir . $foto_lama)) unlink($upload_dir . $foto_lama);
            $foto_sql = "NULL";
        }

        if (empty($message)) {
            $sql = "UPDATE buku SET
                    kode_buku='$kode_buku', judul='$judul', penulis='$penulis',
                    penerbit='$penerbit', tahun_terbit=$tahun_terbit,
                    kategori_id=$kategori_id, stok=$stok, deskripsi='$deskripsi',
                    foto=$foto_sql
                    WHERE id_buku=$id_buku";
            if ($conn->query($sql)) {
                $message = "Sukses: Data buku berhasil diperbarui!";
            } else {
                $message = "Error: " . $conn->error;
            }
        }
    }

    // ---- DELETE COVER ONLY ----
    elseif ($_POST['action'] === 'hapus_cover') {
        $id_buku   = (int)$_POST['id_buku'];
        $foto_file = $conn->real_escape_string($_POST['foto_file']);
        if ($foto_file && file_exists($upload_dir . $foto_file)) unlink($upload_dir . $foto_file);
        $conn->query("UPDATE buku SET foto=NULL WHERE id_buku=$id_buku");
        $message = "Sukses: Cover buku berhasil dihapus.";
    }
}

// ---- DELETE BUKU ----
if (isset($_GET['hapus'])) {
    $id_hapus = (int)$_GET['hapus'];
    $buku_del = $conn->query("SELECT foto FROM buku WHERE id_buku=$id_hapus")->fetch_assoc();
    if ($buku_del && $buku_del['foto'] && file_exists($upload_dir . $buku_del['foto'])) {
        unlink($upload_dir . $buku_del['foto']);
    }
    $conn->query("DELETE FROM buku WHERE id_buku=$id_hapus");
    $message = "Sukses: Buku berhasil dihapus.";
}

// ==========================================
// READ BUKU
// ==========================================
$search    = isset($_GET['search']) ? $conn->real_escape_string(trim($_GET['search'])) : '';
$q_buku    = "SELECT b.*, k.nama_kategori FROM buku b LEFT JOIN kategori k ON b.kategori_id = k.id_kategori";
if ($search) $q_buku .= " WHERE b.judul LIKE '%$search%' OR b.kode_buku LIKE '%$search%' OR b.penulis LIKE '%$search%' OR b.penerbit LIKE '%$search%'";
$q_buku   .= " ORDER BY b.id_buku DESC";
$buku_list = $conn->query($q_buku);

$kategori_list    = $conn->query("SELECT * FROM kategori ORDER BY nama_kategori ASC");
$kategori_options = [];
while ($kat = $kategori_list->fetch_assoc()) $kategori_options[] = $kat;

$cover_base = 'uploads/covers/';
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pengelolaan Buku - Pustakita</title>
  <script src="https://cdn.tailwindcss.com"></script>
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
  <script>tailwind.config={theme:{extend:{colors:{brandLight:'#C9D3F8',brandDark:'#0A0F5C'}}}}</script>
  <style>
    .modal-overlay{display:none;position:fixed;inset:0;background:rgba(0,0,0,0.5);z-index:50;align-items:center;justify-content:center}
    .modal-overlay.active{display:flex}
    .drop-zone{border:2px dashed #d1d5db;border-radius:0.5rem;padding:1.5rem;text-align:center;cursor:pointer;transition:border-color .2s}
    .drop-zone:hover,.drop-zone.drag-over{border-color:#0A0F5C;background:#f0f4ff}
    .drop-zone input[type=file]{display:none}
  </style>
</head>
<body class="bg-white flex h-screen font-sans text-gray-800 overflow-hidden">

<!-- SIDEBAR -->
<aside class="w-64 bg-brandLight flex flex-col border-r border-gray-300 flex-shrink-0">
  <div class="h-20 flex items-center px-6 border-b border-gray-300">
    <h1 class="text-2xl font-bold tracking-wide text-black"><span class="text-3xl">P</span>ustaKita</h1>
  </div>
  <nav class="flex-1 py-6 px-4 space-y-2 overflow-y-auto font-medium">
    <a href="dashboard.php" class="flex items-center px-4 py-3 text-black hover:bg-white hover:bg-opacity-40 rounded-lg transition-colors">
      <i class="fas fa-home w-6 text-center mr-3"></i> Dashboard
    </a>
    <a href="pglbuku.php" class="flex items-center px-4 py-3 bg-brandDark text-white rounded-lg">
      <i class="fas fa-book w-6 text-center mr-3"></i> Pengelolaan Buku
    </a>
    <a href="pglmember.php" class="flex items-center px-4 py-3 text-black hover:bg-white hover:bg-opacity-40 rounded-lg transition-colors">
      <i class="fas fa-user-friends w-6 text-center mr-3"></i> Pengelolaan Member
    </a>
    <a href="kelola_peminjaman.php" class="flex items-center px-4 py-3 text-black hover:bg-white hover:bg-opacity-40 rounded-lg transition-colors">
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
      <a href="Login.php" class="flex items-center px-4 py-3 text-black hover:bg-white hover:bg-opacity-40 rounded-lg transition-colors">
        <i class="fas fa-sign-out-alt w-6 text-center mr-3"></i> Log Out
      </a>
    </div>
  </nav>
</aside>

<!-- MAIN -->
<main class="flex-1 flex flex-col bg-white overflow-y-auto">
  <header class="h-20 flex justify-between items-center px-8 border-b border-gray-200 bg-white sticky top-0 z-10">
    <h2 class="text-3xl font-bold text-black">Pengelolaan Buku</h2>
    <div class="flex flex-col items-center">
      <i class="fas fa-user-circle text-3xl text-black"></i>
      <span class="text-sm font-medium mt-1">Admin</span>
    </div>
  </header>

  <div class="p-8 max-w-7xl w-full mx-auto">

    <?php if (!empty($message)): ?>
    <div class="mb-5 px-4 py-3 rounded-lg text-sm font-medium flex items-center gap-2
      <?= str_starts_with($message,'Sukses') ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200' ?>">
      <i class="fas <?= str_starts_with($message,'Sukses') ? 'fa-check-circle' : 'fa-exclamation-circle' ?>"></i>
      <?= htmlspecialchars($message) ?>
    </div>
    <?php endif; ?>

    <!-- TOOLBAR -->
    <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
      <form method="GET" class="flex-1 flex items-center bg-gray-100 rounded-lg border border-gray-300 px-3 py-2 max-w-md focus-within:border-brandDark transition-colors">
        <i class="fas fa-search text-gray-400 mr-2 text-sm"></i>
        <input type="text" name="search" value="<?= htmlspecialchars($search) ?>" placeholder="Cari judul, kode, atau penulis..."
          class="bg-transparent outline-none w-full text-sm">
        <?php if ($search): ?>
          <a href="pglbuku.php" class="text-gray-400 hover:text-gray-600 text-xs ml-2">✕</a>
        <?php endif; ?>
      </form>
      <button onclick="document.getElementById('modalTambah').classList.add('active')"
        class="px-5 py-2.5 bg-brandDark text-white font-medium rounded-lg hover:bg-opacity-90 flex items-center gap-2 shadow-sm">
        <i class="fas fa-plus text-sm"></i> Tambah Buku
      </button>
    </div>

    <!-- TABEL -->
    <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead class="bg-[#F3F4F8] text-gray-600 border-b border-gray-200">
            <tr>
              <th class="py-3.5 px-4 font-semibold w-10 text-center">#</th>
              <th class="py-3.5 px-4 font-semibold w-16 text-center">Cover</th>
              <th class="py-3.5 px-4 font-semibold">Kode</th>
              <th class="py-3.5 px-4 font-semibold">Judul Buku</th>
              <th class="py-3.5 px-4 font-semibold">Penulis</th>
              <th class="py-3.5 px-4 font-semibold">Penerbit</th>
              <th class="py-3.5 px-4 font-semibold text-center">Tahun</th>
              <th class="py-3.5 px-4 font-semibold">Kategori</th>
              <th class="py-3.5 px-4 font-semibold text-center">Stok</th>
              <th class="py-3.5 px-4 font-semibold text-center">Aksi</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-gray-100 text-gray-700">
            <?php if ($buku_list && $buku_list->num_rows > 0):
              $no = 1;
              while ($row = $buku_list->fetch_assoc()):
                $cover_path = $row['foto'] ? $cover_base . htmlspecialchars($row['foto']) : null;
            ?>
            <tr class="hover:bg-gray-50 transition-colors">
              <td class="py-3 px-4 text-center text-gray-400"><?= $no++ ?></td>
              <td class="py-3 px-4 text-center">
                <?php if ($cover_path): ?>
                  <img src="<?= $cover_path ?>" alt="Cover" class="w-10 h-14 object-cover rounded shadow-sm mx-auto cursor-pointer"
                    onclick="previewCover('<?= $cover_path ?>', '<?= htmlspecialchars(addslashes($row['judul'])) ?>')">
                <?php else: ?>
                  <div class="w-10 h-14 bg-gray-100 rounded flex items-center justify-center mx-auto text-gray-300">
                    <i class="fas fa-image text-lg"></i>
                  </div>
                <?php endif; ?>
              </td>
              <td class="py-3 px-4 font-mono text-xs text-brandDark font-semibold"><?= htmlspecialchars($row['kode_buku']) ?></td>
              <td class="py-3 px-4 text-black font-semibold max-w-[180px]">
                <span class="block truncate" title="<?= htmlspecialchars($row['judul']) ?>"><?= htmlspecialchars($row['judul']) ?></span>
              </td>
              <td class="py-3 px-4"><?= htmlspecialchars($row['penulis']) ?></td>
              <td class="py-3 px-4 text-gray-500"><?= htmlspecialchars($row['penerbit'] ?? '-') ?></td>
              <td class="py-3 px-4 text-center"><?= htmlspecialchars($row['tahun_terbit'] ?? '-') ?></td>
              <td class="py-3 px-4">
                <span class="px-2.5 py-1 bg-blue-50 text-blue-700 rounded-full text-xs font-medium"><?= htmlspecialchars($row['nama_kategori'] ?? 'Umum') ?></span>
              </td>
              <td class="py-3 px-4 text-center font-semibold"><?= $row['stok'] ?></td>
              <td class="py-3 px-4 text-center">
                <div class="flex items-center justify-center gap-1.5">
                  <button onclick="openEditModal(<?= htmlspecialchars(json_encode($row)) ?>)"
                    class="px-3 py-1.5 bg-yellow-500 text-white text-xs font-medium rounded hover:bg-yellow-600 transition-colors">
                    <i class="fas fa-edit mr-1"></i> Edit
                  </button>
                  <button onclick="konfirmasiHapus(<?= $row['id_buku'] ?>, '<?= htmlspecialchars(addslashes($row['judul'])) ?>')"
                    class="px-3 py-1.5 bg-red-500 text-white text-xs font-medium rounded hover:bg-red-600 transition-colors">
                    <i class="fas fa-trash"></i>
                  </button>
                </div>
              </td>
            </tr>
            <?php endwhile; else: ?>
            <tr>
              <td colspan="10" class="py-12 text-center text-gray-400">
                <i class="fas fa-book-open text-4xl block mb-2 text-gray-300"></i>
                Tidak ada data buku yang ditemukan.
              </td>
            </tr>
            <?php endif; ?>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</main>

<!-- ===== MODAL TAMBAH ===== -->
<div id="modalTambah" class="modal-overlay">
  <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 max-h-[92vh] flex flex-col">
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50 flex-shrink-0">
      <h3 class="text-lg font-bold">Tambah Data Buku Baru</h3>
      <button onclick="document.getElementById('modalTambah').classList.remove('active')" class="text-gray-400 hover:text-gray-600">
        <i class="fas fa-times text-xl"></i>
      </button>
    </div>
    <form action="pglbuku.php" method="POST" enctype="multipart/form-data" class="overflow-y-auto p-6 space-y-4">
      <input type="hidden" name="action" value="create">

      <!-- Cover Upload -->
      <div>
        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Cover Buku</label>
        <div class="drop-zone" id="addDropZone" onclick="document.getElementById('add_foto').click()"
          ondragover="handleDragOver(event,'addDropZone')" ondragleave="handleDragLeave(event,'addDropZone')"
          ondrop="handleDrop(event,'add_foto','addDropZone','addPreview')">
          <input type="file" name="foto" id="add_foto" accept="image/jpeg,image/png,image/webp"
            onchange="previewImg(this,'addPreview','addDropZone')">
          <div id="addDropZone-content">
            <i class="fas fa-cloud-upload-alt text-2xl text-gray-300 mb-2 block"></i>
            <p class="text-sm text-gray-500">Klik atau seret gambar ke sini</p>
            <p class="text-xs text-gray-400 mt-1">JPG, PNG, WEBP · Maks 2MB</p>
          </div>
          <img id="addPreview" src="" alt="" class="hidden mx-auto max-h-32 rounded object-contain mt-2">
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Kode Buku *</label>
          <input type="text" name="kode_buku" required placeholder="Contoh: BKP-001"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:border-brandDark text-sm">
        </div>
        <div>
          <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Stok *</label>
          <input type="number" name="stok" value="1" min="0" required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:border-brandDark text-sm">
        </div>
      </div>
      <div>
        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Judul Buku *</label>
        <input type="text" name="judul" required placeholder="Judul Lengkap Buku"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:border-brandDark text-sm">
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Penulis *</label>
          <input type="text" name="penulis" required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:border-brandDark text-sm">
        </div>
        <div>
          <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Penerbit</label>
          <input type="text" name="penerbit"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:border-brandDark text-sm">
        </div>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Tahun Terbit</label>
          <input type="number" name="tahun_terbit" min="1900" max="2099" placeholder="YYYY"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:border-brandDark text-sm">
        </div>
        <div>
          <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Kategori *</label>
          <select name="kategori_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:border-brandDark text-sm bg-white">
            <?php foreach ($kategori_options as $kat): ?>
              <option value="<?= $kat['id_kategori'] ?>"><?= htmlspecialchars($kat['nama_kategori']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div>
        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Deskripsi</label>
        <textarea name="deskripsi" rows="3" placeholder="Sinopsis atau info tambahan..."
          class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:border-brandDark text-sm"></textarea>
      </div>
      <div class="pt-4 border-t border-gray-200 flex justify-end gap-2">
        <button type="button" onclick="document.getElementById('modalTambah').classList.remove('active')"
          class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Batal</button>
        <button type="submit" class="px-5 py-2 bg-brandDark text-white rounded-lg text-sm hover:bg-opacity-90">
          <i class="fas fa-save mr-1"></i> Simpan Buku
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ===== MODAL EDIT ===== -->
<div id="modalEdit" class="modal-overlay">
  <div class="bg-white rounded-xl shadow-xl w-full max-w-lg mx-4 max-h-[92vh] flex flex-col">
    <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50 flex-shrink-0">
      <h3 class="text-lg font-bold">Edit Data Buku</h3>
      <button onclick="document.getElementById('modalEdit').classList.remove('active')" class="text-gray-400 hover:text-gray-600">
        <i class="fas fa-times text-xl"></i>
      </button>
    </div>
    <form action="pglbuku.php" method="POST" enctype="multipart/form-data" class="overflow-y-auto p-6 space-y-4">
      <input type="hidden" name="action" value="update">
      <input type="hidden" name="id_buku" id="edit_id_buku">
      <input type="hidden" name="foto_lama" id="edit_foto_lama">

      <!-- Cover Edit -->
      <div>
        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Cover Buku</label>
        <div class="flex gap-3 items-start">
          <div id="editCurrentCover" class="flex-shrink-0 hidden">
            <img id="editCoverImg" src="" alt="Cover saat ini" class="w-16 h-20 object-cover rounded shadow-sm">
            <p class="text-xs text-gray-400 mt-1 text-center">Saat ini</p>
          </div>
          <div class="flex-1">
            <div class="drop-zone" id="editDropZone" onclick="document.getElementById('edit_foto').click()"
              ondragover="handleDragOver(event,'editDropZone')" ondragleave="handleDragLeave(event,'editDropZone')"
              ondrop="handleDrop(event,'edit_foto','editDropZone','editPreview')">
              <input type="file" name="foto" id="edit_foto" accept="image/jpeg,image/png,image/webp"
                onchange="previewImg(this,'editPreview','editDropZone')">
              <div id="editDropZone-content">
                <i class="fas fa-cloud-upload-alt text-xl text-gray-300 mb-1 block"></i>
                <p class="text-xs text-gray-500">Upload cover baru</p>
              </div>
              <img id="editPreview" src="" alt="" class="hidden mx-auto max-h-24 rounded object-contain mt-2">
            </div>
            <label id="editHapusCoverWrap" class="hidden mt-2 flex items-center gap-2 cursor-pointer">
              <input type="checkbox" name="hapus_foto" value="1" id="editHapusFoto">
              <span class="text-xs text-red-500">Hapus cover yang ada</span>
            </label>
          </div>
        </div>
      </div>

      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Kode Buku *</label>
          <input type="text" name="kode_buku" id="edit_kode_buku" required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:border-brandDark text-sm">
        </div>
        <div>
          <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Stok *</label>
          <input type="number" name="stok" id="edit_stok" min="0" required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:border-brandDark text-sm">
        </div>
      </div>
      <div>
        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Judul Buku *</label>
        <input type="text" name="judul" id="edit_judul" required
          class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:border-brandDark text-sm">
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Penulis *</label>
          <input type="text" name="penulis" id="edit_penulis" required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:border-brandDark text-sm">
        </div>
        <div>
          <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Penerbit</label>
          <input type="text" name="penerbit" id="edit_penerbit"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:border-brandDark text-sm">
        </div>
      </div>
      <div class="grid grid-cols-2 gap-4">
        <div>
          <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Tahun Terbit</label>
          <input type="number" name="tahun_terbit" id="edit_tahun_terbit" min="1900" max="2099"
            class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:border-brandDark text-sm">
        </div>
        <div>
          <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Kategori *</label>
          <select name="kategori_id" id="edit_kategori_id" required
            class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:border-brandDark text-sm bg-white">
            <?php foreach ($kategori_options as $kat): ?>
              <option value="<?= $kat['id_kategori'] ?>"><?= htmlspecialchars($kat['nama_kategori']) ?></option>
            <?php endforeach; ?>
          </select>
        </div>
      </div>
      <div>
        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Deskripsi</label>
        <textarea name="deskripsi" id="edit_deskripsi" rows="3"
          class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:border-brandDark text-sm"></textarea>
      </div>
      <div class="pt-4 border-t border-gray-200 flex justify-end gap-2">
        <button type="button" onclick="document.getElementById('modalEdit').classList.remove('active')"
          class="px-4 py-2 border border-gray-300 rounded-lg text-sm hover:bg-gray-50">Batal</button>
        <button type="submit" class="px-5 py-2 bg-yellow-500 text-white rounded-lg text-sm hover:bg-yellow-600">
          <i class="fas fa-save mr-1"></i> Simpan Perubahan
        </button>
      </div>
    </form>
  </div>
</div>

<!-- ===== MODAL HAPUS ===== -->
<div id="modalHapus" class="modal-overlay">
  <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm mx-4 text-center px-6 py-8">
    <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
      <i class="fas fa-trash text-red-500 text-xl"></i>
    </div>
    <h3 class="text-lg font-bold text-gray-800 mb-2">Hapus Buku?</h3>
    <p class="text-sm text-gray-500 mb-6">Buku <strong id="namaHapus"></strong> akan dihapus permanen beserta covernya.</p>
    <div class="flex justify-center gap-3">
      <button onclick="document.getElementById('modalHapus').classList.remove('active')"
        class="px-5 py-2 border border-gray-300 text-gray-700 text-sm rounded-lg hover:bg-gray-50">Batal</button>
      <a id="linkHapus" href="#" class="px-5 py-2 bg-red-600 text-white text-sm rounded-lg hover:bg-red-700">
        <i class="fas fa-trash mr-1"></i> Hapus
      </a>
    </div>
  </div>
</div>

<!-- ===== MODAL PREVIEW COVER ===== -->
<div id="modalPreview" class="modal-overlay" onclick="document.getElementById('modalPreview').classList.remove('active')">
  <div class="text-center" onclick="event.stopPropagation()">
    <img id="previewImg" src="" alt="" class="max-h-[80vh] max-w-[90vw] rounded-lg shadow-2xl object-contain">
    <p id="previewTitle" class="text-white mt-3 text-sm font-medium"></p>
    <button onclick="document.getElementById('modalPreview').classList.remove('active')"
      class="mt-3 px-4 py-2 bg-white bg-opacity-20 text-white text-sm rounded-lg hover:bg-opacity-30">
      <i class="fas fa-times mr-1"></i> Tutup
    </button>
  </div>
</div>

<script>
// ---- Preview gambar setelah dipilih ----
function previewImg(input, previewId, zoneId) {
  const file = input.files[0];
  if (!file) return;
  const preview = document.getElementById(previewId);
  const content = document.getElementById(zoneId + '-content');
  const reader  = new FileReader();
  reader.onload = e => {
    preview.src = e.target.result;
    preview.classList.remove('hidden');
    if (content) content.classList.add('hidden');
  };
  reader.readAsDataURL(file);
}

// ---- Drag & Drop ----
function handleDragOver(e, zoneId) {
  e.preventDefault();
  document.getElementById(zoneId).classList.add('drag-over');
}
function handleDragLeave(e, zoneId) {
  document.getElementById(zoneId).classList.remove('drag-over');
}
function handleDrop(e, inputId, zoneId, previewId) {
  e.preventDefault();
  document.getElementById(zoneId).classList.remove('drag-over');
  const file  = e.dataTransfer.files[0];
  const input = document.getElementById(inputId);
  if (!file || !file.type.startsWith('image/')) return;
  const dt    = new DataTransfer();
  dt.items.add(file);
  input.files = dt.files;
  previewImg(input, previewId, zoneId);
}

// ---- Buka modal Edit dengan data baris ----
function openEditModal(data) {
  document.getElementById('edit_id_buku').value     = data.id_buku;
  document.getElementById('edit_kode_buku').value   = data.kode_buku;
  document.getElementById('edit_judul').value       = data.judul;
  document.getElementById('edit_penulis').value     = data.penulis;
  document.getElementById('edit_penerbit').value    = data.penerbit ?? '';
  document.getElementById('edit_tahun_terbit').value= data.tahun_terbit ?? '';
  document.getElementById('edit_kategori_id').value = data.kategori_id ?? '';
  document.getElementById('edit_stok').value        = data.stok;
  document.getElementById('edit_deskripsi').value   = data.deskripsi ?? '';
  document.getElementById('edit_foto_lama').value   = data.foto ?? '';

  // Reset preview upload baru
  document.getElementById('edit_foto').value = '';
  const ep = document.getElementById('editPreview');
  ep.src = ''; ep.classList.add('hidden');
  const ec = document.getElementById('editDropZone-content');
  if (ec) ec.classList.remove('hidden');

  // Tampilkan cover lama jika ada
  const coverWrap = document.getElementById('editCurrentCover');
  const coverImg  = document.getElementById('editCoverImg');
  const hapusWrap = document.getElementById('editHapusCoverWrap');
  const hapusCb   = document.getElementById('editHapusFoto');
  hapusCb.checked = false;

  if (data.foto) {
    coverImg.src = '<?= $cover_base ?>' + data.foto;
    coverWrap.classList.remove('hidden');
    hapusWrap.classList.remove('hidden');
  } else {
    coverWrap.classList.add('hidden');
    hapusWrap.classList.add('hidden');
  }

  document.getElementById('modalEdit').classList.add('active');
}

// ---- Konfirmasi hapus buku ----
function konfirmasiHapus(id, judul) {
  document.getElementById('namaHapus').textContent = judul;
  document.getElementById('linkHapus').href = 'pglbuku.php?hapus=' + id;
  document.getElementById('modalHapus').classList.add('active');
}

// ---- Preview cover besar ----
function previewCover(src, judul) {
  document.getElementById('previewImg').src   = src;
  document.getElementById('previewTitle').textContent = judul;
  document.getElementById('modalPreview').classList.add('active');
}

// ---- Tutup modal klik luar ----
document.querySelectorAll('.modal-overlay').forEach(overlay => {
  overlay.addEventListener('click', function(e) {
    if (e.target === overlay && overlay.id !== 'modalPreview') {
      overlay.classList.remove('active');
    }
  });
});
</script>
</body>
</html>