import 'package:flutter/material.dart';

import '../models/catalog.dart';
import 'screens/about_screen.dart';
import 'screens/cart_screen.dart';
import 'screens/checkout_screen.dart';
import 'screens/contact_screen.dart';
import 'screens/faq_screen.dart';
import 'screens/favorites_screen.dart';
import 'screens/home_screen.dart';
import 'screens/invoice_detail_screen.dart';
import 'screens/invoices_screen.dart';
import 'screens/login_screen.dart';
import 'screens/main_shell.dart';
import 'screens/more_screen.dart';
import 'screens/my_products_screen.dart';
import 'screens/notifications_screen.dart';
import 'screens/offers_screen.dart';
import 'screens/order_detail_screen.dart';
import 'screens/orders_screen.dart';
import 'screens/organizations_screen.dart';
import 'screens/privacy_screen.dart';
import 'screens/product_detail_screen.dart';
import 'screens/products_screen.dart';
import 'screens/profile_screen.dart';
import 'screens/register_screen.dart';
import 'screens/returns_screen.dart';
import 'screens/survey_fill_screen.dart';
import 'screens/surveys_screen.dart';
import 'screens/taxonomy_screen.dart';
import 'screens/wallet_screen.dart';
import '../l10n/l10n.dart';

/// Centralised navigation so every screen can be opened from anywhere
/// (including notification taps).
class Routes {
  const Routes._();

  static final navigatorKey = GlobalKey<NavigatorState>();

  static NavigatorState? get _nav => navigatorKey.currentState;

  static Future<void> _push(Widget page, {bool replace = false}) async {
    final nav = _nav;
    if (nav == null) return;
    if (replace) {
      await nav.pushReplacement(MaterialPageRoute(builder: (_) => page));
    } else {
      await nav.push(MaterialPageRoute(builder: (_) => page));
    }
  }

  static Future<void> home() => _push(const MainShell(), replace: true);

  static Future<void> login() => _push(const LoginScreen(), replace: true);

  static Future<void> register() => _push(const RegisterScreen());

  static Future<void> products({
    String? title,
    String? search,
    int? categoryId,
    int? brandId,
  }) => _push(
    ProductsScreen(
      title: title,
      initialSearch: search,
      categoryId: categoryId,
      brandId: brandId,
    ),
  );

  static Future<void> product(int id, {Product? preview}) =>
      _push(ProductDetailScreen(productId: id, preview: preview));

  static Future<void> categories() =>
      _push(const TaxonomyScreen(kind: TaxonomyKind.categories));

  static Future<void> brands() =>
      _push(const TaxonomyScreen(kind: TaxonomyKind.brands));

  static Future<void> offers() => _push(const OffersScreen());

  static Future<void> cart() => _push(const CartScreen());

  static Future<void> checkout() => _push(const CheckoutScreen());

  static Future<void> favorites() => _push(const FavoritesScreen());

  static Future<void> orders() => _push(const OrdersScreen());

  static Future<void> order(int id) => _push(OrderDetailScreen(orderId: id));

  static Future<void> returns() => _push(const ReturnsScreen());

  static Future<void> myProducts() => _push(const MyProductsScreen());

  static Future<void> notifications() => _push(const NotificationsScreen());

  static Future<void> wallet() => _push(const WalletScreen());

  static Future<void> invoices() => _push(const InvoicesScreen());

  static Future<void> invoice(int id) =>
      _push(InvoiceDetailScreen(invoiceId: id));

  static Future<void> surveys() => _push(const SurveysScreen());

  static Future<void> survey(int id) => _push(SurveyFillScreen(surveyId: id));

  static Future<void> faq() => _push(const FaqScreen());

  static Future<void> about() => _push(const AboutScreen());

  static Future<void> contact() => _push(const ContactScreen());

  static Future<void> privacy() => _push(const PrivacyScreen());

  static Future<void> profile() => _push(const ProfileScreen());

  static Future<void> organizations() => _push(const OrganizationsScreen());

  static Future<void> more() => _push(const MoreScreen());
}
