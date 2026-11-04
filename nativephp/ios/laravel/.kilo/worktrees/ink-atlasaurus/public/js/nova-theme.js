(function () {
    'use strict';

    const root = document.documentElement;
    const STORAGE_KEY = 'nova-theme';

    // ============================================================
    // THEME SWITCHING (light / dark / auto)
    // ============================================================
    function getStoredTheme() {
        try { return localStorage.getItem(STORAGE_KEY) || 'auto'; } catch (e) { return 'auto'; }
    }

    function setStoredTheme(theme) {
        try { localStorage.setItem(STORAGE_KEY, theme); } catch (e) {}
    }

    function applyTheme(theme) {
        if (theme === 'auto') {
            const prefersDark = window.matchMedia('(prefers-color-scheme: dark)').matches;
            root.setAttribute('data-theme', prefersDark ? 'dark' : 'light');
        } else {
            root.setAttribute('data-theme', theme);
        }
        document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
            btn.setAttribute('data-current', theme);
        });
    }

    function cycleTheme() {
        const current = getStoredTheme();
        const next = current === 'light' ? 'dark' : current === 'dark' ? 'auto' : 'light';
        setStoredTheme(next);
        applyTheme(next);
    }

    document.addEventListener('DOMContentLoaded', function () {
        applyTheme(getStoredTheme());

        document.querySelectorAll('[data-theme-toggle]').forEach((btn) => {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                cycleTheme();
            });
        });

        if (window.matchMedia) {
            const mq = window.matchMedia('(prefers-color-scheme: dark)');
            const handler = function () { if (getStoredTheme() === 'auto') applyTheme('auto'); };
            if (mq.addEventListener) mq.addEventListener('change', handler);
            else if (mq.addListener) mq.addListener(handler);
        }

        // ============================================================
        // SIDEBAR (collapse + mobile)
        // ============================================================
        const shell = document.querySelector('.nova-shell');
        const collapseBtn = document.getElementById('novaSidebarCollapse');
        const mobileMenuBtn = document.getElementById('novaMobileMenu');
        const overlay = document.querySelector('.nova-sidebar-overlay');

        try {
            if (localStorage.getItem('nova-sidebar-collapsed') === '1' && shell) {
                shell.classList.add('sidebar-collapsed');
            }
        } catch (e) {}

        if (collapseBtn && shell) {
            collapseBtn.addEventListener('click', function () {
                shell.classList.toggle('sidebar-collapsed');
                try {
                    localStorage.setItem('nova-sidebar-collapsed', shell.classList.contains('sidebar-collapsed') ? '1' : '0');
                } catch (e) {}
            });
        }

        if (mobileMenuBtn && shell) {
            mobileMenuBtn.addEventListener('click', function () {
                shell.classList.toggle('sidebar-open');
            });
        }

        if (overlay && shell) {
            overlay.addEventListener('click', function () {
                shell.classList.remove('sidebar-open');
            });
        }

        // ============================================================
        // MODALS (Data Definitions / Customer App Properties)
        // ============================================================
        function openModal(id) {
            const modal = document.getElementById(id);
            if (modal) {
                modal.classList.add('is-open');
                modal.setAttribute('aria-hidden', 'false');
                document.body.style.overflow = 'hidden';
            }
        }

        function closeModal(modal) {
            modal.classList.remove('is-open');
            modal.setAttribute('aria-hidden', 'true');
            document.body.style.overflow = '';
        }

        document.querySelectorAll('[data-modal]').forEach((trigger) => {
            trigger.addEventListener('click', function (e) {
                e.preventDefault();
                const id = this.getAttribute('data-modal');
                openModal(id);
            });
        });

        document.querySelectorAll('[data-close-modal]').forEach((el) => {
            el.addEventListener('click', function () {
                const modal = this.closest('.nova-modal');
                if (modal) closeModal(modal);
            });
        });

        document.addEventListener('keydown', function (e) {
            if (e.key === 'Escape') {
                document.querySelectorAll('.nova-modal.is-open').forEach(closeModal);
                if (shell) shell.classList.remove('sidebar-open');
            }
        });

        // ============================================================
        // DATA DEFINITIONS - Subcategory navigation
        // ============================================================
        const definitionsModal = document.getElementById('data-definitions-modal');
        const subcategoriesContainer = document.getElementById('subcategories-container');
        const backBtn = document.getElementById('back-to-main');
        const subTitle = document.getElementById('subcategory-title');
        const subcardsGrid = document.getElementById('subcards-grid');

        // Load definitions data from the hidden div
        const definitionsDataEl = document.getElementById('definitions-data');
        let definitionsData = {};
        if (definitionsDataEl) {
            try {
                definitionsData = JSON.parse(definitionsDataEl.dataset.json);
            } catch (e) {
                console.error('Failed to parse definitions data:', e);
            }
        }

        // Main category cards
        document.querySelectorAll('.nova-modal-card[data-target]').forEach(card => {
            card.addEventListener('click', function (e) {
                e.preventDefault();
                const target = this.getAttribute('data-target');
                const categoryData = definitionsData[target] || {};
                const items = categoryData.items || [];

                subTitle.textContent = this.querySelector('.nova-modal-card-title').textContent;
                subcardsGrid.innerHTML = '';

                items.forEach(item => {
                    const card = document.createElement('a');
                    card.className = 'nova-modal-card';
                    card.href = item.route && item.route !== '#' ? item.route : 'javascript:void(0);';

                    if (item.disabled) {
                        card.classList.add('nova-modal-card-disabled');
                        card.style.opacity = '0.5';
                        card.style.cursor = 'not-allowed';
                    }

                    card.innerHTML = `
                        <div class="nova-modal-card-icon"><i class="fas ${item.icon || 'fas fa-circle'}"></i></div>
                        <div class="nova-modal-card-title">${item.label}</div>
                    `;

                    if (item.route && item.route !== '#') {
                        card.addEventListener('click', function (e) {
                            e.preventDefault();
                            definitionsModal.classList.remove('is-open');
                            document.body.style.overflow = '';
                            window.location.href = item.route;
                        });
                    }

                    subcardsGrid.appendChild(card);
                });

                // Hide main grid, show subcategories
                document.querySelector('.nova-modal-grid').style.display = 'none';
                subcategoriesContainer.style.display = 'block';
            });
        });

        // Back to main
        if (backBtn) {
            backBtn.addEventListener('click', function () {
                document.querySelector('.nova-modal-grid').style.display = 'grid';
                subcategoriesContainer.style.display = 'none';
                subcardsGrid.innerHTML = '';
                subTitle.textContent = '';
            });
        }
    });
})();