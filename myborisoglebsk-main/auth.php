<?php
require_once 'db.php';
session_start();

// РЕГИСТРАЦИЯ
if (isset($_POST['register'])) {
    $email = $_POST['email'];
    $pass = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $user = $_POST['username'];
    $full_name = $_POST['full_name'];

    $stmt = $pdo->prepare("INSERT INTO users (username, email, password, full_name) VALUES (?, ?, ?, ?)");
    try {
        $stmt->execute([$user, $email, $pass, $full_name]);
        $_SESSION['user_id'] = $pdo->lastInsertId();
        header("Location: index.php");
    } catch (Exception $e) {
        die("Ошибка регистрации (возможно, email или логин заняты)");
    }
}

// ВХОД
if (isset($_POST['login'])) {
    $email = $_POST['email'];
    $pass = $_POST['password'];

    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if ($user && password_verify($pass, $user['password'])) {
        if ($user['is_blocked']) die("Ваш аккаунт заблокирован.");
        
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['role'] = $user['role'];
        header("Location: index.php");
    } else {
        die("Неверный логин или пароль");
    }
}

// ВЫХОД
if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
}