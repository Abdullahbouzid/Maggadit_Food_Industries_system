import 'dart:convert';
import 'dart:io' show Platform;

import 'package:firebase_core/firebase_core.dart';
import 'package:firebase_messaging/firebase_messaging.dart';
import 'package:flutter/foundation.dart';
import 'package:flutter_local_notifications/flutter_local_notifications.dart';

import '../core/config.dart';

/// Background isolate entry point (data-only messages).
/// Notification payloads are rendered by the OS while the app is terminated.
@pragma('vm:entry-point')
Future<void> firebaseBackgroundHandler(RemoteMessage message) async {
  try {
    await Firebase.initializeApp(options: FirebaseBootstrap.options);
  } catch (_) {
    // Native config may be missing in debug builds; nothing to do offline.
  }
  debugPrint('[push] background message: ${message.messageId}');
}

/// Resolves FirebaseOptions from --dart-define values (or native config files).
class FirebaseBootstrap {
  const FirebaseBootstrap._();

  static FirebaseOptions? options;

  static Future<bool> ensureInitialized() async {
    if (kIsWeb) return false;
    if (Firebase.apps.isNotEmpty) return true;

    try {
      final resolved = options ?? _fromDartDefine();
      if (resolved != null) {
        await Firebase.initializeApp(options: resolved);
      } else {
        // Falls back to android/app/google-services.json + GoogleService-Info.plist.
        await Firebase.initializeApp();
      }
      options = Firebase.app().options;
      return true;
    } catch (error) {
      debugPrint('[push] Firebase disabled: $error');
      return false;
    }
  }

  static FirebaseOptions? _fromDartDefine() {
    if (!AppConfig.hasFirebaseDartDefine) return null;
    final storageBucket = AppConfig.firebaseStorageBucket.isNotEmpty
        ? AppConfig.firebaseStorageBucket
        : '${AppConfig.firebaseProjectId}.appspot.com';

    if (Platform.isIOS || Platform.isMacOS) {
      return FirebaseOptions(
        apiKey: AppConfig.firebaseApiKey,
        appId: const String.fromEnvironment(
          'FIREBASE_IOS_APP_ID',
          defaultValue: '',
        ),
        messagingSenderId: AppConfig.firebaseSenderId,
        projectId: AppConfig.firebaseProjectId,
        storageBucket: storageBucket,
        iosBundleId: const String.fromEnvironment(
          'FIREBASE_IOS_BUNDLE_ID',
          defaultValue: 'com.maggadit.maggaditCustomer',
        ),
      );
    }

    return FirebaseOptions(
      apiKey: AppConfig.firebaseApiKey,
      appId: AppConfig.firebaseAppId,
      messagingSenderId: AppConfig.firebaseSenderId,
      projectId: AppConfig.firebaseProjectId,
      storageBucket: storageBucket,
    );
  }
}

class PushPayload {
  PushPayload({
    this.title,
    this.body,
    this.notificationId,
    this.type,
    this.orderId,
    this.screen,
    this.raw = const {},
  });

  factory PushPayload.fromMessage(RemoteMessage message) {
    final data = message.data;
    final notification = message.notification;
    return PushPayload(
      title: notification?.title ?? data['title']?.toString(),
      body: notification?.body ?? data['body']?.toString(),
      notificationId: int.tryParse(data['id']?.toString() ?? ''),
      type: data['type']?.toString(),
      orderId: int.tryParse(data['order_id']?.toString() ?? ''),
      screen: data['screen']?.toString() ?? data['click_action']?.toString(),
      raw: data.map((key, value) => MapEntry(key, value.toString())),
    );
  }

  final String? title;
  final String? body;
  final int? notificationId;
  final String? type;
  final int? orderId;
  final String? screen;
  final Map<String, String> raw;
}

/// Owns Firebase Messaging + local notifications:
/// * foreground pushes are shown as in-app heads-up notifications,
/// * background/terminated pushes are rendered by the OS,
/// * taps are forwarded to the app router.
class PushService {
  PushService({this.onToken, this.onForeground, this.onTap});

  final Future<void> Function(String token)? onToken;
  final void Function(PushPayload payload)? onForeground;
  final void Function(PushPayload payload)? onTap;

  final FlutterLocalNotificationsPlugin _local =
      FlutterLocalNotificationsPlugin();
  bool _ready = false;
  bool _initialisedLocally = false;

  bool get isReady => _ready;

