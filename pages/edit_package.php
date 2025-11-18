<?php
// Pastikan hanya admin yang bisa mengakses
if (user_role() !== 'admin') {
    redirect('index.php?page=dashboard');
}

$package_id = $_GET['id'] ?? null;
if (!$package_id || !is_numeric($package_id)) {
    redirect('index.php?page=settings'); // Kembali jika ID tidak valid
}

// Logika untuk memproses update
if (isset($_POST['update_package'])) {
    $features_raw = trim($_POST['features'] ?? '');
    $features_array = !empty($features_raw) ? explode("\n", str_replace("\r", "", $features_raw)) : [];
    
    $params = [
        ':id' => $package_id,
        ':name' => $_POST['name'],
        ':price' => $_POST['price'],
        ':duration_string' => $_POST['duration_string'],
        ':duration_text' => $_POST['duration_text'],
        ':features' => json_encode($features_array),
        ':is_active' => $_POST['is_active'],
        ':sort_order' => $_POST['sort_order'],
        ':is_recommended' => $_POST['is_recommended']
    ];
    
    $stmt = $pdo->prepare("UPDATE packages SET name=:name, price=:price, duration_string=:duration_string, duration_text=:duration_text, features=:features, is_active=:is_active, sort_order=:sort_order, is_recommended=:is_recommended WHERE id=:id");
    
    if ($stmt->execute($params)) {
        redirect('index.php?page=settings&status=pkg_updated');
    } else {
        $error_message = 'Gagal memperbarui paket.';
    }
}

// Ambil data paket yang ada untuk ditampilkan di form
$stmt = $pdo->prepare("SELECT * FROM packages WHERE id = ?");
$stmt->execute([$package_id]);
$package = $stmt->fetch();

if (!$package) {
    redirect('index.php?page=settings'); // Jika paket tidak ditemukan
}
?>

<div class="container mx-auto">
    <h1 class="text-3xl font-bold mb-6">Edit Paket Harga</h1>

    <?php if (isset($error_message)): ?>
        <div class="bg-red-100 border-red-400 text-red-700 px-4 py-3 rounded mb-4"><?= e($error_message) ?></div>
    <?php endif; ?>

    <div class="bg-white p-6 rounded-lg shadow-md">
        <form action="index.php?page=edit_package&id=<?= e($package_id) ?>" method="POST">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700">Nama Paket</label>
                    <input type="text" name="name" value="<?= e($package['name']) ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3" required>
                </div>
                <div>
                    <label for="price" class="block text-sm font-medium text-gray-700">Harga</label>
                    <input type="text" name="price" value="<?= e($package['price']) ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3" required>
                </div>
                <div>
                    <label for="duration_string" class="block text-sm font-medium text-gray-700">Durasi (String)</label>
                    <input type="text" name="duration_string" value="<?= e($package['duration_string']) ?>" placeholder="+1 month" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3" required>
                </div>
                 <div>
                    <label for="duration_text" class="block text-sm font-medium text-gray-700">Durasi (Teks)</label>
                    <input type="text" name="duration_text" value="<?= e($package['duration_text']) ?>" placeholder="Aktif 30 Hari" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3" required>
                </div>
                <div class="col-span-1 md:col-span-2 lg:col-span-4">
                    <label for="features" class="block text-sm font-medium text-gray-700">Fitur</label>
                    <textarea name="features" rows="5" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 font-mono text-sm"><?= e(implode("\n", json_decode($package['features']))) ?></textarea>
                    <p class="text-xs text-gray-500 mt-1">Satu fitur per baris. Awali dengan `!` untuk ikon silang (❌).</p>
                </div>
                 <div>
                    <label for="sort_order" class="block text-sm font-medium text-gray-700">Urutan</label>
                    <input type="number" name="sort_order" value="<?= e($package['sort_order']) ?>" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3" required>
                </div>
                <div>
                    <label for="is_active" class="block text-sm font-medium text-gray-700">Status</label>
                    <select name="is_active" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3">
                        <option value="1" <?= $package['is_active'] ? 'selected' : '' ?>>Aktif</option>
                        <option value="0" <?= !$package['is_active'] ? 'selected' : '' ?>>Nonaktif</option>
                    </select>
                </div>
                 <div>
                    <label for="is_recommended" class="block text-sm font-medium text-gray-700">Rekomendasi</label>
                    <select name="is_recommended" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3">
                        <option value="0" <?= !$package['is_recommended'] ? 'selected' : '' ?>>Tidak</option>
                        <option value="1" <?= $package['is_recommended'] ? 'selected' : '' ?>>Ya</option>
                    </select>
                </div>
            </div>
            <div class="mt-6">
                <button type="submit" name="update_package" class="bg-green-600 text-white py-2 px-4 rounded-md hover:bg-green-700">Simpan Perubahan</button>
                <a href="index.php?page=settings" class="ml-2 text-gray-600 hover:underline">Batal</a>
            </div>
        </form>
    </div>
</div>