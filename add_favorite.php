<?php
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit;
}

$user_id = $_SESSION['user_id'];

// Получаем данные рецепта
$recipe_uri = isset($_GET['recipe_id']) ? trim($_GET['recipe_id']) : '';
$title = isset($_GET['title']) ? trim($_GET['title']) : '';
$image = isset($_GET['image']) ? trim($_GET['image']) : '';
$calories = isset($_GET['calories']) ? intval($_GET['calories']) : 0;
$time = isset($_GET['time']) ? intval($_GET['time']) : 0;

if ($recipe_uri === '' || $title === '' || $image === '') {
    header('Location: ' . $_SERVER['HTTP_REFERER']);
    exit;
}

// Проверяем или создаём рецепт в локальной БД
$stmt = $conn->prepare("SELECT recipe_id FROM recipes WHERE recipe_image = ? LIMIT 1");
$stmt->bind_param("s", $image);
$stmt->execute();
$stmt->store_result();

if ($stmt->num_rows > 0) {
    $stmt->bind_result($local_recipe_id);
    $stmt->fetch();
    $stmt->close();
} else {
    $stmt->close();
    $insert = $conn->prepare("INSERT INTO recipes (recipe_title, recipe_description, recipe_image, cooking_time, calories, author_id) VALUES (?, ?, ?, ?, ?, ?)");
    $author_id = 1;
    $desc = 'Рецепт из API';
    $insert->bind_param("sssiii", $title, $desc, $image, $time, $calories, $author_id);
    $insert->execute();
    $local_recipe_id = $insert->insert_id;
    $insert->close();
}

// Добавляем в избранное
$check = $conn->prepare("SELECT user_id FROM favorites WHERE user_id = ? AND recipe_id = ?");
$check->bind_param("ii", $user_id, $local_recipe_id);
$check->execute();
$check->store_result();

if ($check->num_rows === 0) {
    $insert_fav = $conn->prepare("INSERT INTO favorites (user_id, recipe_id) VALUES (?, ?)");
    $insert_fav->bind_param("ii", $user_id, $local_recipe_id);
    $insert_fav->execute();
    $insert_fav->close();
}
$check->close();

// ВАЖНО: не перезагружаем страницу, чтобы не долбить API
echo '<!DOCTYPE html>
<html>
<head><meta charset="UTF-8"><title>Добавлено</title></head>
<body style="font-family: sans-serif; text-align: center; padding-top: 50px;">
    <h2 style="color: #2E7D32;">✅ Рецепт добавлен в избранное!</h2>
    <p>Вы можете вернуться к поиску или перейти в <a href="cabinet.php" style="color: #2E7D32; font-weight: bold;">Личный кабинет</a>.</p>
    <br>
    <a href="' . $_SERVER['HTTP_REFERER'] . '" style="display: inline-block; padding: 12px 28px; background: #2E7D32; color: white; text-decoration: none; border-radius: 8px; font-weight: 600;">Вернуться к поиску</a>
</body>
</html>';
exit;