  Future<void> initialize() async {
    await _initLocalNotifications();
    final firebaseReady = await FirebaseBootstrap.ensureInitialized();
    if (!firebaseReady) return;

    final messaging = FirebaseMessaging.instance;

    try {
      await messaging.requestPermission(alert: true, badge: true, sound: true);
      await messaging.setForegroundNotificationPresentationOptions(
        alert: true,
        badge: true,
        sound: true,
      );
    } catch (error) {
      debugPrint('[push] permission error: $error');
    }

    FirebaseMessaging.onBackgroundMessage(firebaseBackgroundHandler);

    FirebaseMessaging.onMessage.listen((message) async {
      final payload = PushPayload.fromMessage(message);
      await _showLocal(payload);
      onForeground?.call(payload);
    });

    FirebaseMessaging.onMessageOpenedApp.listen((message) {
      onTap?.call(PushPayload.fromMessage(message));
    });

    final initial = await messaging.getInitialMessage();
    if (initial != null) onTap?.call(PushPayload.fromMessage(initial));

    messaging.onTokenRefresh.listen((token) {
      onToken?.call(token);
    });

    await syncToken();
    _ready = true;
    await subscribeToBroadcastTopic();
  }

  Future<String?> syncToken() async {
    try {
      final token = await FirebaseMessaging.instance.getToken();
      if (token != null && token.isNotEmpty) {
        onToken?.call(token);
      }
      return token;
    } catch (error) {
      debugPrint('[push] token error: $error');
      return null;
    }
  }

  /// Broadcast topic so the dashboard can notify every customer at once.
  Future<void> subscribeToBroadcastTopic() async {
    if (!_ready) return;
    try {
      await FirebaseMessaging.instance.subscribeToTopic(
        AppConfig.broadcastTopic,
      );
    } catch (error) {
      debugPrint('[push] topic subscription failed: $error');
    }
  }

  Future<void> _initLocalNotifications() async {
    if (_initialisedLocally || kIsWeb) return;

    const android = AndroidInitializationSettings('@mipmap/ic_launcher');
    const darwin = DarwinInitializationSettings(
      requestAlertPermission: true,
      requestBadgePermission: true,
      requestSoundPermission: true,
    );

    await _local.initialize(
      const InitializationSettings(
        android: android,
        iOS: darwin,
        macOS: darwin,
      ),
      onDidReceiveNotificationResponse: (response) {
        final raw = response.payload;
        if (raw == null || raw.isEmpty) return;
        onTap?.call(_payloadFromJson(raw));
      },
    );

    if (Platform.isAndroid) {
      final androidPlugin = _local
          .resolvePlatformSpecificImplementation<
            AndroidFlutterLocalNotificationsPlugin
          >();
      await androidPlugin?.createNotificationChannel(
        const AndroidNotificationChannel(
          AppConfig.androidChannelId,
          AppConfig.androidChannelName,
          description: AppConfig.androidChannelDescription,
          importance: Importance.max,
          playSound: true,
        ),
      );
      await androidPlugin?.requestNotificationsPermission();
    }

    _initialisedLocally = true;
  }

  Future<void> _showLocal(PushPayload payload) async {
    if (!_initialisedLocally) return;

    final body = payload.body ?? '';
    final details = NotificationDetails(
      android: AndroidNotificationDetails(
        AppConfig.androidChannelId,
        AppConfig.androidChannelName,
        channelDescription: AppConfig.androidChannelDescription,
        importance: Importance.max,
        priority: Priority.high,
        icon: '@mipmap/ic_launcher',
        styleInformation: BigTextStyleInformation(body),
      ),
      iOS: const DarwinNotificationDetails(
        presentAlert: true,
        presentBadge: true,
        presentSound: true,
      ),
    );

    await _local.show(
      payload.notificationId ?? DateTime.now().millisecondsSinceEpoch ~/ 1000,
      payload.title ?? AppConfig.appName,
      body,
      details,
      payload: payload.raw.isEmpty ? null : jsonEncode(payload.raw),
    );
  }

  PushPayload _payloadFromJson(String raw) {
    try {
      final decoded = jsonDecode(raw);
      if (decoded is Map) {
        final data = decoded.map(
          (key, value) => MapEntry(key.toString(), value.toString()),
        );
        return PushPayload(
          title: data['title'],
          body: data['body'],
          notificationId: int.tryParse(data['id'] ?? ''),
          type: data['type'],
          orderId: int.tryParse(data['order_id'] ?? ''),
          screen: data['screen'] ?? data['click_action'],
          raw: data,
        );
      }
    } catch (_) {
      // Not JSON: treat the payload as a notification id.
    }
    return PushPayload(notificationId: int.tryParse(raw), raw: {'id': raw});
  }
}
