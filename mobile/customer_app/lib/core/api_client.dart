import 'dart:io';

import 'package:dio/dio.dart';

import '../l10n/l10n.dart';
import 'config.dart';

/// Normalised error coming from the Laravel API.
class ApiException implements Exception {
  ApiException(this.message, {this.statusCode, this.errors});

  final String message;
  final int? statusCode;

  /// Laravel validation errors: {field: [messages]}
  final Map<String, dynamic>? errors;

  bool get isUnauthorized => statusCode == 401 || statusCode == 403;

  bool get isNetwork => statusCode == null;

  String? get firstFieldError {
    final values = errors?.values;
    if (values == null) return null;
    for (final value in values) {
      if (value is List && value.isNotEmpty) return value.first.toString();
    }
    return null;
  }

  @override
  String toString() => message;
}

/// Thin Dio wrapper: JSON in, JSON out, unified errors, bearer token.
class ApiClient {
  ApiClient() {
    _baseUrl = normalizeBaseUrl(AppConfig.defaultApiBaseUrl);
    _dio = Dio(
      BaseOptions(
        baseUrl: _baseUrl,
        connectTimeout: AppConfig.connectTimeout,
        receiveTimeout: AppConfig.receiveTimeout,
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      ),
    );
  }

  late final Dio _dio;
  late String _baseUrl;
  String? _token;

  /// Accepts `host`, `host/api` or `host/api/` and returns the canonical root
  /// so a hand-written `--dart-define` cannot produce `/api//customer/login`.
  static String normalizeBaseUrl(String value) {
    var normalised = value.trim().replaceAll(RegExp(r'/+$'), '');
    if (normalised.isEmpty) return normalised;
    if (!normalised.endsWith('/api')) normalised = '$normalised/api';
    return normalised;
  }

  String get baseUrl => _baseUrl;

  /// The value Dio actually resolves paths against; kept public for diagnostics.
  String get requestBaseUrl => _dio.options.baseUrl;

  /// Bare host used in error messages, e.g. `blue-beaver…hostingersite.com`.
  String get host => Uri.tryParse(_baseUrl)?.host ?? _baseUrl;

  String? get token => _token;

  set baseUrl(String value) {
    _baseUrl = normalizeBaseUrl(value);
    _dio.options.baseUrl = _baseUrl;
  }

  set token(String? value) {
    _token = value;
    if (value == null || value.isEmpty) {
      _dio.options.headers.remove('Authorization');
    } else {
      _dio.options.headers['Authorization'] = 'Bearer $value';
    }
  }

  Future<Map<String, dynamic>> get(
    String path, {
    Map<String, dynamic>? query,
  }) => _request(() => _dio.get<dynamic>(path, queryParameters: _clean(query)));

  Future<Map<String, dynamic>> post(String path, {Object? body}) =>
      _request(() => _dio.post<dynamic>(path, data: body));

  Future<Map<String, dynamic>> put(String path, {Object? body}) =>
      _request(() => _dio.put<dynamic>(path, data: body));

  Future<Map<String, dynamic>> delete(String path) =>
      _request(() => _dio.delete<dynamic>(path));

  /// Downloads a binary file (order / invoice / quotation pdf).
  Future<List<int>> download(String path, {Map<String, dynamic>? query}) async {
    try {
      final response = await _dio.get<List<int>>(
        path,
        queryParameters: _clean(query),
        options: Options(responseType: ResponseType.bytes),
      );
      return response.data ?? const [];
    } on DioException catch (e) {
      throw _mapError(e);
    }
  }

  Future<Map<String, dynamic>> _request(
    Future<Response<dynamic>> Function() call,
  ) async {
    try {
      final response = await call();
      final data = response.data;
      if (data is Map<String, dynamic>) return data;
      if (data is Map) return Map<String, dynamic>.from(data);
      return {'success': true, 'data': data};
    } on DioException catch (e) {
      throw _mapError(e);
    }
  }

  Map<String, dynamic>? _clean(Map<String, dynamic>? query) {
    if (query == null) return null;
    final result = <String, dynamic>{};
    query.forEach((key, value) {
      if (value == null) return;
      if (value is String && value.isEmpty) return;
      result[key] = value;
    });
    return result;
  }

  ApiException _mapError(DioException error) {
    final status = error.response?.statusCode;
    final data = error.response?.data;

    String message = L10n.args(
      'تعذّر الاتصال بالخادم ({0}). تحقّق من الإنترنت وحاول مرة أخرى.',
      [host],
    );
    Map<String, dynamic>? errors;

    if (data is Map) {
      final map = Map<String, dynamic>.from(data);
      message = L10n.tr((map['message'] ?? map['error'] ?? message).toString());
      if (map['errors'] is Map) {
        errors = Map<String, dynamic>.from(map['errors'] as Map);
        for (final value in errors.values) {
          if (value is List && value.isNotEmpty) {
            message = L10n.tr(value.first.toString());
            break;
          }
        }
      }
      if (message.trim().isEmpty) {
        message = L10n.args('حدث خطأ غير متوقع ({0}).', [status ?? '—']);
      }
    } else if (status == 401) {
      message = L10n.tr('انتهت الجلسة، يرجى تسجيل الدخول مرة أخرى.');
    } else if (status == 404) {
      message = L10n.tr('العنصر المطلوب غير موجود.');
    } else if (status != null && status >= 500) {
      message = L10n.tr('خطأ في الخادم، يرجى المحاولة لاحقاً.');
    } else if (status != null) {
      message = L10n.args('رفض الخادم الطلب ({0}).', [status]);
    } else if (error.type == DioExceptionType.badCertificate) {
      message = L10n.args('شهادة الخادم غير موثوقة ({0}).', [host]);
    } else if (error.type == DioExceptionType.connectionTimeout ||
        error.type == DioExceptionType.receiveTimeout ||
        error.type == DioExceptionType.sendTimeout) {
      message = L10n.args('انتهت مهلة الاتصال بالخادم ({0}).', [host]);
    } else if (error.error is SocketException) {
      message = L10n.args('لا يوجد اتصال بالشبكة ({0}).', [host]);
    }

    return ApiException(message, statusCode: status, errors: errors);
  }
}
