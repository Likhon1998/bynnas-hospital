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

    const closeNavDropdowns = (except = null) => {
        document.querySelectorAll('[data-nav-dropdown]').forEach((dropdown) => {
            if (except && dropdown === except) {
                return;
            }

            const trigger = dropdown.querySelector('[data-nav-dropdown-trigger]');
            const menu = dropdown.querySelector('[data-nav-dropdown-panel]');

            if (trigger && menu) {
                dropdown.classList.remove('is-open');
                menu.hidden = true;
                trigger.setAttribute('aria-expanded', 'false');
            }
        });
    };

    document.querySelectorAll('[data-nav-dropdown]').forEach((dropdown) => {
        const trigger = dropdown.querySelector('[data-nav-dropdown-trigger]');
        const menu = dropdown.querySelector('[data-nav-dropdown-panel]');

        if (! trigger || ! menu) {
            return;
        }

        trigger.addEventListener('click', (event) => {
            event.preventDefault();
            const willOpen = menu.hidden;
            closeNavDropdowns(willOpen ? dropdown : null);
            dropdown.classList.toggle('is-open', willOpen);
            menu.hidden = ! willOpen;
            trigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        });
    });

    document.addEventListener('click', (event) => {
        if (! event.target.closest('[data-nav-dropdown]')) {
            closeNavDropdowns();
        }
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            closeNavDropdowns();
        }
    });

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
            adminCollapse.setAttribute('aria-label', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
            adminCollapse.setAttribute('data-label', collapsed ? 'Expand sidebar' : 'Collapse sidebar');
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

    document.querySelectorAll('[data-admin-nav-section]').forEach((section) => {
        const toggle = section.querySelector('[data-admin-nav-toggle]');

        if (! toggle) {
            return;
        }

        toggle.addEventListener('click', () => {
            const isOpen = section.classList.toggle('is-open');
            toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
        });
    });

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

    const notificationsRoot = document.querySelector('[data-admin-notifications]');
    const notificationsUrl = notificationsRoot?.getAttribute('data-admin-notifications-url');
    const notificationsSeenUrl = notificationsRoot?.getAttribute('data-admin-notifications-seen-url');
    const notificationsBody = notificationsRoot?.querySelector('[data-admin-notifications-body]');
    const notificationsBadge = notificationsRoot?.querySelector('[data-admin-notification-badge]');
    const notificationsAll = notificationsRoot?.querySelector('[data-admin-notifications-all]');
    const notificationsMarkAll = notificationsRoot?.querySelector('[data-admin-notifications-read]');
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const setNotificationActions = (unread, listCount) => {
        if (notificationsBadge) {
            if (unread > 0) {
                notificationsBadge.textContent = String(unread);
                notificationsBadge.classList.remove('hidden');
                notificationsBadge.hidden = false;
            } else {
                notificationsBadge.textContent = '0';
                notificationsBadge.classList.add('hidden');
                notificationsBadge.hidden = true;
            }
        }

        if (notificationsMarkAll) {
            notificationsMarkAll.classList.toggle('hidden', unread <= 0);
            notificationsMarkAll.hidden = unread <= 0;
        }

        if (notificationsAll) {
            notificationsAll.classList.toggle('hidden', listCount <= 0);
            notificationsAll.hidden = listCount <= 0;
        }
    };

    const renderNotifications = (alerts) => {
        if (! notificationsBody) {
            return;
        }

        notificationsBody.replaceChildren();

        if (! Array.isArray(alerts) || alerts.length === 0) {
            const empty = document.createElement('p');
            empty.className = 'admin-popover__empty';
            empty.textContent = 'No notifications yet.';
            notificationsBody.appendChild(empty);

            return;
        }

        alerts.forEach((alert) => {
            const link = document.createElement('a');
            link.href = alert.href || '#';
            link.className = `admin-notice admin-notice--${alert.tone || 'orange'}`;

            if (alert.read) {
                link.classList.add('is-read');
            }

            if (alert.id) {
                link.setAttribute('data-admin-notification-id', alert.id);
            }

            const title = document.createElement('span');
            title.className = 'admin-notice__title';
            title.textContent = alert.title || '';
            link.appendChild(title);

            if (alert.detail) {
                const detail = document.createElement('span');
                detail.className = 'admin-notice__detail';
                detail.textContent = alert.detail;
                link.appendChild(detail);
            }

            if (alert.time) {
                const time = document.createElement('span');
                time.className = 'admin-notice__time';
                time.textContent = alert.time;
                link.appendChild(time);
            }

            notificationsBody.appendChild(link);
        });
    };

    const applyNotificationPayload = (payload) => {
        const total = Number(payload.total || 0);
        const alerts = Array.isArray(payload.alerts) ? payload.alerts : [];

        setNotificationActions(total, alerts.length);
        renderNotifications(alerts);

        if (notificationsAll && payload.href) {
            notificationsAll.setAttribute('href', payload.href);
        }
    };

    const markNotificationsSeen = async ({ ids = [], all = false } = {}) => {
        if (! notificationsSeenUrl || (! all && ids.length === 0)) {
            return null;
        }

        try {
            const response = await fetch(notificationsSeenUrl, {
                method: 'POST',
                headers: {
                    Accept: 'application/json',
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify(all ? { all: true } : { ids }),
            });

            if (! response.ok) {
                throw new Error('Unable to mark notifications seen');
            }

            const payload = await response.json();
            applyNotificationPayload(payload);

            return payload;
        } catch (error) {
            return null;
        }
    };

    const refreshNotifications = async () => {
        if (! notificationsUrl) {
            return;
        }

        try {
            const response = await fetch(notificationsUrl, {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (! response.ok) {
                throw new Error('Notifications unavailable');
            }

            applyNotificationPayload(await response.json());
        } catch (error) {
            // Keep the last rendered server-side list if refresh fails.
        }
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

                if (menu.hasAttribute('data-admin-notifications')) {
                    refreshNotifications();
                }
            }
        });
    });

    notificationsRoot?.querySelector('[data-admin-menu-panel]')?.addEventListener('click', (event) => {
        event.stopPropagation();
    });

    notificationsMarkAll?.addEventListener('click', (event) => {
        event.preventDefault();
        event.stopPropagation();
        markNotificationsSeen({ all: true });
    });

    notificationsBody?.addEventListener('click', (event) => {
        const link = event.target instanceof Element
            ? event.target.closest('[data-admin-notification-id]')
            : null;
        const id = link?.getAttribute('data-admin-notification-id');

        if (! link || ! id) {
            return;
        }

        if (link.classList.contains('is-read')) {
            return;
        }

        event.preventDefault();
        event.stopPropagation();

        const href = link.getAttribute('href') || '';

        markNotificationsSeen({ ids: [id] }).finally(() => {
            if (href && href !== '#') {
                window.location.assign(href);
            }
        });
    });

    if (notificationsUrl) {
        window.setInterval(() => refreshNotifications(), 30000);
        document.addEventListener('visibilitychange', () => {
            if (! document.hidden) {
                refreshNotifications();
            }
        });
    }

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

            const scope = trigger.closest('.mc-ops') || document;
            const willOpen = panel.hidden;

            scope.querySelectorAll('[data-mc-reveal]').forEach((otherTrigger) => {
                if (otherTrigger === trigger) {
                    return;
                }

                const otherId = otherTrigger.getAttribute('data-mc-reveal');
                const otherPanel = otherId ? document.getElementById(otherId) : null;

                if (otherPanel) {
                    otherPanel.hidden = true;
                }

                otherTrigger.setAttribute('aria-expanded', 'false');
            });

            panel.hidden = ! willOpen;
            trigger.setAttribute('aria-expanded', willOpen ? 'true' : 'false');
        });
    });

    const chevronIcon = '<svg class="ch-select__chevron" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M5 7.5 10 12.5 15 7.5" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';
    const tickIcon = '<svg class="ch-select__tick" viewBox="0 0 20 20" fill="none" aria-hidden="true"><path d="M4.5 10.5 8 14l7.5-8" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"/></svg>';
    const enhancedSelects = new Map();
    let openSelect = null;

    const selectBackdrop = document.createElement('button');
    selectBackdrop.type = 'button';
    selectBackdrop.className = 'ch-select__backdrop';
    selectBackdrop.setAttribute('aria-label', 'Close dropdown');
    selectBackdrop.tabIndex = -1;
    document.body.appendChild(selectBackdrop);

    const visibleSelectOptions = (select) => [...select.options].filter((option) => ! option.hidden);

    const optionLabelParts = (text) => {
        const match = String(text).trim().match(/^(.*?)(?:\s+[—–·]\s+)(.+)$/);

        return match
            ? { title: match[1].trim(), meta: match[2].trim() }
            : { title: String(text).trim(), meta: '' };
    };

    const optionButtons = (entry) => [...entry.list.querySelectorAll('.ch-select__option:not([hidden])')];

    const closeSelect = (entry) => {
        if (! entry) {
            return;
        }

        entry.wrap.classList.remove('is-open');
        entry.menu.classList.remove('is-open', 'is-sheet');
        entry.button.setAttribute('aria-expanded', 'false');
        selectBackdrop.classList.remove('is-open');

        if (openSelect === entry) {
            openSelect = null;
        }
    };

    const closeAllSelects = () => {
        enhancedSelects.forEach((entry) => closeSelect(entry));
    };

    const setActiveOption = (entry, index) => {
        const items = optionButtons(entry);

        items.forEach((item) => item.classList.remove('is-active'));

        if (items.length === 0) {
            return;
        }

        const current = items[(index + items.length) % items.length];
        current.classList.add('is-active');
        current.scrollIntoView({ block: 'nearest' });
    };

    const syncSelectTone = (entry) => {
        [...entry.wrap.classList].forEach((name) => {
            if (name.startsWith('status-select')) {
                entry.wrap.classList.remove(name);
            }
        });

        [...entry.select.classList].forEach((name) => {
            if (name.startsWith('status-select')) {
                entry.wrap.classList.add(name);
            }
        });

        entry.button.disabled = entry.select.disabled;
        entry.wrap.classList.toggle('is-disabled', entry.select.disabled);
    };

    const placeSelectMenu = (entry) => {
        const rect = entry.button.getBoundingClientRect();

        if (rect.bottom < 0 || rect.top > window.innerHeight) {
            closeSelect(entry);

            return;
        }

        const pad = 12;
        const gap = 8;
        const viewportWidth = window.innerWidth;
        const viewportHeight = window.innerHeight;
        const spaceBelow = viewportHeight - rect.bottom - pad;
        const spaceAbove = rect.top - pad;
        const useSheet = viewportWidth < 720;

        entry.menu.classList.toggle('is-sheet', useSheet);
        selectBackdrop.classList.toggle('is-open', useSheet);

        if (useSheet) {
            const dock = viewportWidth < 1100 ? 76 : 0;

            entry.menu.style.left = `${pad}px`;
            entry.menu.style.right = `${pad}px`;
            entry.menu.style.width = 'auto';
            entry.menu.style.top = 'auto';
            entry.menu.style.bottom = `${pad + dock}px`;
            entry.menu.style.maxHeight = `${Math.min(viewportHeight * 0.68, viewportHeight - pad * 2 - dock)}px`;

            return;
        }

        const width = Math.min(Math.max(rect.width, 280), viewportWidth - pad * 2);
        let left = rect.left;

        if (left + width > viewportWidth - pad) {
            left = viewportWidth - pad - width;
        }

        if (left < pad) {
            left = pad;
        }

        const openUp = spaceBelow < 260 && spaceAbove > spaceBelow;
        const available = openUp ? spaceAbove : spaceBelow;
        const maxHeight = Math.max(200, Math.min(420, available - gap, viewportHeight * 0.62));

        entry.menu.style.right = 'auto';
        entry.menu.style.width = `${width}px`;
        entry.menu.style.left = `${left}px`;
        entry.menu.style.maxHeight = `${maxHeight}px`;

        if (openUp) {
            entry.menu.style.top = 'auto';
            entry.menu.style.bottom = `${viewportHeight - rect.top + gap}px`;
        } else {
            entry.menu.style.bottom = 'auto';
            entry.menu.style.top = `${rect.bottom + gap}px`;
        }
    };

    const refreshSelect = (select) => {
        const entry = enhancedSelects.get(select);

        if (! entry) {
            return;
        }

        const options = visibleSelectOptions(select);
        const selected = select.selectedOptions[0] || options.find((option) => option.value === '') || options[0];
        const isPlaceholder = ! selected || selected.value === '';

        entry.buttonLabel.textContent = selected ? selected.textContent.trim() : 'Select';
        entry.button.classList.toggle('is-placeholder', isPlaceholder);
        syncSelectTone(entry);
        entry.list.replaceChildren();

        options.forEach((option, index) => {
            const item = document.createElement('button');
            item.type = 'button';
            item.className = 'ch-select__option';
            item.dataset.value = option.value;
            item.dataset.index = String(index);
            item.setAttribute('role', 'option');
            item.setAttribute('aria-selected', option.selected ? 'true' : 'false');
            item.classList.toggle('is-selected', option.selected);
            item.classList.toggle('is-placeholder', option.value === '');
            item.disabled = option.disabled && option.value !== '';

            const copy = document.createElement('span');
            copy.className = 'ch-select__option-copy';
            const parts = optionLabelParts(option.textContent);
            const name = document.createElement('strong');
            name.textContent = parts.title;
            copy.appendChild(name);

            if (parts.meta) {
                const meta = document.createElement('small');
                meta.textContent = parts.meta;
                copy.appendChild(meta);
            }

            item.appendChild(copy);

            if (option.selected && option.value !== '') {
                item.insertAdjacentHTML('beforeend', tickIcon);
            }

            item.addEventListener('click', () => {
                if (item.disabled) {
                    return;
                }

                select.value = option.value;
                select.dispatchEvent(new Event('change', { bubbles: true }));
                refreshSelect(select);
                closeSelect(entry);
                entry.button.focus();
            });

            entry.list.appendChild(item);
        });

        if (options.length === 0) {
            const empty = document.createElement('p');
            empty.className = 'ch-select__empty';
            empty.textContent = 'No options available';
            entry.list.appendChild(empty);
        }

        entry.filter.hidden = options.length < 8;

        if (openSelect !== entry) {
            entry.filter.value = '';
        }

        entry.filter.placeholder = 'Search';

        if (openSelect === entry) {
            placeSelectMenu(entry);
        }
    };

    const filterSelectOptions = (entry, query) => {
        const term = query.trim().toLowerCase();
        let visible = 0;

        [...entry.list.querySelectorAll('.ch-select__option')].forEach((item) => {
            const match = term === '' || item.textContent.toLowerCase().includes(term);
            item.hidden = ! match;
            item.classList.toggle('is-active', false);

            if (match) {
                visible += 1;
            }
        });

        let empty = entry.list.querySelector('.ch-select__empty');

        if (visible === 0) {
            if (! empty) {
                empty = document.createElement('p');
                empty.className = 'ch-select__empty';
                entry.list.appendChild(empty);
            }

            empty.textContent = 'No matching options';
        } else if (empty && visibleSelectOptions(entry.select).length > 0) {
            empty.remove();
        }

        setActiveOption(entry, 0);
    };

    const chooseActiveOption = (entry) => {
        const active = entry.list.querySelector('.ch-select__option.is-active:not([hidden])')
            || entry.list.querySelector('.ch-select__option.is-selected:not([hidden])');

        active?.click();
    };

    const openSelectMenu = (entry) => {
        if (openSelect && openSelect !== entry) {
            closeSelect(openSelect);
        }

        refreshSelect(entry.select);
        entry.wrap.classList.add('is-open');
        entry.menu.classList.add('is-open');
        entry.button.setAttribute('aria-expanded', 'true');
        openSelect = entry;
        placeSelectMenu(entry);

        const selected = entry.list.querySelector('.ch-select__option.is-selected') || optionButtons(entry)[0];

        if (selected) {
            selected.classList.add('is-active');
            selected.scrollIntoView({ block: 'nearest' });
        }

        if (entry.filter && ! entry.filter.hidden) {
            entry.filter.focus();
        }
    };

    const enhanceSelect = (select) => {
        if (
            enhancedSelects.has(select)
            || select.closest('.ch-select')
            || select.multiple
            || Number(select.getAttribute('size') || 0) > 1
        ) {
            return;
        }

        const wrap = document.createElement('div');
        wrap.className = 'ch-select';

        if (select.closest('.admin-filters')) {
            wrap.classList.add('ch-select--filter');
        }

        if (select.closest('.ch-search')) {
            wrap.classList.add('ch-select--search');
        }

        if (select.classList.contains('admin-field')) {
            wrap.classList.add('ch-select--field');
        }

        if (select.classList.contains('mc-input')) {
            wrap.classList.add('ch-select--desk');
        }

        if (select.classList.contains('mc-input--compact')) {
            wrap.classList.add('ch-select--compact');
        }

        if (select.classList.contains('mt-2')) {
            wrap.classList.add('mt-2');
        }

        select.parentNode.insertBefore(wrap, select);
        wrap.appendChild(select);
        select.classList.add('ch-select__native');
        select.tabIndex = -1;
        select.setAttribute('aria-hidden', 'true');

        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'ch-select__btn';
        button.setAttribute('aria-haspopup', 'listbox');
        button.setAttribute('aria-expanded', 'false');

        const buttonLabel = document.createElement('span');
        buttonLabel.className = 'ch-select__value';
        button.appendChild(buttonLabel);
        button.insertAdjacentHTML('beforeend', chevronIcon);

        const linkedLabel = select.id
            ? document.querySelector(`label[for="${CSS.escape(select.id)}"]`)?.textContent?.trim()
            : '';
        const fieldLabel = select.getAttribute('aria-label')
            || select.closest('label')?.querySelector('span')?.textContent?.trim()
            || linkedLabel
            || '';

        if (fieldLabel) {
            button.setAttribute('aria-label', fieldLabel);
        }

        if (select.required) {
            button.setAttribute('aria-required', 'true');
        }

        const menu = document.createElement('div');
        menu.className = 'ch-select__menu';
        menu.setAttribute('role', 'listbox');

        const filter = document.createElement('input');
        filter.type = 'search';
        filter.className = 'ch-select__filter';
        filter.placeholder = 'Search';
        filter.setAttribute('aria-label', 'Filter options');
        filter.autocomplete = 'off';
        menu.appendChild(filter);

        const list = document.createElement('div');
        list.className = 'ch-select__list';
        menu.appendChild(list);

        wrap.appendChild(button);
        document.body.appendChild(menu);

        const entry = { select, wrap, button, buttonLabel, menu, filter, list, fieldLabel };
        enhancedSelects.set(select, entry);
        refreshSelect(select);

        button.addEventListener('click', (event) => {
            event.preventDefault();
            event.stopPropagation();

            if (entry.wrap.classList.contains('is-open')) {
                closeSelect(entry);
            } else {
                openSelectMenu(entry);
            }
        });

        button.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowDown' || event.key === 'Enter' || event.key === ' ') {
                event.preventDefault();
                openSelectMenu(entry);
            }
        });

        select.addEventListener('invalid', () => {
            openSelectMenu(entry);
            button.focus();
        });

        select.addEventListener('focus', () => button.focus());

        filter.addEventListener('input', () => filterSelectOptions(entry, filter.value));
        filter.addEventListener('keydown', (event) => {
            if (event.key === 'ArrowDown') {
                event.preventDefault();
                setActiveOption(entry, optionButtons(entry).findIndex((item) => item.classList.contains('is-active')) + 1);
            } else if (event.key === 'ArrowUp') {
                event.preventDefault();
                setActiveOption(entry, optionButtons(entry).findIndex((item) => item.classList.contains('is-active')) - 1);
            } else if (event.key === 'Enter') {
                event.preventDefault();
                chooseActiveOption(entry);
            } else if (event.key === 'Escape') {
                event.preventDefault();
                closeSelect(entry);
                button.focus();
            }
        });

        new MutationObserver(() => refreshSelect(select)).observe(select, {
            childList: true,
            subtree: true,
            attributes: true,
            attributeFilter: ['hidden', 'disabled', 'selected', 'class'],
        });
    };

    document.querySelectorAll('select').forEach(enhanceSelect);

    selectBackdrop.addEventListener('click', (event) => {
        event.preventDefault();
        closeAllSelects();
    });

    document.addEventListener('click', (event) => {
        if (! openSelect) {
            return;
        }

        if (openSelect.wrap.contains(event.target) || openSelect.menu.contains(event.target)) {
            return;
        }

        closeAllSelects();
    });

    document.addEventListener('keydown', (event) => {
        if (event.key === 'Escape' && calendarRoot && ! calendarRoot.hidden) {
            setCalendarOpen(false);
            closeAllSelects();

            return;
        }

        if (! openSelect) {
            return;
        }

        if (event.key === 'Escape') {
            closeAllSelects();

            return;
        }

        if (event.target === openSelect.filter) {
            return;
        }

        if (event.key === 'ArrowDown') {
            event.preventDefault();
            setActiveOption(openSelect, optionButtons(openSelect).findIndex((item) => item.classList.contains('is-active')) + 1);
        } else if (event.key === 'ArrowUp') {
            event.preventDefault();
            setActiveOption(openSelect, optionButtons(openSelect).findIndex((item) => item.classList.contains('is-active')) - 1);
        } else if (event.key === 'Enter') {
            event.preventDefault();
            chooseActiveOption(openSelect);
        }
    });

    window.addEventListener('resize', () => {
        if (openSelect) {
            placeSelectMenu(openSelect);
        }
    });

    window.addEventListener('scroll', (event) => {
        if (! openSelect || openSelect.menu.contains(event.target)) {
            return;
        }

        placeSelectMenu(openSelect);
    }, true);

    const appointmentForm = document.querySelector('#appointment[data-appointment-schedule]');
    const appointmentDoctor = document.querySelector('#appointment select[name="doctor"]');
    const appointmentService = document.querySelector('#appointment select[name="service"]');
    const appointmentDate = document.querySelector('#appointment input[name="date"]');
    const appointmentTime = document.querySelector('#appointment input[name="time"]');
    const appointmentSlotNote = document.querySelector('#appointment [data-slot-note]');

    const setSlotNote = (message, blocked = false) => {
        if (! appointmentSlotNote) {
            return;
        }

        appointmentSlotNote.textContent = message || '';
        appointmentSlotNote.classList.toggle('is-blocked', blocked);
        appointmentSlotNote.hidden = ! blocked && ! message;
    };

    const calendarRoot = document.querySelector('[data-doctor-calendar]');
    const calendarOpen = document.querySelector('[data-cal-open]');
    const calendarApply = document.querySelector('[data-cal-apply]');
    const calendarBoard = document.querySelector('[data-cal-board]');
    const calendarWeekdays = document.querySelector('[data-cal-weekdays]');
    const calendarLabel = document.querySelector('[data-cal-label]');
    const calendarHint = document.querySelector('[data-cal-hint]');
    const calendarTimes = document.querySelector('[data-cal-times]');
    const calendarSlotLabel = document.querySelector('[data-cal-slot-label]');
    const calendarSlots = document.querySelector('[data-cal-slots]');
    const calendarPick = document.querySelector('[data-cal-pick]');
    const weekdayLabels = ['Sat', 'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri'];
    const weekdayKeys = ['saturday', 'sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday'];
    const localDateStamp = (value = new Date()) => {
        const month = String(value.getMonth() + 1).padStart(2, '0');
        const day = String(value.getDate()).padStart(2, '0');

        return `${value.getFullYear()}-${month}-${day}`;
    };

    let calendarView = 'month';
    let calendarFocus = appointmentDate?.value || localDateStamp();
    let calendarDays = [];
    let draftDate = appointmentDate?.value || '';
    let draftTime = appointmentTime?.value || '';

    const emptySlotLabel = calendarPick?.getAttribute('data-cal-empty') || 'Choose from clinic schedule';

    const setSelectedSlot = (date, time) => {
        if (appointmentDate) {
            appointmentDate.value = date || '';
        }

        if (appointmentTime) {
            appointmentTime.value = time || '';
        }

        if (calendarPick) {
            const picked = Boolean(date && time);
            const pretty = picked
                ? new Date(`${date}T00:00:00`).toLocaleDateString(undefined, {
                    weekday: 'short',
                    day: 'numeric',
                    month: 'short',
                })
                : '';

            calendarPick.textContent = picked ? `${pretty} · ${time}` : emptySlotLabel;
            calendarOpen?.classList.toggle('is-picked', picked);
        }

        setSlotNote('');
    };

    const prettyClinicDay = (date) => new Date(`${date}T00:00:00`).toLocaleDateString(undefined, {
        weekday: 'long',
        day: 'numeric',
        month: 'short',
    });

    const clinicTimes = (day) => {
        if (! day || ! Array.isArray(day.slots)) {
            return [];
        }

        return day.slots.map((slot) => {
            if (typeof slot === 'string') {
                return { time: slot, status: 'open' };
            }

            return {
                time: slot.time || '',
                status: slot.status || 'open',
            };
        }).filter((slot) => slot.time);
    };

    const selectClinicDay = (day) => {
        calendarFocus = day.date;
        draftDate = day.date;

        if (appointmentDate?.value === day.date && appointmentTime?.value) {
            draftTime = appointmentTime.value;
        } else {
            draftTime = '';
        }

        renderCalendarBoard(calendarDays);
        renderSlotButtons(day);
        syncApplyButton();

        if (calendarHint) {
            calendarHint.textContent = 'Tap a green time to choose that visit.';
        }
    };

    const renderSlotButtons = (day) => {
        if (! calendarSlots) {
            return;
        }

        calendarSlots.replaceChildren();

        const times = clinicTimes(day);
        const visible = day && ['open', 'full'].includes(day.status) && times.length > 0;
        const openCount = times.filter((slot) => slot.status === 'open').length;
        const bookedCount = times.filter((slot) => slot.status === 'booked').length;

        if (calendarTimes) {
            calendarTimes.hidden = ! visible;
        }

        if (calendarHint) {
            calendarHint.hidden = visible;
        }

        if (calendarSlotLabel) {
            const parts = [];

            if (openCount > 0) {
                parts.push(`${openCount} free`);
            }

            if (bookedCount > 0) {
                parts.push(`${bookedCount} booked`);
            }

            calendarSlotLabel.textContent = visible
                ? `${prettyClinicDay(day.date)} · ${parts.join(' · ') || 'No free times'}`
                : '';
        }

        if (! visible) {
            return;
        }

        times.forEach((slot) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.textContent = slot.time;
            button.classList.toggle('is-on', draftDate === day.date && draftTime === slot.time && slot.status === 'open');
            button.classList.toggle('is-booked', slot.status === 'booked');
            button.classList.toggle('is-past', slot.status === 'past');
            button.disabled = slot.status !== 'open';
            button.setAttribute('aria-label', slot.status === 'booked' ? `${slot.time} booked` : slot.time);

            if (slot.status === 'open') {
                button.addEventListener('click', () => {
                    draftDate = day.date;
                    draftTime = slot.time;
                    setSelectedSlot(day.date, slot.time);
                    setCalendarOpen(false);
                });
            }

            calendarSlots.appendChild(button);
        });
    };

    const renderWeekdays = (visible) => {
        if (! calendarWeekdays) {
            return;
        }

        calendarWeekdays.hidden = ! visible;
        calendarWeekdays.replaceChildren();

        if (! visible) {
            return;
        }

        weekdayLabels.forEach((label) => {
            const item = document.createElement('span');
            item.textContent = label;
            calendarWeekdays.appendChild(item);
        });
    };

    const renderCalendarBoard = (days) => {
        if (! calendarBoard) {
            return;
        }

        calendarDays = days;
        calendarBoard.replaceChildren();
        calendarBoard.className = calendarView === 'week' ? 'ch-cal__week' : 'ch-cal__month';
        renderWeekdays(calendarView === 'month');

        days.forEach((day) => {
            const button = document.createElement('button');
            button.type = 'button';
            button.className = `ch-cal__day is-${day.status}`;
            button.disabled = ! ['open', 'full'].includes(day.status);
            button.classList.toggle('is-muted', calendarView === 'month' && ! day.in_month);
            button.classList.toggle('is-on', calendarFocus === day.date);

            if (calendarView === 'week') {
                const dow = document.createElement('span');
                dow.className = 'ch-cal__dow';
                dow.textContent = weekdayLabels[weekdayKeys.indexOf(day.weekday)] || day.weekday.slice(0, 3);
                const date = document.createElement('strong');
                date.textContent = day.label;
                const meta = document.createElement('em');
                meta.className = 'ch-cal__count';
                meta.textContent = day.status === 'open'
                    ? `${day.open_count} open`
                    : (day.status === 'leave' ? 'Away' : (day.status === 'full' ? 'Full' : ''));
                button.append(dow, date, meta);
            } else {
                button.textContent = day.label;
            }

            if (['open', 'full'].includes(day.status)) {
                button.addEventListener('click', () => selectClinicDay(day));
            }

            calendarBoard.appendChild(button);
        });

        const selected = days.find((day) => day.date === calendarFocus && ['open', 'full'].includes(day.status))
            || days.find((day) => day.status === 'open')
            || days.find((day) => day.status === 'full');

        renderSlotButtons(selected || null);
    };

    const loadDoctorSchedule = async () => {
        if (! appointmentForm || ! calendarRoot) {
            return;
        }

        const scheduleUrl = appointmentForm.getAttribute('data-appointment-schedule');
        const doctorId = appointmentDoctor?.value;

        if (! scheduleUrl || ! doctorId) {
            if (calendarLabel) {
                calendarLabel.textContent = 'Choose a doctor to see free times';
            }
            if (calendarHint) {
                calendarHint.textContent = 'Green times are free. Red times are already booked.';
            }
            calendarBoard?.replaceChildren();
            calendarSlots?.replaceChildren();
            renderWeekdays(false);

            if (calendarTimes) {
                calendarTimes.hidden = true;
            }

            if (calendarHint) {
                calendarHint.hidden = false;
            }

            return;
        }

        if (calendarHint) {
            calendarHint.textContent = 'Loading clinic days…';
        }

        try {
            const url = new URL(scheduleUrl, window.location.origin);
            url.searchParams.set('doctor', doctorId);
            url.searchParams.set('view', calendarView);
            url.searchParams.set('date', calendarFocus);

            const response = await fetch(url.toString(), {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (! response.ok) {
                throw new Error('Schedule unavailable');
            }

            const payload = await response.json();

            if (calendarLabel) {
                calendarLabel.textContent = payload.label || '';
            }

            const days = Array.isArray(payload.days) ? payload.days : [];
            const clinic = Array.isArray(payload.clinic_days) ? payload.clinic_days : [];

            if (calendarHint) {
                calendarHint.textContent = clinic.length
                    ? `Clinic days: ${clinic.map((day) => day.charAt(0).toUpperCase() + day.slice(1)).join(', ')}. Green times are free. Red times are booked.`
                    : 'Green times are free. Red times are already booked.';
            }

            renderCalendarBoard(days);
        } catch (error) {
            if (calendarHint) {
                calendarHint.textContent = 'Schedule could not load. Close and try again.';
            }
        }
    };

    const setCalendarOpen = (open) => {
        if (! calendarRoot) {
            return;
        }

        calendarRoot.hidden = ! open;
        document.body.classList.toggle('ch-cal-lock', open);
        calendarOpen?.setAttribute('aria-expanded', open ? 'true' : 'false');

        if (open) {
            draftDate = appointmentDate?.value || '';
            draftTime = appointmentTime?.value || '';
            calendarFocus = draftDate || localDateStamp();
            loadDoctorSchedule();
            syncApplyButton();
        }
    };

    const openFieldSelect = (select) => {
        select?.closest('.ch-select')?.querySelector('.ch-select__btn')?.click();
    };

    const schedulePrompt = () => {
        if (! appointmentDoctor?.value) {
            return 'Choose a doctor first';
        }

        if (! appointmentService?.value) {
            return 'Choose a service first';
        }

        return emptySlotLabel;
    };

    const syncApplyButton = () => {
        if (calendarApply) {
            calendarApply.disabled = ! (draftDate && draftTime);
        }
    };

    const syncBookingState = () => {
        if (! appointmentService) {
            return;
        }

        const departmentId = appointmentDoctor?.selectedOptions[0]?.dataset.department || '';
        const hasDoctor = Boolean(appointmentDoctor?.value);

        [...appointmentService.options].forEach((option) => {
            if (option.value === '') {
                option.textContent = hasDoctor ? 'Choose a service' : 'Choose a doctor first';

                return;
            }

            option.hidden = ! hasDoctor || option.dataset.department !== departmentId;
        });

        const visible = [...appointmentService.options].filter((option) => option.value !== '' && ! option.hidden);

        if (! hasDoctor) {
            appointmentService.value = '';
        } else if (appointmentService.selectedOptions[0]?.hidden || appointmentService.value === '') {
            appointmentService.value = visible[0]?.value || '';
        }

        refreshSelect(appointmentService);

        const serviceWrap = appointmentService.closest('.ch-select');
        const serviceLocked = ! hasDoctor;
        serviceWrap?.classList.toggle('is-disabled', serviceLocked);
        const serviceButton = serviceWrap?.querySelector('.ch-select__btn');

        if (serviceButton instanceof HTMLButtonElement) {
            serviceButton.disabled = serviceLocked;
        }

        const ready = hasDoctor && Boolean(appointmentService.value);
        const picked = Boolean(appointmentDate?.value && appointmentTime?.value);

        calendarOpen?.classList.toggle('is-ready', ready);
        calendarOpen?.classList.toggle('is-picked', picked);

        if (calendarPick && ! picked) {
            calendarPick.textContent = schedulePrompt();
        }

        if (hasDoctor && visible.length === 0) {
            setSlotNote('This doctor has no bookable service yet.', true);
        } else {
            setSlotNote('');
        }
    };

    const shiftCalendar = (direction) => {
        const focus = new Date(`${calendarFocus}T00:00:00`);

        if (calendarView === 'week') {
            focus.setDate(focus.getDate() + (direction * 7));
        } else {
            focus.setMonth(focus.getMonth() + direction);
            focus.setDate(1);
        }

        calendarFocus = localDateStamp(focus);
        loadDoctorSchedule();
    };

    if (appointmentDoctor) {
        const requestedDoctor = new URLSearchParams(window.location.search).get('doctor');
        const hasRequestedDoctor = requestedDoctor
            && [...appointmentDoctor.options].some((option) => option.value === requestedDoctor);

        if (hasRequestedDoctor) {
            appointmentDoctor.value = requestedDoctor;
            refreshSelect(appointmentDoctor);
            syncBookingState();
        }

        appointmentDoctor.addEventListener('change', () => {
            draftDate = '';
            draftTime = '';
            setSelectedSlot('', '');
            calendarFocus = localDateStamp();
            syncBookingState();
        });
    }

    if (appointmentService) {
        appointmentService.addEventListener('change', () => {
            syncBookingState();
        });
    }

    calendarRoot?.querySelectorAll('[data-cal-view]').forEach((button) => {
        button.addEventListener('click', () => {
            calendarView = button.getAttribute('data-cal-view') || 'month';
            calendarRoot.querySelectorAll('[data-cal-view]').forEach((item) => {
                item.classList.toggle('is-on', item === button);
            });
            loadDoctorSchedule();
        });
    });

    calendarRoot?.querySelector('[data-cal-prev]')?.addEventListener('click', () => shiftCalendar(-1));
    calendarRoot?.querySelector('[data-cal-next]')?.addEventListener('click', () => shiftCalendar(1));
    calendarRoot?.querySelectorAll('[data-cal-close]').forEach((button) => {
        button.addEventListener('click', () => setCalendarOpen(false));
    });

    calendarOpen?.addEventListener('click', () => {
        if (! appointmentDoctor?.value) {
            setSlotNote('Choose a doctor first.', true);
            openFieldSelect(appointmentDoctor);

            return;
        }

        if (! appointmentService?.value) {
            setSlotNote('Choose a service this doctor provides.', true);
            openFieldSelect(appointmentService);

            return;
        }

        setCalendarOpen(true);
    });

    calendarApply?.addEventListener('click', () => {
        if (! (draftDate && draftTime)) {
            if (calendarHint) {
                calendarHint.hidden = false;
                calendarHint.textContent = 'Tap a green time first.';
            }

            return;
        }

        setSelectedSlot(draftDate, draftTime);
        setCalendarOpen(false);
    });

    if (appointmentForm) {
        syncBookingState();

        if (appointmentDate?.value && appointmentTime?.value) {
            setSelectedSlot(appointmentDate.value, appointmentTime.value);
        }

        appointmentForm.addEventListener('submit', (event) => {
            if (appointmentDoctor?.value && appointmentService?.value && appointmentDate?.value && appointmentTime?.value) {
                return;
            }

            event.preventDefault();

            if (! appointmentDoctor?.value) {
                setSlotNote('Choose a doctor first.', true);
                openFieldSelect(appointmentDoctor);

                return;
            }

            if (! appointmentService?.value) {
                setSlotNote('Choose a service this doctor provides.', true);
                openFieldSelect(appointmentService);

                return;
            }

            setSlotNote('Pick a free time from the clinic schedule.', true);
            setCalendarOpen(true);
        });
    }

    document.addEventListener('click', (event) => {
        const link = event.target.closest('[data-book-doctor]');

        if (!link || !appointmentDoctor || !link.dataset.bookDoctor) {
            return;
        }

        appointmentDoctor.value = link.dataset.bookDoctor;
        refreshSelect(appointmentDoctor);
        draftDate = '';
        draftTime = '';
        setSelectedSlot('', '');
        calendarFocus = localDateStamp();
        syncBookingState();

        if (appointmentService?.value) {
            setSlotNote('Pick a free time from the clinic schedule.');
        }
    });

    const finder = document.querySelector('[data-doctor-finder]');
    const doctorSpecialty = document.querySelector('[data-doctor-specialty]');
    const doctorList = document.querySelector('[data-doctor-list]');
    const finderForm = document.querySelector('[data-doctor-filter]');

    if (finder && doctorSpecialty && doctorList) {
        const directoryNode = document.querySelector('[data-finder-directory]');
        const people = finder.querySelector('[data-finder-people]');
        const result = finder.querySelector('[data-finder-result]');
        const resultText = finder.querySelector('[data-finder-result-text]');
        const photo = finder.querySelector('[data-finder-photo]');
        const initials = finder.querySelector('[data-finder-initials]');
        const vacant = finder.querySelector('[data-finder-vacant]');
        const caption = finder.querySelector('[data-finder-caption]');
        const count = finder.querySelector('[data-finder-count]');
        const countLabel = finder.querySelector('[data-finder-count-label]');
        const total = Number(finder.dataset.finderTotal || 0);
        let directory = [];

        try {
            directory = JSON.parse(directoryNode?.textContent || '[]');
        } catch (error) {
            directory = [];
        }

        const matchesSpecialty = (doctor, specialty) => {
            if (specialty === '') {
                return true;
            }

            const haystack = `${doctor.specialty || ''} ${doctor.specialization || ''}`.toLowerCase();

            return doctor.specialty === specialty || haystack.includes(specialty.toLowerCase());
        };

        const optionMatchesSpecialty = (option, specialty) => {
            if (specialty === '') {
                return true;
            }

            const haystack = `${option.dataset.specialty || ''} ${option.dataset.specialization || ''}`.toLowerCase();

            return option.dataset.specialty === specialty || haystack.includes(specialty.toLowerCase());
        };

        const visibleDoctors = () => {
            const specialty = doctorSpecialty.value;
            const selectedName = doctorList.value;

            return directory.filter((doctor) => {
                if (!matchesSpecialty(doctor, specialty)) {
                    return false;
                }

                if (selectedName !== '' && doctor.name !== selectedName) {
                    return false;
                }

                if (specialty === '' && selectedName === '') {
                    return doctor.preview;
                }

                return true;
            });
        };

        const setHidden = (node, hidden) => {
            if (!node) {
                return;
            }

            node.hidden = hidden;
        };

        const renderLead = (doctors) => {
            const lead = doctors.find((doctor) => doctor.photo) || doctors[0] || null;

            finder.classList.toggle('is-vacant', doctors.length === 0);

            if (!lead) {
                setHidden(photo, true);
                setHidden(initials, true);
                setHidden(vacant, false);

                if (photo) {
                    photo.removeAttribute('src');
                    photo.alt = '';
                }

                if (caption) {
                    caption.textContent = 'No consultants in this clinic';
                }

                return;
            }

            setHidden(vacant, true);

            if (lead.photo && photo) {
                photo.src = lead.photo;
                photo.alt = lead.name;
                setHidden(photo, false);
                setHidden(initials, true);
            } else {
                setHidden(photo, true);

                if (initials) {
                    initials.textContent = lead.initials || 'DR';
                }

                setHidden(initials, false);
            }

            if (caption) {
                caption.textContent = 'Consult with our expert doctors';
            }
        };

        const renderPeople = (doctors) => {
            if (!people) {
                return;
            }

            people.replaceChildren();

            doctors.forEach((doctor) => {
                const card = document.createElement('a');
                card.className = 'ch-find__person';
                card.href = doctor.url || `${window.location.pathname}#appointment`;

                if (doctor.photo) {
                    const image = document.createElement('img');
                    image.src = doctor.photo;
                    image.alt = doctor.name;
                    card.append(image);
                } else {
                    const mark = document.createElement('span');
                    mark.className = 'ch-find__initials';
                    mark.setAttribute('aria-hidden', 'true');
                    mark.textContent = doctor.initials || 'DR';
                    card.append(mark);
                }

                const copy = document.createElement('span');
                const name = document.createElement('strong');
                name.textContent = doctor.name;
                const role = document.createElement('em');
                role.textContent = doctor.specialization || doctor.specialty || 'Consultant';
                copy.append(name, role);
                card.append(copy);
                people.append(card);
            });

            setHidden(people, doctors.length === 0);
        };

        const syncDoctorOptions = () => {
            const specialty = doctorSpecialty.value;

            [...doctorList.options].forEach((option) => {
                if (option.value === '') {
                    return;
                }

                option.hidden = !optionMatchesSpecialty(option, specialty);
            });

            if (doctorList.selectedOptions[0]?.hidden) {
                doctorList.value = '';
            }

            refreshSelect(doctorList);
        };

        const applyFinder = () => {
            const specialty = doctorSpecialty.value;
            const selectedName = doctorList.value;
            const filtered = specialty !== '' || selectedName !== '';
            const doctors = visibleDoctors();
            const shownCount = filtered ? doctors.length : total;

            syncDoctorOptions();
            renderPeople(doctors);
            renderLead(doctors);

            if (count) {
                count.textContent = String(shownCount);
            }

            if (countLabel) {
                countLabel.textContent = shownCount === 1 ? 'Specialist' : 'Specialists';
            }

            if (result && resultText) {
                if (!filtered) {
                    setHidden(result, true);
                    resultText.textContent = '';
                } else if (doctors.length === 0) {
                    resultText.textContent = 'No consultants in this clinic yet. Pick another specialty or browse all doctors.';
                    setHidden(result, false);
                } else {
                    const label = specialty || selectedName;
                    const noun = doctors.length === 1 ? 'consultant' : 'consultants';
                    resultText.textContent = label
                        ? `${doctors.length} ${noun} in ${label}.`
                        : `${doctors.length} ${noun}.`;
                    setHidden(result, false);
                }
            }

            finder.querySelectorAll('[data-finder-chip]').forEach((chip) => {
                chip.classList.toggle('is-active', chip.dataset.finderChip === specialty);
            });
        };

        const clearFinder = () => {
            doctorSpecialty.value = '';
            doctorList.value = '';
            refreshSelect(doctorSpecialty);
            refreshSelect(doctorList);
            applyFinder();
        };

        doctorSpecialty.addEventListener('change', applyFinder);
        doctorList.addEventListener('change', applyFinder);

        finderForm?.addEventListener('submit', (event) => {
            event.preventDefault();
            applyFinder();
        });

        finder.querySelectorAll('[data-finder-chip]').forEach((chip) => {
            chip.addEventListener('click', (event) => {
                event.preventDefault();
                doctorSpecialty.value = chip.dataset.finderChip || '';
                doctorList.value = '';
                refreshSelect(doctorSpecialty);
                applyFinder();
            });
        });

        finder.querySelector('[data-finder-clear]')?.addEventListener('click', (event) => {
            event.preventDefault();
            clearFinder();
        });

        applyFinder();
    } else if (doctorSpecialty && doctorList) {
        const syncDoctorOptions = () => {
            const specialty = doctorSpecialty.value;

            [...doctorList.options].forEach((option) => {
                if (option.value === '') {
                    return;
                }

                const haystack = `${option.dataset.specialty || ''} ${option.dataset.specialization || ''}`.toLowerCase();
                option.hidden = specialty !== ''
                    && option.dataset.specialty !== specialty
                    && !haystack.includes(specialty.toLowerCase());
            });

            if (doctorList.selectedOptions[0]?.hidden) {
                doctorList.value = '';
            }

            refreshSelect(doctorList);
        };

        doctorSpecialty.addEventListener('change', syncDoctorOptions);
        syncDoctorOptions();
    }

    document.querySelectorAll('.clinic-day__switch input').forEach((input) => {
        const day = input.closest('.clinic-day');

        const syncOpen = () => {
            day?.classList.toggle('is-open', input.checked);
        };

        input.addEventListener('change', syncOpen);
        syncOpen();
    });

    document.querySelectorAll('.mc-ops form').forEach((form) => {
        form.addEventListener('submit', (event) => {
            if (form.dataset.submitting === '1') {
                event.preventDefault();

                return;
            }

            form.dataset.submitting = '1';
            form.querySelectorAll('button[type="submit"], .mc-btn').forEach((button) => {
                button.setAttribute('aria-busy', 'true');
            });
        });
    });

    document.querySelectorAll('form[data-live-filter]').forEach((form) => {
        form.addEventListener('change', (event) => {
            const field = event.target;

            if (! (field instanceof HTMLElement)) {
                return;
            }

            if (field.matches('select, input[type="date"]')) {
                form.requestSubmit();
            }
        });

        const search = form.querySelector('input[type="search"][name="q"]');

        if (! search) {
            return;
        }

        let searchTimer;

        search.addEventListener('input', () => {
            window.clearTimeout(searchTimer);
            searchTimer = window.setTimeout(() => form.requestSubmit(), 350);
        });
    });
});
