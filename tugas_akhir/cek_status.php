<?php
session_start();
include('config/koneksi.php');

$search_result = null;
$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $keyword = mysqli_real_escape_string($koneksi, $_POST['keyword']);
    
    // Asumsi pelanggan mencari dengan ID Reservasi atau Nomor Telepon
    if (empty($keyword)) {
        $error = "Masukkan ID Reservasi atau Nomor Telepon Anda.";
    } else {
        // Query mencari reservasi berdasarkan ID atau Telepon
        $query = "SELECT 
                    R.*, L.nama_layanan, L.harga 
                  FROM reservasi R
                  JOIN layanan L ON R.id_layanan = L.id_layanan
                  WHERE R.id_reservasi = '$keyword' OR R.telp_pelanggan = '$keyword'
                  ORDER BY R.id_reservasi DESC LIMIT 1";

        $result = mysqli_query($koneksi, $query);
        
        if (mysqli_num_rows($result) > 0) {
            $search_result = mysqli_fetch_assoc($result);
        } else {
            $error = "Data reservasi tidak ditemukan. Pastikan ID atau Nomor Telepon sudah benar.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cek Status Reservasi | Nature Clean Shoes</title>
    <link rel="stylesheet" href="assets/style.css"> 
    <style>
        .status-box {
            background-color: #1a1a1a;
            padding: 20px;
            border-radius: 8px;
            border-left: 5px solid var(--color-accent);
        }
        .status-box p {
            margin-bottom: 10px;
        }
        .status-badge {
            display: inline-block;
            padding: 8px 15px;
            border-radius: 4px;
            font-weight: bold;
            text-transform: uppercase;
        }
        .status-Menunggu-Pembayaran { background-color: #f44336; color: white; }
        .status-Menunggu-Verifikasi { background-color: #ff9800; color: white; }
        .status-Dikonfirmasi { background-color: #4CAF50; color: white; }
        .status-Diproses { background-color: #2196F3; color: white; }
        .status-Selesai { background-color: var(--color-accent); color: var(--color-darker); }
        .status-Dibatalkan { background-color: #333; color: #f44336; }
    </style>
</head>
<body>
    
    <?php include('includes/header.php'); ?>

    <section style="max-width: 800px; margin: 50px auto; background-color: #2e2e2e; padding: 40px; border-radius: 8px;">
        <h2 style="text-align: center; color: var(--color-accent); margin-bottom: 25px;">Lacak Status Pencucian Sepatu Anda</h2>
        
        <?php if ($error): ?>
            <p style="color: red; text-align: center; margin-bottom: 15px; font-weight: bold;"><?= $error; ?></p>
        <?php endif; ?>

        <form method="POST" action="cek_status.php" style="text-align: center; margin-bottom: 30px;">
            <input type="text" name="keyword" placeholder="Masukkan ID Reservasi atau No. Telp" required 
                style="width: 70%; padding: 12px; margin-right: 10px; background-color: #1a1a1a; color: var(--color-text-light); border: 1px solid var(--color-secondary);">
            <button type="submit" class="btn-primary" style="padding: 12px 25px;">Cek Status</button>
        </form>

        <?php if ($search_result): 
            $status_class = str_replace(' ', '-', $search_result['status']);
            $total_harga = $search_result['harga'] * $search_result['jumlah_sepatu'];
        ?>
            <h3 style="color: var(--color-text-light); border-bottom: 2px solid var(--color-accent); padding-bottom: 5px; margin-bottom: 20px;">
                Detail Pesanan #<?= $search_result['id_reservasi']; ?>
            </h3>
            
            <div class="status-box">
                <p>Status Saat Ini:</p>
                <div class="status-badge status-<?= $status_class; ?>">
                    <?= $search_result['status']; ?>
                </div>

                <table style="width: 100%; margin-top: 20px; color: var(--color-text-light);">
                    <tr>
                        <td style="width: 40%; padding: 5px 0;">Tanggal Reservasi</td>
                        <td style="font-weight: bold;"><?= $search_result['tgl_reservasi']; ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0;">Layanan Dipesan</td>
                        <td><?= $search_result['nama_layanan']; ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0;">Jumlah Sepatu</td>
                        <td><?= $search_result['jumlah_sepatu']; ?> Pasang</td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0;">Detail Sepatu</td>
                        <td><?= $search_result['detail_sepatu']; ?></td>
                    </tr>
                    <tr>
                        <td style="padding: 5px 0;">Total Biaya</td>
                        <td style="color: var(--color-accent); font-weight: bold;">Rp <?= number_format($total_harga, 0, ',', '.'); ?></td>
                    </tr>
                </table>
                
                <?php if ($search_result['status'] === 'Menunggu Pembayaran'): ?>
                    <p style="margin-top: 20px; color: #ff9800;">Silakan segera lakukan pembayaran dan <a href="pembayaran.php" style="color: var(--color-accent); font-weight: bold;">Upload Bukti Transfer</a>.</p>
                <?php elseif ($search_result['status'] === 'Dibatalkan'): ?>
                    <p style="margin-top: 20px; color: #f44336;">Reservasi ini dibatalkan (Pembayaran gagal diverifikasi atau kuota penuh).</p>
                <?php elseif ($search_result['status'] === 'Selesai'): ?>
                    <p style="margin-top: 20px; color: var(--color-accent); font-weight: bold;">Sepatu Anda sudah Selesai dicuci dan siap diambil!</p>
                <?php endif; ?>
                
            </div>
        <?php endif; ?>
    </section>

</body>
</html>
<?php 
mysqli_close($koneksi); 
?>