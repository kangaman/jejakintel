<?php
// Ambil data untuk pertama kali halaman dimuat
$user_id = $_SESSION['user_id'];
$pending_stmt = $pdo->prepare("SELECT id, query_text, status, created_at FROM api_queue WHERE user_id = ? AND status IN ('pending', 'processing') ORDER BY created_at DESC");
$pending_stmt->execute([$user_id]);
$pending_requests = $pending_stmt->fetchAll();

$failed_stmt = $pdo->prepare("SELECT id, query_text, error_message, processed_at FROM api_queue WHERE user_id = ? AND status = 'failed' ORDER BY processed_at DESC LIMIT 10");
$failed_stmt->execute([$user_id]);
$failed_requests = $failed_stmt->fetchAll();

$completed_histories = [];
if (user_role() === 'admin') {
    $history_stmt = $pdo->query("SELECT h.id, h.query_text, h.search_timestamp, u.username FROM search_history h JOIN users u ON h.user_id = u.id ORDER BY h.search_timestamp DESC LIMIT 100");
    $completed_histories = $history_stmt->fetchAll();
} else {
    $history_stmt = $pdo->prepare("SELECT id, query_text, search_timestamp FROM search_history WHERE user_id = ? ORDER BY search_timestamp DESC LIMIT 100");
    $history_stmt->execute([$user_id]);
    $completed_histories = $history_stmt->fetchAll();
}
?>

<div class="container mx-auto">
    <h1 class="text-3xl font-bold mb-6">History Pencarian</h1>

    <?php if (isset($_GET['status']) && $_GET['status'] === 'queued'): ?>
        <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-6" role="alert">
            <p class="font-bold">Berhasil!</p>
            <p>Pencarian Anda telah ditambahkan ke antrean dan akan segera diproses.</p>
        </div>
    <?php endif; ?>

    <div id="history-content">
        <?php include 'history_tables.php'; ?>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Fungsi untuk memperbarui konten history
    function updateHistory() {
        // Cek jika tab browser sedang tidak aktif, jangan lakukan update untuk menghemat resource
        if (document.hidden) {
            return;
        }

        fetch('index.php?page=check_status')
            .then(response => response.json())
            .then(data => {
                if (data.status === 'success' && data.history_html) {
                    const historyContent = document.getElementById('history-content');
                    // Ganti konten hanya jika ada perubahan untuk menghindari kedipan layar
                    if (historyContent.innerHTML.trim() !== data.history_html.trim()) {
                        historyContent.innerHTML = data.history_html;
                        console.log('History content updated at ' + new Date().toLocaleTimeString());
                    }
                }
            })
            .catch(error => console.error('Error updating history:', error));
    }

    // Jalankan pembaruan setiap 20 detik (20000 milidetik)
    const historyInterval = setInterval(updateHistory, 20000);
});
</script>