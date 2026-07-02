<?php
$locale = service('request')->getLocale();
$isRtl  = $locale === 'fa';
?>
<!DOCTYPE html>
<html lang="<?= esc($locale) ?>" dir="<?= $isRtl ? 'rtl' : 'ltr' ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= esc($title ?? lang('Install.title')) ?> — <?= esc(lang('App.app_name')) ?></title>
    <link href="https://fonts.googleapis.com/css2?family=Vazirmatn:wght@400;500;600;700&family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="<?= base_url('assets/css/install.css') ?>">
</head>
<body class="install-body <?= $isRtl ? 'rtl' : 'ltr' ?>">
    <div class="install-wrapper">
        <div class="install-card">
            <div class="install-header">
                <div class="brand-icon">BP</div>
                <h1><?= esc(lang('Install.title')) ?></h1>
                <p><?= esc(lang('Install.subtitle')) ?></p>
                <div class="install-locale">
                    <a href="<?= site_url('locale/fa') ?>" class="<?= $locale === 'fa' ? 'active' : '' ?>">فارسی</a>
                    <span aria-hidden="true">|</span>
                    <a href="<?= site_url('locale/en') ?>" class="<?= $locale === 'en' ? 'active' : '' ?>">English</a>
                </div>
            </div>

            <?= view('install/partials/steps', ['step' => $step ?? 1]) ?>

            <?php if (session()->getFlashdata('error')): ?>
                <div class="alert alert-error"><?= esc(session()->getFlashdata('error')) ?></div>
            <?php endif; ?>

            <?php if (session()->getFlashdata('errors')): ?>
                <div class="alert alert-error">
                    <?php foreach (session()->getFlashdata('errors') as $err): ?>
                        <div><?= esc($err) ?></div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <?= $body ?>
        </div>
    </div>
</body>
</html>
