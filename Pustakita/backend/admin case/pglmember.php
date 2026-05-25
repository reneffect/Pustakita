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

// Buat direktori 'uploads' jika belum ada untuk menyimpan foto
$upload_dir = 'uploads/';
if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
}

// ==========================================
// PROSES CRUD (CREATE & UPDATE)
// ==========================================
$message = "";

if (isset($_POST['action'])) {
    $username     = $conn->real_escape_string($_POST['username']);
    $email        = $conn->real_escape_string($_POST['email']);
    $kelas        = $conn->real_escape_string($_POST['kelas']);
    $jurusan      = $conn->real_escape_string($_POST['jurusan']);
    $status_siswa = $conn->real_escape_string($_POST['status_siswa'] ?? 'Aktif'); // Menangkap status siswa
    $password     = $_POST['password'];

    // Handle File Upload Foto
    $foto_name = null;
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] === UPLOAD_ERR_OK) {
        $ext = pathinfo($_FILES['foto']['name'], PATHINFO_EXTENSION);
        $foto_name = time() . '_' . uniqid() . '.' . $ext;
        $tmp_name = $_FILES['foto']['tmp_name'];
        move_uploaded_file($tmp_name, $upload_dir . $foto_name);
    }

    if ($_POST['action'] == 'create') {
        // FITUR: Create Member
        $check_user = $conn->query("SELECT id_siswa FROM siswa WHERE username = '$username' OR email = '$email'");

        if ($check_user->num_rows > 0) {
            $message = "Error: Username atau Email sudah terdaftar!";
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $foto_val = $foto_name ? "'$foto_name'" : "NULL";

            $sql = "INSERT INTO siswa (username, email, password, kelas, jurusan, foto, status_siswa) 
                    VALUES ('$username', '$email', '$hashed_password', '$kelas', '$jurusan', $foto_val, '$status_siswa')";

            if ($conn->query($sql)) {
                $message = "Sukses: Member baru berhasil ditambahkan!";
            } else {
                $message = "Error: " . $conn->error;
            }
        }
    } elseif ($_POST['action'] == 'update') {
        // FITUR: Update Member
        $id_siswa = (int)$_POST['id_siswa'];

        $res_lama = $conn->query("SELECT foto FROM siswa WHERE id_siswa = $id_siswa");
        $data_lama = $res_lama->fetch_assoc();

        $update_parts = [
            "username = '$username'",
            "email = '$email'",
            "kelas = '$kelas'",
            "jurusan = '$jurusan'",
            "status_siswa = '$status_siswa'" // Memperbarui status siswa
        ];

        if (!empty($password)) {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $update_parts[] = "password = '$hashed_password'";
        }

        if ($foto_name) {
            $update_parts[] = "foto = '$foto_name'";
            if (!empty($data_lama['foto']) && file_exists($upload_dir . $data_lama['foto'])) {
                unlink($upload_dir . $data_lama['foto']);
            }
        }

        $sql = "UPDATE siswa SET " . implode(", ", $update_parts) . " WHERE id_siswa = $id_siswa";

        if ($conn->query($sql)) {
            $message = "Sukses: Data member berhasil diperbarui!";
        } else {
            $message = "Error: " . $conn->error;
        }
    }
}

// ==========================================
// FITUR: Search & Read Member
// ==========================================
$search = isset($_GET['search']) ? $_GET['search'] : '';
$q_member = "SELECT * FROM siswa";

