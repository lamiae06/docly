<?php $patients = $patients ?? []; $doctors = $doctors ?? []; ?>
<div class="page-header">
    <div class="header-back">
        <a href="/lab-results" class="btn btn-ghost btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <h1><i class="fa-solid fa-flask"></i> Nouvelle analyse</h1>
    </div>
</div>

<div class="card">
    <form id="labResultForm" method="post" action="/lab-results/store" class="form-grid">
        <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
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
            <label>Médecin prescripteur</label>
            <select name="doctor_id">
                <option value="">Aucun</option>
                <?php foreach ($doctors as $d): ?>
                <option value="<?= $d['id'] ?>">Dr. <?= e($d['first_name'].' '.$d['last_name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group"><label>Nom de l'analyse *</label><input type="text" name="test_name" placeholder="Ex : Numération formule sanguine" required></div>
        <div class="form-group"><label>Catégorie</label><input type="text" name="test_category" placeholder="Ex : Hématologie"></div>
        <div class="form-group"><label>Laboratoire</label><input type="text" name="laboratory"></div>
        <div class="form-group"><label>Date de l'analyse *</label><input type="date" name="test_date" required value="<?= date('Y-m-d') ?>"></div>
        <div class="form-group"><label>Résultat</label><input type="text" name="result_value" placeholder="Laisser vide si en attente"></div>
        <div class="form-group"><label>Unité</label><input type="text" name="unit" placeholder="Ex : g/L"></div>
        <div class="form-group"><label>Valeurs de référence</label><input type="text" name="reference_range" placeholder="Ex : 12-16"></div>
        <div class="form-group full"><label>Interprétation</label><textarea name="interpretation" rows="2"></textarea></div>
        <div class="form-group full"><label>Notes</label><textarea name="notes" rows="2"></textarea></div>
        <div class="form-actions full">
            <a href="/lab-results" class="btn btn-ghost">Annuler</a>
            <button type="submit" class="btn btn-primary" id="submitBtn"><i class="fa-solid fa-check"></i> Enregistrer l'analyse</button>
        </div>
    </form>
</div>

<script>
document.getElementById('labResultForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    const formData = new FormData(this);
    try {
        const res = await fetch('/lab-results/store', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            showToast('Analyse enregistrée', 'success');
            setTimeout(() => window.location.href = data.redirect || '/lab-results', 500);
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
