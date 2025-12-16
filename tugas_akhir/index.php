<?php 
// Sertakan file koneksi database
include('config/koneksi.php'); 
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nature Clean Shoes | Reservasi Jasa Cuci Sepatu</title>
    <link rel="stylesheet" href="assets/style.css"> 
</head>
<body>
    
    <?php include('includes/header.php'); ?> <section class="hero">
        <h1>TINGKATKAN KUALITAS SEPATUMU</h1>
        <p>
            100% TERPERCAYA SELAMA BERTAHUN-TAHUN
        </p>
        <a href="reservasi.php" class="btn-primary" style="padding: 15px 40px; font-size: 1.1em;">
            PESAN LAYANAN SEKARANG
        </a>
    </section>

    <section id="layanan">
        <h2>Pilihan Layanan Kami</h2>
        <div class="layanan-container" style="display: flex; justify-content: center; gap: 30px; flex-wrap: wrap;">
            <?php
            // Query untuk mengambil data layanan dari database
            $query = "SELECT * FROM layanan ORDER BY harga ASC";
            $result = mysqli_query($koneksi, $query);

            if (mysqli_num_rows($result) > 0) {
                while($data = mysqli_fetch_assoc($result)) {
            ?>
                <div class="service-card" style="width: 300px; background-color: #2e2e2e; padding: 20px; border-radius: 8px; border: 1px solid var(--color-secondary);">
                    <h3 style="color: var(--color-accent); margin-bottom: 10px;"><?= htmlspecialchars($data['nama_layanan']); ?></h3>
                    <p style="color: #ccc; min-height: 70px;"><?= htmlspecialchars($data['deskripsi']); ?></p>
                    <p style="font-size: 1.5em; font-weight: bold; margin: 15px 0;">
                        Rp <?= number_format($data['harga'], 0, ',', '.'); ?>
                    </p>
                    <a href="reservasi.php?layanan=<?= $data['id_layanan']; ?>" class="btn-primary" style="width: 100%;">
                        Pesan
                    </a>
                </div>
            <?php
                }
            } else {
                echo "<p>Belum ada layanan yang tersedia.</p>";
            }
            mysqli_close($koneksi); 
            ?>
        </div>
    </section>

    <footer style="background-color: var(--color-darker); padding: 20px; border-top: 1px solid var(--color-secondary);">
        <p>&copy; 2025 Nature Clean Shoes | Muhammad Nur Pua Geno 22120048</p>
    </footer>
</body>
</html>