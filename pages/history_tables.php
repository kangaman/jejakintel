<div class="bg-white p-6 rounded-lg shadow-md mb-8">
    <h2 class="text-xl font-semibold mb-4 text-blue-600">Permintaan dalam Antrean</h2>
    <?php if (!empty($pending_requests)): ?>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-200">
                <tr>
                    <th class="py-2 px-3 text-left text-sm font-semibold text-gray-600">Query</th>
                    <th class="py-2 px-3 text-left text-sm font-semibold text-gray-600">Status</th>
                    <th class="py-2 px-3 text-left text-sm font-semibold text-gray-600">Dikirim pada</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($pending_requests as $req): ?>
                <tr>
                    <td class="py-2 px-3 font-medium"><?= e($req['query_text']) ?></td>
                    <td class="py-2 px-3">
                        <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?= $req['status'] == 'pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800' ?>">
                            <?= e(ucfirst($req['status'])) ?>
                        </span>
                    </td>
                    <td class="py-2 px-3 text-sm text-gray-600"><?= date('d M Y, H:i', strtotime($req['created_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <p class="text-sm text-gray-500">Tidak ada permintaan dalam antrean saat ini.</p>
    <?php endif; ?>
</div>

<div class="bg-white p-6 rounded-lg shadow-md mb-8">
    <h2 class="text-xl font-semibold mb-4 text-red-600">Pencarian Gagal</h2>
    <?php if (!empty($failed_requests)): ?>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-200">
                <tr>
                    <th class="py-2 px-3 text-left text-sm font-semibold text-gray-600">Query</th>
                    <th class="py-2 px-3 text-left text-sm font-semibold text-gray-600">Pesan Error</th>
                    <th class="py-2 px-3 text-left text-sm font-semibold text-gray-600">Waktu Gagal</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200">
                <?php foreach ($failed_requests as $req): ?>
                <tr>
                    <td class="py-2 px-3 font-medium text-gray-500"><?= e($req['query_text']) ?></td>
                    <td class="py-2 px-3 text-sm text-red-700 font-mono"><?= e($req['error_message']) ?></td>
                    <td class="py-2 px-3 text-sm text-gray-600"><?= date('d M Y, H:i', strtotime($req['processed_at'])) ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php else: ?>
        <p class="text-sm text-gray-500">Tidak ada riwayat pencarian yang gagal.</p>
    <?php endif; ?>
</div>

<div class="bg-white p-6 rounded-lg shadow-md">
    <h2 class="text-xl font-semibold mb-4 text-green-600">Pencarian Selesai</h2>
    <div class="overflow-x-auto">
        <table class="min-w-full bg-white">
            <thead class="bg-gray-800 text-white">
                <tr>
                    <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Query (Klik untuk lihat detail)</th>
                    <?php if (user_role() === 'admin'): ?><th class="py-3 px-4 uppercase font-semibold text-sm text-left">Pengguna</th><?php endif; ?>
                    <th class="py-3 px-4 uppercase font-semibold text-sm text-left">Tanggal Selesai</th>
                </tr>
            </thead>
            <tbody class="text-gray-700 divide-y divide-gray-200">
                <?php if ($completed_histories): ?>
                    <?php foreach ($completed_histories as $history): ?>
                        <tr class="hover:bg-gray-50">
                            <td class="py-3 px-4">
                                <a href="index.php?page=search&history_id=<?= e($history['id']) ?>" class="text-blue-600 hover:underline font-semibold">
                                    <?= e($history['query_text']) ?>
                                </a>
                            </td>
                            <?php if (user_role() === 'admin'): ?><td class="py-3 px-4 text-sm text-gray-600"><?= e($history['username']) ?></td><?php endif; ?>
                            <td class="py-3 px-4 text-sm text-gray-600"><?= date('d M Y, H:i:s', strtotime($history['search_timestamp'])) ?></td>
                        </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="<?= user_role() === 'admin' ? '3' : '2' ?>" class="py-3 px-4 text-center text-gray-500">Belum ada pencarian yang selesai.</td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>