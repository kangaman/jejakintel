<?php
$error_message = '';
$success_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';

    if (empty($username) || empty($email) || empty($password) || empty($confirm_password)) {
        $error_message = 'Semua kolom wajib diisi.';
    } elseif (!preg_match('/^[a-zA-Z]+$/', $username)) {
        $error_message = 'Username hanya boleh mengandung huruf dan tidak boleh ada spasi.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error_message = 'Format email tidak valid.';
    } elseif (strlen($password) < 8) {
        $error_message = 'Password minimal harus 8 karakter.';
    } elseif (!preg_match('/[a-z]/', $password) || !preg_match('/[A-Z]/', $password) || !preg_match('/[0-9]/', $password)) {
        $error_message = 'Password harus mengandung kombinasi huruf besar, kecil, dan angka.';
    } elseif ($password !== $confirm_password) {
        $error_message = 'Password dan konfirmasi password tidak cocok.';
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
        $stmt->execute([$username, $email]);
        if ($stmt->fetch()) {
            $error_message = 'Username atau email sudah terdaftar.';
        } else {
            $hashed_password = password_hash($password, PASSWORD_DEFAULT);
            $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role) VALUES (?, ?, ?, 'free')");
            if ($stmt->execute([$username, $email, $hashed_password])) {
                $success_message = 'Registrasi berhasil! Silakan <a href="index.php?page=login" class="font-bold hover:underline">login</a>.';
                
                // KIRIM NOTIFIKASI TELEGRAM
                $notif_message = "<b>✅ Pendaftaran Baru!</b>\n\n";
                $notif_message .= "<b>Username:</b> " . htmlspecialchars($username) . "\n";
                $notif_message .= "<b>Email:</b> " . htmlspecialchars($email);
                send_telegram_notification($notif_message, $pdo);

            } else {
                $error_message = 'Terjadi kesalahan sistem. Gagal mendaftar.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Registrasi - JejakIntel</title>
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
            <p class="text-slate-400 mt-2">Buat akun gratis Anda.</p>
        </div>
        
        <?php if (!empty($error_message)): ?>
            <div class="bg-red-500/10 border border-red-500/30 text-red-300 px-4 py-3 rounded-lg mb-4 text-sm">
                <?= e($error_message) ?>
            </div>
        <?php endif; ?>

        <?php if (!empty($success_message)): ?>
            <div class="bg-green-500/10 border border-green-500/30 text-green-300 px-4 py-3 rounded-lg mb-4 text-sm">
                <?= $success_message ?>
            </div>
        <?php else: ?>
            <form action="index.php?page=register" method="POST" class="space-y-4">
                <div>
                    <label for="username" class="block text-slate-300 text-sm font-medium mb-1">Username</label>
                    <input type="text" name="username" id="username" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                    <p class="text-xs text-slate-500 mt-1">Hanya huruf, tanpa angka atau spasi.</p>
                </div>
                <div>
                    <label for="email" class="block text-slate-300 text-sm font-medium mb-1">Email</label>
                    <input type="email" name="email" id="email" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <div>
                    <label for="password" class="block text-slate-300 text-sm font-medium mb-1">Password</o>
                    <input type="password" name="password" id="password" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                     <p class="text-xs text-slate-500 mt-1">Min. 8 karakter, kombinasi huruf besar, kecil, dan angka.</p>
                </div>
                 <div>
                    <label for="confirm_password" class="block text-slate-300 text-sm font-medium mb-1">Konfirmasi Password</label>
                    <input type="password" name="confirm_password" id="confirm_password" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                </div>
                <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2 rounded-lg hover:bg-blue-700 transition">Buat Akun</button>
            </form>
        <?php endif; ?>
        <p class="text-center text-sm text-slate-400 mt-6">
            Sudah punya akun? <a href="index.php?page=login" class="text-blue-400 hover:underline font-medium">Masuk disini</a>
        </p>
    </div>
</body>
</html>