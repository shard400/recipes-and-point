<?php
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$action = $_GET['action'] ?? '';

if ($action === 'add') {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $ingredients = trim($_POST['ingredients']);
        $instructions = trim($_POST['instructions']);
        $cooking_time = intval($_POST['cooking_time']);
        $calories = intval($_POST['calories']);
        $category_id = intval($_POST['category_id']);
        $chef_id = intval($_POST['chef_id']);

        $stmt = $conn->prepare("INSERT INTO recipes (recipe_title, recipe_description, recipe_ingredients, recipe_instructions, cooking_time, calories, category_id, chef_id, author_id) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("ssssiiiii", $title, $description, $ingredients, $instructions, $cooking_time, $calories, $category_id, $chef_id, $user_id);
        if ($stmt->execute()) {
            header('Location: cabinet.php');
            exit;
        } else {
            $error = 'Ошибка при сохранении.';
        }
    }
    include 'header.php';
    ?>
    <div class="container" style="max-width: 600px; margin-top: 40px;">
        <h2>Добавить рецепт</h2>
        <?php if (isset($error)): ?><div style="color:red;"><?= $error ?></div><?php endif; ?>
        <form method="post">
            <div class="form-group"><label>Название *</label><input type="text" name="title" required></div>
            <div class="form-group"><label>Описание</label><textarea name="description"></textarea></div>
            <div class="form-group"><label>Ингредиенты *</label><textarea name="ingredients" required></textarea></div>
            <div class="form-group"><label>Инструкция</label><textarea name="instructions"></textarea></div>
            <div class="form-group"><label>Время (мин)</label><input type="number" name="cooking_time" value="30"></div>
            <div class="form-group"><label>Калории</label><input type="number" name="calories" value="300"></div>
            <div class="form-group"><label>Категория</label>
                <select name="category_id">
                    <?php
                    $cat = $conn->query("SELECT category_id, category_name FROM categories");
                    while ($row = $cat->fetch_assoc()) {
                        echo "<option value='{$row['category_id']}'>{$row['category_name']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group"><label>Шеф-повар</label>
                <select name="chef_id">
                    <?php
                    $chef = $conn->query("SELECT chef_id, chef_name FROM chefs");
                    while ($row = $chef->fetch_assoc()) {
                        echo "<option value='{$row['chef_id']}'>{$row['chef_name']}</option>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Сохранить</button>
            <a href="cabinet.php" class="btn btn-outline">Отмена</a>
        </form>
    </div>
    <?php include 'footer.php'; ?>
    <?php exit;
}

if ($action === 'edit') {
    $id = intval($_GET['id']);
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $title = trim($_POST['title']);
        $description = trim($_POST['description']);
        $ingredients = trim($_POST['ingredients']);
        $instructions = trim($_POST['instructions']);
        $cooking_time = intval($_POST['cooking_time']);
        $calories = intval($_POST['calories']);
        $category_id = intval($_POST['category_id']);
        $chef_id = intval($_POST['chef_id']);

        $stmt = $conn->prepare("UPDATE recipes SET recipe_title=?, recipe_description=?, recipe_ingredients=?, recipe_instructions=?, cooking_time=?, calories=?, category_id=?, chef_id=? WHERE recipe_id=? AND author_id=?");
        $stmt->bind_param("ssssiiiiii", $title, $description, $ingredients, $instructions, $cooking_time, $calories, $category_id, $chef_id, $id, $user_id);
        if ($stmt->execute()) {
            header('Location: cabinet.php');
            exit;
        } else {
            $error = 'Ошибка при обновлении.';
        }
    }
    $stmt = $conn->prepare("SELECT * FROM recipes WHERE recipe_id = ? AND author_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($result->num_rows === 0) {
        header('Location: cabinet.php');
        exit;
    }
    $recipe = $result->fetch_assoc();
    include 'header.php';
    ?>
    <div class="container" style="max-width: 600px; margin-top: 40px;">
        <h2>Редактировать рецепт</h2>
        <?php if (isset($error)): ?><div style="color:red;"><?= $error ?></div><?php endif; ?>
        <form method="post">
            <div class="form-group"><label>Название *</label><input type="text" name="title" value="<?= htmlspecialchars($recipe['recipe_title']) ?>" required></div>
            <div class="form-group"><label>Описание</label><textarea name="description"><?= htmlspecialchars($recipe['recipe_description']) ?></textarea></div>
            <div class="form-group"><label>Ингредиенты *</label><textarea name="ingredients" required><?= htmlspecialchars($recipe['recipe_ingredients']) ?></textarea></div>
            <div class="form-group"><label>Инструкция</label><textarea name="instructions"><?= htmlspecialchars($recipe['recipe_instructions']) ?></textarea></div>
            <div class="form-group"><label>Время (мин)</label><input type="number" name="cooking_time" value="<?= $recipe['cooking_time'] ?>"></div>
            <div class="form-group"><label>Калории</label><input type="number" name="calories" value="<?= $recipe['calories'] ?>"></div>
            <div class="form-group"><label>Категория</label>
                <select name="category_id">
                    <?php
                    $cat = $conn->query("SELECT category_id, category_name FROM categories");
                    while ($row = $cat->fetch_assoc()) {
                        $sel = ($row['category_id'] == $recipe['category_id']) ? 'selected' : '';
                        echo "<option value='{$row['category_id']}' $sel>{$row['category_name']}</option>";
                    }
                    ?>
                </select>
            </div>
            <div class="form-group"><label>Шеф-повар</label>
                <select name="chef_id">
                    <?php
                    $chef = $conn->query("SELECT chef_id, chef_name FROM chefs");
                    while ($row = $chef->fetch_assoc()) {
                        $sel = ($row['chef_id'] == $recipe['chef_id']) ? 'selected' : '';
                        echo "<option value='{$row['chef_id']}' $sel>{$row['chef_name']}</option>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit" class="btn btn-primary">Сохранить</button>
            <a href="cabinet.php" class="btn btn-outline">Отмена</a>
        </form>
    </div>
    <?php include 'footer.php'; ?>
    <?php exit;
}

if ($action === 'delete') {
    $id = intval($_GET['id']);
    $stmt = $conn->prepare("DELETE FROM recipes WHERE recipe_id = ? AND author_id = ?");
    $stmt->bind_param("ii", $id, $user_id);
    $stmt->execute();
    header('Location: cabinet.php');
    exit;
}

if ($action === 'remove_favorite') {
    $recipe_id = intval($_GET['recipe_id']);
    $stmt = $conn->prepare("DELETE FROM favorites WHERE user_id = ? AND recipe_id = ?");
    $stmt->bind_param("ii", $user_id, $recipe_id);
    $stmt->execute();
    header('Location: cabinet.php');
    exit;
}

header('Location: cabinet.php');