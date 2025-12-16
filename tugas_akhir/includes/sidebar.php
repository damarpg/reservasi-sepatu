<?php 
// Pastikan hak_akses didefinisikan
$hak_akses = $_SESSION['hak_akses'] ?? 'Guest'; 
?>

<div class="sidebar">
    <h2 style="color: var(--color-accent); text-align: center; margin-bottom: 30px;">DASHBOARD</h2>
    
    <?php if ($hak_akses === 'Admin'): ?>
        <h4 style="color: #aaa; padding: 10px 15px; margin-top: 10px;">OPERASIONAL</h4>
        <a href="admin_reservasi.php" 
           class="btn-action <?= (basename($_SERVER['PHP_SELF']) == 'admin_reservasi.php') ? 'active' : ''; ?>">
            Verifikasi Reservasi
        </a>
        <a href="admin_layanan.php" 
           class="btn-action <?= (basename($_SERVER['PHP_SELF']) == 'admin_layanan.php') ? 'active' : ''; ?>">
            Manajemen Layanan & Kuota
        </a>
        
    <?php elseif ($hak_akses === 'Pemilik'): ?>
        <h4 style="color: #aaa; padding: 10px 15px; margin-top: 10px;">MANAJERIAL</h4>
        <a href="owner_laporan_keuangan.php" 
           class="btn-action <?= (basename($_SERVER['PHP_SELF']) == 'owner_laporan_keuangan.php') ? 'active' : ''; ?>">
            Laporan Keuangan
        </a>
        <a href="owner_laporan_pelanggan.php" 
           class="btn-action <?= (basename($_SERVER['PHP_SELF']) == 'owner_laporan_pelanggan.php') ? 'active' : ''; ?>">
            Laporan Pelanggan
        </a>
    <?php endif; ?>
    
    </div>