<!DOCTYPE html>
<html lang="<?= esc($locale) ?>" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>" data-theme="<?= esc($theme) ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc(lang('Auth.login_title')) ?> — <?= esc(lang('App.app_name')) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Vazirmatn:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/app.css') ?>">
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
<body class="auth-body <?= $isRtl ? 'rtl' : 'ltr' ?>">
    <div class="auth-wrapper">
        <div class="auth-card">
            <div class="auth-brand">
                <div class="brand-icon">BP</div>
                <h1><?= esc(lang('App.app_name')) ?></h1>
                <p><?= esc(lang('App.tagline')) ?></p>
            </div>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
            <?php endif; ?>

            <?= $this->renderSection('content') ?>

            <div class="auth-footer">
                <div class="auth-locale">
                    <a href="<?= site_url('locale/fa') ?>" class="<?= $locale === 'fa' ? 'active' : '' ?>">FA</a>
                    <span>|</span>
                    <a href="<?= site_url('locale/en') ?>" class="<?= $locale === 'en' ? 'active' : '' ?>">EN</a>
                </div>
            </div>
        </div>
    </div>
    <script src="<?= base_url('assets/js/app.js') ?>"></script>
</body>
</html>
