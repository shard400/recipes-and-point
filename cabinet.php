<?php
require_once 'db.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: auth.php');
    exit;
}

$user_id = $_SESSION['user_id'];
$stmt = $conn->prepare("SELECT user_first_name, user_last_name, user_email FROM users WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($first_name, $last_name, $email);
$stmt->fetch();
$stmt->close();

$recipes = [];
$stmt = $conn->prepare("SELECT recipe_id, recipe_title, recipe_description FROM recipes WHERE author_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $recipes[] = $row;
}
$stmt->close();

$favorites = [];
$stmt = $conn->prepare("SELECT r.recipe_id, r.recipe_title FROM favorites f JOIN recipes r ON f.recipe_id = r.recipe_id WHERE f.user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
while ($row = $result->fetch_assoc()) {
    $favorites[] = $row;
}
$stmt->close();

require_once 'header.php';
?>

<section class="cabinet-section">
    <div class="container">
        <div class="section-header">
            <h2>Личный кабинет</h2>
            <p>Добро пожаловать, <?= htmlspecialchars($first_name) ?> <?= htmlspecialchars($last_name) ?>!</p>
        </div>
        <div class="cabinet-grid">
            <div class="cabinet-sidebar">
                <ul>
                    <li class="active"><a href="#">Мои рецепты</a></li>
                    <li><a href="#">Избранное</a></li>
                    <li><a href="#">Профиль</a></li>
                </ul>
            </div>
            <div class="cabinet-content">
                <h3>Мои рецепты</h3>
                <div style="margin-bottom: 20px;">
                    <a href="recipe_action.php?action=add" class="btn btn-primary small">+ Добавить рецепт</a>
                </div>
                <div class="my-recipes-list">
                    <?php if (empty($recipes)): ?>
                        <p>У вас пока нет рецептов.</p>
                    <?php else: ?>
                        <?php foreach ($recipes as $recipe): ?>
                            <div class="my-recipe-item">
                                <span><?= htmlspecialchars($recipe['recipe_title']) ?></span>
                                <div class="actions">
                                    <a href="recipe_action.php?action=edit&id=<?= $recipe['recipe_id'] ?>" class="btn btn-outline small">Редактировать</a>
                                    <a href="recipe_action.php?action=delete&id=<?= $recipe['recipe_id'] ?>" class="btn btn-outline small" onclick="return confirm('Удалить рецепт?')">Удалить</a>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

                <h3>Избранное</h3>
                <div class="favorites-list">
                    <?php if (empty($favorites)): ?>
                        <p>У вас пока нет избранных рецептов.</p>
                    <?php else: ?>
                        <?php foreach ($favorites as $fav): ?>
                            <div class="favorite-item">
                                <span><?= htmlspecialchars($fav['recipe_title']) ?></span>
                                <a href="recipe_action.php?action=remove_favorite&recipe_id=<?= $fav['recipe_id'] ?>" class="btn btn-outline small">Убрать</a>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</section>

<?php require_once 'footer.php'; ?>