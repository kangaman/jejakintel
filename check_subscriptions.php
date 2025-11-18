<?php
// Skrip ini hanya untuk dijalankan oleh Cron Job
echo "Starting subscription check...\n";
set_time_limit(300); // Batas waktu 5 menit

// Muat file konfigurasi dan fungsi yang diperlukan
require_once __DIR__ . '/config/database.php';

try {
    // 1. Dapatkan tanggal hari ini dalam format YYYY-MM-DD
    $today = date('Y-m-d');

    // 2. Siapkan query untuk mencari semua pengguna premium yang sudah kedaluwarsa
    // Kondisinya: perannya 'premium' DAN tanggal kedaluwarsanya sudah lewat dari hari ini
    $stmt = $pdo->prepare("
        UPDATE users 
        SET 
            role = 'free', 
            api_token_pribadi = NULL,
            premium_expiry_date = NULL
        WHERE 
            role = 'premium' AND premium_expiry_date < ?
    ");
    
    // 3. Eksekusi query
    $stmt->execute([$today]);

    // 4. Dapatkan jumlah baris (pengguna) yang terpengaruh/diubah
    $affected_users = $stmt->rowCount();

    if ($affected_users > 0) {
        $message = "Success: {$affected_users} premium subscription(s) have expired and were reverted to 'free'.\n";
        echo $message;
        // Anda bisa menambahkan logging ke file jika diperlukan
        // error_log($message, 3, __DIR__ . '/subscription_log.txt');
    } else {
        echo "No expired premium subscriptions found today.\n";
    }

} catch (Exception $e) {
    // Jika terjadi error, catat ke log error utama server
    $error_message = "Subscription check failed: " . $e->getMessage() . "\n";
    echo $error_message;
    error_log($error_message);
    die();
}

echo "Subscription check finished.\n";
?>