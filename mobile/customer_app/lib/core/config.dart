/// Global, compile-time configuration.
///
/// Every value can be overridden at build time, e.g.
/// `flutter build apk --dart-define=API_BASE_URL=https://app.maggadit.ly/api`
class AppConfig {
  const AppConfig._();

  static const String appName = 'ماغديت';
  static const String companyName = 'ماغديت للصناعات الغذائية';

  /// Laravel API root (routes/api.php -> /api/customer/...).
  static const String defaultApiBaseUrl = String.fromEnvironment(
    'API_BASE_URL',
    defaultValue: 'http://10.0.2.2:8000/api',
  );

  static const Duration connectTimeout = Duration(seconds: 20);
  static const Duration receiveTimeout = Duration(seconds: 30);

  /// Firebase project used by the backend (FCM service).
  static const String firebaseProjectId = String.fromEnvironment(
    'FIREBASE_PROJECT_ID',
    defaultValue: 'maggaditfoodsystem',
  );
  static const String firebaseApiKey = String.fromEnvironment('FIREBASE_API_KEY');
  static const String firebaseAppId = String.fromEnvironment('FIREBASE_APP_ID');
  static const String firebaseSenderId = String.fromEnvironment('FIREBASE_SENDER_ID');
  static const String firebaseStorageBucket =
      String.fromEnvironment('FIREBASE_STORAGE_BUCKET');

  /// Must match the channel id used by the Laravel push sender.
  static const String androidChannelId = 'maggadit_channel';
  static const String androidChannelName = 'إشعارات ماغديت';
  static const String androidChannelDescription = 'تحديثات الطلبات والعروض والفواتير';

  /// Topic used by the dashboard for broadcast notifications.
  static const String broadcastTopic = 'customers';

  static bool get hasFirebaseDartDefine =>
      firebaseApiKey.isNotEmpty && firebaseAppId.isNotEmpty && firebaseSenderId.isNotEmpty;
}
