<?php
$moduleIcons = [
    'dashboard' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>',
    'finance'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>',
    'payroll'   => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87M16 3.13a4 4 0 0 1 0 7.75"/></svg>',
    'insurance' => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>',
    'tax'       => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/><path d="M14 2v6h6M16 13H8M16 17H8M10 9H8"/></svg>',
    'projects'  => '<svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M22 19a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h5l2 3h9a2 2 0 0 1 2 2z"/></svg>',
];
$currentPath = uri_string();
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-top">
        <button class="sidebar-toggle" id="sidebarToggle" type="button" aria-label="Toggle menu">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 12h18M3 6h18M3 18h18"/></svg>
        </button>
        <a href="<?= site_url('dashboard') ?>" class="sidebar-brand">
            <span class="brand-mark">BP</span>
            <span class="brand-text"><?= esc(lang('App.app_name')) ?></span>
        </a>
    </div>

    <nav class="sidebar-nav">
        <?php foreach ($tenantModules as $module): ?>
            <?php
            $code = $module['code'];
            $url  = match ($code) {
                'dashboard' => site_url('dashboard'),
                'finance'   => site_url('module/finance'),
                'payroll'   => site_url('module/payroll'),
                'insurance' => site_url('module/insurance'),
                'tax'       => site_url('module/tax'),
                'projects'  => site_url('module/projects'),
                default     => site_url('module/' . $code),
            };
            $active = ($code === 'dashboard' && $currentPath === 'dashboard')
                || ($code === 'finance' && str_starts_with($currentPath, 'module/finance'))
                || ($code !== 'dashboard' && $code !== 'finance' && str_starts_with($currentPath, 'module/' . $code));
            $icon = $moduleIcons[$code] ?? $moduleIcons['dashboard'];
            ?>
            <a href="<?= $url ?>" class="nav-item <?= $active ? 'active' : '' ?>" title="<?= esc(lang('App.menu.' . $code)) ?>">
                <span class="nav-icon"><?= $icon ?></span>
                <span class="nav-label"><?= esc(lang('App.menu.' . $code)) ?></span>
            </a>
        <?php endforeach; ?>

        <a href="<?= site_url('module/settings') ?>" class="nav-item <?= str_starts_with($currentPath, 'module/settings') ? 'active' : '' ?>" title="<?= esc(lang('App.menu.settings')) ?>">
            <span class="nav-icon">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="3"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
            </span>
            <span class="nav-label"><?= esc(lang('App.menu.settings')) ?></span>
        </a>

        <?php if ($isPlatformAdmin): ?>
            <a href="<?= site_url('platform/tenants') ?>" class="nav-item <?= str_starts_with($currentPath, 'platform') ? 'active' : '' ?>" title="<?= esc(lang('App.menu.platform')) ?>">
                <span class="nav-icon">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
                </span>
                <span class="nav-label"><?= esc(lang('App.menu.platform')) ?></span>
            </a>
        <?php endif; ?>
    </nav>

    <button class="sidebar-collapse" id="sidebarCollapse" type="button" aria-label="Collapse">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M15 18l-6-6 6-6"/></svg>
    </button>
</aside>
