<?php
// Ambil data statistik dasar untuk admin
if (user_role() === 'admin') {
    // Jumlah Pengguna
    $total_users = $pdo->query("SELECT COUNT(id) FROM users")->fetchColumn();
    $premium_users = $pdo->query("SELECT COUNT(id) FROM users WHERE role = 'premium'")->fetchColumn();
    $free_users = $pdo->query("SELECT COUNT(id) FROM users WHERE role = 'free'")->fetchColumn();

    // Data untuk Grafik Aktivitas Pencarian (7 Hari Terakhir)
    $search_activity_query = $pdo->query("
        SELECT 
            DATE(search_timestamp) as search_date, 
            COUNT(id) as total_searches
        FROM search_history
        WHERE search_timestamp >= CURDATE() - INTERVAL 6 DAY
        GROUP BY search_date
        ORDER BY search_date ASC
    ");
    $search_activity = $search_activity_query->fetchAll(PDO::FETCH_ASSOC);

    // Siapkan data untuk JavaScript
    $chart_labels = [];
    $chart_data = [];
    // Buat array tanggal untuk 7 hari terakhir
    for ($i = 6; $i >= 0; $i--) {
        $date = date('Y-m-d', strtotime("-$i days"));
        $chart_labels[] = date('d M', strtotime($date)); // Format '13 Oct'
        $chart_data[$date] = 0; // Inisialisasi dengan 0
    }
    // Isi data dari database
    foreach ($search_activity as $activity) {
        if (isset($chart_data[$activity['search_date']])) {
            $chart_data[$activity['search_date']] = $activity['total_searches'];
        }
    }
}
?>

<div class="container mx-auto">
    <h1 class="text-3xl font-bold mb-6">Dashboard</h1>
    
    <p class="mb-8">Selamat datang, <span class="font-semibold"><?= e($_SESSION['username']) ?></span>!</p>

    <?php if (user_role() === 'admin'): ?>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="bg-white p-6 rounded-lg shadow-md flex items-center">
                <i class="fas fa-users text-3xl text-blue-500 mr-4"></i>
                <div>
                    <h2 class="text-xl font-semibold text-gray-700">Total Pengguna</h2>
                    <p class="text-3xl font-bold mt-1"><?= $total_users ?></p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md flex items-center">
                <i class="fas fa-star text-3xl text-yellow-500 mr-4"></i>
                <div>
                    <h2 class="text-xl font-semibold text-gray-700">Pengguna Premium</h2>
                    <p class="text-3xl font-bold mt-1"><?= $premium_users ?></p>
                </div>
            </div>
            <div class="bg-white p-6 rounded-lg shadow-md flex items-center">
                <i class="fas fa-user-circle text-3xl text-green-500 mr-4"></i>
                <div>
                    <h2 class="text-xl font-semibold text-gray-700">Pengguna Free</h2>
                    <p class="text-3xl font-bold mt-1"><?= $free_users ?></p>
                </div>
            </div>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Aktivitas Pencarian (7 Hari Terakhir)</h2>
            
            <div class="relative h-80">
                <canvas id="searchActivityChart"></canvas>
            </div>
            </div>
        
        <script>
        document.addEventListener('DOMContentLoaded', function () {
            const ctx = document.getElementById('searchActivityChart').getContext('2d');
            const searchActivityChart = new Chart(ctx, {
                type: 'line', // Jenis grafik: garis
                data: {
                    labels: <?= json_encode(array_values($chart_labels)) ?>,
                    datasets: [{
                        label: 'Jumlah Pencarian',
                        data: <?= json_encode(array_values($chart_data)) ?>,
                        backgroundColor: 'rgba(59, 130, 246, 0.2)',
                        borderColor: 'rgba(59, 130, 246, 1)',
                        borderWidth: 2,
                        tension: 0.3, // Membuat garis sedikit melengkung
                        fill: true,
                    }]
                },
                options: {
                    scales: {
                        y: {
                            beginAtZero: true,
                            ticks: {
                                // Hanya tampilkan angka bulat di sumbu Y
                                precision: 0
                            }
                        }
                    },
                    responsive: true,
                    maintainAspectRatio: false
                }
            });
        });
        </script>

    <?php elseif (user_role() === 'premium'): ?>
        <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-yellow-400">
            <h2 class="text-xl font-semibold text-gray-700 mb-4">Pencarian Terakhir Anda</h2>
            <?php
                $stmt = $pdo->prepare("SELECT query_text, search_timestamp FROM search_history WHERE user_id = ? ORDER BY search_timestamp DESC LIMIT 5");
                $stmt->execute([$_SESSION['user_id']]);
                $histories = $stmt->fetchAll();
            ?>
            <?php if ($histories): ?>
                <ul class="list-disc list-inside space-y-2">
                    <?php foreach ($histories as $history): ?>
                        <li><?= e($history['query_text']) ?> <span class="text-sm text-gray-500">- <?= date('d M Y, H:i', strtotime($history['search_timestamp'])) ?></span></li>
                    <?php endforeach; ?>
                </ul>
            <?php else: ?>
                <p>Anda belum melakukan pencarian apapun.</p>
            <?php endif; ?>
        </div>

    <?php else: // 'free' role ?>
        <?php
            $user_id = $_SESSION['user_id'];
            $today = date('Y-m-d');
            $stmt = $pdo->prepare("SELECT daily_query_count, last_query_date FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user_data = $stmt->fetch();

            $queries_today = ($user_data['last_query_date'] == $today) ? $user_data['daily_query_count'] : 0;
            $queries_left = 3 - $queries_today;
        ?>
      <div class="bg-white p-6 rounded-lg shadow-md border-l-4 border-green-400">
        <h2 class="text-xl font-semibold text-gray-700">Sisa Kuota Pencarian Hari Ini</h2>
        <p class="text-5xl font-bold mt-4 text-center text-blue-600"><?= $queries_left ?></p>
        <p class="text-center text-gray-500 mt-2">dari 5 kuota harian.</p> <div class="text-center mt-6">
            <a href="index.php?page=upgrade" class="bg-yellow-500 text-white font-bold py-2 px-4 rounded-lg hover:bg-yellow-600 transition">
                <i class="fas fa-rocket mr-2"></i> Upgrade ke Premium
            </a>
        </div>
    <?php endif; ?>
</div>