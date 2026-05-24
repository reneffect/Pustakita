<?php
// seeder_admin.php (hapus setelah dipakai!)
include 'database.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

class AdminSeeder {
    private $db;

    public function __construct($db) {
        $this->db = $db;
    }

    public function seed($username, $password) {
        $check = $this->db->prepare("SELECT id_admin FROM admin WHERE username = ?");
        $check->bind_param("s", $username);
        $check->execute();
        $result = $check->get_result();

        if ($result->num_rows > 0) {
            return "Admin '$username' sudah ada!";
        }

        $hashed = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $this->db->prepare("INSERT INTO admin (username, password) VALUES (?, ?)");
        $stmt->bind_param("ss", $username, $hashed);

        if ($stmt->execute()) {
            return "Admin berhasil dibuat! Username: $username";
        } else {
            return "Gagal: " . $this->db->error;
        }
    }
}

$seeder = new AdminSeeder($koneksi);
$message = $seeder->seed('admin', 'password_anda');
echo $message;
?>