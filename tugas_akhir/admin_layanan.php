<?php
session_start();
include('config/koneksi.php');

// Proteksi Admin: Hanya Admin (Operasional) yang boleh mengakses
if (!isset($_SESSION['hak_akses']) || $_SESSION['hak_akses'] !== 'Admin') {
    header('Location: login.php');
    exit;
}

$message = '';

// LOGIKA 1: TAMBAH LAYANAN BARU (CREATE)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['aksi']) && $_POST['aksi'] == 'tambah_layanan') {
    $nama_layanan = mysqli_real_escape_string($koneksi, $_POST['nama_layanan_baru']);
    $harga = intval($_POST['harga_baru']);
    $maksimal_sepatu = intval($_POST['maksimal_sepatu_baru']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi_baru']);

    if ($maksimal_sepatu < 1 || $harga < 0) {
        $message = "<p style='color: red; font-weight: bold;'>❌ Maksimal sepatu harus minimal 1 dan harga tidak boleh negatif.</p>";
    } else {
        $sql_insert = "INSERT INTO layanan (nama_layanan, harga, maksimal_sepatu, deskripsi) 
                       VALUES ('$nama_layanan', $harga, $maksimal_sepatu, '$deskripsi')";
        
        if (mysqli_query($koneksi, $sql_insert)) {
            $message = "<p style='color: var(--color-accent); font-weight: bold;'>✅ Layanan '$nama_layanan' berhasil ditambahkan.</p>";
        } else {
            $message = "<p style='color: red;'>Gagal menambahkan layanan: " . mysqli_error($koneksi) . "</p>";
        }
    }
}

// LOGIKA 2: UPDATE DATA LAYANAN (Update)
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['aksi']) && $_POST['aksi'] == 'update_layanan') {
    $id_layanan = intval($_POST['id_layanan']);
    $nama_layanan = mysqli_real_escape_string($koneksi, $_POST['nama_layanan']);
    $harga = intval($_POST['harga']);
    $maksimal_sepatu = intval($_POST['maksimal_sepatu']);
    $deskripsi = mysqli_real_escape_string($koneksi, $_POST['deskripsi']);

    if ($maksimal_sepatu < 1 || $harga < 0) {
        $message = "<p style='color: red; font-weight: bold;'>❌ Maksimal sepatu harus minimal 1 dan harga tidak boleh negatif.</p>";
    } else {
        $sql_update = "UPDATE layanan SET 
                       nama_layanan = '$nama_layanan', 
                       harga = $harga, 
                       maksimal_sepatu = $maksimal_sepatu,
                       deskripsi = '$deskripsi'
                       WHERE id_layanan = $id_layanan";
        
        if (mysqli_query($koneksi, $sql_update)) {
            $message = "<p style='color: var(--color-accent); font-weight: bold;'>✅ Data Layanan ID $id_layanan berhasil diperbarui.</p>";
        } else {
            $message = "<p style='color: red;'>Gagal memperbarui data: " . mysqli_error($koneksi) . "</p>";
        }
    }
}

// LOGIKA 3: HAPUS LAYANAN (DELETE)
if (isset($_GET['aksi']) && $_GET['aksi'] == 'hapus' && isset($_GET['id'])) {
    $id_layanan = intval($_GET['id']);
    
    $sql_delete = "DELETE FROM layanan WHERE id_layanan = $id_layanan";
    
    if (mysqli_query($koneksi, $sql_delete)) {
        $message = "<p style='color: var(--color-accent); font-weight: bold;'>✅ Layanan ID $id_layanan berhasil dihapus.</p>";
    } else {
        $message = "<p style='color: red; font-weight: bold;'>❌ Gagal menghapus layanan. Pastikan tidak ada reservasi yang masih menggunakan layanan ini.</p>";
    }
    // Redirect untuk menghilangkan parameter GET dari URL
    header("Location: admin_layanan.php?msg=" . urlencode(strip_tags($message)));
    exit;
}

// Query untuk menampilkan semua data layanan
$query_layanan = "SELECT * FROM layanan ORDER BY id_layanan ASC";
$result_layanan = mysqli_query($koneksi, $query_layanan);

