<?php $doctor = $doctor ?? []; ?>
<div class="page-header">
    <div class="header-back">
        <a href="/doctors/<?= (int) $doctor['id'] ?>" class="btn btn-ghost btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <h1><i class="fa-solid fa-user-doctor"></i> Modifier médecin</h1>
    </div>
</div>

<div class="card">
    <form id="doctorForm" method="post" action="/doctors/<?= (int) $doctor['id'] ?>/update" class="form-grid">
        <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
        <div class="form-group"><label>Prénom *</label><input type="text" name="first_name" required value="<?= e($doctor['first_name']) ?>"></div>
        <div class="form-group"><label>Nom *</label><input type="text" name="last_name" required value="<?= e($doctor['last_name']) ?>"></div>
        <div class="form-group"><label>Spécialité *</label><input type="text" name="specialty" required value="<?= e($doctor['specialty']) ?>"></div>
        <div class="form-group"><label>Sous-spécialité</label><input type="text" name="sub_specialty" value="<?= e($doctor['sub_specialty'] ?? '') ?>"></div>
        <div class="form-group"><label>Numéro de licence *</label><input type="text" name="license_number" required value="<?= e($doctor['license_number']) ?>"></div>
        <div class="form-group"><label>Téléphone</label><input type="tel" name="phone" value="<?= e($doctor['phone'] ?? '') ?>"></div>
        <div class="form-group"><label>Email</label><input type="email" name="email" value="<?= e($doctor['email'] ?? '') ?>"></div>
        <div class="form-group"><label>Tarif de consultation (<?= e(currency_symbol()) ?>)</label><input type="number" name="consultation_fee" step="0.01" min="0" value="<?= e((string) $doctor['consultation_fee']) ?>"></div>
        <div class="form-group">
            <label>Statut</label>
            <select name="is_active">
                <option value="1" <?= $doctor['is_active'] ? 'selected' : '' ?>>Actif</option>
                <option value="0" <?= !$doctor['is_active'] ? 'selected' : '' ?>>Inactif</option>
            </select>
        </div>
        <div class="form-group full"><label>Biographie</label><textarea name="biography" rows="3"><?= e($doctor['biography'] ?? '') ?></textarea></div>
        <div class="form-actions full">
            <a href="/doctors/<?= (int) $doctor['id'] ?>" class="btn btn-ghost">Annuler</a>
            <button type="submit" class="btn btn-primary" id="submitBtn"><i class="fa-solid fa-check"></i> Enregistrer</button>
        </div>
    </form>
</div>

<script>
document.getElementById('doctorForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    const formData = new FormData(this);
    try {
        const res = await fetch('/doctors/<?= (int) $doctor['id'] ?>/update', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            showToast('Modifications enregistrées', 'success');
            setTimeout(() => window.location.href = data.redirect || '/doctors', 500);
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
