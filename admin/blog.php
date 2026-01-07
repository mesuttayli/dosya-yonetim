<?php
/**
 * TechCode Admin - Blog Yönetimi
 */

$pageTitle = 'Blog & Duyurular';
$currentPage = 'blog';

require_once __DIR__ . '/includes/header.php';
requirePermission('blog');

// İşlem yap
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $id = $_POST['id'] ?? null;
        $title = trim($_POST['title'] ?? '');
        $excerpt = trim($_POST['excerpt'] ?? '');
        $content = trim($_POST['content'] ?? '');
        $category = $_POST['category'] ?? '';
        $tags = trim($_POST['tags'] ?? '');
        $status = $_POST['status'] ?? 'draft';

        // Slug oluştur
        $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));

        if (empty($title) || empty($content)) {
            setFlash('error', 'Başlık ve içerik gereklidir.');
        } else {
            if ($action === 'add') {
                $stmt = $db->prepare("INSERT INTO posts (title, slug, excerpt, content, category, tags, author_id, status, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $publishedAt = $status === 'published' ? date('Y-m-d H:i:s') : null;
                $stmt->execute([$title, $slug, $excerpt, $content, $category, $tags, $_SESSION['user_id'], $status, $publishedAt]);
                setFlash('success', 'Yazı eklendi.');
            } else {
                $stmt = $db->prepare("UPDATE posts SET title=?, slug=?, excerpt=?, content=?, category=?, tags=?, status=?, updated_at=CURRENT_TIMESTAMP WHERE id=?");
                $stmt->execute([$title, $slug, $excerpt, $content, $category, $tags, $status, $id]);
                setFlash('success', 'Yazı güncellendi.');
            }
            header('Location: blog.php');
            exit;
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? null;
        if ($id) {
            $stmt = $db->prepare("DELETE FROM posts WHERE id = ?");
            $stmt->execute([$id]);
            setFlash('success', 'Yazı silindi.');
        }
        header('Location: blog.php');
        exit;
    }
}

// Yazıları getir
$stmt = $db->query("SELECT p.*, u.name as author_name FROM posts p LEFT JOIN users u ON p.author_id = u.id ORDER BY p.created_at DESC");
$posts = $stmt->fetchAll();

// İstatistikler
$stmt = $db->query("SELECT COUNT(*) FROM posts");
$totalPosts = $stmt->fetchColumn();

$stmt = $db->query("SELECT COUNT(*) FROM posts WHERE status = 'published'");
$publishedPosts = $stmt->fetchColumn();

$stmt = $db->query("SELECT COUNT(*) FROM posts WHERE status = 'draft'");
$draftPosts = $stmt->fetchColumn();

// Düzenleme
$editPost = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM posts WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editPost = $stmt->fetch();
}

$showModal = isset($_GET['action']) && $_GET['action'] === 'new' || $editPost;
?>

<!-- Stats -->
<div class="stats-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 25px;">
    <div class="stat-card">
        <div class="stat-card-icon blue"><i class="fas fa-newspaper"></i></div>
        <div class="stat-card-info"><h3><?= $totalPosts ?></h3><p>Toplam Yazı</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon green"><i class="fas fa-check-circle"></i></div>
        <div class="stat-card-info"><h3><?= $publishedPosts ?></h3><p>Yayında</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon orange"><i class="fas fa-clock"></i></div>
        <div class="stat-card-info"><h3><?= $draftPosts ?></h3><p>Taslak</p></div>
    </div>
</div>

<!-- Header -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <p style="color: var(--text-secondary); margin: 0;">Blog yazılarını ve duyuruları yönetin</p>
    <button class="btn btn-primary" onclick="openModal('blogModal')">
        <i class="fas fa-plus"></i>
        Yeni Yazı Ekle
    </button>
</div>