// Ambil pesan dari URL (setelah redirect hapus)
if (isset($_GET['msg'])) {
    $message = urldecode($_GET['msg']);
}

?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin - Manajemen Layanan</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        .form-tambah-layanan input, .form-tambah-layanan textarea {
            background-color: #1a1a1a;
            color: var(--color-text-light);
            border: 1px solid var(--color-secondary);
            padding: 8px;
            margin-right: 10px;
            border-radius: 4px;
        }
    </style>
    <script>
        function konfirmasiHapus(id) {
            return confirm("PERINGATAN! Anda yakin ingin menghapus Layanan ID " + id + "? Semua data terkait reservasi juga akan terpengaruh.");
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
                Manajemen Layanan & Kuota Harian
            </h1>
            
            <?php echo $message; ?>

            <div style="background-color: #2e2e2e; padding: 20px; border-radius: 8px; margin-bottom: 30px;">
                <h3 style="color: var(--color-accent); margin-bottom: 15px;">+ Tambah Layanan Baru</h3>
                <form method="POST" action="admin_layanan.php" class="form-tambah-layanan" style="display: flex; flex-wrap: wrap; gap: 10px;">
                    <input type="hidden" name="aksi" value="tambah_layanan">
                    
                    <input type="text" name="nama_layanan_baru" placeholder="Nama Layanan" required style="width: 200px;">
                    <input type="number" name="harga_baru" placeholder="Harga (Rp)" required min="0" style="width: 100px;">
                    <input type="number" name="maksimal_sepatu_baru" placeholder="Maks Sepatu Harian" required min="1" style="width: 150px;">
                    <textarea name="deskripsi_baru" placeholder="Deskripsi Singkat" rows="1" style="width: 200px;"></textarea>

                    <button type="submit" class="btn-primary" style="padding: 8px 15px; font-size: 0.9em;">
                        Tambah
                    </button>
                </form>
            </div>
            
            <h3 style="color: var(--color-accent); margin-bottom: 15px;">Daftar Layanan Tersedia</h3>

            <table class="admin-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Nama Layanan</th>
                        <th>Harga (Rp)</th>
                        <th>Maks. Sepatu Harian</th>
                        <th>Deskripsi</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (mysqli_num_rows($result_layanan) > 0): ?>
                        <?php while($data = mysqli_fetch_assoc($result_layanan)): ?>
                            <tr>
                                <form method="POST" action="admin_layanan.php" style="display: table-row;">
                                    <input type="hidden" name="aksi" value="update_layanan">
                                    <input type="hidden" name="id_layanan" value="<?= $data['id_layanan']; ?>">

                                    <td><?= $data['id_layanan']; ?></td>
                                    <td><input type="text" name="nama_layanan" value="<?= htmlspecialchars($data['nama_layanan']); ?>" required style="background:none; border:none; color:inherit; width:100%; border-bottom: 1px dotted #555;"></td>
                                    <td><input type="number" name="harga" value="<?= $data['harga']; ?>" required style="background:none; border:none; color:inherit; width:100px; border-bottom: 1px dotted #555;"></td>
                                    
                                    <td><input type="number" name="maksimal_sepatu" value="<?= $data['maksimal_sepatu']; ?>" required min="1" style="background:none; border:none; color:inherit; width:80px; border-bottom: 1px dotted #555;"></td>
                                    
                                    <td><textarea name="deskripsi" rows="2" style="background:none; border:none; color:inherit; width:200px; border-bottom: 1px dotted #555;"><?= htmlspecialchars($data['deskripsi']); ?></textarea></td>
                                    <td style="display: flex; gap: 5px;">
                                        <button type="submit" class="btn-primary" style="padding: 5px 10px; font-size: 0.8em; background-color: #00bcd4;">
                                            Simpan
                                        </button>
                                        <a href="admin_layanan.php?aksi=hapus&id=<?= $data['id_layanan']; ?>" 
                                           onclick="return konfirmasiHapus(<?= $data['id_layanan']; ?>)" 
                                           class="btn-primary" style="padding: 5px 10px; font-size: 0.8em; background-color: #f44336;">
                                            Hapus
                                        </a>
                                    </td>
                                </form>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">Tidak ada data layanan.</td>
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