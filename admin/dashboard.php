<?php
/**
 * TechCode Admin - Dashboard
 */

$pageTitle = 'Dashboard';
$currentPage = 'dashboard';

require_once __DIR__ . '/includes/header.php';

// İstatistikler
$stats = [];

$stmt = $db->query("SELECT COUNT(*) FROM projects");
$stats['projects'] = $stmt->fetchColumn();

$stmt = $db->query("SELECT COUNT(*) FROM messages");
$stats['messages'] = $stmt->fetchColumn();

$stmt = $db->query("SELECT COUNT(*) FROM messages WHERE is_read = 0");
$stats['unread'] = $stmt->fetchColumn();

$stmt = $db->query("SELECT COUNT(*) FROM posts WHERE status = 'published'");
$stats['posts'] = $stmt->fetchColumn();

// Son mesajlar
$stmt = $db->query("SELECT * FROM messages ORDER BY created_at DESC LIMIT 5");
$recentMessages = $stmt->fetchAll();

// Son projeler
$stmt = $db->query("SELECT * FROM projects ORDER BY created_at DESC LIMIT 5");
$recentProjects = $stmt->fetchAll();
?>

<!-- Stats Grid -->
<div class="stats-grid">
    <div class="stat-card">
        <div class="stat-card-icon blue">
            <i class="fas fa-folder-open"></i>
        </div>
        <div class="stat-card-info">
            <h3><?= $stats['projects'] ?></h3>
            <p>Toplam Proje</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon purple">
            <i class="fas fa-envelope"></i>
        </div>
        <div class="stat-card-info">
            <h3><?= $stats['messages'] ?></h3>
            <p>Mesajlar</p>
            <?php if ($stats['unread'] > 0): ?>
            <span class="trend up"><i class="fas fa-arrow-up"></i> <?= $stats['unread'] ?> yeni</span>
            <?php endif; ?>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon green">
            <i class="fas fa-cogs"></i>
        </div>
        <div class="stat-card-info">
            <?php
            $stmt = $db->query("SELECT COUNT(*) FROM services WHERE status = 'active'");
            ?>
            <h3><?= $stmt->fetchColumn() ?></h3>
            <p>Aktif Hizmet</p>
        </div>
    </div>

    <div class="stat-card">
        <div class="stat-card-icon orange">
            <i class="fas fa-newspaper"></i>
        </div>
        <div class="stat-card-info">
            <h3><?= $stats['posts'] ?></h3>
            <p>Blog Yazısı</p>
        </div>
    </div>
</div>

<!-- Quick Actions -->
<div class="admin-card">
    <div class="admin-card-header">
        <h3>Hızlı İşlemler</h3>
    </div>
    <div class="admin-card-body">
        <div class="quick-actions">
            <?php if (hasPermission('projects')): ?>
            <a href="projects.php?action=new" class="quick-action-card">
                <i class="fas fa-plus"></i>
                <span>Yeni Proje</span>
            </a>
            <?php endif; ?>
            <?php if (hasPermission('blog')): ?>
            <a href="blog.php?action=new" class="quick-action-card">
                <i class="fas fa-edit"></i>
                <span>Yeni Yazı</span>
            </a>
            <?php endif; ?>
            <?php if (hasPermission('services')): ?>
            <a href="services.php?action=new" class="quick-action-card">
                <i class="fas fa-cogs"></i>
                <span>Yeni Hizmet</span>
            </a>
            <?php endif; ?>
            <?php if (hasPermission('messages')): ?>
            <a href="messages.php" class="quick-action-card">
                <i class="fas fa-envelope-open"></i>
                <span>Mesajları Gör</span>
            </a>
            <?php endif; ?>
            <?php if (hasPermission('settings')): ?>
            <a href="settings.php" class="quick-action-card">
                <i class="fas fa-sliders-h"></i>
                <span>Ayarlar</span>
            </a>
            <?php endif; ?>
        </div>
    </div>
</div>

<div style="display: grid; grid-template-columns: 1fr 1fr; gap: 25px;">
    <!-- Recent Messages -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3>Son Mesajlar</h3>
            <a href="messages.php" class="btn btn-ghost btn-sm">Tümünü Gör</a>
        </div>
        <div class="admin-card-body" style="padding: 0;">
            <div class="message-list">
                <?php if (empty($recentMessages)): ?>
                <div style="padding: 30px; text-align: center; color: var(--text-muted);">
                    <i class="fas fa-inbox" style="font-size: 2rem; margin-bottom: 10px;"></i>
                    <p>Henüz mesaj yok</p>
                </div>
                <?php else: ?>
                    <?php foreach ($recentMessages as $message): ?>
                    <div class="message-item <?= !$message['is_read'] ? 'unread' : '' ?>">
                        <div class="message-avatar"><?= strtoupper(substr($message['name'], 0, 2)) ?></div>
                        <div class="message-content">
                            <div class="message-header">
                                <span class="message-sender"><?= e($message['name']) ?></span>
                                <span class="message-time"><?= date('d M H:i', strtotime($message['created_at'])) ?></span>
                            </div>
                            <div class="message-subject"><?= e($message['subject'] ?: 'Konu belirtilmemiş') ?></div>
                            <div class="message-preview"><?= e(substr($message['message'], 0, 80)) ?>...</div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Recent Projects -->
    <div class="admin-card">
        <div class="admin-card-header">
            <h3>Son Projeler</h3>
            <a href="projects.php" class="btn btn-ghost btn-sm">Tümünü Gör</a>
        </div>
        <div class="admin-card-body" style="padding: 0;">
            <?php if (empty($recentProjects)): ?>
            <div style="padding: 30px; text-align: center; color: var(--text-muted);">
                <i class="fas fa-folder-open" style="font-size: 2rem; margin-bottom: 10px;"></i>
                <p>Henüz proje yok</p>
            </div>
            <?php else: ?>
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Proje</th>
                        <th>Kategori</th>
                        <th>Durum</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($recentProjects as $project): ?>
                    <tr>
                        <td><strong><?= e($project['title']) ?></strong></td>
                        <td><?= e($project['category']) ?></td>
                        <td>
                            <span class="status-badge <?= $project['status'] === 'active' ? 'active' : 'pending' ?>">
                                <?= $project['status'] === 'active' ? 'Aktif' : 'Beklemede' ?>
                            </span>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
