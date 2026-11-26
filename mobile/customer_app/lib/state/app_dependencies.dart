import '../core/api_client.dart';
import '../core/config.dart';
import '../core/session_store.dart';
import '../services/auth_service.dart';
import '../services/catalog_service.dart';
import '../services/notification_service.dart';
import '../services/order_service.dart';
import '../services/push_service.dart';
import '../l10n/l10n.dart';
import 'auth_controller.dart';
import 'cart_controller.dart';
import 'favorites_controller.dart';
import 'locale_controller.dart';
import 'notifications_controller.dart';

/// Composition root: one instance per app process.
class AppDependencies {
  AppDependencies._(this.api, this.store);

  final ApiClient api;
  final SessionStore store;

  late final AuthService authService;
  late final CatalogService catalogService;
  late final OrderService orderService;
  late final SurveyService surveyService;
  late final NotificationService notificationService;

  late final LocaleController locale;
  late final AuthController auth;
  late final CartController cart;
  late final FavoritesController favorites;
  late final NotificationsController notifications;
  late final PushService push;

  /// Invoked when a push is tapped so the UI can navigate.
  void Function(PushPayload payload)? onPushTap;

  static Future<AppDependencies> create() async {
    final store = await SessionStore.open();
    final deps = AppDependencies._(ApiClient(), store);

    deps.api.baseUrl = AppConfig.defaultApiBaseUrl;
    deps.api.token = store.token;

    deps.authService = AuthService(deps.api);
    deps.catalogService = CatalogService(deps.api);
    deps.orderService = OrderService(deps.api);
    deps.surveyService = SurveyService(deps.api);
    deps.notificationService = NotificationService(deps.api);

    deps.locale = LocaleController(store);
    deps.cart = CartController(store);
    deps.favorites = FavoritesController(deps.catalogService);
    deps.notifications = NotificationsController(deps.notificationService);

    deps.auth = AuthController(
      api: deps.api,
      store: store,
      onSessionChanged: () {
        if (deps.auth.isAuthenticated) {
          deps.favorites.refreshIds();
          deps.notifications.load();
        } else {
          deps.favorites.clear();
          deps.notifications.reset();
        }
      },
    );

    deps.push = PushService(
      onToken: (token) => deps.auth.setFcmToken(token),
      onForeground: (payload) {
        deps.notifications.prependFromPush(
          title: payload.title ?? L10n.tr('إشعار جديد'),
          body: payload.body ?? '',
        );
      },
      onTap: (payload) => deps.onPushTap?.call(payload),
    );

    return deps;
  }

  Future<void> bootstrap() async {
    await auth.bootstrap();
    await push.initialize();
  }
}
