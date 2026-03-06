<?php
require_once 'db.php';
session_start();
if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }

$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$_SESSION['user_id']]);
$user = $stmt->fetch();

// Категории для шапки
$categories = $pdo->query("SELECT * FROM categories ORDER BY id")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Мой профиль - Мой Борисоглебск</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
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
                                <li><a class="dropdown-item" href="profile.php"><i class="bi bi-person"></i> Мой профиль</a></li>
                                
                                <?php if (isset($_SESSION['role']) && $_SESSION['role'] == 'admin'): ?>
                                    <li><a class="dropdown-item text-warning fw-bold" href="admin_panel.php"><i class="bi bi-gear"></i> Админ-панель</a></li>
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
                            <a href="#" class="main-nav-link active">АФИША</a>
                            <a href="#" class="main-nav-link">РОДНОЙ ГОРОД</a>
                            <a href="#" class="main-nav-link">СПРАВОЧНИК</a>
                            <a href="#" class="main-nav-link">ДОСТОПРИМЕЧАТЕЛЬНОСТИ</a>
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

    <main class="py-5">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6">
                    <div class="card shadow-sm border-0 rounded-4 p-4">
                        <div class="card-body text-center">
                            <i class="bi bi-person-circle display-1 text-primary mb-3"></i>
                            <h2 class="fw-bold mb-1"><?= htmlspecialchars($user['full_name'] ?? 'Пользователь') ?></h2>
                            <p class="text-muted">@<?= htmlspecialchars($user['username']) ?></p>
                            <hr class="my-4">
                            <div class="text-start">
                                <p><strong>Email:</strong> <?= htmlspecialchars($user['email']) ?></p>
                                <p><strong>Роль:</strong> <?= ($user['role'] == 'admin') ? 'Администратор' : 'Читатель' ?></p>
                                <p><strong>Дата регистрации:</strong> <?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></p>
                            </div>
                            <a href="auth_handler.php?action=logout" class="btn btn-outline-danger w-100 mt-4">Выйти из аккаунта</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="script.js"></script>
</body>
</html>