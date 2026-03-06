<?php
require_once 'db.php';
session_start();

// Если пользователь НЕ заблокирован, ему тут делать нечего — вернем на главную
$check = $pdo->prepare("SELECT is_blocked FROM users WHERE id = ?");
$check->execute([$_SESSION['user_id']]);
$status = $check->fetchColumn();

if ($status == 0) {
    header("Location: index.php");
    exit();
}

$categories = $pdo->query("SELECT * FROM categories ORDER BY id")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Доступ ограничен - Мой Борисоглебск</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;800&display=swap" rel="stylesheet">
</head>
<body class="bg-light">

    <!-- Упрощенный HEADER (без поиска и меню, только логотип и выход) -->
    <header class="header-custom">
        <div class="container d-flex justify-content-between align-items-center py-3">
            <a href="index.php" class="navbar-brand">
                <img src="img/logo.png" alt="Лого" height="40">
            </a>
            <a href="auth_handler.php?action=logout" class="btn btn-outline-danger btn-sm rounded-pill px-4">
                <i class="bi bi-box-arrow-left"></i> Выйти
            </a>
        </div>
    </header>

    <main class="d-flex align-items-center justify-content-center" style="min-height: 70vh;">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-6 text-center">
                    <div class="card border-0 shadow-sm rounded-5 p-5 animate__animated animate__fadeIn">
                        <div class="icon-box mb-4">
                            <i class="bi bi-person-x-fill text-danger" style="font-size: 5rem;"></i>
                        </div>
                        <h1 class="fw-bold mb-3" style="color: var(--dark-purple);">Ваш аккаунт заблокирован</h1>
                        <p class="text-muted fs-5 mb-4">
                            Доступ к функциям городского портала ограничен решением администрации. 
                            Вы больше не можете оставлять комментарии и пользоваться личным кабинетом.
                        </p>
                        <div class="alert alert-warning border-0 rounded-4 py-3">
                            <i class="bi bi-info-circle-fill me-2"></i> 
                            Если вы считаете, что это произошло по ошибке, свяжитесь с поддержкой: 
                            <a href="mailto:bsk@mybsk.ru" class="fw-bold text-dark">bsk@mybsk.ru</a>
                        </div>
                        <a href="index.php" class="btn btn-primary-custom mt-3 px-5 py-2 rounded-pill">Вернуться на главную</a>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- FOOTER -->
    <footer class="site-footer bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p class="mb-0 opacity-50 small">© <?= date('Y') ?> Сетевое издание «Мой Борисоглебск». Все права защищены.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>