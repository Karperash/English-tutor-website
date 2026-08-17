<?php $title = 'Вход'; ?>
<section class="auth-section">
    <form class="auth-card" method="post" action="/login">
        <?= csrf_field() ?>
        <span class="eyebrow">Личный кабинет</span>
        <h1>Войти</h1>
        <p>Введите логин и пароль, выданные преподавателем.</p>
        <label class="field">
            <span>Логин</span>
            <input type="text" name="login" autocomplete="username" required autofocus>
        </label>
        <label class="field">
            <span>Пароль</span>
            <input type="password" name="password" autocomplete="current-password" required>
        </label>
        <button class="button button-full" type="submit">Войти</button>
        <a class="back-link" href="/">← На главную</a>
    </form>
</section>
