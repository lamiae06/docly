<?php $patients = $patients ?? []; $taxRate = $taxRate ?? 20; ?>
<div class="page-header">
    <div class="header-back">
        <a href="/billing" class="btn btn-ghost btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <h1><i class="fa-solid fa-file-invoice-dollar"></i> Nouvelle facture</h1>
    </div>
</div>

<div class="card">
    <form id="invoiceForm" method="post" action="/billing/store" style="padding:1.5rem">
        <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
        <div class="form-grid">
            <div class="form-group">
                <label>Patient *</label>
                <select name="patient_id" required>
                    <option value="">Sélectionner un patient</option>
                    <?php foreach ($patients as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= e($p['first_name'].' '.$p['last_name'].' ('.$p['patient_code'].')') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Date d'échéance *</label>
                <input type="date" name="due_date" required value="<?= date('Y-m-d', strtotime('+30 days')) ?>">
            </div>
        </div>

        <h3 class="section-title">Lignes de facturation</h3>
        <div id="itemsList"></div>
        <button type="button" class="btn btn-ghost btn-sm" id="addItemBtn" style="margin-top:0.5rem">
            <i class="fa-solid fa-plus"></i> Ajouter une ligne
        </button>

        <div class="form-grid" style="margin-top:1.5rem">
            <div class="form-group">
                <label>Remise (<?= e(currency_symbol()) ?>)</label>
                <input type="number" name="discount_amount" id="discountInput" step="0.01" min="0" value="0">
            </div>
            <div class="form-group">
                <label>TVA (%)</label>
                <input type="number" name="tax_rate" id="taxRateInput" step="0.1" min="0" value="<?= e($taxRate) ?>">
            </div>
        </div>

        <div style="padding:1rem 0;border-top:1px solid var(--border);margin-top:1rem;display:flex;flex-direction:column;gap:0.375rem;align-items:flex-end">
            <span>Sous-total : <strong id="sumSubtotal">0,00 <?= e(currency_symbol()) ?></strong></span>
            <span>Remise : <strong id="sumDiscount">0,00 <?= e(currency_symbol()) ?></strong></span>
            <span>TVA : <strong id="sumTax">0,00 <?= e(currency_symbol()) ?></strong></span>
            <span style="font-size:1.125rem">Total : <strong id="sumTotal">0,00 <?= e(currency_symbol()) ?></strong></span>
        </div>

        <div class="form-group full" style="margin-top:1rem">
            <label>Notes</label>
            <textarea name="notes" rows="2"></textarea>
        </div>

        <div class="form-actions">
            <a href="/billing" class="btn btn-ghost">Annuler</a>
            <button type="submit" class="btn btn-primary" id="submitBtn"><i class="fa-solid fa-check"></i> Créer la facture</button>
        </div>
    </form>
</div>

<script>
let itemCount = 0;
const fmt = (n) => doclyFormatMoney(n || 0);

function recalculate() {
    let subtotal = 0;
    document.querySelectorAll('#itemsList .invoice-item').forEach(row => {
        const qty = parseFloat(row.querySelector('.item-qty').value) || 0;
        const price = parseFloat(row.querySelector('.item-price').value) || 0;
        const lineTotal = qty * price;
        row.querySelector('.item-total').textContent = fmt(lineTotal);
        subtotal += lineTotal;
    });
    const discount = Math.min(parseFloat(document.getElementById('discountInput').value) || 0, subtotal);
    const taxRate = parseFloat(document.getElementById('taxRateInput').value) || 0;
    const taxable = subtotal - discount;
    const tax = taxable * (taxRate / 100);
    const total = taxable + tax;

    document.getElementById('sumSubtotal').textContent = fmt(subtotal);
    document.getElementById('sumDiscount').textContent = fmt(discount);
    document.getElementById('sumTax').textContent = fmt(tax);
    document.getElementById('sumTotal').textContent = fmt(total);
}

function addItemRow() {
    const idx = itemCount++;
    const row = document.createElement('div');
    row.className = 'form-grid invoice-item';
    row.style.cssText = 'border:1px solid var(--border);border-radius:var(--radius-sm);padding:1rem;margin-bottom:0.75rem;position:relative;align-items:end';
    row.innerHTML = `
        <div class="form-group full">
            <label>Description *</label>
            <input type="text" name="items[${idx}][description]" required placeholder="Ex : Consultation générale">
        </div>
        <div class="form-group"><label>Quantité</label><input type="number" class="item-qty" name="items[${idx}][quantity]" min="1" value="1"></div>
        <div class="form-group"><label>Prix unitaire (${window.DOCLY_CURRENCY_SYMBOL || '€'})</label><input type="number" class="item-price" name="items[${idx}][unit_price]" step="0.01" min="0" value="0"></div>
        <div class="form-group"><label>Total</label><div class="item-total" style="padding:0.7rem 0;font-weight:600">${doclyFormatMoney(0)}</div></div>
        <button type="button" class="btn btn-ghost btn-sm remove-item" style="position:absolute;top:0.5rem;right:0.5rem"><i class="fa-solid fa-xmark"></i></button>
    `;
    row.querySelector('.remove-item').addEventListener('click', () => { row.remove(); recalculate(); });
    row.querySelectorAll('.item-qty, .item-price').forEach(el => el.addEventListener('input', recalculate));
    document.getElementById('itemsList').appendChild(row);
}

document.getElementById('addItemBtn').addEventListener('click', addItemRow);
document.getElementById('discountInput').addEventListener('input', recalculate);
document.getElementById('taxRateInput').addEventListener('input', recalculate);
addItemRow();
recalculate();

document.getElementById('invoiceForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    if (document.querySelectorAll('#itemsList .invoice-item').length === 0) {
        showToast('Ajoutez au moins une ligne', 'error');
        return;
    }
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    const formData = new FormData(this);
    try {
        const data = await doclySubmitForm('/billing/store', formData);
        if (data.success) {
            showToast('Facture créée', 'success');
            setTimeout(() => window.location.href = data.redirect || '/billing', 500);
        } else {
            let msg = data.error || 'Erreur lors de la création';
            if (data.errors) msg = Object.values(data.errors).join(' ');
            showToast(msg, 'error');
            btn.disabled = false;
        }
    } catch (err) {
        showToast(err.message || 'Erreur réseau', 'error');
        btn.disabled = false;
    }
});
</script>
