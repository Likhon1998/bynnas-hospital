document.addEventListener('DOMContentLoaded', () => {
    const toggle = document.querySelector('[data-nav-toggle]');
    const panel = document.querySelector('[data-nav-panel]');
    const closeButtons = document.querySelectorAll('[data-nav-close]');

    if (toggle && panel) {
        const setOpen = (open) => {
            panel.classList.toggle('hidden', !open);
            document.body.classList.toggle('overflow-hidden', open);
            toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
        };

        toggle.addEventListener('click', () => {
            setOpen(panel.classList.contains('hidden'));
        });

        closeButtons.forEach((button) => {
            button.addEventListener('click', () => setOpen(false));
        });
    }

    const adminSidebar = document.querySelector('[data-admin-sidebar]');
    const adminToggle = document.querySelector('[data-admin-sidebar-toggle]');
    const adminClose = document.querySelectorAll('[data-admin-sidebar-close]');
    const adminShell = document.querySelector('[data-admin-shell]');
    const adminCollapse = document.querySelector('[data-admin-sidebar-collapse]');

    if (adminSidebar && adminToggle) {
        const setAdminOpen = (open) => {
            adminSidebar.classList.toggle('hidden', !open);
            document.body.classList.toggle('overflow-hidden', open);
        };

        adminToggle.addEventListener('click', () => {
            setAdminOpen(adminSidebar.classList.contains('hidden'));
        });

        adminClose.forEach((button) => {
            button.addEventListener('click', () => setAdminOpen(false));
        });
    }

    if (adminShell && adminCollapse) {
        const storageKey = 'admin-sidebar-collapsed';

        const setCollapsed = (collapsed) => {
            adminShell.classList.toggle('is-collapsed', collapsed);
            adminCollapse.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Squeeze sidebar');
            adminCollapse.setAttribute('title', collapsed ? 'Expand sidebar' : 'Squeeze sidebar');
            adminCollapse.setAttribute('aria-expanded', collapsed ? 'false' : 'true');

            try {
                localStorage.setItem(storageKey, collapsed ? '1' : '0');
            } catch (error) {
                // Ignore private-mode storage failures.
            }
        };

        let saved = false;

        try {
            saved = localStorage.getItem(storageKey) === '1';
        } catch (error) {
            saved = false;
        }

        setCollapsed(saved);

        adminCollapse.addEventListener('click', () => {
            setCollapsed(! adminShell.classList.contains('is-collapsed'));
        });
    }

    const closeAllMenus = (except = null) => {
        document.querySelectorAll('[data-admin-menu]').forEach((menu) => {
            if (except && menu === except) {
                return;
            }

            const trigger = menu.querySelector('[data-admin-menu-trigger]');
            const panelEl = menu.querySelector('[data-admin-menu-panel]');

            if (trigger && panelEl) {
                panelEl.classList.add('hidden');
                panelEl.hidden = true;
                trigger.setAttribute('aria-expanded', 'false');
            }
        });
    };

    document.querySelectorAll('[data-admin-menu]').forEach((menu) => {
        const trigger = menu.querySelector('[data-admin-menu-trigger]');
        const panelEl = menu.querySelector('[data-admin-menu-panel]');

        if (! trigger || ! panelEl) {
            return;
        }

        trigger.addEventListener('click', (event) => {
            event.stopPropagation();
            const willOpen = panelEl.classList.contains('hidden');
            closeAllMenus();

            if (willOpen) {
                panelEl.classList.remove('hidden');
                panelEl.hidden = false;
                trigger.setAttribute('aria-expanded', 'true');
            }
        });
    });

    document.addEventListener('click', () => closeAllMenus());
    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeAllMenus();
            closeSearchResults();
        }
    });

    const adminSearch = document.querySelector('[data-admin-search]');
    const searchResults = document.querySelector('[data-admin-search-results]');
    const searchHotkey = document.querySelector('[data-admin-search-hotkey]');
    let searchTimer = null;
    let activeIndex = -1;
    let currentResults = [];

    if (searchHotkey) {
        const isApple = /Mac|iPhone|iPad|iPod/.test(navigator.platform || '');
        searchHotkey.textContent = isApple ? '⌘ K' : 'Ctrl K';
    }

    const setSearchOpen = (open) => {
        if (! searchResults || ! adminSearch) {
            return;
        }

        searchResults.classList.toggle('hidden', ! open);
        searchResults.hidden = ! open;
        adminSearch.setAttribute('aria-expanded', open ? 'true' : 'false');
    };

    const closeSearchResults = () => {
        setSearchOpen(false);
        activeIndex = -1;
    };

    const renderResults = (results) => {
        if (! searchResults) {
            return;
        }

        currentResults = results;
        activeIndex = -1;

        if (results.length === 0) {
            searchResults.innerHTML = '<p class="admin-popover__empty">No matches found.</p>';
            setSearchOpen(true);

            return;
        }

        searchResults.innerHTML = results.map((item, index) => `
            <a href="${item.href}" class="admin-search-item" role="option" data-admin-search-item data-index="${index}">
                <span class="admin-search-item__type">${item.type}</span>
                <span class="admin-search-item__title"></span>
                <span class="admin-search-item__detail"></span>
            </a>
        `).join('');

        searchResults.querySelectorAll('[data-admin-search-item]').forEach((node, index) => {
            node.querySelector('.admin-search-item__title').textContent = results[index].title;
            node.querySelector('.admin-search-item__detail').textContent = results[index].detail;
        });

        setSearchOpen(true);
    };

    const highlightActive = () => {
        if (! searchResults) {
            return;
        }

        searchResults.querySelectorAll('[data-admin-search-item]').forEach((node, index) => {
            node.classList.toggle('is-active', index === activeIndex);
        });
    };

    const runSearch = async (term) => {
        if (! adminSearch) {
            return;
        }

        const url = adminSearch.getAttribute('data-admin-search-url');

        if (! url || term.trim().length < 2) {
            closeSearchResults();
            currentResults = [];

            return;
        }

        try {
            const response = await fetch(`${url}?q=${encodeURIComponent(term.trim())}`, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (! response.ok) {
                throw new Error('Search failed');
            }

            const payload = await response.json();
            renderResults(Array.isArray(payload.results) ? payload.results : []);
        } catch (error) {
            if (searchResults) {
                searchResults.innerHTML = '<p class="admin-popover__empty">Search unavailable.</p>';
                setSearchOpen(true);
            }
        }
    };

    if (adminSearch) {
        document.addEventListener('keydown', (event) => {
            if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
                event.preventDefault();
                adminSearch.focus();
                adminSearch.select();
            }
        });

        adminSearch.addEventListener('input', () => {
            closeAllMenus();
            window.clearTimeout(searchTimer);
            searchTimer = window.setTimeout(() => runSearch(adminSearch.value), 220);
        });

        adminSearch.addEventListener('focus', () => {
            if (currentResults.length > 0 || adminSearch.value.trim().length >= 2) {
                runSearch(adminSearch.value);
            }
        });

        adminSearch.addEventListener('keydown', (event) => {
            if (! searchResults || searchResults.hidden) {
                return;
            }

            if (event.key === 'ArrowDown') {
                event.preventDefault();
                activeIndex = Math.min(activeIndex + 1, currentResults.length - 1);
                highlightActive();
            }

            if (event.key === 'ArrowUp') {
                event.preventDefault();
                activeIndex = Math.max(activeIndex - 1, 0);
                highlightActive();
            }

            if (event.key === 'Enter' && activeIndex >= 0 && currentResults[activeIndex]) {
                event.preventDefault();
                window.location.href = currentResults[activeIndex].href;
            }
        });

        if (searchResults) {
            searchResults.addEventListener('click', (event) => event.stopPropagation());
        }
    }

    document.querySelectorAll('[data-mc-reveal]').forEach((trigger) => {
        trigger.addEventListener('click', () => {
            const targetId = trigger.getAttribute('data-mc-reveal');
            const panel = targetId ? document.getElementById(targetId) : null;

            if (! panel) {
                return;
            }

            const willOpen = panel.hidden;
            panel.hidden = ! willOpen;
            trigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        });
    });
});
