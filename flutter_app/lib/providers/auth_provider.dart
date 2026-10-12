import 'package:flutter/material.dart';
import 'package:shared_preferences/shared_preferences.dart';

class AuthProvider extends ChangeNotifier {
  static const String _apiTokenKey = 'api_token';
  static const String _customerIdKey = 'customer_id';
  static const String _userNameKey = 'user_name';
  static const String _userEmailKey = 'user_email';

  String? _token;
  String? _customerId;
  String? _userName;
  String? _userEmail;
  bool _isLoading = false;

  // Getters
  String? get token => _token;
  String? get customerId => _customerId;
  String? get userName => _userName;
  String? get userEmail => _userEmail;
  bool get isLoggedIn => _token != null;
  bool get isLoading => _isLoading;

  // Initialize from local storage
  Future<void> init() async {
    final prefs = await SharedPreferences.getInstance();
    _token = prefs.getString(_apiTokenKey);
    _customerId = prefs.getString(_customerIdKey);
    _userName = prefs.getString(_userNameKey);
    _userEmail = prefs.getString(_userEmailKey);
    notifyListeners();
  }

  // Update token
  void setToken(String token) {
    _token = token;
    notifyListeners();
  }

  // Update customer ID
  void setCustomerId(String customerId) {
    _customerId = customerId;
    notifyListeners();
  }

  // Update user info
  void updateUserInfo(String name, String email, String username) {
    _userName = name;
    _userEmail = email;
    _token = username;
    notifyListeners();
  }

  // Login
  Future<bool> login(String username, String password) async {
    _isLoading = true;
    notifyListeners();

    try {
      // TODO: Call real API
      // final api = ApiService();
      // final user = await api.login(username, password);
      // if (user != null) {
      //   _token = user.apiToken;
      //   _customerId = user.id.toString();
      //   _userName = user.name;
      //   _userEmail = user.email;
      //   notifyListeners();
      //   return true;
      // }
      // Simulate API call for now
      await Future.delayed(Duration(seconds: 1));
      return false;
    } catch (e) {
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  // Register
  Future<bool> register(Map<String, dynamic> data) async {
    _isLoading = true;
    notifyListeners();

    try {
      // TODO: Call real API
      // final api = ApiService();
      // final success = await api.register(data);
      // await Future.delayed(Duration(seconds: 1));
      // return success;
      await Future.delayed(Duration(seconds: 1));
      return false;
    } catch (e) {
      return false;
    } finally {
      _isLoading = false;
      notifyListeners();
    }
  }

  // Logout
  Future<void> logout() async {
    try {
      final prefs = await SharedPreferences.getInstance();
      await prefs.clear();

      _token = null;
      _customerId = null;
      _userName = null;
      _userEmail = null;
      notifyListeners();
    } catch (e) {
      print('Logout error: $e');
    }
  }
}