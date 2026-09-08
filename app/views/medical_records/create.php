<?php $patients = $patients ?? []; ?>
<div class="page-header">
    <div class="header-back">
        <a href="/medical-records" class="btn btn-ghost btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <h1><i class="fa-solid fa-file-medical"></i> Nouveau dossier médical</h1>
    </div>
</div>

<div class="card">
    <form id="recordForm" method="post" action="/medical-records/store" class="form-grid">
        <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
        <div class="form-group full">
            <label>Patient *</label>
            <select name="patient_id" required>
                <option value="">Sélectionner un patient</option>
                <?php foreach ($patients as $p): ?>
                <option value="<?= $p['id'] ?>"><?= e($p['first_name'].' '.$p['last_name'].' ('.$p['patient_code'].')') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Type *</label>
            <select name="record_type" required>
                <option value="general">Général</option>
                <option value="allergy">Allergie</option>
                <option value="chronic_disease">Maladie chronique</option>
                <option value="surgery">Chirurgie</option>
                <option value="family_history">Antécédent familial</option>
                <option value="vaccination">Vaccination</option>
            </select>
        </div>
        <div class="form-group"><label>Titre *</label><input type="text" name="title" required></div>
        <div class="form-group">
            <label>Sévérité</label>
            <select name="severity">
                <option value="low">Faible</option>
                <option value="medium">Moyenne</option>
                <option value="high">Élevée</option>
                <option value="critical">Critique</option>
            </select>
        </div>
        <div class="form-group">
            <label>Statut</label>
            <select name="status">
                <option value="active">Actif</option>
                <option value="ongoing">En cours</option>
                <option value="monitored">Sous surveillance</option>
                <option value="resolved">Résolu</option>
            </select>
        </div>
        <div class="form-group"><label>Date de diagnostic</label><input type="date" name="diagnosed_date"></div>
        <div class="form-group full"><label>Description</label><textarea name="description" rows="3"></textarea></div>
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
        const res = await fetch('/medical-records/store', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            showToast('Dossier médical créé', 'success');
            setTimeout(() => window.location.href = data.redirect || '/medical-records', 500);
        } else {
            let msg = data.error || 'Erreur lors de la création';
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
