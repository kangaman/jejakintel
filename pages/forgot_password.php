<?php
$message = '';
$is_success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['email'])) {
    $email = trim($_POST['email']);

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Silakan masukkan alamat email yang valid.';
        $is_success = false;
    } else {
        $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $expiry = date('Y-m-d H:i:s', time() + 3600); // Token berlaku 1 jam

            $update_stmt = $pdo->prepare("UPDATE users SET reset_token = ?, reset_token_expiry = ? WHERE id = ?");
            $update_stmt->execute([$token, $expiry, $user['id']]);

            // Dapatkan base URL dengan benar
            $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
            $host = $_SERVER['HTTP_HOST'];
            $path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
            $base_url = "{$protocol}://{$host}{$path}";
            
            $reset_link = "{$base_url}/index.php?page=reset_password&token={$token}";
            
            $subject = "Reset Password Akun JejakIntel Anda";
            $body = "
                <div style='font-family: Arial, sans-serif; line-height: 1.6;'>
                    <h2>Permintaan Reset Password</h2>
                    <p>Kami menerima permintaan untuk mereset password akun Anda di JejakIntel. Klik tombol di bawah ini untuk melanjutkan:</p>
                    <p style='margin: 20px 0;'>
                        <a href='{$reset_link}' style='padding: 12px 20px; background-color: #2563EB; color: white; text-decoration: none; border-radius: 5px; font-weight: bold;'>Reset Password Saya</a>
                    </p>
                    <p>Jika Anda tidak merasa melakukan permintaan ini, silakan abaikan email ini.</p>
                    <p>Link ini akan kedaluwarsa dalam 1 jam.</p>
                    <hr>
                    <p style='font-size: 0.8em; color: #777;'>Jika tombol tidak berfungsi, salin dan tempel URL berikut di browser Anda:<br>{$reset_link}</p>
                </div>
            ";

            send_email($email, $subject, $body);
        }
        
        $message = 'Jika alamat email Anda terdaftar di sistem kami, sebuah link untuk mereset password telah dikirim.';
        $is_success = true;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lupa Password - JejakIntel</title>
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
            <p class="text-slate-400 mt-2">Reset Password Akun Anda.</p>
        </div>
        
        <?php if (!empty($message)): ?>
            <div class="<?= $is_success ? 'bg-green-500/10 border-green-500/30 text-green-300' : 'bg-red-500/10 border-red-500/30 text-red-300' ?> px-4 py-3 rounded-lg mb-4 text-sm">
                <?= e($message) ?>
            </div>
        <?php endif; ?>

        <?php if (!$is_success): ?>
        <form action="index.php?page=forgot_password" method="POST">
            <div class="mb-4">
                <label for="email" class="block text-slate-300 text-sm font-medium mb-1">Alamat Email Terdaftar</label>
                <input type="email" name="email" id="email" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2.5 rounded-lg hover:bg-blue-700 transition">Kirim Link Reset</button>
        </form>
        <?php endif; ?>

        <p class="text-center text-sm text-slate-400 mt-6">
            Ingat password Anda? <a href="index.php?page=login" class="text-blue-400 hover:underline font-medium">Kembali ke Login</a>
        </p>
    </div>
</body>
</html>