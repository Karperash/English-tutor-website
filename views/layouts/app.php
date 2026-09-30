<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?= e($title ?? 'Личный кабинет') ?> · EnglishTutor</title>
    <link rel="stylesheet" href="<?= asset('css/app.css') ?>">
</head>
<body class="app-body">
<div class="app-shell">
    <aside class="sidebar" id="sidebar">
        <a class="brand brand-light" href="<?= $user['role'] === 'teacher' ? '/teacher' : '/student' ?>">English<span>Tutor</span></a>
        <div class="sidebar-user">
            <div class="avatar"><?= e(mb_strtoupper(mb_substr((string)$user['first_name'], 0, 1))) ?></div>
            <div>
                <strong><?= e($user['first_name'] . ' ' . $user['last_name']) ?></strong>
                <small><?= $user['role'] === 'teacher' ? 'Преподаватель' : 'Ученик' ?></small>
            </div>
        </div>

        <nav class="sidebar-nav">
            <?php if ($user['role'] === 'teacher'): ?>
                <a href="/teacher">Главная</a>
                <a href="/teacher/schedule">Расписание</a>
                <a href="/teacher/students">Ученики</a>
                <a href="/teacher/homework">Домашние задания</a>
            <?php else: ?>
                <a href="/student">Главная</a>
                <a href="/student/lessons">Мои занятия</a>
                <a href="/student/homework">Домашние задания</a>
            <?php endif; ?>
            <a href="/notifications">Уведомления<?php if ($notificationCount): ?><span class="badge"><?= (int)$notificationCount ?></span><?php endif; ?></a>
        </nav>

        <form class="sidebar-logout" method="post" action="/logout">
            <?= csrf_field() ?>
            <button class="link-button" type="submit">Выйти</button>
        </form>
    </aside>

    <div class="app-main">
        <header class="mobile-header">
            <button class="menu-button" type="button" data-menu-toggle aria-label="Открыть меню">☰</button>
            <a class="brand" href="<?= $user['role'] === 'teacher' ? '/teacher' : '/student' ?>">English<span>Tutor</span></a>
            <a class="notification-link" href="/notifications">🔔<?= $notificationCount ? '<span>' . (int)$notificationCount . '</span>' : '' ?></a>
        </header>
        <?php require dirname(__DIR__) . '/partials/flash.php'; ?>
        <main class="app-content">
            <?= $content ?>
        </main>
    </div>
</div>
<script src="<?= asset('js/app.js') ?>" defer></script>
</body>
</html>
