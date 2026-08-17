<?php $title = 'Домашнее задание'; ?>
<div class="page-head"><div><span class="eyebrow">Домашнее задание</span><h1><?= e($homework['title']) ?></h1><p><?= e($homework['first_name'] . ' ' . $homework['last_name']) ?> · урок <?= $homework['lesson_starts_at'] ? date('d.m.Y', strtotime($homework['lesson_starts_at'])) : '—' ?></p></div><a class="button button-secondary" href="/teacher/lessons/<?= (int)$homework['lesson_id'] ?>">К занятию</a></div>
<div class="content-grid content-grid-2">
    <section class="panel">
        <div class="panel-head"><h2>Задание</h2><span class="status status-<?= e($homework['status']) ?>"><?= e(match($homework['status']) {'assigned'=>'Задано','submitted'=>'На проверке','checked'=>'Проверено',default=>$homework['status']}) ?></span></div>
        <div class="rich-text"><?= nl2br(e($homework['description'])) ?></div>
        <h3>Файлы преподавателя</h3>
        <div class="file-list"><?php foreach ($files as $file): if ($file['uploaded_by'] !== 'teacher') continue; ?><a href="/files/<?= (int)$file['id'] ?>">📎 <?= e($file['original_name']) ?> <small><?= round($file['file_size']/1024, 1) ?> КБ</small></a><?php endforeach; ?></div>
    </section>
    <section class="panel">
        <div class="panel-head"><h2>Ответ ученика</h2></div>
        <?php if ($homework['student_comment']): ?><div class="rich-text"><?= nl2br(e($homework['student_comment'])) ?></div><?php else: ?><p class="empty">Комментарий пока не отправлен.</p><?php endif; ?>
        <div class="file-list"><?php foreach ($files as $file): if ($file['uploaded_by'] !== 'student') continue; ?><a href="/files/<?= (int)$file['id'] ?>">📎 <?= e($file['original_name']) ?> <small><?= round($file['file_size']/1024, 1) ?> КБ</small></a><?php endforeach; ?></div>
        <hr>
        <form method="post" action="/teacher/homework/<?= (int)$homework['id'] ?>/check" class="stack-form">
            <?= csrf_field() ?>
            <label class="field"><span>Комментарий после проверки</span><textarea name="teacher_feedback" rows="4" placeholder="Что исправить / что получилось хорошо"><?= e($homework['teacher_feedback']) ?></textarea></label>
            <button class="button" type="submit">Отметить как проверенное</button>
        </form>
    </section>
</div>
