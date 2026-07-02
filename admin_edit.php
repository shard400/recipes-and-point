<?php
require_once 'db.php';

// Проверка прав администратора
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$table = $_GET['table'] ?? '';
$action = $_GET['action'] ?? '';
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

// Список разрешённых таблиц
$allowed_tables = ['users', 'categories', 'chefs', 'collections', 'recipes', 'blog_posts', 'ingredients'];

if (!in_array($table, $allowed_tables)) {
    die('Недопустимая таблица.');
}

// Получаем структуру таблицы
$result = $conn->query("SHOW COLUMNS FROM `$table`");
$columns = [];
$primary_key = '';
while ($row = $result->fetch_assoc()) {
    $columns[] = $row['Field'];
    if ($row['Key'] === 'PRI') {
        $primary_key = $row['Field'];
    }
}

// Если это редактирование, загружаем данные
$row_data = [];
if ($action === 'edit' && $id > 0) {
    $stmt = $conn->prepare("SELECT * FROM `$table` WHERE `$primary_key` = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $res = $stmt->get_result();
    if ($res->num_rows > 0) {
        $row_data = $res->fetch_assoc();
    }
    $stmt->close();
}

// Обработка сохранения (POST)
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $updates = [];
    $params = [];
    $types = '';

    foreach ($columns as $col) {
        if ($col === $primary_key) continue; // пропускаем ID
        if (isset($_POST[$col])) {
            $updates[] = "`$col` = ?";
            $params[] = $_POST[$col];
            $types .= 's';
        }
    }

    if (!empty($updates)) {
        if ($action === 'edit' && $id > 0) {
            // Обновление
            $sql = "UPDATE `$table` SET " . implode(', ', $updates) . " WHERE `$primary_key` = ?";
            $params[] = $id;
            $types .= 'i';
        } else {
            // Добавление
            $cols = array_filter($columns, fn($c) => $c !== $primary_key);
            $placeholders = implode(', ', array_fill(0, count($cols), '?'));
            $sql = "INSERT INTO `$table` (`" . implode('`, `', $cols) . "`) VALUES ($placeholders)";
            // Параметры уже в $params
        }

        $stmt = $conn->prepare($sql);
        $stmt->bind_param($types, ...$params);
        if ($stmt->execute()) {
            header("Location: admin.php?table=$table");
            exit;
        } else {
            $error = "Ошибка сохранения: " . $conn->error;
        }
        $stmt->close();
    }
}

include 'header.php';
?>

<div class="container" style="max-width: 700px; margin-top: 40px; margin-bottom: 40px;">
    <h2><?= $action === 'edit' ? 'Редактировать' : 'Добавить' ?> запись в таблице «<?= htmlspecialchars($table) ?>»</h2>

    <?php if (isset($error)): ?>
        <p style="color: red;"><?= $error ?></p>
    <?php endif; ?>

    <form method="post" style="background: #f9f9f9; padding: 24px; border-radius: 12px;">
        <?php foreach ($columns as $col): 
            if ($col === $primary_key) continue; // не показываем ID
            $value = $row_data[$col] ?? '';
        ?>
        <div class="form-group">
            <label><?= htmlspecialchars($col) ?></label>
            <input type="text" name="<?= $col ?>" value="<?= htmlspecialchars($value) ?>" class="form-control" style="width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 6px;">
        </div>
        <?php endforeach; ?>

        <div style="display: flex; gap: 12px; margin-top: 20px;">
            <button type="submit" class="btn btn-primary">Сохранить</button>
            <a href="admin.php?table=<?= $table ?>" class="btn btn-outline">Отмена</a>
        </div>
    </form>
</div>

<?php include 'footer.php'; ?>