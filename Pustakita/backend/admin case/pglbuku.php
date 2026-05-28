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

// Catatan Struktural: Pastikan Anda sudah menjalankan perintah sql berikut di phpMyAdmin:
// ALTER TABLE buku ADD COLUMN cover VARCHAR(255) NULL AFTER deskripsi;

// Buat direktori upload jika belum ada
$upload_dir = 'uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0755, true);
}

// Pastikan ada data Admin & Kategori minimal agar tidak terjadi error Foreign Key Constraint
$check_admin = $conn->query("SELECT id_admin FROM admin LIMIT 1");
if ($check_admin->num_rows == 0) {
    $conn->query("INSERT INTO admin (username, password) VALUES ('admin', '" . password_hash('admin123', PASSWORD_DEFAULT) . "')");
}
$admin_data = $conn->query("SELECT id_admin FROM admin LIMIT 1")->fetch_assoc();
$id_admin = $admin_data['id_admin'];

$check_kategori = $conn->query("SELECT id_kategori FROM kategori LIMIT 1");
if ($check_kategori->num_rows == 0) {
    $conn->query("INSERT INTO kategori (id_admin, nama_kategori) VALUES ($id_admin, 'Sains'), ($id_admin, 'Fiksi'), ($id_admin, 'Sejarah'), ($id_admin, 'Bahasa')");
}

// ==========================================
// PROSES CRUD (CREATE & UPDATE)
// ==========================================
$message = "";

if (isset($_POST['action'])) {
    if ($_POST['action'] == 'create') {
        // FITUR: Create Buku
        $kode_buku    = $_POST['kode_buku'];
        $judul        = $_POST['judul'];
        $penulis      = $_POST['penulis'];
        $penerbit     = $_POST['penerbit'];
        $tahun_terbit = $_POST['tahun_terbit'];
        $kategori_id  = $_POST['kategori_id'];
        $stok         = $_POST['stok'];
        $deskripsi    = $_POST['deskripsi'];
        
        // Handle upload cover
        $cover_name = null;
        if (isset($_FILES['cover']) && $_FILES['cover']['error'] == 0) {
            $ext = pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION);
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            
            if (in_array(strtolower($ext), $allowed)) {
                $cover_name = time() . '_' . uniqid() . '.' . $ext;
                move_uploaded_file($_FILES['cover']['tmp_name'], $upload_dir . $cover_name);
            } else {
                $message = "Error: Format gambar tidak valid! (Gunakan jpg, jpeg, png, atau webp)";
            }
        }

        if (strpos($message, 'Error') === false) {
            // Cek kode buku unik
            $check_kode = $conn->query("SELECT id_buku FROM buku WHERE kode_buku = '$kode_buku'");
            if ($check_kode->num_rows > 0) {
                $message = "Error: Kode buku sudah digunakan!";
                // hapus kembali file jika gagal simpan ke db
                if ($cover_name) @unlink($upload_dir . $cover_name);
            } else {
                $sql = "INSERT INTO buku (id_admin, kode_buku, judul, penulis, penerbit, tahun_terbit, kategori_id, stok, deskripsi, cover) 
                        VALUES ($id_admin, '$kode_buku', '$judul', '$penulis', '$penerbit', '$tahun_terbit', $kategori_id, $stok, '$deskripsi', " . ($cover_name ? "'$cover_name'" : "NULL") . ")";
                if ($conn->query($sql)) {
                    $message = "Sukses: Buku berhasil ditambahkan!";
                } else {
                    $message = "Error: " . $conn->error;
                    if ($cover_name) @unlink($upload_dir . $cover_name);
                }
            }
        }
    } elseif ($_POST['action'] == 'update') {
        // FITUR: Update Buku
        $id_buku      = $_POST['id_buku'];
        $kode_buku    = $_POST['kode_buku'];
        $judul        = $_POST['judul'];
        $penulis      = $_POST['penulis'];
        $penerbit     = $_POST['penerbit'];
        $tahun_terbit = $_POST['tahun_terbit'];
        $kategori_id  = $_POST['kategori_id'];
        $stok         = $_POST['stok'];
        $deskripsi    = $_POST['deskripsi'];

        // Ambil data buku lama untuk cek cover lama
        $old_data = $conn->query("SELECT cover FROM buku WHERE id_buku = $id_buku")->fetch_assoc();
        $cover_name = $old_data['cover'];

        // Handle upload cover baru jika ada
        if (isset($_FILES['cover']) && $_FILES['cover']['error'] == 0) {
            $ext = pathinfo($_FILES['cover']['name'], PATHINFO_EXTENSION);
            $allowed = ['jpg', 'jpeg', 'png', 'webp'];
            
            if (in_array(strtolower($ext), $allowed)) {
                $new_cover_name = time() . '_' . uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES['cover']['tmp_name'], $upload_dir . $new_cover_name)) {
                    // Hapus cover lama dari folder jika ada
                    if (!empty($cover_name) && file_exists($upload_dir . $cover_name)) {
                        @unlink($upload_dir . $cover_name);
                    }
                    $cover_name = $new_cover_name;
                }
            } else {
                $message = "Error: Format gambar tidak valid! (Gunakan jpg, jpeg, png, atau webp)";
            }
        }

        if (strpos($message, 'Error') === false) {
            $sql = "UPDATE buku SET 
                    kode_buku = '$kode_buku', 
                    judul = '$judul', 
                    penulis = '$penulis', 
                    penerbit = '$penerbit', 
                    tahun_terbit = '$tahun_terbit', 
                    kategori_id = $kategori_id, 
                    stok = $stok, 
                    deskripsi = '$deskripsi',
                    cover = " . ($cover_name ? "'$cover_name'" : "NULL") . " 
                    WHERE id_buku = $id_buku";

            if ($conn->query($sql)) {
                $message = "Sukses: Data buku berhasil diperbarui!";
            } else {
                $message = "Error: " . $conn->error;
            }
        }
    }
}

