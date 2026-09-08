<?php $roles = $roles ?? []; ?>
<div class="page-header">
    <div class="header-back">
        <a href="/settings" class="btn btn-ghost btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <h1><i class="fa-solid fa-user-shield"></i> Rôles & Permissions</h1>
    </div>
</div>

<div class="doctors-grid">
    <?php foreach ($roles as $r): ?>
    <a href="/settings/roles/<?= (int) $r['id'] ?>" class="doctor-card" style="text-decoration:none;color:inherit;cursor:pointer;display:block">
        <div class="doctor-header">
            <div class="doctor-avatar" style="background:<?= e($r['color']) ?>"><i class="fa-solid fa-user-shield"></i></div>
            <div class="doctor-info">
                <h3><?= e($r['name']) ?></h3>
                <span class="doctor-specialty"><?= e($r['description'] ?? '') ?></span>
            </div>
        </div>
        <div class="doctor-stats">
            <div class="dstat"><span class="dstat-value"><?= (int) $r['permission_count'] ?></span><span class="dstat-label">Permissions</span></div>
            <div class="dstat"><span class="dstat-value"><?= (int) $r['user_count'] ?></span><span class="dstat-label">Utilisateurs</span></div>
        </div>
    </a>
    <?php endforeach; ?>
</div>
<p class="empty-state-inline" style="margin-top:1rem">Cliquez sur un rôle pour choisir précisément les pages et actions auxquelles il donne accès.</p>
