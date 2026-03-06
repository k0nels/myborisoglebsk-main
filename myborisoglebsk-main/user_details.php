<?php
require_once 'db.php';
session_start();
if ($_SESSION['role'] !== 'admin') exit;

$id = (int)$_GET['id'];

// Обработка действий (такая же как в админке)
if (isset($_GET['approve'])) {
    $pdo->prepare("UPDATE comments SET is_approved = 1 WHERE id = ?")->execute([$_GET['approve']]);
    header("Location: user_details.php?id=$id"); exit;
}
if (isset($_GET['delete'])) {
    $pdo->prepare("DELETE FROM comments WHERE id = ?")->execute([$_GET['delete']]);
    header("Location: user_details.php?id=$id"); exit;
}

$user = $pdo->query("SELECT username FROM users WHERE id = $id")->fetch();
$comms = $pdo->query("SELECT comments.*, news.title as news_title FROM comments JOIN news ON comments.news_id = news.id WHERE user_id = $id ORDER BY created_at DESC")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8"><title>История: <?= $user['username'] ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h3>Все комментарии пользователя: <span class="text-primary"><?= htmlspecialchars($user['username']) ?></span></h3>
            <a href="admin_panel.php#nav-users" class="btn btn-secondary rounded-pill">Назад в админку</a>
        </div>

        <div class="row">
            <?php if(empty($comms)) echo '<p class="text-muted ps-3">Пользователь еще не оставлял комментариев.</p>'; ?>
            <?php foreach($comms as $c): ?>
                <div class="col-12 mb-3">
                    <div class="card border-0 shadow-sm rounded-4 p-3 <?= $c['is_approved'] ? 'border-start border-success border-4' : 'border-start border-warning border-4' ?>">
                        <div class="d-flex justify-content-between align-items-center">
                            <div class="flex-grow-1 me-3">
                                <small class="text-muted">К новости: <strong><?= htmlspecialchars($c['news_title']) ?></strong></small>
                                <p class="mb-1 mt-1 fs-5"><?= htmlspecialchars($c['comment_text']) ?></p>
                                <small class="text-muted"><?= $c['created_at'] ?></small>
                            </div>
                            <div class="d-flex gap-2">
                                <?php if(!$c['is_approved']): ?>
                                    <a href="?id=<?= $id ?>&approve=<?= $c['id'] ?>" class="btn btn-success btn-sm">Одобрить</a>
                                <?php endif; ?>
                                <a href="?id=<?= $id ?>&delete=<?= $c['id'] ?>" class="btn btn-danger btn-sm" onclick="return confirm('Удалить?')">Удалить</a>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    </div>
</body>
</html>