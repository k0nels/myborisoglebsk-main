<?php
$current_page = basename($_SERVER['PHP_SELF']);
require_once 'db.php';
session_start();

if (isset($_SESSION['user_id'])) {
    $check = $pdo->prepare("SELECT is_blocked FROM users WHERE id = ?");
    $check->execute([$_SESSION['user_id']]);
    $userStatus = $check->fetch();

    // Если пользователь заблокирован и он НЕ на странице blocked.php
    if ($userStatus && $userStatus['is_blocked'] == 1 && basename($_SERVER['PHP_SELF']) != 'blocked.php') {
        header("Location: blocked.php");
        exit();
    }
}


$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    die("Новость не найдена");
}

// 1. Получаем саму новость и её категорию
$stmt = $pdo->prepare("
    SELECT news.*, categories.name AS category_name
    FROM news
    JOIN categories ON news.category_id = categories.id
    WHERE news.id = ?
");
$stmt->execute([$id]);
$news = $stmt->fetch();

if (!$news) {
    die("Новость не найдена");
}

// Находим запрос комментариев в начале news.php и меняем его:
$commentStmt = $pdo->prepare("
    SELECT comments.*, users.username 
    FROM comments 
    JOIN users ON comments.user_id = users.id 
    WHERE comments.news_id = ? AND comments.is_approved = 1 
    ORDER BY comments.created_at DESC
");
$commentStmt->execute([$id]);
$comments = $commentStmt->fetchAll();

// 3. Получаем список категорий ДЛЯ ШАПКИ И МЕНЮ (чтобы выпадающее меню работало)
$catStmt = $pdo->query("SELECT * FROM categories ORDER BY id");
$categories = $catStmt->fetchAll();
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($news['title']) ?> - Мой Борисоглебск</title>
    
    <!-- CSS (Идентично остальным страницам) -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="style.css">
</head>
<body>

    <!-- ======================= HEADER (ИДЕНТИЧНЫЙ ГЛАВНОЙ) ======================= -->
    <header class="header-custom">
        <div class="top-bar d-none d-lg-block">
            <div class="container d-flex justify-content-between align-items-center">
                <div class="d-flex gap-4">
                    <a href="#" class="top-bar-link">Работа в Борисоглебске</a>
                    <a href="#" class="top-bar-link">Жильё в Борисоглебске</a>
                    <a href="#" class="top-bar-link">Наши контакты</a>
                </div>
                <div class="d-flex gap-4 align-items-center">
                    <div class="theme-switcher-container">
                        <i class="bi bi-sun-fill theme-icon sun"></i>
                        <div class="form-check form-switch">
                            <input class="form-check-input" type="checkbox" role="switch" id="theme-switcher">
                        </div>
                        <i class="bi bi-moon-fill theme-icon moon"></i>
                    </div>
<form action="search.php" method="GET" class="search-wrapper" id="search-wrapper">
    <i class="bi bi-search search-icon"></i>
    <span class="search-label">Поиск по сайту</span>
    <input type="text" name="q" id="search-field" class="search-input" placeholder="Введите запрос...">
</form>
                  <?php if (isset($_SESSION['user_id'])): ?>
    <!-- Этот блок покажется, если пользователь АВТОРИЗОВАН -->
    <div class="dropdown">
        <a href="#" class="top-bar-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle"></i> 
            <span>Профиль</span>
        </a>
        <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow">
            <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person"></i> Мой профиль</a></li>
            
            <?php if ($_SESSION['role'] == 'admin'): ?>
                <!-- Ссылка видна только админу -->
                <li><a class="dropdown-item text-warning" href="admin_panel.php"><i class="bi bi-gear"></i> Админ-панель</a></li>
            <?php endif; ?>
            
            <li><hr class="dropdown-divider"></li>
            <li><a class="dropdown-item" href="auth_handler.php?action=logout"><i class="bi bi-box-arrow-left"></i> Выйти</a></li>
        </ul>
    </div>
<?php else: ?>
    <!-- А эта строчка (ваша оригинальная) покажется, если пользователь НЕ вошел -->
    <a href="#" class="top-bar-link" data-bs-toggle="modal" data-bs-target="#authModal">
        <i class="bi bi-box-arrow-in-right"></i> 
        <span>Войти</span>
    </a>
<?php endif; ?>
                </div>
            </div>
        </div>
        
        <div class="main-header-wrapper">
            <div class="container">
                <div class="d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center">
                        <a href="index.php" class="navbar-brand py-3">
                            <img class="logo-light" src="img/logo.png" alt="Логотип">
                            <img class="logo-dark" src="img/logo-white.svg" alt="Логотип">
                        </a>
                        <div class="main-nav-container d-none d-lg-flex">
                            <nav class="main-navigation">
                                <div class="nav-item-with-submenu">
                                    <button class="main-nav-link" id="desktop-menu-toggle">
                                        <div class="burger-icon"><span class="top-line"></span><span class="bottom-line"></span></div>
                                        <span class="burger-text">МЕНЮ</span>
                                    </button>
                                </div>
                                <a href="all-news.php" class="main-nav-link active">ГОРОДСКИЕ НОВОСТИ</a>
                                <a href="index.php" class="main-nav-link">АФИША</a>
                                <a href="index.php" class="main-nav-link">РОДНОЙ ГОРОД</a>
                                <a href="index.php" class="main-nav-link">СПРАВОЧНИК</a>
                                <a href="index.php" class="main-nav-link">ДОСТОПРИМЕЧАТЕЛЬНОСТИ</a>
                            </nav>
                        </div>
                    </div>
                    <button class="burger-menu-mobile d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu">
                        <div class="burger-icon"><span class="top-line"></span><span class="bottom-line"></span></div>
                    </button>
                </div>
            </div>
        </div>

        <!-- ВЫПАДАЮЩЕЕ МЕГА-МЕНЮ -->
        <div class="mega-menu" id="mega-menu">
            <div class="container">
                <div class="mega-menu-grid">
                    <div class="mega-menu-column">
                        <h6 class="mega-menu-heading">Городские новости</h6>
                        <a href="all-news.php" class="mega-menu-link">Все новости</a>
                        
                        <?php foreach($categories as $cat): ?>
                            <!-- УБИРАЕМ ПОВТОРЕНИЕ: если в БД категория называется "Все новости", пропускаем её -->
                            <?php if(mb_strtolower($cat['name']) == 'все новости') continue; ?>
                            
                            <a href="all-news.php?category=<?= $cat['id'] ?>" class="mega-menu-link">
                                <?= htmlspecialchars($cat['name']) ?>
                            </a>
                        <?php endforeach; ?>
                    </div>
                    <div class="mega-menu-column">
                        <h6 class="mega-menu-heading">Календарь событий</h6>
                        <a href="#" class="mega-menu-link">Кино</a><a href="#" class="mega-menu-link">Концерты</a>
                    </div>
                    <div class="mega-menu-column">
                        <h6 class="mega-menu-heading">Справочник</h6>
                        <a href="#" class="mega-menu-link">Библиотеки</a><a href="#" class="mega-menu-link">Досуг</a>
                    </div>
                    <div class="mega-menu-column">
                        <h6 class="mega-menu-heading">Достопримечательности</h6>
                        <a href="#" class="mega-menu-link">Памятники</a><a href="#" class="mega-menu-link">Парки</a>
                    </div>
                </div>
            </div>
        </div>
    </header>

    <!-- OFFCANVAS (МОБИЛЬНОЕ МЕНЮ) -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileMenu">
        <div class="offcanvas-header">
            <h5 class="offcanvas-title">Меню</h5>
            <button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button>
        </div>
        <div class="offcanvas-body">
            <nav class="d-flex flex-column gap-3">
                <a href="all-news.php" class="mobile-nav-link active">ВСЕ НОВОСТИ</a>
                <?php foreach($categories as $cat): ?>
                    <?php if(mb_strtolower($cat['name']) == 'все новости') continue; ?>
                    <a href="all-news.php?category=<?= $cat['id'] ?>" class="mobile-nav-link"><?= htmlspecialchars($cat['name']) ?></a>
                <?php endforeach; ?>
                <hr>
                <a href="#" class="mobile-nav-link" data-bs-toggle="modal" data-bs-target="#authModal">Войти</a>
            </nav>
        </div>
    </div>

    <!-- ======================= ОСНОВНОЙ КОНТЕНТ НОВОСТИ ======================= -->
    <main class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    
                    <nav class="mb-4">
                        <a href="all-news.php" class="btn-link-arrow d-inline-flex">
                            <i class="bi bi-arrow-left"></i> Назад к списку
                        </a>
                    </nav>

                    <article class="news-detail-container" data-aos="fade-up">
                        <header class="mb-4">
                            <span class="news-card-category mb-2 d-inline-block"><?= htmlspecialchars($news['category_name']) ?></span>
                            <h1 class="display-5 fw-bold mb-3"><?= htmlspecialchars($news['title']) ?></h1>
                            <div class="news-card-meta">
                                <span><i class="bi bi-calendar3"></i> <?= date('d.m.Y H:i', strtotime($news['created_at'])) ?></span>
                                <span><i class="bi bi-eye"></i> <?= rand(500, 3000) ?></span>
                            </div>
                        </header>

                  <?php if (!empty($news['image'])): ?>
    <div class="news-detail-img mb-5">
        <?php 
            $img = $news['image'];
            $src = (strpos($img, 'http') === 0) ? $img : "img/" . $img; 
        ?>
        <img src="<?= $src ?>" class="img-fluid rounded-4 w-100 shadow-sm" alt="">
    </div>
<?php endif; ?>

                        <div class="news-detail-content mb-5" style="font-size: 1.1rem; line-height: 1.8;">
                            <?= nl2br(htmlspecialchars($news['content'])) ?>
                        </div>
                    </article>

                    <hr class="footer-divider mb-5">

                    <!-- КОММЕНТАРИИ -->
                   <!-- КОММЕНТАРИИ -->
<section class="comments-section mb-5" data-aos="fade-up">
    <h3 class="fw-bold mb-4">Комментарии (<?= count($comments) ?>)</h3>

    <!-- 1. Сообщение об успешной отправке на модерацию -->
    <?php if (isset($_GET['msg']) && $_GET['msg'] == 'moderation'): ?>
        <div class="alert alert-success rounded-4 border-0 shadow-sm mb-4">
            <i class="bi bi-check-circle-fill me-2"></i> Спасибо! Ваш комментарий отправлен на модерацию и появится после проверки администратором.
        </div>
    <?php endif; ?>

    <!-- 2. Форма отправки (только для залогиненных) -->
    <?php if(isset($_SESSION['user_id'])): ?>
        <div class="mt-2 p-4 rounded-4 mb-5" style="background-color: var(--section-bg-light);">
            <h5 class="fw-bold mb-3">Оставить комментарий</h5>
            <form action="auth_handler.php" method="POST">
                <input type="hidden" name="action" value="add_comment">
                <input type="hidden" name="news_id" value="<?= $id ?>">
                <textarea name="comment_text" class="form-control rounded-3 mb-3" rows="3" placeholder="Ваше сообщение..." required></textarea>
                <button type="submit" class="btn btn-primary-custom">Отправить на модерацию</button>
            </form>
        </div>
    <?php else: ?>
        <div class="alert alert-light rounded-4 border-0 shadow-sm mb-5 text-center">
            Чтобы оставить комментарий, пожалуйста, <a href="#" data-bs-toggle="modal" data-bs-target="#authModal" class="fw-bold text-primary">войдите в аккаунт</a>.
        </div>
    <?php endif; ?>

    <!-- 3. Список самих комментариев -->
    <div class="comments-list d-flex flex-column gap-3">
        <?php foreach ($comments as $comment): ?>
            <div class="card border-0 shadow-sm rounded-4 p-2">
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <h6 class="fw-bold" style="color: var(--primary-purple);"><i class="bi bi-person-circle"></i> <?= htmlspecialchars($comment['username']) ?></h6>
                        <small class="text-muted"><?= date('d.m.Y H:i', strtotime($comment['created_at'])) ?></small>
                    </div>
                    <p class="mb-0"><?= htmlspecialchars($comment['comment_text']) ?></p>
                </div>
            </div>
        <?php endforeach; ?>
    </div>
</section>
                </div>
            </div>
        </div>
    </main>

    <!-- ======================= FOOTER (ПОЛНАЯ КОПИЯ) ======================= -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-navigation-bar">
                <a href="index.php" class="footer-logo">
                    <img src="img/logo-white.svg" alt="Логотип">
                </a>
                <nav class="footer-nav d-none d-lg-flex">
                    <a href="#">ДОСТОПРИМЕЧАТЕЛЬНОСТИ</a>
                    <a href="#">АФИША</a>
                    <a href="#">РОДНОЙ ГОРОД</a>
                    <a href="#">ЖИЛЬЁ</a>
                    <a href="#">РАБОТА</a>
                    <a href="#">СПРАВОЧНИК</a>
                </nav>
                <div class="footer-socials">
                    <a href="#" data-bs-toggle="tooltip" title="Telegram"><i class="bi bi-telegram"></i></a>
                    <a href="#" data-bs-toggle="tooltip" title="VK"><i class="bi bi-google"></i></a>
                    <a href="#" data-bs-toggle="tooltip" title="Одноклассники"><i class="bi bi-people-fill"></i></a>
                    <a href="#" data-bs-toggle="tooltip" title="Ссылка"><i class="bi bi-link-45deg"></i></a>
                </div>
            </div>
            <hr class="footer-divider">
            <div class="footer-main-content">
                <div class="footer-contacts-grid">
                    <div class="footer-contact-info">
                        <p><strong>Контакты редакции</strong></p>
                        <p>Телефон: <a href="tel:+79587091270">+7 958 709-12-70</a></p>
                        <p>Email: <a href="mailto:bsk@mybsk.ru">bsk@mybsk.ru</a></p>
                        <p>Адрес: г. Борисоглебск, ул. Матросовская д. 127</p>
                    </div>
                    <div class="footer-staff-info">
                        <p>Гл. редактор: Хвастунов А.А.</p>
                        <p>Журналисты: Козякова Т.С., Крюкова С.Н.</p>
                    </div>
                </div>
                <hr class="footer-divider d-lg-none">
                <div class="footer-legal-info">
                    <p><strong>Сетевое издание «Мой Борисоглебск»</strong></p>
                    <p>Зарегистрировано Роскомнадзором. Регистрационный номер: ЭЛ № ФС77 80278 от 25.01.2021.</p>
                </div>
            </div>
        </div>
    </footer>

   <!-- ======================= МОДАЛЬНОЕ ОКНО АВТОРИЗАЦИИ ======================= -->
<div class="modal fade" id="authModal" tabindex="-1" aria-labelledby="authModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pt-0">
                <!-- Переключатели Войти / Регистрация -->
                <ul class="nav nav-pills nav-fill mb-4" id="pills-tab" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active" id="pills-login-tab" data-bs-toggle="pill" data-bs-target="#pills-login" type="button" role="tab" aria-controls="pills-login" aria-selected="true">Войти</button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link" id="pills-register-tab" data-bs-toggle="pill" data-bs-target="#pills-register" type="button" role="tab" aria-controls="pills-register" aria-selected="false">Регистрация</button>
                    </li>
                </ul>

                <div class="tab-content" id="pills-tabContent">
                    <!-- ФОРМА ВХОДА -->
                    <div class="tab-pane fade show active" id="pills-login" role="tabpanel" aria-labelledby="pills-login-tab">
                        <form action="auth_handler.php" method="POST">
                            <input type="hidden" name="action" value="login">
                            <div class="mb-3">
                                <input type="email" name="email" class="form-control" placeholder="Email" required>
                            </div>
                            <div class="mb-3">
                                <input type="password" name="password" class="form-control" placeholder="Пароль" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Войти</button>
                        </form>
                    </div>

                    <!-- ФОРМА РЕГИСТРАЦИИ -->
                    <div class="tab-pane fade" id="pills-register" role="tabpanel" aria-labelledby="pills-register-tab">
                        <form action="auth_handler.php" method="POST">
                            <input type="hidden" name="action" value="register">
                            <div class="mb-3">
                                <input type="text" name="full_name" class="form-control" placeholder="ФИО (Полное имя)" required>
                            </div>
                            <div class="mb-3">
                                <input type="text" name="username" class="form-control" placeholder="Логин (Username)" required>
                            </div>
                            <div class="mb-3">
                                <input type="email" name="email" class="form-control" placeholder="Email" required>
                            </div>
                            <div class="mb-3">
                                <input type="password" name="password" class="form-control" placeholder="Придумайте пароль" required>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Зарегистрироваться</button>
                        </form>
                    </div>
                </div>
                
                <!-- Здесь будут выводиться ошибки, если они есть в URL -->
                <?php if(isset($_GET['error'])): ?>
                    <p class="text-danger mt-3 text-center">
                        <?php 
                            if($_GET['error'] == 'login') echo "Неверный логин или пароль";
                            if($_GET['error'] == 'exists') echo "Email или Логин уже заняты";
                            if($_GET['error'] == 'blocked') echo "Ваш аккаунт заблокирован";
                        ?>
                    </p>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
    <a href="#" class="back-to-top d-flex align-items-center justify-content-center"><i class="bi bi-arrow-up-short"></i></a>

    <!-- SCRIPTS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="script.js"></script>
    <script>AOS.init({duration: 800, once: true});</script>
</body>
</html>