<!-- Posts Table -->
<div class="admin-card">
    <div class="admin-card-body" style="padding: 0; overflow-x: auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Başlık</th>
                    <th>Kategori</th>
                    <th>Yazar</th>
                    <th>Durum</th>
                    <th>Tarih</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($posts)): ?>
                <tr>
                    <td colspan="6" style="text-align: center; padding: 40px; color: var(--text-muted);">
                        Henüz yazı eklenmemiş
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($posts as $post): ?>
                    <tr>
                        <td><strong><?= e($post['title']) ?></strong></td>
                        <td><span class="project-tag"><?= e($post['category'] ?: 'Genel') ?></span></td>
                        <td><?= e($post['author_name'] ?? 'Bilinmeyen') ?></td>
                        <td>
                            <span class="status-badge <?= $post['status'] === 'published' ? 'active' : 'pending' ?>">
                                <?= $post['status'] === 'published' ? 'Yayında' : 'Taslak' ?>
                            </span>
                        </td>
                        <td><?= date('d M Y', strtotime($post['created_at'])) ?></td>
                        <td>
                            <div class="table-actions">
                                <a href="?edit=<?= $post['id'] ?>" class="table-action-btn" title="Düzenle"><i class="fas fa-edit"></i></a>
                                <form method="POST" style="display: inline;" onsubmit="return confirm('Bu yazıyı silmek istediğinizden emin misiniz?')">
                                    <input type="hidden" name="action" value="delete">
                                    <input type="hidden" name="id" value="<?= $post['id'] ?>">
                                    <button type="submit" class="table-action-btn delete" title="Sil"><i class="fas fa-trash"></i></button>
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

<!-- Add/Edit Modal -->
<div class="modal-overlay <?= $showModal ? 'active' : '' ?>" id="blogModal">
    <div class="modal" style="max-width: 700px;">
        <div class="modal-header">
            <h3><?= $editPost ? 'Yazıyı Düzenle' : 'Yeni Yazı Ekle' ?></h3>
            <a href="blog.php" class="modal-close">&times;</a>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="action" value="<?= $editPost ? 'edit' : 'add' ?>">
                <?php if ($editPost): ?>
                <input type="hidden" name="id" value="<?= $editPost['id'] ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label>Başlık <span class="required">*</span></label>
                    <input type="text" name="title" class="form-control" value="<?= e($editPost['title'] ?? '') ?>" required>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Kategori</label>
                        <select name="category" class="form-control">
                            <option value="">Seçin</option>
                            <option value="Teknoloji" <?= ($editPost['category'] ?? '') === 'Teknoloji' ? 'selected' : '' ?>>Teknoloji</option>
                            <option value="Eğitim" <?= ($editPost['category'] ?? '') === 'Eğitim' ? 'selected' : '' ?>>Eğitim</option>
                            <option value="Haberler" <?= ($editPost['category'] ?? '') === 'Haberler' ? 'selected' : '' ?>>Haberler</option>
                            <option value="Duyuru" <?= ($editPost['category'] ?? '') === 'Duyuru' ? 'selected' : '' ?>>Duyuru</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Durum</label>
                        <select name="status" class="form-control">
                            <option value="draft" <?= ($editPost['status'] ?? 'draft') === 'draft' ? 'selected' : '' ?>>Taslak</option>
                            <option value="published" <?= ($editPost['status'] ?? '') === 'published' ? 'selected' : '' ?>>Yayınla</option>
                        </select>
                    </div>
                </div>

                <div class="form-group">
                    <label>Özet</label>
                    <textarea name="excerpt" class="form-control" rows="2"><?= e($editPost['excerpt'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label>İçerik <span class="required">*</span></label>
                    <textarea name="content" class="form-control" rows="10" required><?= e($editPost['content'] ?? '') ?></textarea>
                </div>

                <div class="form-group">
                    <label>Etiketler</label>
                    <input type="text" name="tags" class="form-control" placeholder="react, javascript, web" value="<?= e($editPost['tags'] ?? '') ?>">
                </div>
            </div>
            <div class="modal-footer">
                <a href="blog.php" class="btn btn-secondary">İptal</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Kaydet</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
