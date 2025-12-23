<?php
/**
 * TechCode Admin - Hizmetler Yönetimi
 */

$pageTitle = 'Hizmetler';
$currentPage = 'services';

require_once __DIR__ . '/includes/header.php';
requirePermission('services');

// İşlem yap
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $id = $_POST['id'] ?? null;
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $icon = trim($_POST['icon'] ?? 'fa-cog');
        $icon_color = $_POST['icon_color'] ?? 'blue';
        $sort_order = (int)($_POST['sort_order'] ?? 0);
        $status = $_POST['status'] ?? 'active';

        if (empty($title)) {
            setFlash('error', 'Hizmet adı gereklidir.');
        } else {
            if ($action === 'add') {
                $stmt = $db->prepare("INSERT INTO services (title, description, icon, icon_color, sort_order, status) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $description, $icon, $icon_color, $sort_order, $status]);
                setFlash('success', 'Hizmet başarıyla eklendi.');
            } else {
                $stmt = $db->prepare("UPDATE services SET title=?, description=?, icon=?, icon_color=?, sort_order=?, status=?, updated_at=CURRENT_TIMESTAMP WHERE id=?");
                $stmt->execute([$title, $description, $icon, $icon_color, $sort_order, $status, $id]);
                setFlash('success', 'Hizmet başarıyla güncellendi.');
            }
            header('Location: services.php');
            exit;
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? null;
        if ($id) {
            $stmt = $db->prepare("DELETE FROM services WHERE id = ?");
            $stmt->execute([$id]);
            setFlash('success', 'Hizmet silindi.');
        }
        header('Location: services.php');
        exit;
    }
}

// Hizmetleri getir
$stmt = $db->query("SELECT * FROM services ORDER BY sort_order ASC, id ASC");
$services = $stmt->fetchAll();

// Düzenleme modu
$editService = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM services WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editService = $stmt->fetch();
}

$showModal = isset($_GET['action']) && $_GET['action'] === 'new' || $editService;
?>

<!-- Header Actions -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <div>
        <p style="color: var(--text-secondary); margin: 0;">Sitede gösterilen hizmetleri yönetin</p>
    </div>
    <button class="btn btn-primary" onclick="openModal('serviceModal')">
        <i class="fas fa-plus"></i>
        Yeni Hizmet Ekle
    </button>
</div>

<!-- Services Grid -->
<div class="grid grid-3">
    <?php foreach ($services as $service): ?>
    <div class="admin-card" style="position: relative;">
        <div style="position: absolute; top: 15px; right: 15px; display: flex; gap: 8px;">
            <a href="?edit=<?= $service['id'] ?>" class="table-action-btn" title="Düzenle"><i class="fas fa-edit"></i></a>
            <form method="POST" style="display: inline;" onsubmit="return confirm('Bu hizmeti silmek istediğinizden emin misiniz?')">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" value="<?= $service['id'] ?>">
                <button type="submit" class="table-action-btn delete" title="Sil"><i class="fas fa-trash"></i></button>
            </form>
        </div>
        <div class="admin-card-body" style="text-align: center; padding-top: 40px;">
            <div class="card-icon" style="margin: 0 auto 20px; background: rgba(<?= $service['icon_color'] === 'blue' ? '0, 212, 255' : ($service['icon_color'] === 'purple' ? '123, 44, 191' : ($service['icon_color'] === 'green' ? '0, 255, 136' : '255, 165, 0')) ?>, 0.1);">
                <i class="fas <?= e($service['icon']) ?>" style="color: var(--accent-<?= $service['icon_color'] ?>);"></i>
            </div>
            <h4 style="margin-bottom: 10px;"><?= e($service['title']) ?></h4>
            <p style="font-size: 0.9rem; color: var(--text-secondary);">
                <?= e($service['description']) ?>
            </p>
            <span class="status-badge <?= $service['status'] === 'active' ? 'active' : 'inactive' ?>" style="margin-top: 15px;">
                <?= $service['status'] === 'active' ? 'Aktif' : 'Pasif' ?>
            </span>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- Add/Edit Service Modal -->
<div class="modal-overlay <?= $showModal ? 'active' : '' ?>" id="serviceModal">
    <div class="modal" style="max-width: 500px;">
        <div class="modal-header">
            <h3><?= $editService ? 'Hizmeti Düzenle' : 'Yeni Hizmet Ekle' ?></h3>
            <a href="services.php" class="modal-close">&times;</a>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="action" value="<?= $editService ? 'edit' : 'add' ?>">
                <?php if ($editService): ?>
                <input type="hidden" name="id" value="<?= $editService['id'] ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label>Hizmet Adı <span class="required">*</span></label>
                    <input type="text" name="title" class="form-control" placeholder="Örn: Web Geliştirme" value="<?= e($editService['title'] ?? '') ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>İkon Sınıfı <span class="required">*</span></label>
                        <input type="text" name="icon" class="form-control" placeholder="fa-globe" value="<?= e($editService['icon'] ?? 'fa-cog') ?>">
                        <span class="form-hint">Font Awesome ikon sınıfı</span>
                    </div>
                    <div class="form-group">
                        <label>İkon Rengi</label>
                        <select name="icon_color" class="form-control">
                            <option value="blue" <?= ($editService['icon_color'] ?? '') === 'blue' ? 'selected' : '' ?>>Mavi</option>
                            <option value="purple" <?= ($editService['icon_color'] ?? '') === 'purple' ? 'selected' : '' ?>>Mor</option>
                            <option value="green" <?= ($editService['icon_color'] ?? '') === 'green' ? 'selected' : '' ?>>Yeşil</option>
                            <option value="orange" <?= ($editService['icon_color'] ?? '') === 'orange' ? 'selected' : '' ?>>Turuncu</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Açıklama <span class="required">*</span></label>
                    <textarea name="description" class="form-control" rows="4" placeholder="Hizmet açıklaması..."><?= e($editService['description'] ?? '') ?></textarea>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Sıralama</label>
                        <input type="number" name="sort_order" class="form-control" value="<?= $editService['sort_order'] ?? 0 ?>" min="0">
                    </div>
                    <div class="form-group">
                        <label>Durum</label>
                        <select name="status" class="form-control">
                            <option value="active" <?= ($editService['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Aktif</option>
                            <option value="inactive" <?= ($editService['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Pasif</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a href="services.php" class="btn btn-secondary">İptal</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Kaydet
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
