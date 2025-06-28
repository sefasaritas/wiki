<?php
require_once '../config.php';
requireAdmin();

// İstatistikleri çek
$stats = [];

// Toplam kategori sayısı
$stmt = $pdo->query("SELECT COUNT(*) FROM categories WHERE is_active = 1");
$stats['categories'] = $stmt->fetchColumn();

// Toplam makale sayısı
$stmt = $pdo->query("SELECT COUNT(*) FROM articles WHERE is_active = 1");
$stats['articles'] = $stmt->fetchColumn();

// Toplam görüntülenme sayısı
$stmt = $pdo->query("SELECT SUM(view_count) FROM articles");
$stats['total_views'] = $stmt->fetchColumn() ?: 0;

// En popüler arama terimleri
$stmt = $pdo->query("SELECT search_term, search_count FROM search_stats ORDER BY search_count DESC LIMIT 5");
$popular_searches = $stmt->fetchAll();

// Son eklenen makaleler
$stmt = $pdo->query("
    SELECT a.*, c.name as category_name 
    FROM articles a 
    JOIN categories c ON a.category_id = c.id 
    ORDER BY a.created_at DESC 
    LIMIT 5
");
$recent_articles = $stmt->fetchAll();

// En popüler makaleler
$stmt = $pdo->query("
    SELECT a.*, c.name as category_name 
    FROM articles a 
    JOIN categories c ON a.category_id = c.id 
    WHERE a.is_active = 1
    ORDER BY a.view_count DESC 
    LIMIT 5
");
$popular_articles = $stmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?= SITE_NAME ?></title>
    <link rel="stylesheet" href="../style.css">
    <link rel="stylesheet" href="admin-style.css">
</head>
<body class="admin-body">
    <?php include 'includes/header.php'; ?>
    
    <div class="admin-container">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="admin-main">
            <div class="admin-header">
                <h1>Dashboard</h1>
                <p>Hoş geldiniz, <?= htmlspecialchars($_SESSION['admin_username']) ?>!</p>
            </div>
            
            <!-- İstatistikler -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">📁</div>
                    <div class="stat-content">
                        <h3><?= number_format($stats['categories']) ?></h3>
                        <p>Aktif Kategori</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">📄</div>
                    <div class="stat-content">
                        <h3><?= number_format($stats['articles']) ?></h3>
                        <p>Toplam Makale</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">👁️</div>
                    <div class="stat-content">
                        <h3><?= number_format($stats['total_views']) ?></h3>
                        <p>Toplam Görüntülenme</p>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">🔍</div>
                    <div class="stat-content">
                        <h3><?= number_format(array_sum(array_column($popular_searches, 'search_count'))) ?></h3>
                        <p>Toplam Arama</p>
                    </div>
                </div>
            </div>
            
            <!-- Ana İçerik Grid -->
            <div class="dashboard-grid">
                <!-- Son Eklenen Makaleler -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Son Eklenen Makaleler</h3>
                        <a href="articles.php" class="btn-small">Tümünü Gör</a>
                    </div>
                    <div class="card-content">
                        <?php if (!empty($recent_articles)): ?>
                        <div class="article-list">
                            <?php foreach ($recent_articles as $article): ?>
                            <div class="article-item">
                                <div class="article-info">
                                    <h4><?= htmlspecialchars($article['title']) ?></h4>
                                    <div class="article-meta">
                                        <span class="category"><?= htmlspecialchars($article['category_name']) ?></span>
                                        <span class="date"><?= formatDate($article['created_at']) ?></span>
                                    </div>
                                </div>
                                <div class="article-actions">
                                    <a href="articles.php?action=edit&id=<?= $article['id'] ?>" class="btn-edit">Düzenle</a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <p class="no-data">Henüz makale eklenmemiş.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- En Popüler Makaleler -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>En Popüler Makaleler</h3>
                    </div>
                    <div class="card-content">
                        <?php if (!empty($popular_articles)): ?>
                        <div class="article-list">
                            <?php foreach ($popular_articles as $article): ?>
                            <div class="article-item">
                                <div class="article-info">
                                    <h4><?= htmlspecialchars($article['title']) ?></h4>
                                    <div class="article-meta">
                                        <span class="category"><?= htmlspecialchars($article['category_name']) ?></span>
                                        <span class="views">👁️ <?= number_format($article['view_count']) ?></span>
                                    </div>
                                </div>
                                <div class="article-actions">
                                    <a href="articles.php?action=edit&id=<?= $article['id'] ?>" class="btn-edit">Düzenle</a>
                                </div>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        <?php else: ?>
                        <p class="no-data">Henüz makale eklenmemiş.</p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <!-- Popüler Arama Terimleri -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h3>Popüler Arama Terimleri</h3>
                    </div>
                    <div class="card-content">
                        <?php if (!empty($popular_searches)): ?>
                        <div class="search-list">
                            <?php foreach ($popular_searches as $search): ?>
                            <div class="search-item">
                                <span class="search-term"><?= htmlspecialchars($search['search_term']) ?></span>