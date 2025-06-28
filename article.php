<?php
require_once 'config.php';

// Makale slug'ını al
$slug = isset($_GET['slug']) ? sanitize($_GET['slug']) : '';

if (empty($slug)) {
    header('Location: index.php');
    exit;
}

// Makaleyi ve kategori bilgisini çek
$stmt = $pdo->prepare("
    SELECT a.*, c.name as category_name, c.slug as category_slug, c.color as category_color
    FROM articles a
    JOIN categories c ON a.category_id = c.id
    WHERE a.slug = ? AND a.is_active = 1 AND c.is_active = 1
");
$stmt->execute([$slug]);
$article = $stmt->fetch();

if (!$article) {
    header('HTTP/1.0 404 Not Found');
    include '404.php';
    exit;
}

// Görüntülenme sayısını artır
$stmt = $pdo->prepare("UPDATE articles SET view_count = view_count + 1 WHERE id = ?");
$stmt->execute([$article['id']]);

// İlgili makaleler
$stmt = $pdo->prepare("
    SELECT id, title, slug, meta_description
    FROM articles 
    WHERE category_id = ? AND id != ? AND is_active = 1 
    ORDER BY view_count DESC, created_at DESC 
    LIMIT 5
");
$stmt->execute([$article['category_id'], $article['id']]);
$related_articles = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($article['title']) ?> - <?= SITE_NAME ?></title>
    <meta name="description" content="<?= htmlspecialchars($article['meta_description'] ?: truncateText(strip_tags($article['content']), 160)) ?>">
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
            
            <div class="article-container">
                <div class="article-header" style="background: linear-gradient(135deg, <?= $article['category_color'] ?>, <?= $article['category_color'] ?>dd);">
                    <div class="article-breadcrumb" style="margin-bottom: 15px; opacity: 0.9;">
                        <a href="category.php?slug=<?= $article['category_slug'] ?>" style="color: white; text-decoration: none;">
                            <?= htmlspecialchars($article['category_name']) ?>
                        </a>
                    </div>
                    <h1 class="article-title"><?= htmlspecialchars($article['title']) ?></h1>
                    <div class="article-meta">
                        <span>Son güncelleme: <?= formatDate($article['updated_at']) ?></span>
                        <span style="margin-left: 20px;">👁 <?= number_format($article['view_count']) ?> görüntülenme</span>
                        <?php if ($article['is_featured']): ?>
                        <span style="margin-left: 20px;">⭐ Öne Çıkan</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="article-content">
                    <?= nl2br(htmlspecialchars($article['content'])) ?>
                </div>
            </div>

            <!-- İlgili Makaleler -->
            <?php if (!empty($related_articles)): ?>
            <div style="margin-top: 40px;">
                <h3 style="color: #1f2937; margin-bottom: 20px; font-size: 24px;">İlgili Makaleler</h3>
                <div class="categories-grid">
                    <?php foreach ($related_articles as $related): ?>
                    <div class="category-card" style="border-left-color: <?= $article['category_color'] ?>">
                        <h4 style="margin-bottom: 10px;">
                            <a href="article.php?slug=<?= $related['slug'] ?>" style="color: #1f2937; text-decoration: none;">
                                <?= htmlspecialchars($related['title']) ?>
                            </a>
                        </h4>
                        <p style="color: #6b7280; font-size: 14px;">
                            <?= truncateText($related['meta_description'] ?: '', 100) ?>
                        </p>
                    </div>
                    <?php endforeach; ?>
                </div>
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