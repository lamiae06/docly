<?php $patients = $patients ?? []; $doctors = $doctors ?? []; ?>
<div class="page-header">
    <div class="header-back">
        <a href="/appointments" class="btn btn-ghost btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <h1><i class="fa-solid fa-calendar-plus"></i> Nouveau rendez-vous</h1>
    </div>
</div>

<div class="card">
    <form id="appointmentForm" method="post" action="/appointments/store" style="padding:1.5rem">
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
            <div class="form-group">
                <label for="appointment_date">Date *</label>
                <input type="date" id="appointment_date" name="appointment_date" required value="<?= date('Y-m-d') ?>">
            </div>
            <div class="form-group">
                <label for="appointment_time">Heure *</label>
                <input type="time" id="appointment_time" name="appointment_time" required value="09:00">
            </div>
            <div class="form-group">
                <label for="duration_minutes">Durée (minutes)</label>
                <input type="number" id="duration_minutes" name="duration_minutes" value="30" min="10" step="5">
            </div>
            <div class="form-group">
                <label for="type">Type de rendez-vous</label>
                <select id="type" name="type">
                    <option value="consultation">Consultation</option>
                    <option value="follow_up">Suivi</option>
                    <option value="emergency">Urgence</option>
                    <option value="routine_check">Bilan de routine</option>
                    <option value="vaccination">Vaccination</option>
                    <option value="surgery">Chirurgie</option>
                    <option value="other">Autre</option>
                </select>
            </div>
            <div class="form-group full">
                <label for="reason">Motif</label>
                <input type="text" id="reason" name="reason" placeholder="Motif de la consultation">
            </div>
            <div class="form-group full">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes" rows="2"></textarea>
            </div>
        </div>
        <div class="form-actions">
            <a href="/appointments" class="btn btn-ghost">Annuler</a>
            <button type="submit" class="btn btn-primary" id="submitBtn"><i class="fa-solid fa-check"></i> Créer le rendez-vous</button>
        </div>
    </form>
</div>

<script>
document.getElementById('appointmentForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    const formData = new FormData(this);
    try {
        const data = await doclySubmitForm('/appointments/store', formData);
        if (data.success) {
            showToast('Rendez-vous créé', 'success');
            setTimeout(() => window.location.href = data.redirect || '/appointments', 500);
        } else {
            showToast(data.error || 'Erreur lors de la création', 'error');
            btn.disabled = false;
        }
    } catch (err) {
        showToast(err.message || 'Erreur réseau', 'error');
        btn.disabled = false;
    }
});
</script>
