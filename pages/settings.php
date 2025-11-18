<?php
// Inisialisasi variabel pesan
$success_message = '';
$error_message = '';
$pass_success_message = '';
$pass_error_message = '';
$token_success_message = '';
$token_error_message = '';
$email_success_message = '';
$email_error_message = '';

$user_id = $_SESSION['user_id'];

// Logika ubah email
if (isset($_POST['change_email'])) {
    $new_email = trim($_POST['new_email'] ?? '');
    $current_password_for_email = $_POST['current_password_for_email'] ?? '';
    if (empty($new_email) || empty($current_password_for_email)) { $email_error_message = 'Email baru dan password saat ini wajib diisi.';
    } elseif (!filter_var($new_email, FILTER_VALIDATE_EMAIL)) { $email_error_message = 'Format email baru tidak valid.';
    } else {
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        if ($user && password_verify($current_password_for_email, $user['password'])) {
            $stmt_check = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $stmt_check->execute([$new_email, $user_id]);
            if ($stmt_check->fetch()) {
                $email_error_message = 'Email ini sudah digunakan oleh akun lain.';
            } else {
                $update_stmt = $pdo->prepare("UPDATE users SET email = ? WHERE id = ?");
                if ($update_stmt->execute([$new_email, $user_id])) {
                    $email_success_message = 'Email Anda telah berhasil diperbarui.';
                    log_activity($pdo, 'USER_CHANGE_EMAIL', 'Pengguna berhasil mengubah emailnya.');
                } else { $email_error_message = 'Gagal memperbarui email. Silakan coba lagi.'; }
            }
        } else { $email_error_message = 'Password yang Anda masukkan salah.'; }
    }
}

// Logika simpan API token pribadi
if (user_role() === 'premium' && isset($_POST['save_private_token'])) {
    $private_token = $_POST['private_api_token'] ?? '';
    if (!empty($private_token)) {
        $stmt = $pdo->prepare("UPDATE users SET api_token_pribadi = ? WHERE id = ?");
        if ($stmt->execute([$private_token, $user_id])) {
            $token_success_message = 'API Token pribadi Anda telah berhasil disimpan.';
            log_activity($pdo, 'PREMIUM_UPDATE_TOKEN', 'Pengguna premium memperbarui token pribadinya.');
        } else { $token_error_message = 'Gagal menyimpan API Token pribadi.'; }
    } else {
        $stmt = $pdo->prepare("UPDATE users SET api_token_pribadi = NULL WHERE id = ?");
        if ($stmt->execute([$user_id])) {
            $token_success_message = 'API Token pribadi Anda telah dihapus.';
            log_activity($pdo, 'PREMIUM_REMOVE_TOKEN', 'Pengguna premium menghapus token pribadinya.');
        } else { $token_error_message = 'Gagal menghapus API Token pribadi.'; }
    }
}

