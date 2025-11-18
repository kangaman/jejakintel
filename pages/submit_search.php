<?php
// File ini SEKARANG berjalan mandiri (standalone)
// jadi kita harus memuat semua file yang diperlukan.

// 1. Mulai session (jika belum dimulai)
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// 2. Muat file konfigurasi dan fungsi
// Kita perlu naik satu level (../) untuk menemukan folder config dan includes
// __DIR__ adalah path folder saat ini (yaitu 'pages')
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../includes/functions.php';

// 3. Tentukan URL untuk redirect (sekarang harus pakai ../)
$login_url   = '../index.php?page=login';
$search_url  = '../index.php?page=search';
$history_url = '../index.php?page=history';

// 4. Pastikan pengguna sudah login
if (!is_logged_in()) {
    redirect_with_message($login_url, 'error', 'Anda harus login untuk mengakses halaman ini.');
}

// 5. Cek metode POST
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['query'])) {
    
    // Ambil input mentah
    $search_query = trim($_POST['query']);
    
    // =========================================================
    // VALIDASI XSS BARU DITAMBAHKAN DI SINI
    // =========================================================
    // Periksa apakah input mengandung karakter tag HTML (< atau >)
    if (preg_match('/[<>]/', $search_query)) {
        // Jika ya, tolak input dan beri notifikasi
        redirect_with_message($search_url, 'error', 'Input tidak valid. Karakter < dan > tidak diizinkan.');
    }
    // =========================================================
    // AKHIR DARI VALIDASI XSS
    // =========================================================

    // Cek jika input kosong (setelah divalidasi)
    if (empty($search_query)) {
        redirect_with_message($search_url, 'error', 'Kolom pencarian tidak boleh kosong.');
    }

    $user_id = $_SESSION['user_id'];
    $user_role = user_role();
    $can_search = false;

    if ($user_role === 'admin' || $user_role === 'premium') {
        $can_search = true;
    } else { // Logic for free users
        $today = date('Y-m-d');
        $stmt = $pdo->prepare("SELECT daily_query_count, last_query_date FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();

        $user_count = 0;
        if ($user) {
            if ($user['last_query_date'] != $today) {
                // Reset kuota jika hari baru
                $stmt_reset = $pdo->prepare("UPDATE users SET daily_query_count = 0, last_query_date = ? WHERE id = ?");
                $stmt_reset->execute([$today, $user_id]);
                $user_count = 0;
            } else {
                $user_count = (int)$user['daily_query_count'];
            }
        }

        // Ambil batas dari settings
        $stmt_limit = $pdo->query("SELECT setting_value FROM settings WHERE setting_name = 'free_user_search_limit'");
        $free_limit = $stmt_limit->fetchColumn();
        $free_limit = $free_limit ? (int)$free_limit : 3; // Default 3 jika tidak ada di settings

        if ($user_count < $free_limit) {
            $can_search = true;
        } else {
            redirect_with_message($search_url, 'error', "Anda telah mencapai batas kuota pencarian harian ($free_limit).");
        }
    }

    if ($can_search) {
        try {
            // Masukkan ke antrean (input yang sudah divalidasi)
            $stmt = $pdo->prepare("INSERT INTO api_queue (user_id, query_text) VALUES (?, ?)");
            $stmt->execute([$user_id, $search_query]);
            
            // Update kuota HANYA jika free user
            if ($user_role === 'free') {
                $stmt_update = $pdo->prepare("UPDATE users SET daily_query_count = daily_query_count + 1, last_query_date = ? WHERE id = ?");
                $stmt_update->execute([date('Y-m-d'), $user_id]);
            }

            // Log aktivitas
            $log_details = "Pengguna '{$_SESSION['username']}' mengirim query baru (sudah divalidasi).";
            log_activity($pdo, 'USER_NEW_QUERY', $log_details, $user_id);
            
            // Alihkan ke halaman history dengan pesan sukses
            redirect_with_message($history_url, 'success', 'Pencarian berhasil dikirim ke antrean.');

        } catch (PDOException $e) {
            // error_log($e->getMessage()); // uncomment untuk debug
            redirect_with_message($search_url, 'error', 'Gagal menambahkan ke antrean karena masalah database.');
        }
    }
} else {
    // Jika file ini diakses langsung (bukan via POST)
    redirect_with_message($search_url, 'error', 'Metode pengiriman tidak valid.');
}
?>