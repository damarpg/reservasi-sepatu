<?php
session_start();
include('config/koneksi.php');

// Proteksi: Jika belum login, arahkan ke login.php
if (!isset($_SESSION['hak_akses'])) {
    header('Location: login.php');
    exit;
}

$hak_akses = $_SESSION['hak_akses'];
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard | <?= $hak_akses; ?></title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        /* CSS Layout untuk Dashboard (Sidebar + Content) */
        .dashboard-layout {
            display: flex;
            min-height: 100vh;
        }

        .sidebar {
            width: 250px;
            background-color: var(--color-darker);
            /* Hitam Pekat */
            padding-top: 20px;
            box-shadow: 2px 0 5px rgba(0, 0, 0, 0.5);
            border-right: 3px solid var(--color-accent);
        }

        .sidebar a {
            display: block;
            color: var(--color-text-light);
            padding: 15px 20px;
            text-decoration: none;
            transition: background-color 0.3s, color 0.3s;
            border-bottom: 1px solid #333;
        }

        .sidebar a:hover,
        .sidebar a.active {
            background-color: #333;
            color: var(--color-accent);
        }

        .content {
            flex-grow: 1;
            padding: 30px;
        }
    </style>
</head>

<body>
    <div class="dashboard-layout">
        <?php include('includes/sidebar.php'); ?>

        <div class="content">
            <div style="text-align: right; margin-bottom: 20px;">
            <a href="logout.php" class="btn-primary" style="padding: 10px 20px; font-size: 0.9em;">LOGOUT</a>
        </div>
            <h1 style="color: var(--color-accent); margin-bottom: 20px;">
                Selamat Datang, <?= $_SESSION['username']; ?> (<?= $hak_akses; ?>)
            </h1>

            <div style="background-color: #2e2e2e; padding: 20px; border-radius: 8px;">
                <p>Ini adalah halaman utama dashboard Anda. Silakan pilih menu di samping untuk melanjutkan tugas:</p>

                <?php if ($hak_akses === 'Admin'): ?>
                    <p style="margin-top: 10px;">Tugas utama: <a href="admin_reservasi.php" class="btn-primary"
                            style="padding: 5px 10px; margin-left: 10px;">Verifikasi Pembayaran</a> dan <a
                            href="admin_layanan.php" class="btn-primary" style="padding: 5px 10px; margin-left: 10px;">Atur
                            Kuota</a>.</p>
                <?php elseif ($hak_akses === 'Pemilik'): ?>
                    <p style="margin-top: 10px;">Tugas utama: <a href="owner_laporan_keuangan.php" class="btn-primary"
                            style="padding: 5px 10px; margin-left: 10px;">Lihat Laporan Keuangan</a>.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>

</html>
<?php
mysqli_close($koneksi);
?>