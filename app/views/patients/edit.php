<?php $patient = $patient ?? []; ?>
<div class="page-header">
    <div class="header-back">
        <a href="/patients/<?= $patient['id'] ?>" class="btn btn-ghost btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <h1><i class="fa-solid fa-user-pen"></i> Modifier <?= e($patient['first_name'].' '.$patient['last_name']) ?></h1>
    </div>
</div>
<div class="card">
    <form id="editPatientForm" method="post" action="/patients/<?= (int) $patient['id'] ?>/update" class="form-grid">
        <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
        <div class="form-group"><label>Prénom *</label><input type="text" name="first_name" value="<?= e($patient['first_name']) ?>" required></div>
        <div class="form-group"><label>Nom *</label><input type="text" name="last_name" value="<?= e($patient['last_name']) ?>" required></div>
        <div class="form-group"><label>Date de naissance *</label><input type="date" name="date_of_birth" value="<?= e($patient['date_of_birth']) ?>" required></div>
        <div class="form-group"><label>Sexe *</label>
            <select name="gender" required>
                <option value="male" <?= $patient['gender']==='male'?'selected':'' ?>>Homme</option>
                <option value="female" <?= $patient['gender']==='female'?'selected':'' ?>>Femme</option>
                <option value="other" <?= $patient['gender']==='other'?'selected':'' ?>>Autre</option>
            </select>
        </div>
        <div class="form-group"><label>Téléphone *</label><input type="tel" name="phone" value="<?= e($patient['phone']) ?>" required></div>
        <div class="form-group"><label>Email</label><input type="email" name="email" value="<?= e($patient['email'] ?? '') ?>"></div>
        <div class="form-group full"><label>Adresse</label><input type="text" name="address" value="<?= e($patient['address'] ?? '') ?>"></div>
        <div class="form-group"><label>Ville</label><input type="text" name="city" value="<?= e($patient['city'] ?? '') ?>"></div>
        <div class="form-group"><label>Code postal</label><input type="text" name="postal_code" value="<?= e($patient['postal_code'] ?? '') ?>"></div>
        <div class="form-group"><label>Contact d'urgence</label><input type="text" name="emergency_contact_name" value="<?= e($patient['emergency_contact_name'] ?? '') ?>"></div>
        <div class="form-group"><label>Tél. urgence</label><input type="tel" name="emergency_contact_phone" value="<?= e($patient['emergency_contact_phone'] ?? '') ?>"></div>
        <div class="form-group"><label>Groupe sanguin</label>
            <select name="blood_type">
                <?php foreach (['unknown'=>'Inconnu','A+'=>'A+','A-'=>'A-','B+'=>'B+','B-'=>'B-','AB+'=>'AB+','AB-'=>'AB-','O+'=>'O+','O-'=>'O-'] as $val=>$label): ?>
                <option value="<?= $val ?>" <?= ($patient['blood_type'] ?? 'unknown')===$val?'selected':'' ?>><?= $label ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group full"><label>Allergies</label><textarea name="allergies" rows="2"><?= e($patient['allergies'] ?? '') ?></textarea></div>
        <div class="form-group full"><label>Maladies chroniques</label><textarea name="chronic_diseases" rows="2"><?= e($patient['chronic_diseases'] ?? '') ?></textarea></div>
        <div class="form-group"><label>Assurance</label><input type="text" name="insurance_name" value="<?= e($patient['insurance_name'] ?? '') ?>"></div>
        <div class="form-group"><label>N° assurance</label><input type="text" name="insurance_number" value="<?= e($patient['insurance_number'] ?? '') ?>"></div>
        <div class="form-group full"><label>Notes</label><textarea name="notes" rows="3"><?= e($patient['notes'] ?? '') ?></textarea></div>
        <div class="form-actions full">
            <a href="/patients/<?= $patient['id'] ?>" class="btn btn-ghost">Annuler</a>
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Enregistrer les modifications</button>
        </div>
    </form>
</div>

<script>
document.getElementById('editPatientForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    try {
        const res = await fetch('/patients/<?= $patient['id'] ?>/update', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            showToast('Patient mis à jour', 'success');
            setTimeout(() => window.location.href = '/patients/<?= $patient['id'] ?>', 600);
        } else {
            showToast(data.error || 'Erreur', 'error');
        }
    } catch (err) {
        showToast('Erreur réseau', 'error');
    }
});
</script>
