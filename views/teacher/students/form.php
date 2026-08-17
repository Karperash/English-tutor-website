<?php $title = $mode === 'create' ? 'Новый ученик' : 'Редактирование ученика'; ?>
<div class="page-head"><div><span class="eyebrow">Ученики</span><h1><?= $mode === 'create' ? 'Новый ученик' : 'Редактирование' ?></h1></div><a class="button button-secondary" href="/teacher/students">Назад</a></div>
<form class="panel form-panel" method="post" action="<?= $mode === 'create' ? '/teacher/students/create' : '/teacher/students/' . (int)$student['id'] . '/edit' ?>">
    <?= csrf_field() ?>
    <div class="form-grid">
        <label class="field"><span>Имя *</span><input name="first_name" value="<?= e($student['first_name'] ?? '') ?>" required></label>
        <label class="field"><span>Фамилия *</span><input name="last_name" value="<?= e($student['last_name'] ?? '') ?>" required></label>
        <label class="field"><span>Логин *</span><input name="login" value="<?= e($student['login'] ?? '') ?>" pattern="[A-Za-z0-9_.-]{3,50}" required></label>
        <label class="field"><span><?= $mode === 'create' ? 'Пароль *' : 'Новый пароль' ?></span><input type="password" name="password" <?= $mode === 'create' ? 'required minlength="6"' : 'minlength="6"' ?>><small><?= $mode === 'edit' ? 'Оставьте пустым, чтобы не менять.' : 'Минимум 6 символов.' ?></small></label>
        <label class="field"><span>Телефон ученика *</span><input name="phone" value="<?= e($student['phone'] ?? '') ?>" required></label>
        <div></div>
        <label class="field"><span>Имя родителя / мамы *</span><input name="parent_name" value="<?= e($student['parent_name'] ?? '') ?>" required></label>
        <label class="field"><span>Телефон родителя *</span><input name="parent_phone" value="<?= e($student['parent_phone'] ?? '') ?>" required></label>
    </div>
    <div class="form-actions"><button class="button" type="submit"><?= $mode === 'create' ? 'Создать ученика' : 'Сохранить' ?></button></div>
</form>
