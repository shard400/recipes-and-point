<?php
session_start();
$host = 'localhost';
$user = 'root';          // ваш пользователь MySQL
$pass = '';              // ваш пароль (пустой, если нет)
$dbname = 'recipes_and_point';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}
$conn->set_charset("utf8mb4");
?>