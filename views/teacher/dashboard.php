<?php $title = 'Главная'; ?>
<div class="page-head">
    <div><span class="eyebrow">Кабинет преподавателя</span><h1>Добро пожаловать, <?= e($user['first_name']) ?>!</h1></div>
    <a class="button" href="/teacher/schedule">+ Добавить время</a>
</div>

<div class="stats-grid">
    <div class="stat-card"><span>Сегодня</span><strong><?= count($todayLessons) ?></strong><small>занятий</small></div>
    <div class="stat-card"><span>Ближайшее</span><strong><?= $upcomingLessons ? date('H:i', strtotime($upcomingLessons[0]['starts_at'])) : '—' ?></strong><small><?= $upcomingLessons ? e($upcomingLessons[0]['first_name'] . ' ' . $upcomingLessons[0]['last_name']) : 'нет занятий' ?></small></div>
    <div class="stat-card"><span>Не оплачено</span><strong><?= (int)$unpaidCount ?></strong><small>занятий</small></div>
    <div class="stat-card"><span>На проверку</span><strong><?= (int)$submittedHomeworkCount ?></strong><small>домашних работ</small></div>
</div>

<div class="content-grid content-grid-2">
    <section class="panel">
        <div class="panel-head"><h2>Сегодня</h2><a href="/teacher/schedule">Расписание</a></div>
        <?php if (!$todayLessons): ?><p class="empty">Сегодня занятий нет.</p><?php endif; ?>
        <div class="list">
            <?php foreach ($todayLessons as $lesson): ?>
                <a class="list-row" href="/teacher/lessons/<?= (int)$lesson['id'] ?>">
                    <div class="time-pill"><?= date('H:i', strtotime($lesson['starts_at'])) ?></div>
                    <div><strong><?= e($lesson['first_name'] . ' ' . $lesson['last_name']) ?></strong><small><?= $lesson['payment_status'] === 'paid' ? 'Оплачено' : 'Не оплачено' ?></small></div>
                    <span class="row-arrow">→</span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>

    <section class="panel">
        <div class="panel-head"><h2>Ближайшие занятия</h2><a href="/teacher/students">Ученики</a></div>
        <?php if (!$upcomingLessons): ?><p class="empty">Ближайших занятий пока нет.</p><?php endif; ?>
        <div class="list">
            <?php foreach ($upcomingLessons as $lesson): ?>
                <a class="list-row" href="/teacher/lessons/<?= (int)$lesson['id'] ?>">
                    <div class="date-pill"><strong><?= date('d', strtotime($lesson['starts_at'])) ?></strong><span><?= date('m', strtotime($lesson['starts_at'])) ?></span></div>
                    <div><strong><?= e($lesson['first_name'] . ' ' . $lesson['last_name']) ?></strong><small><?= date('d.m.Y · H:i', strtotime($lesson['starts_at'])) ?></small></div>
                    <span class="row-arrow">→</span>
                </a>
            <?php endforeach; ?>
        </div>
    </section>
</div>
