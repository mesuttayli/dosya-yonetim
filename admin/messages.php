<?php
/**
 * TechCode Admin - Mesajlar Yönetimi
 */

$pageTitle = 'Mesajlar';
$currentPage = 'messages';

require_once __DIR__ . '/includes/header.php';
requirePermission('messages');

// İşlem yap
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'mark_read') {
        $id = $_POST['id'] ?? null;
        if ($id) {
            $stmt = $db->prepare("UPDATE messages SET is_read = 1 WHERE id = ?");
            $stmt->execute([$id]);
        }
        header('Location: messages.php');
        exit;
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? null;
        if ($id) {
            $stmt = $db->prepare("DELETE FROM messages WHERE id = ?");
            $stmt->execute([$id]);
            setFlash('success', 'Mesaj silindi.');
        }
        header('Location: messages.php');
        exit;
    } elseif ($action === 'mark_all_read') {
        $db->exec("UPDATE messages SET is_read = 1");
        setFlash('success', 'Tüm mesajlar okundu olarak işaretlendi.');
        header('Location: messages.php');
        exit;
    }
}

// İstatistikler
$stmt = $db->query("SELECT COUNT(*) FROM messages");
$totalMessages = $stmt->fetchColumn();

$stmt = $db->query("SELECT COUNT(*) FROM messages WHERE is_read = 0");
$unreadCount = $stmt->fetchColumn();

$stmt = $db->query("SELECT COUNT(*) FROM messages WHERE is_replied = 1");
$repliedCount = $stmt->fetchColumn();

// Mesajları getir
$stmt = $db->query("SELECT * FROM messages ORDER BY is_read ASC, created_at DESC");
$messages = $stmt->fetchAll();

// Mesaj detay
$viewMessage = null;
if (isset($_GET['view'])) {
    $stmt = $db->prepare("SELECT * FROM messages WHERE id = ?");
    $stmt->execute([$_GET['view']]);
    $viewMessage = $stmt->fetch();

    if ($viewMessage && !$viewMessage['is_read']) {
        $stmt = $db->prepare("UPDATE messages SET is_read = 1 WHERE id = ?");
        $stmt->execute([$_GET['view']]);
    }
}
?>

<!-- Stats -->
<div class="stats-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 25px;">
    <div class="stat-card">
        <div class="stat-card-icon blue">
            <i class="fas fa-envelope"></i>
        </div>
        <div class="stat-card-info">
            <h3><?= $totalMessages ?></h3>
            <p>Toplam Mesaj</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon green">
            <i class="fas fa-envelope-open"></i>
        </div>
        <div class="stat-card-info">
            <h3><?= $unreadCount ?></h3>
            <p>Okunmamış</p>
        </div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon purple">
            <i class="fas fa-reply"></i>
        </div>
        <div class="stat-card-info">
            <h3><?= $repliedCount ?></h3>
            <p>Yanıtlanan</p>
        </div>
    </div>
</div>

<!-- Actions -->
<?php if ($unreadCount > 0): ?>
<div style="margin-bottom: 20px;">
    <form method="POST" style="display: inline;">
        <input type="hidden" name="action" value="mark_all_read">
        <button type="submit" class="btn btn-ghost btn-sm">
            <i class="fas fa-check-double"></i>
            Tümünü Okundu İşaretle
        </button>
    </form>
</div>
<?php endif; ?>

