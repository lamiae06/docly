<?php $settings = $settings ?? []; ?>
<div class="page-header">
    <div class="header-back">
        <a href="/settings" class="btn btn-ghost btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <h1><i class="fa-solid fa-hospital"></i> Clinique</h1>
    </div>
</div>

<div class="card">
    <form id="clinicForm" class="form-grid">
        <div class="form-group full"><label>Nom de la clinique</label><input type="text" name="clinic_name" value="<?= e($settings['clinic_name'] ?? '') ?>"></div>
        <div class="form-group full"><label>Adresse</label><input type="text" name="clinic_address" value="<?= e($settings['clinic_address'] ?? '') ?>"></div>
        <div class="form-group"><label>Téléphone</label><input type="text" name="clinic_phone" value="<?= e($settings['clinic_phone'] ?? '') ?>"></div>
        <div class="form-group"><label>Email</label><input type="email" name="clinic_email" value="<?= e($settings['clinic_email'] ?? '') ?>"></div>
        <div class="form-group"><label>Site web</label><input type="text" name="clinic_website" value="<?= e($settings['clinic_website'] ?? '') ?>"></div>
        <div class="form-group">
            <label>Devise</label>
            <select name="currency">
                <?php $currentCurrency = strtoupper($settings['currency'] ?? 'EUR'); ?>
                <?php foreach (currency_options() as $code => $info): ?>
                <option value="<?= e($code) ?>"<?= $code === $currentCurrency ? ' selected' : '' ?>><?= e($info['label']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group"><label>Taux de TVA (%)</label><input type="number" name="tax_rate" value="<?= e($settings['tax_rate'] ?? '20') ?>" step="0.1"></div>
        <div class="form-group"><label>Durée de RDV par défaut (min)</label><input type="number" name="appointment_default_duration" value="<?= e($settings['appointment_default_duration'] ?? '30') ?>"></div>
        <div class="form-group"><label>Heure d'ouverture</label><input type="time" name="working_hours_start" value="<?= e($settings['working_hours_start'] ?? '08:00') ?>"></div>
        <div class="form-group"><label>Heure de fermeture</label><input type="time" name="working_hours_end" value="<?= e($settings['working_hours_end'] ?? '18:00') ?>"></div>
        <div class="form-actions full">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Enregistrer</button>
        </div>
    </form>
</div>

<script>
document.getElementById('clinicForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    try {
        const res = await fetch('/api/settings/clinic', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            showToast('Paramètres de la clinique mis à jour', 'success');
        } else {
            showToast(data.error || 'Erreur', 'error');
        }
    } catch (err) {
        showToast('Erreur réseau', 'error');
    }
});
</script>
