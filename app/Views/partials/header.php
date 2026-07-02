<header class="app-header">
    <div class="header-start">
        <h1 class="page-title"><?= esc($title ?? lang('Dashboard.title')) ?></h1>
    </div>

    <div class="header-end">
        <?php if (! empty($userTenants) && count($userTenants) > 0): ?>
            <div class="tenant-switcher">
                <button class="tenant-btn" id="tenantBtn" type="button">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M3 21h18M5 21V7l8-4v18M19 21V11l-6-4"/><path d="M9 9v0M9 12v0M9 15v0"/></svg>
                    <span><?= esc($currentTenant['name'] ?? lang('App.switch_tenant')) ?></span>
                    <svg class="chevron" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M6 9l6 6 6-6"/></svg>
                </button>
                <div class="tenant-dropdown" id="tenantDropdown">
                    <?php foreach ($userTenants as $t): ?>
                        <a href="<?= site_url('tenant/switch/' . $t['id']) ?>"
                           class="tenant-option <?= ($currentTenant['id'] ?? 0) == $t['id'] ? 'active' : '' ?>">
                            <?= esc($t['name']) ?>
                        </a>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endif; ?>

        <div class="header-actions">
            <div class="locale-switch">
                <a href="<?= site_url('locale/fa') ?>" class="<?= $locale === 'fa' ? 'active' : '' ?>">FA</a>
                <a href="<?= site_url('locale/en') ?>" class="<?= $locale === 'en' ? 'active' : '' ?>">EN</a>
            </div>

            <div class="theme-switch" id="themeSwitch">
                <button type="button" data-theme="light" title="<?= esc(lang('App.theme_light')) ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="5"/><path d="M12 1v2M12 21v2M4.22 4.22l1.42 1.42M18.36 18.36l1.42 1.42M1 12h2M21 12h2M4.22 19.78l1.42-1.42M18.36 5.64l1.42-1.42"/></svg>
                </button>
                <button type="button" data-theme="dark" title="<?= esc(lang('App.theme_dark')) ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"/></svg>
                </button>
                <button type="button" data-theme="system" title="<?= esc(lang('App.theme_system')) ?>">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><rect x="2" y="3" width="20" height="14" rx="2"/><path d="M8 21h8M12 17v4"/></svg>
                </button>
            </div>

            <button class="icon-btn" type="button" title="<?= esc(lang('App.notifications')) ?>">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 8A6 6 0 0 0 6 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 0 1-3.46 0"/></svg>
            </button>

            <div class="user-menu">
                <button class="user-btn" type="button">
                    <span class="user-avatar"><?= esc(mb_substr($userName ?? 'U', 0, 1)) ?></span>
                    <span class="user-name"><?= esc($userName ?? '') ?></span>
                </button>
                <div class="user-dropdown">
                    <a href="<?= site_url('logout') ?>"><?= esc(lang('App.logout')) ?></a>
                </div>
            </div>
        </div>
    </div>
</header>
