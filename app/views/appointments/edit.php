<?php $appointment = $appointment ?? []; $patients = $patients ?? []; $doctors = $doctors ?? []; ?>
<div class="page-header">
    <div class="header-back">
        <a href="/appointments/<?= (int) $appointment['id'] ?>" class="btn btn-ghost btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <h1><i class="fa-solid fa-calendar-check"></i> Modifier le rendez-vous</h1>
    </div>
</div>

<div class="card">
    <form id="appointmentForm" method="post" action="/appointments/<?= (int) $appointment['id'] ?>/update" style="padding:1.5rem">
        <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
        <div class="form-grid">
            <div class="form-group">
                <label for="patient_id">Patient *</label>
                <select id="patient_id" name="patient_id" required>
                    <?php foreach ($patients as $p): ?>
                    <option value="<?= $p['id'] ?>" <?= $p['id'] == $appointment['patient_id'] ? 'selected' : '' ?>><?= e($p['first_name'].' '.$p['last_name'].' ('.$p['patient_code'].')') ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="doctor_id">Médecin *</label>
                <select id="doctor_id" name="doctor_id" required>
                    <?php foreach ($doctors as $d): ?>
                    <option value="<?= $d['id'] ?>" <?= $d['id'] == $appointment['doctor_id'] ? 'selected' : '' ?>>Dr. <?= e($d['first_name'].' '.$d['last_name'].' - '.$d['specialty']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="appointment_date">Date *</label>
                <input type="date" id="appointment_date" name="appointment_date" required value="<?= e($appointment['appointment_date']) ?>">
            </div>
            <div class="form-group">
                <label for="appointment_time">Heure *</label>
                <input type="time" id="appointment_time" name="appointment_time" required value="<?= e(substr($appointment['appointment_time'], 0, 5)) ?>">
            </div>
            <div class="form-group">
                <label for="duration_minutes">Durée (minutes)</label>
                <input type="number" id="duration_minutes" name="duration_minutes" value="<?= (int) $appointment['duration_minutes'] ?>" min="10" step="5">
            </div>
            <div class="form-group">
                <label for="type">Type de rendez-vous</label>
                <?php $types = ['consultation'=>'Consultation','follow_up'=>'Suivi','emergency'=>'Urgence','routine_check'=>'Bilan de routine','vaccination'=>'Vaccination','surgery'=>'Chirurgie','other'=>'Autre']; ?>
                <select id="type" name="type">
                    <?php foreach ($types as $val => $label): ?>
                    <option value="<?= $val ?>" <?= $appointment['type'] === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group">
                <label for="status">Statut</label>
                <?php $statuses = ['scheduled'=>'Programmé','confirmed'=>'Confirmé','in_progress'=>'En cours','completed'=>'Terminé','cancelled'=>'Annulé','no_show'=>'Absent']; ?>
                <select id="status" name="status">
                    <?php foreach ($statuses as $val => $label): ?>
                    <option value="<?= $val ?>" <?= $appointment['status'] === $val ? 'selected' : '' ?>><?= $label ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="form-group full">
                <label for="reason">Motif</label>
                <input type="text" id="reason" name="reason" value="<?= e($appointment['reason'] ?? '') ?>">
            </div>
            <div class="form-group full">
                <label for="notes">Notes</label>
                <textarea id="notes" name="notes" rows="2"><?= e($appointment['notes'] ?? '') ?></textarea>
            </div>
        </div>
        <div class="form-actions">
            <a href="/appointments/<?= (int) $appointment['id'] ?>" class="btn btn-ghost">Annuler</a>
            <button type="submit" class="btn btn-primary" id="submitBtn"><i class="fa-solid fa-check"></i> Enregistrer</button>
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
        const data = await doclySubmitForm('/appointments/<?= (int) $appointment['id'] ?>/update', formData);
        if (data.success) {
            showToast('Modifications enregistrées', 'success');
            setTimeout(() => window.location.href = data.redirect || '/appointments', 500);
        } else {
            let msg = data.error || 'Erreur lors de la modification';
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
