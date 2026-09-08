<?php $documents = $documents ?? []; ?>
<div class="page-header">
    <h1><i class="fa-solid fa-folder-open"></i> Documents</h1>
    <?php if (can('documents.manage')): ?>
    <div class="page-header-actions">
        <a href="/documents/create" class="btn btn-primary"><i class="fa-solid fa-upload"></i> Ajouter un document</a>
    </div>
    <?php endif; ?>
</div>
<div class="documents-grid">
    <?php foreach ($documents as $doc): ?>
    <div class="document-card">
        <div class="document-icon"><i class="fa-solid fa-file-pdf"></i></div>
        <div class="document-info">
            <h4><?= e($doc['title']) ?></h4>
            <p><?= e($doc['category']) ?> • <?= $doc['patient_id'] ? e($doc['patient_first_name'].' '.$doc['patient_last_name']) : 'Aucun patient' ?> • <?= format_date($doc['created_at']) ?></p>
        </div>
        <a href="<?= e($doc['file_path']) ?>" class="btn btn-sm btn-ghost" download="<?= e($doc['file_name']) ?>" title="Télécharger"><i class="fa-solid fa-download"></i></a>
        <?php if (can('documents.manage')): ?>
        <a href="/documents/<?= $doc['id'] ?>/edit" class="btn btn-sm btn-ghost" title="Modifier"><i class="fa-solid fa-pen"></i></a>
        <a href="/settings/audit-logs?entity_type=document&entity_id=<?= $doc['id'] ?>" class="btn btn-sm btn-ghost" title="Historique"><i class="fa-solid fa-clock-rotate-left"></i></a>
        <button type="button" class="btn btn-sm btn-ghost" title="Supprimer" onclick="doclyConfirmDelete('/documents/<?= $doc['id'] ?>/delete', this.closest('.document-card'))"><i class="fa-solid fa-trash" style="color:var(--danger)"></i></button>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
</div>
