<?php $medications = $medications ?? []; $alerts = $alerts ?? []; ?>
<div class="page-header">
    <h1><i class="fa-solid fa-pills"></i> Médicaments</h1>
    <?php if (can('medications.manage')): ?>
    <div class="page-header-actions">
        <a href="/medications/create" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Ajouter un médicament</a>
    </div>
    <?php endif; ?>
</div>

<?php if (!empty($alerts)): ?>
<div class="alert-section">
    <h3><i class="fa-solid fa-triangle-exclamation"></i> Alertes</h3>
    <div class="alert-cards">
        <?php foreach ($alerts as $a): ?>
        <div class="alert-card alert-<?= $a['alert_type'] ?>">
            <div class="alert-card-icon">
                <?php if ($a['alert_type'] === 'expired'): ?><i class="fa-solid fa-circle-xmark"></i>
                <?php elseif ($a['alert_type'] === 'low_stock'): ?><i class="fa-solid fa-box-open"></i>
                <?php else: ?><i class="fa-regular fa-clock"></i><?php endif; ?>
            </div>
            <div class="alert-card-info">
                <h4><?= e($a['name']) ?></h4>
                <p><?php if ($a['alert_type'] === 'expired'): ?>Expiré le <?= format_date($a['expiry_date']) ?>
                    <?php elseif ($a['alert_type'] === 'low_stock'): ?><?= $a['stock_quantity'] ?> unités restantes (seuil: <?= $a['stock_alert_level'] ?>)
                    <?php else: ?>Expire le <?= format_date($a['expiry_date']) ?><?php endif; ?></p>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
</div>
<?php endif; ?>

<div class="card">
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>Nom</th><th>Générique</th><th>Catégorie</th><th>Forme</th><th>Stock</th><th>Prix</th><th>Expiration</th><th>Statut</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($medications as $m): 
                    $isLow = $m['stock_quantity'] <= $m['stock_alert_level'];
                    $isExpiring = $m['expiry_date'] && strtotime($m['expiry_date']) <= strtotime('+3 months');
                    $isExpired = $m['expiry_date'] && strtotime($m['expiry_date']) <= time();
                ?>
                <tr class="<?= $isExpired ? 'row-danger' : ($isLow || $isExpiring ? 'row-warning' : '') ?>">
                    <td><strong><?= e($m['name']) ?></strong></td>
                    <td><?= e($m['generic_name'] ?: '-') ?></td>
                    <td><?= e($m['category'] ?: '-') ?></td>
                    <td><?= ucfirst($m['form']) ?></td>
                    <td><span class="stock-badge <?= $isLow ? 'stock-low' : 'stock-ok' ?>" id="stock-<?= $m['id'] ?>"><?= $m['stock_quantity'] ?></span></td>
                    <td><?= format_money($m['unit_price']) ?></td>
                    <td><?= format_date($m['expiry_date']) ?></td>
                    <td><?= $m['is_active'] ? '<span class="badge badge-green">Actif</span>' : '<span class="badge badge-gray">Inactif</span>' ?></td>
                    <td>
                        <div style="display:flex;gap:0.375rem;flex-wrap:wrap">
                            <button class="btn btn-ghost btn-sm" title="Réapprovisionner (+10)" onclick="adjustStock(<?= $m['id'] ?>, 10)"><i class="fa-solid fa-plus"></i></button>
                            <button class="btn btn-ghost btn-sm" title="Retirer (-1)" onclick="adjustStock(<?= $m['id'] ?>, -1)"><i class="fa-solid fa-minus"></i></button>
                            <?php if (can('medications.manage')): ?>
                            <a href="/medications/<?= $m['id'] ?>/edit" class="btn btn-sm btn-ghost" title="Modifier"><i class="fa-solid fa-pen"></i></a>
                            <a href="/settings/audit-logs?entity_type=medication&entity_id=<?= $m['id'] ?>" class="btn btn-sm btn-ghost" title="Historique"><i class="fa-solid fa-clock-rotate-left"></i></a>
                            <button type="button" class="btn btn-sm btn-ghost" title="Supprimer" onclick="doclyConfirmDelete('/medications/<?= $m['id'] ?>/delete', this.closest('tr'))"><i class="fa-solid fa-trash" style="color:var(--danger)"></i></button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
async function adjustStock(id, delta) {
    const formData = new FormData();
    formData.append('delta', delta);
    try {
        const res = await fetch('/api/medications/' + id + '/stock', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            const el = document.getElementById('stock-' + id);
            if (el) el.textContent = data.stock_quantity;
            showToast('Stock mis à jour', 'success');
        } else {
            showToast(data.error || 'Erreur', 'error');
        }
    } catch (err) {
        showToast('Erreur réseau', 'error');
    }
}
</script>
