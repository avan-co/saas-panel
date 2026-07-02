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

    if (typeof jalaliDatepicker !== 'undefined') {
        jalaliDatepicker.startWatch({
            selector: '.jalali-date',
            autoHide: true,
            hideAfterChange: true,
            showTodayBtn: true,
            showEmptyBtn: true,
            separatorChars: { date: '/' },
        });
    }

    var typeSelect = document.getElementById('type');
    var transferGroup = document.getElementById('transferToGroup');
    var categoryGroup = document.getElementById('categoryGroup');

    function syncTxnTypeFields() {
        if (!typeSelect) return;
        var isTransfer = typeSelect.value === 'transfer';
        if (transferGroup) transferGroup.style.display = isTransfer ? '' : 'none';
        if (categoryGroup) categoryGroup.style.display = isTransfer ? 'none' : '';
    }

    if (typeSelect) {
        typeSelect.addEventListener('change', syncTxnTypeFields);
        syncTxnTypeFields();
    }

    var notificationBtn = document.getElementById('notificationBtn');
    var notificationMenu = document.getElementById('notificationMenu');
    var notificationBody = document.getElementById('notificationDropdownBody');
    var notificationsLoaded = false;

    if (notificationBtn && notificationMenu) {
        notificationMenu.addEventListener('click', function (e) {
            e.stopPropagation();
        });

        notificationBtn.addEventListener('click', function (e) {
            e.stopPropagation();
            notificationMenu.classList.toggle('open');

            if (notificationMenu.classList.contains('open') && !notificationsLoaded && notificationBody) {
                notificationsLoaded = true;
                fetch('/notifications/dropdown', { credentials: 'same-origin' })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (!data.items || data.items.length === 0) {
                            notificationBody.innerHTML = '<div class="notification-empty text-muted">' + (document.documentElement.lang === 'fa' ? 'اعلان جدیدی ندارید.' : 'No new notifications.') + '</div>';
                            return;
                        }
                        notificationBody.innerHTML = data.items.map(function (item) {
                            var link = item.link ? '<a href="' + item.link + '" class="notification-link">' + item.title + '</a>' : '<span>' + item.title + '</span>';
                            return '<div class="notification-dropdown-item">' + link + '</div>';
                        }).join('');
                    })
                    .catch(function () {
                        notificationBody.innerHTML = '<div class="notification-empty text-muted">—</div>';
                    });
            }
        });
    }

    document.addEventListener('click', function () {
        if (notificationMenu) notificationMenu.classList.remove('open');
    });
})();
