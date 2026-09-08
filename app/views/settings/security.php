<div class="page-header">
    <div class="header-back">
        <a href="/settings" class="btn btn-ghost btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <h1><i class="fa-solid fa-shield-halved"></i> Sécurité</h1>
    </div>
</div>

<div class="card">
    <form id="securityForm" class="form-grid">
        <div class="form-group full"><label>Mot de passe actuel *</label><input type="password" name="current_password" required></div>
        <div class="form-group"><label>Nouveau mot de passe *</label><input type="password" name="new_password" required minlength="8"></div>
        <div class="form-group"><label>Confirmer le nouveau mot de passe *</label><input type="password" name="confirm_password" required minlength="8"></div>
        <div class="form-group full">
            <p style="font-size:0.8125rem;color:var(--text-muted)">Le mot de passe doit contenir au moins 8 caractères.</p>
        </div>
        <div class="form-actions full">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Changer le mot de passe</button>
        </div>
    </form>
</div>

<script>
document.getElementById('securityForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    if (formData.get('new_password') !== formData.get('confirm_password')) {
        showToast('Les mots de passe ne correspondent pas', 'error');
        return;
    }
    try {
        const res = await fetch('/api/settings/security', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            showToast('Mot de passe modifié', 'success');
            e.target.reset();
        } else {
            showToast(data.error || 'Erreur', 'error');
        }
    } catch (err) {
        showToast('Erreur réseau', 'error');
    }
});
</script>
