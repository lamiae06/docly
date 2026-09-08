<!DOCTYPE html>
<html lang="<?= e(current_lang()) ?>" dir="<?= e(lang_direction()) ?>" data-theme="<?= $_SESSION['user_theme'] ?? 'system' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <script>window.DOCLY_CURRENCY_SYMBOL = <?= json_encode(currency_symbol()) ?>;</script>
    <title><?= isset($pageTitle) ? e($pageTitle) . ' | ' : '' ?>Docly</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
    <link rel="stylesheet" href="<?= asset('assets/css/docly.css?v=1.0.0') ?>">
</head>
<body>
    <div class="app-container">
        <?php $this->partial('sidebar', ['pageTitle' => $pageTitle ?? '']); ?>

        <div class="main-content">
            <?php $this->partial('header', ['pageTitle' => $pageTitle ?? '']); ?>

            <main class="page-content">
                <?= $content ?>
            </main>
        </div>
    </div>

    <!-- Toast Container -->
    <div id="toast-container" class="toast-container"></div>

    <!-- Modal Container -->
    <div id="modal-overlay" class="modal-overlay hidden">
        <div class="modal" id="modal">
            <div class="modal-header">
                <h3 id="modal-title">Titre</h3>
                <button class="modal-close" onclick="closeModal()">&times;</button>
            </div>
            <div class="modal-body" id="modal-body"></div>
            <div class="modal-footer" id="modal-footer"></div>
        </div>
    </div>

    <script src="<?= asset('assets/js/docly.js?v=1.0.0') ?>"></script>
</body>
</html>
