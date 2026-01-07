<?php
/**
 * TechCode - Ana Sayfa
 * Veritabanından dinamik içerik çeker
 */

require_once __DIR__ . '/includes/config.php';
require_once __DIR__ . '/includes/setup.php';

$db = getDB();

// Ayarları getir
$stmt = $db->query("SELECT setting_key, setting_value FROM settings");
$settingsRaw = $stmt->fetchAll();
$settings = [];
foreach ($settingsRaw as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

// Hizmetleri getir
$stmt = $db->query("SELECT * FROM services WHERE status = 'active' ORDER BY sort_order ASC LIMIT 6");
$services = $stmt->fetchAll();

// Projeleri getir
$stmt = $db->query("SELECT * FROM projects WHERE status = 'active' ORDER BY sort_order ASC, created_at DESC LIMIT 6");
$projects = $stmt->fetchAll();

// Referansları getir
$stmt = $db->query("SELECT * FROM testimonials WHERE status = 'active' ORDER BY sort_order ASC LIMIT 3");
$testimonials = $stmt->fetchAll();

// İletişim formu işleme
$formSuccess = false;
$formError = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['contact_form'])) {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $phone = trim($_POST['phone'] ?? '');
    $subject = $_POST['subject'] ?? '';
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($message)) {
        $formError = 'Lütfen gerekli alanları doldurun.';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $formError = 'Geçerli bir e-posta adresi girin.';
    } else {
        $stmt = $db->prepare("INSERT INTO messages (name, email, phone, subject, message) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$name, $email, $phone, $subject, $message]);
        $formSuccess = true;
    }
}

// Icon renk haritası
$colorMap = [
    'blue' => 'var(--accent-blue)',
    'purple' => 'var(--accent-purple)',
    'green' => 'var(--accent-green)',
    'orange' => '#ffa500'
];
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="<?= e($settings['meta_description'] ?? $settings['site_description'] ?? '') ?>">
    <meta name="keywords" content="<?= e($settings['meta_keywords'] ?? '') ?>">
    <meta name="author" content="<?= e($settings['site_name'] ?? 'TechCode') ?>">

    <!-- Open Graph -->
    <meta property="og:title" content="<?= e($settings['meta_title'] ?? $settings['site_name'] ?? 'TechCode') ?>">
    <meta property="og:description" content="<?= e($settings['meta_description'] ?? '') ?>">
    <meta property="og:type" content="website">

    <title><?= e($settings['meta_title'] ?? $settings['site_name'] . ' | ' . $settings['site_slogan']) ?></title>

    <!-- Favicon -->
    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect fill='%230a0e17' width='100' height='100' rx='20'/><text x='50' y='65' font-family='monospace' font-size='50' fill='%2300d4ff' text-anchor='middle'>&lt;/&gt;</text></svg>">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">

    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <!-- Main CSS -->
    <link rel="stylesheet" href="assets/css/main.css">

    <?php if (!empty($settings['google_analytics'])): ?>
    <!-- Google Analytics -->
    <script async src="https://www.googletagmanager.com/gtag/js?id=<?= e($settings['google_analytics']) ?>"></script>
    <script>
        window.dataLayer = window.dataLayer || [];
        function gtag(){dataLayer.push(arguments);}
        gtag('js', new Date());
        gtag('config', '<?= e($settings['google_analytics']) ?>');
    </script>
    <?php endif; ?>
