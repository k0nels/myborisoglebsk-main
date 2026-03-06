<?php
require_once 'db.php';

// --- 1. ФИЛЬТРАЦИЯ НОВОСТЕЙ ---
if (isset($_GET['category'])) {
    $cat_id = (int)$_GET['category'];
    
    // Проверяем: нам нужны все новости (для all-news) или только 4 (для главной)?
    $isFullPage = isset($_GET['full']) && $_GET['full'] == '1';
    
    $limit = $isFullPage ? "" : "LIMIT 4"; 
    $colClass = $isFullPage ? "col-md-4" : "col-md-6"; 

    $query = "SELECT news.*, categories.name AS category_name FROM news JOIN categories ON news.category_id = categories.id ";
    
    if ($cat_id > 0) {
        $stmt = $pdo->prepare($query . "WHERE categories.id = ? ORDER BY news.created_at DESC $limit");
        $stmt->execute([$cat_id]);
    } else {
        $stmt = $pdo->query($query . "ORDER BY news.created_at DESC $limit");
    }
    
    $list = $stmt->fetchAll();

    if (!$list) {
        echo '<div class="col-12 text-center p-4"><p class="text-muted">Новостей в этой категории пока нет.</p></div>';
        exit;
    }

    foreach ($list as $news) {
        $img = $news['image'];
        if (empty($img)) {
            $src = "https://via.placeholder.com/600x400?text=Нет+фото";
        } elseif (strpos($img, 'http') === 0) {
            $src = $img;
        } else {
            $src = "img/" . $img;
        }
        
        echo '
        <div class="'.$colClass.' mb-4" data-aos="fade-up">
            <a href="news.php?id='.$news['id'].'" class="news-card h-100">
                <div class="news-card-img"><img src="'.$src.'" alt="" style="width:100%; height:100%; object-fit:cover;"></div>
                <div class="news-card-body">
                    <span class="news-card-category">'.htmlspecialchars($news['category_name']).'</span>
                    <h5 class="news-card-title">'.htmlspecialchars($news['title']).'</h5>
                    <div class="news-card-meta">
                        <span>'.date('d.m.Y', strtotime($news['created_at'])).'</span>
                    </div>
                </div>
            </a>
        </div>';
    }
    exit;
}

// --- 2. ФИЛЬТРАЦИЯ СЛАЙДЕРА (ДОСТОПРИМЕЧАТЕЛЬНОСТИ) ---
if (isset($_GET['att_cat'])) {
    $cat = $_GET['att_cat'];
    
    $query = "SELECT * FROM site_assets WHERE asset_type = 'slider' ";
    
    if ($cat !== 'all') {
        $stmt = $pdo->prepare($query . "AND category = ? ORDER BY sort_order ASC");
        $stmt->execute([$cat]);
    } else {
        $stmt = $pdo->query($query . "ORDER BY sort_order ASC");
    }
    
    $assets = $stmt->fetchAll();

    if (!$assets) {
        echo '<div class="swiper-slide text-center p-5"><p class="text-muted">В этой категории объектов пока нет.</p></div>';
        exit;
    }

    foreach ($assets as $item) {
        $path = $item['image_path'];
        // Логика пути: если пусто — заглушка, если ссылка — как есть, если файл — папка img/
        if (empty($path)) {
            $src = "https://via.placeholder.com/200/cccccc/ffffff?text=ФОТО";
        } elseif (strpos($path, 'http') === 0) {
            $src = $path;
        } else {
            $src = "img/" . $path;
        }
        
        echo '
        <div class="swiper-slide">
            <div class="attraction-card">
                <div class="attraction-image-wrapper">
                    <img src="'.$src.'" alt="" style="width:100%; height:100%; object-fit:cover;">
                </div>
                <div class="attraction-caption">'.htmlspecialchars($item['title']).'</div>
            </div>
        </div>';
    }
    exit;
}