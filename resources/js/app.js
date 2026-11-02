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
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';

    const setNotificationBadge = (total) => {
        if (! notificationsBadge) {
            return;
        }

        if (total > 0) {
            notificationsBadge.textContent = String(total);
            notificationsBadge.classList.remove('hidden');
            notificationsBadge.hidden = false;
        } else {
            notificationsBadge.textContent = '0';
            notificationsBadge.classList.add('hidden');
            notificationsBadge.hidden = true;
        }

        if (notificationsAll) {
            notificationsAll.classList.toggle('hidden', total <= 0);
            notificationsAll.hidden = total <= 0;
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
            empty.textContent = 'No critical alerts right now.';
            notificationsBody.appendChild(empty);

            return;
        }

        alerts.forEach((alert) => {
            const link = document.createElement('a');
            link.href = alert.href || '#';
            link.className = `admin-notice admin-notice--${alert.tone || 'orange'}`;

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

    const markNotificationsSeen = async () => {
        if (! notificationsSeenUrl) {
            return;
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
                body: JSON.stringify({ all: true }),
            });

            if (! response.ok) {
                throw new Error('Unable to mark notifications seen');
            }

            const payload = await response.json();
            setNotificationBadge(Number(payload.total || 0));
        } catch (error) {
            // Keep the badge if marking seen fails.
        }
    };

    const refreshNotifications = async ({ markSeen = false } = {}) => {
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

            const payload = await response.json();
            const total = Number(payload.total || 0);
            const alerts = Array.isArray(payload.alerts) ? payload.alerts : [];

            setNotificationBadge(total);
            renderNotifications(alerts);

            if (notificationsAll && payload.href) {
                notificationsAll.setAttribute('href', payload.href);
            }

            if (markSeen && total > 0) {
                await markNotificationsSeen();
            }
        } catch (error) {
            // Keep the last rendered server-side list if refresh fails.
            if (markSeen) {
                await markNotificationsSeen();
            }
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
                    refreshNotifications({ markSeen: true });
                }
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

    const appointmentForm = document.querySelector('#appointment[data-appointment-slots]');
    const appointmentDoctor = document.querySelector('#appointment select[name="doctor"]');
    const appointmentDate = document.querySelector('#appointment input[name="date"]');
    const appointmentTime = document.querySelector('#appointment select[name="time"]');
    const appointmentSlotNote = document.querySelector('#appointment [data-slot-note]');
    const fallbackTimes = appointmentTime
        ? [...appointmentTime.options].filter((option) => option.value !== '').map((option) => option.value)
        : [];

    const setSlotNote = (message, blocked = false) => {
        if (! appointmentSlotNote) {
            return;
        }

        appointmentSlotNote.textContent = message || '';
        appointmentSlotNote.classList.toggle('is-blocked', blocked);
        appointmentSlotNote.hidden = ! blocked && ! message;
    };

    const fillTimeOptions = (slots, preferred) => {
        if (! appointmentTime) {
            return;
        }

        const selected = preferred || appointmentTime.dataset.oldTime || appointmentTime.value;
        appointmentTime.replaceChildren();

        const placeholder = document.createElement('option');
        placeholder.value = '';
        placeholder.textContent = slots.length ? 'Choose an open time' : 'No open times';
        appointmentTime.appendChild(placeholder);

        slots.forEach((slot) => {
            const option = document.createElement('option');
            option.value = slot;
            option.textContent = slot;
            option.selected = slot === selected;
            appointmentTime.appendChild(option);
        });
    };

    const refreshAppointmentSlots = async () => {
        if (! appointmentForm || ! appointmentDoctor || ! appointmentDate || ! appointmentTime) {
            return;
        }

        const slotsUrl = appointmentForm.getAttribute('data-appointment-slots');
        const doctorId = appointmentDoctor.value;
        const date = appointmentDate.value;

        if (! slotsUrl || ! doctorId || ! date) {
            fillTimeOptions(fallbackTimes, appointmentTime.dataset.oldTime);
            setSlotNote('');

            return;
        }

        try {
            const url = new URL(slotsUrl, window.location.origin);
            url.searchParams.set('doctor', doctorId);
            url.searchParams.set('date', date);

            const response = await fetch(url.toString(), {
                headers: {
                    Accept: 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });

            if (! response.ok) {
                throw new Error('Slots unavailable');
            }

            const payload = await response.json();
            const slots = Array.isArray(payload.slots) ? payload.slots : [];

            fillTimeOptions(slots, appointmentTime.dataset.oldTime);

            if (payload.reason) {
                setSlotNote(payload.reason, true);
            } else if (slots.length) {
                setSlotNote('');
            } else {
                setSlotNote('No open slots remain for this doctor on that day.', true);
            }
        } catch (error) {
            fillTimeOptions(fallbackTimes, appointmentTime.dataset.oldTime);
            setSlotNote('');
        }
    };

    if (appointmentDoctor) {
        appointmentDoctor.addEventListener('change', refreshAppointmentSlots);
    }

    if (appointmentDate) {
        appointmentDate.addEventListener('change', refreshAppointmentSlots);
    }

    if (appointmentForm) {
        refreshAppointmentSlots();
    }

    document.querySelectorAll('[data-book-doctor]').forEach((link) => {
        link.addEventListener('click', () => {
            if (appointmentDoctor && link.dataset.bookDoctor) {
                appointmentDoctor.value = link.dataset.bookDoctor;
                refreshAppointmentSlots();
            }
        });
    });

    const doctorSpecialty = document.querySelector('[data-doctor-specialty]');
    const doctorList = document.querySelector('[data-doctor-list]');

    if (doctorSpecialty && doctorList) {
        const syncDoctorOptions = () => {
            const specialty = doctorSpecialty.value;

            [...doctorList.options].forEach((option) => {
                if (option.value === '') {
                    return;
                }

                option.hidden = specialty !== '' && option.dataset.specialty !== specialty;
            });

            if (doctorList.selectedOptions[0]?.hidden) {
                doctorList.value = '';
            }
        };

        doctorSpecialty.addEventListener('change', syncDoctorOptions);
        syncDoctorOptions();
    }

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
});