</head>
<body>
    <!-- Navigation -->
    <nav class="navbar" id="navbar">
        <div class="container">
            <a href="#" class="logo">
                <div class="logo-icon">&lt;/&gt;</div>
                <span><?= e($settings['site_name'] ?? 'TechCode') ?></span>
            </a>

            <ul class="nav-menu" id="navMenu">
                <li><a href="#hero" class="active">Ana Sayfa</a></li>
                <li><a href="#services">Hizmetler</a></li>
                <li><a href="#why-us">Neden Biz</a></li>
                <li><a href="#projects">Projeler</a></li>
                <li><a href="#testimonials">Referanslar</a></li>
                <li><a href="#contact">İletişim</a></li>
            </ul>

            <div class="flex" style="gap: 15px;">
                <a href="#contact" class="btn btn-primary btn-sm hide-mobile">Teklif Al</a>
                <button class="mobile-menu-btn" id="mobileMenuBtn" aria-label="Menü">
                    <span></span>
                    <span></span>
                    <span></span>
                </button>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero" id="hero">
        <div class="hero-bg">
            <div class="grid-pattern"></div>
            <div class="gradient-orb gradient-orb-1"></div>
            <div class="gradient-orb gradient-orb-2"></div>
            <div class="code-rain" id="codeRain"></div>
        </div>

        <div class="container">
            <div class="hero-content">
                <div class="hero-badge">
                    <span class="dot"></span>
                    <span><?= e($settings['hero_badge'] ?? 'Yeni Nesil Yazılım Çözümleri') ?></span>
                </div>

                <h1>
                    <?php
                    $heroTitle = $settings['hero_title'] ?? 'Fikirlerinizi Kodla Hayata Geçiriyoruz';
                    $words = explode(' ', $heroTitle);
                    $midpoint = ceil(count($words) / 2);
                    $firstPart = implode(' ', array_slice($words, 0, $midpoint));
                    $secondPart = implode(' ', array_slice($words, $midpoint));
                    ?>
                    <?= e($firstPart) ?> <br>
                    <span class="gradient-text"><?= e($secondPart) ?></span>
                </h1>

                <p class="hero-description">
                    <?= e($settings['hero_subtitle'] ?? 'Modern teknolojiler ve yenilikçi yaklaşımlarla işletmenizi dijital dünyada bir adım öne taşıyoruz.') ?>
                </p>

                <div class="hero-buttons">
                    <a href="#contact" class="btn btn-primary btn-lg">
                        <i class="fas fa-rocket"></i>
                        Projenizi Başlatın
                    </a>
                    <a href="#projects" class="btn btn-secondary btn-lg">
                        <i class="fas fa-folder-open"></i>
                        Projelerimiz
                    </a>
                </div>

                <div class="hero-stats">
                    <div class="stat-item">
                        <div class="stat-number"><?= e($settings['stat_projects'] ?? '150+') ?></div>
                        <div class="stat-label">Tamamlanan Proje</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?= e($settings['stat_clients'] ?? '50+') ?></div>
                        <div class="stat-label">Mutlu Müşteri</div>
                    </div>
                    <div class="stat-item">
                        <div class="stat-number"><?= e($settings['stat_experience'] ?? '8+') ?></div>
                        <div class="stat-label">Yıllık Deneyim</div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Services Section -->
    <section class="section services" id="services">
        <div class="container">
            <div class="section-header">
                <span class="subtitle">Hizmetlerimiz</span>
                <h2>Sunduğumuz Çözümler</h2>
                <p>İşletmenizin ihtiyaçlarına özel, modern ve ölçeklenebilir yazılım çözümleri sunuyoruz.</p>
            </div>

            <div class="grid grid-3">
                <?php foreach ($services as $service): ?>
                <div class="card service-card">
                    <div class="card-icon" style="background: rgba(<?= $service['icon_color'] === 'blue' ? '0, 212, 255' : ($service['icon_color'] === 'purple' ? '123, 44, 191' : ($service['icon_color'] === 'green' ? '0, 255, 136' : '255, 165, 0')) ?>, 0.1);">
                        <i class="fas <?= e($service['icon']) ?>" style="color: <?= $colorMap[$service['icon_color']] ?? 'var(--accent-blue)' ?>;"></i>
                    </div>
                    <h3 class="card-title"><?= e($service['title']) ?></h3>
                    <p class="card-text"><?= e($service['description']) ?></p>
                    <a href="#contact" class="learn-more">
                        Detaylı Bilgi <i class="fas fa-arrow-right"></i>
                    </a>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Why Us Section -->
    <section class="section why-us" id="why-us">
        <div class="container">
            <div class="why-us-grid">
                <div class="why-us-content">
                    <span class="subtitle">Neden Biz</span>
                    <h2>Fark Yaratan Yazılım Ortağınız</h2>
                    <p>
                        <?= e($settings['stat_experience'] ?? '8') ?> yılı aşkın deneyimimizle, işletmelerin dijital
                        dönüşüm yolculuğunda güvenilir bir partner oluyoruz.
                    </p>

                    <div class="feature-list">
                        <div class="feature-item">
                            <div class="feature-icon"><i class="fas fa-code"></i></div>
                            <div class="feature-content">
                                <h4>Modern Teknolojiler</h4>
                                <p>React, Vue, Node.js, Python ve daha fazlası ile güncel çözümler.</p>
                            </div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon"><i class="fas fa-users"></i></div>
                            <div class="feature-content">
                                <h4>Uzman Ekip</h4>
                                <p>Alanında deneyimli yazılımcılar ve tasarımcılardan oluşan ekip.</p>
                            </div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon"><i class="fas fa-clock"></i></div>
                            <div class="feature-content">
                                <h4>Zamanında Teslimat</h4>
                                <p>Projeleri belirlenen sürede ve bütçede tamamlıyoruz.</p>
                            </div>
                        </div>
                        <div class="feature-item">
                            <div class="feature-icon"><i class="fas fa-headset"></i></div>
                            <div class="feature-content">
                                <h4>7/24 Destek</h4>
                                <p>Proje sonrası teknik destek ve bakım hizmetleri sunuyoruz.</p>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="why-us-visual">
                    <div class="code-window">
                        <div class="code-window-header">
                            <span class="code-window-dot red"></span>
                            <span class="code-window-dot yellow"></span>
                            <span class="code-window-dot green"></span>
                        </div>
                        <div class="code-window-body">
                            <div class="code-line"><span class="line-number">1</span><span class="code-keyword">const</span> <span class="code-variable">techcode</span> <span class="code-operator">=</span> <span class="code-operator">{</span></div>
                            <div class="code-line"><span class="line-number">2</span><span class="code-variable">  name</span>: <span class="code-string">"<?= e($settings['site_name'] ?? 'TechCode') ?>"</span>,</div>
                            <div class="code-line"><span class="line-number">3</span><span class="code-variable">  mission</span>: <span class="code-string">"Dijital dönüşüm"</span>,</div>
                            <div class="code-line"><span class="line-number">4</span><span class="code-variable">  experience</span>: <span class="code-string">"<?= e($settings['stat_experience'] ?? '8+') ?> yıl"</span>,</div>
                            <div class="code-line"><span class="line-number">5</span><span class="code-variable">  projects</span>: <span class="code-function"><?= preg_replace('/[^0-9]/', '', $settings['stat_projects'] ?? '150') ?></span>,</div>
                            <div class="code-line"><span class="line-number">6</span><span class="code-function">  build</span>: <span class="code-keyword">async</span> () <span class="code-operator">=></span> <span class="code-operator">{</span></div>
                            <div class="code-line"><span class="line-number">7</span><span class="code-keyword">    return</span> <span class="code-string">"success"</span>;</div>
                            <div class="code-line"><span class="line-number">8</span><span class="code-operator">  }</span></div>
                            <div class="code-line"><span class="line-number">9</span><span class="code-operator">}</span>;</div>
                            <div class="code-line"><span class="line-number">10</span><span class="code-comment">// Hayallerinizi kodluyoruz...</span></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Projects Section -->
    <section class="section projects" id="projects">
        <div class="container">
            <div class="section-header">
                <span class="subtitle">Portfolyo</span>
                <h2>Son Projelerimiz</h2>
                <p>Farklı sektörlerden müşterilerimiz için geliştirdiğimiz bazı projeler.</p>
            </div>

            <div class="grid grid-3">
                <?php
                $projectIcons = ['fa-store', 'fa-heartbeat', 'fa-graduation-cap', 'fa-chart-line', 'fa-truck', 'fa-hotel'];
                $iconColors = ['var(--accent-blue)', 'var(--accent-purple)', 'var(--accent-green)', 'var(--accent-blue)', 'var(--accent-purple)', 'var(--accent-green)'];
                $i = 0;
                foreach ($projects as $project):
                ?>
                <div class="card project-card">
                    <div class="project-image">
                        <div style="width:100%;height:100%;background:linear-gradient(135deg, #1a2232 0%, #2a3444 100%);display:flex;align-items:center;justify-content:center;">
                            <i class="fas <?= $projectIcons[$i % count($projectIcons)] ?>" style="font-size:3rem;color:<?= $iconColors[$i % count($iconColors)] ?>;opacity:0.5;"></i>
                        </div>
                    </div>
                    <div class="project-info">
                        <div class="project-tags">
                            <?php
                            $techs = explode(',', $project['technologies']);
                            foreach (array_slice($techs, 0, 3) as $tech):
                                $tech = trim($tech);
                                if ($tech):
                            ?>
                            <span class="project-tag"><?= e($tech) ?></span>
                            <?php endif; endforeach; ?>
                        </div>
                        <h3><?= e($project['title']) ?></h3>
                        <p><?= e($project['description']) ?></p>
                        <?php if ($project['project_url']): ?>
                        <a href="<?= e($project['project_url']) ?>" target="_blank" class="project-link">
                            Projeyi İncele <i class="fas fa-arrow-right"></i>
                        </a>
                        <?php endif; ?>
                    </div>
                </div>
                <?php $i++; endforeach; ?>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <?php if (!empty($testimonials)): ?>
    <section class="section testimonials" id="testimonials">
        <div class="container">
            <div class="section-header">
                <span class="subtitle">Referanslar</span>
                <h2>Müşterilerimiz Ne Diyor?</h2>
                <p>Birlikte çalıştığımız müşterilerimizin görüşleri.</p>
            </div>

            <div class="grid grid-3">
                <?php foreach ($testimonials as $testimonial): ?>
                <div class="card testimonial-card">
                    <div class="testimonial-rating">
                        <?php for ($star = 1; $star <= 5; $star++): ?>
                        <i class="fas fa-star" style="color: <?= $star <= ($testimonial['rating'] ?? 5) ? '#ffc107' : '#3a4555' ?>;"></i>
                        <?php endfor; ?>
                    </div>
                    <p class="testimonial-text">"<?= e($testimonial['content']) ?>"</p>
                    <div class="testimonial-author">
                        <div class="author-avatar">
                            <div style="width:100%;height:100%;background:var(--accent-gradient);display:flex;align-items:center;justify-content:center;color:var(--bg-primary);font-weight:600;">
                                <?= strtoupper(substr($testimonial['name'], 0, 1) . substr(strstr($testimonial['name'], ' '), 1, 1)) ?>
                            </div>
                        </div>
                        <div class="author-info">
                            <h4><?= e($testimonial['name']) ?></h4>
                            <span><?= e($testimonial['position']) ?>, <?= e($testimonial['company']) ?></span>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Contact Section -->
    <section class="section contact" id="contact">
        <div class="container">
            <div class="section-header">
                <span class="subtitle">İletişim</span>
                <h2>Projenizi Konuşalım</h2>
                <p>Bir sonraki projeniz için bizimle iletişime geçin.</p>
            </div>

            <div class="contact-grid">
                <div class="contact-info">
                    <h3>Bize Ulaşın</h3>
                    <p>Sorularınız mı var? Bize mesaj gönderin.</p>

                    <div class="contact-details">
                        <div class="contact-item">
                            <div class="contact-item-icon"><i class="fas fa-map-marker-alt"></i></div>
                            <div class="contact-item-content">
                                <h5>Adres</h5>
                                <p><?= nl2br(e($settings['site_address'] ?? '')) ?></p>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-item-icon"><i class="fas fa-envelope"></i></div>
                            <div class="contact-item-content">
                                <h5>E-posta</h5>
                                <a href="mailto:<?= e($settings['site_email'] ?? '') ?>"><?= e($settings['site_email'] ?? '') ?></a>
                            </div>
                        </div>
                        <div class="contact-item">
                            <div class="contact-item-icon"><i class="fas fa-phone"></i></div>
                            <div class="contact-item-content">
                                <h5>Telefon</h5>
                                <a href="tel:<?= e($settings['site_phone'] ?? '') ?>"><?= e($settings['site_phone'] ?? '') ?></a>
                            </div>
                        </div>
                    </div>

                    <div class="social-links">
                        <?php if (!empty($settings['social_linkedin'])): ?>
                        <a href="<?= e($settings['social_linkedin']) ?>" class="social-link" target="_blank"><i class="fab fa-linkedin-in"></i></a>
                        <?php endif; ?>
                        <?php if (!empty($settings['social_twitter'])): ?>
                        <a href="<?= e($settings['social_twitter']) ?>" class="social-link" target="_blank"><i class="fab fa-twitter"></i></a>
                        <?php endif; ?>
                        <?php if (!empty($settings['social_github'])): ?>
                        <a href="<?= e($settings['social_github']) ?>" class="social-link" target="_blank"><i class="fab fa-github"></i></a>
                        <?php endif; ?>
                        <?php if (!empty($settings['social_instagram'])): ?>
                        <a href="<?= e($settings['social_instagram']) ?>" class="social-link" target="_blank"><i class="fab fa-instagram"></i></a>
                        <?php endif; ?>
                    </div>
                </div>

                <form class="contact-form" method="POST">
                    <input type="hidden" name="contact_form" value="1">

                    <?php if ($formSuccess): ?>
                    <div class="alert alert-success" style="margin-bottom: 20px;">
                        <i class="fas fa-check-circle"></i>
                        <div class="alert-content">
                            <h5>Mesajınız Gönderildi!</h5>
                            <p>En kısa sürede size dönüş yapacağız.</p>
                        </div>
                    </div>
                    <?php elseif ($formError): ?>
                    <div class="alert alert-error" style="margin-bottom: 20px;">
                        <i class="fas fa-exclamation-circle"></i>
                        <div class="alert-content">
                            <p><?= e($formError) ?></p>
                        </div>
                    </div>
                    <?php endif; ?>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="name">Ad Soyad</label>
                            <input type="text" class="form-control" id="name" name="name" placeholder="Adınız Soyadınız" required>
                        </div>
                        <div class="form-group">
                            <label for="email">E-posta</label>
                            <input type="email" class="form-control" id="email" name="email" placeholder="ornek@email.com" required>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-group">
                            <label for="phone">Telefon</label>
                            <input type="tel" class="form-control" id="phone" name="phone" placeholder="+90 (___) ___ __ __">
                        </div>
                        <div class="form-group">
                            <label for="subject">Konu</label>
                            <select class="form-control" id="subject" name="subject">
                                <option value="">Konu Seçin</option>
                                <option value="Web Geliştirme">Web Geliştirme</option>
                                <option value="Mobil Uygulama">Mobil Uygulama</option>
                                <option value="Kurumsal Yazılım">Kurumsal Yazılım</option>
                                <option value="E-Ticaret">E-Ticaret</option>
                                <option value="Diğer">Diğer</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="message">Mesajınız</label>
                        <textarea class="form-control" id="message" name="message" rows="5" placeholder="Projeniz hakkında bize bilgi verin..." required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                        <i class="fas fa-paper-plane"></i>
                        Mesaj Gönder
                    </button>
                </form>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <div class="footer-grid">
                <div class="footer-brand">
                    <a href="#" class="logo">
                        <div class="logo-icon">&lt;/&gt;</div>
                        <span><?= e($settings['site_name'] ?? 'TechCode') ?></span>
                    </a>
                    <p><?= e($settings['site_description'] ?? '') ?></p>
                </div>

                <div class="footer-links-col">
                    <h4 class="footer-title">Hizmetler</h4>
                    <ul class="footer-links">
                        <?php foreach (array_slice($services, 0, 5) as $service): ?>
                        <li><a href="#services"><?= e($service['title']) ?></a></li>
                        <?php endforeach; ?>
                    </ul>
                </div>

                <div class="footer-links-col">
                    <h4 class="footer-title">Şirket</h4>
                    <ul class="footer-links">
                        <li><a href="#why-us">Hakkımızda</a></li>
                        <li><a href="#projects">Projeler</a></li>
                        <li><a href="#testimonials">Referanslar</a></li>
                        <li><a href="#contact">İletişim</a></li>
                    </ul>
                </div>

                <div class="footer-links-col">
                    <h4 class="footer-title">İletişim</h4>
                    <ul class="footer-links">
                        <li><a href="mailto:<?= e($settings['site_email'] ?? '') ?>"><?= e($settings['site_email'] ?? '') ?></a></li>
                        <li><a href="tel:<?= e($settings['site_phone'] ?? '') ?>"><?= e($settings['site_phone'] ?? '') ?></a></li>
                    </ul>
                </div>
            </div>

            <div class="footer-bottom">
                <p>&copy; <?= date('Y') ?> <?= e($settings['site_name'] ?? 'TechCode') ?>. Tüm hakları saklıdır.</p>
                <p>Made with <span style="color: var(--accent-blue);">&lt;/&gt;</span> in Istanbul</p>
            </div>
        </div>
    </footer>

    <script src="assets/js/main.js"></script>
</body>
</html>
