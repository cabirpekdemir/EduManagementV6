<?php
// core/config.php

// Geliştirme/Production ayarı
define('ENVIRONMENT', 'development'); // 'development' veya 'production'

// Hata raporlama
if (ENVIRONMENT === 'development') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
} else {
    error_reporting(0);
    ini_set('display_errors', 0);
    ini_set('log_errors', 1);
    ini_set('error_log', __DIR__ . '/../logs/php-errors.log');
}

// BASE URL - OTOMATİK ALGILAMA (DÜZELTİLMİŞ)
$protocol = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

// Script'in bulunduğu dizini al
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));

// BASE_URL oluştur - Her zaman sonunda / olsun
$baseUrl = $protocol . '://' . $host . $scriptDir;
define('BASE_URL', rtrim($baseUrl, '/') . '/');

define('SITE_NAME', 'EduManagement Sistemi');
define('SITE_VERSION', '1.0.0');

// Veritabanı ayarları
define('DB_HOST', 'localhost');
define('DB_NAME', 'hipotezegitimcom_edumanagement8');
define('DB_USER', 'root');
define('DB_PASS', 'root');
define('DB_CHARSET', 'utf8mb4');

// Oturum ayarları
if (session_status() === PHP_SESSION_NONE) {
    ini_set('session.cookie_httponly', 1);
    ini_set('session.use_only_cookies', 1);
    ini_set('session.cookie_secure', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? 1 : 0);
    ini_set('session.cookie_samesite', 'Lax');
    ini_set('session.gc_maxlifetime', 3600);
}

// Timezone
date_default_timezone_set('Europe/Istanbul');

// Dosya yükleme ayarları
define('MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('ALLOWED_FILE_TYPES', ['jpg', 'jpeg', 'png', 'gif', 'pdf', 'doc', 'docx', 'xls', 'xlsx', 'webp']);
define('UPLOAD_PATH', __DIR__ . '/../uploads/');

// Sayfalama ayarları
define('ITEMS_PER_PAGE', 20);

// Mail ayarları
define('MAIL_HOST', 'smtp.gmail.com');
define('MAIL_PORT', 587);
define('MAIL_USERNAME', 'your-email@gmail.com');
define('MAIL_PASSWORD', 'your-app-password');
define('MAIL_FROM_EMAIL', 'noreply@hipotezegitim.com.tr');
define('MAIL_FROM_NAME', SITE_NAME);

// Güvenlik
define('CSRF_TOKEN_LIFETIME', 3600);
define('PASSWORD_MIN_LENGTH', 8);
define('LOGIN_MAX_ATTEMPTS', 5);
define('LOGIN_LOCKOUT_TIME', 900);

// Dil ayarları
define('DEFAULT_LANGUAGE', 'tr');

// Cache ayarları
define('CACHE_ENABLED', false);
define('CACHE_LIFETIME', 3600);

// DEBUG - silinecek
error_log("BASE_URL DEBUG: " . BASE_URL);
error_log("HTTP_HOST: " . ($_SERVER['HTTP_HOST'] ?? 'YOK'));
error_log("SCRIPT_NAME: " . ($_SERVER['SCRIPT_NAME'] ?? 'YOK'));