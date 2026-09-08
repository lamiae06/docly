<?php $invoices = $invoices ?? ['data' => [], 'total' => 0]; ?>
<div class="page-header">
    <h1><i class="fa-solid fa-file-invoice-dollar"></i> Facturation</h1>
    <?php if (can('billing.manage')): ?>
    <div class="page-header-actions">
        <a href="/billing/create" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Nouvelle facture</a>
    </div>
    <?php endif; ?>
</div>

<div class="stats-grid" style="grid-template-columns: repeat(3, 1fr); margin-bottom: 1.5rem;">
    <div class="stat-card"><div class="stat-icon" style="background:#dbeafe;color:#1d4ed8"><i class="fa-solid fa-sack-dollar"></i></div><div class="stat-info"><span class="stat-value" id="monthRevenue">--</span><span class="stat-label">Revenus du mois</span></div></div>
    <div class="stat-card"><div class="stat-icon" style="background:#fef3c7;color:#d97706"><i class="fa-solid fa-clock"></i></div><div class="stat-info"><span class="stat-value" id="pendingCount">--</span><span class="stat-label">En attente</span></div></div>
    <div class="stat-card"><div class="stat-icon" style="background:#fee2e2;color:#b91c1c"><i class="fa-solid fa-triangle-exclamation"></i></div><div class="stat-info"><span class="stat-value" id="overdueCount">--</span><span class="stat-label">En retard</span></div></div>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>N° Facture</th><th>Patient</th><th>Date</th><th>Échéance</th><th>Montant</th><th>Payé</th><th>Solde</th><th>Statut</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($invoices['data'] as $inv): ?>
                <tr onclick="window.location='/invoices/<?= $inv['id'] ?>'" style="cursor:pointer">
                    <td><span class="code-badge"><?= e($inv['invoice_number']) ?></span></td>
                    <td><a href="/patients/<?= $inv['patient_id'] ?>" onclick="event.stopPropagation()"><?= e($inv['patient_first_name'].' '.$inv['patient_last_name']) ?></a></td>
                    <td><?= format_date($inv['issue_date']) ?></td>
                    <td><?= format_date($inv['due_date']) ?></td>
                    <td><?= format_money($inv['total_amount']) ?></td>
                    <td><?= format_money($inv['paid_amount']) ?></td>
                    <td><?= format_money($inv['balance_due']) ?></td>
                    <td><?= invoice_status_badge($inv['status']) ?></td>
                    <td onclick="event.stopPropagation()">
                        <?php if (can('billing.manage')): ?>
                        <div style="display:flex;gap:0.375rem">
                            <?php if ((float) $inv['paid_amount'] <= 0): ?>
                            <a href="/billing/<?= $inv['id'] ?>/edit" class="btn btn-sm btn-ghost" title="Modifier"><i class="fa-solid fa-pen"></i></a>
                            <button type="button" class="btn btn-sm btn-ghost" title="Supprimer" onclick="doclyConfirmDelete('/billing/<?= $inv['id'] ?>/delete', this.closest('tr'))"><i class="fa-solid fa-trash" style="color:var(--danger)"></i></button>
                            <?php endif; ?>
                            <a href="/settings/audit-logs?entity_type=invoice&entity_id=<?= $inv['id'] ?>" class="btn btn-sm btn-ghost" title="Historique"><i class="fa-solid fa-clock-rotate-left"></i></a>
                        </div>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
fetch('/api/billing/stats').then(r => r.json()).then(data => {
    document.getElementById('monthRevenue').textContent = doclyFormatMoney(data.monthly_revenue || 0);
    document.getElementById('pendingCount').textContent = data.pending_count || 0;
    document.getElementById('overdueCount').textContent = (data.overdue || []).length;
});
</script>
