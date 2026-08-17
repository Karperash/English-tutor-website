<?php $title = $student['first_name'] . ' ' . $student['last_name']; ?>
<div class="page-head">
    <div><span class="eyebrow">Ученик</span><h1><?= e($student['first_name'] . ' ' . $student['last_name']) ?></h1><p>@<?= e($student['login']) ?></p></div>
    <div class="head-actions"><a class="button button-secondary" href="/teacher/students/<?= (int)$student['id'] ?>/edit">Редактировать</a><a class="button" href="/teacher/schedule">Расписание</a></div>
</div>
<div class="content-grid content-grid-2">
    <section class="panel">
        <div class="panel-head"><h2>Контакты</h2></div>
        <dl class="details-list">
            <div><dt>Телефон ученика</dt><dd><?= e($student['phone']) ?></dd></div>
            <div><dt>Родитель</dt><dd><?= e($student['parent_name']) ?></dd></div>
            <div><dt>Телефон родителя</dt><dd><?= e($student['parent_phone']) ?></dd></div>
        </dl>
        <form class="danger-zone" method="post" action="/teacher/students/<?= (int)$student['id'] ?>/delete" onsubmit="return confirm('Удалить ученика? Вход будет закрыт, история сохранится.');">
            <?= csrf_field() ?><button class="button button-danger button-small" type="submit">Удалить ученика</button>
        </form>
    </section>

    <section class="panel">
        <div class="panel-head"><h2>Заметки преподавателя</h2></div>
        <form method="post" action="/teacher/students/<?= (int)$student['id'] ?>/notes" class="stack-form">
            <?= csrf_field() ?>
            <label class="field"><span>Что повторить / ошибки</span><textarea name="content" rows="4" required placeholder="Например: повторить do/does, ошибки в вопросах..."></textarea></label>
            <label class="field"><span>Связать с занятием (необязательно)</span><select name="lesson_id"><option value="">Без привязки</option><?php foreach ($lessons as $lesson): ?><option value="<?= (int)$lesson['id'] ?>"><?= date('d.m.Y H:i', strtotime($lesson['starts_at'])) ?></option><?php endforeach; ?></select></label>
            <button class="button button-small" type="submit">Добавить заметку</button>
        </form>
        <div class="note-list">
            <?php foreach ($notes as $note): ?><article class="note"><small><?= date('d.m.Y H:i', strtotime($note['created_at'])) ?><?= $note['lesson_starts_at'] ? ' · урок ' . date('d.m.Y', strtotime($note['lesson_starts_at'])) : '' ?></small><p><?= nl2br(e($note['content'])) ?></p></article><?php endforeach; ?>
            <?php if (!$notes): ?><p class="empty">Заметок пока нет.</p><?php endif; ?>
        </div>
    </section>
</div>
<section class="panel space-top">
    <div class="panel-head"><h2>История занятий</h2></div>
    <div class="table-wrap"><table><thead><tr><th>Дата</th><th>Статус</th><th>Оплата</th><th></th></tr></thead><tbody>
    <?php foreach ($lessons as $lesson): ?><tr><td><?= date('d.m.Y H:i', strtotime($lesson['starts_at'])) ?></td><td><span class="status status-<?= e($lesson['status']) ?>"><?= e(match($lesson['status']) {'scheduled'=>'Запланировано','completed'=>'Проведено','student_cancelled'=>'Отменено учеником','teacher_cancelled'=>'Отменено учителем',default=>$lesson['status']}) ?></span></td><td><?= $lesson['payment_status'] === 'paid' ? 'Оплачено' : 'Не оплачено' ?></td><td><a href="/teacher/lessons/<?= (int)$lesson['id'] ?>">Открыть →</a></td></tr><?php endforeach; ?>
    <?php if (!$lessons): ?><tr><td colspan="4" class="empty">Занятий пока нет.</td></tr><?php endif; ?>
    </tbody></table></div>
</section>
