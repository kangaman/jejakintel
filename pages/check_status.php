<?php
// Skrip ini hanya untuk dipanggil oleh JavaScript (AJAX)
header('Content-Type: application/json');

if (!is_logged_in()) {
    echo json_encode(['status' => 'error', 'message' => 'Not logged in']);
    exit();
}

try {
    $user_id = $_SESSION['user_id'];
    $response = ['status' => 'success', 'notifications' => [], 'history_html' => ''];

    // 1. Ambil Notifikasi (logika yang sudah ada)
    $stmt_notif = $pdo->prepare("SELECT id, query_text, status FROM api_queue WHERE user_id = ? AND status IN ('completed', 'failed') AND notified = 'no'");
    $stmt_notif->execute([$user_id]);
    $notifications = $stmt_notif->fetchAll(PDO::FETCH_ASSOC);

    if ($notifications) {
        $response['notifications'] = $notifications;
        $ids_to_update = array_column($notifications, 'id');
        if (!empty($ids_to_update)) {
            $placeholders = implode(',', array_fill(0, count($ids_to_update), '?'));
            $update_stmt = $pdo->prepare("UPDATE api_queue SET notified = 'yes' WHERE id IN ($placeholders)");
            $update_stmt->execute($ids_to_update);
        }
    }

    // 2. LOGIKA BARU: Buat ulang konten HTML untuk halaman History
    ob_start(); // Mulai menangkap output HTML

    // Ambil data dari antrean yang masih pending/processing
    $pending_stmt = $pdo->prepare("SELECT id, query_text, status, created_at FROM api_queue WHERE user_id = ? AND status IN ('pending', 'processing') ORDER BY created_at DESC");
    $pending_stmt->execute([$user_id]);
    $pending_requests = $pending_stmt->fetchAll();
    
    // Ambil data history yang sudah selesai
    $history_stmt = $pdo->prepare("SELECT id, query_text, search_timestamp FROM search_history WHERE user_id = ? ORDER BY search_timestamp DESC LIMIT 100");
    $history_stmt->execute([$user_id]);
    $completed_histories = $history_stmt->fetchAll();

    // Ambil juga data yang gagal
    $failed_stmt = $pdo->prepare("SELECT id, query_text, error_message, processed_at FROM api_queue WHERE user_id = ? AND status = 'failed' ORDER BY processed_at DESC LIMIT 10");
    $failed_stmt->execute([$user_id]);
    $failed_requests = $failed_stmt->fetchAll();
    
    // Sertakan file parsial (potongan HTML) untuk me-render tabel
    include 'history_tables.php';

    $response['history_html'] = ob_get_clean(); // Ambil HTML yang ditangkap dan bersihkan buffer

    echo json_encode($response);

} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}

exit();
?>