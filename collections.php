<?php require_once 'header.php'; ?>

<section class="collections-section">
    <div class="container">
        <div class="section-header">
            <h1>Коллекции рецептов</h1>
        </div>
        <div class="collections-grid">
            <a href="search.php?collection=winter" class="collection-card">
                <div class="icon"><img src="img/зимние блюда.jpg" alt="" class="food-icon"></div>
                <h3>Зимние блюда</h3>
                <p>Согревающие и сытные рецепты для холодного времени года</p>
            </a>
            <a href="search.php?collection=fast" class="collection-card">
                <div class="icon"><img src="img/5ужинов.jpg" alt="" class="food-icon"></div>
                <h3>Быстрые ужины</h3>
                <p>Рецепты, которые готовятся не более 30 минут</p>
            </a>
            <a href="search.php?collection=pp" class="collection-card">
                <div class="icon"><img src="img/ппменю.jpg" alt="" class="food-icon"></div>
                <h3>ПП-рецепты</h3>
                <p>Полезные и сбалансированные блюда для здорового питания</p>
            </a>
            <a href="search.php?collection=family" class="collection-card">
                <div class="icon"><img src="img/семейные.jpg" alt="" class="food-icon"></div>
                <h3>Семейные ужины</h3>
                <p>Рецепты, которые понравятся и взрослым, и детям</p>
            </a>
            <a href="search.php?collection=holiday" class="collection-card">
                <div class="icon"><img src="img/праздник.jpg" alt="" class="food-icon"></div>
                <h3>Праздничные блюда</h3>
                <p>Рецепты для новогоднего стола и особых случаев</p>
            </a>
            <a href="search.php?collection=economy" class="collection-card">
                <div class="icon"><img src="img/бюджет.jpg" alt="" class="food-icon"></div>
                <h3>Экономные рецепты</h3>
                <p>Вкусные блюда из недорогих продуктов</p>
            </a>
        </div>
    </div>
</section>

<?php require_once 'footer.php'; ?>