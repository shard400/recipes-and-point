<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Рецепты и точка</title>
    <link rel="stylesheet" href="style.css">
    <link href="https://fonts.googleapis.com/css2?family=MuseoModerno:wght@300;400;500;600;700&display=swap" rel="stylesheet">
</head>
<body>
    <header class="header">
        <div class="container header-inner">
            <div class="logo">
                <a href="index.php">
                    <img src="Vector.png" alt="Логотип Рецепты и точка">
                </a>
            </div>
            <nav class="nav">
                <ul>
                    <li><a href="index.php">Главная</a></li>
                    <li><a href="search.php">Поиск</a></li>
                    <li><a href="collections.php">Коллекции</a></li>
                    <li><a href="chefs.php">Шеф-повара</a></li>
                    <li><a href="blog.php">Блог</a></li>
                    <?php if (isset($_SESSION['user_id'])): ?>
                        <li><a href="cabinet.php">Личный кабинет</a></li>
                        <?php if ($_SESSION['user_role'] === 'admin'): ?>
                            <li><a href="admin.php">Админ-панель</a></li>
                        <?php endif; ?>
                        <li><a href="logout.php" class="btn btn-outline">Выход</a></li>
                    <?php else: ?>
                        <li><a href="auth.php" class="btn btn-outline">Вход</a></li>
                        <li><a href="auth.php" class="btn btn-primary">Регистрация</a></li>
                    <?php endif; ?>
                </ul>
            </nav>
        </div>
    </header>
    <main>