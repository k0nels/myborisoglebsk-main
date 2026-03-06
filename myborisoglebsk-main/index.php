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

require_once 'db.php';
$categories = $pdo->query("SELECT * FROM categories ORDER BY id")->fetchAll();
$newsList = $pdo->query("SELECT news.*, categories.name AS category_name FROM news JOIN categories ON news.category_id = categories.id ORDER BY news.created_at DESC LIMIT 4")->fetchAll();
$sliderAssets = $pdo->query("SELECT * FROM site_assets WHERE asset_type = 'slider' ORDER BY sort_order ASC")->fetchAll();
$heroImg = $pdo->query("SELECT image_path FROM site_assets WHERE asset_type = 'hero' LIMIT 1")->fetchColumn();
$galleryAssets = $pdo->query("SELECT * FROM site_assets WHERE asset_type = 'gallery' ORDER BY sort_order ASC LIMIT 8")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мой Борисоглебск - Главная</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.css"/>
    <link href="https://unpkg.com/aos@2.3.1/dist/aos.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body>

  <!-- HEADER -->
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
                        <div class="form-check form-switch"><input class="form-check-input" type="checkbox" role="switch" id="theme-switcher"></div>
                        <i class="bi bi-moon-fill theme-icon moon"></i>
                    </div>
                    
                    <!-- ПОИСК -->
                    <form action="search.php" method="GET" class="search-wrapper" id="search-wrapper">
                        <i class="bi bi-search search-icon"></i>
                        <span class="search-label">Поиск по сайту</span>
                        <input type="text" name="q" id="search-field" class="search-input" placeholder="Введите запрос...">
                    </form>

                    <!-- ЛОГИКА ПЕРЕКЛЮЧЕНИЯ: ВОЙТИ / ПРОФИЛЬ -->
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <div class="dropdown">
                            <a href="#" class="top-bar-link dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="bi bi-person-circle"></i> 
                                <span>Профиль</span>
                            </a>
                            <ul class="dropdown-menu dropdown-menu-dark dropdown-menu-end shadow">
                       <!-- Внутри Header, в выпадающем меню Профиль -->
<li><a class="dropdown-item" href="profile.php"><i class="bi bi-person"></i> Мой профиль</a></li>

<?php if ($_SESSION['role'] == 'admin'): ?>
    <!-- Админ видит "Админ-панель" -->
    <li><a class="dropdown-item text-warning fw-bold" href="admin_panel.php"><i class="bi bi-gear"></i> Админ-панель</a></li>
<?php elseif ($_SESSION['role'] == 'editor'): ?>
    <!-- Редактор видит "Редактирование новостей" -->
    <li><a class="dropdown-item text-info fw-bold" href="admin_panel.php"><i class="bi bi-pencil-square"></i> Редактирование новостей</a></li>
<?php endif; ?>

