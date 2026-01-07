<?php
/**
 * TechCode Admin - Referanslar Yönetimi
 */

$pageTitle = 'Referanslar';
$currentPage = 'testimonials';

require_once __DIR__ . '/includes/header.php';
requirePermission('testimonials');

// İşlem yap
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $id = $_POST['id'] ?? null;
        $name = trim($_POST['name'] ?? '');
        $company = trim($_POST['company'] ?? '');
        $position = trim($_POST['position'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $rating = (int)($_POST['rating'] ?? 5);
        $sort_order = (int)($_POST['sort_order'] ?? 0);
        $status = $_POST['status'] ?? 'active';

        if (empty($name) || empty($content)) {
            setFlash('error', 'Ad ve yorum içeriği gereklidir.');
        } else {
            if ($action === 'add') {
                $stmt = $db->prepare("INSERT INTO testimonials (name, company, position, content, rating, sort_order, status) VALUES (?, ?, ?, ?, ?, ?, ?)");
                $stmt->execute([$name, $company, $position, $content, $rating, $sort_order, $status]);
                setFlash('success', 'Referans eklendi.');
            } else {
                $stmt = $db->prepare("UPDATE testimonials SET name=?, company=?, position=?, content=?, rating=?, sort_order=?, status=?, updated_at=CURRENT_TIMESTAMP WHERE id=?");
                $stmt->execute([$name, $company, $position, $content, $rating, $sort_order, $status, $id]);
                setFlash('success', 'Referans güncellendi.');
            }
            header('Location: testimonials.php');
            exit;
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? null;
        if ($id) {
            $stmt = $db->prepare("DELETE FROM testimonials WHERE id = ?");
            $stmt->execute([$id]);
            setFlash('success', 'Referans silindi.');
        }
        header('Location: testimonials.php');
        exit;
    }
}

// Referansları getir
$stmt = $db->query("SELECT * FROM testimonials ORDER BY sort_order ASC, created_at DESC");
$testimonials = $stmt->fetchAll();

// Düzenleme
$editItem = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM testimonials WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editItem = $stmt->fetch();
}

$showModal = isset($_GET['action']) && $_GET['action'] === 'new' || $editItem;
?>

<!-- Header Actions -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <p style="color: var(--text-secondary); margin: 0;">Müşteri yorumlarını yönetin</p>
    <button class="btn btn-primary" onclick="openModal('testimonialModal')">
        <i class="fas fa-plus"></i>
        Yeni Referans Ekle
    </button>
</div>

<!-- Testimonials Grid -->
<div class="grid grid-3">
    <?php foreach ($testimonials as $item): ?>
    <div class="admin-card" style="position: relative;">
        <div style="position: absolute; top: 15px; right: 15px; display: flex; gap: 8px;">
            <a href="?edit=<?= $item['id'] ?>" class="table-action-btn" title="Düzenle"><i class="fas fa-edit"></i></a>
            <form method="POST" style="display: inline;" onsubmit="return confirm('Bu referansı silmek istediğinizden emin misiniz?')">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $item['id'] ?>">
                <button type="submit" class="table-action-btn delete" title="Sil"><i class="fas fa-trash"></i></button>
            </form>
        </div>
        <div class="admin-card-body" style="padding-top: 40px;">
            <div style="color: #ffc107; margin-bottom: 15px;">
                <?php for ($i = 0; $i < $item['rating']; $i++): ?>
                <i class="fas fa-star"></i>
                <?php endfor; ?>
            </div>
            <p style="font-style: italic; color: var(--text-secondary); margin-bottom: 20px; line-height: 1.7;">
                "<?= e($item['content']) ?>"
            </p>
            <div style="display: flex; align-items: center; gap: 12px;">
                <div class="message-avatar" style="width: 45px; height: 45px;">
                    <?= strtoupper(substr($item['name'], 0, 1)) ?>
                </div>
                <div>
                    <strong><?= e($item['name']) ?></strong>
                    <p style="font-size: 0.8rem; color: var(--text-muted); margin: 0;">
                        <?= e($item['position']) ?><?= $item['company'] ? ', ' . e($item['company']) : '' ?>
                    </p>
                </div>
            </div>
            <div style="margin-top: 15px;">
                <span class="status-badge <?= $item['status'] === 'active' ? 'active' : 'inactive' ?>">
                    <?= $item['status'] === 'active' ? 'Aktif' : 'Pasif' ?>
                </span>
            </div>
        </div>
    </div>
    <?php endforeach; ?>

    <?php if (empty($testimonials)): ?>
    <div class="admin-card" style="grid-column: span 3; text-align: center; padding: 50px;">
        <i class="fas fa-quote-right" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 15px;"></i>
        <p style="color: var(--text-muted);">Henüz referans eklenmemiş</p>
    </div>
    <?php endif; ?>
</div>

<!-- Add/Edit Modal -->
<div class="modal-overlay <?= $showModal ? 'active' : '' ?>" id="testimonialModal">
    <div class="modal" style="max-width: 500px;">
        <div class="modal-header">
            <h3><?= $editItem ? 'Referansı Düzenle' : 'Yeni Referans Ekle' ?></h3>
            <a href="testimonials.php" class="modal-close">&times;</a>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="action" value="<?= $editItem ? 'edit' : 'add' ?>">
                <?php if ($editItem): ?>
                <input type="hidden" name="id" value="<?= $editItem['id'] ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label>Ad Soyad <span class="required">*</span></label>
                    <input type="text" name="name" class="form-control" value="<?= e($editItem['name'] ?? '') ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Şirket</label>
                        <input type="text" name="company" class="form-control" value="<?= e($editItem['company'] ?? '') ?>">
                    </div>
                    <div class="form-group">
                        <label>Pozisyon</label>
                        <input type="text" name="position" class="form-control" value="<?= e($editItem['position'] ?? '') ?>">
                    </div>
                </div>

                <div class="form-group">
                    <label>Yorum <span class="required">*</span></label>
                    <textarea name="content" class="form-control" rows="4" required><?= e($editItem['content'] ?? '') ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Puan</label>
                        <select name="rating" class="form-control">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                            <option value="<?= $i ?>" <?= ($editItem['rating'] ?? 5) == $i ? 'selected' : '' ?>><?= $i ?> Yıldız</option>
                            <?php endfor; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Durum</label>
                        <select name="status" class="form-control">
                            <option value="active" <?= ($editItem['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Aktif</option>
                            <option value="inactive" <?= ($editItem['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Pasif</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a href="testimonials.php" class="btn btn-secondary">İptal</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Kaydet</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
