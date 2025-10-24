<?php
// File ini akan memproses pengiriman form dan mengarahkan ke halaman History.

// Pastikan pengguna sudah login
if (!is_logged_in()) {
    redirect('index.php?page=login');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['query'])) {
    $search_query = trim($_POST['query']);
    
    if (empty($search_query)) {
        redirect('index.php?page=search&error=empty');
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

        if ($user['last_query_date'] != $today) {
            $stmt = $pdo->prepare("UPDATE users SET daily_query_count = 0, last_query_date = ? WHERE id = ?");
            $stmt->execute([$today, $user_id]);
            $user['daily_query_count'] = 0;
        }

        // ## PERUBAHAN DI SINI: Batas diubah dari 10 menjadi 5 ##
        if ($user['daily_query_count'] < 5) {
            $can_search = true;
        } else {
            redirect('index.php?page=search&error=limit');
        }
    }

    if ($can_search) {
        try {
            // Masukkan ke antrean
            $stmt = $pdo->prepare("INSERT INTO api_queue (user_id, query_text) VALUES (?, ?)");
            $stmt->execute([$user_id, $search_query]);
            
            // Update kuota
            if ($user_role === 'free') {
                $stmt = $pdo->prepare("UPDATE users SET daily_query_count = daily_query_count + 1, last_query_date = ? WHERE id = ?");
                $stmt->execute([date('Y-m-d'), $user_id]);
            }

            // Log aktivitas
            $log_details = "Pengguna '{$_SESSION['username']}' mengirim query baru: '{$search_query}'.";
            log_activity($pdo, 'USER_NEW_QUERY', $log_details);
            
            // Alihkan ke halaman history
            redirect('index.php?page=history&status=queued');

        } catch (PDOException $e) {
            redirect('index.php?page=search&error=queue_failed');
        }
    }
} else {
    redirect('index.php?page=search');
}
?>