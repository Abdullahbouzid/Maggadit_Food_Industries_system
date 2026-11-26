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
- Flutter 3.x app, Arabic RTL UI with a live Arabic/English switch, provider state, Dio client; mirrors the API above
- Default API root is production `https://blue-beaver-923812.hostingersite.com/api`
  (`lib/core/config.dart`); override at build time with `--dart-define=API_BASE_URL=...`
- Relative media paths from the API resolve under `/uploads` (`lib/models/json_utils.dart` → `mediaUrl`)
- Localisation: write Arabic inside `tr(context, '...')` (or `tArgs(context, '... {0} ...', [v])`),
  add the English copy to `lib/l10n/en_strings.dart`; `test/l10n_test.dart` fails on a missing key
- Locale is stored in `SharedPreferences` key `app.language` via `LocaleController` (`lib/state/`)
- Server messages are Arabic; `ApiClient` passes them through `L10n.tr` so known ones render in English
- Branding: `public/icon.png` is the source; run the launcher/logo generation to refresh
  `assets/images/logo_tile.png` and the Android/iOS/web icon sets
- Build: `cd mobile/customer_app && flutter pub get && flutter analyze && flutter test`
- Without an Android SDK use `flutter build web --release` as the full compile check
- Firebase options are injected with `--dart-define` (see `lib/core/config.dart`)
- `android/app/google-services.json` and `ios/Runner/GoogleService-Info.plist` are gitignored; the
  google-services Gradle plugin is applied only when the json file exists
- Tests: `test/models_test.dart` (real API payload shapes), `test/cart_and_formatters_test.dart`,
  `test/media_url_test.dart`, `test/l10n_test.dart`, `test/locale_controller_test.dart`
