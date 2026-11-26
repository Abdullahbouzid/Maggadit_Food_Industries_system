import '../core/api_client.dart';
import '../models/account.dart';
import '../models/json_utils.dart';

class NotificationsResult {
  NotificationsResult({required this.items, required this.unreadCount});

  final List<AppNotification> items;
  final int unreadCount;
}

class NotificationService {
  NotificationService(this._api);

  static const _prefix = '/customer';
  final ApiClient _api;

  String? get _baseUrl => _api.baseUrl;

  Future<NotificationsResult> list() async {
    final json = await _api.get('$_prefix/notifications');
    return NotificationsResult(
      items: asMapList(
        json['notifications'],
      ).map((e) => AppNotification.fromJson(e, baseUrl: _baseUrl)).toList(),
      unreadCount: asInt(json['unread_count']),
    );
  }

  /// Used for polling / foreground badge refresh.
  Future<List<AppNotification>> checkNew({int lastId = 0}) async {
    final json = await _api.post(
      '$_prefix/notifications/check-new',
      body: {'last_id': lastId},
    );
    return asMapList(
      json['new_notifications'],
    ).map((e) => AppNotification.fromJson(e, baseUrl: _baseUrl)).toList();
  }

  Future<void> markRead(int id) async {
    await _api.post('$_prefix/notifications/$id/read');
  }

  Future<bool> toggleRead(int id) async {
    final json = await _api.post('$_prefix/notifications/$id/toggle-read');
    return asBool(json['is_read']);
  }

  Future<bool> toggleStar(int id) async {
    final json = await _api.post('$_prefix/notifications/$id/toggle-star');
    return asBool(json['is_starred']);
  }

  Future<bool> toggleArchive(int id) async {
    final json = await _api.post('$_prefix/notifications/$id/toggle-archive');
    return asBool(json['is_archived']);
  }

  Future<void> updateFcmToken(String token) async {
    await _api.post('$_prefix/fcm-token', body: {'fcm_token': token});
  }
}
