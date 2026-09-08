<h2 style="text-align:center;margin-bottom:1.5rem;font-size:1.125rem;color:var(--text-secondary)">
    <i class="fa-solid fa-shield-halved" style="color:var(--primary)"></i> Administration plateforme
</h2>
<p style="text-align:center;margin:-1rem 0 1.5rem;color:var(--text-muted);font-size:0.8125rem">
    Espace réservé aux administrateurs de la plateforme
</p>

<form id="platformLoginForm" class="auth-form">
    <div class="form-group">
        <label for="email">Email</label>
        <div class="input-group">
            <i class="fa-regular fa-envelope"></i>
            <input type="email" id="email" name="email" required autofocus>
        </div>
    </div>
    <div class="form-group">
        <label for="password">Mot de passe</label>
        <div class="input-group">
            <i class="fa-solid fa-lock"></i>
            <input type="password" id="password" name="password" required>
        </div>
    </div>
    <button type="submit" class="btn btn-primary btn-block" id="submitBtn">Se connecter</button>
    <div id="loginError" class="login-error hidden"></div>
</form>

<div class="auth-demo">
    <p><a href="/login" style="color:var(--primary);font-weight:600">&larr; Retour à la connexion cabinet</a></p>
</div>

<script>
document.getElementById('platformLoginForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const errorBox = document.getElementById('loginError');
    errorBox.classList.add('hidden');
    const formData = new FormData(this);
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    try {
        const res = await fetch('/api/platform/login', {
            method: 'POST', body: formData,
            headers: { 'X-CSRF-Token': csrfToken }
        });
        const data = await res.json();
        if (data.success) {
            window.location.href = data.redirect;
        } else {
            errorBox.textContent = data.error || 'Erreur de connexion';
            errorBox.classList.remove('hidden');
        }
    } catch (err) {
        errorBox.textContent = 'Erreur réseau';
        errorBox.classList.remove('hidden');
    }
});
</script>
