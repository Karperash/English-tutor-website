<?php $title = 'Расписание'; ?>
<div class="page-head"><div><span class="eyebrow">Расписание</span><h1>Выберите удобное время</h1><p>Запись закрывается за 12 часов. Максимум 3 занятия в неделю.</p></div></div>
<div class="info-box space-bottom">Флажок «Могу также» только сообщает преподавателю, что вам подходит это время. Он <strong>не бронирует</strong> занятие.</div>
<section class="panel">
    <?php if (!$slots): ?><p class="empty">Свободных занятий пока нет.</p><?php endif; ?>
    <div class="schedule-list">
    <?php $lastDate = null; foreach ($slots as $slot): $date = date('Y-m-d', strtotime($slot['starts_at'])); $bookable = strtotime($slot['starts_at']) >= time() + 12*3600; ?>
        <?php if ($date !== $lastDate): $lastDate = $date; ?><div class="schedule-date"><strong><?= date('d.m.Y', strtotime($slot['starts_at'])) ?></strong><span><?= e(['Sun'=>'Вс','Mon'=>'Пн','Tue'=>'Вт','Wed'=>'Ср','Thu'=>'Чт','Fri'=>'Пт','Sat'=>'Сб'][date('D', strtotime($slot['starts_at']))] ?? '') ?></span></div><?php endif; ?>
        <div class="student-slot">
            <div class="schedule-time"><?= date('H:i', strtotime($slot['starts_at'])) ?><small>— <?= date('H:i', strtotime($slot['ends_at'])) ?></small></div>
            <div class="student-slot-actions">
                <form method="post" action="/student/schedule/<?= (int)$slot['id'] ?>/availability">
                    <?= csrf_field() ?>
                    <button class="toggle-button <?= $slot['flagged'] ? 'is-active' : '' ?>" type="submit"><?= $slot['flagged'] ? '✓ Могу также' : 'Могу также' ?></button>
                </form>
                <?php if ($bookable): ?>
                    <form method="post" action="/student/schedule/<?= (int)$slot['id'] ?>/book" onsubmit="return confirm('Записаться на <?= date('d.m.Y H:i', strtotime($slot['starts_at'])) ?>?');">
                        <?= csrf_field() ?><button class="button button-small" type="submit">Записаться</button>
                    </form>
                <?php else: ?><span class="muted-label">Запись закрыта</span><?php endif; ?>
            </div>
        </div>
    <?php endforeach; ?>
    </div>
</section>
