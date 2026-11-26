import 'package:flutter/foundation.dart';

import '../core/api_client.dart';
import '../core/formatters.dart';
import '../core/session_store.dart';
import '../models/account.dart';

enum AuthStatus { unknown, anonymous, authenticated }

class AuthController extends ChangeNotifier {
  AuthController({
    required ApiClient api,
    required SessionStore store,
    this.onSessionChanged,
  })  : _api = api,
        _store = store;

  final ApiClient _api;
  final SessionStore _store;

  /// Called after login/logout so cart, favorites and notifications can refresh.
  final VoidCallback? onSessionChanged;

  AuthStatus _status = AuthStatus.unknown;
  Customer? _customer;
  List<Organization> _organizations = const [];
  String? _fcmToken;
  String? _error;

  AuthStatus get status => _status;
  Customer? get customer => _customer;
  List<Organization> get organizations => _organizations;
  String? get fcmToken => _fcmToken;
  String? get error => _error;
  bool get isAuthenticated => _status == AuthStatus.authenticated;
  String get baseUrl => _api.baseUrl;

  Organization? get activeOrganization {
    for (final org in _organizations) {
      if (org.isActive) return org;
    }
    return _organizations.isEmpty ? null : _organizations.first;
  }

  /// Restores token/customer from disk and validates it against the server.
  Future<void> bootstrap() async {
    _api.baseUrl = _store.baseUrl;
    _fcmToken = _store.fcmToken;

    final token = _store.token;
    final cached = _store.customer;

    if (token == null || token.isEmpty) {
      _status = AuthStatus.anonymous;
      notifyListeners();
      return;
    }

    _api.token = token;
    if (cached != null) {
      _customer = Customer.fromJson(cached);
      _organizations = _store.organizations.map(Organization.fromJson).toList();
      _status = AuthStatus.authenticated;
      notifyListeners();
    }

    try {
      final fresh = await _api.get('/customer/profile');
      final payload = fresh['customer'] is Map ? fresh['customer'] as Map : fresh;
      _customer = Customer.fromJson(Map<String, dynamic>.from(payload));
      await _store.saveCustomer(_customer!.toJson());
      _status = AuthStatus.authenticated;
      if (_fcmToken != null) await _pushTokenToServer(_fcmToken!);
    } on ApiException catch (e) {
      if (e.isUnauthorized) {
        await _clearSession();
      } else if (_status != AuthStatus.authenticated) {
        _status = AuthStatus.anonymous;
      }
    } catch (_) {
      if (_status != AuthStatus.authenticated) _status = AuthStatus.anonymous;
    }
    notifyListeners();
  }

  Future<bool> login({required String username, required String password}) async {
    _error = null;
    notifyListeners();
    try {
      final token = _fcmToken ?? await _resolveFcmToken();
      final result = await _api.post('/customer/login', body: {
        'username': username,
        'password': password,
        if (token != null && token.isNotEmpty) 'fcm_token': token,
        'device_name': 'mobile',
      });
      await _applySession(result);
      return true;
    } on ApiException catch (e) {
      _error = e.firstFieldError ?? e.message;
      _status = AuthStatus.anonymous;
      notifyListeners();
      return false;
    }
  }

  Future<bool> register({
    required String name,
    required String username,
    required String email,
    required String password,
    required String passwordConfirmation,
  }) async {
    _error = null;
    notifyListeners();
    try {
      final token = _fcmToken ?? await _resolveFcmToken();
      final result = await _api.post('/customer/register', body: {
        'name': name,
        'username': username,
        'email': email,
        'password': password,
        'password_confirmation': passwordConfirmation,
        if (token != null && token.isNotEmpty) 'fcm_token': token,
      });
      await _applySession(result);
      return true;
    } on ApiException catch (e) {
      _error = e.firstFieldError ?? e.message;
      notifyListeners();
      return false;
    }
  }

  Future<void> logout() async {
    try {
      await _api.post('/customer/logout');
    } on ApiException {
      // Ignore network failures: local session is cleared either way.
    }
    await _clearSession();
  }

