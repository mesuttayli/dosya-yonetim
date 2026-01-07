<?php
/**
 * Admin Panel - Header Include
 */

require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/setup.php';

// Giriş kontrolü
requireLogin();

$db = getDB();

// Okunmamış mesaj sayısı
$stmt = $db->prepare("SELECT COUNT(*) FROM messages WHERE is_read = 0");
$stmt->execute();
$unreadMessages = $stmt->fetchColumn();

// Mevcut kullanıcı
$currentUser = [
    'id' => $_SESSION['user_id'],
    'name' => $_SESSION['user_name'],
    'email' => $_SESSION['user_email'],
    'role' => $_SESSION['user_role'],
    'initials' => strtoupper(substr($_SESSION['user_name'], 0, 1))
];

// Flash mesaj
$flash = getFlash();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $pageTitle ?? 'Dashboard' ?> | TechCode Admin</title>

    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect fill='%230a0e17' width='100' height='100' rx='20'/><text x='50' y='65' font-family='monospace' font-size='50' fill='%2300d4ff' text-anchor='middle'>&lt;/&gt;</text></svg>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="admin-wrapper">
        <!-- Sidebar -->
        <aside class="admin-sidebar" id="sidebar">
            <div class="sidebar-header">
                <a href="dashboard.php" class="logo">
                    <div class="logo-icon">&lt;/&gt;</div>
                    <span>TechCode</span>
                </a>
            </div>

            <nav class="sidebar-nav">
                <div class="nav-section">
                    <span class="nav-section-title">Ana Menü</span>
                    <a href="dashboard.php" class="nav-item <?= ($currentPage ?? '') === 'dashboard' ? 'active' : '' ?>">
                        <i class="fas fa-th-large"></i>
                        <span>Dashboard</span>
                    </a>
                    <?php if (hasPermission('pages')): ?>
                    <a href="pages.php" class="nav-item <?= ($currentPage ?? '') === 'pages' ? 'active' : '' ?>">
                        <i class="fas fa-file-alt"></i>
                        <span>Sayfalar</span>
                    </a>
                    <?php endif; ?>
                    <?php if (hasPermission('services')): ?>
                    <a href="services.php" class="nav-item <?= ($currentPage ?? '') === 'services' ? 'active' : '' ?>">
                        <i class="fas fa-cogs"></i>
                        <span>Hizmetler</span>
                    </a>
                    <?php endif; ?>
                    <?php if (hasPermission('projects')): ?>
                    <a href="projects.php" class="nav-item <?= ($currentPage ?? '') === 'projects' ? 'active' : '' ?>">
                        <i class="fas fa-folder-open"></i>
                        <span>Projeler</span>
                    </a>
                    <?php endif; ?>
                </div>

                <div class="nav-section">
                    <span class="nav-section-title">İçerik</span>
                    <?php if (hasPermission('blog')): ?>
                    <a href="blog.php" class="nav-item <?= ($currentPage ?? '') === 'blog' ? 'active' : '' ?>">
                        <i class="fas fa-newspaper"></i>
                        <span>Blog / Duyurular</span>
                    </a>
                    <?php endif; ?>
                    <?php if (hasPermission('testimonials')): ?>
                    <a href="testimonials.php" class="nav-item <?= ($currentPage ?? '') === 'testimonials' ? 'active' : '' ?>">
                        <i class="fas fa-quote-right"></i>
                        <span>Referanslar</span>
                    </a>
                    <?php endif; ?>
                    <?php if (hasPermission('messages')): ?>
                    <a href="messages.php" class="nav-item <?= ($currentPage ?? '') === 'messages' ? 'active' : '' ?>">
                        <i class="fas fa-envelope"></i>
                        <span>Mesajlar</span>
                        <?php if ($unreadMessages > 0): ?>
                        <span class="badge"><?= $unreadMessages ?></span>
                        <?php endif; ?>
                    </a>
                    <?php endif; ?>
                </div>

                <div class="nav-section">
                    <span class="nav-section-title">Yönetim</span>
                    <?php if (hasPermission('users')): ?>
                    <a href="users.php" class="nav-item <?= ($currentPage ?? '') === 'users' ? 'active' : '' ?>">
                        <i class="fas fa-users"></i>
                        <span>Kullanıcılar</span>
                    </a>
                    <?php endif; ?>
                    <?php if (hasPermission('settings')): ?>
                    <a href="settings.php" class="nav-item <?= ($currentPage ?? '') === 'settings' ? 'active' : '' ?>">
                        <i class="fas fa-cog"></i>
                        <span>Site Ayarları</span>
                    </a>
                    <?php endif; ?>
                </div>
            </nav>

            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <div class="sidebar-user-avatar"><?= $currentUser['initials'] ?></div>
                    <div class="sidebar-user-info">
                        <h5><?= e($currentUser['name']) ?></h5>
                        <span><?= ucfirst($currentUser['role']) ?></span>
                    </div>
                </div>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="admin-main">
            <!-- Header -->
            <header class="admin-header">
                <div class="header-left">
                    <button class="sidebar-toggle" id="sidebarToggle">
                        <i class="fas fa-bars"></i>
                    </button>
                    <h1 class="page-title"><?= $pageTitle ?? 'Dashboard' ?></h1>
                </div>

                <div class="header-right">
                    <div class="header-search">
                        <i class="fas fa-search"></i>
                        <input type="text" placeholder="Ara...">
                    </div>

                    <div class="header-actions">
                        <a href="messages.php" class="header-btn" title="Mesajlar">
                            <i class="fas fa-bell"></i>
                            <?php if ($unreadMessages > 0): ?>
                            <span class="notification-dot"></span>
                            <?php endif; ?>
                        </a>
                        <a href="../index.php" class="header-btn" title="Siteyi Görüntüle" target="_blank">
                            <i class="fas fa-external-link-alt"></i>
                        </a>
                        <a href="logout.php" class="header-btn" title="Çıkış Yap">
                            <i class="fas fa-sign-out-alt"></i>
                        </a>
                    </div>
                </div>
            </header>

            <!-- Content -->
            <div class="admin-content">
                <?php if ($flash): ?>
                <div class="alert alert-<?= $flash['type'] ?>" style="margin-bottom: 20px;">
                    <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : ($flash['type'] === 'error' ? 'times-circle' : 'info-circle') ?>"></i>
                    <div class="alert-content">
                        <p style="margin: 0;"><?= e($flash['message']) ?></p>
                    </div>
                </div>
                <?php endif; ?>
