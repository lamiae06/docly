<?php $records = $records ?? []; ?>
<div class="page-header">
<h1><i class="fa-solid fa-file-medical"></i> Dossiers medicaux</h1>
<?php if (can('medical_records.manage')): ?>
<div class="page-header-actions"><a href="/medical-records/create" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Nouveau dossier</a></div>
<?php endif; ?>
</div>
<div class="card"><div class="table-responsive">
<table class="data-table">
<thead><tr><th>Patient</th><th>Type</th><th>Titre</th><th>Severite</th><th>Statut</th><th>Date</th><th>Actions</th></tr></thead>
<tbody>
<?php foreach ($records as $r): ?>
<tr>
<td><a href="/patients/<?= $r['patient_id'] ?>"><?= e($r['first_name'].' '.$r['last_name']) ?></a></td>
<td><?= ucfirst(str_replace('_', ' ', $r['record_type'])) ?></td>
<td><?= e($r['title']) ?></td>
<td><span class="record-severity severity-<?= $r['severity'] ?>"><?= ucfirst($r['severity']) ?></span></td>
<td><span class="record-status status-<?= $r['status'] ?>"><?= ucfirst($r['status']) ?></span></td>
<td><?= format_date($r['diagnosed_date']) ?></td>
<td>
    <?php if (can('medical_records.manage')): ?>
    <div style="display:flex;gap:0.375rem">
        <a href="/medical-records/<?= $r['id'] ?>/edit" class="btn btn-sm btn-ghost" title="Modifier"><i class="fa-solid fa-pen"></i></a>
        <a href="/settings/audit-logs?entity_type=medical_record&entity_id=<?= $r['id'] ?>" class="btn btn-sm btn-ghost" title="Historique"><i class="fa-solid fa-clock-rotate-left"></i></a>
        <button type="button" class="btn btn-sm btn-ghost" title="Supprimer" onclick="doclyConfirmDelete('/medical-records/<?= $r['id'] ?>/delete', this.closest('tr'))"><i class="fa-solid fa-trash" style="color:var(--danger)"></i></button>
    </div>
    <?php endif; ?>
</td>
</tr>
<?php endforeach; ?>
</tbody>
</table>
</div></div>