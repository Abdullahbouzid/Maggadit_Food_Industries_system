import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';
import 'dart:async';
import '../services/api_service.dart';

class NotificationProvider extends ChangeNotifier {
  static const String _lastNotificationIdKey = 'mgd_last_notif_id';
  static const String _unreadCountKey = 'mgd_unread_count';

  int _lastId = 0;
  int _unreadCount = 0;
  List<dynamic> _notifications = [];
  bool _isPolling = false;
  Timer? _pollingTimer;

  // Getters
  int get lastId => _lastId;
  int get unreadCount => _unreadCount;
  List<dynamic> get notifications => _notifications;
  bool get isPolling => _isPolling;

  // Initialize from local storage
  Future<void> init() async {
    final prefs = await SharedPreferences.getInstance();
    _lastId = prefs.getInt(_lastNotificationIdKey) ?? 0;
    _unreadCount = prefs.getInt(_unreadCountKey) ?? 0;
    notifyListeners();
  }

  // Save state to local storage
  Future<void> _saveState() async {
    final prefs = await SharedPreferences.getInstance();
    await prefs.setInt(_lastNotificationIdKey, _lastId);
    await prefs.setInt(_unreadCountKey, _unreadCount);
  }

  // Start polling for new notifications
  void startPolling() {
    if (_isPolling) return;

    _isPolling = true;
    notifyListeners();

    _pollingTimer = Timer.periodic(Duration(seconds: 10), (timer) async {
      await _poll();
    });
  }

  // Stop polling
  void stopPolling() {
    _pollingTimer?.cancel();
    _pollingTimer = null;
    _isPolling = false;
    notifyListeners();
  }

  // Poll for new notifications
  Future<void> _poll() async {
    if (!_isPolling) return;

    try {
      final api = ApiService();
      final data = await api.checkNewNotifications(_lastId);

      if (!(data['success'] ?? false)) {
        stopPolling();
        return;
      }

      // Update unread count from server
      final unreadCount = data['unread_count'] as int?;
      if (unreadCount != null && unreadCount != _unreadCount) {
        _unreadCount = unreadCount;
        await _saveState();
      }

      // Fetch full notifications list
      final notifications = await api.getNotifications();
      if (notifications.isNotEmpty) {
        final maxId = notifications.first.id;
        if (maxId > _lastId) {
          _lastId = maxId;
          await _saveState();
        }

        // Check for new notifications
        final newNotifs = notifications.where((n) => n.id > _lastId).toList();
        if (newNotifs.isNotEmpty) {
          onNewNotifications?.call(newNotifs);
        }

        // Update notifications list
        _notifications = notifications;
        final unreadCount = notifications.where((n) => !n.isRead).length;
        if (unreadCount != _unreadCount) {
          _unreadCount = unreadCount;
          await _saveState();
        }
      }

      notifyListeners();
    } catch (e) {
      // Silent fail - don't disturb user
      stopPolling();
    }
  }

  // Callback for new notifications
  Function(List<dynamic>)? onNewNotifications;

  // Update notifications from outside
  void updateNotifications(List<dynamic> notifications) {
    _notifications = notifications;
    final unreadCount = notifications.where((n) => !n.isRead).length;
    if (unreadCount != _unreadCount) {
      _unreadCount = unreadCount;
      _saveState();
    }
    notifyListeners();
  }

  // Mark all notifications as read
  Future<void> markAllAsRead() async {
    _unreadCount = 0;
    await _saveState();
    notifyListeners();
  }

  // Dispose
  @override
  void dispose() {
    stopPolling();
    super.dispose();
  }
}