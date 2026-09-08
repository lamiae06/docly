<?php
$logs = $logs ?? [];
$filterEntityType = $filterEntityType ?? '';
$filterEntityId = $filterEntityId ?? 0;
$filterModule = $filterModule ?? '';
$hasFilter = $filterEntityType !== '' || $filterEntityId > 0 || $filterModule !== '';
?>
<div class="page-header">
    <div class="header-back">
        <a href="/settings" class="btn btn-ghost btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <h1><i class="fa-solid fa-list-check"></i> Logs d'audit</h1>
    </div>
</div>

<?php if ($hasFilter): ?>
<div class="alert-item" style="margin-bottom:1rem;display:flex;align-items:center;justify-content:space-between">
    <span>
        <i class="fa-solid fa-filter"></i>
        Historique filtré<?= $filterEntityType !== '' ? ' — ' . e($filterEntityType) : '' ?><?= $filterEntityId > 0 ? ' #' . (int) $filterEntityId : '' ?>
    </span>
    <a href="/settings/audit-logs" class="btn btn-ghost btn-sm">Voir tout l'historique</a>
</div>
<?php endif; ?>

<div class="card">
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>Date</th><th>Action</th><th>Module</th><th>Description</th><th>IP</th></tr></thead>
            <tbody>
            <?php if (empty($logs)): ?>
                <tr><td colspan="5"><div class="empty-state-inline">Aucun log pour le moment.</div></td></tr>
            <?php endif; ?>
            <?php foreach ($logs as $log): ?>
                <?php
                    $actionColors = ['create' => 'badge-green', 'update' => 'badge-amber', 'delete' => 'badge-danger', 'login' => 'badge-blue', 'logout' => 'badge-gray'];
                    $badgeClass = $actionColors[$log['action']] ?? 'badge-gray';
                ?>
                <tr>
                    <td><?= format_date($log['created_at'], 'd/m/Y H:i') ?></td>
                    <td><span class="badge <?= $badgeClass ?>"><?= e(ucfirst($log['action'])) ?></span></td>
                    <td><?= e($log['module'] ?? '-') ?></td>
                    <td><?= e($log['description'] ?? '-') ?></td>
                    <td><?= e($log['ip_address'] ?? '-') ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
