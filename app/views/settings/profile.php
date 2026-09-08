<?php $user = $user ?? []; ?>
<div class="page-header">
    <div class="header-back">
        <a href="/settings" class="btn btn-ghost btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <h1><i class="fa-regular fa-user"></i> Profil</h1>
    </div>
</div>

<div class="card">
    <form id="profileForm" class="form-grid">
        <div class="form-group"><label>Prénom *</label><input type="text" name="first_name" value="<?= e($user['first_name'] ?? '') ?>" required></div>
        <div class="form-group"><label>Nom *</label><input type="text" name="last_name" value="<?= e($user['last_name'] ?? '') ?>" required></div>
        <div class="form-group"><label>Email</label><input type="email" value="<?= e($user['email'] ?? '') ?>" disabled></div>
        <div class="form-group"><label>Téléphone</label><input type="tel" name="phone" value="<?= e($user['phone'] ?? '') ?>"></div>
        <div class="form-group"><label>Rôle</label><input type="text" value="<?= e($user['role_name'] ?? '') ?>" disabled></div>
        <div class="form-group">
            <label>Thème</label>
            <select name="theme">
                <?php $theme = $user['theme'] ?? 'system'; ?>
                <option value="system" <?= $theme==='system'?'selected':'' ?>>Système</option>
                <option value="light" <?= $theme==='light'?'selected':'' ?>>Clair</option>
                <option value="dark" <?= $theme==='dark'?'selected':'' ?>>Sombre</option>
            </select>
        </div>
        <div class="form-actions full">
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Enregistrer</button>
        </div>
    </form>
</div>

<script>
document.getElementById('profileForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    try {
        const res = await fetch('/api/settings/profile', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            showToast('Profil mis à jour', 'success');
            const theme = formData.get('theme');
            if (theme && theme !== 'system') {
                localStorage.setItem('docly-theme', theme);
                applyTheme(theme);
            }
            setTimeout(() => window.location.reload(), 700);
        } else {
            showToast(data.error || 'Erreur', 'error');
        }
    } catch (err) {
        showToast('Erreur réseau', 'error');
    }
});
</script>
