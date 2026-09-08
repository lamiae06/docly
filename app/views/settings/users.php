<?php $users = $users ?? []; $roles = $roles ?? []; ?>
<div class="page-header">
    <div class="header-back">
        <a href="/settings" class="btn btn-ghost btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <h1><i class="fa-solid fa-users-gear"></i> Utilisateurs</h1>
    </div>
    <div class="page-header-actions">
        <button class="btn btn-primary" onclick="document.getElementById('newUserCard').classList.toggle('hidden')">
            <i class="fa-solid fa-user-plus"></i> Nouvel utilisateur
        </button>
    </div>
</div>

<div class="card hidden" id="newUserCard" style="margin-bottom:1.5rem">
    <form id="newUserForm" class="form-grid">
        <div class="form-group"><label>Prénom *</label><input type="text" name="first_name" required></div>
        <div class="form-group"><label>Nom *</label><input type="text" name="last_name" required></div>
        <div class="form-group"><label>Email *</label><input type="email" name="email" required></div>
        <div class="form-group"><label>Téléphone</label><input type="tel" name="phone"></div>
        <div class="form-group">
            <label>Rôle *</label>
            <select name="role_id" required>
                <option value="">Sélectionner</option>
                <?php foreach ($roles as $r): ?>
                <option value="<?= $r['id'] ?>"><?= e($r['name']) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group"><label>Mot de passe *</label><input type="password" name="password" required minlength="8"></div>
        <div class="form-actions full">
            <button type="button" class="btn btn-ghost" onclick="document.getElementById('newUserCard').classList.add('hidden')">Annuler</button>
            <button type="submit" class="btn btn-primary"><i class="fa-solid fa-check"></i> Créer l'utilisateur</button>
        </div>
    </form>
</div>

<div class="card">
    <div class="table-responsive">
        <table class="data-table">
            <thead><tr><th>Utilisateur</th><th>Email</th><th>Rôle</th><th>Statut</th><th>Dernière connexion</th><th></th></tr></thead>
            <tbody>
            <?php foreach ($users as $u): ?>
                <tr>
                    <td>
                        <div class="patient-cell">
                            <div class="cell-avatar" style="background:<?= e($u['role_color'] ?? '#64748b') ?>"><?= strtoupper(substr($u['first_name'],0,1).substr($u['last_name'],0,1)) ?></div>
                            <span class="cell-name"><?= e($u['first_name'].' '.$u['last_name']) ?></span>
                        </div>
                    </td>
                    <td><?= e($u['email']) ?></td>
                    <td><span class="role-badge" style="background:<?= e($u['role_color'] ?? '#64748b') ?>"><?= e($u['role_name']) ?></span></td>
                    <td><?= $u['is_active'] ? '<span class="badge badge-green">Actif</span>' : '<span class="badge badge-gray">Inactif</span>' ?></td>
                    <td><?= $u['last_login'] ? format_date($u['last_login'], 'd/m/Y H:i') : 'Jamais' ?></td>
                    <td>
                        <button class="btn btn-ghost btn-sm" onclick="toggleUser(<?= $u['id'] ?>, this)">
                            <i class="fa-solid <?= $u['is_active'] ? 'fa-user-slash' : 'fa-user-check' ?>"></i>
                        </button>
                    </td>
                </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
document.getElementById('newUserForm').addEventListener('submit', async (e) => {
    e.preventDefault();
    const formData = new FormData(e.target);
    try {
        const res = await fetch('/api/settings/users', { method: 'POST', body: formData });
        const data = await res.json();
        if (data.success) {
            showToast('Utilisateur créé', 'success');
            setTimeout(() => window.location.reload(), 700);
        } else {
            showToast(data.error || 'Erreur', 'error');
        }
    } catch (err) {
        showToast('Erreur réseau', 'error');
    }
});

async function toggleUser(id, btn) {
    btn.disabled = true;
    try {
        const res = await fetch('/api/settings/users/' + id + '/toggle', { method: 'POST' });
        const data = await res.json();
        if (data.success) {
            showToast('Statut mis à jour', 'success');
            setTimeout(() => window.location.reload(), 500);
        } else {
            showToast(data.error || 'Erreur', 'error');
            btn.disabled = false;
        }
    } catch (err) {
        showToast('Erreur réseau', 'error');
        btn.disabled = false;
    }
}
</script>
