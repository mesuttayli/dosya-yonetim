<?php
/**
 * TechCode Admin - Kullanıcı Yönetimi
 */

$pageTitle = 'Kullanıcılar';
$currentPage = 'users';

require_once __DIR__ . '/includes/header.php';
requirePermission('users');

// İşlem yap
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add' || $action === 'edit') {
        $id = $_POST['id'] ?? null;
        $name = trim($_POST['name'] ?? '');
        $username = trim($_POST['username'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $role = $_POST['role'] ?? 'viewer';
        $status = $_POST['status'] ?? 'active';

        if (empty($name) || empty($username) || empty($email)) {
            setFlash('error', 'Tüm gerekli alanları doldurun.');
        } elseif ($action === 'add' && empty($password)) {
            setFlash('error', 'Şifre gereklidir.');
        } else {
            if ($action === 'add') {
                // E-posta kontrolü
                $stmt = $db->prepare("SELECT COUNT(*) FROM users WHERE email = ?");
                $stmt->execute([$email]);
                if ($stmt->fetchColumn() > 0) {
                    setFlash('error', 'Bu e-posta adresi zaten kullanılıyor.');
                } else {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("INSERT INTO users (name, username, email, password, role, status) VALUES (?, ?, ?, ?, ?, ?)");
                    $stmt->execute([$name, $username, $email, $hashedPassword, $role, $status]);
                    setFlash('success', 'Kullanıcı başarıyla eklendi.');
                }
            } else {
                if (!empty($password)) {
                    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                    $stmt = $db->prepare("UPDATE users SET name=?, username=?, email=?, password=?, role=?, status=?, updated_at=CURRENT_TIMESTAMP WHERE id=?");
                    $stmt->execute([$name, $username, $email, $hashedPassword, $role, $status, $id]);
                } else {
                    $stmt = $db->prepare("UPDATE users SET name=?, username=?, email=?, role=?, status=?, updated_at=CURRENT_TIMESTAMP WHERE id=?");
                    $stmt->execute([$name, $username, $email, $role, $status, $id]);
                }
                setFlash('success', 'Kullanıcı güncellendi.');
            }
            header('Location: users.php');
            exit;
        }
    } elseif ($action === 'delete') {
        $id = $_POST['id'] ?? null;
        if ($id && $id != $_SESSION['user_id']) {
            $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
            $stmt->execute([$id]);
            setFlash('success', 'Kullanıcı silindi.');
        } else {
            setFlash('error', 'Kendinizi silemezsiniz.');
        }
        header('Location: users.php');
        exit;
    }
}

// Kullanıcıları getir
$stmt = $db->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();

// Rol istatistikleri
$roleStats = ['admin' => 0, 'editor' => 0, 'author' => 0, 'viewer' => 0];
foreach ($users as $user) {
    if (isset($roleStats[$user['role']])) {
        $roleStats[$user['role']]++;
    }
}

// Düzenleme
$editUser = null;
if (isset($_GET['edit'])) {
    $stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$_GET['edit']]);
    $editUser = $stmt->fetch();
}

$showModal = isset($_GET['action']) && $_GET['action'] === 'new' || $editUser;
?>

<!-- Info Alert -->
<div class="alert alert-info" style="margin-bottom: 25px;">
    <i class="fas fa-info-circle"></i>
    <div class="alert-content">
        <h5>Rol Bazlı Yetkilendirme</h5>
        <p><strong>Admin:</strong> Tüm yetkiler | <strong>Editör:</strong> İçerik düzenleme | <strong>Yazar:</strong> Blog yazma | <strong>Görüntüleyici:</strong> Sadece okuma</p>
    </div>
</div>

<!-- Header Actions -->
<div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 25px;">
    <p style="color: var(--text-secondary); margin: 0;">Toplam <?= count($users) ?> kullanıcı</p>
    <button class="btn btn-primary" onclick="openModal('userModal')">
        <i class="fas fa-user-plus"></i>
        Yeni Kullanıcı Ekle
    </button>
</div>

<!-- Role Stats -->
<div class="stats-grid" style="grid-template-columns: repeat(4, 1fr); margin-bottom: 25px;">
    <div class="stat-card">
        <div class="stat-card-icon purple"><i class="fas fa-user-shield"></i></div>
        <div class="stat-card-info"><h3><?= $roleStats['admin'] ?></h3><p>Admin</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon blue"><i class="fas fa-user-edit"></i></div>
        <div class="stat-card-info"><h3><?= $roleStats['editor'] ?></h3><p>Editör</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon green"><i class="fas fa-user-pen"></i></div>
        <div class="stat-card-info"><h3><?= $roleStats['author'] ?></h3><p>Yazar</p></div>
    </div>
    <div class="stat-card">
        <div class="stat-card-icon orange"><i class="fas fa-user"></i></div>
        <div class="stat-card-info"><h3><?= $roleStats['viewer'] ?></h3><p>Görüntüleyici</p></div>
    </div>