<li><hr class="dropdown-divider"></li>
<li><a class="dropdown-item" href="auth_handler.php?action=logout"><i class="bi bi-box-arrow-left"></i> Выйти</a></li>
                            </ul>
                        </div>
                    <?php else: ?>
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
                        <nav class="main-navigation d-none d-lg-flex ms-5">
                            <button class="main-nav-link" id="desktop-menu-toggle">
                                <div class="burger-icon"><span class="top-line"></span><span class="bottom-line"></span></div>
                                <span class="burger-text">МЕНЮ</span>
                            </button>
                            <a href="all-news.php" class="main-nav-link">ГОРОДСКИЕ НОВОСТИ</a>
                            <a href="index.php" class="main-nav-link active">АФИША</a>
                            <a href="index.php" class="main-nav-link">РОДНОЙ ГОРОД</a>
                            <a href="index.php" class="main-nav-link">СПРАВОЧНИК</a>
                            <a href="index.php" class="main-nav-link">ДОСТОПРИМЕЧАТЕЛЬНОСТИ</a>
                        </nav>
                    </div>
                    <button class="burger-menu-mobile d-lg-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileMenu">
                        <div class="burger-icon"><span class="top-line"></span><span class="bottom-line"></span></div>
                    </button>
                </div>
            </div>
        </div>

        <div class="mega-menu" id="mega-menu">
            <div class="container">
                <div class="mega-menu-grid">
                    <div class="mega-menu-column">
                        <h6 class="mega-menu-heading">Городские новости</h6>
                        <a href="all-news.php" class="mega-menu-link">Все новости</a>
                        <?php foreach($categories as $cat): ?>
                            <?php if(mb_strtolower($cat['name']) == 'все новости') continue; ?>
                            <a href="all-news.php?category=<?= $cat['id'] ?>" class="mega-menu-link"><?= htmlspecialchars($cat['name']) ?></a>
                        <?php endforeach; ?>
                    </div>
                    <div class="mega-menu-column"><h6 class="mega-menu-heading">Календарь событий</h6><a href="#" class="mega-menu-link">Кино</a><a href="#" class="mega-menu-link">Концерты</a></div>
                    <div class="mega-menu-column"><h6 class="mega-menu-heading">Родной город</h6><a href="#" class="mega-menu-link">Таланты</a></div>
                    <div class="mega-menu-column"><h6 class="mega-menu-heading">Справочник</h6><a href="#" class="mega-menu-link">Библиотеки</a><a href="#" class="mega-menu-link">Заводы</a><a href="#" class="mega-menu-link">Здоровье</a></div>
                    <div class="mega-menu-column"><h6 class="mega-menu-heading">Достопримечательности</h6><a href="#" class="mega-menu-link">Памятники</a><a href="#" class="mega-menu-link">Парки</a><a href="#" class="mega-menu-link">Соборы</a></div>
                </div>
            </div>
        </div>
    </header>

    <!-- MOBILE MENU -->
    <div class="offcanvas offcanvas-start" tabindex="-1" id="mobileMenu">
        <div class="offcanvas-header"><h5 class="offcanvas-title">Меню</h5><button type="button" class="btn-close" data-bs-dismiss="offcanvas"></button></div>
        <div class="offcanvas-body">
            <nav class="d-flex flex-column gap-3">
                <a href="all-news.php" class="mobile-nav-link active">ГОРОДСКИЕ НОВОСТИ</a><a href="#" class="mobile-nav-link">АФИША</a>
                <hr>
                <?php if (isset($_SESSION['user_id'])): ?>
                    <a href="profile.php" class="mobile-nav-link">МОЙ ПРОФИЛЬ</a>
                    <a href="auth_handler.php?action=logout" class="mobile-nav-link">ВЫЙТИ</a>
                <?php else: ?>
                    <a href="#" class="mobile-nav-link" data-bs-toggle="modal" data-bs-target="#authModal">ВОЙТИ</a>
                <?php endif; ?>
            </nav>
        </div>
    </div>
        <!-- HERO -->
        <section class="hero-section" style="background-image: url('<?= $heroImg ?>');">
            <div class="hero-shape-overlay"></div>
            <div class="container hero-content-container">
                <div class="hero-content-grid">
                    <div class="hero-title-wrapper" data-aos="fade-right"><h1 class="hero-title">ТОЛЬКО<br>ОПЕРАТИВНЫЕ<br>НОВОСТИ</h1></div>
                    <div class="hero-search-wrapper" data-aos="fade-left">
                        <p class="hero-subtitle">ПОИСК ПО САЙТУ: <span id="typed-subtitle"></span></p>
                        <form class="hero-search-form" action="search.php" method="GET">
                            <input type="text" name="q" class="form-control" placeholder="Начните поиск..." required>
                            <button type="submit" class="btn btn-primary-search"><i class="bi bi-search"></i></button>
                        </form>
                    </div>
                </div>
            </div>
        </section>

        <!-- NEWS -->
        <section id="news-section" class="news-section py-5">
            <div class="container">
                <div class="section-header d-flex justify-content-between align-items-center mb-4" data-aos="fade-up">
                    <div class="tabs-pills" id="news-tabs">
                        <a href="fetch_data.php?category=0" class="nav-link active">Все новости</a>
                        <?php foreach($categories as $cat): ?>
                            <?php if(mb_strtolower($cat['name']) == 'все новости') continue; ?>
                            <a href="fetch_data.php?category=<?= $cat['id'] ?>" class="nav-link"><?= htmlspecialchars($cat['name']) ?></a>
                        <?php endforeach; ?>
                    </div>
                    <a href="all-news.php" class="btn-link-arrow d-none d-md-inline-flex">ПОДРОБНЕЕ <i class="bi bi-arrow-right"></i></a>
                </div>
                <div class="news-grid">
                    <div class="news-main-column"><div class="row g-4" id="news-container">
                        <?php foreach($newsList as $news): ?>
                        <div class="col-md-6" data-aos="fade-up">
                            <a href="news.php?id=<?= $news['id'] ?>" class="news-card">
                             <div class="news-card-img">
    <?php 
        $img = $news['image'];
        if (empty($img)) {
            $src = "https://via.placeholder.com/600x400?text=Нет+фото";
        } elseif (strpos($img, 'http') === 0) {
            $src = $img;
        } else {
            // МЕНЯЕМ ПУТЬ ТУТ:
            $src = "img/" . $img;
        }
    ?>
    <img src="<?= $src ?>" alt="" style="width:100%; height:100%; object-fit:cover;">
