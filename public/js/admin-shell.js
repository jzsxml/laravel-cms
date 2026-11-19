/**
 * Admin shell: theme, fullscreen, sidebar, menu search.
 */
(function () {
    const STORAGE_THEME = 'cms_admin_theme';
    const STORAGE_SIDEBAR = 'cms_sidebar_collapsed';
    const root = document.documentElement;
    const sidebar = document.getElementById('cms-sidebar');
    const backdrop = document.getElementById('cms-sidebar-backdrop');
    const menuLinks = window.__cmsAdminMenu || [];

    function applySidebarCollapsed(collapsed) {
        if (!sidebar) return;
        sidebar.classList.toggle('is-collapsed', collapsed);
        root.classList.toggle('cms-sidebar-is-collapsed', collapsed);
        localStorage.setItem(STORAGE_SIDEBAR, collapsed ? '1' : '0');
    }

    function initSidebar() {
        const collapsed = localStorage.getItem(STORAGE_SIDEBAR) === '1';
        applySidebarCollapsed(collapsed);

        const toggle = () => applySidebarCollapsed(!sidebar.classList.contains('is-collapsed'));
        document.getElementById('btn-sidebar')?.addEventListener('click', toggle);
        document.getElementById('btn-sidebar-mobile')?.addEventListener('click', () => {
            sidebar?.classList.toggle('is-mobile-open');
            if (backdrop) {
                backdrop.hidden = !sidebar?.classList.contains('is-mobile-open');
            }
        });
        backdrop?.addEventListener('click', () => {
            sidebar?.classList.remove('is-mobile-open');
            backdrop.hidden = true;
        });

        document.querySelectorAll('[data-menu-toggle="sub"]').forEach((el) => {
            el.addEventListener('click', (e) => {
                if (sidebar?.classList.contains('is-collapsed')) return;
                e.preventDefault();
                const group = el.closest('.cms-menu-group');
                const sub = group?.querySelector('.cms-submenu');
                const chevron = el.querySelector('.cms-menu-chevron');
                if (!sub) return;
                sub.classList.toggle('is-collapsed');
                chevron?.classList.toggle('is-open', !sub.classList.contains('is-collapsed'));
            });
        });
    }

    function initTheme() {
        const current = localStorage.getItem(STORAGE_THEME) || 'default';
        if (current !== 'default') {
            root.setAttribute('data-cms-theme', current);
        }
        document.querySelectorAll('.cms-theme-picker [data-cms-theme]').forEach((btn) => {
            btn.classList.toggle('active', btn.getAttribute('data-cms-theme') === current);
            btn.addEventListener('click', () => {
                const name = btn.getAttribute('data-cms-theme') || 'default';
                if (name === 'default') {
                    root.removeAttribute('data-cms-theme');
                    localStorage.removeItem(STORAGE_THEME);
                } else {
                    root.setAttribute('data-cms-theme', name);
                    localStorage.setItem(STORAGE_THEME, name);
                }
                document.querySelectorAll('.cms-theme-picker [data-cms-theme]').forEach((b) => {
                    b.classList.toggle('active', b.getAttribute('data-cms-theme') === name);
                });
            });
        });
    }

    function initFullscreen() {
        const btn = document.getElementById('btn-fullscreen');
        const icon = document.getElementById('fullscreen-icon');
        if (!btn || !icon) return;

        const syncIcon = () => {
            const on = !!document.fullscreenElement;
            icon.classList.toggle('fa-expand', !on);
            icon.classList.toggle('fa-compress', on);
            btn.title = on ? '退出全屏' : '全屏';
        };

        btn.addEventListener('click', async () => {
            try {
                if (document.fullscreenElement) {
                    await document.exitFullscreen();
                } else {
                    await document.documentElement.requestFullscreen();
                }
            } catch (_) {
                /* unsupported */
            }
        });
        document.addEventListener('fullscreenchange', syncIcon);
        syncIcon();
    }

    function filterSidebarMenu(q) {
        const query = (q || '').trim().toLowerCase();
        const groups = document.querySelectorAll('.cms-menu-group');
        let visible = 0;
        groups.forEach((group) => {
            const blob = group.getAttribute('data-menu-search') || '';
            const childLinks = group.querySelectorAll('.sub a');
            let groupMatch = !query || blob.includes(query);
            let anyChild = false;
            childLinks.forEach((a) => {
                const t = a.getAttribute('data-menu-search') || a.textContent.toLowerCase();
                const show = !query || t.includes(query) || groupMatch;
                a.style.display = show ? '' : 'none';
                if (show) anyChild = true;
            });
            const showGroup = !query || groupMatch || anyChild;
            group.style.display = showGroup ? '' : 'none';
            if (showGroup) visible += 1;
            const sub = group.querySelector('.cms-submenu');
            if (query && sub) {
                sub.classList.remove('is-collapsed');
                group.querySelector('.cms-menu-chevron')?.classList.add('is-open');
            }
        });
        document.getElementById('menu-empty')?.classList.toggle('d-none', visible > 0);
    }

    function initSidebarSearch() {
        const input = document.getElementById('sidebar-menu-search');
        input?.addEventListener('input', () => filterSidebarMenu(input.value));
    }

    function initTopbarSearch() {
        const input = document.getElementById('topbar-menu-search');
        const box = document.getElementById('topbar-search-results');
        if (!input || !box) return;

        const render = (q) => {
            const query = (q || '').trim().toLowerCase();
            if (!query) {
                box.classList.add('d-none');
                box.innerHTML = '';
                return;
            }
            const hits = menuLinks.filter((m) => (m.title || '').toLowerCase().includes(query)).slice(0, 12);
            if (hits.length === 0) {
                box.innerHTML = '<div class="cms-search-empty">无匹配菜单</div>';
            } else {
                box.innerHTML = hits.map((m) => `<a href="${m.url}" class="cms-search-item">${m.title}</a>`).join('');
            }
            box.classList.remove('d-none');
        };

        input.addEventListener('input', () => render(input.value));
        input.addEventListener('focus', () => render(input.value));
        document.addEventListener('click', (e) => {
            if (!e.target.closest('.cms-topbar-search')) {
                box.classList.add('d-none');
            }
        });
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                const first = box.querySelector('.cms-search-item');
                if (first) {
                    e.preventDefault();
                    window.location.href = first.getAttribute('href');
                }
            }
        });
    }

    initSidebar();
    initTheme();
    initFullscreen();
    initSidebarSearch();
    initTopbarSearch();
})();