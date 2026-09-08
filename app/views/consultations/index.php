<?php $consultations = $consultations ?? []; ?>
<div class="page-header">
    <h1><i class="fa-solid fa-stethoscope"></i> Consultations</h1>
    <a href="/consultations/create" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Nouvelle consultation</a>
</div>
<div class="card">
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>Date</th><th>Patient</th><th>Médecin</th><th>Motif</th><th>Diagnostic</th><th>Statut</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($consultations as $c): ?>
                <tr>
                    <td><?= format_date($c['consultation_date'], 'd/m/Y H:i') ?></td>
                    <td><a href="/patients/<?= $c['patient_id'] ?>"><?= e($c['patient_first_name'].' '.$c['patient_last_name']) ?></a></td>
                    <td>Dr. <?= e($c['doctor_last_name']) ?></td>
                    <td><?= e(truncate($c['chief_complaint'] ?: '-', 30)) ?></td>
                    <td><?= e(truncate($c['diagnosis'] ?: '-', 40)) ?></td>
                    <td><?= $c['status']==='completed' ? '<span class="badge badge-green">Terminée</span>' : '<span class="badge badge-amber">En cours</span>' ?></td>
                    <td>
                        <div style="display:flex;gap:0.375rem">
                            <a href="/consultations/<?= $c['id'] ?>/edit" class="btn btn-sm btn-ghost" title="Modifier"><i class="fa-solid fa-pen"></i></a>
                            <a href="/settings/audit-logs?entity_type=consultation&entity_id=<?= $c['id'] ?>" class="btn btn-sm btn-ghost" title="Historique"><i class="fa-solid fa-clock-rotate-left"></i></a>
                            <button type="button" class="btn btn-sm btn-ghost" title="Supprimer" onclick="doclyConfirmDelete('/consultations/<?= $c['id'] ?>/delete', this.closest('tr'))"><i class="fa-solid fa-trash" style="color:var(--danger)"></i></button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
