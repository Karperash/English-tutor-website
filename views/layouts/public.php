<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'English Tutor') ?></title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body class="public-body">
<header class="public-header">
    <div class="container nav-row">
        <a class="brand" href="/">English<span>Tutor</span></a>
        <nav class="public-nav" aria-label="Главная навигация">
            <a href="/#about">Обо мне</a>
            <a href="/#price">Стоимость</a>
            <a href="/#reviews">Отзывы</a>
            <a href="/#contacts">Контакты</a>
            <a class="button button-small" href="/login">Войти</a>
        </nav>
    </div>
</header>
<main>
    <?php require dirname(__DIR__) . '/partials/flash.php'; ?>
    <?= $content ?>
</main>
<footer class="public-footer">
    <div class="container footer-row">
        <strong>EnglishTutor</strong>
        <span>Онлайн-занятия английским · <?= date('Y') ?></span>
    </div>
</footer>
<script src="<?= asset('js/app.js') ?>" defer></script>
</body>
</html>
