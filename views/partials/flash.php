<?php if ($message = flash('success')): ?>
    <div class="flash-wrap"><div class="flash flash-success"><?= e($message) ?></div></div>
<?php endif; ?>
<?php if ($message = flash('error')): ?>
    <div class="flash-wrap"><div class="flash flash-error"><?= e($message) ?></div></div>
<?php endif; ?>