</div>
                                <div class="news-card-body">
                                    <span class="news-card-category"><?= htmlspecialchars($news['category_name']) ?></span>
                                    <h5 class="news-card-title"><?= htmlspecialchars($news['title']) ?></h5>
                                    <div class="news-card-meta"><span><?= date('d.m.Y, H:i', strtotime($news['created_at'])) ?></span><span><i class="bi bi-eye"></i> <?= rand(100, 2000) ?></span></div>
                                </div>
                            </a>
                        </div>
                        <?php endforeach; ?>
                    </div></div>
                    <div class="news-ads-column">
                        <div class="ad-banner" data-aos="fade-left"><img src="img/333.png" alt=""></div>
                       
                    </div>
                </div>
            </div>
        </section>

        <!-- 5 REASONS -->
        <section class="reasons-section-v2">
            <div class="reasons-overlay"></div>
            <div class="container h-100"><div class="row h-100 align-items-center"><div class="col-lg-5"><div class="reasons-content text-white" data-aos="fade-right">
                <h2 class="reasons-title">5 весомых причин переехать в Борисоглебск</h2>
                <ul class="reasons-list list-unstyled mt-4">
                    <li><div class="reason-icon"><img src="https://mybsk.ru/wp-content/uploads/2024/03/icon-1.svg" alt=""></div><span>Исторический город</span></li>
                    <li><div class="reason-icon"><img src="https://mybsk.ru/wp-content/uploads/2024/03/icon-2.svg" alt=""></div><span>Трудоустройство</span></li>
                    <li><div class="reason-icon"><img src="https://mybsk.ru/wp-content/uploads/2024/03/icon-3.svg" alt=""></div><span>Жилье</span></li>
                    <li><div class="reason-icon"><img src="https://mybsk.ru/wp-content/uploads/2024/03/icon-4.svg" alt=""></div><span>Отдых</span></li>
                    <li><div class="reason-icon"><img src="img/puzzle.svg" alt=""></div><span>Объекты для детей</span></li>
                </ul>
                <a href="#" class="btn btn-reasons-more mt-4"><span>Подробнее</span> <i class="bi bi-arrow-right"></i></a>
            </div></div></div></div>
        </section>

        <!-- MAP -->
        <section class="interactive-map-section py-5" data-aos="fade-in">
            <div class="container text-center">
                <div class="interactive-map-header d-flex justify-content-between align-items-center">
    <!-- id="map-city-name" обязателен -->
    <h3 id="map-city-name" class="map-city-endpoint">Саратов</h3> 
    <div class="map-time-wrapper">
        <!-- id="map-travel-time" обязателен -->
        <span id="map-travel-time" class="map-travel-time-value">3 часа 15 минут</span>
        <div class="map-road-line">
            <img src="https://mybsk.ru/wp-content/themes/mybsk/assets/img/index/main-map/car.gif" alt="" class="map-car-icon">
        </div>
    </div>
    <h3 class="map-city-endpoint">Борисоглебск</h3>
