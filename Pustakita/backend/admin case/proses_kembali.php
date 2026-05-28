<?php
session_start();
include 'database.php'; // Pastikan sesuai dengan nama file koneksi kamu

if (!isset($_GET['id'])) {
    header("Location: kelola_pengembalian.php?pesan=Error: ID Peminjaman tidak ditemukan.");
    exit();
}

$id_detail = intval($_GET['id']);
$db_conn = isset($koneksi) ? $koneksi : $conn; 

mysqli_begin_transaction($db_conn);

try {
    // 1. Ambil data peminjaman yang akan dikembalikan
    $query_cek = mysqli_query($db_conn, "SELECT id_peminjaman, buku_id, tgl_pengembalian FROM detail_peminjaman WHERE id_detail = $id_detail");
    
    if (mysqli_num_rows($query_cek) == 0) {
        throw new Exception("Data peminjaman tidak ditemukan.");
    }
    
    $data_pinjam = mysqli_fetch_assoc($query_cek);
    $buku_id = $data_pinjam['buku_id'];
    $id_peminjaman = $data_pinjam['id_peminjaman'];
    $tgl_jatuh_tempo = $data_pinjam['tgl_pengembalian'];
    
    // 2. Hitung Denda Keterlambatan
    $tgl_hari_ini = date('Y-m-d');
    $denda = 0;
    $status_denda = 'lunas'; 
    
    if (strtotime($tgl_hari_ini) > strtotime($tgl_jatuh_tempo)) {
        $selisih_detik = strtotime($tgl_hari_ini) - strtotime($tgl_jatuh_tempo);
        $selisih_hari = floor($selisih_detik / (60 * 60 * 24)); 
        
        $tarif_denda_per_hari = 5000; // Tarif denda Rp 5.000 / hari
        $denda = $selisih_hari * $tarif_denda_per_hari;
        $status_denda = 'belum dibayar';
    }

    // 3. Update tabel detail_peminjaman
    // PERBAIKAN: Nilai req_kembali diubah menjadi 'belum' agar sesuai dengan opsi ENUM di database
    $query_detail = "UPDATE detail_peminjaman 
                     SET status = 'dikembalikan', req_kembali = 'belum' 
                     WHERE id_detail = $id_detail";
    if (!mysqli_query($db_conn, $query_detail)) {
        throw new Exception("Gagal mengupdate detail peminjaman.");
    }

    // 4. Update tabel peminjaman
    $query_peminjaman = "UPDATE peminjaman 
                         SET denda = $denda, status_denda = '$status_denda' 
                         WHERE id_peminjaman = $id_peminjaman";
    if (!mysqli_query($db_conn, $query_peminjaman)) {
        throw new Exception("Gagal mengupdate denda peminjaman.");
    }

    // 5. Kembalikan stok buku ke perpustakaan (+1)
    $query_buku = "UPDATE buku SET stok = stok + 1 WHERE id_buku = $buku_id";
    if (!mysqli_query($db_conn, $query_buku)) {
        throw new Exception("Gagal mengupdate stok buku.");
    }

    mysqli_commit($db_conn);
    
    if ($denda > 0) {
        $pesan = "Buku berhasil dikembalikan! Peminjam terlambat dan dikenakan denda Rp " . number_format($denda, 0, ',', '.');
    } else {
        $pesan = "Buku berhasil dikembalikan tepat waktu. Stok buku telah ditambahkan.";
    }
    
    header("Location: kelola_pengembalian.php?pesan=" . urlencode($pesan));
    exit();

} catch (Exception $e) {
    mysqli_rollback($db_conn);
    
    $pesan_error = "Error: " . $e->getMessage();
    header("Location: kelola_pengembalian.php?pesan=Error: " . urlencode($e->getMessage()));
    exit();
}
?>