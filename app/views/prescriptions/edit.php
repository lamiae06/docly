<?php $prescription = $prescription ?? []; $items = $items ?? []; $patients = $patients ?? []; $doctors = $doctors ?? []; $medications = $medications ?? []; ?>
<div class="page-header">
    <div class="header-back">
        <a href="/prescriptions" class="btn btn-ghost btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <h1><i class="fa-solid fa-prescription"></i> Modifier l'ordonnance</h1>
    </div>
</div>

<div class="card">
    <form id="prescriptionForm" method="post" action="/prescriptions/<?= (int) $prescription['id'] ?>/update" style="padding:1.5rem">
        <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
        <div class="form-grid">
            <div class="form-group">
                <label>Patient *</label>
                <select name="patient_id" required>
                    <?php foreach ($patients as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $p['id'] == $prescription['patient_id'] ? 'selected' : '' ?>><?= e($p['first_name'].' '.$p['last_name'].' ('.$p['patient_code'].')') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label>Médecin *</label>
                <select name="doctor_id" required>
                    <?php foreach ($doctors as $d): ?>
                    <option value="<?= $d['id'] ?>" <?= $d['id'] == $prescription['doctor_id'] ? 'selected' : '' ?>>Dr. <?= e($d['first_name'].' '.$d['last_name'].' - '.$d['specialty']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <h3 class="section-title">Médicaments</h3>
        <div id="itemsList"></div>
        <button type="button" class="btn btn-ghost btn-sm" id="addItemBtn" style="margin-top:0.5rem">
            <i class="fa-solid fa-plus"></i> Ajouter un médicament
        </button>

        <div class="form-group full" style="margin-top:1.5rem">
            <label>Notes</label>
            <textarea name="notes" rows="2"><?= e($prescription['notes'] ?? '') ?></textarea>
        </div>

        <div class="form-actions">
            <a href="/prescriptions" class="btn btn-ghost">Annuler</a>
            <button type="submit" class="btn btn-primary" id="submitBtn"><i class="fa-solid fa-check"></i> Enregistrer</button>
        </div>
    </form>
</div>

<datalist id="medicationOptions">
    <?php foreach ($medications as $m): ?>
    <option value="<?= e($m['name']) ?>" data-id="<?= $m['id'] ?>"></option>
    <?php endforeach; ?>
</datalist>

<script>
let itemCount = 0;
const existingItems = <?= json_encode(array_map(function($it) {
    return [
        'medication_id' => $it['medication_id'],
        'medication_name' => $it['medication_name'],
        'dosage' => $it['dosage'],
        'frequency' => $it['frequency'],
        'duration' => $it['duration'],
        'quantity' => $it['quantity'],
        'instructions' => $it['instructions'],
    ];
}, $items), JSON_UNESCAPED_UNICODE) ?>;

function addItemRow(existing) {
    existing = existing || {};
    const idx = itemCount++;
    const row = document.createElement('div');
    row.className = 'form-grid';
    row.style.cssText = 'border:1px solid var(--border);border-radius:var(--radius-sm);padding:1rem;margin-bottom:0.75rem;position:relative';
    row.innerHTML = `
        <div class="form-group full">
            <label>Médicament *</label>
            <input type="text" name="items[${idx}][medication_name]" list="medicationOptions" required placeholder="Nom du médicament" value="${existing.medication_name || ''}">
        </div>
        <div class="form-group"><label>Dosage *</label><input type="text" name="items[${idx}][dosage]" placeholder="Ex : 500mg" required value="${existing.dosage || ''}"></div>
        <div class="form-group"><label>Fréquence *</label><input type="text" name="items[${idx}][frequency]" placeholder="Ex : 3x/jour" required value="${existing.frequency || ''}"></div>
        <div class="form-group"><label>Durée</label><input type="text" name="items[${idx}][duration]" placeholder="Ex : 7 jours" value="${existing.duration || ''}"></div>
        <div class="form-group"><label>Quantité</label><input type="number" name="items[${idx}][quantity]" min="1" value="${existing.quantity || 1}"></div>
        <div class="form-group full"><label>Instructions</label><input type="text" name="items[${idx}][instructions]" placeholder="Ex : à prendre pendant les repas" value="${existing.instructions || ''}"></div>
        <button type="button" class="btn btn-ghost btn-sm remove-item" style="position:absolute;top:0.5rem;right:0.5rem"><i class="fa-solid fa-xmark"></i></button>
    `;
    row.querySelector('.remove-item').addEventListener('click', () => row.remove());
    document.getElementById('itemsList').appendChild(row);
}

document.getElementById('addItemBtn').addEventListener('click', () => addItemRow());
if (existingItems.length > 0) {
    existingItems.forEach(it => addItemRow(it));
} else {
    addItemRow();
}

document.getElementById('prescriptionForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('submitBtn');
    if (document.querySelectorAll('#itemsList .form-grid').length === 0) {
        showToast('Ajoutez au moins un médicament', 'error');
        return;
    }
    btn.disabled = true;
    const formData = new FormData(this);
    try {
        const res = await fetch('/prescriptions/<?= (int) $prescription['id'] ?>/update', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            showToast('Modifications enregistrées', 'success');
            setTimeout(() => window.location.href = data.redirect || '/prescriptions', 500);
        } else {
            let msg = data.error || 'Erreur lors de la modification';
            if (data.errors) msg = Object.values(data.errors).join(' ');
            showToast(msg, 'error');
            btn.disabled = false;
        }
    } catch (err) {
        showToast('Erreur réseau', 'error');
        btn.disabled = false;
    }
});
</script>
