<?php
// Skrip ini harus dijalankan oleh Cron Job
set_time_limit(60); 

require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

try {
    $pdo->beginTransaction();
    
    // Ambil satu job dari antrean
    $stmt = $pdo->prepare("SELECT * FROM api_queue WHERE status = 'pending' ORDER BY created_at ASC LIMIT 1 FOR UPDATE");
    $stmt->execute();
    $job = $stmt->fetch();

    if ($job) {
        // Tandai job sebagai 'processing'
        $update_stmt = $pdo->prepare("UPDATE api_queue SET status = 'processing' WHERE id = ?");
        $update_stmt->execute([$job['id']]);
        $pdo->commit();
        
        echo "Processing job ID: {$job['id']} for query: '{$job['query_text']}'\n";

        // Ambil data peran dan token pribadi dari pengguna yang meminta
        $user_stmt = $pdo->prepare("SELECT role, api_token_pribadi FROM users WHERE id = ?");
        $user_stmt->execute([$job['user_id']]);
        $user = $user_stmt->fetch();
        $api_token = null;

        if ($user && $user['role'] === 'premium' && !empty($user['api_token_pribadi'])) {
            $api_token = $user['api_token_pribadi'];
            echo "Using private token for user ID: {$job['user_id']}\n";
        } else {
            $api_token = get_main_api_token($pdo);
            echo "Using main system token.\n";
        }

        $query_result = call_osint_api($api_token, $job['query_text'], 300);

        if ($query_result !== null && !isset($query_result['Error code'])) {
            // Simpan hasilnya ke search_history
            $history_stmt = $pdo->prepare("SELECT id FROM search_history WHERE user_id = ? AND query_text = ?");
            $history_stmt->execute([$job['user_id'], $job['query_text']]);
            $existing_history = $history_stmt->fetch();

            if ($existing_history) {
                $update_history_stmt = $pdo->prepare("UPDATE search_history SET result_json = ?, search_timestamp = NOW() WHERE id = ?");
                $update_history_stmt->execute([json_encode($query_result), $existing_history['id']]);
            } else {
                $insert_history_stmt = $pdo->prepare("INSERT INTO search_history (user_id, query_text, result_json) VALUES (?, ?, ?)");
                $insert_history_stmt->execute([$job['user_id'], $job['query_text'], json_encode($query_result)]);
            }

            // Tandai job sebagai 'completed'
            $final_stmt = $pdo->prepare("UPDATE api_queue SET status = 'completed', processed_at = NOW() WHERE id = ?");
            $final_stmt->execute([$job['id']]);
            echo "Job ID: {$job['id']} completed successfully.\n";

            // ## LOGIKA BARU: KIRIM NOTIFIKASI HASIL KE TELEGRAM ##
            $user_info_stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
            $user_info_stmt->execute([$job['user_id']]);
            $username = $user_info_stmt->fetchColumn();

            $notif_message = "<b>✅ Hasil Pencarian Selesai</b>\n\n";
            $notif_message .= "<b>Pengguna:</b> " . htmlspecialchars($username) . "\n";
            $notif_message .= "<b>Query:</b> " . htmlspecialchars($job['query_text']) . "\n";
            $notif_message .= "--------------------------------------\n";

            if (isset($query_result['List']) && !empty($query_result['List'])) {
                $notif_message .= "<b>Ditemukan di " . count($query_result['List']) . " sumber:</b>\n\n";
                
                foreach ($query_result['List'] as $db_name => $leak_info) {
                    $entry = "<b>" . htmlspecialchars($db_name) . "</b>\n";
                    if (isset($leak_info['Data'][0])) {
                        $first_item = $leak_info['Data'][0];
                        foreach ($first_item as $key => $value) {
                            $entry .= "- " . htmlspecialchars($key) . ": <code>" . htmlspecialchars($value) . "</code>\n";
                        }
                    }
                    
                    // Cek panjang pesan agar tidak melebihi batas Telegram
                    if (strlen($notif_message) + strlen($entry) > 3500) {
                        $notif_message .= "\n<i>...dan hasil lainnya. Cek website untuk detail lengkap.</i>";
                        break;
                    }
                    $notif_message .= $entry . "\n";
                }
            } else {
                $notif_message .= "<b>Status:</b> Tidak ada data yang ditemukan.";
            }
            
            send_telegram_notification($notif_message, $pdo);
            // ## AKHIR LOGIKA BARU ##

        } else {
            // Jika ada error, tandai job sebagai 'failed'
            $error_msg = $query_result['Error code'] ?? 'API response is null or invalid';
            $final_stmt = $pdo->prepare("UPDATE api_queue SET status = 'failed', processed_at = NOW(), error_message = ? WHERE id = ?");
            $final_stmt->execute([$error_msg, $job['id']]);
            echo "Job ID: {$job['id']} failed. Error: $error_msg\n";
        }
    } else {
        $pdo->commit();
        echo "No pending jobs found.\n";
    }

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    error_log("Queue processing failed: " . $e->getMessage());
    die("An error occurred: " . $e->getMessage());
}
?>