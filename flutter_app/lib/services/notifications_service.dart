import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:async';
import 'dart:convert';
import 'package:http/http.dart' as http;

class NotificationsService {
  static const String _lastNotificationIdKey = 'mgd_last_notif_id';
  static const String _unreadCountKey = 'mgd_unread_count';
  static const String _apiTokenKey = 'api_token';
  static const String _baseUrl = 'https://blue-beaver-923812.hostingersite.com/api';

  Timer? _pollingTimer;
  final int _pollInterval = 10000; // 10 seconds
  bool _isPolling = false;
  final GlobalKey<NavigatorState> navigatorKey = GlobalKey<NavigatorState>();

  // Getters for local storage
  Future<int> getLastNotificationId() async {
    final SharedPreferences prefs = await SharedPreferences.getInstance();
    return prefs.getInt(_lastNotificationIdKey) ?? 0;
  }

  Future<void> setLastNotificationId(int id) async {
    final SharedPreferences prefs = await SharedPreferences.getInstance();
    final current = await getLastNotificationId();
    if (id > current) {
      await prefs.setInt(_lastNotificationIdKey, id);
    }
  }

  Future<int> getUnreadCount() async {
    final SharedPreferences prefs = await SharedPreferences.getInstance();
    return prefs.getInt(_unreadCountKey) ?? 0;
  }

  Future<void> setUnreadCount(int count) async {
    final SharedPreferences prefs = await SharedPreferences.getInstance();
    await prefs.setInt(_unreadCountKey, count);
  }

  // Get API token from local storage
  Future<String?> getApiToken() async {
    final SharedPreferences prefs = await SharedPreferences.getInstance();
    return prefs.getString(_apiTokenKey);
  }

  // Get CSRF token
  Future<String> _getCsrfToken() async {
    final SharedPreferences prefs = await SharedPreferences.getInstance();
    return prefs.getString('csrf_token') ?? '';
  }

  // Check for new notifications
  Future<Map<String, dynamic>> checkNewNotifications(int lastId) async {
    try {
      final apiToken = await getApiToken();
      final csrfToken = await _getCsrfToken();

      final Map<String, String> headers = {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-CSRF-TOKEN': csrfToken,
      };
      if (apiToken != null) {
        headers['Authorization'] = 'Bearer $apiToken';
      }

      final response = await http.post(
        Uri.parse('$_baseUrl/customer/notifications/check_new'),
        headers: headers,
        body: jsonEncode({'last_id': lastId}),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        return data;
      }
    } catch (e) {
      print('Error checking notifications: $e');
    }
    return {};
  }

  // Show native notification (Android/iOS)
  void showNativeNotification(String title, String body) {
    try {
      // Use Flutter's built-in Toast/Snackbar or a plugin for native notifications
      // For now, we'll show a simple Snackbar
      if (navigatorKey.currentContext != null) {
        ScaffoldMessenger.of(navigatorKey.currentContext!).showSnackBar(
          SnackBar(
            content: Text('$title\n$body'),
            duration: Duration(seconds: 3),
            behavior: SnackBarBehavior.floating,
            shape: RoundedRectangleBorder(
              borderRadius: BorderRadius.circular(8),
            ),
            action: SnackBarAction(
              label: 'View',
              onPressed: () {
                // Navigate to notifications screen
                Navigator.pushNamed(navigatorKey.currentContext!, '/notifications');
              },
            ),
          ),
        );
      }
    } catch (e) {
      print('Error showing native notification: $e');
    }
  }

  // Start polling for new notifications
  void startPolling(Function onNewNotifications) {
    if (_isPolling) return;
    _isPolling = true;

    _pollingTimer = Timer.periodic(Duration(milliseconds: _pollInterval), (timer) async {
      try {
        await onNewNotifications();
      } catch (e) {
        print('Error in notification polling: $e');
      }
    });
  }

  void stopPolling() {
    _pollingTimer?.cancel();
    _pollingTimer = null;
    _isPolling = false;
  }

  bool get isPolling => _isPolling;

  void init(BuildContext context, String token) {
    // Initialize notifications service with context and token
    // This sets up the polling mechanism for new notifications
  }
}