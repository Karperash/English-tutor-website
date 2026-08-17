<?php $title = 'Расписание'; ?>
<div class="page-head">
    <div><span class="eyebrow">Расписание</span><h1>Свободное время и занятия</h1><p>Каждый слот автоматически длится 60 минут.</p></div>
</div>
<div class="content-grid schedule-layout">
    <section class="panel sticky-panel">
        <div class="panel-head"><h2>Добавить время</h2></div>
        <form method="post" action="/teacher/schedule/slots" class="stack-form">
            <?= csrf_field() ?>
            <label class="field"><span>Дата</span><input type="date" name="date" min="<?= date('Y-m-d') ?>" required></label>
            <label class="field"><span>Время начала</span><input type="time" name="time" step="900" required></label>
            <button class="button button-full" type="submit">Добавить слот на 60 минут</button>
        </form>
        <div class="info-box">Ученик может отдельно отметить свободный слот как «могу также в это время». Такая отметка не бронирует занятие.</div>
    </section>

    <section class="panel">
        <div class="panel-head"><h2>Ближайшие 60 дней</h2></div>
        <?php if (!$slots): ?><p class="empty">Слотов пока нет. Добавьте первое свободное время.</p><?php endif; ?>
        <div class="schedule-list">
            <?php $lastDate = null; foreach ($slots as $slot): $date = date('Y-m-d', strtotime($slot['starts_at'])); ?>
                <?php if ($date !== $lastDate): $lastDate = $date; ?><div class="schedule-date"><strong><?= date('d.m.Y', strtotime($slot['starts_at'])) ?></strong><span><?= e(['Sun'=>'Вс','Mon'=>'Пн','Tue'=>'Вт','Wed'=>'Ср','Thu'=>'Чт','Fri'=>'Пт','Sat'=>'Сб'][date('D', strtotime($slot['starts_at']))] ?? '') ?></span></div><?php endif; ?>
                <div class="schedule-row <?= $slot['lesson_id'] ? 'is-booked' : '' ?>">
                    <div class="schedule-time"><?= date('H:i', strtotime($slot['starts_at'])) ?><small>— <?= date('H:i', strtotime($slot['ends_at'])) ?></small></div>
                    <div class="schedule-info">
                        <?php if ($slot['lesson_id']): ?>
                            <strong><?= e($slot['first_name'] . ' ' . $slot['last_name']) ?></strong>
                            <small>Занято · <a href="/teacher/lessons/<?= (int)$slot['lesson_id'] ?>">открыть занятие</a></small>
                        <?php else: ?>
                            <strong>Свободно</strong>
                            <?php if ($slot['availability_names']): ?><small>Также могут: <?= e(implode(', ', array_map(fn($n) => $n['first_name'] . ' ' . $n['last_name'], $slot['availability_names']))) ?></small><?php else: ?><small>Нет дополнительных отметок</small><?php endif; ?>
                        <?php endif; ?>
                    </div>
                    <?php if (!$slot['lesson_id']): ?><form method="post" action="/teacher/schedule/slots/<?= (int)$slot['id'] ?>/delete" onsubmit="return confirm('Удалить свободный слот?');"><?= csrf_field() ?><button class="icon-button" type="submit" title="Удалить">×</button></form><?php endif; ?>
                </div>
            <?php endforeach; ?>
        </div>
    </section>
</div>
