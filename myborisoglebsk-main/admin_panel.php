<?php
require_once 'db.php';
session_start();

// ИСПРАВЛЕНО: Теперь пускаем и админа, и редактора
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'editor')) {
    header("Location: index.php");
    exit;
}

$role = $_SESSION['role'];

// --- ОБРАБОТКА ДЕЙСТВИЙ ---
// Новости могут удалять и те, и другие
if (isset($_GET['del_news'])) {
    $pdo->prepare("DELETE FROM news WHERE id = ?")->execute([$_GET['del_news']]);
    header("Location: admin_panel.php#nav-news"); exit;
}

// А эти действия — ТОЛЬКО для админа
if ($role === 'admin') {
    if (isset($_GET['approve_comm'])) {
        $pdo->prepare("UPDATE comments SET is_approved = 1 WHERE id = ?")->execute([$_GET['approve_comm']]);
        header("Location: admin_panel.php#nav-comments"); exit;
    }
    if (isset($_GET['del_comm'])) {
        $pdo->prepare("DELETE FROM comments WHERE id = ?")->execute([$_GET['del_comm']]);
        header("Location: admin_panel.php#nav-comments"); exit;
    }
    if (isset($_GET['toggle_block'])) {
        $pdo->prepare("UPDATE users SET is_blocked = NOT is_blocked WHERE id = ?")->execute([$_GET['toggle_block']]);
        header("Location: admin_panel.php#nav-users"); exit;
    }
    if (isset($_GET['del_feedback'])) {
        $pdo->prepare("DELETE FROM feedback WHERE id = ?")->execute([$_GET['del_feedback']]);
        header("Location: admin_panel.php#nav-feedback"); exit;
    }
}

// --- ЗАГРУЗКА ДАННЫХ ---
$news = $pdo->query("SELECT news.*, categories.name as cat_name FROM news JOIN categories ON news.category_id = categories.id ORDER BY created_at DESC")->fetchAll();

// Загружаем остальное только если зашел АДМИН
$pendingComments = [];
$approvedComments = [];
$users = [];
$feedbackList = [];

