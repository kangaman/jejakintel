<?php
require_once 'functions.php';
manage_session(); // Panggil fungsi manajemen sesi di setiap halaman
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>JejakIntel - Lacak Jejak Digital Anda</title>
    
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700;800&display=swap');
        body { font-family: 'Inter', sans-serif; background-color: #f1f5f9; }
        aside { background-color: #1e293b; }
        aside a:hover { background-color: #334155; }
        .sidebar-title { color: #cbd5e1; }
    </style>

    <script>
    document.addEventListener('DOMContentLoaded', function () {
        <?php if (is_logged_in()): ?>
        function showToast(message, isSuccess = true) {
            const container = document.getElementById('toast-container');
            if (!container) return;
            const toast = document.createElement('div');
            const bgColor = isSuccess ? 'bg-green-500' : 'bg-red-500';
            const icon = isSuccess ? '<i class="fas fa-check-circle mr-2"></i>' : '<i class="fas fa-exclamation-triangle mr-2"></i>';
            toast.className = `p-4 rounded-lg text-white shadow-lg flex items-center transform transition-all duration-300 translate-x-full opacity-0`;
            toast.classList.add(bgColor);
            toast.innerHTML = `${icon} <span>${message}</span>`;
            container.appendChild(toast);
            setTimeout(() => { toast.classList.remove('translate-x-full', 'opacity-0'); }, 100);
            setTimeout(() => {
                toast.classList.add('opacity-0');
                toast.addEventListener('transitionend', () => toast.remove());
            }, 5000);
        }
        function checkNotificationStatus() {
            fetch('index.php?page=check_status')
                .then(response => response.json())
                .then(data => {
                    if (data.status === 'success' && data.notifications.length > 0) {
                        data.notifications.forEach(notif => {
                            let message = '';
                            if (notif.status === 'completed') {
                                message = `Pencarian "<strong>${notif.query_text}</strong>" telah selesai.`;
                                showToast(message, true);
                            } else if (notif.status === 'failed') {
                                message = `Pencarian "<strong>${notif.query_text}</strong>" gagal.`;
                                showToast(message, false);
                            }
                        });
                        if (window.location.href.includes('page=history')) {
                            showToast('Hasil baru tersedia. Segarkan halaman.', true);
                        }
                    }
                })
                .catch(error => console.error('Error checking notifications:', error));
        }
        setInterval(checkNotificationStatus, 20000);
        <?php endif; ?>
    });
    </script>
</head>
<body class="font-sans leading-normal tracking-normal">

<div class="flex flex-col md:flex-row">

    <?php if (is_logged_in()): ?>
    <aside class="w-full md:w-64 text-white min-h-screen">
        <div class="p-4 text-2xl font-bold text-white flex items-center">
            <i class="fas fa-fingerprint text-blue-400 mr-3"></i>
            <span>Jejak<span class="font-light">Intel</span></span>
        </div>
        <nav>
            <a href="index.php?page=dashboard" class="block py-2.5 px-4 rounded transition duration-200">
                <i class="fas fa-tachometer-alt mr-2 w-5"></i>Dashboard
            </a>
            <a href="index.php?page=search" class="block py-2.5 px-4 rounded transition duration-200">
                <i class="fas fa-search mr-2 w-5"></i>Pencarian Baru
            </a>
            <a href="index.php?page=history" class="block py-2.5 px-4 rounded transition duration-200">
                <i class="fas fa-history mr-2 w-5"></i>History
            </a>
            <a href="index.php?page=settings" class="block py-2.5 px-4 rounded transition duration-200">
                <i class="fas fa-cogs mr-2 w-5"></i>Pengaturan
            </a>
            
            <?php if (user_role() === 'admin'): ?>
            <a href="index.php?page=logs" class="block py-2.5 px-4 rounded transition duration-200">
                <i class="fas fa-clipboard-list mr-2 w-5"></i>Log Aktivitas
            </a>
            <?php endif; ?>
            
            <a href="index.php?page=logout" class="block py-2.5 px-4 rounded transition duration-200 mt-4">
                <i class="fas fa-sign-out-alt mr-2 w-5"></i>Logout
            </a>
        </nav>
    </aside>
    <?php endif; ?>

    <main class="flex-1 p-4 md:p-8">

        <?php
        if (isset($_SESSION['flash_message'])):
            $message = $_SESSION['flash_message'];
            unset($_SESSION['flash_message']); // Hapus pesan setelah dibaca
            
            // Tentukan style Tailwind berdasarkan tipe pesan
            $type_class = '';
            $icon_class = '';
            $title = '';
            
            if ($message['type'] === 'success') {
                $type_class = 'bg-green-100 border-l-4 border-green-500 text-green-700';
                $icon_class = 'fas fa-check-circle';
                $title = 'Sukses!';
            } else { // Asumsi 'error' atau default
                $type_class = 'bg-red-100 border-l-4 border-red-500 text-red-700';
                $icon_class = 'fas fa-exclamation-triangle';
                $title = 'Gagal!';
            }
        ?>
        <div class="<?= $type_class ?> p-4 mb-6 rounded-lg shadow-md" role="alert">
            <p class="font-bold"><i class="<?= $icon_class ?> mr-2"></i><?= $title ?></p>
            <p><?= e($message['message']) // Fungsi e() untuk escaping HTML ?></p>
        </div>
        <?php endif; ?>