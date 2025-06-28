<?php
require_once '../config.php';
requireAdmin();

$action = $_GET['action'] ?? 'list';
$error = '';
$success = '';

// Kategorileri çek
$stmt = $pdo->query("SELECT id, name FROM categories WHERE is_active = 1 ORDER BY name");
$categories = $stmt->fetchAll();

// Makale işlemleri
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $csrf_token = $_POST['csrf_token'] ?? '';
    
    if (!hash_equals($_SESSION[CSRF_TOKEN_NAME], $csrf_token)) {
        $error = 'Güvenlik hatası. Lütfen tekrar deneyin.';
    } else {
        $title = sanitize($_POST['title'] ?? '');
        $content = $_POST['content'] ?? '';
        $meta_description = sanitize($_POST['meta_description'] ?? '');
        $category_id = intval($_POST['category_id'] ?? 0);
        $is_featured = isset($_POST['is_featured']) ? 1 : 0;
        $is_active = isset($_POST['is_active']) ? 1 : 0;
        $sort_order = intval($_POST['sort_order'] ?? 0);
        
        if (empty($title) || empty($content) || empty($category_id)) {
            $error = 'Başlık, içerik ve kategori alanları gereklidir.';
        } else {
            $slug = createSlug($title);
            
            if ($action === 'create') {
                // Slug kontrolü
                $stmt = $pdo->prepare("SELECT id FROM articles WHERE slug = ?");
                $stmt->execute([$slug]);
                if ($stmt->fetch()) {
                    $slug .= '-' . time();
                }
                
                $stmt = $pdo->prepare("
                    INSERT INTO articles (title, slug, content, meta_description, category_id, is_featured, is_active, sort_order, created_at, updated_at) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, NOW(), NOW())
                ");
                
                if ($stmt->execute([$title, $slug, $content, $meta_description, $category_id, $is_featured, $is_active, $sort_order])) {
                    $success = 'Makale başarıyla oluşturuldu.';
                    $action = 'list';
                } else {
                    $