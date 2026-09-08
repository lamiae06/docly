<?php $doctors = $doctors ?? []; ?>
<div class="page-header">
    <h1><i class="fa-solid fa-user-doctor"></i> Médecins</h1>
    <?php if (can('doctors.manage')): ?>
    <div class="page-header-actions">
        <a href="/doctors/create" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Ajouter un médecin</a>
    </div>
    <?php endif; ?>
</div>
<div class="doctors-grid">
    <?php foreach ($doctors as $d): ?>
    <div class="doctor-card">
        <div class="doctor-header">
            <div class="doctor-avatar"><?= substr($d['first_name'],0,1).substr($d['last_name'],0,1) ?></div>
            <div class="doctor-info">
                <h3>Dr. <?= e($d['first_name'].' '.$d['last_name']) ?></h3>
                <span class="doctor-specialty"><?= e($d['specialty']) ?></span>
                <?php if ($d['sub_specialty']): ?><span class="doctor-sub"><?= e($d['sub_specialty']) ?></span><?php endif; ?>
            </div>
        </div>
        <div class="doctor-stats">
            <div class="dstat"><span class="dstat-value"><?= $d['today_appointments'] ?></span><span class="dstat-label">RDV aujourd'hui</span></div>
            <div class="dstat"><span class="dstat-value"><?= $d['today_consultations'] ?></span><span class="dstat-label">Consultations</span></div>
            <div class="dstat"><span class="dstat-value"><?= $d['monthly_patients'] ?></span><span class="dstat-label">Patients/mois</span></div>
        </div>
        <div class="doctor-footer">
            <span class="doctor-fee"><?= format_money($d['consultation_fee']) ?>/consultation</span>
            <div style="display:flex;gap:0.375rem">
                <a href="/doctors/<?= $d['id'] ?>" class="btn btn-sm btn-ghost">Profil</a>
                <?php if (can('doctors.manage')): ?>
                <a href="/doctors/<?= $d['id'] ?>/edit" class="btn btn-sm btn-ghost" title="Modifier"><i class="fa-solid fa-pen"></i></a>
                <a href="/settings/audit-logs?entity_type=doctor&entity_id=<?= $d['id'] ?>" class="btn btn-sm btn-ghost" title="Historique"><i class="fa-solid fa-clock-rotate-left"></i></a>
                <button type="button" class="btn btn-sm btn-ghost" title="Supprimer" onclick="doclyConfirmDelete('/doctors/<?= $d['id'] ?>/delete', this.closest('.doctor-card'))"><i class="fa-solid fa-trash" style="color:var(--danger)"></i></button>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <?php endforeach; ?>
</div>
