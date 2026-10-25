import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

import 'screens/login_screen.dart';
import 'screens/register_screen.dart';
import 'screens/dashboard_screen.dart';
import 'screens/order_history_screen.dart';
import 'screens/orders/order_details_screen.dart';
import 'screens/offers/offers_screen.dart';
import 'screens/profile_screen.dart';
import 'screens/notifications_screen.dart';
import 'screens/products/products_screen.dart';
import 'screens/brands/brand_screen.dart';
import 'screens/categories/category_screen.dart';
import 'screens/product_show_screen.dart';
import 'screens/cart/cart_screen.dart';
import 'screens/more/more_menu_screen.dart';
import 'screens/more/return_create_screen.dart';
import 'screens/returns/returns_screen.dart';
import 'screens/survey/survey_screen.dart';
import 'theme/app_theme.dart';
import 'routes/app_routes.dart';

void main() async {
  WidgetsFlutterBinding.ensureInitialized();

  FlutterError.onError = (FlutterErrorDetails details) {
    FlutterError.presentError(details);
  };

  final SharedPreferences prefs = await SharedPreferences.getInstance();
  final String? token = prefs.getString('api_token');
  final String? userId = prefs.getString('customer_id');

  final bool isLoggedIn = token != null && token.isNotEmpty && userId != null && userId.isNotEmpty;

  runApp(MaggaditApp(
    initialRoute: isLoggedIn ? AppRoutes.dashboard : AppRoutes.login,
    token: token ?? '',
    userId: userId ?? '',
  ));
}

class MaggaditApp extends StatefulWidget {
  final String? initialRoute;
  final String token;
  final String userId;

  const MaggaditApp({
    Key? key,
    required this.initialRoute,
    required this.token,
    required this.userId,
  }) : super(key: key);

  @override
  State<MaggaditApp> createState() => _MaggaditAppState();
}

class _MaggaditAppState extends State<MaggaditApp> {
  String currentRoute = '';

  @override
  void initState() {
    super.initState();
    currentRoute = widget.initialRoute ?? AppRoutes.login;
  }

  void setRoute(String route) {
    setState(() {
      currentRoute = route;
    });
  }

  @override
  Widget build(BuildContext context) {
    return MaterialApp(
      title: 'Maggadit Food Industries',
      theme: appTheme(),
      darkTheme: appTheme(brightness: Brightness.dark),
      themeMode: ThemeMode.system,
      initialRoute: widget.initialRoute,
      routes: {
        AppRoutes.login: (context) => LoginScreen(),
        AppRoutes.register: (context) => RegisterScreen(),
        AppRoutes.dashboard: (context) => DashboardScreen(
          token: widget.token,
          userId: widget.userId,
        ),
        AppRoutes.orderHistory: (context) => OrderHistoryScreen(
          token: widget.token,
          userId: widget.userId,
        ),
        AppRoutes.notifications: (context) => NotificationsScreen(
          token: widget.token,
          userId: widget.userId,
        ),
        AppRoutes.profile: (context) => ProfileScreen(
          token: widget.token,
          userId: widget.userId,
        ),
        AppRoutes.offers: (context) => OffersScreen(
          token: widget.token,
          userId: widget.userId,
        ),
        AppRoutes.products: (context) => ProductsScreen(
          token: widget.token,
          userId: widget.userId,
        ),
        AppRoutes.cart: (context) => CartScreen(
          token: widget.token,
          userId: widget.userId,
        ),
        AppRoutes.more: (context) => MoreMenuScreen(
          token: widget.token,
          userId: widget.userId,
        ),
        AppRoutes.returns: (context) => ReturnsScreen(
          token: widget.token,
          userId: widget.userId,
        ),
        AppRoutes.surveys: (context) => SurveyScreen(
          token: widget.token,
          userId: widget.userId,
        ),
      },
      onGenerateRoute: (RouteSettings settings) {
        if (settings.name == null) return null;
        final uri = Uri.parse(settings.name!);
        final pathSegments = uri.pathSegments;
        if (pathSegments.isEmpty) return null;

        // Order details: /order/{orderId}
        if (pathSegments.first == 'order' && pathSegments.length > 1) {
          final orderId = int.tryParse(pathSegments[1]) ?? 0;
          return MaterialPageRoute(
            builder: (context) => OrderDetailsScreen(
              token: widget.token,
              userId: widget.userId,
              orderId: orderId,
            ),
          );
        }

        // Offer details: /offer/{offerId}
        if (pathSegments.first == 'offer' && pathSegments.length > 1) {
          final offerId = pathSegments[1];
          // Note: offerId is currently unused as OffersScreen shows all offers
          return MaterialPageRoute(
            builder: (context) => OffersScreen(
              token: widget.token,
              userId: widget.userId,
            ),
          );
        }

        // Product details: /product/{productId}
        if (pathSegments.first == 'product' && pathSegments.length > 1) {
          final productId = int.tryParse(pathSegments[1]) ?? 0;
          return MaterialPageRoute(
            builder: (context) => ProductShowScreen(
              token: widget.token,
              userId: widget.userId,
              productId: productId,
            ),
          );
        }

        // Brand details: /brand/{brandId}
        if (pathSegments.first == 'brand' && pathSegments.length > 1) {
          final brandId = int.tryParse(pathSegments[1]) ?? 0;
          final brandName = pathSegments.length > 2 ? pathSegments[2] : 'Brand';
          return MaterialPageRoute(
            builder: (context) => BrandScreen(
              token: widget.token,
              userId: widget.userId,
              brandId: brandId,
              brandName: brandName,
            ),
          );
        }

        // Category details: /category/{categoryId}
        if (pathSegments.first == 'category' && pathSegments.length > 1) {
          final categoryId = int.tryParse(pathSegments[1]) ?? 0;
          final categoryName = pathSegments.length > 2 ? pathSegments[2] : 'Category';
          return MaterialPageRoute(
            builder: (context) => CategoryScreen(
              token: widget.token,
              userId: widget.userId,
              categoryId: categoryId,
              categoryName: categoryName,
            ),
          );
        }

        // Return create: /returns/create/{invoiceId}
        if (pathSegments.first == 'returns' && pathSegments.length > 2 && pathSegments[1] == 'create') {
          final invoiceId = int.tryParse(pathSegments[2]) ?? 0;
          return MaterialPageRoute(
            builder: (context) => ReturnCreateScreen(
              token: widget.token,
              userId: widget.userId,
              invoiceId: invoiceId,
            ),
          );
        }

        return null;
      },
    );
  }
}


