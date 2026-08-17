<?php $title = 'Мои занятия'; ?>
<div class="page-head"><div><span class="eyebrow">Занятия</span><h1>Мои занятия</h1><p>Будущие и прошедшие уроки.</p></div><a class="button" href="/student/schedule">Записаться</a></div>
<section class="panel">
    <div class="table-wrap"><table><thead><tr><th>Дата</th><th>Статус</th><th>Оплата</th><th>Подключение</th><th></th></tr></thead><tbody>
    <?php foreach ($lessons as $lesson): ?><tr><td><?= date('d.m.Y H:i', strtotime($lesson['starts_at'])) ?></td><td><span class="status status-<?= e($lesson['status']) ?>"><?= e(match($lesson['status']) {'scheduled'=>'Запланировано','completed'=>'Проведено','student_cancelled'=>'Отменено вами','teacher_cancelled'=>'Отменено преподавателем',default=>$lesson['status']}) ?></span></td><td><?= $lesson['payment_status'] === 'paid' ? 'Оплачено' : 'Не оплачено' ?></td><td><?= $lesson['meeting_url'] ? 'Ссылка есть' : 'Ожидается' ?></td><td><a href="/student/lessons/<?= (int)$lesson['id'] ?>">Открыть →</a></td></tr><?php endforeach; ?>
    <?php if (!$lessons): ?><tr><td colspan="5" class="empty">Занятий пока нет.</td></tr><?php endif; ?>
    </tbody></table></div>
</section>
