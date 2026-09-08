<?php $patient = $patient ?? []; $tab = $_GET['tab'] ?? 'overview'; ?>
<div class="page-header">
    <div class="header-back">
        <a href="/patients" class="btn btn-ghost btn-sm"><i class="fa-solid fa-arrow-left"></i></a>
        <h1><?= e($patient['first_name'].' '.$patient['last_name']) ?></h1>
    </div>
    <div class="page-header-actions">
        <span class="code-badge"><?= e($patient['patient_code']) ?></span>
        <a href="/patients/<?= $patient['id'] ?>/edit" class="btn btn-ghost btn-sm"><i class="fa-solid fa-pen"></i></a>
    </div>
</div>

<div class="patient-profile">
    <div class="profile-card">
        <div class="profile-avatar" style="background:<?= blood_type_color($patient['blood_type']) ?>">
            <?= substr($patient['first_name'],0,1).substr($patient['last_name'],0,1) ?>
        </div>
        <h2><?= e($patient['first_name'].' '.$patient['last_name']) ?></h2>
        <p class="profile-meta"><?= calculate_age($patient['date_of_birth']) ?> ans • <?= $patient['gender'] === 'male' ? 'Homme' : ($patient['gender'] === 'female' ? 'Femme' : 'Autre') ?></p>

        <div class="profile-details">
            <div class="detail-row"><i class="fa-solid fa-phone"></i><span><?= e($patient['phone']) ?></span></div>
            <div class="detail-row"><i class="fa-regular fa-envelope"></i><span><?= e($patient['email'] ?: '-') ?></span></div>
            <div class="detail-row"><i class="fa-solid fa-location-dot"></i><span><?= e($patient['address'] ?: '-') ?>, <?= e($patient['city'] ?: '') ?></span></div>
            <div class="detail-row"><i class="fa-solid fa-droplet"></i><span>Groupe sanguin: <strong><?= $patient['blood_type'] ?></strong></span></div>
            <div class="detail-row"><i class="fa-solid fa-shield-heart"></i><span><?= e($patient['insurance_name'] ?: 'Aucune assurance') ?></span></div>
        </div>

        <div class="profile-alerts">
            <?php if ($patient['allergies']): ?>
            <div class="alert-box alert-danger"><i class="fa-solid fa-triangle-exclamation"></i> <strong>Allergies:</strong> <?= e($patient['allergies']) ?></div>
            <?php endif; ?>
            <?php if ($patient['chronic_diseases']): ?>
            <div class="alert-box alert-warning"><i class="fa-solid fa-notes-medical"></i> <strong>Antécédents:</strong> <?= e($patient['chronic_diseases']) ?></div>
            <?php endif; ?>
        </div>
    </div>

    <div class="profile-content">
        <div class="tabs">
            <a href="?tab=overview" class="tab <?= $tab === 'overview' ? 'active' : '' ?>">Vue d'ensemble</a>
            <a href="?tab=history" class="tab <?= $tab === 'history' ? 'active' : '' ?>">Historique médical</a>
            <a href="?tab=appointments" class="tab <?= $tab === 'appointments' ? 'active' : '' ?>">Rendez-vous</a>
            <a href="?tab=consultations" class="tab <?= $tab === 'consultations' ? 'active' : '' ?>">Consultations</a>
            <a href="?tab=prescriptions" class="tab <?= $tab === 'prescriptions' ? 'active' : '' ?>">Ordonnances</a>
            <a href="?tab=lab" class="tab <?= $tab === 'lab' ? 'active' : '' ?>">Analyses</a>
            <a href="?tab=documents" class="tab <?= $tab === 'documents' ? 'active' : '' ?>">Documents</a>
            <a href="?tab=billing" class="tab <?= $tab === 'billing' ? 'active' : '' ?>">Facturation</a>
        </div>

        <div class="tab-content">
            <?php if ($tab === 'overview'): ?>
            <div class="overview-grid">
                <div class="info-card">
                    <h4><i class="fa-solid fa-user-shield"></i> Contact d'urgence</h4>
                    <p><strong><?= e($patient['emergency_contact_name'] ?: '-') ?></strong></p>
                    <p><?= e($patient['emergency_contact_phone'] ?: '-') ?></p>
                </div>
                <div class="info-card">
                    <h4><i class="fa-solid fa-calendar-check"></i> Dernier rendez-vous</h4>
                    <p><?= !empty($patient['appointments']) ? format_date($patient['appointments'][0]['appointment_date']).' à '.format_time($patient['appointments'][0]['appointment_time']) : 'Aucun' ?></p>
                </div>
                <div class="info-card">
                    <h4><i class="fa-solid fa-stethoscope"></i> Dernière consultation</h4>
                    <p><?= !empty($patient['consultations']) ? format_date($patient['consultations'][0]['consultation_date']) : 'Aucune' ?></p>
                </div>
                <div class="info-card">
                    <h4><i class="fa-solid fa-file-invoice-dollar"></i> Solde</h4>
                    <p><?= format_money(array_sum(array_column(array_filter($patient['invoices'] ?? [], fn($i) => $i['status'] !== 'paid'), 'balance_due'))) ?></p>
                </div>
            </div>

            <h3 class="section-title">Timeline médicale</h3>
            <div class="timeline">
                <?php 
                $events = [];
                foreach ($patient['consultations'] ?? [] as $c) $events[] = ['date' => $c['consultation_date'], 'type' => 'consultation', 'title' => 'Consultation', 'desc' => $c['diagnosis'] ?: 'Consultation avec Dr. '.$c['doctor_last_name'], 'icon' => 'fa-stethoscope', 'color' => '#3b82f6'];
                foreach ($patient['prescriptions'] ?? [] as $p) $events[] = ['date' => $p['prescription_date'], 'type' => 'prescription', 'title' => 'Ordonnance', 'desc' => 'Ordonnance émise par Dr. '.$p['doctor_last_name'], 'icon' => 'fa-prescription', 'color' => '#10b981'];
                foreach ($patient['lab_results'] ?? [] as $l) $events[] = ['date' => $l['test_date'], 'type' => 'lab', 'title' => 'Analyse: '.$l['test_name'], 'desc' => $l['result_value'].' '.$l['unit'].' (Réf: '.$l['reference_range'].')', 'icon' => 'fa-flask', 'color' => '#8b5cf6'];
                usort($events, fn($a, $b) => strtotime($b['date']) - strtotime($a['date']));
                foreach (array_slice($events, 0, 10) as $e):
                ?>
                <div class="timeline-item">
                    <div class="timeline-dot" style="background:<?= $e['color'] ?>"><i class="fa-solid <?= $e['icon'] ?>"></i></div>
                    <div class="timeline-content">
                        <span class="timeline-date"><?= format_date($e['date']) ?></span>
                        <h4><?= e($e['title']) ?></h4>
                        <p><?= e($e['desc']) ?></p>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php elseif ($tab === 'history'): ?>
            <div class="records-list">
                <?php foreach ($patient['medical_records'] ?? [] as $rec): ?>
                <div class="record-card record-<?= $rec['record_type'] ?>">
                    <div class="record-header">
                        <span class="record-type"><?= ucfirst(str_replace('_', ' ', $rec['record_type'])) ?></span>
                        <span class="record-severity severity-<?= $rec['severity'] ?>"><?= ucfirst($rec['severity']) ?></span>
                    </div>
                    <h4><?= e($rec['title']) ?></h4>
                    <p><?= e($rec['description']) ?></p>
                    <div class="record-meta">
                        <span>Diagnostic: <?= format_date($rec['diagnosed_date']) ?></span>
                        <span class="record-status status-<?= $rec['status'] ?>"><?= ucfirst($rec['status']) ?></span>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php elseif ($tab === 'appointments'): ?>
            <table class="data-table">
                <thead><tr><th>Date</th><th>Heure</th><th>Médecin</th><th>Type</th><th>Motif</th><th>Statut</th></tr></thead>
                <tbody>
                    <?php foreach ($patient['appointments'] ?? [] as $a): ?>
                    <tr>
                        <td><?= format_date($a['appointment_date']) ?></td>
                        <td><?= format_time($a['appointment_time']) ?></td>
                        <td>Dr. <?= e($a['doctor_last_name']) ?></td>
                        <td><?= ucfirst(str_replace('_', ' ', $a['type'])) ?></td>
                        <td><?= e(truncate($a['reason'] ?: '-', 40)) ?></td>
                        <td><?= appointment_status_badge($a['status']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php elseif ($tab === 'consultations'): ?>
            <div class="consultations-list">
                <?php foreach ($patient['consultations'] ?? [] as $c): ?>
                <div class="consultation-card">
                    <div class="consultation-header">
                        <span class="consultation-date"><?= format_date($c['consultation_date']) ?></span>
                        <span class="consultation-doctor">Dr. <?= e($c['doctor_last_name']) ?></span>
                    </div>
                    <div class="consultation-body">
                        <div class="consultation-section">
                            <label>Motif principal</label>
                            <p><?= e($c['chief_complaint'] ?: '-') ?></p>
                        </div>
                        <div class="consultation-section">
                            <label>Symptômes</label>
                            <p><?= e($c['symptoms'] ?: '-') ?></p>
                        </div>
                        <div class="consultation-section">
                            <label>Diagnostic</label>
                            <p><?= e($c['diagnosis'] ?: '-') ?> <?= $c['diagnosis_icd10'] ? '<code>'.$c['diagnosis_icd10'].'</code>' : '' ?></p>
                        </div>
                        <div class="consultation-section">
                            <label>Traitement</label>
                            <p><?= e($c['treatment_plan'] ?: '-') ?></p>
                        </div>
                        <?php if ($c['follow_up_date']): ?>
                        <div class="consultation-followup">
                            <i class="fa-regular fa-calendar"></i> Prochain rendez-vous: <?= format_date($c['follow_up_date']) ?>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php elseif ($tab === 'prescriptions'): ?>
            <div class="prescriptions-list">
                <?php foreach ($patient['prescriptions'] ?? [] as $pr): ?>
                <div class="prescription-card">
                    <div class="prescription-header">
                        <span>Ordonnance du <?= format_date($pr['prescription_date']) ?></span>
                        <span>Dr. <?= e($pr['doctor_last_name']) ?></span>
                        <?= $pr['status'] === 'issued' ? '<span class="badge badge-green">Émise</span>' : '<span class="badge badge-gray">Brouillon</span>' ?>
                    </div>
                    <p class="prescription-notes"><?= e($pr['notes'] ?: '') ?></p>
                </div>
                <?php endforeach; ?>
            </div>

            <?php elseif ($tab === 'lab'): ?>
            <table class="data-table">
                <thead><tr><th>Date</th><th>Analyse</th><th>Catégorie</th><th>Résultat</th><th>Référence</th><th>Statut</th></tr></thead>
                <tbody>
                    <?php foreach ($patient['lab_results'] ?? [] as $l): ?>
                    <tr>
                        <td><?= format_date($l['test_date']) ?></td>
                        <td><?= e($l['test_name']) ?></td>
                        <td><?= e($l['test_category'] ?: '-') ?></td>
                        <td><strong><?= e($l['result_value'] ?: '-') ?></strong> <?= e($l['unit'] ?: '') ?></td>
                        <td><?= e($l['reference_range'] ?: '-') ?></td>
                        <td><?= lab_status_badge($l['status']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>

            <?php elseif ($tab === 'documents'): ?>
            <div class="documents-grid">
                <?php foreach ($patient['documents'] ?? [] as $doc): ?>
                <div class="document-card">
                    <div class="document-icon"><i class="fa-solid fa-file-pdf"></i></div>
                    <div class="document-info">
                        <h4><?= e($doc['title']) ?></h4>
                        <p><?= e($doc['category']) ?> • <?= format_date($doc['created_at']) ?></p>
                    </div>
                    <a href="<?= e($doc['file_path']) ?>" class="btn btn-sm btn-ghost" download><i class="fa-solid fa-download"></i></a>
                </div>
                <?php endforeach; ?>
            </div>

            <?php elseif ($tab === 'billing'): ?>
            <table class="data-table">
                <thead><tr><th>N°</th><th>Date</th><th>Montant</th><th>Payé</th><th>Solde</th><th>Statut</th></tr></thead>
                <tbody>
                    <?php foreach ($patient['invoices'] ?? [] as $inv): ?>
                    <tr>
                        <td><span class="code-badge"><?= e($inv['invoice_number']) ?></span></td>
                        <td><?= format_date($inv['issue_date']) ?></td>
                        <td><?= format_money($inv['total_amount']) ?></td>
                        <td><?= format_money($inv['total_paid'] ?? 0) ?></td>
                        <td><?= format_money($inv['balance_due']) ?></td>
                        <td><?= invoice_status_badge($inv['status']) ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
            <?php endif; ?>
        </div>
    </div>
</div>
