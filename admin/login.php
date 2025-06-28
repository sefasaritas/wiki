<?php
require_once '../config.php';

// Zaten giriş yapmışsa admin panele yönlendir
if (isAdmin()) {
    header('Location: index.php');
    exit;
}

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = sanitize($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    // CSRF kontrolü
    if (!hash_equals($_SESSION[CSRF_TOKEN_NAME], $csrf_token)) {
        $error = 'Güvenlik hatası. Lütfen tekrar deneyin.';
    } elseif (empty($username) || empty($password)) {
        $error = 'Kullanıcı adı ve şifre gereklidir.';
    } else {
        // Kullanıcıyı kontrol et
        $stmt = $pdo->prepare("SELECT id, username, password FROM admins WHERE username = ?");
        $stmt->execute([$username]);
        $admin = $stmt->fetch();
        
        if ($admin && password_verify($password, $admin['password'])) {
            // Giriş başarılı
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['id'];
            $_SESSION['admin_username'] = $admin['username'];
            
            // Son giriş zamanını güncelle
            $stmt = $pdo->prepare("UPDATE admins SET last_login = NOW() WHERE id = ?");
            $stmt->execute([$admin['id']]);
            
            header('Location: index.php');
            exit;
        } else {
            $error = 'Kullanıcı adı veya şifre hatalı.';
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Girişi - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="../style.css">
    <style>
        .login-container {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .login-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 400px;
        }
        
        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .login-icon {
            font-size: 48px;
            margin-bottom: 15px;
        }
        
        .login-title {
            font-size: 28px;
            color: #1f2937;
            margin-bottom: 10px;
            font-weight: 300;
        }
        
        .login-subtitle {
            color: #6b7280;
            font-size: 14px;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            display: block;
            margin-bottom: 8px;
            color: #374151;
            font-weight: 600;
            font-size: 14px;
        }
        
        .form-input {
            width: 100%;
            padding: 12px 15px;
            border: 2px solid #e5e7eb;
            border-radius: 10px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }
        
        .form-input:focus {
            outline: none;
            border-color: #4f46e5;
        }
        
        .login-btn {
            width: 100%;
            background: linear-gradient(135deg, #4f46e5, #7c3aed);
            color: white;
            border: none;
            padding: 15px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.2s ease;
        }
        
        .login-btn:hover {
            transform: translateY(-2px);
        }
        
        .alert {
            padding: 12px 15px;
            border-radius: 8px;
            margin-bottom: 20px;
            font-size: 14px;
        }
        
        .alert-error {
            background: #fef2f2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }
        
        .alert-success {
            background: #f0fdf4;
            color: #16a34a;
            border: 1px solid #bbf7d0;
        }
        
        .back-link {
            text-align: center;
            margin-top: 20px;
        }
        
        .back-link a {
            color: #6b7280;
            text-decoration: none;
            font-size: 14px;
        }
        
        .back-link a:hover {
            color: #4f46e5;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="login-icon">🔐</div>
                <h1 class="login-title">Admin Girişi</h1>
                <p class="login-subtitle"><?= SITE_NAME ?> Yönetim Paneli</p>
            </div>
            
            <?php if ($error): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
            <?php endif; ?>
            
            <?php if ($success): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success) ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?= $_SESSION[CSRF_TOKEN_NAME] ?>">
                
                <div class="form-group">
                    <label for="username" class="form-label">Kullanıcı Adı</label>
                    <input type="text" 
                           id="username" 
                           name="username" 
                           class="form-input" 
                           value="<?= htmlspecialchars($_POST['username'] ?? '') ?>"
                           required 
                           autocomplete="username">
                </div>
                
                <div class="form-group">
                    <label for="password" class="form-label">Şifre</label>
                    <input type="password" 
                           id="password" 
                           name="password" 
                           class="form-input" 
                           required 
                           autocomplete="current-password">
                </div>
                
                <button type="submit" class="login-btn">Giriş Yap</button>
            </form>
            
            <div class="back-link">
                <a href="../index.php">← Ana sayfaya dön</a>
            </div>
        </div>
    </div>
    
    <script>
        // Form validasyonu
        document.querySelector('form').addEventListener('submit', function(e) {
            const username = document.getElementById('username').value.trim();
            const password = document.getElementById('password').value;
            
            if (!username || !password) {
                e.preventDefault();
                alert('Lütfen tüm alanları doldurun.');
            }
        });
        
        // Otomatik odaklanma
        document.getElementById('username').focus();
    </script>
</body>
</html>