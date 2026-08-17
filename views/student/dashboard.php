<?php $title = 'Главная'; ?>
<div class="page-head">
    <div><span class="eyebrow">Личный кабинет</span><h1>Здравствуйте, <?= e($user['first_name']) ?>!</h1><p>Здесь собраны занятия, ссылки и домашние задания.</p></div>
    <a class="button" href="/student/schedule">Записаться на занятие</a>
</div>

<?php if ($nextLesson): ?>
<section class="next-lesson-card">
    <div>
        <span class="eyebrow eyebrow-light">Следующее занятие</span>
        <h2><?= date('d.m.Y', strtotime($nextLesson['starts_at'])) ?> · <?= date('H:i', strtotime($nextLesson['starts_at'])) ?>—<?= date('H:i', strtotime($nextLesson['ends_at'])) ?></h2>
        <p><?= $nextLesson['meeting_comment'] ? nl2br(e($nextLesson['meeting_comment'])) : 'Преподаватель ещё не добавил комментарий к подключению.' ?></p>
    </div>
    <div class="next-lesson-actions">
        <span><?= $nextLesson['payment_status'] === 'paid' ? '✓ Оплачено' : 'Не оплачено' ?></span>
        <?php if ($nextLesson['meeting_url']): ?><a class="button button-light" href="<?= e($nextLesson['meeting_url']) ?>" target="_blank" rel="noopener">Подключиться</a><?php endif; ?>
        <a class="text-link-light" href="/student/lessons/<?= (int)$nextLesson['id'] ?>">Подробнее →</a>
    </div>
</section>
<?php else: ?>
<section class="panel"><p class="empty">У вас пока нет будущих занятий. <a href="/student/schedule">Выбрать время →</a></p></section>
<?php endif; ?>

<div class="content-grid content-grid-2 space-top">
    <section class="panel">
        <div class="panel-head"><h2>Ближайшие занятия</h2><a href="/student/lessons">Все</a></div>
        <div class="list"><?php foreach ($upcomingLessons as $lesson): ?><a class="list-row" href="/student/lessons/<?= (int)$lesson['id'] ?>"><div class="date-pill"><strong><?= date('d', strtotime($lesson['starts_at'])) ?></strong><span><?= date('m', strtotime($lesson['starts_at'])) ?></span></div><div><strong><?= date('H:i', strtotime($lesson['starts_at'])) ?>—<?= date('H:i', strtotime($lesson['ends_at'])) ?></strong><small><?= $lesson['meeting_url'] ? 'Ссылка готова' : 'Ссылка появится позже' ?></small></div><span class="row-arrow">→</span></a><?php endforeach; ?></div>
        <?php if (!$upcomingLessons): ?><p class="empty">Нет будущих занятий.</p><?php endif; ?>
    </section>
    <section class="panel">
        <div class="panel-head"><h2>Домашние задания</h2><a href="/student/homework">Все</a></div>
        <div class="list"><?php foreach ($homework as $hw): ?><a class="list-row" href="/student/homework/<?= (int)$hw['id'] ?>"><div><strong><?= e($hw['title']) ?></strong><small><?= e(match($hw['status']) {'assigned'=>'Нужно выполнить','submitted'=>'Отправлено','checked'=>'Проверено',default=>$hw['status']}) ?></small></div><span class="row-arrow">→</span></a><?php endforeach; ?></div>
        <?php if (!$homework): ?><p class="empty">Домашних заданий пока нет.</p><?php endif; ?>
    </section>
</div>
