<?php
require_once 'db.php';
session_start();

if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'editor')) {
    header("Location: index.php"); exit;
}

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM news WHERE id = ?");
$stmt->execute([$id]);
$news = $stmt->fetch();

if (!$news) die("Новость не найдена");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $cat_id = $_POST['category_id'];
    $desc = $_POST['short_description'];
    $content = $_POST['content'];
    
    $image_name = $news['image']; // По умолчанию старое фото

    if (!empty($_FILES['image']['name'])) {
        $image_name = time() . "_" . $_FILES['image']['name'];
        move_uploaded_file($_FILES['image']['tmp_name'], "img/" . $image_name);
    }

    $stmt = $pdo->prepare("UPDATE news SET title = ?, category_id = ?, short_description = ?, content = ?, image = ? WHERE id = ?");
    $stmt->execute([$title, $cat_id, $desc, $content, $image_name, $id]);
    
    header("Location: admin_panel.php");
    exit;
}

$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8"><title>Редактировать</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card shadow border-0 rounded-4 p-5 mx-auto" style="max-width: 800px;">
            <h2 class="fw-bold mb-4">Редактирование новости</h2>
            <!-- ВАЖНО: enctype="multipart/form-data" -->
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="mb-3"><label class="form-label fw-bold">Заголовок</label><input type="text" name="title" class="form-control" value="<?= htmlspecialchars($news['title']) ?>" required></div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Категория</label>
                    <select name="category_id" class="form-select">
                        <?php foreach($categories as $c): ?>
                            <option value="<?= $c['id'] ?>" <?= ($c['id'] == $news['category_id']) ? 'selected' : '' ?>><?= $c['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3"><label class="form-label fw-bold">Краткое описание</label><textarea name="short_description" class="form-control" rows="2"><?= htmlspecialchars($news['short_description']) ?></textarea></div>
                <div class="mb-3"><label class="form-label fw-bold">Текст новости</label><textarea name="content" class="form-control" rows="10"><?= htmlspecialchars($news['content']) ?></textarea></div>
                
                <div class="mb-4">
                    <label class="form-label fw-bold">Изменить изображение</label>
                    <p class="small text-muted">Сейчас установлено: <?= $news['image'] ?></p>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary-custom px-5">Сохранить</button>
                    <a href="admin_panel.php" class="btn btn-light">Отмена</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>