<?php
$doctor = $doctor ?? [];
$stats = $stats ?? [];
$schedule = $schedule ?? [];
$recentAppointments = $recentAppointments ?? [];
$initials = strtoupper(substr($doctor['first_name'],0,1).substr($doctor['last_name'],0,1));
?>
<div class="page-header">
    <div class="header-back">
        <a href="/doctors" class="btn btn-ghost btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <h1><i class="fa-solid fa-user-doctor"></i> Dr. <?= e($doctor['first_name'].' '.$doctor['last_name']) ?></h1>
    </div>
</div>

<div class="patient-profile">
    <div class="profile-card">
        <div class="profile-avatar" style="background:#6366f1"><?= $initials ?></div>
        <h2>Dr. <?= e($doctor['first_name'].' '.$doctor['last_name']) ?></h2>
        <p class="profile-meta"><?= e($doctor['specialty']) ?><?= !empty($doctor['sub_specialty']) ? ' · '.e($doctor['sub_specialty']) : '' ?></p>
        <div class="profile-details">
            <div class="detail-row"><i class="fa-solid fa-phone"></i><span><?= e($doctor['phone'] ?? '-') ?></span></div>
            <div class="detail-row"><i class="fa-solid fa-envelope"></i><span><?= e($doctor['email'] ?? '-') ?></span></div>
            <div class="detail-row"><i class="fa-solid fa-id-card"></i><span><?= e($doctor['license_number'] ?? '-') ?></span></div>
            <div class="detail-row"><i class="fa-solid fa-money-bill"></i><span><?= format_money((float)($doctor['consultation_fee'] ?? 0)) ?> / consultation</span></div>
        </div>
        <?php if (!empty($doctor['biography'])): ?>
        <p style="margin-top:1rem;color:var(--text-secondary);font-size:.875rem"><?= nl2br(e($doctor['biography'])) ?></p>
        <?php endif; ?>
    </div>

    <div class="profile-content">
        <div class="overview-grid">
            <div class="info-card"><h4><i class="fa-solid fa-calendar-check"></i> RDV aujourd'hui</h4><p style="font-size:1.5rem;font-weight:700"><?= (int)($stats['today_appointments'] ?? 0) ?></p></div>
            <div class="info-card"><h4><i class="fa-solid fa-users"></i> Patients suivis</h4><p style="font-size:1.5rem;font-weight:700"><?= (int)($stats['total_patients'] ?? 0) ?></p></div>
            <div class="info-card"><h4><i class="fa-solid fa-stethoscope"></i> Consultations</h4><p style="font-size:1.5rem;font-weight:700"><?= (int)($stats['total_consultations'] ?? 0) ?></p></div>
            <div class="info-card"><h4><i class="fa-solid fa-sack-dollar"></i> Revenus générés</h4><p style="font-size:1.5rem;font-weight:700"><?= format_money((float)($stats['total_revenue'] ?? 0)) ?></p></div>
        </div>

        <h3 class="section-title">Planning hebdomadaire</h3>
        <?php if (empty($schedule)): ?>
        <p class="empty-state-inline">Aucun planning renseigné pour ce médecin.</p>
        <?php else: ?>
        <div class="schedule-list">
            <?php foreach ($schedule as $s): ?>
            <div class="schedule-row">
                <span class="schedule-day"><?= day_of_week_name((int)$s['day_of_week']) ?></span>
                <span><?= format_time($s['start_time']) ?> - <?= format_time($s['end_time']) ?></span>
                <span class="badge <?= $s['is_available'] ? 'badge-success' : 'badge-danger' ?>"><?= $s['is_available'] ? 'Disponible' : 'Indisponible' ?></span>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>

        <h3 class="section-title">Rendez-vous récents</h3>
        <?php if (empty($recentAppointments)): ?>
        <p class="empty-state-inline">Aucun rendez-vous récent.</p>
        <?php else: ?>
        <table class="data-table">
            <thead><tr><th>Patient</th><th>Date</th><th>Heure</th><th>Statut</th></tr></thead>
            <tbody>
            <?php foreach ($recentAppointments as $a): ?>
                <tr onclick="window.location='/appointments/<?= $a['id'] ?>'" style="cursor:pointer">
                    <td><?= e($a['patient_first_name'].' '.$a['patient_last_name']) ?></td>
                    <td><?= format_date($a['appointment_date']) ?></td>
                    <td><?= format_time($a['appointment_time']) ?></td>
                    <td><?= appointment_status_badge($a['status']) ?></td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        <?php endif; ?>
    </div>
</div>