</div>

<!-- Users Table -->
<div class="admin-card">
    <div class="admin-card-body" style="padding: 0; overflow-x: auto;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Kullanıcı</th>
                    <th>E-posta</th>
                    <th>Rol</th>
                    <th>Durum</th>
                    <th>Son Giriş</th>
                    <th>İşlemler</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div class="message-avatar" style="width: 40px; height: 40px; font-size: 0.9rem;">
                                <?= strtoupper(substr($user['name'], 0, 1)) ?>
                            </div>
                            <div>
                                <strong><?= e($user['name']) ?></strong>
                                <p style="color: var(--text-muted); font-size: 0.8rem; margin: 0;">@<?= e($user['username']) ?></p>
                            </div>
                        </div>
                    </td>
                    <td><?= e($user['email']) ?></td>
                    <td><span class="role-badge <?= $user['role'] ?>"><?= ucfirst($user['role']) ?></span></td>
                    <td><span class="status-badge <?= $user['status'] === 'active' ? 'active' : 'pending' ?>"><?= $user['status'] === 'active' ? 'Aktif' : 'Pasif' ?></span></td>
                    <td><?= $user['last_login'] ? date('d M H:i', strtotime($user['last_login'])) : '-' ?></td>
                    <td>
                        <div class="table-actions">
                            <a href="?edit=<?= $user['id'] ?>" class="table-action-btn" title="Düzenle"><i class="fas fa-edit"></i></a>
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                            <form method="POST" style="display: inline;" onsubmit="return confirm('Bu kullanıcıyı silmek istediğinizden emin misiniz?')">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?= $user['id'] ?>">
                                <button type="submit" class="table-action-btn delete" title="Sil"><i class="fas fa-trash"></i></button>
                            </form>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Add/Edit User Modal -->
<div class="modal-overlay <?= $showModal ? 'active' : '' ?>" id="userModal">
    <div class="modal" style="max-width: 500px;">
        <div class="modal-header">
            <h3><?= $editUser ? 'Kullanıcıyı Düzenle' : 'Yeni Kullanıcı Ekle' ?></h3>
            <a href="users.php" class="modal-close">&times;</a>
        </div>
        <form method="POST">
            <div class="modal-body">
                <input type="hidden" name="action" value="<?= $editUser ? 'edit' : 'add' ?>">
                <?php if ($editUser): ?>
                <input type="hidden" name="id" value="<?= $editUser['id'] ?>">
                <?php endif; ?>

                <div class="form-group">
                    <label>Ad Soyad <span class="required">*</span></label>
                    <input type="text" name="name" class="form-control" value="<?= e($editUser['name'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label>Kullanıcı Adı <span class="required">*</span></label>
                    <input type="text" name="username" class="form-control" value="<?= e($editUser['username'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label>E-posta <span class="required">*</span></label>
                    <input type="email" name="email" class="form-control" value="<?= e($editUser['email'] ?? '') ?>" required>
                </div>

                <div class="form-group">
                    <label>Şifre <?= $editUser ? '(Değiştirmek için doldurun)' : '<span class="required">*</span>' ?></label>
                    <input type="password" name="password" class="form-control" <?= $editUser ? '' : 'required' ?>>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label>Rol <span class="required">*</span></label>
                        <select name="role" class="form-control">
                            <option value="viewer" <?= ($editUser['role'] ?? '') === 'viewer' ? 'selected' : '' ?>>Görüntüleyici</option>
                            <option value="author" <?= ($editUser['role'] ?? '') === 'author' ? 'selected' : '' ?>>Yazar</option>
                            <option value="editor" <?= ($editUser['role'] ?? '') === 'editor' ? 'selected' : '' ?>>Editör</option>
                            <option value="admin" <?= ($editUser['role'] ?? '') === 'admin' ? 'selected' : '' ?>>Admin</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Durum</label>
                        <select name="status" class="form-control">
                            <option value="active" <?= ($editUser['status'] ?? 'active') === 'active' ? 'selected' : '' ?>>Aktif</option>
                            <option value="inactive" <?= ($editUser['status'] ?? '') === 'inactive' ? 'selected' : '' ?>>Pasif</option>
                        </select>
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <a href="users.php" class="btn btn-secondary">İptal</a>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Kaydet</button>
            </div>
        </form>
    </div>
</div>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
