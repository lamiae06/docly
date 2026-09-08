<?php $patients = $patients ?? []; ?>
<div class="page-header">
    <div class="header-back">
        <a href="/documents" class="btn btn-ghost btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <h1><i class="fa-solid fa-upload"></i> Nouveau document</h1>
    </div>
</div>

<div class="card">
    <form id="documentForm" method="post" action="/documents/store" class="form-grid" enctype="multipart/form-data">
        <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
        <div class="form-group full"><label>Titre *</label><input type="text" name="title" required></div>
        <div class="form-group">
            <label>Patient</label>
            <select name="patient_id">
                <option value="">Aucun (document administratif)</option>
                <?php foreach ($patients as $p): ?>
                <option value="<?= $p['id'] ?>"><?= e($p['first_name'].' '.$p['last_name'].' ('.$p['patient_code'].')') ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Catégorie</label>
            <select name="category">
                <option value="other">Autre</option>
                <option value="prescription">Ordonnance</option>
                <option value="lab_result">Résultat d'analyse</option>
                <option value="medical_report">Compte-rendu médical</option>
                <option value="administrative">Administratif</option>
                <option value="imaging">Imagerie</option>
                <option value="consent">Consentement</option>
            </select>
        </div>
        <div class="form-group full">
            <label>Fichier * <span style="font-weight:400;color:var(--text-muted)">(PDF, JPG, PNG, DOC, DOCX — 10 Mo max)</span></label>
            <input type="file" name="file" required accept=".pdf,.jpg,.jpeg,.png,.doc,.docx">
        </div>
        <div class="form-group full"><label>Description</label><textarea name="description" rows="2"></textarea></div>
        <div class="form-actions full">
            <a href="/documents" class="btn btn-ghost">Annuler</a>
            <button type="submit" class="btn btn-primary" id="submitBtn"><i class="fa-solid fa-upload"></i> Téléverser</button>
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
        const data = await doclySubmitForm('/documents/store', formData);
        if (data.success) {
            showToast('Document ajouté', 'success');
            setTimeout(() => window.location.href = data.redirect || '/documents', 500);
        } else {
            let msg = data.error || 'Erreur lors de l\'envoi';
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
