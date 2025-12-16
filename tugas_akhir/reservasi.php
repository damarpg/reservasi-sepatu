<?php
session_start();
include('config/koneksi.php');

$message = '';
$layanan_default_id = '';
$layanan_default_nama = 'Pilih Layanan';

// Mengambil data layanan jika dikirim dari index.php
if (isset($_GET['layanan'])) {
    $layanan_default_id = intval($_GET['layanan']);
    $query_layanan = "SELECT nama_layanan, harga FROM layanan WHERE id_layanan = $layanan_default_id";
    $result_layanan = mysqli_query($koneksi, $query_layanan);
    if (mysqli_num_rows($result_layanan) > 0) {
        $data = mysqli_fetch_assoc($result_layanan);
        $layanan_default_nama = $data['nama_layanan'] . " (Rp " . number_format($data['harga'], 0, ',', '.') . ")";
    }
}

// LOGIKA PEMROSESAN FORM RESERVASI
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Ambil Data Form
    $tgl_reservasi = $_POST['tgl_reservasi'];
    $id_layanan = intval($_POST['id_layanan']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_pelanggan']);
    $telp = mysqli_real_escape_string($koneksi, $_POST['telp_pelanggan']);
    $detail_sepatu = mysqli_real_escape_string($koneksi, $_POST['detail_sepatu']);
    $jumlah_sepatu = intval($_POST['jumlah_sepatu']);

    // Validasi dasar: ID Layanan dan Jumlah sepatu harus > 0
    if ($id_layanan <= 0 || $jumlah_sepatu <= 0) {
        $message = "<p style='color: red; font-weight: bold;'>❌ Harap pilih layanan dan masukkan jumlah sepatu yang benar.</p>";
    } else {
        // --- 2. VALIDASI KUOTA (Anti-Double Booking Berdasarkan LAYANAN YANG DIPILIH) ---
        
        // A. Ambil Kuota MAKSIMAL untuk LAYANAN yang DIPILIH ($id_layanan)
        $query_kuota_max = "SELECT maksimal_sepatu FROM layanan WHERE id_layanan = $id_layanan"; 
        $result_kuota_max = mysqli_query($koneksi, $query_kuota_max);
        
        if (mysqli_num_rows($result_kuota_max) == 0) {
             $message = "<p style='color: red; font-weight: bold;'>❌ Layanan tidak ditemukan.</p>";
             goto end_reservation; // Langsung lompat ke akhir
        }
        
        $kuota_maks_sepatu = mysqli_fetch_assoc($result_kuota_max)['maksimal_sepatu']; 
        
        // B. Query menghitung TOTAL JUMLAH SEPATU yang sudah dipesan untuk TANGGAL INI dan LAYANAN INI
        $query_terpakai = "SELECT SUM(jumlah_sepatu) as total_sepatu_terpakai 
                         FROM reservasi 
                         WHERE tgl_reservasi = '$tgl_reservasi' 
                         AND id_layanan = $id_layanan /* <-- FILTER KRUSIAL */
                         AND status IN ('Menunggu Pembayaran', 'Menunggu Verifikasi', 'Dikonfirmasi', 'Diproses')";
                         
        $result_terpakai = mysqli_query($koneksi, $query_terpakai);
        $data_terpakai = mysqli_fetch_assoc($result_terpakai);
        $sepatu_terpakai = $data_terpakai['total_sepatu_terpakai'] ?? 0;
        
        $kuota_tersisa = $kuota_maks_sepatu - $sepatu_terpakai;

        if ($jumlah_sepatu > $kuota_tersisa) {
            // Kuota Tidak Tersedia
            $message = "<p style='color: red; font-weight: bold;'>❌ MAAF, kuota untuk layanan yang dipilih pada tanggal $tgl_reservasi hanya tersisa $kuota_tersisa pasang sepatu. Pesanan Anda ($jumlah_sepatu pasang) melebihi kuota.</p>";
        } else {
            // --- 3. SIMPAN RESERVASI KE DATABASE ---
            $status_awal = 'Menunggu Pembayaran';
            $sql = "INSERT INTO reservasi (id_layanan, nama_pelanggan, telp_pelanggan, tgl_reservasi, detail_sepatu, jumlah_sepatu, status) 
                    VALUES ('$id_layanan', '$nama', '$telp', '$tgl_reservasi', '$detail_sepatu', '$jumlah_sepatu', '$status_awal')";

            if (mysqli_query($koneksi, $sql)) {
                $last_id = mysqli_insert_id($koneksi);
                $_SESSION['reservasi_id'] = $last_id; 
                // Redirect ke halaman pembayaran
                header("Location: pembayaran.php");
                exit;
            } else {
                $message = "Gagal menyimpan reservasi: " . mysqli_error($koneksi);
            }
        }
    }
}
end_reservation:
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Form Reservasi | Nature Clean Shoes</title>
    <link rel="stylesheet" href="assets/style.css"> 