// Logika ubah password sendiri
if (isset($_POST['change_own_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) { $pass_error_message = 'Semua kolom untuk mengubah password Anda wajib diisi.';
    } elseif (strlen($new_password) < 8) { $pass_error_message = 'Password baru minimal harus 8 karakter.';
    } elseif (!preg_match('/[a-z]/', $new_password) || !preg_match('/[A-Z]/', $new_password) || !preg_match('/[0-9]/', $new_password)) { $pass_error_message = 'Password harus mengandung kombinasi huruf besar, kecil, dan angka.';
    } elseif ($new_password !== $confirm_password) { $pass_error_message = 'Password baru dan konfirmasi tidak cocok.';
    } else {
        $stmt = $pdo->prepare("SELECT password FROM users WHERE id = ?");
        $stmt->execute([$user_id]);
        $user = $stmt->fetch();
        if ($user && password_verify($current_password, $user['password'])) {
            $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $update_stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ?");
            if ($update_stmt->execute([$new_hashed_password, $user_id])) {
                $pass_success_message = 'Password Anda telah berhasil diubah.';
                log_activity($pdo, 'USER_CHANGE_PASSWORD', 'Pengguna berhasil mengubah passwordnya sendiri.');
            } else { $pass_error_message = 'Gagal mengubah password Anda.'; }
        } else { $pass_error_message = 'Password saat ini yang Anda masukkan salah.'; }
    }
}

// ## LOGIKA PENGATURAN ADMIN ##
if (user_role() === 'admin') {
    // Update API Token
    if (isset($_POST['update_api_token'])) {
        $new_token = $_POST['api_token'];
        $stmt = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_name = 'main_api_token'");
        if ($stmt->execute([$new_token])) { $success_message = 'API Token berhasil diperbarui.'; } else { $error_message = 'Gagal memperbarui API Token.'; }
    }

    // Update User Role
    if (isset($_POST['update_user_role'])) {
        $user_id_to_update = $_POST['user_id'];
        $new_role = $_POST['role'];
        $expiry_date = null;
        if ($new_role === 'premium') {
            $duration = $_POST['duration'] ?? '';
            $manual_date = $_POST['expiry_date'] ?? '';
            if (!empty($duration)) {
                $expiry_date = date('Y-m-d H:i:s', strtotime($duration));
            } elseif (!empty($manual_date)) {
                $expiry_date = $manual_date;
            } else {
                $error_message = 'Anda harus memilih masa aktif (durasi atau tanggal) untuk pengguna premium.';
            }
        }
        if (empty($error_message)) {
            $user_before_stmt = $pdo->prepare("SELECT username, role FROM users WHERE id = ?");
            $user_before_stmt->execute([$user_id_to_update]);
            $user_before = $user_before_stmt->fetch();
            $old_role = $user_before['role'] ?? 'unknown';
            $target_username = $user_before['username'] ?? 'unknown_user';
            $stmt = $pdo->prepare("UPDATE users SET role = ?, premium_expiry_date = ? WHERE id = ?");
            if ($stmt->execute([$new_role, $expiry_date, $user_id_to_update])) {
                $success_message = 'Peran pengguna berhasil diperbarui.';
                if ($old_role !== $new_role) {
                    $log_details = "Admin '{$_SESSION['username']}' mengubah peran pengguna '{$target_username}' dari '{$old_role}' menjadi '{$new_role}'.";
                    log_activity($pdo, 'ADMIN_CHANGE_ROLE', $log_details);
                    $admin_username = $_SESSION['username'];
                    $notif_message = "<b>🔄 Perubahan Status Pengguna</b>\n\nAdmin <b>" . htmlspecialchars($admin_username) . "</b> telah mengubah status:\n\n<b>Pengguna:</b> " . htmlspecialchars($target_username) . "\n<b>Dari:</b> " . htmlspecialchars(ucfirst($old_role)) . "\n<b>Menjadi:</b> " . htmlspecialchars(ucfirst($new_role));
                    send_telegram_notification($notif_message, $pdo);
                }
            } else { $error_message = 'Gagal memperbarui peran pengguna.'; }
        }
    }
    
    // Admin mengubah password user lain
    if (isset($_POST['admin_change_user_password'])) {
        $user_id_to_change = $_POST['user_id_to_change']; $admin_new_password = $_POST['admin_new_password'];
        if (empty($admin_new_password)) { $error_message = "Kolom password baru untuk pengguna tidak boleh kosong.";
        } elseif (strlen($admin_new_password) < 8) { $error_message = "Password baru minimal harus 8 karakter.";
        } else {
            $new_hashed_password = password_hash($admin_new_password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE id = ? AND role != 'admin'");
            if ($stmt->execute([$new_hashed_password, $user_id_to_change])) {
                $success_message = "Password untuk pengguna berhasil direset.";
                $user_info_stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
                $user_info_stmt->execute([$user_id_to_change]);
                $target_username = $user_info_stmt->fetchColumn();
                $log_details = "Admin '{$_SESSION['username']}' mereset password untuk pengguna '{$target_username}'.";
                log_activity($pdo, 'ADMIN_RESET_PASSWORD', $log_details);
            } else { $error_message = "Gagal mereset password pengguna."; }
        }
    }
    // Admin menghapus pengguna
    if (isset($_POST['delete_user'])) {
        $user_id_to_delete = $_POST['user_id_to_delete'];
        $user_info_stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
        $user_info_stmt->execute([$user_id_to_delete]);
        $deleted_username = $user_info_stmt->fetchColumn();
        if ($user_id_to_delete == $_SESSION['user_id']) { $error_message = "Anda tidak bisa menghapus akun Anda sendiri.";
        } else {
            $stmt = $pdo->prepare("DELETE FROM users WHERE id = ? AND role != 'admin'");
            if ($stmt->execute([$user_id_to_delete])) {
                $success_message = "Pengguna telah berhasil dihapus.";
                $log_details = "Admin '{$_SESSION['username']}' menghapus pengguna '{$deleted_username}' (ID: {$user_id_to_delete}).";
                log_activity($pdo, 'ADMIN_DELETE_USER', $log_details);
            } else { $error_message = "Gagal menghapus pengguna."; }
        }
    }
    // Update Pengaturan Telegram
    if (isset($_POST['update_telegram_settings'])) {
        $bot_token = $_POST['telegram_bot_token']; $chat_id = $_POST['telegram_chat_id'];
        $stmt1 = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_name = 'telegram_bot_token'");
        $stmt2 = $pdo->prepare("UPDATE settings SET setting_value = ? WHERE setting_name = 'telegram_chat_id'");
        if ($stmt1->execute([$bot_token]) && $stmt2->execute([$chat_id])) { $success_message = 'Pengaturan Telegram berhasil diperbarui.'; } else { $error_message = 'Gagal memperbarui pengaturan Telegram.'; }
    }
    
    // MANAJEMEN PAKET (PACKAGES)
    if (isset($_POST['add_package'])) {
        $features_raw = trim($_POST['features'] ?? '');
        $features_array = !empty($features_raw) ? explode("\n", str_replace("\r", "", $features_raw)) : [];
        $params = [':name' => $_POST['name'], ':price' => $_POST['price'], ':duration_string' => $_POST['duration_string'], ':duration_text' => $_POST['duration_text'], ':features' => json_encode($features_array), ':is_active' => $_POST['is_active'], ':sort_order' => $_POST['sort_order'], ':is_recommended' => $_POST['is_recommended']];
        $stmt = $pdo->prepare("INSERT INTO packages (name, price, duration_string, duration_text, features, is_active, sort_order, is_recommended) VALUES (:name, :price, :duration_string, :duration_text, :features, :is_active, :sort_order, :is_recommended)");
        if ($stmt->execute($params)) { $success_message = 'Paket baru berhasil ditambahkan.'; } else { $error_message = 'Gagal menambahkan paket baru.'; }
    }
    if (isset($_POST['delete_package'])) {
        $package_id = $_POST['package_id'];
        $stmt = $pdo->prepare("DELETE FROM packages WHERE id = ?");
        if ($stmt->execute([$package_id])) { $success_message = 'Paket berhasil dihapus.'; } else { $error_message = 'Gagal menghapus paket.'; }
    }

    // Ambil data untuk panel admin
    $current_api_token = get_main_api_token($pdo);
    $all_users = $pdo->query("SELECT id, username, email, role, premium_expiry_date FROM users WHERE id != {$_SESSION['user_id']} ORDER BY username")->fetchAll();
    $telegram_settings_query = $pdo->query("SELECT setting_name, setting_value FROM settings WHERE setting_name IN ('telegram_bot_token', 'telegram_chat_id')");
    $telegram_settings = $telegram_settings_query->fetchAll(PDO::FETCH_KEY_PAIR);
    $packages = $pdo->query("SELECT * FROM packages ORDER BY sort_order ASC")->fetchAll();
}

// Ambil data user saat ini untuk ditampilkan
$stmt = $pdo->prepare("SELECT username, email, api_token_pribadi FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$current_user_data = $stmt->fetch();
$current_username = $current_user_data['username'] ?? '';
$current_email = $current_user_data['email'] ?? '';
$private_token_value = $current_user_data['api_token_pribadi'] ?? '';
?>
<div class="container mx-auto">
    <h1 class="text-3xl font-bold mb-6">Pengaturan Akun</h1>

    <div class="bg-white p-6 rounded-lg shadow-md mb-8">
        <h2 class="text-xl font-semibold mb-4"><i class="fas fa-user-edit mr-2"></i>Informasi & Ubah Email</h2>
        <?php if ($email_success_message): ?><div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4"><?= e($email_success_message) ?></div><?php endif; ?>
        <?php if ($email_error_message): ?><div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"><?= e($email_error_message) ?></div><?php endif; ?>
        <div class="space-y-2 mb-6 text-sm">
            <div class="grid grid-cols-3 gap-4">
                <div class="col-span-1 text-gray-600 font-semibold">Username:</div>
                <div class="col-span-2 text-gray-800 font-mono"><?= e($current_username) ?></div>
            </div>
            <div class="grid grid-cols-3 gap-4">
                <div class="col-span-1 text-gray-600 font-semibold">Email Terdaftar:</div>
                <div class="col-span-2 text-gray-800 font-mono"><?= e($current_email) ?></div>
            </div>
        </div>
        <hr class="my-6">
        <h3 class="text-lg font-semibold text-gray-800 mb-3">Ubah Alamat Email</h3>
        <form action="index.php?page=settings" method="POST">
            <div class="mb-4"><label for="new_email" class="block text-gray-700">Email Baru:</label><input type="email" name="new_email" id="new_email" class="w-full px-3 py-2 border rounded-lg mt-1" required></div>
            <div class="mb-4"><label for="current_password_for_email" class="block text-gray-700">Konfirmasi dengan Password Anda:</label><input type="password" name="current_password_for_email" id="current_password_for_email" class="w-full px-3 py-2 border rounded-lg mt-1" required></div>
            <button type="submit" name="change_email" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600"><i class="fas fa-save mr-1"></i> Simpan Email Baru</button>
        </form>
    </div>

    <?php if (user_role() === 'premium'): ?>
    <div class="bg-white p-6 rounded-lg shadow-md mb-8 border-l-4 border-yellow-400">
        <h2 class="text-xl font-semibold mb-4 text-gray-800"><i class="fas fa-star mr-2 text-yellow-500"></i>Pengaturan Premium: API Token Pribadi</h2>
        <?php if ($token_success_message): ?><div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4"><?= e($token_success_message) ?></div><?php endif; ?>
        <?php if ($token_error_message): ?><div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"><?= e($token_error_message) ?></div><?php endif; ?>
        <p class="text-sm text-gray-600 mb-4">Masukkan API Token pribadi Anda di sini. Jika diisi, semua pencarian Anda akan menggunakan token ini. Kosongkan untuk kembali menggunakan token sistem.</p>
        <form action="index.php?page=settings" method="POST">
            <div class="mb-4"><label for="private_api_token" class="block text-gray-700">API Token Pribadi Anda:</label><input type="password" name="private_api_token" id="private_api_token" value="<?= e($private_token_value) ?>" class="w-full px-3 py-2 border rounded-lg mt-1" placeholder="Masukkan token Anda..."></div>
            <button type="submit" name="save_private_token" class="bg-yellow-500 text-white py-2 px-4 rounded hover:bg-yellow-600">Simpan Token Pribadi</button>
        </form>
    </div>
    <?php endif; ?>

    <div class="bg-white p-6 rounded-lg shadow-md mb-8">
        <h2 class="text-xl font-semibold mb-4"><i class="fas fa-key mr-2"></i>Ubah Password Saya</h2>
        <?php if ($pass_success_message): ?><div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4"><?= e($pass_success_message) ?></div><?php endif; ?>
        <?php if ($pass_error_message): ?><div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded mb-4"><?= e($pass_error_message) ?></div><?php endif; ?>
        <form action="index.php?page=settings" method="POST">
            <div class="mb-4"><label for="current_password" class="block text-gray-700">Password Saat Ini:</label><input type="password" name="current_password" id="current_password" class="w-full px-3 py-2 border rounded-lg mt-1" required></div>
            <div class="mb-4"><label for="new_password" class="block text-gray-700">Password Baru:</label><input type="password" name="new_password" id="new_password" class="w-full px-3 py-2 border rounded-lg mt-1" required></div>
            <div class="mb-4"><label for="confirm_password" class="block text-gray-700">Konfirmasi Password Baru:</label><input type="password" name="confirm_password" id="confirm_password" class="w-full px-3 py-2 border rounded-lg mt-1" required></div>
            <button type="submit" name="change_own_password" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600"><i class="fas fa-save mr-1"></i> Simpan Password Saya</button>
        </form>
    </div>

    <?php if (user_role() === 'admin'): ?>
        <hr class="my-8 border-t-2 border-gray-300">
        <h1 class="text-3xl font-bold mb-6 text-red-600">Panel Administrator</h1>
        <?php if ($success_message): ?><div class="bg-green-100 border-green-400 text-green-700 px-4 py-3 rounded mb-4"><?= e($success_message) ?></div><?php endif; ?>
        <?php if ($error_message): ?><div class="bg-red-100 border-red-400 text-red-700 px-4 py-3 rounded mb-4"><?= e($error_message) ?></div><?php endif; ?>
        <?php if (isset($_GET['status']) && $_GET['status'] === 'pkg_updated'): ?>
        <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded mb-4">Paket telah berhasil diperbarui.</div>
        <?php endif; ?>
        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h2 class="text-xl font-semibold mb-4"><i class="fas fa-box-open mr-2"></i>Manajemen Paket Harga</h2>
            <h3 class="text-lg font-semibold text-gray-800 mb-3">Daftar Paket Saat Ini</h3>
            <div class="overflow-x-auto mb-8 border rounded-lg">
                <table class="min-w-full">
                    <thead class="bg-gray-100">
                        <tr>
                            <th class="py-2 px-3 text-left text-xs font-semibold text-gray-600 uppercase">Urutan</th>
                            <th class="py-2 px-3 text-left text-xs font-semibold text-gray-600 uppercase">Nama Paket</th>
                            <th class="py-2 px-3 text-left text-xs font-semibold text-gray-600 uppercase">Harga</th>
                            <th class="py-2 px-3 text-left text-xs font-semibold text-gray-600 uppercase">Status</th>
                            <th class="py-2 px-3 text-left text-xs font-semibold text-gray-600 uppercase">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200">
                        <?php foreach($packages as $pkg): ?>
                        <tr>
                            <td class="py-3 px-3 text-sm text-gray-700"><?= e($pkg['sort_order']) ?></td>
                            <td class="py-3 px-3 text-sm font-medium text-gray-900"><?= e($pkg['name']) ?></td>
                            <td class="py-3 px-3 text-sm text-gray-700"><?= e($pkg['price']) ?></td>
                            <td class="py-3 px-3 text-sm">
                                <?php if ($pkg['is_active']): ?><span class="px-2 py-1 text-xs font-semibold rounded-full bg-green-100 text-green-800">Aktif</span><?php else: ?><span class="px-2 py-1 text-xs font-semibold rounded-full bg-gray-100 text-gray-800">Nonaktif</span><?php endif; ?>
                                <?php if ($pkg['is_recommended']): ?><span class="px-2 py-1 text-xs font-semibold rounded-full bg-blue-100 text-blue-800">Rekomendasi</span><?php endif; ?>
                            </td>
                            <td class="py-3 px-3 text-sm">
                                <a href="index.php?page=edit_package&id=<?= $pkg['id'] ?>" class="text-indigo-600 hover:text-indigo-900 mr-4">Edit</a>
                                <form action="index.php?page=settings" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus paket ini?');" class="inline-block">
                                    <input type="hidden" name="package_id" value="<?= $pkg['id'] ?>">
                                    <button type="submit" name="delete_package" class="text-red-600 hover:text-red-800">Hapus</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <hr>
            <h3 class="text-lg font-semibold text-gray-800 mt-6 mb-3">Tambah Paket Baru</h3>
            <form action="index.php?page=settings" method="POST">
                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
                    <div><label for="name" class="block text-sm font-medium text-gray-700">Nama Paket</label><input type="text" name="name" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3" required></div>
                    <div><label for="price" class="block text-sm font-medium text-gray-700">Harga</label><input type="text" name="price" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3" required></div>
                    <div><label for="duration_string" class="block text-sm font-medium text-gray-700">Durasi (String)</label><input type="text" name="duration_string" placeholder="+1 month" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3" required></div>
                    <div><label for="duration_text" class="block text-sm font-medium text-gray-700">Durasi (Teks)</label><input type="text" name="duration_text" placeholder="Aktif 30 Hari" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3" required></div>
                    <div class="col-span-1 md:col-span-2 lg:col-span-4"><label for="features" class="block text-sm font-medium text-gray-700">Fitur</label><textarea name="features" rows="5" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3 font-mono text-sm"></textarea><p class="text-xs text-gray-500 mt-1">Satu fitur per baris. Awali dengan `!` untuk ikon silang (❌).</p></div>
                    <div><label for="sort_order" class="block text-sm font-medium text-gray-700">Urutan</label><input type="number" name="sort_order" value="10" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3" required></div>
                    <div><label for="is_active" class="block text-sm font-medium text-gray-700">Status</label><select name="is_active" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3"><option value="1">Aktif</option><option value="0">Nonaktif</option></select></div>
                    <div><label for="is_recommended" class="block text-sm font-medium text-gray-700">Rekomendasi</label><select name="is_recommended" class="mt-1 block w-full border border-gray-300 rounded-md shadow-sm py-2 px-3"><option value="0">Tidak</option><option value="1">Ya</option></select></div>
                </div>
                <button type="submit" name="add_package" class="mt-4 bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700">Tambah Paket</button>
            </form>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h2 class="text-xl font-semibold mb-4"><i class="fas fa-cogs mr-2"></i>Pengaturan API Token Utama</h2>
            <form action="index.php?page=settings" method="POST">
                <label for="api_token" class="block text-gray-700">API Token:</label>
                <input type="password" name="api_token" id="api_token" value="<?= e($current_api_token ?? '') ?>" class="w-full px-3 py-2 border rounded-lg mb-4 mt-1">
                <button type="submit" name="update_api_token" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">Simpan Token</button>
            </form>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md mb-8">
            <h2 class="text-xl font-semibold mb-4"><i class="fab fa-telegram-plane mr-2"></i>Pengaturan Notifikasi Telegram</h2>
            <form action="index.php?page=settings" method="POST">
                <div class="mb-4"><label for="telegram_bot_token" class="block text-gray-700">Telegram Bot Token:</label><input type="text" name="telegram_bot_token" id="telegram_bot_token" value="<?= e($telegram_settings['telegram_bot_token'] ?? '') ?>" class="w-full px-3 py-2 border rounded-lg mt-1"></div>
                <div class="mb-4"><label for="telegram_chat_id" class="block text-gray-700">Telegram Chat ID (Tujuan):</label><input type="text" name="telegram_chat_id" id="telegram_chat_id" value="<?= e($telegram_settings['telegram_chat_id'] ?? '') ?>" class="w-full px-3 py-2 border rounded-lg mt-1"></div>
                <button type="submit" name="update_telegram_settings" class="bg-blue-500 text-white py-2 px-4 rounded hover:bg-blue-600">Simpan Pengaturan Telegram</button>
            </form>
        </div>

        <div class="bg-white p-6 rounded-lg shadow-md">
            <h2 class="text-xl font-semibold mb-4"><i class="fas fa-users-cog mr-2"></i>Manajemen Pengguna</h2>
            <div class="overflow-x-auto">
                <table class="min-w-full bg-white divide-y divide-gray-200">
                    <thead class="bg-gray-800 text-white">
                        <tr>
                            <th class="py-3 px-4 text-left text-xs font-medium uppercase tracking-wider">Info Pengguna</th>
                            <th class="py-3 px-4 text-left text-xs font-medium uppercase tracking-wider">Manajemen Peran</th>
                            <th class="py-3 px-4 text-left text-xs font-medium uppercase tracking-wider">Tindakan</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200">
                        <?php foreach ($all_users as $user): ?>
                        <tr class="user-row" id="user-row-<?= $user['id'] ?>">
                            <td class="py-4 px-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-gray-900"><?= e($user['username']) ?></div>
                                <div class="text-sm text-gray-500"><?= e($user['email']) ?></div>
                            </td>
                            <td class="py-4 px-4 whitespace-nowrap">
                                <form action="index.php?page=settings" method="POST" class="flex items-center flex-wrap gap-2">
                                    <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                    <select name="role" class="role-selector border p-1.5 rounded-md text-sm" data-userid="<?= $user['id'] ?>">
                                        <option value="free" <?= $user['role'] == 'free' ? 'selected' : '' ?>>Free</option>
                                        <option value="premium" <?= $user['role'] == 'premium' ? 'selected' : '' ?>>Premium</option>
                                    </select>
                                    <div class="premium-options hidden flex items-center gap-2">
                                        <select name="duration" class="duration-selector border p-1.5 rounded-md text-sm">
                                            <option value="">Pilih Durasi...</option>
                                            <option value="+1 day">1 Hari</option>
                                            <option value="+1 week">1 Minggu</option>
                                            <option value="+2 weeks">2 Minggu</option>
                                            <option value="+1 month">1 Bulan</option>
                                            <option value="+1 year">1 Tahun</option>
                                        </select>
                                        <span class="text-sm text-gray-500">atau</span>
                                        <input type="date" name="expiry_date" class="date-selector border p-1 rounded-md text-sm" value="<?= e(substr($user['premium_expiry_date'], 0, 10)) ?>">
                                    </div>
                                    <button type="submit" name="update_user_role" class="bg-indigo-600 text-white px-2 py-1 rounded-md text-xs font-semibold hover:bg-indigo-700">Update</button>
                                </form>
                            </td>
                            <td class="py-4 px-4 whitespace-nowrap text-sm font-medium">
                                <div class="flex items-center space-x-2">
                                    <form action="index.php?page=settings" method="POST" class="flex items-center space-x-2">
                                        <input type="hidden" name="user_id_to_change" value="<?= $user['id'] ?>">
                                        <input type="text" name="admin_new_password" placeholder="Reset Pass..." class="border p-1.5 rounded-md text-sm w-32">
                                        <button type="submit" name="admin_change_user_password" class="bg-yellow-500 text-white px-2 py-1 rounded-md text-xs font-semibold hover:bg-yellow-600">Reset</button>
                                    </form>
                                    <form action="index.php?page=settings" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menghapus pengguna ini secara permanen? Semua datanya akan hilang.');">
                                        <input type="hidden" name="user_id_to_delete" value="<?= $user['id'] ?>">
                                        <button type="submit" name="delete_user" class="bg-red-600 text-white px-2 py-1 rounded-md text-xs font-semibold hover:bg-red-700"><i class="fas fa-trash-alt"></i> Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.user-row').forEach(row => {
        const roleSelector = row.querySelector('.role-selector');
        const premiumOptions = row.querySelector('.premium-options');
        const durationSelector = row.querySelector('.duration-selector');
        const dateSelector = row.querySelector('.date-selector');

        function togglePremiumOptions() {
            if (roleSelector.value === 'premium') {
                premiumOptions.classList.remove('hidden');
            } else {
                premiumOptions.classList.add('hidden');
                durationSelector.value = '';
                dateSelector.value = '';
            }
        }
        togglePremiumOptions();
        roleSelector.addEventListener('change', togglePremiumOptions);
        durationSelector.addEventListener('change', function() {
            if (this.value !== '') { dateSelector.value = ''; }
        });
        dateSelector.addEventListener('change', function() {
            if (this.value !== '') { durationSelector.value = ''; }
        });
    });
});
</script>