// ==========================================
// FITUR: Search & Read Buku
// ==========================================
$search = isset($_GET['search']) ? $_GET['search'] : '';

$q_buku = "SELECT b.*, k.nama_kategori FROM buku b 
           LEFT JOIN kategori k ON b.kategori_id = k.id_kategori";

if (!empty($search)) {
    $q_buku .= " WHERE b.judul LIKE '%$search%' 
                OR b.kode_buku LIKE '%$search%' 
                OR b.penulis LIKE '%$search%' 
                OR b.penerbit LIKE '%$search%'";
}
$q_buku .= " ORDER BY b.id_buku DESC";
$buku_list = $conn->query($q_buku);

$kategori_list = $conn->query("SELECT * FROM kategori ORDER BY nama_kategori ASC");
$kategori_options = [];
while ($kat = $kategori_list->fetch_assoc()) {
    $kategori_options[] = $kat;
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengelolaan Buku - Pustakita</title>
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

<body class="bg-white flex h-screen font-sans text-gray-800 overflow-hidden">

    <aside class="w-64 bg-brandLight flex flex-col border-r border-gray-300">
        <div class="h-20 flex items-center px-6 border-b border-gray-300 border-opacity-50">
            <h1 class="text-2xl font-bold tracking-wide text-black"><span class="text-3xl">P</span>ustaKita</h1>
        </div>
        <nav class="flex-1 py-6 px-4 space-y-2 overflow-y-auto font-medium">
            <a href="dashboard.php" class="flex items-center px-4 py-3 text-black hover:bg-white hover:bg-opacity-40 rounded-lg transition-colors">
                <i class="fas fa-home w-6 text-center mr-3"></i> Dashboard
            </a>
            <a href="#" class="flex items-center px-4 py-3 bg-brandDark text-white rounded-lg">
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
                <a href="logout.php" class="flex items-center px-4 py-3 text-black hover:bg-white hover:bg-opacity-40 rounded-lg transition-colors">
                    <i class="fas fa-sign-out-alt w-6 text-center mr-3"></i> Log Out
                </a>
            </div>
        </nav>
    </aside>

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
                <div class="mb-4 p-4 rounded-lg <?php echo strpos($message, 'Sukses') !== false ? 'bg-green-100 text-green-800 border border-green-200' : 'bg-red-100 text-red-800 border border-red-200'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
                <form method="GET" action="" class="w-full md:w-96 flex items-center bg-gray-100 rounded-lg border border-gray-300 px-3 py-2 focus-within:border-brandDark transition-colors">
                    <i class="fas fa-search text-gray-400 mr-2"></i>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Cari judul, kode, atau penulis..." class="bg-transparent outline-none w-full text-sm">
                    <?php if (!empty($search)): ?>
                        <a href="pengelolaan_buku.php" class="text-gray-400 hover:text-gray-600 text-xs ml-1">Clear</a>
                    <?php endif; ?>
                </form>

                <button onclick="toggleModal('addModal')" class="w-full md:w-auto px-5 py-2.5 bg-brandDark text-white font-medium rounded-lg hover:bg-opacity-90 flex items-center justify-center transition-all shadow-sm">
                    <i class="fas fa-plus mr-2 text-sm"></i> Tambah Buku
                </button>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-[#F3F4F8] text-gray-600 border-b border-gray-200">
                            <tr>
                                <th class="py-3.5 px-5 font-semibold w-12 text-center">#</th>
                                <th class="py-3.5 px-5 font-semibold w-20 text-center">Cover</th>
                                <th class="py-3.5 px-5 font-semibold">Kode Buku</th>
                                <th class="py-3.5 px-5 font-semibold">Judul Buku</th>
                                <th class="py-3.5 px-5 font-semibold">Penulis</th>
                                <th class="py-3.5 px-5 font-semibold">Penerbit</th>
                                <th class="py-3.5 px-5 font-semibold text-center">Tahun</th>
                                <th class="py-3.5 px-5 font-semibold">Kategori</th>
                                <th class="py-3.5 px-5 font-semibold text-center">Stok</th>
                                <th class="py-3.5 px-5 font-semibold text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700">
                            <?php
                            if ($buku_list && $buku_list->num_rows > 0):
                                $no = 1;
                                while ($row = $buku_list->fetch_assoc()):
                            ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="py-3.5 px-5 text-center font-medium text-gray-500"><?php echo $no++; ?></td>
                                        <td class="py-2 px-3 text-center">
                                            <?php if (!empty($row['cover']) && file_exists($upload_dir . $row['cover'])): ?>
                                                <img src="<?php echo $upload_dir . htmlspecialchars($row['cover']); ?>" alt="Cover" class="w-12 h-16 object-cover rounded shadow-sm mx-auto">
                                            <?php else: ?>
                                                <div class="w-12 h-16 bg-gray-200 rounded flex items-center justify-center text-gray-400 mx-auto text-xs"><i class="fas fa-image"></i></div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3.5 px-5 font-mono text-xs text-brandDark font-semibold"><?php echo htmlspecialchars($row['kode_buku']); ?></td>
                                        <td class="py-3.5 px-5 text-black font-semibold"><?php echo htmlspecialchars($row['judul']); ?></td>
                                        <td class="py-3.5 px-5"><?php echo htmlspecialchars($row['penulis']); ?></td>
                                        <td class="py-3.5 px-5 text-gray-500"><?php echo htmlspecialchars($row['penerbit'] ?? '-'); ?></td>
                                        <td class="py-3.5 px-5 text-center"><?php echo htmlspecialchars($row['tahun_terbit'] ?? '-'); ?></td>
                                        <td class="py-3.5 px-5">
                                            <span class="px-2.5 py-1 bg-blue-50 text-blue-700 rounded-full text-xs font-medium">
                                                <?php echo htmlspecialchars($row['nama_kategori'] ?? 'Umum'); ?>
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-5 text-center font-semibold"><?php echo $row['stok']; ?></td>
                                        <td class="py-3.5 px-5 text-center">
                                            <button
                                                onclick="openEditModal(<?php echo htmlspecialchars(json_encode($row)); ?>)"
                                                class="px-3 py-1.5 bg-yellow-500 text-white text-xs font-medium rounded hover:bg-yellow-600 transition-colors inline-flex items-center">
                                                <i class="fas fa-edit mr-1"></i> Edit
                                            </button>
                                        </td>
                                    </tr>
                            <?php
                                endwhile;
                            else:
                            ?>
                                <tr>
                                    <td colspan="10" class="py-10 text-center text-gray-400 font-medium">
                                        <i class="fas fa-book-open text-4xl mb-3 block text-gray-300"></i>
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

    <div id="addModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-lg w-full overflow-hidden max-h-[90vh] flex flex-col">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                <h3 class="text-lg font-bold text-black">Tambah Data Buku Baru</h3>
                <button onclick="toggleModal('addModal')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
            </div>
            <form action="" method="POST" enctype="multipart/form-data" class="overflow-y-auto p-6 space-y-4">
                <input type="hidden" name="action" value="create">

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Kode Buku *</label>
                        <input type="text" name="kode_buku" required placeholder="Contoh: BKP-001" class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:border-brandDark text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Stok *</label>
                        <input type="number" name="stok" value="1" min="0" required class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:border-brandDark text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Judul Buku *</label>
                    <input type="text" name="judul" required placeholder="Judul Lengkap Buku" class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:border-brandDark text-sm">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Penulis *</label>
                        <input type="text" name="penulis" required placeholder="Nama Penulis" class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:border-brandDark text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Penerbit</label>
                        <input type="text" name="penerbit" placeholder="Nama Penerbit" class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:border-brandDark text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Tahun Terbit</label>
                        <input type="number" name="tahun_terbit" min="1900" max="2099" placeholder="YYYY" class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:border-brandDark text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Kategori Buku *</label>
                        <select name="kategori_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:border-brandDark text-sm bg-white">
                            <?php foreach ($kategori_options as $kat): ?>
                                <option value="<?php echo $kat['id_kategori']; ?>"><?php echo htmlspecialchars($kat['nama_kategori']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Deskripsi Ringkas</label>
                    <textarea name="deskripsi" rows="3" placeholder="Sinopsis atau info tambahan buku..." class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:border-brandDark text-sm"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Cover Buku</label>
                    <input type="file" name="cover" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-brandDark hover:file:bg-blue-100">
                    <p class="text-gray-400 text-xs mt-1">Format yang diizinkan: JPG, JPEG, PNG, WEBP.</p>
                </div>

                <div class="pt-4 border-t border-gray-200 flex justify-end gap-2">
                    <button type="button" onclick="toggleModal('addModal')" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium hover:bg-gray-50">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-brandDark text-white rounded-lg text-sm font-medium hover:bg-opacity-90">Simpan Buku</button>
                </div>
            </form>
        </div>
    </div>

    <div id="editModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-lg w-full overflow-hidden max-h-[90vh] flex flex-col">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                <h3 class="text-lg font-bold text-black">Ubah/Edit Data Buku</h3>
                <button onclick="toggleModal('editModal')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
            </div>
            <form action="" method="POST" enctype="multipart/form-data" class="overflow-y-auto p-6 space-y-4">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id_buku" id="edit_id_buku">

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Kode Buku *</label>
                        <input type="text" name="kode_buku" id="edit_kode_buku" required class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:border-brandDark text-sm bg-gray-50">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Stok *</label>
                        <input type="number" name="stok" id="edit_stok" min="0" required class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:border-brandDark text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Judul Buku *</label>
                    <input type="text" name="judul" id="edit_judul" required class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:border-brandDark text-sm">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Penulis *</label>
                        <input type="text" name="penulis" id="edit_penulis" required class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:border-brandDark text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Penerbit</label>
                        <input type="text" name="penerbit" id="edit_penerbit" class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:border-brandDark text-sm">
                    </div>
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Tahun Terbit</label>
                        <input type="number" name="tahun_terbit" id="edit_tahun_terbit" min="1900" max="2099" class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:border-brandDark text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Kategori Buku *</label>
                        <select name="kategori_id" id="edit_kategori_id" required class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:border-brandDark text-sm bg-white">
                            <?php foreach ($kategori_options as $kat): ?>
                                <option value="<?php echo $kat['id_kategori']; ?>"><?php echo htmlspecialchars($kat['nama_kategori']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Deskripsi Ringkas</label>
                    <textarea name="deskripsi" id="edit_deskripsi" rows="3" class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:border-brandDark text-sm"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Ganti Cover Buku</label>
                    <div class="flex items-center gap-4">
                        <div id="edit_cover_preview" class="w-12 h-16 bg-gray-100 rounded border flex items-center justify-center text-xs text-gray-400 overflow-hidden">
                            </div>
                        <input type="file" name="cover" accept="image/*" class="w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-brandDark hover:file:bg-blue-100">
                    </div>
                    <p class="text-gray-400 text-xs mt-1">Biarkan kosong jika tidak ingin mengubah cover.</p>
                </div>

                <div class="pt-4 border-t border-gray-200 flex justify-end gap-2">
                    <button type="button" onclick="toggleModal('editModal')" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium hover:bg-gray-50">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-yellow-500 text-white rounded-lg text-sm font-medium hover:bg-yellow-600">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function toggleModal(modalId) {
            const modal = document.getElementById(modalId);
            if (modal.classList.contains('hidden')) {
                modal.classList.remove('hidden');
            } else {
                modal.classList.add('hidden');
            }
        }

        function openEditModal(data) {
            document.getElementById('edit_id_buku').value = data.id_buku;
            document.getElementById('edit_kode_buku').value = data.kode_buku;
            document.getElementById('edit_judul').value = data.judul;
            document.getElementById('edit_penulis').value = data.penulis;
            document.getElementById('edit_penerbit').value = data.penerbit ?? '';
            document.getElementById('edit_tahun_terbit').value = data.tahun_terbit ?? '';
            document.getElementById('edit_kategori_id').value = data.kategori_id ?? '';
            document.getElementById('edit_stok').value = data.stok;
            document.getElementById('edit_deskripsi').value = data.deskripsi ?? '';

            // Update preview gambar di modal edit
            const previewDiv = document.getElementById('edit_cover_preview');
            if (data.cover) {
                previewDiv.innerHTML = `<img src="uploads/${data.cover}" class="w-full h-full object-cover">`;
            } else {
                previewDiv.innerHTML = `<i class="fas fa-image"></i>`;
            }

            toggleModal('editModal');
        }
    </script>
</body>

</html>