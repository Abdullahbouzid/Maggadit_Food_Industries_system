# MAGMADIT Food Industries System

## Project Overview
Laravel-based inventory/warehouse management system with a dashboard admin panel using the Nova design system (CSS at `public/css/nova.css`).

## Lint / Validation Commands
- Blade files: `php -l <file>` — all 188 dashboard Blade files pass with zero syntax errors
- Run full check: `find resources/views/dashboard/ -name "*.blade.php" -exec php -l {} \; 2>&1 | grep -iv "No syntax errors"`

## Conventions
- Blade views extend `dashboard.partials.layout`
- Nova design system classes: `nova-card`, `nova-btn`, `nova-input`, `nova-select`, `nova-table`, `nova-badge`, `nova-alert`, `nova-actionbar`, `nova-stat`, `nova-form-group`, `nova-form-check`, etc.
- Icons: `fas fa-*` (Font Awesome 6 with `fas` prefix)
- Delete modals: `nova-modal-backdrop` + `nova-modal` + `nova-delete-modal` with `confirmDelete()` JS
- Pagination: `$model->links()` (Nova default)
- Arabic labels used in dashboard views for bilingual support
