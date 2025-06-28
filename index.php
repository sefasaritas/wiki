<?php
require_once 'config.php';

// Kategorileri ve makaleleri çek
$stmt = $pdo->query("
    SELECT c.*, 
           (SELECT COUNT(*) FROM articles a WHERE a.category_id = c.id AND a.is_active = 1) as article_count
    FROM categories c 
    WHERE c.is_active = 1 
    ORDER BY c.sort_order ASC, c.name ASC
");
$categories = $stmt->fetchAll();

// Her kategori için makaleleri çek
foreach ($categories as &$category) {
    $stmt = $pdo->prepare("
        SELECT id, title, slug, meta_description, view_count, is_featured
        FROM articles 
        WHERE category_id = ? AND is_active = 1 
        ORDER BY is_featured DESC, sort_order ASC, title ASC 
        LIMIT 5
    ");
    $stmt->execute([$category['id']]);
    $category['articles'] = $stmt->fetchAll();
}

// Arama işlemi
$search_results = [];
$search_term = '';
if (isset($_GET['search']) && !empty($_GET['search'])) {
    $search_term = sanitize($_GET['search']);
    
    // Arama istatistiklerine kaydet
    $stmt = $pdo->prepare("
        INSERT INTO search_stats (search_term, search_count, last_searched) 
        VALUES (?, 1, NOW()) 
        ON DUPLICATE KEY UPDATE search_count = search_count + 1, last_searched = NOW()
    ");
    $stmt->execute([$search_term]);
    
    // Arama yap
    $stmt = $pdo->prepare("
        SELECT a.*, c.name as category_name, c.color as category_color
        FROM articles a
        JOIN categories c ON a.category_id = c.id
        WHERE (a.title LIKE ? OR a.content LIKE ?) 
        AND a.is_active = 1 AND c.is_active = 1
        ORDER BY a.is_featured DESC, a.view_count DESC
        LIMIT 20
    ");
    $search_pattern = '%' . $search_term . '%';
    $stmt->execute([$search_pattern, $search_pattern]);
    $search_results = $stmt->fetchAll();
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= SITE_NAME ?></title>
    <meta name="description" content="Ticarium oyunu için kapsamlı yardım merkezi ve wiki. Tüm sorularınızın cevaplarını bulun.">
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

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Nasıl yardımcı olabiliriz?</h1>
            <div class="search-container">
                <form method="GET" action="">
                    <input type="text" 
                           name="search" 
                           class="search-box" 
                           placeholder="Sorunuzu veya konunuzu arayın..." 
                           value="<?= htmlspecialchars($search_term) ?>"
                           autocomplete="off">
                    <button type="submit" class="search-btn">Ara</button>
                </form>
            </div>
        </div>
    </section>

    <!-- Search Results -->
    <?php if (!empty($search_results)): ?>
    <section class="categories">
        <div class="container">
            <h2 class="section-title">Arama Sonuçları: "<?= htmlspecialchars($search_term) ?>"</h2>
            <div class="search-results active">
                <?php foreach ($search_results as $result): ?>
                <div class="search-result-item">
                    <h3 class="search-result-title">
                        <a href="article.php?slug=<?= $result['slug'] ?>"><?= htmlspecialchars($result['title']) ?></a>
                    </h3>
                    <p class="search-result-excerpt">
                        <?= truncateText(strip_tags($result['content']), 120) ?>
                    </p>
                    <span class="search-result-category" style="background-color: <?= $result['category_color'] ?>20; color: <?= $result['category_color'] ?>">
                        <?= htmlspecialchars($result['category_name']) ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php elseif (!empty($search_term)): ?>
    <section class="categories">
        <div class="container">
            <h2 class="section-title">Arama sonucu bulunamadı: "<?= htmlspecialchars($search_term) ?>"</h2>
            <p style="text-align: center; color: #6b7280;">Lütfen farklı anahtar kelimeler deneyin veya aşağıdaki kategorilere göz atın.</p>
        </div>
    </section>
    <?php endif; ?>

    <!-- Categories -->
    <?php if (empty($search_term) || empty($search_results)): ?>
    <section class="categories">
        <div class="container">
            <h2 class="section-title">Makaleler</h2>
            <div class="categories-grid">
                <?php foreach ($categories as $category): ?>
                <div class="category-card" style="border-left-color: <?= $category['color'] ?>">
                    <div class="category-header">
                        <div class="category-icon" style="background-color: <?= $category['color'] ?>20;">
                            <?= $category['icon'] ?>
                        </div>
                        <h3 class="category-title" style="color: <?= $category['color'] ?>">
                            <?= htmlspecialchars($category['name']) ?>
                        </h3>
                    </div>
                    
                    <?php if (!empty($category['articles'])): ?>
                    <ul class="category-articles">
                        <?php foreach ($category['articles'] as $article): ?>
                        <li>
                            <a href="article.php?slug=<?= $article['slug'] ?>">
                                <?= htmlspecialchars($article['title']) ?>
                                <?php if ($article['is_featured']): ?>
                                <span style="color: #f59e0b; margin-left: 5px;">★</span>
                                <?php endif; ?>
                            </a>
                        </li>
                        <?php endforeach; ?>
                    </ul>
                    
                    <?php if ($category['article_count'] > 5): ?>
                    <div class="view-all">
                        <a href="category.php?slug=<?= $category['slug'] ?>" class="view-all-btn">
                            Tümünü İncele (<?= $category['article_count'] ?>)
                        </a>
                    </div>
                    <?php endif; ?>
                    
                    <?php else: ?>
                    <p style="color: #9ca3af; font-style: italic;">Henüz makale eklenmemiş.</p>
                    <?php endif; ?>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </section>
    <?php endif; ?>

    <!-- Footer -->
    <footer class="footer">
        <div class="container">
            <p>&copy; 2025 Ticarium. Tüm hakları saklıdır.</p>
        </div>
    </footer>

    <!-- Admin Link -->
    <a href="admin/login.php" class="admin-link">Admin</a>

    <script>
        // Gerçek zamanlı arama önerisi (isteğe bağlı)
        document.querySelector('.search-box').addEventListener('input', function(e) {
            // Burada AJAX ile arama önerileri gösterilebilir
        });
    </script>
</body>
</html>