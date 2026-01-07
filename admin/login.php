<?php
/**
 * TechCode Admin - Giriş Sayfası
 */

require_once __DIR__ . '/../includes/config.php';
require_once __DIR__ . '/../includes/setup.php';

// Zaten giriş yapmışsa dashboard'a yönlendir
if (isLoggedIn()) {
    header('Location: dashboard.php');
    exit;
}

$error = '';

// Form gönderildi mi?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']);

    if (empty($email) || empty($password)) {
        $error = 'E-posta ve şifre gereklidir.';
    } else {
        $db = getDB();
        $stmt = $db->prepare("SELECT * FROM users WHERE email = ? AND status = 'active'");
        $stmt->execute([$email]);
        $user = $stmt->fetch();

        if ($user && password_verify($password, $user['password'])) {
            // Giriş başarılı
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['user_role'] = $user['role'];

            // Son giriş zamanını güncelle
            $stmt = $db->prepare("UPDATE users SET last_login = CURRENT_TIMESTAMP WHERE id = ?");
            $stmt->execute([$user['id']]);

            // Beni hatırla
            if ($remember) {
                setcookie('remember_email', $email, time() + (86400 * 30), '/');
            } else {
                setcookie('remember_email', '', time() - 3600, '/');
            }

            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Geçersiz e-posta veya şifre.';
        }
    }
}

$rememberedEmail = $_COOKIE['remember_email'] ?? '';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Girişi | TechCode</title>

    <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><rect fill='%230a0e17' width='100' height='100' rx='20'/><text x='50' y='65' font-family='monospace' font-size='50' fill='%2300d4ff' text-anchor='middle'>&lt;/&gt;</text></svg>">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&family=Space+Grotesk:wght@400;500;600;700&family=JetBrains+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../assets/css/main.css">
    <link rel="stylesheet" href="../assets/css/admin.css">
</head>
<body>
    <div class="login-page">
        <div class="hero-bg">
            <div class="grid-pattern"></div>
            <div class="gradient-orb gradient-orb-1"></div>
            <div class="gradient-orb gradient-orb-2"></div>
        </div>

        <div class="login-container">
            <div class="login-card">
                <a href="../index.php" class="logo">
                    <div class="logo-icon">&lt;/&gt;</div>
                    <span>TechCode</span>
                </a>
                <p class="welcome-text">Admin Paneline Hoş Geldiniz</p>

                <?php if ($error): ?>
                    <div class="alert alert-error" style="margin-bottom: 20px; text-align: left;">
                        <i class="fas fa-exclamation-circle"></i>
                        <div class="alert-content">
                            <p style="margin: 0;"><?= e($error) ?></p>
                        </div>
                    </div>
                <?php endif; ?>

                <form method="POST" class="admin-form">
                    <div class="form-group">
                        <label for="email">E-posta</label>
                        <input type="email" class="form-control" id="email" name="email"
                               value="<?= e($rememberedEmail) ?>"
                               placeholder="admin@techcode.com.tr" required>
                    </div>

                    <div class="form-group">
                        <label for="password">Şifre</label>
                        <div style="position: relative;">
                            <input type="password" class="form-control" id="password" name="password"
                                   placeholder="••••••••" required>
                            <button type="button" class="password-toggle" onclick="togglePassword()"
                                    style="position:absolute;right:15px;top:50%;transform:translateY(-50%);background:none;border:none;color:var(--text-muted);cursor:pointer;">
                                <i class="fas fa-eye" id="passwordIcon"></i>
                            </button>
                        </div>
                    </div>

                    <div class="form-group" style="display:flex;justify-content:space-between;align-items:center;">
                        <label class="form-check" style="margin:0;">
                            <input type="checkbox" name="remember" <?= $rememberedEmail ? 'checked' : '' ?>>
                            <span style="font-size:0.9rem;color:var(--text-secondary);">Beni hatırla</span>
                        </label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg">
                        <i class="fas fa-sign-in-alt"></i>
                        Giriş Yap
                    </button>
                </form>

                <div style="margin-top: 30px; padding-top: 20px; border-top: 1px solid var(--border-color);">
                    <p style="font-size: 0.8rem; color: var(--text-muted);">
                        <strong>Demo Giriş:</strong><br>
                        E-posta: admin@techcode.com.tr<br>
                        Şifre: Admin123!
                    </p>
                </div>
            </div>

            <p style="text-align:center;margin-top:20px;font-size:0.875rem;color:var(--text-muted);">
                <a href="../index.php" style="color:var(--text-secondary);">
                    <i class="fas fa-arrow-left"></i> Siteye Dön
                </a>
            </p>
        </div>
    </div>

    <script>
        function togglePassword() {
            const password = document.getElementById('password');
            const icon = document.getElementById('passwordIcon');
            if (password.type === 'password') {
                password.type = 'text';
                icon.classList.remove('fa-eye');
                icon.classList.add('fa-eye-slash');
            } else {
                password.type = 'password';
                icon.classList.remove('fa-eye-slash');
                icon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
