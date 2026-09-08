<?php $cabinets = $cabinets ?? []; ?>
<div class="page-header">
    <h1><i class="fa-solid fa-building-shield"></i> Cabinets</h1>
</div>

<div class="stats-grid" style="grid-template-columns:repeat(3,1fr);margin-bottom:1.5rem">
    <div class="stat-card">
        <div class="stat-icon" style="background:var(--primary-light);color:var(--primary)"><i class="fa-solid fa-building"></i></div>
        <div class="stat-info"><span class="stat-value"><?= count($cabinets) ?></span><span class="stat-label">Cabinets au total</span></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#dcfce7;color:#15803d"><i class="fa-solid fa-circle-check"></i></div>
        <div class="stat-info"><span class="stat-value"><?= count(array_filter($cabinets, fn($c) => $c['status'] === 'active')) ?></span><span class="stat-label">Actifs</span></div>
    </div>
    <div class="stat-card">
        <div class="stat-icon" style="background:#fee2e2;color:#b91c1c"><i class="fa-solid fa-ban"></i></div>
        <div class="stat-info"><span class="stat-value"><?= count(array_filter($cabinets, fn($c) => $c['status'] === 'suspended')) ?></span><span class="stat-label">Suspendus</span></div>
    </div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>Cabinet</th><th>Identifiant</th><th>Base de données</th><th>Email de contact</th><th>Créé le</th><th>Statut</th><th></th></tr></thead>
            <tbody>
            <?php if (empty($cabinets)): ?>
                <tr><td colspan="7"><div class="empty-state-inline">Aucun cabinet enregistré.</div></td></tr>
            <?php endif; ?>
            <?php foreach ($cabinets as $c): ?>
                <tr>
                    <td><strong><?= e($c['name']) ?></strong></td>
                    <td><span class="code-badge"><?= e($c['slug']) ?></span></td>
                    <td style="font-family:monospace;font-size:0.8125rem"><?= e($c['db_name']) ?></td>
                    <td><?= e($c['contact_email'] ?? '-') ?></td>
                    <td><?= format_date($c['created_at'], 'd/m/Y') ?></td>
                    <td><?= $c['status'] === 'active' ? '<span class="badge badge-green">Actif</span>' : '<span class="badge badge-danger">Suspendu</span>' ?></td>
                    <td>
                        <button class="btn btn-ghost btn-sm" onclick="toggleCabinet(<?= $c['id'] ?>, this)">
                            <?= $c['status'] === 'active' ? '<i class="fa-solid fa-ban"></i> Suspendre' : '<i class="fa-solid fa-circle-check"></i> Réactiver' ?>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
async function toggleCabinet(id, btn) {
    btn.disabled = true;
    try {
        const res = await fetch('/api/platform/cabinets/' + id + '/toggle', {
            method: 'POST',
            headers: { 'X-CSRF-Token': csrfToken }
        });
        const data = await res.json();
        if (data.success) {
            showToast('Statut mis à jour', 'success');
            setTimeout(() => window.location.reload(), 500);
        } else {
            showToast(data.error || 'Erreur', 'error');
            btn.disabled = false;
        }
    } catch (err) {
        showToast('Erreur réseau', 'error');
        btn.disabled = false;
    }
}
</script>
