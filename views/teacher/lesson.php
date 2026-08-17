<?php $title = 'Занятие'; ?>
<div class="page-head">
    <div><span class="eyebrow">Занятие</span><h1><?= e($lesson['first_name'] . ' ' . $lesson['last_name']) ?></h1><p><?= date('d.m.Y · H:i', strtotime($lesson['starts_at'])) ?>—<?= date('H:i', strtotime($lesson['ends_at'])) ?></p></div>
    <a class="button button-secondary" href="/teacher/students/<?= (int)$lesson['student_id'] ?>">Карточка ученика</a>
</div>
<div class="content-grid content-grid-2">
    <section class="panel">
        <div class="panel-head"><h2>Параметры занятия</h2></div>
        <form class="stack-form" method="post" action="/teacher/lessons/<?= (int)$lesson['id'] ?>/update">
            <?= csrf_field() ?>
            <label class="field"><span>Ссылка на подключение</span><input type="url" name="meeting_url" value="<?= e($lesson['meeting_url']) ?>" placeholder="https://meet.google.com/..."></label>
            <label class="field"><span>Платформа / комментарий</span><textarea name="meeting_comment" rows="3" placeholder="Например: Google Meet. Подключайся за 5 минут."><?= e($lesson['meeting_comment']) ?></textarea></label>
            <div class="form-grid compact-grid">
                <label class="field"><span>Оплата</span><select name="payment_status"><option value="unpaid" <?= selected($lesson['payment_status'], 'unpaid') ?>>Не оплачено</option><option value="paid" <?= selected($lesson['payment_status'], 'paid') ?>>Оплачено</option></select></label>
                <label class="field"><span>Статус</span><select name="status" <?= !in_array($lesson['status'], ['scheduled','completed'], true) ? 'disabled' : '' ?>><option value="scheduled" <?= selected($lesson['status'], 'scheduled') ?>>Запланировано</option><option value="completed" <?= selected($lesson['status'], 'completed') ?>>Проведено</option></select><?php if (!in_array($lesson['status'], ['scheduled','completed'], true)): ?><input type="hidden" name="status" value="completed"><small>Занятие отменено; основные поля доступны только для просмотра истории.</small><?php endif; ?></label>
            </div>
            <?php if (in_array($lesson['status'], ['scheduled','completed'], true)): ?><button class="button" type="submit">Сохранить</button><?php endif; ?>
        </form>
    </section>

    <section class="panel">
        <div class="panel-head"><h2>Перенос и отмена</h2></div>
        <?php if ($lesson['status'] === 'scheduled'): ?>
            <form class="stack-form" method="post" action="/teacher/lessons/<?= (int)$lesson['id'] ?>/move">
                <?= csrf_field() ?>
                <label class="field"><span>Новое свободное время</span><select name="slot_id" required><option value="">Выберите слот</option><?php foreach ($freeSlots as $slot): ?><option value="<?= (int)$slot['id'] ?>"><?= date('d.m.Y H:i', strtotime($slot['starts_at'])) ?>—<?= date('H:i', strtotime($slot['ends_at'])) ?></option><?php endforeach; ?></select></label>
                <button class="button button-secondary" type="submit">Перенести занятие</button>
            </form>
            <hr>
            <form method="post" action="/teacher/lessons/<?= (int)$lesson['id'] ?>/cancel" onsubmit="return confirm('Отменить занятие? Ученик получит уведомление.');"><?= csrf_field() ?><button class="button button-danger" type="submit">Отменить занятие</button></form>
        <?php else: ?>
            <p class="empty">Перенос и отмена недоступны для этого статуса.</p>
        <?php endif; ?>
    </section>
</div>

<section class="panel space-top">
    <div class="panel-head"><h2>Домашнее задание</h2></div>
    <?php if ($homeworkList): ?>
        <div class="list"><?php foreach ($homeworkList as $hw): ?><a class="list-row" href="/teacher/homework/<?= (int)$hw['id'] ?>"><div><strong><?= e($hw['title']) ?></strong><small><?= e(match($hw['status']) {'assigned'=>'Задано','submitted'=>'На проверке','checked'=>'Проверено',default=>$hw['status']}) ?></small></div><span class="row-arrow">→</span></a><?php endforeach; ?></div>
    <?php endif; ?>
    <form class="stack-form space-top-small" method="post" action="/teacher/lessons/<?= (int)$lesson['id'] ?>/homework" enctype="multipart/form-data">
        <?= csrf_field() ?>
        <label class="field"><span>Название</span><input name="title" required placeholder="Например: Unit 8"></label>
        <label class="field"><span>Задание</span><textarea name="description" rows="5" required placeholder="Упражнения, слова, что повторить..."></textarea></label>
        <label class="field"><span>Файлы (DOC, DOCX, JPG, PNG, WEBP)</span><input type="file" name="files[]" multiple accept=".doc,.docx,.jpg,.jpeg,.png,.webp"></label>
        <button class="button" type="submit">Выдать домашнее задание</button>
    </form>
</section>

<?php if ($history): ?><section class="panel space-top"><div class="panel-head"><h2>История изменений</h2></div><div class="timeline"><?php foreach ($history as $item): ?><div class="timeline-item"><strong><?= e($item['comment']) ?></strong><small><?= date('d.m.Y H:i', strtotime($item['created_at'])) ?></small><?php if ($item['old_starts_at'] || $item['new_starts_at']): ?><span><?= $item['old_starts_at'] ? date('d.m H:i', strtotime($item['old_starts_at'])) : '—' ?> → <?= $item['new_starts_at'] ? date('d.m H:i', strtotime($item['new_starts_at'])) : '—' ?></span><?php endif; ?></div><?php endforeach; ?></div></section><?php endif; ?>
