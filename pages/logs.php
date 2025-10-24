<?php
// Pastikan hanya admin yang bisa mengakses halaman ini
if (user_role() !== 'admin') {
    redirect('index.php?page=dashboard');
}

// Logika untuk Paginasi (Halaman)
$page_num = isset($_GET['p']) && is_numeric($_GET['p']) ? (int)$_GET['p'] : 1;
if ($page_num < 1) $page_num = 1;
$limit = 25; // Jumlah log per halaman
$offset = ($page_num - 1) * $limit;

// Ambil total jumlah log untuk paginasi
$total_logs_query = $pdo->query("SELECT COUNT(id) FROM activity_logs");
$total_logs = $total_logs_query->fetchColumn();
$total_pages = $total_logs > 0 ? ceil($total_logs / $limit) : 1;

// Ambil data log dari database dengan limit dan offset
$stmt = $pdo->prepare("SELECT * FROM activity_logs ORDER BY timestamp DESC LIMIT :limit OFFSET :offset");
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$logs = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container mx-auto">
    <h1 class="text-3xl font-bold mb-6">Log Aktivitas Sistem</h1>

    <div class="bg-white p-6 rounded-lg shadow-md">
        <div class="overflow-x-auto">
            <table class="min-w-full bg-white divide-y divide-gray-200">
                <thead class="bg-gray-800 text-white">
                    <tr>
                        <th class="py-3 px-4 text-left text-xs font-medium uppercase tracking-wider">Waktu</th>
                        <th class="py-3 px-4 text-left text-xs font-medium uppercase tracking-wider">Pengguna</th>
                        <th class="py-3 px-4 text-left text-xs font-medium uppercase tracking-wider">Aksi</th>
                        <th class="py-3 px-4 text-left text-xs font-medium uppercase tracking-wider">Detail</th>
                        <th class="py-3 px-4 text-left text-xs font-medium uppercase tracking-wider">Alamat IP</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-200">
                    <?php if ($logs): ?>
                        <?php foreach ($logs as $log): ?>
                            <tr>
                                <td class="py-4 px-4 whitespace-nowrap text-sm text-gray-500"><?= date('d M Y, H:i:s', strtotime($log['timestamp'])) ?></td>
                                <td class="py-4 px-4 whitespace-nowrap text-sm text-gray-900 font-medium"><?= e($log['username']) ?></td>
                                <td class="py-4 px-4 whitespace-nowrap">
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                        <?= e($log['action']) ?>
                                    </span>
                                </td>
                                <td class="py-4 px-4 text-sm text-gray-700 break-words"><?= e($log['details']) ?></td>
                                <td class="py-4 px-4 whitespace-nowrap text-sm text-gray-500"><?= e($log['ip_address']) ?></td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="py-4 px-4 text-center text-gray-500">Belum ada aktivitas yang tercatat.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($total_pages > 1): ?>
            <div class="mt-6 flex justify-between items-center">
                <div class="text-sm text-gray-600">
                    Halaman <?= $page_num ?> dari <?= $total_pages ?>
                </div>
                <div class="flex items-center space-x-2">
                    <?php if ($page_num > 1): ?>
                        <a href="index.php?page=logs&p=<?= $page_num - 1 ?>" class="px-3 py-1 border rounded-md text-sm hover:bg-gray-100">&laquo; Sebelumnya</a>
                    <?php endif; ?>
                    <?php if ($page_num < $total_pages): ?>
                        <a href="index.php?page=logs&p=<?= $page_num + 1 ?>" class="px-3 py-1 border rounded-md text-sm hover:bg-gray-100">Berikutnya &raquo;</a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>