<?php $title = 'Домашние задания'; ?>
<div class="page-head"><div><span class="eyebrow">Обучение</span><h1>Домашние задания</h1><p>Задания, отправленные работы и комментарии преподавателя.</p></div></div>
<section class="panel"><div class="homework-grid">
<?php foreach ($homeworkList as $hw): ?><a class="homework-card" href="/student/homework/<?= (int)$hw['id'] ?>"><div><span class="status status-<?= e($hw['status']) ?>"><?= e(match($hw['status']) {'assigned'=>'Нужно выполнить','submitted'=>'Отправлено','checked'=>'Проверено',default=>$hw['status']}) ?></span><h3><?= e($hw['title']) ?></h3><p><?= e(mb_strimwidth($hw['description'], 0, 110, '…')) ?></p></div><small><?= $hw['lesson_starts_at'] ? 'Урок ' . date('d.m.Y', strtotime($hw['lesson_starts_at'])) : '' ?></small></a><?php endforeach; ?>
<?php if (!$homeworkList): ?><p class="empty">Домашних заданий пока нет.</p><?php endif; ?>
</div></section>
