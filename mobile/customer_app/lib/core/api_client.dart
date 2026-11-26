import 'dart:io';

import 'package:dio/dio.dart';
import 'package:dio/io.dart';

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
    _dio = Dio(
      BaseOptions(
        connectTimeout: AppConfig.connectTimeout,
        receiveTimeout: AppConfig.receiveTimeout,
        headers: {
          'Accept': 'application/json',
          'X-Requested-With': 'XMLHttpRequest',
        },
      ),
    );
    // Accept all SSL certificates (for development/testing with self-signed certs)
    _dio.httpClientAdapter = IOHttpClientAdapter(
      createHttpClient: () {
        final client = HttpClient();
        client.badCertificateCallback = (cert, host, port) => true;
        return client;
      },
    );
  }

  late final Dio _dio;
  String _baseUrl = AppConfig.defaultApiBaseUrl;
  String? _token;

  String get baseUrl => _baseUrl;

  String? get token => _token;

  set baseUrl(String value) {
    final normalised = value.trim().replaceAll(RegExp(r'/+$'), '');
    if (normalised == _baseUrl) return;
    _baseUrl = normalised;
    _dio.options.baseUrl = normalised;
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

    String message = L10n.tr(
      'تعذّر الاتصال بالخادم، تحقّق من الإنترنت وحاول مرة أخرى.',
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
    } else if (error.type == DioExceptionType.connectionTimeout ||
        error.type == DioExceptionType.receiveTimeout ||
        error.type == DioExceptionType.sendTimeout) {
      message = L10n.tr('انتهت مهلة الاتصال بالخادم.');
    } else if (error.error is SocketException) {
      message = L10n.tr('لا يوجد اتصال بالشبكة.');
    }

    return ApiException(message, statusCode: status, errors: errors);
  }
}