</head>
<body>
    
    <?php include('includes/header.php'); ?>

    <section style="max-width: 700px; margin: 50px auto; background-color: #2e2e2e; padding: 40px; border-radius: 8px;">
        <h2 style="text-align: center; color: var(--color-accent);">Formulir Reservasi Jasa Cuci Sepatu</h2>
        
        <?php echo $message; ?>

        <form method="POST" action="reservasi.php" style="text-align: left;">
            
            <label for="id_layanan" style="color: var(--color-accent); display: block; margin-top: 15px;">Pilih Layanan *</label>
            <select name="id_layanan" id="id_layanan" required 
                style="width: 100%; padding: 10px; margin-top: 5px; background-color: #1a1a1a; color: var(--color-text-light); border: 1px solid var(--color-secondary);">
                <?php
                $query_list_layanan = "SELECT id_layanan, nama_layanan, harga FROM layanan ORDER BY nama_layanan ASC";
                $result_list_layanan = mysqli_query($koneksi, $query_list_layanan);
                
                if ($layanan_default_id == '') {
                    echo "<option value=''>Pilih Layanan</option>";
                }

                while($layanan = mysqli_fetch_assoc($result_list_layanan)) {
                    $selected = ($layanan['id_layanan'] == $layanan_default_id) ? 'selected' : '';
                    echo "<option value='{$layanan['id_layanan']}' $selected>" . htmlspecialchars($layanan['nama_layanan']) . " (Rp ".number_format($layanan['harga'], 0, ',', '.').")</option>";
                }
                ?>
            </select>
            
            <label for="tgl_reservasi" style="color: var(--color-accent); display: block; margin-top: 15px;">Tanggal Reservasi * (Cek Kuota)</label>
            <input type="date" name="tgl_reservasi" id="tgl_reservasi" required 
                min="<?= date('Y-m-d', strtotime('+1 day')); ?>" 
                style="width: 100%; padding: 10px; margin-top: 5px; background-color: #1a1a1a; color: var(--color-text-light); border: 1px solid var(--color-secondary);">

            <label for="nama_pelanggan" style="color: var(--color-accent); display: block; margin-top: 15px;">Nama Lengkap *</label>
            <input type="text" name="nama_pelanggan" id="nama_pelanggan" required 
                style="width: 100%; padding: 10px; margin-top: 5px; background-color: #1a1a1a; color: var(--color-text-light); border: 1px solid var(--color-secondary);">

            <label for="telp_pelanggan" style="color: var(--color-accent); display: block; margin-top: 15px;">Nomor Telepon *</label>
            <input type="tel" name="telp_pelanggan" id="telp_pelanggan" required 
                style="width: 100%; padding: 10px; margin-top: 5px; background-color: #1a1a1a; color: var(--color-text-light); border: 1px solid var(--color-secondary);">
            
            <label for="jumlah_sepatu" style="color: var(--color-accent); display: block; margin-top: 15px;">Jumlah Pasang Sepatu *</label>
            <input type="number" name="jumlah_sepatu" id="jumlah_sepatu" required min="1" value="1"
                style="width: 100%; padding: 10px; margin-top: 5px; background-color: #1a1a1a; color: var(--color-text-light); border: 1px solid var(--color-secondary);">

            <label for="detail_sepatu" style="color: var(--color-accent); display: block; margin-top: 15px;">Detail Sepatu (Deskripsi Kondisi) *</label>
            <textarea name="detail_sepatu" id="detail_sepatu" required rows="4"
                style="width: 100%; padding: 10px; margin-top: 5px; background-color: #1a1a1a; color: var(--color-text-light); border: 1px solid var(--color-secondary);"></textarea>

            <button type="submit" class="btn-primary" style="width: 100%; margin-top: 30px; font-size: 1.2em;">
                Pesanan Sekarang
            </button>
            
        </form>
        <p style="text-align: center; margin-top: 20px; color: #aaa;">* Anda akan diarahkan ke halaman pembayaran setelah berhasil memesan.</p>
    </section>

</body>
</html>
<?php 
mysqli_close($koneksi); 
?>