<!-- Messages List -->
<div class="admin-card">
    <div class="admin-card-body" style="padding: 0;">
        <div class="message-list">
            <?php if (empty($messages)): ?>
            <div style="padding: 50px; text-align: center; color: var(--text-muted);">
                <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 15px;"></i>
                <p>Henüz mesaj yok</p>
            </div>
            <?php else: ?>
                <?php foreach ($messages as $message): ?>
                <div class="message-item <?= !$message['is_read'] ? 'unread' : '' ?>" onclick="window.location='?view=<?= $message['id'] ?>'" style="cursor: pointer;">
                    <div class="message-avatar"><?= strtoupper(substr($message['name'], 0, 2)) ?></div>
                    <div class="message-content">
                        <div class="message-header">
                            <span class="message-sender"><?= e($message['name']) ?></span>
                            <span class="message-time"><?= date('d M Y H:i', strtotime($message['created_at'])) ?></span>
                        </div>
                        <div class="message-subject"><?= e($message['subject'] ?: 'Konu belirtilmemiş') ?></div>
                        <div class="message-preview"><?= e(substr($message['message'], 0, 100)) ?>...</div>
                    </div>
                    <div style="display: flex; gap: 8px; align-items: center;">
                        <form method="POST" onclick="event.stopPropagation();" onsubmit="return confirm('Bu mesajı silmek istediğinizden emin misiniz?')">
                            <input type="hidden" name="action" value="delete">
                            <input type="hidden" name="id" value="<?= $message['id'] ?>">
                            <button type="submit" class="table-action-btn delete" title="Sil">
                                <i class="fas fa-trash"></i>
                            </button>
                        </form>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>
</div>

<!-- Message Detail Modal -->
<?php if ($viewMessage): ?>
<div class="modal-overlay active" id="messageModal">
    <div class="modal" style="max-width: 650px;">
        <div class="modal-header">
            <h3>Mesaj Detayı</h3>
            <a href="messages.php" class="modal-close">&times;</a>
        </div>
        <div class="modal-body">
            <div style="margin-bottom: 25px;">
                <div style="display: flex; gap: 15px; margin-bottom: 20px;">
                    <div class="message-avatar" style="width: 55px; height: 55px; font-size: 1.1rem;">
                        <?= strtoupper(substr($viewMessage['name'], 0, 2)) ?>
                    </div>
                    <div>
                        <h4 style="margin-bottom: 5px;"><?= e($viewMessage['name']) ?></h4>
                        <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0;">
                            <a href="mailto:<?= e($viewMessage['email']) ?>"><?= e($viewMessage['email']) ?></a>
                        </p>
                        <?php if ($viewMessage['phone']): ?>
                        <p style="color: var(--text-muted); font-size: 0.875rem; margin: 0;">
                            <a href="tel:<?= e($viewMessage['phone']) ?>"><?= e($viewMessage['phone']) ?></a>
                        </p>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if ($viewMessage['subject']): ?>
                <div style="background: var(--bg-secondary); padding: 15px; border-radius: 10px; margin-bottom: 15px;">
                    <p style="color: var(--text-muted); font-size: 0.8rem; margin-bottom: 5px;">Konu</p>
                    <p style="margin: 0; color: var(--text-primary);"><?= e($viewMessage['subject']) ?></p>
                </div>
                <?php endif; ?>

                <div style="background: var(--bg-secondary); padding: 15px; border-radius: 10px;">
                    <p style="color: var(--text-muted); font-size: 0.8rem; margin-bottom: 10px;">Mesaj</p>
                    <p style="margin: 0; color: var(--text-secondary); line-height: 1.7; white-space: pre-wrap;"><?= e($viewMessage['message']) ?></p>
                </div>

                <p style="color: var(--text-muted); font-size: 0.8rem; margin-top: 15px; text-align: right;">
                    <i class="fas fa-clock"></i>
                    <?= date('d F Y, H:i', strtotime($viewMessage['created_at'])) ?>
                </p>
            </div>
        </div>
        <div class="modal-footer">
            <form method="POST" style="display: inline;" onsubmit="return confirm('Bu mesajı silmek istediğinizden emin misiniz?')">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $viewMessage['id'] ?>">
                <button type="submit" class="btn btn-secondary">
                    <i class="fas fa-trash"></i>
                    Sil
                </button>
            </form>
            <a href="mailto:<?= e($viewMessage['email']) ?>?subject=Re: <?= e($viewMessage['subject']) ?>" class="btn btn-primary">
                <i class="fas fa-reply"></i>
                E-posta ile Yanıtla
            </a>
        </div>
    </div>
</div>
<?php endif; ?>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
