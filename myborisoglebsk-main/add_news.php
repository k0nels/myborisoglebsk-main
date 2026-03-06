<?php
require_once 'db.php';
session_start();

// Только админ или редактор
if (!isset($_SESSION['role']) || ($_SESSION['role'] !== 'admin' && $_SESSION['role'] !== 'editor')) {
    header("Location: index.php"); exit;
}

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = $_POST['title'];
    $cat_id = $_POST['category_id'];
    $desc = $_POST['short_description'];
    $content = $_POST['content'];
    $author_id = $_SESSION['user_id'];
    
    // ЛОГИКА ЗАГРУЗКИ ФАЙЛА
    $image_name = "";
    if (!empty($_FILES['image']['name'])) {
        // Создаем уникальное имя: время_название.jpg
        $image_name = time() . "_" . $_FILES['image']['name'];
        // Переносим файл в папку images (убедитесь, что она создана!)
        move_uploaded_file($_FILES['image']['tmp_name'], "img/" . $image_name);
    }

    $stmt = $pdo->prepare("INSERT INTO news (title, category_id, short_description, content, image, author_id) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->execute([$title, $cat_id, $desc, $content, $image_name, $author_id]);
    
    header("Location: admin_panel.php");
    exit;
}

$categories = $pdo->query("SELECT * FROM categories")->fetchAll();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8"><title>Добавить новость</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card shadow border-0 rounded-4 p-5 mx-auto" style="max-width: 800px;">
            <h2 class="fw-bold mb-4">Новая публикация</h2>
            <!-- ВАЖНО: enctype="multipart/form-data" -->
            <form action="" method="POST" enctype="multipart/form-data">
                <div class="mb-3"><label class="form-label fw-bold">Заголовок</label><input type="text" name="title" class="form-control" required></div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Категория</label>
                    <select name="category_id" class="form-select" required>
                        <?php foreach($categories as $c): ?>
                            <option value="<?= $c['id'] ?>"><?= $c['name'] ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3"><label class="form-label fw-bold">Краткое описание</label><textarea name="short_description" class="form-control" rows="2" required></textarea></div>
                <div class="mb-3"><label class="form-label fw-bold">Текст новости</label><textarea name="content" class="form-control" rows="10" required></textarea></div>
                
                <div class="mb-4">
                    <label class="form-label fw-bold text-primary">Загрузить фото</label>
                    <input type="file" name="image" class="form-control" accept="image/*">
                </div>

                <div class="d-flex gap-2">
                    <button type="submit" class="btn btn-primary-custom px-5">Опубликовать</button>
                    <a href="admin_panel.php" class="btn btn-light">Отмена</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>