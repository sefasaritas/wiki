<header class="admin-header">
    <div class="admin-header-container">
        <div class="admin-brand">
            <a href="index.php" class="admin-logo">
                <span class="admin-logo-icon">💰</span>
                <span class="admin-logo-text"><?= SITE_NAME ?></span>
            </a>
        </div>
        
        <div class="admin-nav">
            <a href="../index.php" class="admin-nav-link" target="_blank">
                <span class="icon">🌐</span>
                <span>Siteyi Görüntüle</span>
            </a>
            
            <div class="admin-user-menu">
                <span class="admin-user-name"><?= htmlspecialchars($_SESSION['admin_username']) ?></span>
                <a href="logout.php" class="admin-logout-btn">
                    <span class="icon">🚪</span>
                    <span>Çıkış</span>
                </a>
            </div>
        </div>
    </div>
</header>