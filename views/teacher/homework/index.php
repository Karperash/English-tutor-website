<?php $title = 'Домашние задания'; ?>
<div class="page-head"><div><span class="eyebrow">Обучение</span><h1>Домашние задания</h1><p>Все задания и отправленные работы учеников.</p></div></div>
<section class="panel">
    <div class="table-wrap"><table><thead><tr><th>Ученик</th><th>Задание</th><th>Урок</th><th>Статус</th><th></th></tr></thead><tbody>
    <?php foreach ($homeworkList as $hw): ?><tr><td><?= e($hw['first_name'] . ' ' . $hw['last_name']) ?></td><td><?= e($hw['title']) ?></td><td><?= $hw['lesson_starts_at'] ? date('d.m.Y', strtotime($hw['lesson_starts_at'])) : '—' ?></td><td><span class="status status-<?= e($hw['status']) ?>"><?= e(match($hw['status']) {'assigned'=>'Задано','submitted'=>'На проверке','checked'=>'Проверено',default=>$hw['status']}) ?></span></td><td><a href="/teacher/homework/<?= (int)$hw['id'] ?>">Открыть →</a></td></tr><?php endforeach; ?>
    <?php if (!$homeworkList): ?><tr><td colspan="5" class="empty">Домашних заданий пока нет.</td></tr><?php endif; ?>
    </tbody></table></div>
</section>