</div>
                <div class="interactive-map-container"><img src="https://mybsk.ru/wp-content/themes/mybsk/assets/img/index/main-map/photo-map.webp" alt="" class="interactive-map-bg"></div>
            </div>
        </section>

<!-- ATTRACTIONS (FIXED SLIDER & TABS) -->
<section class="py-5">
    <div class="container">
        <div class="section-header d-flex justify-content-between align-items-center mb-4 flex-wrap" data-aos="fade-up">
            <div class="tabs-pills" id="att-tabs">
                <a href="fetch_data.php?att_cat=all" class="nav-link active">Все достопримечательности</a>
                <a href="fetch_data.php?att_cat=соборы" class="nav-link">Соборы</a>
                <a href="fetch_data.php?att_cat=храмы" class="nav-link">Храмы</a>
                <a href="fetch_data.php?att_cat=памятники" class="nav-link">Памятники</a>
                <a href="fetch_data.php?att_cat=площади" class="nav-link">Площади</a>
                <a href="fetch_data.php?att_cat=парки" class="nav-link">Парки</a>
            </div>
        </div>
        <div class="swiper attractions-slider" data-aos="fade-up">
            <div class="swiper-wrapper" id="slider-container">
                <?php foreach ($sliderAssets as $item): ?>
                <div class="swiper-slide">
                    <div class="attraction-card">
                        <!-- ОСТАВЛЯЕМ ТОЛЬКО ОДИН ВРАППЕР -->
                        <div class="attraction-image-wrapper">
                            <?php 
                                $path = $item['image_path'];
                                if (empty($path)) {
                                    $src = "https://via.placeholder.com/200/cccccc/ffffff?text=ФОТО";
                                } elseif (strpos($path, 'http') === 0) {
                                    $src = $path;
                                } else {
                                    $src = "img/" . $path;
                                }
                            ?>
                            <img src="<?= $src ?>" alt="<?= htmlspecialchars($item['title']) ?>" style="width:100%; height:100%; object-fit:cover;">
                        </div>
                        <div class="attraction-caption"><?= htmlspecialchars($item['title']) ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</section>
      <!-- ======================= NUMBERS SECTION ======================= -->
       <section class="numbers-section" id="numbers-section"> <!-- ID должен быть именно таким -->
    <div class="container py-5 text-center text-white">
        <h2 class="numbers-section-title mb-5" data-aos="fade-up">Борисоглебск в цифрах</h2>
        <div class="numbers-grid d-flex justify-content-around">
            <div class="numbers-item">
                <!-- Класс должен быть numbers-value, а число в data-target -->
                <span class="numbers-value display-2 fw-bold" data-target="60687">0</span>
                <p class="numbers-label">человек население</p>
            </div>
            <div class="numbers-item">
                <span class="numbers-value display-2 fw-bold" data-target="113">0</span>
                <p class="numbers-label">памятников архитектуры</p>
            </div>
            <div class="numbers-item">
                <span class="numbers-value display-2 fw-bold" data-target="25">0</span>
                <p class="numbers-label">количество ВУЗов</p>
            </div>
        </div>
    </div>
