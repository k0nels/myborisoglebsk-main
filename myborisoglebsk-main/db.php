<?php
$host = '127.0.0.1'; // попробуем IP вместо localhost
$db   = 'city_portal';
$user = 'portal_user';
$pass = '1234';

try {
$pdo = new PDO(
    "mysql:host=127.0.0.1;port=3307;dbname=city_portal;charset=utf8",
    $user,
    $pass

    );
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к базе: " . $e->getMessage());
}
?>