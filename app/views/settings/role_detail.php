<?php
$role = $role ?? [];
$groupedPermissions = $groupedPermissions ?? [];
$grantedIds = $grantedIds ?? [];

$moduleLabels = [
    'dashboard' => 'Tableau de bord',
    'patients' => 'Patients',
    'appointments' => 'Rendez-vous',
    'consultations' => 'Consultations',
    'doctors' => 'Médecins',
    'medical_records' => 'Dossiers médicaux',
    'prescriptions' => 'Ordonnances',
    'medications' => 'Médicaments',
    'lab_results' => 'Analyses',
    'billing' => 'Facturation',
    'documents' => 'Documents',
    'analytics' => 'Analytics',
    'settings' => 'Paramètres',
    'notifications' => 'Notifications',
];
?>
<div class="page-header">
    <div class="header-back">
        <a href="/settings/roles" class="btn btn-ghost btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <h1><i class="fa-solid fa-user-shield" style="color:<?= e($role['color'] ?? '#3b82f6') ?>"></i> <?= e($role['name']) ?></h1>
    </div>
</div>

<div class="card" style="padding:1.5rem">
    <p style="color:var(--text-muted);margin-bottom:1.25rem">
        Cochez les pages et actions auxquelles le rôle <strong><?= e($role['name']) ?></strong> a accès.
        Une case cochée autorise l'accès, décochée le retire — pour tous les utilisateurs ayant ce rôle,
        dès leur prochaine action (pas besoin de se reconnecter).
    </p>

    <form id="permissionsForm" method="post" action="/api/settings/roles/<?= (int) $role['id'] ?>/permissions">
        <input type="hidden" name="_csrf_token" value="<?= csrf_token() ?>">
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th style="width:40%">Module</th>
                        <th>Permission</th>
                        <th style="width:100px;text-align:center">Autorisé</th>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($groupedPermissions as $moduleKey => $perms): ?>
                    <?php foreach ($perms as $i => $perm): ?>
                    <tr>
                        <?php if ($i === 0): ?>
                        <td rowspan="<?= count($perms) ?>" style="font-weight:600;vertical-align:middle;border-right:1px solid var(--border)">
                            <?= e($moduleLabels[$moduleKey] ?? ucfirst($moduleKey)) ?>
                        </td>
                        <?php endif; ?>
                        <td>
                            <div><?= e($perm['name']) ?></div>
                            <?php if (!empty($perm['description'])): ?>
                            <div style="font-size:0.75rem;color:var(--text-muted)"><?= e($perm['description']) ?></div>
                            <?php endif; ?>
                        </td>
                        <td style="text-align:center">
                            <input
                                type="checkbox"
                                class="perm-checkbox"
                                value="<?= (int) $perm['id'] ?>"
                                <?= in_array((int) $perm['id'], $grantedIds, true) ? 'checked' : '' ?>
                                style="width:18px;height:18px;cursor:pointer"
                            >
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <div class="form-actions" style="margin-top:1.25rem">
            <a href="/settings/roles" class="btn btn-ghost">Annuler</a>
            <button type="submit" class="btn btn-primary" id="savePermsBtn">
                <i class="fa-solid fa-check"></i> Enregistrer les permissions
            </button>
        </div>
    </form>
</div>

<script>
document.getElementById('permissionsForm').addEventListener('submit', async function(e) {
    e.preventDefault();
    const btn = document.getElementById('savePermsBtn');
    btn.disabled = true;

    const ids = Array.from(document.querySelectorAll('.perm-checkbox:checked')).map(cb => cb.value);

    try {
        const formData = new FormData();
        ids.forEach(id => formData.append('permission_ids[]', id));

        const res = await fetch('/api/settings/roles/<?= (int) $role['id'] ?>/permissions', {
            method: 'POST',
            body: formData,
        });
        const data = await res.json();
        if (data.success) {
            showToast('Permissions enregistrées', 'success');
        } else {
            showToast(data.error || 'Erreur lors de l\'enregistrement', 'error');
        }
    } catch (err) {
        showToast('Erreur réseau', 'error');
    } finally {
        btn.disabled = false;
    }
});
</script>
