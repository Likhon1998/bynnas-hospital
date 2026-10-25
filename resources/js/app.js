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

    const adminSearch = document.querySelector('[data-admin-search]');

    if (adminSearch) {
        document.addEventListener('keydown', (event) => {
            if ((event.ctrlKey || event.metaKey) && event.key.toLowerCase() === 'k') {
                event.preventDefault();
                adminSearch.focus();
            }
        });
    }
});