</section>

        <!-- GALLERY -->
        <section class="py-5 gallery-section">
            <div class="container">
                <div class="row g-3 gallery-grid">
                    <?php foreach ($galleryAssets as $index => $img): ?>
                        <?php if ($index == 4): ?>
                            <div class="col-12 col-md-4" data-aos="zoom-in-up"><a href="#" class="gallery-share-box"><img src="https://mybsk.ru/wp-content/themes/mybsk/assets/img/index/main-gallery/camera-icon.png" alt=""><span>Поделиться фото</span></a></div>
                        <?php endif; ?>
                        <div class="col-6 col-md-4" data-aos="zoom-in-up" data-aos-delay="<?= $index * 50 ?>"><a href="<?= htmlspecialchars($img['image_path']) ?>" data-bs-toggle="tooltip" title="Увеличить"><img loading="lazy" src="<?= htmlspecialchars($img['image_path']) ?>" alt=""></a></div>
                    <?php endforeach; ?>
                 </div>
            </div>
        </section>

      <!-- CONTACT (AJAX READY) -->
<section class="contact-form-section" data-aos="fade-up">
    <div class="container">
        <h2 class="contact-form-title text-center">Есть чем поделиться? Пишите!</h2>
        
        <!-- Контейнер для сообщения об успехе -->
        <div id="feedback-status" class="mt-3"></div> 

        <!-- ID должен быть ajax-feedback-form -->
        <form id="contact-form" class="mt-4 needs-validation" novalidate>
            <input type="hidden" name="action" value="send_feedback">
            <div class="row g-3 align-items-end">
                <div class="col-lg-3 col-md-6">
                    <label class="form-label">Ваше имя</label>
                    <input type="text" name="name" class="form-control" placeholder="Введите имя" required>
                    <div class="invalid-feedback">Введите имя</div>
                </div>
                <div class="col-lg-3 col-md-6">
                    <label class="form-label">Телефон</label>
                    <input type="tel" name="phone" class="form-control" placeholder="+7 999 999-99-99" required>
                    <div class="invalid-feedback">Введите телефон</div>
                </div>
                <div class="col-lg-4 col-md-12">
                    <label class="form-label">Сообщение</label>
                    <input type="text" name="message" class="form-control" placeholder="Ваше сообщение..." required>
                    <div class="invalid-feedback">Напишите сообщение</div>
                </div>
                <div class="col-lg-2 col-md-12">
                    <!-- Кнопка должна быть submit -->
                    <button type="submit" class="btn btn-primary-custom w-100">Отправить</button>
                </div>
            </div>
            <p class="form-text mt-3 text-center small text-muted">
                Нажимая кнопку «Отправить», вы соглашаетесь с условиями <a href="#">Политики конфиденциальности</a>
            </p>
        </form>
    </div>
</section>
    </main>


    <!-- FOOTER -->
    <footer class="site-footer">
        <div class="container">
            <div class="footer-navigation-bar">
                <a href="/" class="footer-logo"><img src="img/logo-white.svg" alt=""></a>
                <nav class="footer-nav d-none d-lg-flex">
                    <a href="#">ДОСТОПРИМЕЧАТЕЛЬНОСТИ</a><a href="#">АФИША</a><a href="#">РОДНОЙ ГОРОД</a><a href="#">ЖИЛЬЁ</a><a href="#">РАБОТА</a><a href="#">СПРАВОЧНИК</a>
                </nav>
                <div class="footer-socials"><a href="#"><i class="bi bi-telegram"></i></a><a href="#"><i class="bi bi-google"></i></a><a href="#"><i class="bi bi-people-fill"></i></a><a href="#"><i class="bi bi-link-45deg"></i></a></div>
            </div>
            <hr class="footer-divider">
            <div class="footer-main-content">
                <div class="footer-contacts-grid">
                    <div class="footer-contact-info">
                        <p><strong>Контакты редакции</strong></p>
                        <p>Телефон: <a href="tel:+79587091270">+7 958 709-12-70</a></p>
                        <p>Email: bsk@mybsk.ru</p>
                        <p>Адрес: г. Борисоглебск, ул. Матросовская д. 127</p>
                    </div>
                    <div class="footer-staff-info"><p>Гл. редактор: Хвастунов А.А.</p><p>Журналисты: Козякова Т.С., Крюкова С.Н.</p></div>
                </div>
                <div class="footer-legal-info mt-4"><p>© <?= date('Y') ?> Сетевое издание «Мой Борисоглебск». Рег. ЭЛ № ФС77 80278 от 25.01.2021.</p></div>
            </div>
        </div>
    </footer>

