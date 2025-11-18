<?php
// Pengaturan Koneksi Database
define('DB_HOST', 'localhost');
define('DB_NAME', 'namadb');
define('DB_USER', 'userdb'); // Ganti dengan username database Anda
define('DB_PASS', 'passworddb');     // Ganti dengan password database Anda
define('DB_CHARSET', 'utf8mb4');

// Opsi untuk koneksi PDO
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

// Membuat koneksi PDO
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET, DB_USER, DB_PASS, $options);
} catch (\PDOException $e) {
    // Jika koneksi gagal, hentikan aplikasi dan tampilkan pesan error
    // Di lingkungan produksi, error ini sebaiknya dicatat (log) bukan ditampilkan ke pengguna
    throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
?>
