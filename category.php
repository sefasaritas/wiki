<?php
require_once 'config.php';

// Kategori slug'ını al
$slug = isset($_GET['slug']) ? sanitize($_GET['slug']) : '';

if (empty($slug)) {
    header('Location: index.php');
    exit;
}

// Kategoriyi çek
$stmt = $pdo->prepare("SELECT * FROM categories WHERE slug = ? AND is_active = 1");
$stmt->execute([$slug]);
$category = $stmt->fetch();

if (!$category) {
    header('HTTP/1.0 404 Not Found');
    include '404.php';
    exit;
}

// Sayfalama
$page = isset($_GET['page']) ? max(1, intval($_GET['page'])) : 1;
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Toplam makale sayısını al
$stmt = $pdo->prepare("SELECT COUNT(*) FROM articles WHERE category_id = ? AND is_active = 1");
$stmt->execute([$category['id']]);
$total_articles = $stmt->fetchColumn();
$total_pages = ceil($total_articles / $per_page);

// Makaleleri çek
$stmt = $pdo->prepare("
    SELECT id, title, slug, meta_description, view_count, is_featured, created_at, updated_at
    FROM articles 
    WHERE category_id = ? AND is_active = 1 
    ORDER BY is_featured DESC, sort_order ASC, created_at DESC 
    LIMIT ? OFFSET ?
");
$stmt->execute([$category['id'], $per_page, $offset]);
$articles = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($category['name']) ?> - <?= SITE_NAME ?></title>
    <meta name="description" content="<?= htmlspecialchars($category['description']) ?>">
    <link rel="stylesheet" href="style.css">
</head>
<body>
    <!-- Header -->
    <header class="header">
        <div class="header-container">
            <a href="index.php" class="logo">
                <span class="logo-icon">💰</span>
                <?= SITE_NAME ?>
            </a>
            <select class="language-selector">
                <option value="tr">Türkçe</option>
                <option value="en">English</option>
            </select>
        </div>
    </header>

    <!-- Content -->
    <section class="categories">
        <div class="container">
            <a href="index.php" class="back-btn">← Ana Sayfaya Dön</a>
            
            <div class="category-header" style="text-align: center; margin-bottom: 40px; padding: 40px; background: linear-gradient(135deg, <?= $category['color'] ?>, <?= $category['color'] ?>dd); border-radius: 20px; color: white;">
                <div style="font-size: 48px; margin-bottom: 15px;"><?= $category['icon'] ?></div>
                <h1 style="font-size: 36px; margin-bottom: 10px; font-weight: 300;"><?= htmlspecialchars($category['name']) ?></h1>
                <p style="font-size: 18px; opacity: 0.9;"><?= htmlspecialchars($category['description']) ?></p>
                <div style="margin-top: 15px; opacity: 0.8;">
                    <?= $total_articles ?> makale bulundu
                </div>
            </div>

            <?php if (!empty($articles)): ?>
            <div class="categories-grid">
                <?php foreach ($articles as $article): ?>
                <div class="category-card" style="border-left-color: <?= $category['color'] ?>">
                    <h3 style="margin-bottom: 15px; color: <?= $category['color'] ?>">
                        <a href="article.php?slug=<?= $article['slug'] ?>" style="color: inherit; text-decoration: none;">
                            <?= htmlspecialchars($article['title']) ?>
                            <?php if ($article['is_featured']): ?>
                            <span style="color: #f59e0b; margin-left: 8px;">⭐</span>
                            <?php endif; ?>
                        </a>
                    </h3>
                    
                    <p style="color: #6b7280; margin-bottom: 15px; line-height: 1.6;">
                        <?= truncateText($article['meta_description'] ?: '', 120) ?>
                    </p>
                    
                    <div style="display: flex; justify-content: space-between; align-items: center; font-size: 12px; color: #9ca3af;">
                        <span>👁 <?= number_format($article['view_count']) ?> görüntüleme</span>
                        <span><?= formatDate($article['updated_at']) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Sayfalama -->
            <?php if ($total_pages > 1): ?>
            <div style="text-align: center; margin-top: 40px;">
                <div style="display: inline-flex; gap: 10px; align-items: center;">
                    <?php if ($page > 1): ?>
                    <a href="?slug=<?= $category['slug'] ?>&page=<?= $page - 1 ?>" 
                       style="padding: 10px 15px; background: white; border: 1px solid #e5e7eb; border-radius: 8px; text-decoration: none; color: #374151;">
                        « Önceki
                    </a>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <a href="?slug=<?= $category['slug'] ?>&page=<?= $i ?>" 
                       style="padding: 10px 15px; background: <?= $i == $page ? $category['color'] : 'white' ?>; 
                              border: 1px solid <?= $i == $page ? $category['color'] : '#e5e7eb' ?>; 
                              border-radius: 8px; text-decoration: none; 
                              color: <?= $i == $page ? 'white' : '#374151' ?>;">
                        <?= $i ?>
                    </a>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                    <a href="?slug=<?= $category['slug'] ?>&page=<?= $page + 1 ?>" 
                       style="padding: 10px 15px; background: white; border: 1px solid #e5e7eb; border-radius: 8px; text-decoration: none; color: #374151;">
                        Sonraki »
                    </a>
                    <?php endif; ?>
                </div>
            </div>
            <?php endif; ?>

            <?php else: ?>
            <div style="text-align: center; padding: 60px 20px; color: #6b7280;">
                <div style="font-size: 48px; margin-bottom: 20px;">📝</div>
                <h3 style="margin-bottom: 10px;">Henüz makale yok</h3>
                <p>Bu kategoriye henüz makale eklenmemiş.</p>
            </div>
            <?php endif; ?>
        </div>
    </section>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Ticarium. Tüm hakları saklıdır.</p>
        </div>
    </footer>

    <!-- Admin Link -->
    <a href="admin/login.php" class="admin-link">Admin</a>
</body>
</html>