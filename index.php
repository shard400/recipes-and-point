<?php require_once 'header.php'; ?>

<section class="hero">
    <div class="container">
        <h1>Найди рецепт по душе</h1>
        <p class="subtitle">Миллионы рецептов, удобный поиск и полезные подборки</p>
        
        <form class="hero-search" action="search.php" method="GET">
            <input type="text" name="q" placeholder="Поиск рецептов (например: курица, паста, салат)">
            <button type="submit">Найти</button>
        </form>
    </div>
</section>

<section class="categories">
    <div class="container">
        <div class="section-header">
            <h2>Категории</h2>
        </div>
        <div class="categories-grid">
            <a href="search.php?cat=soup" class="category-card">
                <div class="category-icon"><img src="img/green-icon/icons8-суп-50.png" alt="" class="food-icon"></div>
                <p>Супы</p>
            </a>
            <a href="search.php?cat=salad" class="category-card">
                <div class="category-icon"><img src="img/green-icon/icons8-салат-50.png" alt="" class="food-icon"></div>
                <p>Салаты</p>
            </a>
            <a href="search.php?cat=main%20course" class="category-card">
                <div class="category-icon"><img src="img/green-icon/icons8-стейк-50.png" alt="" class="food-icon"></div>
                <p>Вторые блюда</p>
            </a>
            <a href="search.php?cat=dessert" class="category-card">
                <div class="category-icon"><img src="img/green-icon/icons8-десерт-50.png" alt="" class="food-icon"></div>
                <p>Десерты</p>
            </a>
        </div>
    </div>
</section>

<?php require_once 'footer.php'; ?>