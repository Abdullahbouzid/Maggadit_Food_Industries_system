import 'package:flutter/foundation.dart';

import '../l10n/l10n.dart';
import '../models/account.dart';
import '../services/notification_service.dart';

enum NotificationFilter { all, unread, starred, archived }

class NotificationsController extends ChangeNotifier {
  NotificationsController(this._service);

  final NotificationService _service;

  List<AppNotification> _items = const [];
  int _unreadCount = 0;
  bool _loading = false;
  String? _error;
  NotificationFilter _filter = NotificationFilter.all;

  List<AppNotification> get items => visibleItems;
  List<AppNotification> get allItems => _items;
  int get unreadCount => _unreadCount;
  bool get loading => _loading;
  String? get error => _error;
  NotificationFilter get filter => _filter;
  bool get isEmpty => visibleItems.isEmpty;

  List<AppNotification> get visibleItems {
    switch (_filter) {
      case NotificationFilter.all:
        return _items.where((e) => !e.isArchived).toList();
      case NotificationFilter.unread:
        return _items.where((e) => !e.isRead && !e.isArchived).toList();
      case NotificationFilter.starred:
        return _items.where((e) => e.isStarred && !e.isArchived).toList();
      case NotificationFilter.archived:
        return _items.where((e) => e.isArchived).toList();
    }
  }

  void setFilter(NotificationFilter value) {
    if (_filter == value) return;
    _filter = value;
    notifyListeners();
  }

  Future<void> load() async {
    _loading = true;
    _error = null;
    notifyListeners();
    try {
      final result = await _service.list();
      _items = result.items;
      _unreadCount = result.unreadCount;
    } catch (e) {
      _error = e.toString();
    } finally {
      _loading = false;
      notifyListeners();
    }
  }

  /// Polls the API for notifications pushed while the app was closed.
  Future<int> checkNew() async {
    try {
      final lastId = _items.isEmpty
          ? 0
          : _items.map((e) => e.id).reduce((a, b) => a > b ? a : b);
      final fresh = await _service.checkNew(lastId: lastId);
      if (fresh.isEmpty) return 0;
      final known = _items.map((e) => e.id).toSet();
      final merged = [...fresh.where((e) => !known.contains(e.id)), ..._items];
      _items = merged;
      _unreadCount = _items.where((e) => !e.isRead && !e.isArchived).length;
      notifyListeners();
      return fresh.length;
    } catch (_) {
      return 0;
    }
  }

  Future<void> markRead(AppNotification notification) async {
    if (notification.isRead) return;
    _replace(notification.copyWith(isRead: true));
    if (_unreadCount > 0) _unreadCount--;
    notifyListeners();
    try {
      await _service.markRead(notification.id);
    } catch (_) {
      _replace(notification);
      _unreadCount++;
      notifyListeners();
    }
  }

  Future<void> toggleRead(AppNotification notification) async {
    final next = notification.copyWith(isRead: !notification.isRead);
    _replace(next);
    _unreadCount = _items.where((e) => !e.isRead && !e.isArchived).length;
    notifyListeners();
    try {
      await _service.toggleRead(notification.id);
    } catch (_) {
      _replace(notification);
      notifyListeners();
    }
  }

  Future<void> toggleStar(AppNotification notification) async {
    final next = notification.copyWith(isStarred: !notification.isStarred);
    _replace(next);
    notifyListeners();
    try {
      await _service.toggleStar(notification.id);
    } catch (_) {
      _replace(notification);
      notifyListeners();
    }
  }

  Future<void> toggleArchive(AppNotification notification) async {
    final next = notification.copyWith(isArchived: !notification.isArchived);
    _replace(next);
    notifyListeners();
    try {
      await _service.toggleArchive(notification.id);
    } catch (_) {
      _replace(notification);
      notifyListeners();
    }
  }

  void reset() {
    _items = const [];
    _unreadCount = 0;
    _error = null;
    notifyListeners();
  }

  /// Adds a notification received through FCM while the app is running.
  void prependFromPush({
    required String title,
    required String body,
    String? image,
  }) {
    final notification = AppNotification(
      id: DateTime.now().millisecondsSinceEpoch ~/ 1000,
      subject: title,
      body: body,
      iconType: 'notification',
      isRead: false,
      isStarred: false,
      isArchived: false,
      createdAt: L10n.tr('الآن'),
      image: image,
    );
    _items = [notification, ..._items];
    _unreadCount++;
    notifyListeners();
  }

  void _replace(AppNotification value) {
    _items = _items.map((e) => e.id == value.id ? value : e).toList();
  }
}
