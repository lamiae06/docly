<?php $results = $results ?? []; ?>
<div class="page-header">
    <h1><i class="fa-solid fa-flask"></i> Analyses médicales</h1>
    <?php if (can('lab_results.manage')): ?>
    <div class="page-header-actions">
        <a href="/lab-results/create" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Nouvelle analyse</a>
    </div>
    <?php endif; ?>
</div>
<div class="card">
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>Date</th><th>Patient</th><th>Analyse</th><th>Catégorie</th><th>Résultat</th><th>Référence</th><th>Statut</th><th>Actions</th></tr></thead>
            <tbody>
                <?php foreach ($results as $r): ?>
                <tr>
                    <td><?= format_date($r['test_date']) ?></td>
                    <td><?= e($r['patient_first_name'].' '.$r['patient_last_name']) ?></td>
                    <td><?= e($r['test_name']) ?></td>
                    <td><?= e($r['test_category'] ?: '-') ?></td>
                    <td><strong><?= e($r['result_value'] ?: '-') ?></strong> <?= e($r['unit'] ?: '') ?></td>
                    <td><?= e($r['reference_range'] ?: '-') ?></td>
                    <td><?= lab_status_badge($r['status']) ?></td>
                    <td>
                        <?php if (can('lab_results.manage')): ?>
                        <div style="display:flex;gap:0.375rem">
                            <a href="/lab-results/<?= $r['id'] ?>/edit" class="btn btn-sm btn-ghost" title="Modifier"><i class="fa-solid fa-pen"></i></a>
                            <a href="/settings/audit-logs?entity_type=lab_result&entity_id=<?= $r['id'] ?>" class="btn btn-sm btn-ghost" title="Historique"><i class="fa-solid fa-clock-rotate-left"></i></a>
                            <button type="button" class="btn btn-sm btn-ghost" title="Supprimer" onclick="doclyConfirmDelete('/lab-results/<?= $r['id'] ?>/delete', this.closest('tr'))"><i class="fa-solid fa-trash" style="color:var(--danger)"></i></button>
                        </div>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>
