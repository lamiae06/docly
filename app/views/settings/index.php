<div class="page-header">
    <h1><i class="fa-solid fa-gear"></i> Paramètres</h1>
</div>

<div class="settings-grid">
    <a href="/settings/profile" class="settings-card">
        <div class="settings-icon" style="background:#dbeafe;color:#1d4ed8"><i class="fa-regular fa-user"></i></div>
        <div class="settings-info"><h3>Profil</h3><p>Modifier vos informations personnelles</p></div>
        <i class="fa-solid fa-chevron-right"></i>
    </a>
    <a href="/settings/security" class="settings-card">
        <div class="settings-icon" style="background:#fef3c7;color:#d97706"><i class="fa-solid fa-shield-halved"></i></div>
        <div class="settings-info"><h3>Sécurité</h3><p>Mot de passe et authentification</p></div>
        <i class="fa-solid fa-chevron-right"></i>
    </a>
    <a href="/settings/clinic" class="settings-card">
        <div class="settings-icon" style="background:#dcfce7;color:#15803d"><i class="fa-solid fa-hospital"></i></div>
        <div class="settings-info"><h3>Clinique</h3><p>Informations et paramètres de la clinique</p></div>
        <i class="fa-solid fa-chevron-right"></i>
    </a>
    <?php if (has_role('admin')): ?>
    <a href="/settings/users" class="settings-card">
        <div class="settings-icon" style="background:#f3e8ff;color:#9333ea"><i class="fa-solid fa-users-gear"></i></div>
        <div class="settings-info"><h3>Utilisateurs</h3><p>Gérer les comptes utilisateurs</p></div>
        <i class="fa-solid fa-chevron-right"></i>
    </a>
    <a href="/settings/roles" class="settings-card">
        <div class="settings-icon" style="background:#ffe4e6;color:#e11d48"><i class="fa-solid fa-user-shield"></i></div>
        <div class="settings-info"><h3>Rôles & Permissions</h3><p>Configurer les accès</p></div>
        <i class="fa-solid fa-chevron-right"></i>
    </a>
    <a href="/settings/audit-logs" class="settings-card">
        <div class="settings-icon" style="background:#f1f5f9;color:#475569"><i class="fa-solid fa-list-check"></i></div>
        <div class="settings-info"><h3>Logs d'audit</h3><p>Historique des actions</p></div>
        <i class="fa-solid fa-chevron-right"></i>
    </a>
    <?php endif; ?>
</div>
