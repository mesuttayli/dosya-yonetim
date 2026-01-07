<?php
/**
 * TechCode Admin - Site Ayarları
 */

$pageTitle = 'Site Ayarları';
$currentPage = 'settings';

require_once __DIR__ . '/includes/header.php';
requirePermission('settings');

// Ayarları kaydet
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $settingsToUpdate = [
        'site_name', 'site_slogan', 'site_description', 'site_email', 'site_phone', 'site_address',
        'hero_title', 'hero_subtitle', 'hero_badge',
        'stat_projects', 'stat_clients', 'stat_experience',
        'social_linkedin', 'social_twitter', 'social_github', 'social_instagram',
        'meta_title', 'meta_description', 'meta_keywords', 'google_analytics',
        'primary_color', 'secondary_color'
    ];

    foreach ($settingsToUpdate as $key) {
        if (isset($_POST[$key])) {
            $value = trim($_POST[$key]);
            $stmt = $db->prepare("UPDATE settings SET setting_value = ?, updated_at = CURRENT_TIMESTAMP WHERE setting_key = ?");
            $stmt->execute([$value, $key]);
        }
    }

    setFlash('success', 'Ayarlar başarıyla kaydedildi.');
    header('Location: settings.php');
    exit;
}

// Mevcut ayarları getir
$stmt = $db->query("SELECT setting_key, setting_value FROM settings");
$settingsRaw = $stmt->fetchAll();
$settings = [];
foreach ($settingsRaw as $row) {
    $settings[$row['setting_key']] = $row['setting_value'];
}

$activeTab = $_GET['tab'] ?? 'general';
?>

<!-- Settings Tabs -->
<div style="display: flex; gap: 10px; margin-bottom: 25px; flex-wrap: wrap;">
    <a href="?tab=general" class="btn <?= $activeTab === 'general' ? 'btn-primary' : 'btn-secondary' ?> btn-sm">
        <i class="fas fa-globe"></i> Genel
    </a>
    <a href="?tab=hero" class="btn <?= $activeTab === 'hero' ? 'btn-primary' : 'btn-secondary' ?> btn-sm">
        <i class="fas fa-heading"></i> Hero & İstatistik
    </a>
    <a href="?tab=seo" class="btn <?= $activeTab === 'seo' ? 'btn-primary' : 'btn-secondary' ?> btn-sm">
        <i class="fas fa-search"></i> SEO
    </a>
    <a href="?tab=social" class="btn <?= $activeTab === 'social' ? 'btn-primary' : 'btn-secondary' ?> btn-sm">
        <i class="fas fa-share-alt"></i> Sosyal Medya
    </a>
</div>

