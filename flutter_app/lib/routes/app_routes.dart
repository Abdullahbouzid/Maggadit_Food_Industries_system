class AppRoutes {
  // Static route names
  static const String login = '/login';
  static const String register = '/register';
  static const String dashboard = '/dashboard';
  static const String orderHistory = '/order-history';
  static const String notifications = '/notifications';
  static const String profile = '/profile';
  static const String offers = '/offers';
  static const String products = '/products';
  static const String cart = '/cart';
  static const String more = '/more';
  static const String returns = '/returns';
  static const String surveys = '/surveys';
  static const String returnCreate = '/returns/create/{invoiceId}';

  // Dynamic route patterns
  static const String orderDetails = '/order/{orderId}';
  static const String offerDetails = '/offer/{offerId}';
  static const String productDetails = '/product/{productId}';
  static const String brandDetails = '/brand/{brandId}';
  static const String brandProducts = '/brand/{brandId}/products';

  // Helper methods to build routes
  static String orderDetailsRoute(int orderId) => '/order/$orderId';
  static String offerDetailsRoute(int offerId) => '/offer/$offerId';
  static String productDetailsRoute(int productId) => '/product/$productId';
  static String brandDetailsRoute(int brandId) => '/brand/$brandId';
  static String returnCreateRoute(int invoiceId) => '/returns/create/$invoiceId';
}
