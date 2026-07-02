<!DOCTYPE html>
<html lang="<?= esc($locale) ?>" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>" data-theme="<?= esc($theme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? lang('App.app_name')) ?> — <?= esc(lang('App.app_name')) ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Vazirmatn:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
    <link rel="stylesheet" href="https://unpkg.com/@majidh1/jalalidatepicker/dist/jalalidatepicker.min.css">
    <script>
        (function () {
            var theme = <?= json_encode($theme) ?>;
            var stored = localStorage.getItem('theme');
            var effective = stored || theme || 'system';
            if (effective === 'system') {
                effective = window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
            }
            document.documentElement.setAttribute('data-effective-theme', effective);
        })();
    </script>
</head>
<body class="<?= $isRtl ? 'rtl' : 'ltr' ?>">
    <div class="sidebar-backdrop" id="sidebarBackdrop"></div>
    <div class="app-shell" id="appShell">
        <?= $this->include('partials/sidebar') ?>

        <div class="app-main">
            <?= $this->include('partials/header') ?>

            <main class="app-content">
                <?= $this->include('partials/flash') ?>

                <?= $this->renderSection('content') ?>
            </main>
        </div>
    </div>

    <script src="https://unpkg.com/@majidh1/jalalidatepicker/dist/jalalidatepicker.min.js"></script>
    <script src="<?= base_url('assets/js/app.js') ?>"></script>
    <?= $this->renderSection('scripts') ?>
</body>
</html>
