<?php
session_start();
include('config/koneksi.php');

// Proteksi Pemilik: Hanya Pemilik (Manajerial) yang boleh mengakses
if (!isset($_SESSION['hak_akses']) || $_SESSION['hak_akses'] !== 'Pemilik') {
    header('Location: login.php');
    exit;
}

// Logika Filter Tanggal (Opsional: untuk laporan yang lebih spesifik)
$filter_dari = isset($_GET['dari']) ? $_GET['dari'] : date('Y-m-01');
$filter_sampai = isset($_GET['sampai']) ? $_GET['sampai'] : date('Y-m-t');

// Query untuk mengambil data yang statusnya 'Selesai' dalam rentang tanggal
$query_laporan = "SELECT 
                    R.tgl_reservasi, 
                    R.nama_pelanggan, 
                    R.jumlah_sepatu, 
                    L.nama_layanan, 
                    L.harga,
                    (R.jumlah_sepatu * L.harga) AS total_bayar
                  FROM reservasi R
                  JOIN layanan L ON R.id_layanan = L.id_layanan
                  WHERE R.status = 'Selesai' 
                  AND R.tgl_reservasi BETWEEN '$filter_dari' AND '$filter_sampai'
                  ORDER BY R.tgl_reservasi ASC";

$result_laporan = mysqli_query($koneksi, $query_laporan);

$total_pendapatan_global = 0; // Variabel untuk menghitung total pendapatan
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Owner - Laporan Keuangan</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        /* CSS Khusus untuk Laporan */
        @media print {
            .no-print { display: none; }
            body { background: white; color: black; }
            .admin-table th { background-color: #f0f0f0 !important; color: black !important; }
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
                Laporan Keuangan (Reservasi Selesai)
            </h1>
            
            <div class="no-print" style="background-color: #2e2e2e; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
                <h3 style="color: var(--color-text-light);">Filter Tanggal</h3>
                <form method="GET" action="owner_laporan_keuangan.php" style="display: flex; gap: 10px; margin-top: 10px; align-items: center;">
                    Dari: <input type="date" name="dari" value="<?= $filter_dari; ?>" required style="padding: 5px; background-color: #1a1a1a; color: var(--color-text-light); border: 1px solid var(--color-secondary);">
                    Sampai: <input type="date" name="sampai" value="<?= $filter_sampai; ?>" required style="padding: 5px; background-color: #1a1a1a; color: var(--color-text-light); border: 1px solid var(--color-secondary);">
                    <button type="submit" class="btn-primary" style="padding: 7px 15px;">Tampilkan</button>
                    <button type="button" onclick="window.print()" class="btn-primary" style="padding: 7px 15px; background-color: #00bcd4;">Cetak Laporan</button>
                </form>
            </div>

            <p style="color: #aaa;">Periode: **<?= date('d M Y', strtotime($filter_dari)); ?>** sampai **<?= date('d M Y', strtotime($filter_sampai)); ?>**</p>
            
            <table class="admin-table">
                <thead>
                    <tr>
                        <th>Tgl Selesai</th>
                        <th>Nama Pelanggan</th>
                        <th>Layanan</th>
                        <th>Jml Sepatu</th>
                        <th>Harga Satuan</th>
                        <th>Total Pendapatan</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result_laporan) > 0): ?>
                        <?php while($data = mysqli_fetch_assoc($result_laporan)): 
                            $total_pendapatan_global += $data['total_bayar'];
                        ?>
                            <tr>
                                <td><?= $data['tgl_reservasi']; ?></td>
                                <td><?= $data['nama_pelanggan']; ?></td>
                                <td><?= $data['nama_layanan']; ?></td>
                                <td><?= $data['jumlah_sepatu']; ?></td>
                                <td>Rp <?= number_format($data['harga'], 0, ',', '.'); ?></td>
                                <td style="font-weight: bold;">Rp <?= number_format($data['total_bayar'], 0, ',', '.'); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">Tidak ada transaksi Selesai dalam periode ini.</td>
                        </tr>
                    <?php endif; ?>
                    <tr>
                        <td colspan="5" style="text-align: right; font-size: 1.2em; color: var(--color-accent);">TOTAL PENDAPATAN BERSIH:</td>
                        <td style="font-size: 1.2em; font-weight: bold; color: var(--color-accent);">Rp <?= number_format($total_pendapatan_global, 0, ',', '.'); ?></td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
<?php 
mysqli_close($koneksi); 
?>