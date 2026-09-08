<?php $document = $document ?? []; $patients = $patients ?? []; ?>
<div class="page-header">
    <div class="header-back">
        <a href="/documents" class="btn btn-ghost btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <h1><i class="fa-solid fa-file-pen"></i> Modifier le document</h1>
    </div>
</div>

<div class="card">
    <form id="documentForm" method="post" action="/documents/<?= (int) $document['id'] ?>/update" class="form-grid">
        <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
        <div class="form-group full"><label>Titre *</label><input type="text" name="title" required value="<?= e($document['title']) ?>"></div>
        <div class="form-group">
            <label>Patient</label>
            <select name="patient_id">
                <option value="">Aucun (document administratif)</option>
                <?php foreach ($patients as $p): ?>
                <option value="<?= $p['id'] ?>" <?= $p['id'] == $document['patient_id'] ? 'selected' : '' ?>><?= e($p['first_name'].' '.$p['last_name'].' ('.$p['patient_code'].')') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Catégorie</label>
            <?php $cats = ['other'=>'Autre','prescription'=>'Ordonnance','lab_result'=>"Résultat d'analyse",'medical_report'=>'Compte-rendu médical','administrative'=>'Administratif','imaging'=>'Imagerie','consent'=>'Consentement']; ?>
            <select name="category">
                <?php foreach ($cats as $val => $label): ?>
                <option value="<?= $val ?>" <?= $document['category'] === $val ? 'selected' : '' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group full">
            <label>Fichier</label>
            <p style="color:var(--text-muted);margin:0.25rem 0 0">
                <i class="fa-solid fa-paperclip"></i> <?= e($document['file_name']) ?>
                — pour remplacer le fichier, supprimez ce document puis téléversez-en un nouveau.
            </p>
        </div>
        <div class="form-group full"><label>Description</label><textarea name="description" rows="2"><?= e($document['description'] ?? '') ?></textarea></div>
        <div class="form-actions full">
            <a href="/documents" class="btn btn-ghost">Annuler</a>
            <button type="submit" class="btn btn-primary" id="submitBtn"><i class="fa-solid fa-check"></i> Enregistrer</button>
        </div>
    </form>
</div>

<script>
document.getElementById('documentForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('submitBtn');
    btn.disabled = true;
    const formData = new FormData(this);
    try {
        const data = await doclySubmitForm('/documents/<?= (int) $document['id'] ?>/update', formData);
        if (data.success) {
            showToast('Modifications enregistrées', 'success');
            setTimeout(() => window.location.href = data.redirect || '/documents', 500);
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
