<?php $prescriptions = $prescriptions ?? []; ?>
<div class="page-header">
    <h1><i class="fa-solid fa-prescription"></i> Ordonnances</h1>
    <?php if (can('prescriptions.manage')): ?>
    <div class="page-header-actions">
        <a href="/prescriptions/create" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Nouvelle ordonnance</a>
    </div>
    <?php endif; ?>
</div>
<div class="card">
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>Date</th><th>Patient</th><th>Médecin</th><th>Notes</th><th>Statut</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($prescriptions as $pr): ?>
                <tr>
                    <td><?= format_date($pr['prescription_date']) ?></td>
                    <td><?= e($pr['patient_first_name'].' '.$pr['patient_last_name']) ?></td>
                    <td>Dr. <?= e($pr['doctor_last_name']) ?></td>
                    <td><?= e(truncate($pr['notes'] ?: '-', 40)) ?></td>
                    <td><?= $pr['status']==='issued' ? '<span class="badge badge-green">Émise</span>' : '<span class="badge badge-gray">Brouillon</span>' ?></td>
                    <td>
                        <?php if (can('prescriptions.manage')): ?>
                        <div style="display:flex;gap:0.375rem">
                            <a href="/prescriptions/<?= $pr['id'] ?>/edit" class="btn btn-sm btn-ghost" title="Modifier"><i class="fa-solid fa-pen"></i></a>
                            <a href="/settings/audit-logs?entity_type=prescription&entity_id=<?= $pr['id'] ?>" class="btn btn-sm btn-ghost" title="Historique"><i class="fa-solid fa-clock-rotate-left"></i></a>
                            <button type="button" class="btn btn-sm btn-ghost" title="Supprimer" onclick="doclyConfirmDelete('/prescriptions/<?= $pr['id'] ?>/delete', this.closest('tr'))"><i class="fa-solid fa-trash" style="color:var(--danger)"></i></button>
                        </div>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