  Future<bool> updateProfile({
    required String name,
    required String email,
    String? username,
    String? oldPassword,
    String? newPassword,
    String? newPasswordConfirmation,
  }) async {
    try {
      final json = await _api.post('/customer/profile/update', body: {
        'name': name,
        'email': email,
        'username': username ?? _customer?.username ?? '',
        if (oldPassword != null && oldPassword.isNotEmpty) ...{
          'old_password': oldPassword,
          'password': newPassword,
          'password_confirmation': newPasswordConfirmation,
        },
      });
      final payload = json['customer'] is Map ? json['customer'] as Map : json;
      _customer = Customer.fromJson(Map<String, dynamic>.from(payload));
      await _store.saveCustomer(_customer!.toJson());
      notifyListeners();
      return true;
    } on ApiException catch (e) {
      _error = e.firstFieldError ?? e.message;
      notifyListeners();
      return false;
    }
  }

  Future<bool> switchOrganization(int organizationId) async {
    try {
      await _api.post('/customer/change-organization', body: {'organization_id': organizationId});
      _organizations = await _loadOrganizations();
      final active = _organizations.where((o) => o.isActive);
      if (active.isNotEmpty) {
        _customer = Customer(
          id: _customer!.id,
          name: _customer!.name,
          username: _customer!.username,
          email: _customer!.email,
          activeOrganizationId: active.first.id,
        );
        await _store.saveCustomer(_customer!.toJson());
      }
      notifyListeners();
      return true;
    } on ApiException catch (e) {
      _error = e.message;
      notifyListeners();
      return false;
    }
  }

  Future<void> loadOrganizations() async {
    _organizations = await _loadOrganizations();
    notifyListeners();
  }

  /// Called by the push service whenever FCM rotates the device token.
  Future<void> setFcmToken(String token) async {
    _fcmToken = token;
    await _store.saveFcmToken(token);
    if (isAuthenticated) await _pushTokenToServer(token);
  }

  Future<void> changeServer(String baseUrl) async {
    _api.baseUrl = baseUrl;
    await _store.saveBaseUrl(_api.baseUrl);
    notifyListeners();
  }

  Future<List<Organization>> _loadOrganizations() async {
    try {
      final json = await _api.get('/customer/organizations');
      final raw = json['organizations'];
      if (raw is! List) return _organizations;
      final list = raw
          .whereType<Map>()
          .map((e) => Organization.fromJson(Map<String, dynamic>.from(e)))
          .toList();
      await _store.saveOrganizations(list.map((o) => _orgToJson(o)).toList());
      return list;
    } on ApiException {
      return _organizations;
    }
  }

  Map<String, dynamic> _orgToJson(Organization org) => {
        'id': org.id,
        'name': org.name,
        'code': org.code,
        'is_active': org.isActive,
        'organization_type': org.type,
        'parent_id': org.parentId,
      };

  Future<void> _applySession(Map<String, dynamic> json) async {
    final token = (json['token'] ?? '').toString();
    _api.token = token;
    await _store.saveToken(token);

    final customerJson = json['customer'];
    if (customerJson is Map) {
      _customer = Customer.fromJson(Map<String, dynamic>.from(customerJson));
      await _store.saveCustomer(_customer!.toJson());
    }

    final orgs = json['organizations'];
    if (orgs is List) {
      _organizations = orgs
          .whereType<Map>()
          .map((e) => Organization.fromJson(Map<String, dynamic>.from(e)))
          .toList();
      await _store.saveOrganizations(_organizations.map(_orgToJson).toList());
    } else {
      _organizations = await _loadOrganizations();
    }

    _status = AuthStatus.authenticated;
    _error = null;
    notifyListeners();
    onSessionChanged?.call();

    final fcm = _fcmToken;
    if (fcm != null && fcm.isNotEmpty) await _pushTokenToServer(fcm);
  }

  Future<void> _pushTokenToServer(String token) async {
    try {
      await _api.post('/customer/fcm-token', body: {'fcm_token': token});
    } on ApiException {
      // Silent: the token is retried on the next login / app start.
    }
  }

  /// Pulls the FCM token lazily (set by the push service once Firebase is ready).
  Future<String?> _resolveFcmToken() async => _fcmToken ?? _store.fcmToken;

  Future<void> _clearSession() async {
    _api.token = null;
    _customer = null;
    _organizations = const [];
    _status = AuthStatus.anonymous;
    await _store.clearSession();
    notifyListeners();
    onSessionChanged?.call();
  }

  String get displayName => _customer?.name ?? 'زائر';

  String get initials => Fmt.initials(_customer?.name);

}