<div class="modal fade" id="authModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0"><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
            <div class="modal-body pt-0">
                <ul class="nav nav-pills nav-fill mb-4">
                    <li class="nav-item"><button class="nav-link active" data-bs-toggle="pill" data-bs-target="#pills-login">Войти</button></li>
                    <li class="nav-item"><button class="nav-link" data-bs-toggle="pill" data-bs-target="#pills-register">Регистрация</button></li>
                </ul>
                <div class="tab-content">
                    <!-- ВХОД: ID изменен на login-form-action -->
                    <div class="tab-pane fade show active" id="pills-login">
                        <form id="login-form-action" action="auth_handler.php" method="POST">
                            <input type="hidden" name="action" value="login">
                            <div class="mb-3"><input type="email" name="email" class="form-control" placeholder="Email" required></div>
                            <div class="mb-3"><input type="password" name="password" class="form-control" placeholder="Пароль" required></div>
                            <button type="submit" class="btn btn-primary w-100">Войти</button>
                        </form>
                    </div>
                    <!-- РЕГИСТРАЦИЯ: ID изменен на register-form-action -->
                    <div class="tab-pane fade" id="pills-register">
                        <form id="register-form-action" action="auth_handler.php" method="POST">
                            <input type="hidden" name="action" value="register">
                            <div class="mb-3"><input type="text" name="username" class="form-control" placeholder="Логин" required></div>
                            <div class="mb-3"><input type="email" name="email" class="form-control" placeholder="Email" required></div>
                            <div class="mb-3"><input type="password" name="password" class="form-control" placeholder="Пароль" required></div>
                            <button type="submit" class="btn btn-primary w-100">Создать аккаунт</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/swiper@11/swiper-bundle.min.js"></script>
    <script src="https://unpkg.com/aos@2.3.1/dist/aos.js"></script>
    <script src="https://unpkg.com/typed.js@2.1.0/dist/typed.umd.js"></script>
    <script src="script.js"></script>

   <script>
document.addEventListener('DOMContentLoaded', function() {
    // 1. Swiper (инициализация)
    let swiper = new Swiper('.attractions-slider', { 
        slidesPerView: 'auto', 
        spaceBetween: 30, 
        loop: true, 
        autoplay: { delay: 2500, disableOnInteraction: false } 
    });

    // 2. Универсальный AJAX для всех вкладок (Новости и Слайдер)
    const setupAjax = (tabsId, containerId, isSlider = false) => {
        const tabs = document.querySelectorAll(`${tabsId} .nav-link`);
        const container = document.getElementById(containerId);
        if(!tabs.length || !container) return;

        tabs.forEach(tab => {
            tab.addEventListener('click', function(e) {
                e.preventDefault();
                tabs.forEach(t => t.classList.remove('active'));
                this.classList.add('active');

                fetch(this.getAttribute('href'))
                    .then(res => res.text())
                    .then(html => {
                        if (isSlider) {
                            if (swiper) swiper.destroy();
                            container.innerHTML = html;
                            swiper = new Swiper('.attractions-slider', { slidesPerView: 'auto', spaceBetween: 30, loop: true, autoplay: { delay: 2500 } });
                        } else {
                            container.innerHTML = html;
                        }
                        if (typeof AOS !== 'undefined') AOS.refresh();
                    });
            });
        });
    };

    // Запускаем
    setupAjax('#news-tabs', 'news-container');
    setupAjax('#att-tabs', 'slider-container', true);

    // Уведомление об успешной отправке из URL (если не AJAX)
    const urlParams = new URLSearchParams(window.location.search);
    if (urlParams.has('feedback') && urlParams.get('feedback') === 'success') {
        const status = document.getElementById('feedback-status');
        if(status) status.innerHTML = '<div class="alert alert-success">Спасибо! Сообщение сохранено.</div>';
    }
});
</script>
    
</body>
</html>