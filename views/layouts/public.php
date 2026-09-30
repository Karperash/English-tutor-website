<!doctype html>

<html lang="ru">

<head>

    <meta charset="utf-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1"
    >

    <title>
        <?= e($title ?? 'English Tutor') ?>
    </title>

    <link
        rel="stylesheet"
        href="<?= asset('css/app.css') ?>"
    >

</head>


<body class="public-body landing-page">


<header class="public-header landing-header">

    <div class="container nav-row">

        <a
            class="brand"
            href="/"
        >
            English<span>Tutor</span>
        </a>


        <nav
            class="public-nav"
            aria-label="Главная навигация"
        >

            <a href="/#about">
                Обо мне
            </a>

            <a href="/#about">
                Как проходят занятия
            </a>

            <a href="/#contacts">
                Контакты
            </a>

            <a
                class="button button-small landing-login"
                href="/login"
            >
                Войти
            </a>

        </nav>

    </div>

</header>


<main>

    <?php require dirname(__DIR__) . '/partials/flash.php'; ?>

    <?= $content ?>

</main>


<footer class="landing-footer">

    <div class="container landing-footer-main">

        <div class="landing-footer-brand">

            <a
                class="brand"
                href="/"
            >
                English<span>Tutor</span>
            </a>

            <small>
                Больше, чем просто язык
            </small>

        </div>


        <nav class="landing-footer-nav">

            <a href="/#about">
                Обо мне
            </a>

            <a href="/#about">
                Как проходят занятия
            </a>

            <a href="/#contacts">
                Контакты
            </a>

        </nav>


        <div class="landing-footer-contact">

            <div class="landing-footer-socials">
                <span>➤</span>
                <span>◉</span>
                <span>✉</span>
            </div>

            <div>
                <strong>
                    +7 900 123-45-67
                </strong>

                <small>
                    tutor.english@mail.ru
                </small>
            </div>

        </div>

    </div>


    <div class="container landing-footer-bottom">

        <span>
            © <?= date('Y') ?> English Tutor. Все права защищены.
        </span>

        <span>
            Better English. A Brighter You. ♡
        </span>

    </div>

</footer>


<script
    src="<?= asset('js/app.js') ?>"
    defer
></script>

</body>

</html>