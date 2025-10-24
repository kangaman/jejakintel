<?php
$token = $_GET['token'] ?? null;
$error_message = '';
$success_message = '';
$is_valid_token = false;
$user_id = null;

if (!$token) {
    redirect('index.php?page=login');
}

// 1. Validasi token dari URL
$stmt = $pdo->prepare("SELECT id, reset_token_expiry FROM users WHERE reset_token = ?");
$stmt->execute([$token]);
$user = $stmt->fetch();

if ($user && strtotime($user['reset_token_expiry']) > time()) {
    // Token valid dan belum kedaluwarsa
    $is_valid_token = true;
    $user_id = $user['id'];
    
    // 2. Proses form jika password baru dikirim
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['new_password'])) {
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];

        // Validasi kekuatan password
        if (strlen($new_password) < 8) {
            $error_message = 'Password baru minimal harus 8 karakter.';
        } elseif (!preg_match('/[a-z]/', $new_password) || !preg_match('/[A-Z]/', $new_password) || !preg_match('/[0-9]/', $new_password)) {
            $error_message = 'Password harus mengandung kombinasi huruf besar, kecil, dan angka.';
        } elseif ($new_password !== $confirm_password) {
            $error_message = 'Password dan konfirmasi tidak cocok.';
        } else {
            // 3. Update password di database
            $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            
            // 4. Update password dan hapus token agar tidak bisa digunakan lagi
            $update_stmt = $pdo->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_token_expiry = NULL WHERE id = ?");
            
            if ($update_stmt->execute([$new_hashed_password, $user_id])) {
                // Redirect ke halaman login dengan pesan sukses
                redirect('index.php?page=login&status=password_reset_success');
            } else {
                $error_message = 'Gagal memperbarui password. Silakan coba lagi.';
            }
        }
    }
} else {
    $error_message = "Token tidak valid atau sudah kedaluwarsa. Silakan ajukan permintaan reset password kembali.";
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Reset Password - JejakIntel</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; }
    </style>
</head>
<body class="bg-slate-900 flex items-center justify-center min-h-screen p-4">
    <div class="w-full max-w-md bg-slate-800 rounded-lg shadow-lg p-8 border border-slate-700">
        <div class="text-center mb-8">
            <a href="index.php" class="text-3xl font-bold text-white flex items-center justify-center">
                <i class="fas fa-fingerprint text-blue-500 mr-3"></i>
                <span>Jejak<span class="font-light">Intel</span></span>
            </a>
            <p class="text-slate-400 mt-2">Buat Password Baru Anda.</p>
        </div>
        
        <?php if (!empty($error_message)): ?>
            <div class="bg-red-500/10 border border-red-500/30 text-red-300 px-4 py-3 rounded-lg mb-4 text-sm">
                <?= e($error_message) ?>
            </div>
        <?php endif; ?>

        <?php if ($is_valid_token): ?>
            <form action="index.php?page=reset_password&token=<?= e($token) ?>" method="POST" class="space-y-4">
                <div>
                    <label for="new_password" class="block text-slate-300 text-sm font-medium mb-1">Password Baru</label>
                    <input type="password" name="new_password" id="new_password" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <p class="text-xs text-slate-500 mt-1">Min. 8 karakter, kombinasi huruf besar, kecil, dan angka.</p>
                </div>
                <div>
                    <label for="confirm_password" class="block text-slate-300 text-sm font-medium mb-1">Konfirmasi Password Baru</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2.5 rounded-lg hover:bg-blue-700 transition">Simpan Password Baru</button>
            </form>
        <?php endif; ?>

        <p class="text-center text-sm text-slate-400 mt-6">
            <a href="index.php?page=login" class="text-blue-400 hover:underline font-medium">Kembali ke Halaman Login</a>
        </p>
    </div>
</body>
</html>