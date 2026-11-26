import 'package:flutter/material.dart';

import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:provider/provider.dart';

import 'core/config.dart';
import 'services/push_service.dart';
import 'state/app_dependencies.dart';
import 'state/auth_controller.dart';
import 'state/cart_controller.dart';
import 'state/favorites_controller.dart';
import 'state/locale_controller.dart';
import 'state/notifications_controller.dart';
import 'ui/router.dart';
import 'ui/screens/splash_screen.dart';
import 'ui/theme/app_theme.dart';

class MaggaditApp extends StatelessWidget {
  const MaggaditApp({super.key, required this.dependencies});

  final AppDependencies dependencies;

  @override
  Widget build(BuildContext context) {
    return MultiProvider(
      providers: [
        Provider<AppDependencies>.value(value: dependencies),
        ChangeNotifierProvider<LocaleController>.value(
          value: dependencies.locale,
        ),
        ChangeNotifierProvider<AuthController>.value(value: dependencies.auth),
        ChangeNotifierProvider<CartController>.value(value: dependencies.cart),
        ChangeNotifierProvider<FavoritesController>.value(
          value: dependencies.favorites,
        ),
        ChangeNotifierProvider<NotificationsController>.value(
          value: dependencies.notifications,
        ),
      ],
      child: Consumer<LocaleController>(
        builder: (context, locale, _) => MaterialApp(
          title: AppConfig.appName,
          debugShowCheckedModeBanner: false,
          navigatorKey: Routes.navigatorKey,
          theme: buildTheme(),
          locale: locale.locale,
          supportedLocales: LocaleController.supported,
          localizationsDelegates: const [
            GlobalMaterialLocalizations.delegate,
            GlobalWidgetsLocalizations.delegate,
            GlobalCupertinoLocalizations.delegate,
          ],
          builder: (context, child) => Directionality(
            textDirection: locale.direction,
            child: child ?? const SizedBox.shrink(),
          ),
          home: const SplashScreen(),
          onGenerateRoute: (settings) => MaterialPageRoute(
            builder: (_) => const SplashScreen(),
            settings: settings,
          ),
        ),
      ),
    );
  }

  /// Handles a notification tap: order notifications open the order,
  /// everything else opens the notifications list.
  static void handlePushTap(BuildContext context, PushPayload payload) {
    if (payload.orderId != null) {
      Routes.order(payload.orderId!);
      return;
    }
    final screen = (payload.screen ?? '').toLowerCase();
    final type = (payload.type ?? '').toLowerCase();
    if (screen.contains('order') ||
        type.contains('order') ||
        type.contains('quotation')) {
      Routes.orders();
      return;
    }
    if (screen.contains('wallet') ||
        type.contains('invoice') ||
        type.contains('payment')) {
      Routes.wallet();
      return;
    }
    Routes.notifications();
  }
}
