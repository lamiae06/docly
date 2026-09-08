<?php $invoice = $invoice ?? []; $items = $items ?? []; $payments = $payments ?? []; ?>
<div class="page-header">
    <div class="header-back">
        <a href="/billing" class="btn btn-ghost btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <h1><i class="fa-solid fa-file-invoice-dollar"></i> Facture <?= e($invoice['invoice_number']) ?></h1>
    </div>
    <div class="page-header-actions">
        <?php $statusMap = ['paid'=>'badge-green','partially_paid'=>'badge-amber','pending'=>'badge-gray','overdue'=>'badge-danger','cancelled'=>'badge-gray']; ?>
        <span class="badge <?= $statusMap[$invoice['status']] ?? 'badge-gray' ?>"><?= ucfirst(str_replace('_',' ',$invoice['status'])) ?></span>
        <?php if ((float)$invoice['balance_due'] > 0): ?>
        <button class="btn btn-primary btn-sm" id="payBtn"><i class="fa-solid fa-money-bill"></i> Marquer comme payée</button>
        <?php endif; ?>
    </div>
</div>

<div class="card" style="padding:1.5rem;margin-bottom:1.5rem">
    <div class="overview-grid">
        <div class="info-card"><h4><i class="fa-solid fa-user"></i> Patient</h4><p><?= e($invoice['patient_first_name'].' '.$invoice['patient_last_name']) ?> (<?= e($invoice['patient_code']) ?>)</p></div>
        <div class="info-card"><h4><i class="fa-regular fa-calendar"></i> Émise le</h4><p><?= format_date($invoice['issue_date']) ?></p></div>
        <div class="info-card"><h4><i class="fa-regular fa-clock"></i> Échéance</h4><p><?= format_date($invoice['due_date']) ?></p></div>
        <div class="info-card"><h4><i class="fa-solid fa-sack-dollar"></i> Solde dû</h4><p style="font-weight:700"><?= format_money((float)$invoice['balance_due']) ?></p></div>
    </div>
</div>

<div class="card" style="margin-bottom:1.5rem">
    <div class="card-header"><h3><i class="fa-solid fa-list"></i> Détails</h3></div>
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>Description</th><th>Qté</th><th>Prix unitaire</th><th>Total</th></tr></thead>
            <tbody>
            <?php foreach ($items as $it): ?>
                <tr>
                    <td><?= e($it['description']) ?></td>
                    <td><?= (int) $it['quantity'] ?></td>
                    <td><?= format_money((float)$it['unit_price']) ?></td>
                    <td><?= format_money((float)$it['total_price']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <div style="padding:1rem 1.25rem;border-top:1px solid var(--border);display:flex;flex-direction:column;gap:0.375rem;align-items:flex-end">
        <span>Sous-total : <?= format_money((float)$invoice['subtotal']) ?></span>
        <span>TVA : <?= format_money((float)$invoice['tax_amount']) ?></span>
        <span style="font-weight:700;font-size:1.0625rem">Total : <?= format_money((float)$invoice['total_amount']) ?></span>
    </div>
</div>

<div class="card">
    <div class="card-header"><h3><i class="fa-solid fa-money-check-dollar"></i> Paiements</h3></div>
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>Date</th><th>Montant</th><th>Méthode</th></tr></thead>
            <tbody>
            <?php if (empty($payments)): ?>
                <tr><td colspan="3"><div class="empty-state-inline">Aucun paiement enregistré</div></td></tr>
            <?php endif; ?>
            <?php foreach ($payments as $p): ?>
                <tr>
                    <td><?= format_date($p['payment_date']) ?></td>
                    <td><?= format_money((float)$p['amount']) ?></td>
                    <td><?= ucfirst($p['payment_method']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
const payBtn = document.getElementById('payBtn');
if (payBtn) {
    payBtn.addEventListener('click', async () => {
        payBtn.disabled = true;
        try {
            const res = await fetch('/api/billing/<?= $invoice['id'] ?>/pay', { method: 'POST' });
            const data = await res.json();
            if (data.success) {
                showToast('Paiement enregistré', 'success');
                setTimeout(() => window.location.reload(), 700);
            } else {
                showToast(data.error || 'Erreur', 'error');
                payBtn.disabled = false;
            }
        } catch (err) {
            showToast('Erreur réseau', 'error');
            payBtn.disabled = false;
        }
    });
}
</script>
