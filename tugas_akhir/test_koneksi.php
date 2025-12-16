<?php
// Sertakan file koneksi yang sudah kita buat
include('config/koneksi.php');

echo "<!DOCTYPE html>
<html lang='id'>
<head>
    <title>Uji Koneksi</title>
    <style>
        body { font-family: sans-serif; background-color: #1a1a1a; color: #f0f0f0; padding: 20px; }
        .success { color: #ffd700; font-weight: bold; } /* Kuning (Accent) */
        .failure { color: #ff0000; font-weight: bold; }
    </style>
</head>
<body>";

// Periksa apakah variabel koneksi berhasil dibuat tanpa error
if ($koneksi) {
    echo "<h1>Hasil Uji Koneksi</h1>";
    echo "<p class='success'>✅ Koneksi ke database 'tugas_akhir' di port 8111 BERHASIL!</p>";
    echo "<p>Anda dapat melanjutkan ke tahap pengembangan fitur.</p>";
    // Tutup koneksi setelah pengujian selesai
    mysqli_close($koneksi);
} else {
    // Pesan error akan muncul dari logika di file koneksi.php
    echo "<h1>Hasil Uji Koneksi</h1>";
    echo "<p class='failure'>❌ Koneksi GAGAL!</p>";
    echo "<p>Cek kembali konfigurasi XAMPP Anda dan pastikan MySQL berjalan di port 8111.</p>";
}

echo "</body>
</html>";
?>