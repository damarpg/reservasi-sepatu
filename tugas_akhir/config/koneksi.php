<?php
// Konfigurasi Database
$host = "localhost";
$user = "root"; // Ganti jika Anda menggunakan user lain
$password = ""; // Ganti jika Anda menggunakan password
$database = "tugas_akhir"; // Nama database sesuai permintaan Anda
$port = 8111; // <--- PORT MYSQL YANG SUDAH DISESUAIKAN

// Buat koneksi menggunakan port baru
$koneksi = mysqli_connect($host, $user, $password, $database, $port);

// Cek koneksi
if (mysqli_connect_errno()) {
    echo "Koneksi database gagal: " . mysqli_connect_error();
    exit();
}

// Set timezone (penting untuk reservasi agar waktu server/database sinkron)
date_default_timezone_set('Asia/Jakarta'); 
?>