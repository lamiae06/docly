<?php $record = $record ?? []; $patients = $patients ?? []; ?>
<div class="page-header">
    <div class="header-back">
        <a href="/medical-records" class="btn btn-ghost btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <h1><i class="fa-solid fa-file-medical"></i> Modifier le dossier médical</h1>
    </div>
</div>

<div class="card">
    <form id="recordForm" method="post" action="/medical-records/<?= (int) $record['id'] ?>/update" class="form-grid">
        <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
        <div class="form-group full">
            <label>Patient *</label>
            <select name="patient_id" required>
                <?php foreach ($patients as $p): ?>
                <option value="<?= $p['id'] ?>" <?= $p['id'] == $record['patient_id'] ? 'selected' : '' ?>><?= e($p['first_name'].' '.$p['last_name'].' ('.$p['patient_code'].')') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Type *</label>
            <?php $types = ['general'=>'Général','allergy'=>'Allergie','chronic_disease'=>'Maladie chronique','surgery'=>'Chirurgie','family_history'=>'Antécédent familial','vaccination'=>'Vaccination']; ?>
            <select name="record_type" required>
                <?php foreach ($types as $val => $label): ?>
                <option value="<?= $val ?>" <?= $record['record_type'] === $val ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group"><label>Titre *</label><input type="text" name="title" required value="<?= e($record['title']) ?>"></div>
        <div class="form-group">
            <label>Sévérité</label>
            <?php $severities = ['low'=>'Faible','medium'=>'Moyenne','high'=>'Élevée','critical'=>'Critique']; ?>
            <select name="severity">
                <?php foreach ($severities as $val => $label): ?>
                <option value="<?= $val ?>" <?= $record['severity'] === $val ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Statut</label>
            <?php $statuses = ['active'=>'Actif','ongoing'=>'En cours','monitored'=>'Sous surveillance','resolved'=>'Résolu']; ?>
            <select name="status">
                <?php foreach ($statuses as $val => $label): ?>
                <option value="<?= $val ?>" <?= $record['status'] === $val ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group"><label>Date de diagnostic</label><input type="date" name="diagnosed_date" value="<?= e($record['diagnosed_date'] ?? '') ?>"></div>
        <div class="form-group full"><label>Description</label><textarea name="description" rows="3"><?= e($record['description'] ?? '') ?></textarea></div>
        <div class="form-actions full">
            <a href="/medical-records" class="btn btn-ghost">Annuler</a>
            <button type="submit" class="btn btn-primary" id="submitBtn"><i class="fa-solid fa-check"></i> Enregistrer</button>
        </div>
    </form>
</div>

<script>
document.getElementById('recordForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    const formData = new FormData(this);
    try {
        const res = await fetch('/medical-records/<?= (int) $record['id'] ?>/update', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            showToast('Modifications enregistrées', 'success');
            setTimeout(() => window.location.href = data.redirect || '/medical-records', 500);
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
