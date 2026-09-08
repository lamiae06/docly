<div class="page-header">
    <h1><i class="fa-solid fa-user-plus"></i> Nouveau patient</h1>
</div>
<div class="card">
    <form id="createPatientForm" method="post" action="/patients/store" class="form-grid">
        <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
        <div class="form-group"><label>Prénom *</label><input type="text" name="first_name" required></div>
        <div class="form-group"><label>Nom *</label><input type="text" name="last_name" required></div>
        <div class="form-group"><label>Date de naissance *</label><input type="date" name="date_of_birth" required></div>
        <div class="form-group"><label>Sexe *</label>
            <select name="gender" required>
                <option value="male">Homme</option>
                <option value="female">Femme</option>
                <option value="other">Autre</option>
            </select>
        </div>
        <div class="form-group"><label>Téléphone *</label><input type="tel" name="phone" required></div>
        <div class="form-group"><label>Email</label><input type="email" name="email"></div>
        <div class="form-group full"><label>Adresse</label><input type="text" name="address"></div>
        <div class="form-group"><label>Ville</label><input type="text" name="city"></div>
        <div class="form-group"><label>Code postal</label><input type="text" name="postal_code"></div>
        <div class="form-group"><label>Contact d'urgence</label><input type="text" name="emergency_contact_name" placeholder="Nom"></div>
        <div class="form-group"><label>Tél. urgence</label><input type="tel" name="emergency_contact_phone"></div>
        <div class="form-group"><label>Groupe sanguin</label>
            <select name="blood_type">
                <option value="unknown">Inconnu</option>
                <option value="A+">A+</option><option value="A-">A-</option>
                <option value="B+">B+</option><option value="B-">B-</option>
                <option value="AB+">AB+</option><option value="AB-">AB-</option>
                <option value="O+">O+</option><option value="O-">O-</option>
            </select>
        </div>
        <div class="form-group full"><label>Allergies</label><textarea name="allergies" rows="2"></textarea></div>
        <div class="form-group full"><label>Maladies chroniques</label><textarea name="chronic_diseases" rows="2"></textarea></div>
        <div class="form-group"><label>Assurance</label><input type="text" name="insurance_name"></div>
        <div class="form-group"><label>N° assurance</label><input type="text" name="insurance_number"></div>
        <div class="form-group full"><label>Notes</label><textarea name="notes" rows="3"></textarea></div>
        <div class="form-actions full">
            <a href="/patients" class="btn btn-ghost">Annuler</a>
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Créer le patient</button>
        </div>
    </form>
</div>

<script>
document.getElementById('createPatientForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    try {
        const res = await fetch('/patients/store', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            showToast('Patient créé avec succès', 'success');
            setTimeout(() => window.location.href = data.redirect, 800);
        } else {
            showToast(data.error || 'Erreur', 'error');
        }
    } catch (err) {
        showToast('Erreur réseau', 'error');
    }
});
</script>
