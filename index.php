<?php
// Memasukkan file konfigurasi dan fungsi
require_once __DIR__ . '/config/database.php';
require_once __DIR__ . '/includes/functions.php';

// Menentukan halaman yang akan ditampilkan berdasarkan parameter URL
$page = $_GET['page'] ?? 'landing';

// Logika untuk logout
if ($page === 'logout') {
    if (session_status() == PHP_SESSION_NONE) { session_start(); }
    session_unset();
    session_destroy();
    redirect('index.php?page=landing&status=logged_out');
}

// ## PERBAIKAN DI SINI ##
// Tentukan halaman mana saja yang bersifat publik
$public_pages = ['landing', 'login', 'register', 'about', 'faq', 'contact', 'submit_contact'];

// Tentukan halaman yang hanya untuk tamu (belum login)
$guest_only_pages = ['landing', 'login', 'register'];

// Jika pengguna sudah login, jangan biarkan mereka mengakses halaman khusus tamu.
if (is_logged_in() && in_array($page, $guest_only_pages)) {
    redirect('index.php?page=dashboard');
}

// Jika pengguna belum login, paksa ke landing page jika mencoba akses halaman internal.
if (!is_logged_in() && !in_array($page, $public_pages)) {
    $page = 'landing';
}
// ## AKHIR PERBAIKAN ##

// Daftar semua halaman yang valid di sistem
$allowed_pages = [
    'landing', 'login', 'register', 'dashboard', 'search', 
    'history', 'settings', 'faq', 'submit_search', 'check_status', 
    'logs', 'about', 'forgot_password', 'reset_password', 'pricing', 
    'edit_package', 'contact'
];

// Memuat file halaman yang sesuai
if (in_array($page, $allowed_pages)) {
    
    // Halaman yang menggunakan layout publik (dark theme, no sidebar)
    $public_layout_pages = ['landing', 'about', 'faq', 'pricing', 'contact'];
    
    // Halaman standalone (layout sendiri)
    $standalone_pages = ['login', 'register', 'forgot_password', 'reset_password', 'submit_contact'];

    if (in_array($page, $public_layout_pages)) {
        include 'includes/public_header.php';
        if ($page === 'landing') {
            include 'landing.php';
        } else {
            include 'pages/' . $page . '.php';
        }
        include 'includes/public_footer.php';

    } elseif (in_array($page, $standalone_pages)) {
        include 'pages/' . $page . '.php';

    } else {
        // Halaman internal (membutuhkan login) dengan layout sidebar
        include 'includes/header.php';
        include 'pages/' . $page . '.php';
        include 'includes/footer.php';
    }

} else {
    http_response_code(404);
    echo "<h1>404 - Halaman tidak ditemukan</h1>";
}
?>