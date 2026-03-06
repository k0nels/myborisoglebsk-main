<?php
// СТРОГО БЕЗ ПРОБЕЛОВ ПЕРЕД <?php
require_once 'db.php';
session_start();

$action = $_POST['action'] ?? $_GET['action'] ?? '';

// --- РЕГИСТРАЦИЯ ---
if ($action == 'register') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $hashed_pass = password_hash($password, PASSWORD_DEFAULT);
    
    try {
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password, role, is_blocked) VALUES (?, ?, ?, 'user', 0)");
        $stmt->execute([$username, $email, $hashed_pass]);
        $_SESSION['user_id'] = $pdo->lastInsertId();
        $_SESSION['role'] = 'user';
        session_write_close(); 
        header("Location: index.php");
        exit();
    } catch (PDOException $e) {
        header("Location: index.php?error=exists");
        exit();
    }
}

// --- ВХОД ---
if ($action == 'login') {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($password, $user['password'])) {
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        session_write_close(); 
        if ($user['is_blocked'] == 1) {
            header("Location: blocked.php");
            exit();
        }
        header("Location: index.php");
        exit();
    } else {
        header("Location: index.php?error=login");
        exit();
    }
}

// --- ВЫХОД ---
if ($action == 'logout') {
    session_destroy();
    header("Location: index.php");
    exit();
}

// --- ДОБАВЛЕНИЕ КОММЕНТАРИЯ ---
if ($action == 'add_comment') {
    if (!isset($_SESSION['user_id'])) { header("Location: index.php"); exit; }
    $news_id = (int)$_POST['news_id'];
    $user_id = $_SESSION['user_id'];
    $text = trim($_POST['comment_text']);
    if (!empty($text)) {
        $stmt = $pdo->prepare("INSERT INTO comments (news_id, user_id, comment_text, is_approved) VALUES (?, ?, ?, 0)");
        $stmt->execute([$news_id, $user_id, $text]);
    }
    header("Location: news.php?id=$news_id&msg=moderation");
    exit;
}

// --- ОТПРАВКА ОБРАТНОЙ СВЯЗИ (БАЗА + СОХРАНЕНИЕ В ФАЙЛ) ---
if ($action == 'send_feedback') {
    $name = trim($_POST['name']);
    $phone = trim($_POST['phone']);
    $message = trim($_POST['message']);

    if (!empty($name) && !empty($phone) && !empty($message)) {
        // 1. Запись в базу данных
        $stmt = $pdo->prepare("INSERT INTO feedback (name, phone, message) VALUES (?, ?, ?)");
        $stmt->execute([$name, $phone, $message]);

        // 2. СОХРАНЕНИЕ ПИСЬМА В ПАПКУ
        $dir = 'sent_emails'; 
        if (!is_dir($dir)) mkdir($dir, 0777, true); // Создаем папку, если её нет

        // Формируем имя файла (дата_время_имя.txt)
        $filename = $dir . '/mail_' . date('Y-m-d_H-i-s') . '_' . $name . '.txt';
        
        // Текст "письма"
        $email_text = "НОВОЕ ОБРАЩЕНИЕ С САЙТА\n";
        $email_text .= "==========================\n";
        $email_text .= "От кого: " . $name . "\n";
        $email_text .= "Телефон: " . $phone . "\n";
        $email_text .= "Дата: " . date('d.m.Y H:i:s') . "\n";
        $email_text .= "--------------------------\n";
        $email_text .= "Сообщение:\n" . $message . "\n";
        $email_text .= "==========================\n";

        // Записываем файл на диск
        file_put_contents($filename, $email_text);

        // Ответ для AJAX или редирект
        if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
            echo "success";
            exit;
        }
        header("Location: index.php?feedback=success#contact-form");
    } else {
        header("Location: index.php?error=empty_fields#contact-form");
    }
    exit;
}

// --- УДАЛЕНИЕ ОБРАЩЕНИЯ ---
if ($action == 'del_feedback' && isset($_SESSION['role']) && $_SESSION['role'] == 'admin') {
    $id = (int)$_GET['id'];
    $stmt = $pdo->prepare("DELETE FROM feedback WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: admin_panel.php#nav-feedback");
    exit;
}