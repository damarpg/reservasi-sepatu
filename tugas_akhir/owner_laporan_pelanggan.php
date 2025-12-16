<?php
session_start();
include('config/koneksi.php');

// Proteksi Pemilik: Hanya Pemilik (Manajerial) yang boleh mengakses
if (!isset($_SESSION['hak_akses']) || $_SESSION['hak_akses'] !== 'Pemilik') {
    header('Location: login.php');
    exit;
}

// Query 1: Menghitung total pelanggan unik untuk rekap
$query_total_unik = "SELECT COUNT(DISTINCT nama_pelanggan, telp_pelanggan) AS total_unik FROM reservasi";
$result_total_unik = mysqli_query($koneksi, $query_total_unik);
$total_pelanggan_unik = mysqli_fetch_assoc($result_total_unik)['total_unik'] ?? 0;


// Query 2: Mengambil data unik pelanggan dan riwayat reservasi mereka (untuk detail tabel)
$query_pelanggan = "SELECT 
                        nama_pelanggan, 
                        telp_pelanggan, 
                        COUNT(id_reservasi) AS total_reservasi,
                        MAX(tgl_reservasi) AS reservasi_terakhir
                    FROM reservasi 
                    GROUP BY nama_pelanggan, telp_pelanggan
                    ORDER BY total_reservasi DESC";

$result_pelanggan = mysqli_query($koneksi, $query_pelanggan);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner - Laporan Data Pelanggan</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        @media print {
            .no-print { display: none; }
            body { background: white; color: black; }
            .admin-table th { background-color: #f0f0f0 !important; color: black !important; }
        }
        .rekap-box {
            background-color: var(--color-accent);
            color: var(--color-darker);
            padding: 20px;
            border-radius: 8px;
            text-align: center;
            margin-bottom: 20px;
        }
        .rekap-box h2 {
            margin: 0;
            font-size: 3em;
        }
        .rekap-box p {
            margin: 0;
            font-size: 1.2em;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="dashboard-layout">
        <div class="no-print">
            <?php include('includes/sidebar.php'); ?>
        </div>

        <div class="content">
            <div class="no-print" style="text-align: right; margin-bottom: 20px;">
                <a href="logout.php" class="btn-primary" style="padding: 10px 20px; font-size: 0.9em;">LOGOUT</a>
            </div>

            <h1 style="color: var(--color-accent); margin-bottom: 20px;">
                Laporan Data Pelanggan
            </h1>
            
            <div class="rekap-box">
                <h2><?= $total_pelanggan_unik; ?></h2>
                <p>TOTAL PELANGGAN UNIK TERDAFTAR</p>
            </div>
            
            <div class="no-print" style="margin-bottom: 20px;">
                <button type="button" onclick="window.print()" class="btn-primary" style="padding: 7px 15px; background-color: #00bcd4;">Cetak Laporan Pelanggan</button>
            </div>
            
            <h3 style="color: var(--color-accent); margin-bottom: 15px;">Riwayat Detail Pelanggan</h3>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Nama Pelanggan</th>
                        <th>No. Telepon</th>
                        <th>Total Reservasi</th>
                        <th>Reservasi Terakhir</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result_pelanggan) > 0): ?>
                        <?php while($data = mysqli_fetch_assoc($result_pelanggan)): ?>
                            <tr>
                                <td><?= $data['nama_pelanggan']; ?></td>
                                <td><?= $data['telp_pelanggan']; ?></td>
                                <td style="font-weight: bold;"><?= $data['total_reservasi']; ?> kali</td>
                                <td><?= $data['reservasi_terakhir']; ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="4" style="text-align: center;">Belum ada data pelanggan yang tercatat.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
<?php 
mysqli_close($koneksi); 
?>