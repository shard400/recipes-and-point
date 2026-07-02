<?php require_once 'header.php'; 

// === НАСТРОЙКИ API EDAMAM (НОВЫЕ КЛЮЧИ) ===
$app_id = '493187c2';          // ВАШ НОВЫЙ ID
$app_key = 'ea6bfe7e10d6285e6f3782c7d57d2985'; // ВАШ НОВЫЙ КЛЮЧ

$query = isset($_GET['q']) ? trim($_GET['q']) : '';

// ===== АВТОМАТИЧЕСКИЙ ПЕРЕВОД ПОИСКОВОГО ЗАПРОСА С РУССКОГО НА АНГЛИЙСКИЙ =====
if (preg_match('/[а-яё]/i', $query)) {
    $translate_url = "https://translate.googleapis.com/translate_a/single?client=gtx&sl=ru&tl=en&dt=t&q=" . urlencode($query);
    $trans_response = file_get_contents($translate_url);
    if ($trans_response) {
        $trans_data = json_decode($trans_response, true);
        if (isset($trans_data[0][0][0])) {
            $query = $trans_data[0][0][0];
        }
    }
}

// Формируем URL API
$api_url = "https://api.edamam.com/api/recipes/v2?type=public&q=" . urlencode($query) . "&app_id=$app_id&app_key=$app_key";

// Добавляем фильтры из URL (НЕ ДОБАВЛЯЕМ ПУСТЫЕ)
if (isset($_GET['cat']) && $_GET['cat'] !== '') {
    $api_url .= "&dishType=" . urlencode($_GET['cat']);
}
if (isset($_GET['calories']) && $_GET['calories'] !== '') {
    // ВАЖНО: меняем формат 100-300 на 100 TO 300
    $calories_fixed = str_replace('-', ' TO ', $_GET['calories']);
    $api_url .= "&calories=" . urlencode($calories_fixed);
}
if (isset($_GET['time']) && $_GET['time'] !== '') {
    $api_url .= "&time=" . urlencode($_GET['time']);
}
if (isset($_GET['diet']) && $_GET['diet'] !== '') {
    $api_url .= "&diet=" . urlencode($_GET['diet']);
}

// ===== КЕШИРОВАНИЕ =====
$cache_file = 'cache_' . md5($api_url) . '.json';
$recipes = [];
$error_msg = '';

if (file_exists($cache_file) && (time() - filemtime($cache_file)) < 900) {
    $response = file_get_contents($cache_file);
} else {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $api_url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($http_code === 200) {
        file_put_contents($cache_file, $response);
    } else {
        $error_msg = "Ошибка API (код $http_code). Проверьте ключи или подождите.";
    }
}

if (!$error_msg) {
    $data = json_decode($response, true);
    $recipes = $data['hits'] ?? [];
}
?>

