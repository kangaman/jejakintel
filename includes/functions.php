<?php
// Impor class PHPMailer
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// Muat file autoloader PHPMailer
require_once __DIR__ . '/../vendor/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../vendor/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../vendor/PHPMailer/src/SMTP.php';

// Memulai sesi di awal file
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Fungsi untuk memeriksa apakah pengguna sudah login
function is_logged_in() {
    return isset($_SESSION['user_id']);
}

// Fungsi untuk memeriksa peran (role) pengguna
function user_role() {
    return $_SESSION['user_role'] ?? null;
}

// Fungsi untuk mengamankan output HTML (mencegah XSS)
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

// Fungsi untuk mengarahkan pengguna ke halaman lain
function redirect($url) {
    header("Location: " . $url);
    exit();
}

// Fungsi untuk mengelola sesi dan auto-logout
function manage_session() {
    if (is_logged_in()) {
        $timeout_duration = 1800; // 30 menit
        if (isset($_SESSION['last_activity']) && (time() - $_SESSION['last_activity']) > $timeout_duration) {
            session_unset();
            session_destroy();
            redirect("index.php?page=login&status=session_expired");
        }
        $_SESSION['last_activity'] = time();
    }
}

// Fungsi untuk mengambil API token dari database
function get_main_api_token($pdo) {
    $stmt = $pdo->prepare("SELECT setting_value FROM settings WHERE setting_name = 'main_api_token'");
    $stmt->execute();
    $result = $stmt->fetch();
    return $result ? $result['setting_value'] : null;
}

// Fungsi untuk memanggil API OSINT
function call_osint_api($token, $query, $limit = 100) {
    $url = 'https://leakosintapi.com/';
    $data = [ "token" => $token, "request" => $query, "limit" => $limit, "lang" => "en", "type" => "json" ];
    $options = [ 'http' => [ 'header'  => "Content-type: application/json\r\n", 'method'  => 'POST', 'content' => json_encode($data), 'ignore_errors' => true ] ];
    $context  = stream_context_create($options);
    $response = file_get_contents($url, false, $context);
    return json_decode($response, true);
}

// Fungsi untuk mengirim notifikasi ke Telegram
function send_telegram_notification($message, $pdo) {
    try {
        $stmt = $pdo->query("SELECT setting_name, setting_value FROM settings WHERE setting_name IN ('telegram_bot_token', 'telegram_chat_id')");
        $settings = $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    } catch (PDOException $e) {
        error_log("Gagal mengambil pengaturan Telegram: " . $e->getMessage());
        return false;
    }
    $bot_token = $settings['telegram_bot_token'] ?? null;
    $chat_id = $settings['telegram_chat_id'] ?? null;
    if (empty($bot_token) || empty($chat_id) || $bot_token === 'KOSONGKAN_ATAU_ISI_TOKEN_BOT_ANDA') { return false; }
    $api_url = "https://api.telegram.org/bot{$bot_token}/sendMessage";
    $data = [ 'chat_id' => $chat_id, 'text' => $message, 'parse_mode' => 'HTML' ];
    $options = [ 'http' => [ 'header'  => "Content-type: application/x-www-form-urlencoded\r\n", 'method'  => 'POST', 'content' => http_build_query($data), 'ignore_errors' => true ] ];
    $context  = stream_context_create($options);
    $result = file_get_contents($api_url, false, $context);
    $response_data = json_decode($result, true);
    return $response_data['ok'] ?? false;
}

// Fungsi untuk log aktivitas
function log_activity($pdo, $action, $details, $user_id = null) {
    $username = 'Guest';
    $ip_address = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
    if ($user_id === null && isset($_SESSION['user_id'])) { $user_id = $_SESSION['user_id']; }
    if ($user_id !== null) {
        try {
            $stmt = $pdo->prepare("SELECT username FROM users WHERE id = ?");
            $stmt->execute([$user_id]);
            $user = $stmt->fetch();
            if ($user) { $username = $user['username']; }
        } catch (PDOException $e) { error_log("Gagal mendapatkan username untuk logging: " . $e->getMessage()); }
    }
    try {
        $stmt = $pdo->prepare("INSERT INTO activity_logs (user_id, username, action, details, ip_address) VALUES (:user_id, :username, :action, :details, :ip_address)");
        $stmt->execute([':user_id' => $user_id, ':username' => $username, ':action' => $action, ':details' => $details, ':ip_address' => $ip_address]);
    } catch (PDOException $e) { error_log("Gagal mencatat aktivitas: " . $e->getMessage()); }
}


/**
 * Mengirim email menggunakan PHPMailer dengan SMTP Brevo (Sendinblue).
 *
 * @param string $to Email tujuan.
 * @param string $subject Judul email.
 * @param string $body Isi email dalam format HTML.
 * @return bool True jika email berhasil dikirim, false jika gagal.
 */
function send_email($to, $subject, $body) {
    $mail = new PHPMailer(true);

    try {
        // Matikan debug setelah selesai testing
        $mail->SMTPDebug = 0; 

        // ## PENGATURAN SERVER DARI BREVO ANDA ##
        $mail->isSMTP();
        $mail->Host       = 'smtp-relay.brevo.com';        // Server SMTP Brevo
        $mail->SMTPAuth   = true;
        $mail->Username   = '993f34001@smtp-brevo.com';      // Login dari Brevo Anda
        $mail->Password   = 'fZ7xRhEG5X9Uk2cA';            // Master password / Kunci SMTP dari Brevo Anda
        $mail->SMTPSecure = 'tls';                          // Gunakan 'tls'
        $mail->Port       = 587;                           // Gunakan port 587

        // Pengirim dan Penerima
        // Gunakan email yang sudah terverifikasi (email login Anda di Brevo)
        $mail->setFrom('no-reply@jejakintel.my.id', 'JejakIntel'); 
        $mail->addAddress($to);

        // Konten Email
        $mail->isHTML(true);
        $mail->Subject = $subject;
        $mail->Body    = $body;
        $mail->AltBody = strip_tags($body);

        $mail->send();
        return true;
    } catch (Exception $e) {
        // Jika gagal, catat error ke log server untuk debugging.
        error_log("Message could not be sent. Mailer Error: {$mail->ErrorInfo}");
        return false;
    }
}

/**
 * --- FUNGSI BARU DITAMBAHKAN DI SINI ---
 * Mengalihkan ke URL lain dengan menyimpan pesan di session.
 *
 * @param string $url URL tujuan
 * @param string $type Tipe pesan ('success' atau 'error')
 * @param string $message Isi pesan
 */
function redirect_with_message($url, $type, $message) {
    // Session sudah dimulai di atas file ini, jadi kita bisa langsung pakai
    
    $_SESSION['flash_message'] = [
        'type' => $type,
        'message' => $message
    ];
    
    // Menggunakan fungsi 'redirect()' yang sudah ada di file ini
    redirect($url);
}

?>