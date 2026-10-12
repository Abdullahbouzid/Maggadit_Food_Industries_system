import 'dart:convert';
import 'package:http/http.dart' as http;
import 'package:shared_preferences/shared_preferences.dart';
import '../models/user_model.dart';
import '../models/order_model.dart';
import '../models/notification_model.dart';

class ApiService {
  static const String _baseUrl = 'https://blue-beaver-923812.hostingersite.com/api';

  Future<Map<String, String>> _getHeaders() async {
    final SharedPreferences prefs = await SharedPreferences.getInstance();
    final String? token = prefs.getString('api_token');

    final Map<String, String> headers = {
      'Content-Type': 'application/json',
      'Accept': 'application/json',
      'X-Requested-With': 'XMLHttpRequest',
      'X-CSRF-TOKEN': await _getCsrfToken(),
    };
    if (token != null) {
      headers['Authorization'] = 'Bearer $token';
    }
    return headers;
  }

  Future<String> _getCsrfToken() async {
    // Try to get from shared prefs or make a request to get it
    final SharedPreferences prefs = await SharedPreferences.getInstance();
    return prefs.getString('csrf_token') ?? '';
  }

  Future<UserModel?> login(String username, String password) async {
    try {
      final response = await http.post(
        Uri.parse('$_baseUrl/customer/login'),
        headers: await _getHeaders(),
        body: jsonEncode({
          'username': username,
          'password': password,
        }),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        final userData = data['customer'];
        final token = data['token'];

        final prefs = await SharedPreferences.getInstance();
        await prefs.setString('api_token', token);
        await prefs.setString('customer_id', userData['id'].toString());
        await prefs.setString('user_name', userData['name']);
        await prefs.setString('user_email', userData['email']);
        await prefs.setString('user_username', userData['username']);

        return UserModel.fromJson({
          ...userData,
          'api_token': token,
        });
      }
      return null;
    } catch (e) {
      return null;
    }
  }

  Future<bool> register(Map<String, dynamic> userData) async {
    try {
      final response = await http.post(
        Uri.parse('$_baseUrl/customer/register'),
        headers: await _getHeaders(),
        body: jsonEncode(userData),
      );

      return response.statusCode == 200 || response.statusCode == 201;
    } catch (e) {
      return false;
    }
  }

  Future<List<OrderModel>> getOrderHistory({int page = 1}) async {
    try {
      final response = await http.get(
        Uri.parse('$_baseUrl/customer/order-history?page=$page'),
        headers: await _getHeaders(),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        final List<dynamic> ordersData = data['data']['rows'] ?? [];
        return ordersData
            .map((json) => OrderModel.fromJson(json))
            .toList();
      }
      return [];
    } catch (e) {
      return [];
    }
  }

  Future<OrderModel?> getOrderDetails(int orderId) async {
    try {
      final response = await http.get(
        Uri.parse('$_baseUrl/customer/orders/$orderId'),
        headers: await _getHeaders(),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        final orderData = data['data'] ?? {};
        return OrderModel.fromJson(orderData);
      }
      return null;
    } catch (e) {
      return null;
    }
  }

  Future<List<NotificationModel>> getNotifications({int page = 1}) async {
    try {
      final response = await http.get(
        Uri.parse('$_baseUrl/customer/notifications?page=$page'),
        headers: await _getHeaders(),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        final List<dynamic> notificationsData = data['data'] ?? [];
        return notificationsData
            .map((json) => NotificationModel.fromJson(json))
            .toList();
      }
      return [];
    } catch (e) {
      return [];
    }
  }

  Future<Map<String, dynamic>> checkNewNotifications(int lastId) async {
    try {
      final response = await http.post(
        Uri.parse('$_baseUrl/customer/notifications/check_new'),
        headers: await _getHeaders(),
        body: jsonEncode({'last_id': lastId}),
      );

      if (response.statusCode == 200) {
        final data = jsonDecode(response.body);
        return data;
      }
      return {'success': false};
    } catch (e) {
      return {'success': false};
    }
  }

  Future<void> logout() async {
    final SharedPreferences prefs = await SharedPreferences.getInstance();
    await prefs.clear();
  }

  Future<Map<String, dynamic>> get(String endpoint) async {
    try {
      final response = await http.get(
        Uri.parse('$_baseUrl$endpoint'),
        headers: await _getHeaders(),
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      }
      return {'success': false, 'data': []};
    } catch (e) {
      return {'success': false, 'data': []};
    }
  }

  Future<Map<String, dynamic>> post(String endpoint, {Map<String, dynamic>? body}) async {
    try {
      final response = await http.post(
        Uri.parse('$_baseUrl$endpoint'),
        headers: await _getHeaders(),
        body: body != null ? jsonEncode(body) : null,
      );

      if (response.statusCode == 200) {
        return jsonDecode(response.body);
      }
      return {'success': false};
    } catch (e) {
      return {'success': false};
    }
  }
}
