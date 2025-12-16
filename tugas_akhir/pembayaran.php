<?php
session_start();
include('config/koneksi.php');

// Pastikan ada ID reservasi di session untuk melanjutkan
if (!isset($_SESSION['reservasi_id'])) {
    // Jika tidak ada ID, coba cek apakah ada ID reservasi yang statusnya Menunggu Pembayaran dari user yang sama (jika ada)
    // Untuk sederhana, kita arahkan ke reservasi.php
    header('Location: reservasi.php');
    exit;
}

$reservasi_id = $_SESSION['reservasi_id'];
$message = '';

// Query untuk menampilkan detail pesanan yang baru dibuat
$query_detail = "SELECT R.*, L.harga, L.nama_layanan FROM reservasi R JOIN layanan L ON R.id_layanan = L.id_layanan WHERE R.id_reservasi = $reservasi_id";
$result_detail = mysqli_query($koneksi, $query_detail);
$data_reservasi = mysqli_fetch_assoc($result_detail);
$total_bayar = $data_reservasi['harga'] * $data_reservasi['jumlah_sepatu'];

// LOGIKA UPLOAD BUKTI TRANSFER
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_FILES['bukti_transfer'])) {
    $target_dir = "uploads/";
    
    // Pastikan folder 'uploads' ada dan dapat ditulis (writeable)
    if (!is_dir($target_dir)) {
        mkdir($target_dir, 0777, true);
    }

    $file_ext = strtolower(pathinfo($_FILES["bukti_transfer"]["name"], PATHINFO_EXTENSION));
    $new_file_name = 'bukti_' . $reservasi_id . '.' . $file_ext;
    $target_file = $target_dir . $new_file_name;
    $uploadOk = 1;
    
    // Cek ukuran file dan tipe file
    if ($_FILES["bukti_transfer"]["size"] > 5000000) { // Max 5MB
        $message = "❌ File terlalu besar.";
        $uploadOk = 0;
    }
    if ($file_ext != "jpg" && $file_ext != "png" && $file_ext != "jpeg") {
        $message = "❌ Hanya diperbolehkan file JPG, JPEG, & PNG.";
        $uploadOk = 0;
    }
    
    if ($uploadOk == 0) {
        $message .= "<p style='color: red; font-weight: bold;'>Upload GAGAL.</p>";
    } else {
        if (move_uploaded_file($_FILES["bukti_transfer"]["tmp_name"], $target_file)) {
            // Update nama file bukti transfer dan status di database
            $sql_update = "UPDATE reservasi SET bukti_transfer = '$new_file_name', status = 'Menunggu Verifikasi' WHERE id_reservasi = $reservasi_id";
            if (mysqli_query($koneksi, $sql_update)) {
                $message = "<p style='color: var(--color-accent); font-weight: bold;'>✅ Bukti Transfer BERHASIL diupload!</p>";
                $message .= "<p style='color: var(--color-text-light);'>Reservasi Anda (#$reservasi_id) sedang <span style='color: var(--color-accent);'>Menunggu Verifikasi Admin</span>. Anda akan menerima notifikasi status.</p>";
                // Setelah upload, kita tidak unset session agar pelanggan bisa refresh halaman ini dan melihat status
                $data_reservasi['status'] = 'Menunggu Verifikasi'; 
            } else {
                $message = "Gagal mengupdate database: " . mysqli_error($koneksi);
            }
        } else {
            $message = "❌ Terjadi kesalahan saat mengunggah file.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran & Upload Bukti</title>
    <link rel="stylesheet" href="assets/style.css"> 
</head>
<body>
    <?php include('includes/header.php'); ?>

    <section style="max-width: 800px; margin: 50px auto; background-color: #2e2e2e; padding: 40px; border-radius: 8px;">
        <h2 style="text-align: center; color: var(--color-accent);">Konfirmasi Pembayaran Transfer Manual</h2>
        
        <?php echo $message; ?>

        <?php if ($data_reservasi['status'] == 'Menunggu Pembayaran'): ?>
            <div style="text-align: center; margin-bottom: 30px; padding: 20px; border: 1px dashed var(--color-accent);">
                
                <h3 style="color: var(--color-text-light); margin-bottom: 10px;">DETAIL RESERVASI</h3>
                <p>Layanan: <b><?= $data_reservasi['nama_layanan']; ?></b> | Jumlah: <b><?= $data_reservasi['jumlah_sepatu']; ?> psg</b></p>
                
                <p style="font-size: 1.2em; margin-top: 15px;">TOTAL BAYAR:</p>
                <p style="font-size: 2.5em; color: var(--color-accent); font-weight: bold;">
                    Rp <?= number_format($total_bayar, 0, ',', '.'); ?>
                </p>
                
                <hr style="border-top: 1px solid var(--color-secondary); margin: 20px 0;">

                <h3 style="color: var(--color-text-light); margin-bottom: 15px;">PILIHAN PEMBAYARAN</h3>

                <div style="display: flex; justify-content: space-around; align-items: flex-start;">
                    
                    <div style="width: 45%; text-align: left;">
                        <p style="font-weight: bold; color: var(--color-accent);">TRANSFER MANUAL</p>
                        <p style="margin-top: 5px;">Bank: **BCA**</p>
                        <p>Nomor Rekening: **1234567890**</p>
                        <p>Atas Nama: **Nature Clean Shoes**</p>
                    </div>

                    <div style="width: 45%; border-left: 1px solid var(--color-secondary); padding-left: 20px;">
                        <p style="font-weight: bold; color: var(--color-accent);">SCAN QR CODE</p>
                        <img src="assets/qr_pembayaran.png" alt="QR Code Pembayaran" style="width: 150px; height: 150px; margin-top: 10px; border: 2px solid white;">
                        <p style="font-size: 0.9em; margin-top: 5px; color: #aaa;">(Pastikan nama tujuan sudah benar)</p>
                    </div>

                </div>
            </div>

            <h3 style="color: var(--color-text-light);">Langkah 2: Upload Bukti Transfer (Max 5MB)</h3>
            <form method="POST" action="pembayaran.php" enctype="multipart/form-data" style="margin-top: 20px;">
                <input type="file" name="bukti_transfer" required accept=".jpg, .jpeg, .png"
                    style="display: block; width: 100%; margin: 10px 0; padding: 10px; background-color: #1a1a1a; color: var(--color-text-light); border: 1px solid var(--color-secondary);">
                
                <button type="submit" class="btn-primary" style="width: 100%; margin-top: 15px;">
                    Upload & Konfirmasi Pembayaran
                </button>
            </form>
        <?php else: ?>
            <p style="text-align: center; font-size: 1.2em; color: var(--color-text-light);">
                Status Reservasi Anda saat ini: <span style="color: var(--color-accent); font-weight: bold;"><?= $data_reservasi['status']; ?></span>.
            </p>
            <p style="text-align: center; margin-top: 20px;">
                Terima kasih atas konfirmasi Anda. Admin akan segera memproses verifikasi.
            </p>
            <div style="text-align: center; margin-top: 30px;">
                <a href="cek_status.php" class="btn-primary">Lacak Status Pesanan</a>
            </div>
        <?php endif; ?>
    </section>
</body>
</html>