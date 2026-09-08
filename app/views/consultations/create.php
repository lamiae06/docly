<?php $patients = $patients ?? []; $doctors = $doctors ?? []; ?>
<div class="page-header">
    <div class="header-back">
        <a href="/consultations" class="btn btn-ghost btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <h1><i class="fa-solid fa-stethoscope"></i> Nouvelle consultation</h1>
    </div>
</div>

<div class="card">
    <form id="consultationForm" method="post" action="/consultations/store" style="padding:1.5rem">
        <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
        <div class="form-grid">
            <div class="form-group">
                <label for="patient_id">Patient *</label>
                <select id="patient_id" name="patient_id" required>
                    <option value="">Sélectionner un patient</option>
                    <?php foreach ($patients as $p): ?>
                    <option value="<?= $p['id'] ?>"><?= e($p['first_name'].' '.$p['last_name'].' ('.$p['patient_code'].')') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="doctor_id">Médecin *</label>
                <select id="doctor_id" name="doctor_id" required>
                    <option value="">Sélectionner un médecin</option>
                    <?php foreach ($doctors as $d): ?>
                    <option value="<?= $d['id'] ?>">Dr. <?= e($d['first_name'].' '.$d['last_name'].' - '.$d['specialty']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group full">
                <label for="chief_complaint">Motif principal</label>
                <input type="text" id="chief_complaint" name="chief_complaint" placeholder="Ex : douleur thoracique">
            </div>
            <div class="form-group full">
                <label for="symptoms">Symptômes</label>
                <textarea id="symptoms" name="symptoms" rows="2"></textarea>
            </div>
            <div class="form-group">
                <label for="diagnosis">Diagnostic</label>
                <input type="text" id="diagnosis" name="diagnosis">
            </div>
            <div class="form-group">
                <label for="diagnosis_icd10">Code CIM-10</label>
                <input type="text" id="diagnosis_icd10" name="diagnosis_icd10" placeholder="Ex : I10">
            </div>
            <div class="form-group full">
                <label for="treatment_plan">Plan de traitement</label>
                <textarea id="treatment_plan" name="treatment_plan" rows="2"></textarea>
            </div>
            <div class="form-group full">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes" rows="2"></textarea>
            </div>
            <div class="form-group">
                <label for="follow_up_date">Date de suivi</label>
                <input type="date" id="follow_up_date" name="follow_up_date">
            </div>
        </div>
        <div class="form-actions">
            <a href="/consultations" class="btn btn-ghost">Annuler</a>
            <button type="submit" class="btn btn-primary" id="submitBtn"><i class="fa-solid fa-check"></i> Enregistrer la consultation</button>
        </div>
    </form>
</div>

<script>
document.getElementById('consultationForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    const formData = new FormData(this);
    try {
        const res = await fetch('/consultations/store', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            showToast('Consultation enregistrée', 'success');
            setTimeout(() => window.location.href = data.redirect || '/consultations', 500);
        } else {
            showToast(data.error || 'Erreur lors de l\'enregistrement', 'error');
            btn.disabled = false;
        }
    } catch (err) {
        showToast('Erreur réseau', 'error');
        btn.disabled = false;
    }
});
</script>
