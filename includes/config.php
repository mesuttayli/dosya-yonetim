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

// Veritabanı yolu - data klasörü yoksa oluştur
$dataDir = __DIR__ . '/../data';
if (!is_dir($dataDir)) {
    mkdir($dataDir, 0777, true);
}
define('DB_PATH', $dataDir . '/techcode.db');

// Varsayılan admin bilgileri
define('DEFAULT_ADMIN_EMAIL', 'admin@techcode.com.tr');
define('DEFAULT_ADMIN_PASSWORD', 'Admin123!');

/**
 * Veritabanı bağlantısı (SQLite)
 */
function getDB() {
    static $db = null;
    static $initialized = false;

    if ($db === null) {
        // SQLite uzantısı kontrolü
        if (!extension_loaded('pdo_sqlite')) {
            die('<div style="font-family: Arial; padding: 40px; max-width: 600px; margin: 50px auto; background: #1a1a2e; color: #fff; border-radius: 10px;">
                <h2 style="color: #ff4444;">SQLite Uzantısı Yüklü Değil!</h2>
                <p>XAMPP\'ta SQLite\'ı etkinleştirmek için:</p>
                <ol>
                    <li>XAMPP Control Panel\'den Apache\'yi durdurun</li>
                    <li><code style="background: #333; padding: 2px 6px; border-radius: 3px;">C:\xampp\php\php.ini</code> dosyasını açın</li>
                    <li><code style="background: #333; padding: 2px 6px; border-radius: 3px;">;extension=pdo_sqlite</code> satırını bulun</li>
                    <li>Başındaki <code style="background: #333; padding: 2px 6px; border-radius: 3px;">;</code> işaretini kaldırın</li>
                    <li><code style="background: #333; padding: 2px 6px; border-radius: 3px;">;extension=sqlite3</code> satırını da aynı şekilde aktifleştirin</li>
                    <li>Apache\'yi yeniden başlatın</li>
                </ol>
            </div>');
        }

        $needsSetup = !file_exists(DB_PATH);

        try {
            $db = new PDO('sqlite:' . DB_PATH);
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $db->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);

            // İlk kez oluşturuluyorsa veritabanını kur
            if ($needsSetup && !$initialized) {
                $initialized = true;
                initializeDatabase($db);
            }
        } catch (PDOException $e) {
            die('<div style="font-family: Arial; padding: 40px; max-width: 600px; margin: 50px auto; background: #1a1a2e; color: #fff; border-radius: 10px;">
                <h2 style="color: #ff4444;">Veritabanı Hatası</h2>
                <p>' . htmlspecialchars($e->getMessage()) . '</p>
                <p>Data klasörünün yazma izinlerini kontrol edin.</p>
            </div>');
        }
    }

    return $db;
}

/**
 * Veritabanını otomatik kur
 */
function initializeDatabase($db) {
    // Tabloları oluştur
    $db->exec("
        CREATE TABLE IF NOT EXISTS users (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            username TEXT UNIQUE NOT NULL,
            email TEXT UNIQUE NOT NULL,
            password TEXT NOT NULL,
            role TEXT DEFAULT 'viewer',
            status TEXT DEFAULT 'active',
            avatar TEXT,
            last_login DATETIME,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS settings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            setting_key TEXT UNIQUE NOT NULL,
            setting_value TEXT,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS pages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            slug TEXT UNIQUE NOT NULL,
            content TEXT,
            meta_title TEXT,
            meta_description TEXT,
            status TEXT DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS services (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            description TEXT,
            icon TEXT DEFAULT 'fa-cog',
            icon_color TEXT DEFAULT 'blue',
            sort_order INTEGER DEFAULT 0,
            status TEXT DEFAULT 'active',
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS projects (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            description TEXT,
            category TEXT,
            technologies TEXT,
            image TEXT,
            project_url TEXT,
            status TEXT DEFAULT 'active',
            sort_order INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS posts (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            title TEXT NOT NULL,
            slug TEXT UNIQUE NOT NULL,
            excerpt TEXT,
            content TEXT,
            category TEXT,
            tags TEXT,
            image TEXT,
            author_id INTEGER,
            views INTEGER DEFAULT 0,
            status TEXT DEFAULT 'draft',
            published_at DATETIME,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (author_id) REFERENCES users(id)
        );

        CREATE TABLE IF NOT EXISTS testimonials (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            company TEXT,
            position TEXT,
            content TEXT NOT NULL,
            avatar TEXT,
            rating INTEGER DEFAULT 5,
            status TEXT DEFAULT 'active',
            sort_order INTEGER DEFAULT 0,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        CREATE TABLE IF NOT EXISTS messages (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            name TEXT NOT NULL,
            email TEXT NOT NULL,
            phone TEXT,
            subject TEXT,
            message TEXT NOT NULL,
            is_read INTEGER DEFAULT 0,
            is_replied INTEGER DEFAULT 0,
            replied_at DATETIME,
            reply_message TEXT,
            created_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );
    ");

    // Varsayılan admin kullanıcısı
    $hashedPassword = password_hash(DEFAULT_ADMIN_PASSWORD, PASSWORD_DEFAULT);
    $stmt = $db->prepare("INSERT OR IGNORE INTO users (name, username, email, password, role, status) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute(['Admin User', 'admin', DEFAULT_ADMIN_EMAIL, $hashedPassword, 'admin', 'active']);

    // Varsayılan site ayarları
    $defaultSettings = [
        'site_name' => 'TechCode',
        'site_slogan' => 'Yazılım & Web Geliştirme',
        'site_description' => 'Profesyonel web sitesi ve yazılım geliştirme hizmetleri.',
        'site_email' => 'info@techcode.com.tr',
        'site_phone' => '+90 (216) 123 45 67',
        'site_address' => 'Teknokent, A Blok No: 42, Kadıköy, İstanbul',
        'hero_title' => 'Fikirlerinizi Kodla Hayata Geçiriyoruz',
        'hero_subtitle' => 'Modern teknolojiler ve yenilikçi yaklaşımlarla işletmenizi dijital dünyada bir adım öne taşıyoruz.',
        'hero_badge' => 'Yeni Nesil Yazılım Çözümleri',
        'stat_projects' => '150+',
        'stat_clients' => '50+',
        'stat_experience' => '8+',
        'social_linkedin' => '',
        'social_twitter' => '',
        'social_github' => '',
        'social_instagram' => '',
        'meta_title' => 'TechCode | Yazılım & Web Geliştirme',
        'meta_description' => 'Profesyonel web sitesi ve yazılım geliştirme hizmetleri.',
        'meta_keywords' => 'yazılım, web geliştirme, mobil uygulama',
        'google_analytics' => '',
        'primary_color' => '#00d4ff',
        'secondary_color' => '#7b2cbf'
    ];

    foreach ($defaultSettings as $key => $value) {
        $stmt = $db->prepare("INSERT OR IGNORE INTO settings (setting_key, setting_value) VALUES (?, ?)");
        $stmt->execute([$key, $value]);
    }

    // Varsayılan hizmetler
    $defaultServices = [
        ['Web Geliştirme', 'Responsive, SEO uyumlu ve yüksek performanslı web siteleri geliştiriyoruz.', 'fa-globe', 'blue', 1],
        ['Mobil Uygulama', 'iOS ve Android için native ve cross-platform uygulamalar geliştiriyoruz.', 'fa-mobile-alt', 'purple', 2],
        ['Kurumsal Yazılım', 'ERP, CRM ve özel iş süreçlerinize uygun yazılım çözümleri.', 'fa-cogs', 'green', 3],
        ['E-Ticaret', 'Güvenli ödeme ve stok yönetimi ile e-ticaret platformları.', 'fa-shopping-cart', 'orange', 4],
        ['Cloud Çözümler', 'AWS, Azure ve Google Cloud ile ölçeklenebilir bulut çözümleri.', 'fa-cloud', 'blue', 5],
        ['Güvenlik & Bakım', 'Yazılım güvenliği, bakım ve güncelleme hizmetleri.', 'fa-shield-alt', 'purple', 6]
    ];

    $stmt = $db->prepare("INSERT INTO services (title, description, icon, icon_color, sort_order) VALUES (?, ?, ?, ?, ?)");
    foreach ($defaultServices as $service) {
        $stmt->execute($service);
    }

    // Varsayılan projeler
    $defaultProjects = [
        ['E-Ticaret Platformu', 'Büyük ölçekli perakende şirketi için e-ticaret platformu.', 'E-Ticaret', 'React,Node.js,MongoDB', 1],
        ['Sağlık Yönetim Sistemi', 'Hastaneler için hasta takip ve randevu sistemi.', 'Kurumsal', 'Vue.js,Python,PostgreSQL', 2],
        ['Eğitim Mobil Uygulaması', 'Online eğitim platformu mobil uygulaması.', 'Mobil', 'React Native,Firebase', 3],
        ['Finans Dashboard', 'Gerçek zamanlı veri analiz paneli.', 'Web', 'Angular,.NET Core,SQL Server', 4]
    ];

    $stmt = $db->prepare("INSERT INTO projects (title, description, category, technologies, sort_order) VALUES (?, ?, ?, ?, ?)");
    foreach ($defaultProjects as $project) {
        $stmt->execute($project);
    }

    // Varsayılan referanslar
    $defaultTestimonials = [
        ['Ahmet Yılmaz', 'RetailMax', 'CEO', 'TechCode ekibi e-ticaret projemizi mükemmel şekilde hayata geçirdi.', 5, 1],
        ['Zeynep Kaya', 'MediCare', 'IT Direktörü', 'Hastane yönetim sistemimiz çok verimli çalışıyor.', 5, 2],
        ['Mehmet Demir', 'EduLearn', 'Kurucu', 'Mobil uygulamamız tam istediğimiz gibi oldu.', 5, 3]
    ];

    $stmt = $db->prepare("INSERT INTO testimonials (name, company, position, content, rating, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
    foreach ($defaultTestimonials as $testimonial) {
        $stmt->execute($testimonial);
    }
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