if ($role === 'admin') {
    $pendingComments = $pdo->query("SELECT comments.*, users.username, news.title as news_title FROM comments JOIN users ON comments.user_id = users.id JOIN news ON comments.news_id = news.id WHERE is_approved = 0 ORDER BY created_at DESC")->fetchAll();
    $approvedComments = $pdo->query("SELECT comments.*, users.username, news.title as news_title FROM comments JOIN users ON comments.user_id = users.id JOIN news ON comments.news_id = news.id WHERE is_approved = 1 ORDER BY created_at DESC LIMIT 50")->fetchAll();
    $users = $pdo->query("SELECT * FROM users ORDER BY created_at DESC")->fetchAll();
    $feedbackList = $pdo->query("SELECT * FROM feedback ORDER BY created_at DESC")->fetchAll();
}
$categories = $pdo->query("SELECT * FROM categories ORDER BY id")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Админ-центр - Мой Борисоглебск</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;600;700;800&display=swap" rel="stylesheet">
    <style>
        body { background-color: var(--section-bg-light); font-family: 'Manrope', sans-serif; }
        .admin-card { border: none; border-radius: 20px; box-shadow: var(--shadow-sm); background: #fff; }
        .nav-pills .nav-link { color: var(--text-color); font-weight: 600; border-radius: 12px; padding: 12px 20px; transition: all 0.3s; border: 1px solid transparent; }
        .nav-pills .nav-link.active { background-color: var(--dark-purple); color: #fff; }
        .nav-pills .nav-link:hover:not(.active) { border-color: var(--border-color); background: #fff; }
        .table thead th { background-color: var(--dark-purple); color: #fff; border: none; padding: 15px; }
        .table tbody td { padding: 15px; vertical-align: middle; border-bottom: 1px solid var(--border-color); }
        .btn-action { width: 35px; height: 35px; display: inline-flex; align-items: center; justify-content: center; border-radius: 10px; transition: 0.3s; }
        .badge-role { padding: 6px 12px; border-radius: 8px; font-size: 11px; text-transform: uppercase; letter-spacing: 0.5px; }
    </style>
</head>
<body>

    <!-- Упрощенная шапка для админки -->
    <header class="header-custom shadow-sm bg-white mb-5">
        <div class="container d-flex justify-content-between align-items-center py-3">
            <a href="index.php" class="navbar-brand">
                <img src="img/logo.png" alt="Логотип" height="40">
            </a>
            <div class="d-flex align-items-center gap-3">
                <span class="fw-bold text-muted small">Администратор: <?= htmlspecialchars($_SESSION['username'] ?? 'Admin') ?></span>
                <a href="index.php" class="btn btn-primary-custom btn-sm rounded-pill px-4">На сайт</a>
            </div>
        </div>
    </header>

    <div class="container pb-5">
        <h2 class="fw-extrabold mb-4" style="color: var(--dark-purple);">Панель управления</h2>

        <!-- Навигация -->
       <div class="admin-card p-2 mb-4">
    <ul class="nav nav-pills nav-fill" id="adminTabs" role="tablist">
        <!-- Эта вкладка видна ВСЕМ (Админу и Редактору) -->
        <li class="nav-item">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#nav-news" type="button">
                <i class="bi bi-newspaper me-2"></i>Новости
            </button>
        </li>

        <?php if ($role === 'admin'): ?>
            <!-- Эти три вкладки увидит ТОЛЬКО Администратор -->
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#nav-comments" type="button">
                    <i class="bi bi-chat-left-dots me-2"></i>Комментарии 
                    <?php if(count($pendingComments) > 0): ?><span class="badge bg-danger ms-1"><?= count($pendingComments) ?></span><?php endif; ?>
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#nav-users" type="button">
                    <i class="bi bi-people me-2"></i>Пользователи
                </button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#nav-feedback" type="button">
                    <i class="bi bi-envelope me-2"></i>Обращения
                    <?php if(count($feedbackList) > 0): ?><span class="badge bg-primary ms-1"><?= count($feedbackList) ?></span><?php endif; ?>
                </button>
            </li>
        <?php endif; ?>
    </ul>
</div>
        <div class="tab-content">
            
            <!-- ВКЛАДКА: НОВОСТИ -->
            <div class="tab-pane fade show active" id="nav-news" role="tabpanel">
                <div class="d-flex justify-content-between align-items-center mb-4">
                    <h4 class="fw-bold m-0">Список новостей</h4>
                    <a href="add_news.php" class="btn btn-success rounded-pill px-4 shadow-sm">
                        <i class="bi bi-plus-lg me-2"></i>Добавить новость
                    </a>
                </div>
                <div class="admin-card overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Заголовок</th>
                                    <th>Категория</th>
                                    <th class="text-end">Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($news as $n): ?>
                                <tr>
                                    <td class="text-muted">#<?= $n['id'] ?></td>
                                    <td class="fw-bold"><?= mb_strimwidth($n['title'], 0, 60, "...") ?></td>
                                    <td><span class="badge bg-light text-dark border"><?= $n['cat_name'] ?></span></td>
                                    <td class="text-end">
                                        <a href="edit_news.php?id=<?= $n['id'] ?>" class="btn-action btn btn-warning me-1" title="Редактировать"><i class="bi bi-pencil"></i></a>
                                        <a href="?del_news=<?= $n['id'] ?>" class="btn-action btn btn-danger" onclick="return confirm('Удалить новость?')" title="Удалить"><i class="bi bi-trash"></i></a>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- ВКЛАДКА: КОММЕНТАРИИ -->
            <div class="tab-pane fade" id="nav-comments" role="tabpanel">
                <h4 class="fw-bold mb-4">Модерация комментариев</h4>
                
                <h6 class="text-danger fw-bold mb-3"><i class="bi bi-clock-history me-2"></i>Новые (ожидают одобрения)</h6>
                <?php if(empty($pendingComments)) echo '<p class="text-muted small">Новых комментариев нет.</p>'; ?>
                <?php foreach($pendingComments as $c): ?>
                    <div class="admin-card p-3 mb-3 border-start border-danger border-4">
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <span class="fw-bold text-primary"><?= htmlspecialchars($c['username']) ?></span> 
                                <span class="text-muted small ms-2">к новости: <?= htmlspecialchars($c['news_title']) ?></span>
                                <p class="mb-0 mt-2 text-dark"><?= htmlspecialchars($c['comment_text']) ?></p>
                            </div>
                            <div class="d-flex gap-2">
                                <a href="?approve_comm=<?= $c['id'] ?>" class="btn btn-success btn-sm rounded-pill px-3">Одобрить</a>
                                <a href="?del_comm=<?= $c['id'] ?>" class="btn btn-outline-danger btn-sm rounded-pill px-3">Удалить</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>

                <h6 class="text-success fw-bold mt-5 mb-3"><i class="bi bi-check-all me-2"></i>Опубликованные (последние 50)</h6>
                <div class="admin-card overflow-hidden">
                    <table class="table table-sm mb-0">
                        <tbody>
                            <?php foreach($approvedComments as $c): ?>
                            <tr>
                                <td class="ps-3 py-3">
                                    <span class="fw-bold small"><?= htmlspecialchars($c['username']) ?>:</span> 
                                    <span class="text-muted small"><?= mb_strimwidth($c['comment_text'], 0, 100, "...") ?></span>
                                </td>
                                <td class="text-end pe-3">
                                    <a href="?del_comm=<?= $c['id'] ?>" class="text-danger" onclick="return confirm('Удалить?')"><i class="bi bi-x-square"></i></a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- ВКЛАДКА: ОБРАЩЕНИЯ -->
            <div class="tab-pane fade" id="nav-feedback" role="tabpanel">
                <h4 class="fw-bold mb-4">Сообщения от пользователей</h4>
                <div class="row">
                    <?php if(empty($feedbackList)) echo '<div class="col-12 text-center py-5 admin-card"><p class="text-muted">Сообщений пока нет.</p></div>'; ?>
                    <?php foreach($feedbackList as $f): ?>
                        <div class="col-12 mb-3">
                            <div class="admin-card p-4 border-start border-primary border-4">
                                <div class="d-flex justify-content-between align-items-start">
                                    <div>
                                        <div class="d-flex align-items-center mb-2">
                                            <h6 class="fw-bold mb-0 text-primary"><?= htmlspecialchars($f['name']) ?></h6>
                                            <span class="ms-3 badge bg-light text-dark border small"><i class="bi bi-telephone me-1"></i> <?= htmlspecialchars($f['phone']) ?></span>
                                        </div>
                                        <p class="mb-2 text-dark" style="line-height: 1.6;"><?= nl2br(htmlspecialchars($f['message'])) ?></p>
                                        <small class="text-muted opacity-75"><?= date('d.m.Y H:i', strtotime($f['created_at'])) ?></small>
                                    </div>
                                    <a href="?del_feedback=<?= $f['id'] ?>" class="btn btn-outline-danger btn-sm rounded-pill" onclick="return confirm('Удалить обращение?')">
                                        <i class="bi bi-trash"></i>
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <!-- ВКЛАДКА: ПОЛЬЗОВАТЕЛИ -->
            <div class="tab-pane fade" id="nav-users" role="tabpanel">
                <h4 class="fw-bold mb-4">Управление пользователями</h4>
                <div class="admin-card overflow-hidden">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead>
                                <tr>
                                    <th>Пользователь</th>
                                    <th>Роль</th>
                                    <th>Статус</th>
                                    <th class="text-end">Управление</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($users as $u): ?>
                                <tr>
                                    <td>
                                        <div class="fw-bold"><?= htmlspecialchars($u['username']) ?></div>
                                        <div class="small text-muted"><?= htmlspecialchars($u['email']) ?></div>
                                    </td>
                                    <td>
                                        <?php 
                                        if ($u['role'] == 'admin') echo '<span class="badge-role bg-warning text-dark">Админ</span>';
                                        elseif ($u['role'] == 'editor') echo '<span class="badge-role bg-info text-dark">Редактор</span>';
                                        else echo '<span class="badge-role bg-secondary text-white">Юзер</span>';
                                        ?>
                                    </td>
                                    <td>
                                        <?= $u['is_blocked'] ? '<span class="text-danger small fw-bold"><i class="bi bi-x-circle me-1"></i>Забанен</span>' : '<span class="text-success small fw-bold"><i class="bi bi-check-circle me-1"></i>Активен</span>' ?>
                                    </td>
                                    <td class="text-end">
                                        <div class="btn-group shadow-sm rounded-3 overflow-hidden">
                                            <a href="user_details.php?id=<?= $u['id'] ?>" class="btn btn-light btn-sm" title="История"><i class="bi bi-chat-dots"></i></a>
                                            <a href="edit_user.php?id=<?= $u['id'] ?>" class="btn btn-light btn-sm" title="Настройки"><i class="bi bi-person-gear"></i></a>
                                            <?php if ($u['id'] != $_SESSION['user_id']): ?>
                                                <a href="?toggle_block=<?= $u['id'] ?>" class="btn <?= $u['is_blocked'] ? 'btn-success' : 'btn-danger' ?> btn-sm text-white">
                                                    <?= $u['is_blocked'] ? 'ВКЛ' : 'БАН' ?>
                                                </a>
                                            <?php endif; ?>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Скрипт для автоматического переключения на вкладку, указанную в URL (например, admin_panel.php#nav-users)
        document.addEventListener("DOMContentLoaded", function() {
            var hash = window.location.hash;
            if (hash) {
                var triggerEl = document.querySelector('button[data-bs-target="' + hash + '"]');
                if (triggerEl) {
                    bootstrap.Tab.getOrCreateInstance(triggerEl).show();
                }
            }
        });
    </script>
</body>
</html>