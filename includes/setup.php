<?php
/**
 * TechCode - Database Setup
 * Veritabanı tablolarını oluşturur ve varsayılan verileri ekler
 */

require_once __DIR__ . '/config.php';

function setupDatabase() {
    $db = getDB();

    // Tabloları oluştur
    $db->exec("
        -- Kullanıcılar tablosu
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

        -- Site ayarları tablosu
        CREATE TABLE IF NOT EXISTS settings (
            id INTEGER PRIMARY KEY AUTOINCREMENT,
            setting_key TEXT UNIQUE NOT NULL,
            setting_value TEXT,
            updated_at DATETIME DEFAULT CURRENT_TIMESTAMP
        );

        -- Sayfalar tablosu
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

        -- Hizmetler tablosu
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

        -- Projeler tablosu
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

        -- Blog/Duyurular tablosu
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

        -- Referanslar tablosu
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

        -- İletişim mesajları tablosu
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
    $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
    $stmt->execute([DEFAULT_ADMIN_EMAIL]);

    if ($stmt->fetchColumn() == 0) {
        $hashedPassword = password_hash(DEFAULT_ADMIN_PASSWORD, PASSWORD_DEFAULT);
        $stmt = $db->prepare("
            INSERT INTO users (name, username, email, password, role, status)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $stmt->execute(['Admin User', 'admin', DEFAULT_ADMIN_EMAIL, $hashedPassword, 'admin', 'active']);
    }

    // Varsayılan site ayarları
    $defaultSettings = [
        'site_name' => 'TechCode',
        'site_slogan' => 'Yazılım & Web Geliştirme',
        'site_description' => 'Profesyonel web sitesi ve yazılım geliştirme hizmetleri. Modern, ölçeklenebilir ve güvenilir çözümler.',
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
        'meta_keywords' => 'yazılım, web geliştirme, mobil uygulama, dijital dönüşüm',
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
        ['Web Geliştirme', 'Responsive, SEO uyumlu ve yüksek performanslı web siteleri ve web uygulamaları geliştiriyoruz.', 'fa-globe', 'blue', 1],
        ['Mobil Uygulama', 'iOS ve Android platformları için native ve cross-platform mobil uygulamalar geliştiriyoruz.', 'fa-mobile-alt', 'purple', 2],
        ['Kurumsal Yazılım', 'ERP, CRM ve özel iş süreçlerinize uygun kurumsal yazılım çözümleri sunuyoruz.', 'fa-cogs', 'green', 3],
        ['E-Ticaret', 'Güvenli ödeme sistemleri ve stok yönetimi ile güçlü e-ticaret platformları kuruyoruz.', 'fa-shopping-cart', 'orange', 4],
        ['Cloud Çözümler', 'AWS, Azure ve Google Cloud altyapıları ile ölçeklenebilir bulut çözümleri sunuyoruz.', 'fa-cloud', 'blue', 5],
        ['Güvenlik & Bakım', 'Yazılımlarınızın güvenliğini sağlıyor, düzenli bakım ve güncelleme hizmetleri sunuyoruz.', 'fa-shield-alt', 'purple', 6]
    ];

    $stmt = $db->prepare("SELECT COUNT(*) FROM services");
    $stmt->execute();

    if ($stmt->fetchColumn() == 0) {
        $stmt = $db->prepare("INSERT INTO services (title, description, icon, icon_color, sort_order) VALUES (?, ?, ?, ?, ?)");
        foreach ($defaultServices as $service) {
            $stmt->execute($service);
        }
    }

    // Varsayılan projeler
    $defaultProjects = [
        ['E-Ticaret Platformu', 'Büyük ölçekli bir perakende şirketi için geliştirilen e-ticaret platformu.', 'E-Ticaret', 'React,Node.js,MongoDB', 1],
        ['Sağlık Yönetim Sistemi', 'Hastaneler için hasta takip ve randevu yönetim sistemi.', 'Kurumsal', 'Vue.js,Python,PostgreSQL', 2],
        ['Eğitim Mobil Uygulaması', 'Online eğitim platformu için iOS ve Android uygulaması.', 'Mobil', 'React Native,Firebase', 3],
        ['Finans Dashboard', 'Finans şirketi için gerçek zamanlı veri analiz paneli.', 'Web', 'Angular,.NET Core,SQL Server', 4],
        ['Lojistik Takip Sistemi', 'Kargo ve teslimat süreçleri için entegre takip sistemi.', 'Kurumsal', 'Next.js,GraphQL,AWS', 5],
        ['Otel Rezervasyon Sistemi', 'Otel zinciri için online rezervasyon ve yönetim sistemi.', 'Web', 'Laravel,Vue.js,MySQL', 6]
    ];

    $stmt = $db->prepare("SELECT COUNT(*) FROM projects");
    $stmt->execute();

    if ($stmt->fetchColumn() == 0) {
        $stmt = $db->prepare("INSERT INTO projects (title, description, category, technologies, sort_order) VALUES (?, ?, ?, ?, ?)");
        foreach ($defaultProjects as $project) {
            $stmt->execute($project);
        }
    }

    // Varsayılan referanslar
    $defaultTestimonials = [
        ['Ahmet Yılmaz', 'RetailMax', 'CEO', 'TechCode ekibi, e-ticaret projemizi mükemmel bir şekilde hayata geçirdi. Profesyonel yaklaşımları ve teknik uzmanlıkları sayesinde satışlarımız %200 arttı.', 5, 1],
        ['Zeynep Kaya', 'MediCare', 'IT Direktörü', 'Hastane yönetim sistemimiz için TechCode ile çalışmak harika bir deneyimdi. Süreçlerimiz artık çok daha verimli ve hasta memnuniyeti arttı.', 5, 2],
        ['Mehmet Demir', 'EduLearn', 'Kurucu', 'Mobil uygulamamız tam istediğimiz gibi oldu. TechCode\'un yaratıcı tasarım anlayışı ve teknik becerisi gerçekten etkileyici.', 5, 3]
    ];

    $stmt = $db->prepare("SELECT COUNT(*) FROM testimonials");
    $stmt->execute();

    if ($stmt->fetchColumn() == 0) {
        $stmt = $db->prepare("INSERT INTO testimonials (name, company, position, content, rating, sort_order) VALUES (?, ?, ?, ?, ?, ?)");
        foreach ($defaultTestimonials as $testimonial) {
            $stmt->execute($testimonial);
        }
    }

    return true;
}

// Kurulumu sessizce çalıştır (echo yok - web sayfasında görünmesin)
try {
    setupDatabase();
} catch (Exception $e) {
    // Hata logla ama ekrana yazma
    error_log("TechCode DB Setup Error: " . $e->getMessage());
}
