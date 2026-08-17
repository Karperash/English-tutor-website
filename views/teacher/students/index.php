<?php $title = 'Ученики'; ?>
<div class="page-head">
    <div><span class="eyebrow">Управление</span><h1>Ученики</h1><p>Аккаунты создаёт и редактирует только преподаватель.</p></div>
    <a class="button" href="/teacher/students/create">+ Добавить ученика</a>
</div>
<section class="panel">
    <?php if (!$students): ?><p class="empty">Пока нет учеников. Создайте первый аккаунт.</p><?php endif; ?>
    <div class="student-grid">
        <?php foreach ($students as $student): ?>
            <a class="student-card" href="/teacher/students/<?= (int)$student['id'] ?>">
                <div class="avatar large"><?= e(mb_strtoupper(mb_substr($student['first_name'], 0, 1))) ?></div>
                <div class="student-card-main">
                    <strong><?= e($student['first_name'] . ' ' . $student['last_name']) ?></strong>
                    <span>@<?= e($student['login']) ?></span>
                    <small><?= $student['upcoming'] ? 'Следующее: ' . date('d.m.Y H:i', strtotime($student['upcoming']['starts_at'])) : 'Нет будущих занятий' ?></small>
                </div>
                <span class="row-arrow">→</span>
            </a>
        <?php endforeach; ?>
    </div>
</section>
