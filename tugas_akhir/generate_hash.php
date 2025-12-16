<?php
$password_plain = 'admin123';
$hash = password_hash($password_plain, PASSWORD_DEFAULT);

echo "Password yang digunakan: admin123\n";
echo "Hash yang dihasilkan sistem Anda:\n";
echo $hash;
?>