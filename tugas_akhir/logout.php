<?php
session_start();
// Hapus semua variabel sesi
$_SESSION = array();

// Hancurkan sesi
session_destroy();

// Redirect ke halaman login atau beranda
header("Location: index.php");
exit;
?>