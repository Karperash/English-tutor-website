<?php $title = 'Занятие'; $canCancel = $lesson['status'] === 'scheduled' && strtotime($lesson['starts_at']) > time() + 3*3600; ?>
<div class="page-head"><div><span class="eyebrow">Занятие</span><h1><?= date('d.m.Y · H:i', strtotime($lesson['starts_at'])) ?></h1><p>60 минут · Онлайн</p></div><a class="button button-secondary" href="/student/lessons">Все занятия</a></div>
<div class="content-grid content-grid-2">
    <section class="panel">
        <div class="panel-head"><h2>Подключение</h2><span class="status status-<?= e($lesson['status']) ?>"><?= e(match($lesson['status']) {'scheduled'=>'Запланировано','completed'=>'Проведено','student_cancelled'=>'Отменено вами','teacher_cancelled'=>'Отменено преподавателем',default=>$lesson['status']}) ?></span></div>
        <?php if ($lesson['meeting_comment']): ?><div class="rich-text"><?= nl2br(e($lesson['meeting_comment'])) ?></div><?php else: ?><p class="empty">Комментарий от преподавателя ещё не добавлен.</p><?php endif; ?>
        <?php if ($lesson['meeting_url']): ?><a class="button button-full space-top-small" href="<?= e($lesson['meeting_url']) ?>" target="_blank" rel="noopener"><?= $lesson['status'] === 'scheduled' ? 'Подключиться к занятию' : 'Открыть сохранённую ссылку' ?></a><?php elseif ($lesson['status'] === 'scheduled'): ?><div class="info-box">Ссылка на занятие появится здесь, когда преподаватель её добавит.</div><?php endif; ?>
        <dl class="details-list space-top-small"><div><dt>Оплата</dt><dd><?= $lesson['payment_status'] === 'paid' ? '✓ Оплачено' : 'Не оплачено' ?></dd></div></dl>
        <?php if ($canCancel): ?><form class="space-top-small" method="post" action="/student/lessons/<?= (int)$lesson['id'] ?>/cancel" onsubmit="return confirm('Отменить занятие?');"><?= csrf_field() ?><button class="button button-danger" type="submit">Отменить занятие</button></form><?php elseif ($lesson['status'] === 'scheduled'): ?><small class="muted-text">Самостоятельная отмена доступна не позднее чем за 3 часа.</small><?php endif; ?>
    </section>
    <section class="panel">
        <div class="panel-head"><h2>Домашнее задание</h2></div>
        <div class="list"><?php foreach ($homeworkList as $hw): ?><a class="list-row" href="/student/homework/<?= (int)$hw['id'] ?>"><div><strong><?= e($hw['title']) ?></strong><small><?= e(match($hw['status']) {'assigned'=>'Нужно выполнить','submitted'=>'Отправлено','checked'=>'Проверено',default=>$hw['status']}) ?></small></div><span class="row-arrow">→</span></a><?php endforeach; ?></div>
        <?php if (!$homeworkList): ?><p class="empty">К этому занятию заданий пока нет.</p><?php endif; ?>
    </section>
</div>
<?php if ($history): ?><section class="panel space-top"><div class="panel-head"><h2>Изменения занятия</h2></div><div class="timeline"><?php foreach ($history as $item): ?><div class="timeline-item"><strong><?= e($item['comment']) ?></strong><small><?= date('d.m.Y H:i', strtotime($item['created_at'])) ?></small><?php if ($item['old_starts_at'] || $item['new_starts_at']): ?><span><?= $item['old_starts_at'] ? date('d.m H:i', strtotime($item['old_starts_at'])) : '—' ?> → <?= $item['new_starts_at'] ? date('d.m H:i', strtotime($item['new_starts_at'])) : '—' ?></span><?php endif; ?></div><?php endforeach; ?></div></section><?php endif; ?>
