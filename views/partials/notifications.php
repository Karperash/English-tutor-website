<?php $title = 'Уведомления'; ?>
<div class="page-head"><div><span class="eyebrow">События</span><h1>Уведомления</h1></div><form method="post" action="/notifications/read-all"><?= csrf_field() ?><button class="button button-secondary" type="submit">Отметить все прочитанными</button></form></div>
<section class="panel">
    <div class="notification-list">
        <?php foreach ($notifications as $notification): ?>
            <?php $tag = $notification['link'] ? 'a' : 'div'; ?>
            <<?= $tag ?> class="notification-item <?= $notification['is_read'] ? '' : 'is-unread' ?>" <?= $notification['link'] ? 'href="' . e($notification['link']) . '"' : '' ?>>
                <div class="notification-dot"></div>
                <div><strong><?= e($notification['message']) ?></strong><small><?= date('d.m.Y H:i', strtotime($notification['created_at'])) ?></small></div>
                <?php if ($notification['link']): ?><span class="row-arrow">→</span><?php endif; ?>
            </<?= $tag ?>>
        <?php endforeach; ?>
        <?php if (!$notifications): ?><p class="empty">Уведомлений пока нет.</p><?php endif; ?>
    </div>
</section>
