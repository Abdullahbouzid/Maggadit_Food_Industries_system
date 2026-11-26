import '../core/api_client.dart';
import '../models/account.dart';
import '../models/json_utils.dart';

class AuthResult {
  AuthResult({
    required this.token,
    required this.customer,
    required this.organizations,
  });

  factory AuthResult.fromJson(Map<String, dynamic> json) => AuthResult(
        token: asString(json['token']),
        customer: Customer.fromJson(asMap(json['customer'])),
        organizations: asMapList(json['organizations']).map(Organization.fromJson).toList(),
      );

  final String token;
  final Customer customer;
  final List<Organization> organizations;
}

class AuthService {
  AuthService(this._api);

  static const _prefix = '/customer';
  final ApiClient _api;

  Future<AuthResult> login({
    required String username,
    required String password,
    String? fcmToken,
  }) async {
    final json = await _api.post('$_prefix/login', body: {
      'username': username,
      'password': password,
      if (fcmToken != null && fcmToken.isNotEmpty) 'fcm_token': fcmToken,
      'device_name': 'mobile',
    });
    return AuthResult.fromJson(json);
  }

  Future<AuthResult> register({
    required String name,
    required String username,
    required String email,
    required String password,
    required String passwordConfirmation,
    String? fcmToken,
  }) async {
    final json = await _api.post('$_prefix/register', body: {
      'name': name,
      'username': username,
      'email': email,
      'password': password,
      'password_confirmation': passwordConfirmation,
      if (fcmToken != null && fcmToken.isNotEmpty) 'fcm_token': fcmToken,
    });
    return AuthResult.fromJson(json);
  }

  Future<void> logout() async {
    try {
      await _api.post('$_prefix/logout');
    } on ApiException {
      // Token may already be revoked on the server; local logout still proceeds.
    }
  }

  Future<Customer> profile() async {
    final json = await _api.get('$_prefix/profile');
    final payload = asMap(json['customer']).isEmpty ? json : asMap(json['customer']);
    return Customer.fromJson(payload);
  }

  Future<Customer> updateProfile({
    required String name,
    required String email,
    String? currentPassword,
    String? newPassword,
    String? newPasswordConfirmation,
  }) async {
    final json = await _api.post('$_prefix/profile/update', body: {
      'name': name,
      'email': email,
      if (currentPassword != null && currentPassword.isNotEmpty) ...{
        'current_password': currentPassword,
        'password': newPassword,
        'password_confirmation': newPasswordConfirmation,
      },
    });
    final payload = asMap(json['customer']).isEmpty ? json : asMap(json['customer']);
    return Customer.fromJson(payload);
  }

  Future<List<Organization>> organizations() async {
    final json = await _api.get('$_prefix/organizations');
    return asMapList(json['organizations']).map(Organization.fromJson).toList();
  }

  Future<void> switchOrganization(int organizationId) async {
    await _api.post('$_prefix/change-organization', body: {'organization_id': organizationId});
  }

  Future<void> updateFcmToken(String token) async {
    await _api.post('$_prefix/fcm-token', body: {'fcm_token': token});
  }
}
