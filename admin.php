<?php
require_once 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: index.php');
    exit;
}

$table = $_GET['table'] ?? '';
$view = $_GET['view'] ?? '';
$action = $_GET['action'] ?? '';

$tables = ['users', 'categories', 'chefs', 'collections', 'recipes', 'recipes_collections', 'favorites', 'blog_posts', 'ingredients', 'recipe_ingredients'];
$views = ['v_recipes_by_category', 'v_low_calorie_recipes', 'v_fast_recipes', 'v_chicken_recipes', 'v_diet_recipes'];

function getPrimaryKey($table) {
    $map = [
        'users' => 'user_id',
        'categories' => 'category_id',
        'chefs' => 'chef_id',
        'collections' => 'collection_id',
        'recipes' => 'recipe_id',
        'recipes_collections' => 'recipe_id',
        'favorites' => 'user_id',
        'blog_posts' => 'post_id',
        'ingredients' => 'ingredient_id',
        'recipe_ingredients' => 'recipe_id'
    ];
    return $map[$table] ?? 'id';
}

include 'header.php';
?>

<section class="admin-section">
    <div class="container">
        <h1>Административная панель</h1>
        
        <div style="margin-bottom: 20px;">
            <strong>Таблицы:</strong>
            <?php foreach ($tables as $t): ?>
                <a href="admin.php?table=<?= $t ?>" class="btn <?= $table === $t ? 'btn-primary' : 'btn-outline' ?>" style="margin: 2px;"><?= $t ?></a>
            <?php endforeach; ?>
        </div>

        <?php if ($table && in_array($table, $tables)): ?>
            <h2>Таблица: <?= htmlspecialchars($table) ?></h2>
            <div style="margin-bottom: 10px;">
                <a href="admin_edit.php?table=<?= $table ?>&action=add" class="btn btn-primary small">+ Добавить запись</a>
            </div>
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#2E7D32; color:white;">
                        <?php
                        $result = $conn->query("SHOW COLUMNS FROM `$table`");
                        $columns = [];
                        while ($row = $result->fetch_assoc()) {
                            $columns[] = $row['Field'];
                            echo "<th style='padding:8px; border:1px solid #ccc;'>" . htmlspecialchars($row['Field']) . "</th>";
                        }
                        echo "<th style='padding:8px; border:1px solid #ccc;'>Действия</th>";
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $data = $conn->query("SELECT * FROM `$table`");
                    while ($row = $data->fetch_assoc()) {
                        echo "<tr>";
                        foreach ($columns as $col) {
                            echo "<td style='padding:8px; border:1px solid #ccc;'>" . htmlspecialchars($row[$col] ?? '') . "</td>";
                        }
                        $pk = getPrimaryKey($table);
                        $id = $row[$pk] ?? '';
                        echo "<td style='padding:8px; border:1px solid #ccc;'>
                                <a href='admin_edit.php?table=$table&action=edit&id=$id' class='btn btn-outline small'>Ред.</a>
                                <form method='post' style='display:inline;' onsubmit='return confirm(\"Удалить?\");'>
                                    <input type='hidden' name='action' value='delete'>
                                    <input type='hidden' name='id' value='$id'>
                                    <button type='submit' class='btn btn-outline small'>Удалить</button>
                                </form>
                              </td>";
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        <?php endif; ?>

        <hr style="margin: 40px 0;">

        <h2>Представления (Views)</h2>
        <div style="margin-bottom: 20px;">
            <?php foreach ($views as $v): ?>
                <a href="admin.php?view=<?= $v ?>" class="btn <?= $view === $v ? 'btn-primary' : 'btn-outline' ?>"><?= $v ?></a>
            <?php endforeach; ?>
        </div>

        <?php if ($view && in_array($view, $views)): ?>
            <h3>Представление: <?= htmlspecialchars($view) ?></h3>
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#2E7D32; color:white;">
                        <?php
                        $result = $conn->query("SHOW COLUMNS FROM `$view`");
                        while ($row = $result->fetch_assoc()) {
                            echo "<th style='padding:8px; border:1px solid #ccc;'>" . htmlspecialchars($row['Field']) . "</th>";
                        }
                        ?>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $data = $conn->query("SELECT * FROM `$view`");
                    while ($row = $data->fetch_assoc()) {
                        echo "<tr>";
                        foreach ($row as $value) {
                            echo "<td style='padding:8px; border:1px solid #ccc;'>" . htmlspecialchars($value ?? '') . "</td>";
                        }
                        echo "</tr>";
                    }
                    ?>
                </tbody>
            </table>
        <?php endif; ?>
    </div>
</section>

<?php include 'footer.php'; ?>  