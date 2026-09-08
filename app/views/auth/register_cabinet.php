<h2 style="text-align:center;margin-bottom:1.5rem;font-size:1.125rem;color:var(--text-secondary)">
    <i class="fa-solid fa-building" style="color:var(--primary)"></i> Créer votre cabinet
</h2>
<p style="text-align:center;margin:-1rem 0 1.5rem;color:var(--text-muted);font-size:0.8125rem">
    Une base de données dédiée et isolée est créée automatiquement pour vous
</p>

<form id="registerForm" class="auth-form">
    <div class="form-group">
        <label for="cabinet_name">Nom du cabinet</label>
        <div class="input-group">
            <i class="fa-solid fa-building"></i>
            <input type="text" id="cabinet_name" name="cabinet_name" placeholder="Clinique Dupont" required autofocus>
        </div>
    </div>
    <div class="form-group">
        <label for="first_name">Votre prénom</label>
        <div class="input-group">
            <i class="fa-regular fa-user"></i>
            <input type="text" id="first_name" name="first_name" required>
        </div>
    </div>
    <div class="form-group">
        <label for="last_name">Votre nom</label>
        <div class="input-group">
            <i class="fa-regular fa-user"></i>
            <input type="text" id="last_name" name="last_name" required>
        </div>
    </div>
    <div class="form-group">
        <label for="email">Votre email (identifiant administrateur)</label>
        <div class="input-group">
            <i class="fa-regular fa-envelope"></i>
            <input type="email" id="email" name="email" required>
        </div>
    </div>
    <div class="form-group">
        <label for="password">Mot de passe</label>
        <div class="input-group">
            <i class="fa-solid fa-lock"></i>
            <input type="password" id="password" name="password" required minlength="8">
        </div>
    </div>
    <div class="form-group">
        <label for="confirm_password">Confirmer le mot de passe</label>
        <div class="input-group">
            <i class="fa-solid fa-lock"></i>
            <input type="password" id="confirm_password" name="confirm_password" required minlength="8">
        </div>
    </div>
    <button type="submit" class="btn btn-primary btn-block" id="registerBtn">
        <span class="btn-text">Créer mon cabinet</span>
        <span class="btn-loader hidden"><i class="fa-solid fa-circle-notch fa-spin"></i></span>
    </button>
    <div class="login-error hidden" id="registerError"></div>
</form>

<div class="auth-demo">
    <p>Vous avez déjà un cabinet ? <a href="/login" style="color:var(--primary);font-weight:600">Se connecter</a></p>
</div>

<script>
document.getElementById('registerForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('registerBtn');
    const errorDiv = document.getElementById('registerError');
    const btnText = btn.querySelector('.btn-text');
    const btnLoader = btn.querySelector('.btn-loader');
    errorDiv.classList.add('hidden');
    btn.disabled = true;
    btnText.classList.add('hidden');
    btnLoader.classList.remove('hidden');

    const formData = new FormData(this);
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    try {
        const res = await fetch('/api/cabinets/register', {
            method: 'POST', body: formData,
            headers: { 'X-CSRF-Token': csrfToken }
        });
        const data = await res.json();
        if (data.success) {
            showToast('Cabinet créé avec succès !', 'success');
            setTimeout(() => window.location.href = data.redirect, 500);
        } else {
            let msg = data.error || 'Erreur lors de la création';
            if (data.errors) msg = Object.values(data.errors).join(' ');
            errorDiv.textContent = msg;
            errorDiv.classList.remove('hidden');
        }
    } catch (err) {
        errorDiv.textContent = 'Erreur réseau. Veuillez réessayer.';
        errorDiv.classList.remove('hidden');
    } finally {
        btn.disabled = false;
        btnText.classList.remove('hidden');
        btnLoader.classList.add('hidden');
    }
});
</script>
