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

## Customer API (`/api/customer`)
- Routes: `routes/api.php` → `CustomerApiController`, `CustomerAuthController`, `ProfileController`
- Auth: Sanctum personal access token, guard `api` (provider `customers`); send `Authorization: Bearer <token>`
- Response envelope is inconsistent by endpoint — always check the controller before parsing:
  - `products` → paginator under `products`
  - `order-history` → `{data: {stats, rows}}` (`total` is a formatted string like `"1,250.00"`)
  - `orderDetails` → `{order: {...}, steps: [], timeline: []}`; step state is `done|current|pending|rejected`
  - `financial-statement` → `{statement: [transactions], totals: {...}}`
  - `returns` → `{returns: {data: []}}`
  - `surveys/{id}/respond` expects `answers: {question_id: text}` (map, not list)
  - `quotation/respond` accepts `approved|rejected|changes_requested`
- Push: `App\Services\Customer\FcmNotificationService` sends FCM HTTP v1 with Android channel `maggadit_channel`

## Flutter customer app (`mobile/customer_app/`)
- Flutter 3.x app, Arabic RTL UI, provider state, Dio client; mirrors the API above
- Build: `cd mobile/customer_app && flutter pub get && flutter analyze && flutter test`
- Without an Android SDK use `flutter build web --release` as the full compile check
- Server URL and Firebase options are injected with `--dart-define` (see `lib/core/config.dart`)
- `android/app/google-services.json` and `ios/Runner/GoogleService-Info.plist` are gitignored; the
  google-services Gradle plugin is applied only when the json file exists
- Tests: `test/models_test.dart` (real API payload shapes), `test/cart_and_formatters_test.dart`
