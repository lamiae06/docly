<?php
$appointments = $appointments ?? ['data' => [], 'total' => 0];
$doctors = $doctors ?? [];
?>
<div class="page-header">
    <h1><i class="fa-solid fa-calendar-check"></i> Rendez-vous</h1>
    <a href="/appointments/create" class="btn btn-primary"><i class="fa-solid fa-plus"></i> Nouveau RDV</a>
</div>

<div class="card">
    <div class="card-toolbar filters">
        <input type="date" id="filterDateFrom" value="<?= e($dateFrom ?? date('Y-m-d')) ?>">
        <input type="date" id="filterDateTo" value="<?= e($dateTo ?? date('Y-m-d', strtotime('+7 days'))) ?>">
        <select id="filterDoctor">
            <option value="0">Tous les médecins</option>
            <?php foreach ($doctors as $d): ?>
            <option value="<?= $d['id'] ?>" <?= ($doctorId ?? 0) == $d['id'] ? 'selected' : '' ?>>Dr. <?= e($d['last_name']) ?></option>
            <?php endforeach; ?>
        </select>
        <select id="filterStatus">
            <option value="">Tous les statuts</option>
            <option value="scheduled" <?= ($status ?? '') === 'scheduled' ? 'selected' : '' ?>>Programmé</option>
            <option value="confirmed" <?= ($status ?? '') === 'confirmed' ? 'selected' : '' ?>>Confirmé</option>
            <option value="in_progress" <?= ($status ?? '') === 'in_progress' ? 'selected' : '' ?>>En cours</option>
            <option value="completed" <?= ($status ?? '') === 'completed' ? 'selected' : '' ?>>Terminé</option>
            <option value="cancelled" <?= ($status ?? '') === 'cancelled' ? 'selected' : '' ?>>Annulé</option>
        </select>
        <button class="btn btn-ghost" onclick="applyFilters()"><i class="fa-solid fa-filter"></i> Filtrer</button>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr><th>Date</th><th>Heure</th><th>Patient</th><th>Médecin</th><th>Type</th><th>Motif</th><th>Statut</th><th>Actions</th></tr>
            </thead>
            <tbody>
                <?php foreach ($appointments['data'] as $a): ?>
                <tr>
                    <td><?= format_date($a['appointment_date']) ?></td>
                    <td><?= format_time($a['appointment_time']) ?></td>
                    <td><a href="/patients/<?= $a['patient_id'] ?>"><?= e($a['patient_first_name'].' '.$a['patient_last_name']) ?></a></td>
                    <td>Dr. <?= e($a['doctor_last_name']) ?> <small><?= e($a['specialty']) ?></small></td>
                    <td><?= ucfirst(str_replace('_', ' ', $a['type'])) ?></td>
                    <td><?= e(truncate($a['reason'] ?: '-', 30)) ?></td>
                    <td><?= appointment_status_badge($a['status']) ?></td>
                    <td>
                        <select class="status-select" onchange="updateStatus(<?= $a['id'] ?>, this.value)">
                            <option value="scheduled" <?= $a['status']==='scheduled'?'selected':'' ?>>Programmé</option>
                            <option value="confirmed" <?= $a['status']==='confirmed'?'selected':'' ?>>Confirmé</option>
                            <option value="in_progress" <?= $a['status']==='in_progress'?'selected':'' ?>>En cours</option>
                            <option value="completed" <?= $a['status']==='completed'?'selected':'' ?>>Terminé</option>
                            <option value="cancelled" <?= $a['status']==='cancelled'?'selected':'' ?>>Annulé</option>
                            <option value="no_show" <?= $a['status']==='no_show'?'selected':'' ?>>Absent</option>
                        </select>
                    </td>
                    <td>
                        <div style="display:flex;gap:0.375rem">
                            <a href="/appointments/<?= $a['id'] ?>/edit" class="btn btn-sm btn-ghost" title="Modifier"><i class="fa-solid fa-pen"></i></a>
                            <a href="/settings/audit-logs?entity_type=appointment&entity_id=<?= $a['id'] ?>" class="btn btn-sm btn-ghost" title="Historique"><i class="fa-solid fa-clock-rotate-left"></i></a>
                            <button type="button" class="btn btn-sm btn-ghost" title="Supprimer" onclick="doclyConfirmDelete('/appointments/<?= $a['id'] ?>/delete', this.closest('tr'))"><i class="fa-solid fa-trash" style="color:var(--danger)"></i></button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

<script>
function applyFilters() {
    const params = new URLSearchParams();
    params.set('date_from', document.getElementById('filterDateFrom').value);
    params.set('date_to', document.getElementById('filterDateTo').value);
    params.set('doctor_id', document.getElementById('filterDoctor').value);
    params.set('status', document.getElementById('filterStatus').value);
    window.location.href = '/appointments?' + params.toString();
}
async function updateStatus(id, status) {
    const formData = new FormData();
    formData.append('status', status);
    await fetch('/api/appointments/' + id + '/status', { method: 'POST', body: formData });
    showToast('Statut mis à jour', 'success');
}
</script>
