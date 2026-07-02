(function () {
    'use strict';

    function getEffectiveTheme(preference) {
        if (preference === 'system' || !preference) {
            return window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light';
        }
        return preference;
    }

    function applyTheme(preference) {
        var effective = getEffectiveTheme(preference);
        document.documentElement.setAttribute('data-effective-theme', effective);
        localStorage.setItem('theme', preference || 'system');

        document.querySelectorAll('#themeSwitch button').forEach(function (btn) {
            btn.classList.toggle('active', btn.dataset.theme === preference);
        });
    }

    var storedTheme = localStorage.getItem('theme') || document.documentElement.getAttribute('data-theme') || 'system';
    applyTheme(storedTheme);

    document.querySelectorAll('#themeSwitch button').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var theme = btn.dataset.theme;
            applyTheme(theme);

            fetch('/theme/' + theme, { method: 'GET', credentials: 'same-origin' }).catch(function () {});
        });
    });

    window.matchMedia('(prefers-color-scheme: dark)').addEventListener('change', function () {
        var current = localStorage.getItem('theme') || 'system';
        if (current === 'system') {
            applyTheme('system');
        }
    });

    var sidebarToggle = document.getElementById('sidebarToggle');
    var sidebarCollapse = document.getElementById('sidebarCollapse');

    if (sidebarToggle) {
        sidebarToggle.addEventListener('click', function () {
            if (window.innerWidth <= 768) {
                document.body.classList.toggle('sidebar-mobile-open');
                document.body.classList.toggle('sidebar-backdrop-open', document.body.classList.contains('sidebar-mobile-open'));
            } else {
                document.body.classList.toggle('sidebar-expanded');
                localStorage.setItem('sidebarExpanded', document.body.classList.contains('sidebar-expanded'));
            }
        });
    }

    if (sidebarCollapse) {
        sidebarCollapse.addEventListener('click', function () {
            document.body.classList.toggle('sidebar-expanded');
            localStorage.setItem('sidebarExpanded', document.body.classList.contains('sidebar-expanded'));
        });
    }

    if (localStorage.getItem('sidebarExpanded') === 'true' && window.innerWidth > 768) {
        document.body.classList.add('sidebar-expanded');
    }

    var tenantBtn = document.getElementById('tenantBtn');
    var tenantSwitcher = tenantBtn ? tenantBtn.closest('.tenant-switcher') : null;

    if (tenantBtn && tenantSwitcher) {
        tenantBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            tenantSwitcher.classList.toggle('open');
        });
    }

    document.querySelectorAll('.user-btn').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.stopPropagation();
            var menu = btn.closest('.user-menu');
            if (menu) menu.classList.toggle('open');
        });
    });

    document.addEventListener('click', function () {
        document.querySelectorAll('.tenant-switcher.open').forEach(function (el) {
            el.classList.remove('open');
        });
        document.querySelectorAll('.user-menu.open').forEach(function (el) {
            el.classList.remove('open');
        });
    });

    var sidebarBackdrop = document.getElementById('sidebarBackdrop');
    if (sidebarBackdrop) {
        sidebarBackdrop.addEventListener('click', function () {
            document.body.classList.remove('sidebar-mobile-open', 'sidebar-backdrop-open');
        });
    }
})();
