<?php
session_start();
include('config/koneksi.php');

// Proteksi Admin: Hanya Admin (Operasional) yang boleh mengakses
if (!isset($_SESSION['hak_akses']) || $_SESSION['hak_akses'] !== 'Admin') {
    header('Location: login.php');
    exit;
}

$message = '';

// LOGIKA UPDATE STATUS (Aksi: Konfirmasi, Tolak, Selesai)
if (isset($_GET['aksi']) && isset($_GET['id']) && isset($_GET['status'])) {
    $id_reservasi = intval($_GET['id']);
    $new_status = mysqli_real_escape_string($koneksi, $_GET['status']);

    // Pastikan status yang diizinkan
    if (in_array($new_status, ['Dikonfirmasi', 'Dibatalkan', 'Selesai', 'Diproses'])) {
        $sql_update = "UPDATE reservasi SET status = '$new_status' WHERE id_reservasi = $id_reservasi";

        if (mysqli_query($koneksi, $sql_update)) {
            $message = "<p style='color: var(--color-accent); font-weight: bold;'>Status Reservasi ID $id_reservasi berhasil diperbarui menjadi $new_status.</p>";
        } else {
            $message = "<p style='color: red;'>Gagal memperbarui status: " . mysqli_error($koneksi) . "</p>";
        }
    }
}

// Query untuk menampilkan semua reservasi yang relevan (Menunggu Verifikasi ke atas)
$query_reservasi = "SELECT 
                        R.*, L.nama_layanan, L.harga
                    FROM reservasi R
                    JOIN layanan L ON R.id_layanan = L.id_layanan
                    WHERE R.status IN ('Menunggu Verifikasi', 'Dikonfirmasi', 'Diproses', 'Selesai', 'Dibatalkan')
                    ORDER BY FIELD(R.status, 'Menunggu Verifikasi', 'Dikonfirmasi', 'Diproses', 'Selesai', 'Dibatalkan'), R.tgl_reservasi ASC";
$result_reservasi = mysqli_query($koneksi, $query_reservasi);

?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Verifikasi Reservasi</title>
    <link rel="stylesheet" href="assets/style.css">
    <script>
        function konfirmasiAksi(id, status) {
            return confirm("Yakin ingin mengubah status Reservasi ID " + id + " menjadi " + status + "?");
        }
    </script>
</head>

<body>
    <div class="dashboard-layout">
        <?php include('includes/sidebar.php'); ?>

        <div class="content">
            <div style="text-align: right; margin-bottom: 20px;">
            <a href="logout.php" class="btn-primary" style="padding: 10px 20px; font-size: 0.9em;">LOGOUT</a>
        </div>
            <h1 style="color: var(--color-accent); margin-bottom: 20px;">
                Verifikasi Reservasi & Operasional
            </h1>

            <?php echo $message; ?>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Tanggal</th>
                        <th>Pelanggan</th>
                        <th>Layanan</th>
                        <th>Jml</th>
                        <th>Detail Sepatu</th>
                        <th>Total Bayar</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result_reservasi) > 0): ?>
                        <?php while ($data = mysqli_fetch_assoc($result_reservasi)):
                            $total_bayar = $data['harga'] * $data['jumlah_sepatu'];
                            ?>
                            <tr>
                                <td><?= $data['id_reservasi']; ?></td>
                                <td><?= $data['tgl_reservasi']; ?></td>
                                <td><?= $data['nama_pelanggan']; ?> <br>(<?= $data['telp_pelanggan']; ?>)</td>
                                <td><?= $data['nama_layanan']; ?></td>
                                <td><?= $data['jumlah_sepatu']; ?> psg</td>
                                <td><?= htmlspecialchars($data['detail_sepatu']); ?></td>
                                <td>Rp <?= number_format($total_bayar, 0, ',', '.'); ?></td>
                                <td class="status-<?= strtolower(str_replace(' ', '-', $data['status'])); ?>">
                                    <?= $data['status']; ?>
                                </td>
                                <td>
                                    <?php if ($data['status'] === 'Menunggu Verifikasi'): ?>
                                        <a href="uploads/<?= $data['bukti_transfer']; ?>" target="_blank" class="btn-primary"
                                            style="padding: 5px 10px; font-size: 0.8em; margin-bottom: 5px; background-color: #00bcd4;">
                                            Lihat Bukti
                                        </a>
                                        <br>
                                        <a href="admin_reservasi.php?aksi=update&id=<?= $data['id_reservasi']; ?>&status=Dikonfirmasi"
                                            onclick="return konfirmasiAksi(<?= $data['id_reservasi']; ?>, 'Dikonfirmasi')"
                                            class="btn-primary"
                                            style="padding: 5px 10px; font-size: 0.8em; background-color: #4CAF50; margin-top: 5px;">
                                            Konfirmasi (Lunas)
                                        </a>
                                        <a href="admin_reservasi.php?aksi=update&id=<?= $data['id_reservasi']; ?>&status=Dibatalkan"
                                            onclick="return konfirmasiAksi(<?= $data['id_reservasi']; ?>, 'Dibatalkan')"
                                            class="btn-primary"
                                            style="padding: 5px 10px; font-size: 0.8em; background-color: #f44336; margin-top: 5px;">
                                            Tolak
                                        </a>
                                    <?php elseif ($data['status'] === 'Dikonfirmasi'): ?>
                                        <a href="admin_reservasi.php?aksi=update&id=<?= $data['id_reservasi']; ?>&status=Diproses"
                                            onclick="return konfirmasiAksi(<?= $data['id_reservasi']; ?>, 'Diproses')"
                                            class="btn-primary"
                                            style="padding: 5px 10px; font-size: 0.8em; background-color: #ff9800;">
                                            Tandai Diproses
                                        </a>
                                    <?php elseif ($data['status'] === 'Diproses'): ?>
                                        <a href="admin_reservasi.php?aksi=update&id=<?= $data['id_reservasi']; ?>&status=Selesai"
                                            onclick="return konfirmasiAksi(<?= $data['id_reservasi']; ?>, 'Selesai')"
                                            class="btn-primary"
                                            style="padding: 5px 10px; font-size: 0.8em; background-color: #00bcd4;">
                                            Tandai Selesai
                                        </a>
                                    <?php else: ?>
                                        -
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="9" style="text-align: center;">Tidak ada data reservasi yang relevan saat ini.</td>
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