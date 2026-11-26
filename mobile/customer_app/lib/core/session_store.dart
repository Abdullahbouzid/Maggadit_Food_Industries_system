import 'dart:convert';

import 'package:shared_preferences/shared_preferences.dart';

/// Persists the sanctum token, the cached customer payload, the cart and the
/// selected language.
class SessionStore {
  SessionStore(this._prefs);

  static const _tokenKey = 'auth.token';
  static const _customerKey = 'auth.customer';
  static const _organizationsKey = 'auth.organizations';
  static const _cartKey = 'cart.items';
  static const _fcmTokenKey = 'push.fcmToken';
  static const _languageKey = 'app.language';

  final SharedPreferences _prefs;

  static Future<SessionStore> open() async =>
      SessionStore(await SharedPreferences.getInstance());

  String? get token => _prefs.getString(_tokenKey);

  Future<void> saveToken(String? value) async {
    if (value == null || value.isEmpty) {
      await _prefs.remove(_tokenKey);
    } else {
      await _prefs.setString(_tokenKey, value);
    }
  }

  Map<String, dynamic>? get customer {
    final raw = _prefs.getString(_customerKey);
    if (raw == null || raw.isEmpty) return null;
    final decoded = jsonDecode(raw);
    return decoded is Map ? Map<String, dynamic>.from(decoded) : null;
  }

  Future<void> saveCustomer(Map<String, dynamic>? value) async {
    if (value == null) {
      await _prefs.remove(_customerKey);
      return;
    }
    await _prefs.setString(_customerKey, jsonEncode(value));
  }

  List<Map<String, dynamic>> get organizations {
    final raw = _prefs.getString(_organizationsKey);
    if (raw == null || raw.isEmpty) return const [];
    final decoded = jsonDecode(raw);
    if (decoded is! List) return const [];
    return decoded
        .whereType<Map>()
        .map((e) => Map<String, dynamic>.from(e))
        .toList(growable: false);
  }

  Future<void> saveOrganizations(List<Map<String, dynamic>> value) async =>
      _prefs.setString(_organizationsKey, jsonEncode(value));

  String? get cart => _prefs.getString(_cartKey);

  Future<void> saveCart(String value) async =>
      _prefs.setString(_cartKey, value);

  String? get fcmToken => _prefs.getString(_fcmTokenKey);

  Future<void> saveFcmToken(String value) async =>
      _prefs.setString(_fcmTokenKey, value);

  /// `ar` (default) or `en`.
  String get languageCode => _prefs.getString(_languageKey) ?? 'ar';

  Future<void> saveLanguageCode(String code) async =>
      _prefs.setString(_languageKey, code);

  Future<void> clearSession() async {
    await _prefs.remove(_tokenKey);
    await _prefs.remove(_customerKey);
    await _prefs.remove(_organizationsKey);
  }
}
