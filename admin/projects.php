<?php
/**
 * TechCode Admin - Projeler Yönetimi
 */

$pageTitle = 'Projeler';
$currentPage = 'projects';

require_once __DIR__ . '/includes/header.php';
requirePermission('projects');

// İşlem yap
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $id = $_POST['id'] ?? null;
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $category = $_POST['category'] ?? '';
        $technologies = trim($_POST['technologies'] ?? '');
        $project_url = trim($_POST['project_url'] ?? '');
        $status = $_POST['status'] ?? 'active';

        if (empty($title)) {
            setFlash('error', 'Proje adı gereklidir.');
        } else {
            if ($action === 'add') {
                $stmt = $db->prepare("INSERT INTO projects (title, description, category, technologies, project_url, status) VALUES (?, ?, ?, ?, ?, ?)");
                $stmt->execute([$title, $description, $category, $technologies, $project_url, $status]);
                setFlash('success', 'Proje başarıyla eklendi.');
            } else {
                $stmt = $db->prepare("UPDATE projects SET title=?, description=?, category=?, technologies=?, project_url=?, status=?, updated_at=CURRENT_TIMESTAMP WHERE id=?");
                $stmt->execute([$title, $description, $category, $technologies, $project_url, $status, $id]);
                setFlash('success', 'Proje başarıyla güncellendi.');
            }
            header('Location: projects.php');
            exit;
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? null;
        if ($id) {
            $stmt = $db->prepare("DELETE FROM projects WHERE id = ?");
            $stmt->execute([$id]);
            setFlash('success', 'Proje silindi.');
        }
        header('Location: projects.php');
        exit;
    }
}

// Projeleri getir
$stmt = $db->query("SELECT * FROM projects ORDER BY sort_order ASC, created_at DESC");
$projects = $stmt->fetchAll();

// Düzenleme modu
$editProject = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM projects WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editProject = $stmt->fetch();
}

$showModal = isset($_GET['action']) && $_GET['action'] === 'new' || $editProject;
?>

<!-- Header Actions -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <div>
        <p style="color: var(--text-secondary); margin: 0;">Toplam <?= count($projects) ?> proje bulundu</p>
    </div>
    <button class="btn btn-primary" onclick="openModal('projectModal')">
        <i class="fas fa-plus"></i>
        Yeni Proje Ekle
    </button>
</div>

<!-- Projects Table -->
<div class="admin-card">
    <div class="admin-card-body" style="padding: 0; overflow-x: auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Proje Adı</th>
                    <th>Kategori</th>
                    <th>Teknolojiler</th>
                    <th>Durum</th>
                    <th>Tarih</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($projects)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-muted);">
                        Henüz proje eklenmemiş
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($projects as $project): ?>
                    <tr>
                        <td><strong><?= e($project['title']) ?></strong></td>
                        <td><?= e($project['category']) ?></td>
                        <td>
                            <?php
                            $techs = explode(',', $project['technologies']);
                            foreach ($techs as $tech):
                                $tech = trim($tech);
                                if ($tech):
                            ?>
                                <span class="project-tag"><?= e($tech) ?></span>
                            <?php
                                endif;
                            endforeach;
                            ?>
                        </td>
                        <td>
                            <span class="status-badge <?= $project['status'] === 'active' ? 'active' : 'pending' ?>">
                                <?= $project['status'] === 'active' ? 'Aktif' : 'Beklemede' ?>
                            </span>
                        </td>
                        <td><?= date('d M Y', strtotime($project['created_at'])) ?></td>
                        <td>
                            <div class="table-actions">
                                <a href="?edit=<?= $project['id'] ?>" class="table-action-btn" title="Düzenle">
                                    <i class="fas fa-edit"></i>
                                </a>
                                <?php if ($project['project_url']): ?>
                                <a href="<?= e($project['project_url']) ?>" target="_blank" class="table-action-btn" title="Görüntüle">
                                    <i class="fas fa-external-link-alt"></i>
                                </a>
                                <?php endif; ?>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Bu projeyi silmek istediğinizden emin misiniz?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $project['id'] ?>">
                                    <button type="submit" class="table-action-btn delete" title="Sil">
                                        <i class="fas fa-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit Project Modal -->
<div class="modal-overlay <?= $showModal ? 'active' : '' ?>" id="projectModal">
    <div class="modal" style="max-width: 600px;">
        <div class="modal-header">
            <h3><?= $editProject ? 'Projeyi Düzenle' : 'Yeni Proje Ekle' ?></h3>
            <a href="projects.php" class="modal-close">&times;</a>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="action" value="<?= $editProject ? 'edit' : 'add' ?>">
                <?php if ($editProject): ?>
                <input type="hidden" name="id" value="<?= $editProject['id'] ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label>Proje Adı <span class="required">*</span></label>
                    <input type="text" name="title" class="form-control" placeholder="Proje adını girin" value="<?= e($editProject['title'] ?? '') ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Kategori <span class="required">*</span></label>
                        <select name="category" class="form-control">
                            <option value="">Seçin</option>
                            <option value="Web" <?= ($editProject['category'] ?? '') === 'Web' ? 'selected' : '' ?>>Web</option>
                            <option value="Mobil" <?= ($editProject['category'] ?? '') === 'Mobil' ? 'selected' : '' ?>>Mobil</option>
                            <option value="Kurumsal" <?= ($editProject['category'] ?? '') === 'Kurumsal' ? 'selected' : '' ?>>Kurumsal</option>
                            <option value="E-Ticaret" <?= ($editProject['category'] ?? '') === 'E-Ticaret' ? 'selected' : '' ?>>E-Ticaret</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Durum</label>
                        <select name="status" class="form-control">
                            <option value="active" <?= ($editProject['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Aktif</option>
                            <option value="pending" <?= ($editProject['status'] ?? '') === 'pending' ? 'selected' : '' ?>>Beklemede</option>
                            <option value="inactive" <?= ($editProject['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Pasif</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Teknolojiler</label>
                    <input type="text" name="technologies" class="form-control" placeholder="React, Node.js, MongoDB (virgülle ayırın)" value="<?= e($editProject['technologies'] ?? '') ?>">
                </div>

                <div class="form-group">
                    <label>Açıklama <span class="required">*</span></label>
                    <textarea name="description" class="form-control" rows="4" placeholder="Proje açıklaması..."><?= e($editProject['description'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label>Proje URL (İsteğe bağlı)</label>
                    <input type="url" name="project_url" class="form-control" placeholder="https://example.com" value="<?= e($editProject['project_url'] ?? '') ?>">
                </div>
            </div>
            <div class="modal-footer">
                <a href="projects.php" class="btn btn-secondary">İptal</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i>
                    Kaydet
                </button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
