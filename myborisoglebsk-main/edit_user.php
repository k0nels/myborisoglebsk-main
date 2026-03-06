<?php
require_once 'db.php';
session_start();

// Доступ только для админов
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
    header("Location: index.php");
    exit;
}

$id = (int)$_GET['id'];
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$id]);
$user = $stmt->fetch();

if (!$user) die("Пользователь не найден");

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $role = $_POST['role']; // Получаем новую роль
    
    // Если поле пароля не пустое — меняем его, если пустое — оставляем старый
    if (!empty($_POST['password'])) {
        $pass_hash = password_hash($_POST['password'], PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, role = ?, password = ? WHERE id = ?");
        $stmt->execute([$username, $email, $role, $pass_hash, $id]);
    } else {
        $stmt = $pdo->prepare("UPDATE users SET username = ?, email = ?, role = ? WHERE id = ?");
        $stmt->execute([$username, $email, $role, $id]);
    }
    
    header("Location: admin_panel.php#nav-users"); 
    exit;
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Редактирование профиля: <?= htmlspecialchars($user['username']) ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="style.css">
</head>
<body class="bg-light py-5">
    <div class="container">
        <div class="card border-0 shadow-sm rounded-4 p-5 mx-auto" style="max-width: 500px;">
            <h2 class="fw-bold mb-4 text-center">Настройки доступа</h2>
            
            <form method="POST">
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">ЛОГИН</label>
                    <input type="text" name="username" class="form-control" value="<?= htmlspecialchars($user['username']) ?>" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">EMAIL</label>
                    <input type="email" name="email" class="form-control" value="<?= htmlspecialchars($user['email']) ?>" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-bold text-muted">ИЗМЕНИТЬ РОЛЬ</label>
                    <select name="role" class="form-select">
                        <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>Пользователь (Читатель)</option>
                        <option value="editor" <?= $user['role'] == 'editor' ? 'selected' : '' ?>>Редактор (Только новости)</option>
                        <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Администратор (Полный доступ)</option>
                    </select>
                </div>

                <div class="mb-4">
                    <label class="form-label small fw-bold text-muted">НОВЫЙ ПАРОЛЬ</label>
                    <input type="password" name="password" class="form-control" placeholder="Оставьте пустым, если не меняете">
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary-custom">Сохранить изменения</button>
                    <a href="admin_panel.php#nav-users" class="btn btn-light">Отмена</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>