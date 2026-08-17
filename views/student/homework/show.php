<?php $title = $homework['title']; ?>
<div class="page-head"><div><span class="eyebrow">Домашнее задание</span><h1><?= e($homework['title']) ?></h1><p><?= $homework['lesson_starts_at'] ? 'К уроку ' . date('d.m.Y', strtotime($homework['lesson_starts_at'])) : '' ?></p></div><a class="button button-secondary" href="/student/homework">Все задания</a></div>
<div class="content-grid content-grid-2">
    <section class="panel">
        <div class="panel-head"><h2>Что нужно сделать</h2><span class="status status-<?= e($homework['status']) ?>"><?= e(match($homework['status']) {'assigned'=>'Нужно выполнить','submitted'=>'Отправлено','checked'=>'Проверено',default=>$homework['status']}) ?></span></div>
        <div class="rich-text"><?= nl2br(e($homework['description'])) ?></div>
        <div class="file-list"><?php foreach ($files as $file): if ($file['uploaded_by'] !== 'teacher') continue; ?><a href="/files/<?= (int)$file['id'] ?>">📎 <?= e($file['original_name']) ?> <small><?= round($file['file_size']/1024, 1) ?> КБ</small></a><?php endforeach; ?></div>
        <?php if ($homework['teacher_feedback']): ?><div class="feedback-box"><strong>Комментарий преподавателя</strong><p><?= nl2br(e($homework['teacher_feedback'])) ?></p></div><?php endif; ?>
    </section>
    <section class="panel">
        <div class="panel-head"><h2>Моя работа</h2></div>
        <?php if ($homework['student_comment']): ?><div class="rich-text"><strong>Последний комментарий:</strong><br><?= nl2br(e($homework['student_comment'])) ?></div><?php endif; ?>
        <div class="file-list"><?php foreach ($files as $file): if ($file['uploaded_by'] !== 'student') continue; ?><a href="/files/<?= (int)$file['id'] ?>">📎 <?= e($file['original_name']) ?> <small><?= round($file['file_size']/1024, 1) ?> КБ</small></a><?php endforeach; ?></div>
        <form class="stack-form space-top-small" method="post" action="/student/homework/<?= (int)$homework['id'] ?>/submit" enctype="multipart/form-data">
            <?= csrf_field() ?>
            <label class="field"><span>Комментарий</span><textarea name="student_comment" rows="4" placeholder="Напишите, что вы сделали или что было сложно..."></textarea></label>
            <label class="field"><span>Прикрепить файлы</span><input type="file" name="files[]" multiple accept=".doc,.docx,.jpg,.jpeg,.png,.webp"><small>DOC, DOCX, JPG, PNG, WEBP · до 10 МБ каждый</small></label>
            <button class="button" type="submit">Отправить преподавателю</button>
        </form>
    </section>
</div>
