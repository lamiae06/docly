<?php
$query = $query ?? '';
$patients = $patients ?? ['data' => [], 'total' => 0, 'current_page' => 1, 'last_page' => 1];
?>
<div class="page-header">
    <h1><i class="fa-solid fa-users"></i> Patients</h1>
    <a href="/patients/create" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Nouveau patient</a>
</div>

<div class="card">
    <div class="card-toolbar">
        <div class="search-box">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input type="text" id="patientSearch" placeholder="Rechercher un patient..." value="<?= e($query) ?>">
        </div>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Code</th>
                    <th>Patient</th>
                    <th>Âge</th>
                    <th>Téléphone</th>
                    <th>Groupe</th>
                    <th>Statut</th>
                    <th>Inscription</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="patientsTable">
                <?php foreach ($patients['data'] as $p): ?>
                <tr>
                    <td><span class="code-badge"><?= e($p['patient_code']) ?></span></td>
                    <td>
                        <div class="patient-cell">
                            <div class="cell-avatar" style="background:<?= blood_type_color($p['blood_type']) ?>"><?= substr($p['first_name'],0,1).substr($p['last_name'],0,1) ?></div>
                            <div>
                                <div class="cell-name"><?= e($p['first_name'].' '.$p['last_name']) ?></div>
                                <div class="cell-email"><?= e($p['email'] ?: '-') ?></div>
                            </div>
                        </div>
                    </td>
                    <td><?= calculate_age($p['date_of_birth']) ?> ans</td>
                    <td><?= e($p['phone']) ?></td>
                    <td><span class="blood-badge" style="background:<?= blood_type_color($p['blood_type']) ?>20;color:<?= blood_type_color($p['blood_type']) ?>"><?= $p['blood_type'] ?></span></td>
                    <td><?= $p['is_active'] ? '<span class="badge badge-green">Actif</span>' : '<span class="badge badge-gray">Inactif</span>' ?></td>
                    <td><?= format_date($p['created_at']) ?></td>
                    <td><a href="/patients/<?= $p['id'] ?>" class="btn btn-sm btn-ghost"><i class="fa-solid fa-eye"></i></a></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php if ($patients['last_page'] > 1): ?>
    <div class="pagination">
        <?php for ($i = 1; $i <= $patients['last_page']; $i++): ?>
        <a href="?page=<?= $i ?>&q=<?= urlencode($query) ?>" class="page-link <?= $i == $patients['current_page'] ? 'active' : '' ?>"><?= $i ?></a>
        <?php endfor; ?>
    </div>
    <?php endif; ?>
</div>

<script>
document.getElementById('patientSearch')?.addEventListener('input', debounce(function() {
    const q = this.value;
    window.location.href = '/patients?q=' + encodeURIComponent(q);
}, 500));
</script>
