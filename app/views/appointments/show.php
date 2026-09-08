<?php $appointment = $appointment ?? []; ?>
<div class="page-header">
    <div class="header-back">
        <a href="/appointments" class="btn btn-ghost btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <h1><i class="fa-solid fa-calendar-check"></i> Rendez-vous #<?= $appointment['id'] ?></h1>
    </div>
    <div class="page-header-actions">
        <?= appointment_status_badge($appointment['status']) ?>
    </div>
</div>

<div class="patient-profile">
    <div class="profile-card">
        <div class="profile-avatar" style="background:#0ea5e9">
            <?= substr($appointment['patient_first_name'],0,1).substr($appointment['patient_last_name'],0,1) ?>
        </div>
        <h2><?= e($appointment['patient_first_name'].' '.$appointment['patient_last_name']) ?></h2>
        <p class="profile-meta"><?= e($appointment['patient_code']) ?></p>
        <div class="profile-details">
            <div class="detail-row"><i class="fa-solid fa-phone"></i><span><?= e($appointment['patient_phone'] ?? '-') ?></span></div>
            <div class="detail-row"><i class="fa-solid fa-user-doctor"></i><span>Dr. <?= e($appointment['doctor_first_name'].' '.$appointment['doctor_last_name']) ?></span></div>
            <div class="detail-row"><i class="fa-solid fa-stethoscope"></i><span><?= e($appointment['specialty']) ?></span></div>
        </div>
        <div class="form-actions" style="justify-content:flex-start;padding-top:1rem">
            <a href="/patients/<?= $appointment['patient_id'] ?>" class="btn btn-ghost btn-sm"><i class="fa-solid fa-user"></i> Voir la fiche patient</a>
        </div>
    </div>

    <div class="profile-content">
        <div class="tab-content">
            <div class="overview-grid">
                <div class="info-card">
                    <h4><i class="fa-regular fa-calendar"></i> Date & heure</h4>
                    <p><?= format_date($appointment['appointment_date']) ?> à <?= format_time($appointment['appointment_time']) ?></p>
                </div>
                <div class="info-card">
                    <h4><i class="fa-regular fa-clock"></i> Durée</h4>
                    <p><?= (int) $appointment['duration_minutes'] ?> minutes</p>
                </div>
                <div class="info-card">
                    <h4><i class="fa-solid fa-tag"></i> Type</h4>
                    <p><?= ucfirst(str_replace('_', ' ', $appointment['type'])) ?></p>
                </div>
                <div class="info-card">
                    <h4><i class="fa-solid fa-list-check"></i> Statut</h4>
                    <p>
                        <select id="statusSelect" class="status-select">
                            <?php $statuses = ['scheduled'=>'Programmé','confirmed'=>'Confirmé','in_progress'=>'En cours','completed'=>'Terminé','cancelled'=>'Annulé','no_show'=>'Absent']; ?>
                            <?php foreach ($statuses as $val => $label): ?>
                            <option value="<?= $val ?>" <?= $appointment['status']===$val?'selected':'' ?>><?= $label ?></option>
                            <?php endforeach; ?>
                        </select>
                    </p>
                </div>
            </div>

            <h3 class="section-title">Motif</h3>
            <p><?= e($appointment['reason'] ?: 'Aucun motif renseigné') ?></p>

            <?php if (!empty($appointment['notes'])): ?>
            <h3 class="section-title">Notes</h3>
            <p><?= nl2br(e($appointment['notes'])) ?></p>
            <?php endif; ?>
        </div>
    </div>
</div>

<script>
document.getElementById('statusSelect').addEventListener('change', async function() {
    const formData = new FormData();
    formData.append('status', this.value);
    try {
        const res = await fetch('/api/appointments/<?= $appointment['id'] ?>/status', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            showToast('Statut mis à jour', 'success');
        } else {
            showToast(data.error || 'Erreur', 'error');
        }
    } catch (err) {
        showToast('Erreur réseau', 'error');
    }
});
</script>