<section class="search-page-header">
    <div class="container">
        <h1>Поиск рецептов</h1>
        <form class="hero-search" action="search.php" method="GET">
            <input type="text" name="q" placeholder="Поиск рецептов (например: курица, паста, салат)" value="<?= htmlspecialchars($_GET['q'] ?? '') ?>">
            <button type="submit">Найти</button>
            
            <!-- ВСЕ ФИЛЬТРЫ ВНУТРИ ОДНОЙ ФОРМЫ (нет onchange, чтобы не долбить API) -->
            <div class="search-filters">
                <div class="filter-group">
                    <label>Категория</label>
                    <select name="cat">
                        <option value="">Все категории</option>
                        <option value="soup" <?= (isset($_GET['cat']) && $_GET['cat'] == 'soup') ? 'selected' : '' ?>>Супы</option>
                        <option value="salad" <?= (isset($_GET['cat']) && $_GET['cat'] == 'salad') ? 'selected' : '' ?>>Салаты</option>
                        <option value="main course" <?= (isset($_GET['cat']) && $_GET['cat'] == 'main course') ? 'selected' : '' ?>>Вторые блюда</option>
                        <option value="dessert" <?= (isset($_GET['cat']) && $_GET['cat'] == 'dessert') ? 'selected' : '' ?>>Десерты</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Калорийность</label>
                    <select name="calories">
                        <option value="">Любая</option>
                        <option value="100-300" <?= (isset($_GET['calories']) && $_GET['calories'] == '100-300') ? 'selected' : '' ?>>До 300 ккал</option>
                        <option value="300-600" <?= (isset($_GET['calories']) && $_GET['calories'] == '300-600') ? 'selected' : '' ?>>300–600 ккал</option>
                        <option value="600+" <?= (isset($_GET['calories']) && $_GET['calories'] == '600+') ? 'selected' : '' ?>>Более 600 ккал</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Время</label>
                    <select name="time">
                        <option value="">Любое</option>
                        <option value="0-30" <?= (isset($_GET['time']) && $_GET['time'] == '0-30') ? 'selected' : '' ?>>До 30 мин</option>
                        <option value="30-60" <?= (isset($_GET['time']) && $_GET['time'] == '30-60') ? 'selected' : '' ?>>30–60 мин</option>
                        <option value="60+" <?= (isset($_GET['time']) && $_GET['time'] == '60+') ? 'selected' : '' ?>>Более 60 мин</option>
                    </select>
                </div>
                <div class="filter-group">
                    <label>Диета</label>
                    <select name="diet">
                        <option value="">Любая</option>
                        <option value="low-carb" <?= (isset($_GET['diet']) && $_GET['diet'] == 'low-carb') ? 'selected' : '' ?>>Низкокалорийная</option>
                        <option value="vegan" <?= (isset($_GET['diet']) && $_GET['diet'] == 'vegan') ? 'selected' : '' ?>>Вегетарианская</option>
                        <option value="gluten-free" <?= (isset($_GET['diet']) && $_GET['diet'] == 'gluten-free') ? 'selected' : '' ?>>Безглютеновая</option>
                    </select>
                </div>
            </div>
        </form>
    </div>
</section>

<section class="search-filters-section">
    <div class="container">
        <?php if ($error_msg): ?>
            <p style="color:red; margin-bottom:20px;"><?= $error_msg ?></p>
        <?php endif; ?>

        <div class="recipe-grid">
            <?php if (!empty($recipes)): ?>
                <?php foreach ($recipes as $hit): 
                    $r = $hit['recipe'];
                    
                    // ===== ПЕРЕВОД НАЗВАНИЯ РЕЦЕПТА НА РУССКИЙ =====
                    $label_ru = $r['label'];
                    if (preg_match('/[a-zA-Z]/', $label_ru)) {
                        $translate_url = "https://translate.googleapis.com/translate_a/single?client=gtx&sl=en&tl=ru&dt=t&q=" . urlencode($label_ru);
                        $trans_response = file_get_contents($translate_url);
                        if ($trans_response) {
                            $trans_data = json_decode($trans_response, true);
                            if (isset($trans_data[0][0][0])) {
                                $label_ru = $trans_data[0][0][0];
                            }
                        }
                    }
                ?>
                <div class="recipe-card">
                    <img src="<?= htmlspecialchars($r['image']) ?>" alt="<?= htmlspecialchars($label_ru) ?>">
                    <div class="recipe-info">
                        <h3><?= htmlspecialchars($label_ru) ?></h3>
                        <div class="meta">
                            <span>⏱ <?= $r['totalTime'] ? $r['totalTime'] . ' мин' : 'Не указано' ?></span>
                            <span>🔥 <?= round($r['calories']) ?> ккал</span>
                        </div>
                        <span class="recipe-tag"><?= $r['dishType'][0] ?? 'Рецепт' ?></span>
                    </div>
                   <!-- Кнопка добавления в избранное -->
                    <div style="padding: 0 18px 18px; display: flex; justify-content: flex-end;">
                        <a href="add_favorite.php?recipe_id=<?= urlencode($r['url']) ?>&title=<?= urlencode($r['label']) ?>&image=<?= urlencode($r['image']) ?>&calories=<?= round($r['calories']) ?>&time=<?= $r['totalTime'] ?>" class="btn btn-outline small" style="font-size: 13px; padding: 4px 12px;">♥ В избранное</a>
                    </div>
                </div>
                <?php endforeach; ?>
            <?php else: ?>
                <p style="grid-column: 1/-1; text-align:center; padding:40px;">По запросу ничего не найдено.</p>
            <?php endif; ?>
        </div>
    </div>
</section>

<?php require_once 'footer.php'; ?>