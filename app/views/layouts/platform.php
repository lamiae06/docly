<!DOCTYPE html>
<html lang="fr" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <title><?= isset($pageTitle) ? e($pageTitle) . ' - ' : '' ?>Docly Platform</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= asset('assets/css/docly.css') ?>">
</head>
<body>
    <div style="min-height:100vh;background:var(--bg)">
        <header class="main-header" style="position:static">
            <div style="display:flex;align-items:center;gap:0.75rem">
                <div class="logo-icon"><i class="fa-solid fa-shield-halved" style="font-size:1.5rem;color:var(--primary)"></i></div>
                <span class="brand-text">Docly · Administration plateforme</span>
            </div>
            <div class="header-actions">
                <span style="color:var(--text-muted);font-size:0.875rem"><?= e($_SESSION['platform_admin_name'] ?? '') ?></span>
                <a href="/platform/logout" class="btn btn-ghost btn-sm"><i class="fa-solid fa-arrow-right-from-bracket"></i> Déconnexion</a>
            </div>
        </header>
        <main class="page-content" style="max-width:1200px;margin:0 auto">
            <?= $content ?>
        </main>
    </div>
    <div class="toast-container" id="toast-container"></div>
    <script>
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';
    function showToast(message, type) {
        const container = document.getElementById('toast-container');
        if (!container) return;
        const toast = document.createElement('div');
        toast.className = 'toast toast-' + (type || 'success');
        toast.textContent = message;
        container.appendChild(toast);
        setTimeout(() => { toast.style.opacity = '0'; setTimeout(() => toast.remove(), 300); }, 3000);
    }
    </script>
</body>
</html>
