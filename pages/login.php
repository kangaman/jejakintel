<?php
// Logika untuk proses login (tidak ada perubahan)
$error_message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        $error_message = 'Username dan password tidak boleh kosong.';
    } else {
        $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");
        $stmt->execute([$username]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['user_role'] = $user['role'];
            $_SESSION['last_activity'] = time();
            log_activity($pdo, 'USER_LOGIN_SUCCESS', "Pengguna '{$username}' berhasil login.", $user['id']);
            redirect('index.php?page=dashboard');
        } else {
            $error_message = 'Username atau password salah.';
            log_activity($pdo, 'USER_LOGIN_FAILED', "Percobaan login gagal untuk username '{$username}'.");
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Login - JejakIntel Platform</title>
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
            <p class="text-slate-400 mt-2">Masuk ke akun Anda.</p>
        </div>
        
        <?php if (!empty($error_message)): ?>
            <div class="bg-red-500/10 border border-red-500/30 text-red-300 px-4 py-3 rounded-lg mb-4 text-sm"><?= e($error_message) ?></div>
        <?php endif; ?>
        <?php if (isset($_GET['status']) && $_GET['status'] == 'session_expired'): ?>
            <div class="bg-yellow-500/10 border border-yellow-500/30 text-yellow-300 px-4 py-3 rounded-lg mb-4 text-sm">Sesi Anda telah berakhir. Silakan login kembali.</div>
        <?php endif; ?>
        <?php if (isset($_GET['status']) && $_GET['status'] == 'logged_out'): ?>
            <div class="bg-green-500/10 border border-green-500/30 text-green-300 px-4 py-3 rounded-lg mb-4 text-sm">Anda telah berhasil logout.</div>
        <?php endif; ?>
        <?php if (isset($_GET['status']) && $_GET['status'] == 'password_reset_success'): ?>
            <div class="bg-green-500/10 border border-green-500/30 text-green-300 px-4 py-3 rounded-lg mb-4 text-sm">Password Anda telah berhasil diubah. Silakan login dengan password baru.</div>
        <?php endif; ?>

        <form action="index.php?page=login" method="POST" class="space-y-4">
            <div>
                <label for="username" class="block text-slate-300 text-sm font-medium mb-1">Username</label>
                <input type="text" name="username" id="username" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            <div>
                <label for="password" class="block text-slate-300 text-sm font-medium mb-1">Password</label>
                <input type="password" name="password" id="password" class="w-full px-3 py-2 bg-slate-700 border border-slate-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-blue-500" required>
            </div>
            
            <div class="text-right text-sm">
                <a href="index.php?page=forgot_password" class="text-blue-400 hover:underline font-medium">Lupa Password?</a>
            </div>

            <button type="submit" class="w-full bg-blue-600 text-white font-bold py-2.5 rounded-lg hover:bg-blue-700 transition">Login</button>
        </form>
        <p class="text-center text-sm text-slate-400 mt-6">
            Belum punya akun? <a href="index.php?page=register" class="text-blue-400 hover:underline font-medium">Registrasi disini</a>
        </p>
    </div>
</body>
</html>