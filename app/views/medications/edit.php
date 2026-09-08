<?php $medication = $medication ?? []; ?>
<div class="page-header">
    <div class="header-back">
        <a href="/medications" class="btn btn-ghost btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <h1><i class="fa-solid fa-pills"></i> Modifier le médicament</h1>
    </div>
</div>

<div class="card">
    <form id="medicationForm" method="post" action="/medications/<?= (int) $medication['id'] ?>/update" class="form-grid">
        <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
        <div class="form-group"><label>Nom *</label><input type="text" name="name" required value="<?= e($medication['name']) ?>"></div>
        <div class="form-group"><label>Nom générique</label><input type="text" name="generic_name" value="<?= e($medication['generic_name'] ?? '') ?>"></div>
        <div class="form-group"><label>Catégorie</label><input type="text" name="category" value="<?= e($medication['category'] ?? '') ?>"></div>
        <div class="form-group">
            <label>Forme</label>
            <?php $forms = ['tablet'=>'Comprimé','capsule'=>'Capsule','syrup'=>'Sirop','injection'=>'Injection','cream'=>'Crème','ointment'=>'Pommade','drops'=>'Gouttes','inhaler'=>'Inhalateur','patch'=>'Patch','suppository'=>'Suppositoire','other'=>'Autre']; ?>
            <select name="form">
                <?php foreach ($forms as $val => $label): ?>
                <option value="<?= $val ?>" <?= $medication['form'] === $val ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group"><label>Dosage</label><input type="text" name="dosage_strength" value="<?= e($medication['dosage_strength'] ?? '') ?>"></div>
        <div class="form-group"><label>Fabricant</label><input type="text" name="manufacturer" value="<?= e($medication['manufacturer'] ?? '') ?>"></div>
        <div class="form-group"><label>Stock</label><input type="number" name="stock_quantity" min="0" value="<?= (int) $medication['stock_quantity'] ?>"></div>
        <div class="form-group"><label>Seuil d'alerte stock</label><input type="number" name="stock_alert_level" min="0" value="<?= (int) $medication['stock_alert_level'] ?>"></div>
        <div class="form-group"><label>Prix unitaire (<?= e(currency_symbol()) ?>)</label><input type="number" name="unit_price" step="0.01" min="0" value="<?= e((string) $medication['unit_price']) ?>"></div>
        <div class="form-group"><label>Date d'expiration</label><input type="date" name="expiry_date" value="<?= e($medication['expiry_date'] ?? '') ?>"></div>
        <div class="form-group"><label>N° de lot</label><input type="text" name="batch_number" value="<?= e($medication['batch_number'] ?? '') ?>"></div>
        <div class="form-group">
            <label>Statut</label>
            <select name="is_active">
                <option value="1" <?= $medication['is_active'] ? 'selected' : '' ?>>Actif</option>
                <option value="0" <?= !$medication['is_active'] ? 'selected' : '' ?>>Inactif</option>
            </select>
        </div>
        <div class="form-group full"><label>Description</label><textarea name="description" rows="2"><?= e($medication['description'] ?? '') ?></textarea></div>
        <div class="form-actions full">
            <a href="/medications" class="btn btn-ghost">Annuler</a>
            <button type="submit" class="btn btn-primary" id="submitBtn"><i class="fa-solid fa-check"></i> Enregistrer</button>
        </div>
    </form>
</div>

<script>
document.getElementById('medicationForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    const formData = new FormData(this);
    try {
        const res = await fetch('/medications/<?= (int) $medication['id'] ?>/update', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            showToast('Modifications enregistrées', 'success');
            setTimeout(() => window.location.href = data.redirect || '/medications', 500);
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
