<?php
// Veritabanı bağlantı ayarları
define('DB_HOST', 'localhost');
define('DB_NAME', 'ticarium_wiki');
define('DB_USER', 'ticarium_wiki');
define('DB_PASS', '4nGzC4eJPpKzKmjr');

// Site ayarları
define('SITE_NAME', 'Ticarium FAQ');
define('SITE_URL', 'http://localhost/ticarium');
define('ADMIN_URL', SITE_URL . '/admin');

// Güvenlik ayarları
define('SESSION_NAME', 'ticarium_admin');
define('CSRF_TOKEN_NAME', 'csrf_token');

// Veritabanı bağlantısı
try {
    $pdo = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4", DB_USER, DB_PASS);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch(PDOException $e) {
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

// Session başlat
session_name(SESSION_NAME);
session_start();

// CSRF token oluştur
if (!isset($_SESSION[CSRF_TOKEN_NAME])) {
    $_SESSION[CSRF_TOKEN_NAME] = bin2hex(random_bytes(32));
}

// Yardımcı fonksiyonlar
function sanitize($data) {
    return htmlspecialchars(strip_tags(trim($data)), ENT_QUOTES, 'UTF-8');
}

function createSlug($text) {
    $turkish = array('ç', 'ğ', 'ı', 'ö', 'ş', 'ü', 'Ç', 'Ğ', 'I', 'İ', 'Ö', 'Ş', 'Ü');
    $english = array('c', 'g', 'i', 'o', 's', 'u', 'c', 'g', 'i', 'i', 'o', 's', 'u');
    
    $text = str_replace($turkish, $english, $text);
    $text = preg_replace('/[^a-zA-Z0-9\s]/', '', $text);
    $text = preg_replace('/\s+/', '-', trim($text));
    
    return strtolower($text);
}

function isAdmin() {
    return isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true;
}

function requireAdmin() {
    if (!isAdmin()) {
        header('Location: ' . ADMIN_URL . '/login.php');
        exit;
    }
}

function formatDate($date) {
    return date('d.m.Y H:i', strtotime($date));
}

function truncateText($text, $length = 150) {
    if (strlen($text) <= $length) {
        return $text;
    }
    return substr($text, 0, $length) . '...';
}
?>