<?php
$stats = $stats ?? [];
$todayAppointments = $todayAppointments ?? [];
$recentPatients = $recentPatients ?? [];
$alerts = $alerts ?? [];
?>
<div class="dashboard">
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background:#e0f2fe;color:#0284c7"><i class="fa-solid fa-users"></i></div>
            <div class="stat-info">
                <span class="stat-value"><?= $stats['total_patients'] ?? 0 ?></span>
                <span class="stat-label">Patients actifs</span>
                <span class="stat-change positive">+<?= $stats['new_patients_this_month'] ?? 0 ?> ce mois</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#fef3c7;color:#d97706"><i class="fa-solid fa-calendar-check"></i></div>
            <div class="stat-info">
                <span class="stat-value"><?= $stats['today_appointments'] ?? 0 ?></span>
                <span class="stat-label">RDV aujourd'hui</span>
                <span class="stat-change"><?= $stats['today_appointments_confirmed'] ?? 0 ?> confirmes</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#dcfce7;color:#16a34a"><i class="fa-solid fa-stethoscope"></i></div>
            <div class="stat-info">
                <span class="stat-value"><?= $stats['today_consultations'] ?? 0 ?></span>
                <span class="stat-label">Consultations</span>
                <span class="stat-change">Aujourd'hui</span>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon" style="background:#f3e8ff;color:#9333ea"><i class="fa-solid fa-euro-sign"></i></div>
            <div class="stat-info">
                <span class="stat-value"><?= format_money($stats['monthly_revenue'] ?? 0) ?></span>
                <span class="stat-label">Revenus du mois</span>
                <span class="stat-change"><?= $stats['pending_invoices'] ?? 0 ?> factures en attente</span>
            </div>
        </div>
    </div>

    <div class="dashboard-grid">
        <div class="dashboard-card">
            <div class="card-header">
                <h3><i class="fa-regular fa-clock"></i> Rendez-vous du jour</h3>
                <a href="/appointments" class="btn btn-sm btn-ghost">Voir tout</a>
            </div>
            <div class="card-body">
                <?php if (empty($todayAppointments)): ?>
                    <div class="empty-state"><i class="fa-regular fa-calendar-xmark"></i><p>Aucun rendez-vous aujourd'hui</p></div>
                <?php else: ?>
                <div class="appointment-list">
                    <?php foreach ($todayAppointments as $apt): ?>
                    <div class="appointment-item" data-status="<?= $apt['status'] ?>">
                        <div class="apt-time"><?= format_time($apt['appointment_time']) ?></div>
                        <div class="apt-info">
                            <span class="apt-patient"><?= e($apt['patient_first_name'].' '.$apt['patient_last_name']) ?></span>
                            <span class="apt-doctor">Dr. <?= e($apt['doctor_last_name']) ?> • <?= e($apt['type']) ?></span>
                        </div>
                        <div class="apt-status"><?= appointment_status_badge($apt['status']) ?></div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="dashboard-card">
            <div class="card-header">
                <h3><i class="fa-solid fa-user-plus"></i> Patients recents</h3>
                <a href="/patients" class="btn btn-sm btn-ghost">Voir tout</a>
            </div>
            <div class="card-body">
                <div class="patient-list">
                    <?php foreach ($recentPatients as $pat): ?>
                    <a href="/patients/<?= $pat['id'] ?>" class="patient-item">
                        <div class="patient-avatar" style="background:<?= blood_type_color($pat['blood_type']) ?>">
                            <?= substr($pat['first_name'], 0, 1).substr($pat['last_name'], 0, 1) ?>
                        </div>
                        <div class="patient-info">
                            <span class="patient-name"><?= e($pat['first_name'].' '.$pat['last_name']) ?></span>
                            <span class="patient-meta"><?= calculate_age($pat['date_of_birth']) ?> ans • <?= e($pat['phone']) ?></span>
                        </div>
                        <div class="patient-date">
                            <?= $pat['last_appointment_date'] ? format_date($pat['last_appointment_date']) : 'Nouveau' ?>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <div class="dashboard-card wide">
            <div class="card-header"><h3><i class="fa-solid fa-chart-line"></i> Activite</h3></div>
            <div class="card-body">
                <div class="chart-grid">
                    <div class="chart-container"><canvas id="appointmentsChart"></canvas></div>
                    <div class="chart-container"><canvas id="patientsChart"></canvas></div>
                    <div class="chart-container"><canvas id="revenueChart"></canvas></div>
                </div>
            </div>
        </div>

        <div class="dashboard-card">
            <div class="card-header"><h3><i class="fa-solid fa-triangle-exclamation"></i> Alertes</h3></div>
            <div class="card-body">
                <?php if (empty($alerts)): ?>
                    <div class="empty-state"><i class="fa-regular fa-circle-check"></i><p>Aucune alerte</p></div>
                <?php else: ?>
                <div class="alert-list">
                    <?php foreach ($alerts as $alert): ?>
                    <div class="alert-item alert-<?= $alert['severity'] ?>">
                        <div class="alert-icon">
                            <?php if ($alert['type'] === 'appointment'): ?><i class="fa-regular fa-clock"></i>
                            <?php elseif ($alert['type'] === 'medication'): ?><i class="fa-solid fa-pills"></i>
                            <?php elseif ($alert['type'] === 'billing'): ?><i class="fa-solid fa-file-invoice-dollar"></i>
                            <?php else: ?><i class="fa-solid fa-bell"></i><?php endif; ?>
                        </div>
                        <div class="alert-content">
                            <span class="alert-title"><?= e($alert['title']) ?></span>
                            <span class="alert-message"><?= e($alert['message']) ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
