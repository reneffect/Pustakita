<?php
session_start();
session_unset();
session_destroy();
echo "Session berhasil dihapus. <a href='admin case/Login_admin.php'>Klik di sini untuk login</a>";
?>