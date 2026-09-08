<?php
$menuItems = [
    ['icon' => 'fa-chart-line', 'label' => t('nav.dashboard'), 'route' => '/dashboard', 'perm' => 'dashboard.view'],
    ['icon' => 'fa-users', 'label' => t('nav.patients'), 'route' => '/patients', 'perm' => 'patients.view'],
    ['icon' => 'fa-calendar-check', 'label' => t('nav.appointments'), 'route' => '/appointments', 'perm' => 'appointments.view'],
    ['icon' => 'fa-stethoscope', 'label' => t('nav.consultations'), 'route' => '/consultations', 'perm' => 'consultations.view'],
    ['icon' => 'fa-user-doctor', 'label' => t('nav.doctors'), 'route' => '/doctors', 'perm' => 'doctors.view'],
    ['icon' => 'fa-file-medical', 'label' => t('nav.medical_records'), 'route' => '/medical-records', 'perm' => 'medical_records.view'],
    ['icon' => 'fa-prescription', 'label' => t('nav.prescriptions'), 'route' => '/prescriptions', 'perm' => 'prescriptions.view'],
    ['icon' => 'fa-pills', 'label' => t('nav.medications'), 'route' => '/medications', 'perm' => 'medications.view'],
    ['icon' => 'fa-flask', 'label' => t('nav.lab_results'), 'route' => '/lab-results', 'perm' => 'lab_results.view'],
    ['icon' => 'fa-file-invoice-dollar', 'label' => t('nav.billing'), 'route' => '/billing', 'perm' => 'billing.view'],
    ['icon' => 'fa-folder-open', 'label' => t('nav.documents'), 'route' => '/documents', 'perm' => 'documents.view'],
    ['icon' => 'fa-chart-pie', 'label' => t('nav.analytics'), 'route' => '/analytics', 'perm' => 'analytics.view'],
    ['icon' => 'fa-bell', 'label' => t('nav.notifications'), 'route' => '/notifications', 'perm' => 'dashboard.view'],
    ['icon' => 'fa-gear', 'label' => t('nav.settings'), 'route' => '/settings', 'perm' => 'settings.manage'],
];
$currentUri = trim(parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH), '/');
?>
<div class="sidebar-overlay" id="sidebarOverlay"></div>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-brand">
        <div class="logo-icon">
            <svg viewBox="0 0 40 40" fill="none"><rect width="40" height="40" rx="10" fill="#0ea5e9"/>
                <path d="M20 10v20M10 20h20" stroke="white" stroke-width="3" stroke-linecap="round"/>
            </svg>
        </div>
        <div style="display:flex;flex-direction:column;min-width:0">
            <span class="brand-text">Docly</span>
            <?php if (!empty($_SESSION['cabinet_name'])): ?>
            <span style="font-size:0.6875rem;color:var(--text-muted);white-space:nowrap;overflow:hidden;text-overflow:ellipsis"><?= e($_SESSION['cabinet_name']) ?></span>
            <?php endif; ?>
        </div>
    </div>
    <nav class="sidebar-nav">
        <?php foreach ($menuItems as $item): ?>
            <?php if (can($item['perm'])): ?>
            <a href="<?= $item['route'] ?>" class="sidebar-link <?= str_starts_with($currentUri, ltrim($item['route'], '/')) ? 'active' : '' ?>" title="<?= e($item['label']) ?>">
                <i class="fa-solid <?= $item['icon'] ?>"></i>
                <span><?= e($item['label']) ?></span>
            </a>
            <?php endif; ?>
        <?php endforeach; ?>
    </nav>
    <div class="sidebar-footer">
        <div class="user-mini">
            <div class="user-avatar"><?= substr($_SESSION['user_name'] ?? 'U', 0, 1) ?></div>
            <div class="user-info">
                <span class="user-name"><?= e($_SESSION['user_name'] ?? 'Utilisateur') ?></span>
                <span class="user-role" style="color:<?= $_SESSION['user_role_color'] ?? '#666' ?>"><?= e($_SESSION['user_role_name'] ?? '') ?></span>
            </div>
        </div>
    </div>
</aside>
