<?php
session_start();
include('config/koneksi.php');

$error = '';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // 1. Ambil input
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = $_POST['password'];

    // 2. Query ke database
    $query = "SELECT * FROM pengguna WHERE username = '$username'";
    $result = mysqli_query($koneksi, $query);
    $user = mysqli_fetch_assoc($result);

    if ($user) {
        // Cek password (gunakan password_verify jika Anda menggunakan password_hash saat menyimpan)
        // Catatan: Asumsi password default 'admin123' sudah di-hash saat setup SQL
        
        // Simpel Cek: Jika Anda belum melakukan hashing, gunakan perbandingan langsung (TIDAK AMAN)
        // if ($password === 'admin123') { ... } 
        
        // Verifikasi Hash Password (Jika Anda menggunakan SQL dari Langkah 1)
        if (password_verify($password, $user['password'])) {
             // 3. Login Berhasil, Set Sesi
            $_SESSION['user_id'] = $user['id_user'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['hak_akses'] = $user['hak_akses']; // 'Admin' atau 'Pemilik'

            // 4. Arahkan ke Dashboard
            header('Location: dashboard.php');
            exit;
        } else {
            $error = "Password salah.";
        }
    } else {
        $error = "Username tidak ditemukan.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login | Admin & Owner</title>
    <link rel="stylesheet" href="assets/style.css">
    <style>
        /* CSS Tambahan untuk Form Login (Hitam-Kuning) */
        .login-container {
            max-width: 400px;
            margin: 100px auto;
            background-color: #2e2e2e;
            padding: 40px;
            border-radius: 8px;
            box-shadow: 0 0 15px rgba(255, 215, 0, 0.3); /* Sedikit glow kuning */
        }
        .login-container input[type="text"],
        .login-container input[type="password"] {
            width: 100%;
            padding: 12px;
            margin-bottom: 20px;
            background-color: #1a1a1a;
            color: var(--color-text-light);
            border: 1px solid var(--color-secondary);
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <?php include('includes/header.php'); ?>

    <div class="login-container">
        <h2 style="text-align: center; color: var(--color-accent); margin-bottom: 25px;">Login Sistem</h2>
        
        <?php if ($error): ?>
            <p style="color: red; text-align: center; margin-bottom: 15px; font-weight: bold;"><?= $error; ?></p>
        <?php endif; ?>

        <form method="POST" action="login.php">
            <label for="username" style="color: var(--color-text-light);">Username:</label>
            <input type="text" name="username" id="username" required>

            <label for="password" style="color: var(--color-text-light);">Password:</label>
            <input type="password" name="password" id="password" required>

            <button type="submit" class="btn-primary" style="width: 100%; padding: 12px;">LOGIN</button>
        </form>
    </div>
</body>
</html>
<?php 
mysqli_close($koneksi); 
?>