if (!empty($search)) {
    $search_safe = $conn->real_escape_string($search);
    $q_member .= " WHERE username LIKE '%$search_safe%' 
                  OR email LIKE '%$search_safe%' 
                  OR kelas LIKE '%$search_safe%' 
                  OR jurusan LIKE '%$search_safe%'
                  OR status_siswa LIKE '%$search_safe%'";
}
$q_member .= " ORDER BY id_siswa DESC";
$member_list = $conn->query($q_member);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengelolaan Member - Pustakita</title>
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
            <h1 class="text-2xl font-bold tracking-wide text-black"><span class="text-3xl">P</span>usataKita</h1>
        </div>
        <nav class="flex-1 py-6 px-4 space-y-2 overflow-y-auto font-medium">
            <a href="dashboard.php" class="flex items-center px-4 py-3 text-black hover:bg-white hover:bg-opacity-40 rounded-lg transition-colors">
                <i class="fas fa-home w-6 text-center mr-3"></i> Dashboard
            </a>
            <a href="pgl_buku.php" class="flex items-center px-4 py-3 text-black hover:bg-white hover:bg-opacity-40 rounded-lg transition-colors">
                <i class="fas fa-book w-6 text-center mr-3"></i> Pengelolaan Buku
            </a>
            <a href="#" class="flex items-center px-4 py-3 bg-brandDark text-white rounded-lg">
                <i class="fas fa-user-friends w-6 text-center mr-3"></i> Pengelolaan Member
            </a>
            <a href="kelola_peminjaman.php" class="flex items-center px-4 py-3 text-black hover:bg-white hover:bg-opacity-40 rounded-lg transition-colors">
                <i class="fas fa-clock w-6 text-center mr-3"></i> Kelola Peminjaman
            </a>
            <a href="#" class="flex items-center px-4 py-3 text-black hover:bg-white hover:bg-opacity-40 rounded-lg transition-colors">
                <i class="fas fa-exchange-alt w-6 text-center mr-3"></i> Kelola Pengembalian
            </a>
            <a href="#" class="flex items-center px-4 py-3 text-black hover:bg-white hover:bg-opacity-40 rounded-lg transition-colors">
                <i class="fas fa-exclamation-circle w-6 text-center mr-3"></i> Kelola Denda
            </a>
            <a href="#" class="flex items-center px-4 py-3 text-black hover:bg-white hover:bg-opacity-40 rounded-lg transition-colors">
                <i class="fas fa-clipboard-list w-6 text-center mr-3"></i> Laporan
            </a>
            <div class="pt-8">
                <a href="#" class="flex items-center px-4 py-3 text-black hover:bg-white hover:bg-opacity-40 rounded-lg transition-colors">
                    <i class="fas fa-sign-out-alt w-6 text-center mr-3"></i> Log Out
                </a>
            </div>
        </nav>
    </aside>

    <main class="flex-1 flex flex-col bg-white overflow-y-auto">
        <header class="h-20 flex justify-between items-center px-8 border-b border-gray-200 bg-white sticky top-0 z-10">
            <h2 class="text-3xl font-bold text-black">Pengelolaan Member</h2>
            <div class="flex flex-col items-center">
                <i class="fas fa-user-circle text-3xl text-black"></i>
                <span class="text-sm font-medium mt-1">Admin</span>
            </div>
        </header>

        <div class="p-8 max-w-7xl w-full mx-auto">

            <?php if (!empty($message)): ?>
                <div class="mb-4 p-4 rounded-lg <?php echo strpos($message, 'Sukses') !== false ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <div class="flex flex-col md:flex-row justify-between items-center gap-4 mb-6">
                <form method="GET" action="" class="w-full md:w-96 flex items-center bg-gray-100 rounded-lg border border-gray-300 px-3 py-2">
                    <i class="fas fa-search text-gray-400 mr-2"></i>
                    <input type="text" name="search" value="<?php echo htmlspecialchars($search); ?>" placeholder="Cari username, email, kelas..." class="bg-transparent outline-none w-full text-sm">
                </form>

                <button onclick="toggleModal('addMemberModal')" class="px-5 py-2.5 bg-brandDark text-white font-medium rounded-lg hover:bg-opacity-90 flex items-center justify-center shadow-sm">
                    <i class="fas fa-user-plus mr-2 text-sm"></i> Tambah Member
                </button>
            </div>

            <div class="bg-white border border-gray-200 rounded-xl shadow-sm overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full text-left text-sm">
                        <thead class="bg-[#F3F4F8] text-gray-600 border-b border-gray-200">
                            <tr>
                                <th class="py-3 px-5 font-semibold text-center">Foto</th>
                                <th class="py-3 px-5 font-semibold">Username</th>
                                <th class="py-3 px-5 font-semibold">Email</th>
                                <th class="py-3 px-5 font-semibold text-center">Kelas</th>
                                <th class="py-3 px-5 font-semibold">Jurusan</th>
                                <th class="py-3 px-5 font-semibold text-center">Status</th>
                                <th class="py-3 px-5 font-semibold text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 text-gray-700">
                            <?php
                            if ($member_list && $member_list->num_rows > 0):
                                while ($row = $member_list->fetch_assoc()):
                            ?>
                                    <tr class="hover:bg-gray-50 transition-colors">
                                        <td class="py-3 px-5 text-center flex justify-center">
                                            <?php if (!empty($row['foto']) && file_exists($upload_dir . $row['foto'])): ?>
                                                <img src="<?php echo $upload_dir . $row['foto']; ?>" alt="Foto" class="w-10 h-10 rounded-full object-cover border border-gray-300">
                                            <?php else: ?>
                                                <div class="w-10 h-10 rounded-full bg-gray-200 flex items-center justify-center text-gray-500">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 px-5 text-black font-semibold"><?php echo htmlspecialchars($row['username']); ?></td>
                                        <td class="py-3 px-5"><?php echo htmlspecialchars($row['email'] ?? '-'); ?></td>
                                        <td class="py-3 px-5 text-center">
                                            <span class="px-2.5 py-1 bg-purple-50 text-purple-700 rounded-full text-xs font-medium">
                                                <?php echo htmlspecialchars($row['kelas'] ?? '-'); ?>
                                            </span>
                                        </td>
                                        <td class="py-3 px-5 text-gray-500"><?php echo htmlspecialchars($row['jurusan'] ?? '-'); ?></td>
                                        <td class="py-3 px-5 text-center">
                                            <?php if (isset($row['status_siswa']) && $row['status_siswa'] == 'Aktif'): ?>
                                                <span class="px-2.5 py-1 bg-green-100 text-green-700 rounded-md text-xs font-bold border border-green-200"><i class="fas fa-check-circle mr-1"></i>Aktif</span>
                                            <?php else: ?>
                                                <span class="px-2.5 py-1 bg-red-100 text-red-700 rounded-md text-xs font-bold border border-red-200"><i class="fas fa-times-circle mr-1"></i>NonAktif</span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="py-3 px-5 text-center">
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
                                    <td colspan="7" class="py-10 text-center text-gray-400 font-medium">Tidak ada data member yang ditemukan.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <div id="addMemberModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-lg w-full overflow-hidden max-h-[90vh] flex flex-col">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                <h3 class="text-lg font-bold text-black">Tambah Member Baru</h3>
                <button onclick="toggleModal('addMemberModal')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
            </div>
            <form action="" method="POST" enctype="multipart/form-data" class="overflow-y-auto p-6 space-y-4">
                <input type="hidden" name="action" value="create">

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Username *</label>
                    <input type="text" name="username" required placeholder="Contoh: andi_123" class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:border-brandDark text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Email</label>
                    <input type="email" name="email" placeholder="Contoh: andi@gmail.com" class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:border-brandDark text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Password *</label>
                    <input type="password" name="password" required placeholder="Masukkan password" class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:border-brandDark text-sm">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Kelas</label>
                        <input type="text" name="kelas" placeholder="Contoh: X, XI, XII" class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:border-brandDark text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Jurusan</label>
                        <input type="text" name="jurusan" placeholder="Contoh: RPL, TKJ" class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:border-brandDark text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Status Member *</label>
                    <select name="status_siswa" required class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:border-brandDark text-sm bg-white">
                        <option value="Aktif" selected>Aktif</option>
                        <option value="NonAktif">NonAktif</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Foto Profil (Opsional)</label>
                    <input type="file" name="foto" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:border-brandDark text-sm file:mr-4 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-brandLight file:text-brandDark hover:file:bg-gray-200">
                </div>

                <div class="pt-4 border-t border-gray-200 flex justify-end gap-2">
                    <button type="button" onclick="toggleModal('addMemberModal')" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium hover:bg-gray-50">Batal</button>
                    <button type="submit" class="px-5 py-2 bg-brandDark text-white rounded-lg text-sm font-medium hover:bg-opacity-90">Simpan Member</button>
                </div>
            </form>
        </div>
    </div>

    <div id="editMemberModal" class="fixed inset-0 bg-black bg-opacity-50 z-50 flex items-center justify-center hidden p-4">
        <div class="bg-white rounded-xl shadow-xl max-w-lg w-full overflow-hidden max-h-[90vh] flex flex-col">
            <div class="px-6 py-4 border-b border-gray-200 flex justify-between items-center bg-gray-50">
                <h3 class="text-lg font-bold text-black">Ubah/Edit Data Member</h3>
                <button onclick="toggleModal('editMemberModal')" class="text-gray-400 hover:text-gray-600"><i class="fas fa-times text-xl"></i></button>
            </div>
            <form action="" method="POST" enctype="multipart/form-data" class="overflow-y-auto p-6 space-y-4">
                <input type="hidden" name="action" value="update">
                <input type="hidden" name="id_siswa" id="edit_id_siswa">

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Username *</label>
                    <input type="text" name="username" id="edit_username" required class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:border-brandDark text-sm bg-gray-50">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Email</label>
                    <input type="email" name="email" id="edit_email" class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:border-brandDark text-sm">
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Password Baru (Kosongkan jika tidak diubah)</label>
                    <input type="password" name="password" placeholder="Ketik password baru" class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:border-brandDark text-sm">
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Kelas</label>
                        <input type="text" name="kelas" id="edit_kelas" class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:border-brandDark text-sm">
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Jurusan</label>
                        <input type="text" name="jurusan" id="edit_jurusan" class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:border-brandDark text-sm">
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Status Member *</label>
                    <select name="status_siswa" id="edit_status_siswa" required class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:border-brandDark text-sm bg-white">
                        <option value="Aktif">Aktif</option>
                        <option value="NonAktif">NonAktif</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-gray-700 uppercase mb-1">Ganti Foto Profil (Opsional)</label>
                    <input type="file" name="foto" accept="image/*" class="w-full px-3 py-2 border border-gray-300 rounded-lg outline-none focus:border-brandDark text-sm file:mr-4 file:py-1.5 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-brandLight file:text-brandDark">
                    <p class="text-[10px] text-gray-400 mt-1">*Biarkan kosong jika tidak ingin mengubah foto</p>
                </div>

                <div class="pt-4 border-t border-gray-200 flex justify-end gap-2">
                    <button type="button" onclick="toggleModal('editMemberModal')" class="px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium hover:bg-gray-50">Batal</button>
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
            document.getElementById('edit_id_siswa').value = data.id_siswa;
            document.getElementById('edit_username').value = data.username;
            document.getElementById('edit_email').value = data.email ?? '';
            document.getElementById('edit_kelas').value = data.kelas ?? '';
            document.getElementById('edit_jurusan').value = data.jurusan ?? '';

            // Injeksi Status Siswa ke dropdown
            const statusSelect = document.getElementById('edit_status_siswa');
            if (data.status_siswa) {
                statusSelect.value = data.status_siswa;
            } else {
                statusSelect.value = 'Aktif'; // Default fallback
            }

            toggleModal('editMemberModal');
        }
    </script>
</body>

</html>