<form method="POST">
    <!-- General Settings -->
    <?php if ($activeTab === 'general'): ?>
    <div class="admin-card">
        <div class="admin-card-header">
            <h3><i class="fas fa-globe" style="margin-right: 10px; color: var(--accent-blue);"></i> Genel Ayarlar</h3>
        </div>
        <div class="admin-card-body">
            <div class="form-row">
                <div class="form-group">
                    <label>Site Adı <span class="required">*</span></label>
                    <input type="text" name="site_name" class="form-control" value="<?= e($settings['site_name'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Site Sloganı</label>
                    <input type="text" name="site_slogan" class="form-control" value="<?= e($settings['site_slogan'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Site Açıklaması</label>
                <textarea name="site_description" class="form-control" rows="3"><?= e($settings['site_description'] ?? '') ?></textarea>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label>E-posta Adresi</label>
                    <input type="email" name="site_email" class="form-control" value="<?= e($settings['site_email'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label>Telefon</label>
                    <input type="tel" name="site_phone" class="form-control" value="<?= e($settings['site_phone'] ?? '') ?>">
                </div>
            </div>

            <div class="form-group">
                <label>Adres</label>
                <textarea name="site_address" class="form-control" rows="2"><?= e($settings['site_address'] ?? '') ?></textarea>
            </div>
        </div>
        <div class="admin-card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                Değişiklikleri Kaydet
            </button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Hero & Stats Settings -->
    <?php if ($activeTab === 'hero'): ?>
    <div class="admin-card">
        <div class="admin-card-header">
            <h3><i class="fas fa-heading" style="margin-right: 10px; color: var(--accent-blue);"></i> Hero Bölümü & İstatistikler</h3>
        </div>
        <div class="admin-card-body">
            <div class="form-group">
                <label>Hero Badge Metni</label>
                <input type="text" name="hero_badge" class="form-control" value="<?= e($settings['hero_badge'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Hero Başlık</label>
                <input type="text" name="hero_title" class="form-control" value="<?= e($settings['hero_title'] ?? '') ?>">
            </div>

            <div class="form-group">
                <label>Hero Alt Başlık</label>
                <textarea name="hero_subtitle" class="form-control" rows="2"><?= e($settings['hero_subtitle'] ?? '') ?></textarea>
            </div>

            <hr style="border-color: var(--border-color); margin: 30px 0;">

            <h4 style="margin-bottom: 20px;">İstatistikler</h4>

            <div class="form-row" style="grid-template-columns: repeat(3, 1fr);">
                <div class="form-group">
                    <label>Tamamlanan Proje</label>
                    <input type="text" name="stat_projects" class="form-control" value="<?= e($settings['stat_projects'] ?? '') ?>" placeholder="150+">
                </div>
                <div class="form-group">
                    <label>Mutlu Müşteri</label>
                    <input type="text" name="stat_clients" class="form-control" value="<?= e($settings['stat_clients'] ?? '') ?>" placeholder="50+">
                </div>
                <div class="form-group">
                    <label>Yıllık Deneyim</label>
                    <input type="text" name="stat_experience" class="form-control" value="<?= e($settings['stat_experience'] ?? '') ?>" placeholder="8+">
                </div>
            </div>
        </div>
        <div class="admin-card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                Değişiklikleri Kaydet
            </button>
        </div>
    </div>
    <?php endif; ?>

    <!-- SEO Settings -->
    <?php if ($activeTab === 'seo'): ?>
    <div class="admin-card">
        <div class="admin-card-header">
            <h3><i class="fas fa-search" style="margin-right: 10px; color: var(--accent-blue);"></i> SEO Ayarları</h3>
        </div>
        <div class="admin-card-body">
            <div class="form-group">
                <label>Meta Başlık</label>
                <input type="text" name="meta_title" class="form-control" value="<?= e($settings['meta_title'] ?? '') ?>">
                <span class="form-hint">Önerilen: 50-60 karakter</span>
            </div>

            <div class="form-group">
                <label>Meta Açıklama</label>
                <textarea name="meta_description" class="form-control" rows="3"><?= e($settings['meta_description'] ?? '') ?></textarea>
                <span class="form-hint">Önerilen: 150-160 karakter</span>
            </div>

            <div class="form-group">
                <label>Meta Anahtar Kelimeler</label>
                <input type="text" name="meta_keywords" class="form-control" value="<?= e($settings['meta_keywords'] ?? '') ?>">
                <span class="form-hint">Virgülle ayırarak yazın</span>
            </div>

            <div class="form-group">
                <label>Google Analytics ID</label>
                <input type="text" name="google_analytics" class="form-control" placeholder="G-XXXXXXXXXX" value="<?= e($settings['google_analytics'] ?? '') ?>">
            </div>
        </div>
        <div class="admin-card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                SEO Ayarlarını Kaydet
            </button>
        </div>
    </div>
    <?php endif; ?>

    <!-- Social Media Settings -->
    <?php if ($activeTab === 'social'): ?>
    <div class="admin-card">
        <div class="admin-card-header">
            <h3><i class="fas fa-share-alt" style="margin-right: 10px; color: var(--accent-blue);"></i> Sosyal Medya Ayarları</h3>
        </div>
        <div class="admin-card-body">
            <div class="form-row">
                <div class="form-group">
                    <label><i class="fab fa-linkedin" style="margin-right: 8px;"></i>LinkedIn</label>
                    <input type="url" name="social_linkedin" class="form-control" placeholder="https://linkedin.com/company/..." value="<?= e($settings['social_linkedin'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label><i class="fab fa-twitter" style="margin-right: 8px;"></i>Twitter / X</label>
                    <input type="url" name="social_twitter" class="form-control" placeholder="https://twitter.com/..." value="<?= e($settings['social_twitter'] ?? '') ?>">
                </div>
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label><i class="fab fa-github" style="margin-right: 8px;"></i>GitHub</label>
                    <input type="url" name="social_github" class="form-control" placeholder="https://github.com/..." value="<?= e($settings['social_github'] ?? '') ?>">
                </div>
                <div class="form-group">
                    <label><i class="fab fa-instagram" style="margin-right: 8px;"></i>Instagram</label>
                    <input type="url" name="social_instagram" class="form-control" placeholder="https://instagram.com/..." value="<?= e($settings['social_instagram'] ?? '') ?>">
                </div>
            </div>
        </div>
        <div class="admin-card-footer">
            <button type="submit" class="btn btn-primary">
                <i class="fas fa-save"></i>
                Sosyal Medya Ayarlarını Kaydet
            </button>
        </div>
    </div>
    <?php endif; ?>
</form>

<?php require_once __DIR__ . '/includes/footer.php'; ?>
