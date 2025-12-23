<?php
/**
 * TechCode - Configuration File
 * Veritabanı ve site ayarları
 */

// Hata raporlama (production'da kapatın)
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session başlat
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Timezone
date_default_timezone_set('Europe/Istanbul');

// Site sabitleri
define('SITE_NAME', 'TechCode');
define('SITE_URL', 'http://localhost');
define('ADMIN_EMAIL', 'admin@techcode.com.tr');

// Veritabanı yolu
define('DB_PATH', __DIR__ . '/../data/techcode.db');

// Varsayılan admin bilgileri
define('DEFAULT_ADMIN_EMAIL', 'admin@techcode.com.tr');
define('DEFAULT_ADMIN_PASSWORD', 'Admin123!');

/**
 * Veritabanı bağlantısı (SQLite)
 */
function getDB() {
    static $db = null;

    if ($db === null) {
        try {
            $db = new PDO('sqlite:' . DB_PATH);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
        } catch (PDOException $e) {
            die('Veritabanı bağlantı hatası: ' . $e->getMessage());
        }
    }

    return $db;
}

/**
 * Güvenli çıktı
 */
function e($string) {
    return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * JSON yanıt gönder
 */
function jsonResponse($data, $statusCode = 200) {
    http_response_code($statusCode);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($data, JSON_UNESCAPED_UNICODE);
    exit;
}

/**
 * Giriş kontrolü
 */
function isLoggedIn() {
    return isset($_SESSION['user_id']) && !empty($_SESSION['user_id']);
}

/**
 * Admin kontrolü
 */
function isAdmin() {
    return isLoggedIn() && isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin';
}

/**
 * Giriş zorunluluğu
 */
function requireLogin() {
    if (!isLoggedIn()) {
        header('Location: login.php');
        exit;
    }
}

/**
 * Admin zorunluluğu
 */
function requireAdmin() {
    requireLogin();
    if (!isAdmin()) {
        header('Location: dashboard.php');
        exit;
    }
}

/**
 * Yetki kontrolü
 */
function hasPermission($permission) {
    if (!isLoggedIn()) return false;

    $role = $_SESSION['user_role'] ?? 'viewer';

    $permissions = [
        'admin' => ['dashboard', 'pages', 'services', 'projects', 'blog', 'testimonials', 'messages', 'users', 'settings'],
        'editor' => ['dashboard', 'pages', 'services', 'projects', 'blog', 'testimonials', 'messages'],
        'author' => ['dashboard', 'blog'],
        'viewer' => ['dashboard']
    ];

    return in_array($permission, $permissions[$role] ?? []);
}

/**
 * Yetki zorunluluğu
 */
function requirePermission($permission) {
    requireLogin();
    if (!hasPermission($permission)) {
        $_SESSION['error'] = 'Bu sayfaya erişim yetkiniz yok.';
        header('Location: dashboard.php');
        exit;
    }
}

/**
 * Flash mesaj
 */
function setFlash($type, $message) {
    $_SESSION['flash'] = ['type' => $type, 'message' => $message];
}

function getFlash() {
    if (isset($_SESSION['flash'])) {
        $flash = $_SESSION['flash'];
        unset($_SESSION['flash']);
        return $flash;
    }
    return null;
}
