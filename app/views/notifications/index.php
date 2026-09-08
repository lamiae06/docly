<?php $notifications = $notifications ?? []; ?>
<div class="page-header">
    <h1><i class="fa-solid fa-bell"></i> Notifications</h1>
    <div class="page-header-actions">
        <button class="btn btn-ghost btn-sm" onclick="markAllRead(); setTimeout(()=>window.location.reload(),400)"><i class="fa-solid fa-check-double"></i> Tout marquer comme lu</button>
    </div>
</div>

<div class="card">
    <?php if (empty($notifications)): ?>
        <div class="empty-state"><i class="fa-regular fa-bell-slash"></i><p>Aucune notification</p></div>
    <?php else: ?>
    <div class="notif-list" style="max-height:none">
        <?php foreach ($notifications as $n): ?>
        <div class="notif-item <?= $n['is_read'] ? 'read' : 'unread' ?>" onclick="window.location='<?= e($n['link'] ?: '#') ?>'" style="cursor:pointer">
            <div class="notif-title"><?= e($n['title']) ?></div>
            <div class="notif-message"><?= e($n['message']) ?></div>
            <div class="notif-time"><?= format_date($n['created_at'], 'd/m/Y H:i') ?></div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>
