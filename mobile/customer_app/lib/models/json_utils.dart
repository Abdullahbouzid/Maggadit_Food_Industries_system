/// Shared parsing helpers for the Laravel JSON payloads.
library;

double asDouble(Object? value, [double fallback = 0]) {
  if (value == null) return fallback;
  if (value is num) return value.toDouble();
  final text = value.toString().replaceAll(',', '').trim();
  return double.tryParse(text) ?? fallback;
}

int asInt(Object? value, [int fallback = 0]) {
  if (value == null) return fallback;
  if (value is int) return value;
  if (value is num) return value.toInt();
  return int.tryParse(value.toString().replaceAll(',', '').trim()) ??
      double.tryParse(value.toString().replaceAll(',', '').trim())?.toInt() ??
      fallback;
}

bool asBool(Object? value, [bool fallback = false]) {
  if (value == null) return fallback;
  if (value is bool) return value;
  final text = value.toString().toLowerCase();
  if (text == 'true' || text == '1') return true;
  if (text == 'false' || text == '0') return false;
  return fallback;
}

String asString(Object? value, [String fallback = '']) =>
    value == null ? fallback : value.toString();

Map<String, dynamic> asMap(Object? value) {
  if (value is Map<String, dynamic>) return value;
  if (value is Map) return Map<String, dynamic>.from(value);
  return const {};
}

List<Map<String, dynamic>> asMapList(Object? value) {
  if (value is! List) return const [];
  return value
      .whereType<Object>()
      .map(asMap)
      .where((e) => e.isNotEmpty)
      .toList();
}

/// Resolves relative media paths coming from the API to absolute urls.
String? mediaUrl(String? path, {String? baseUrl}) {
  if (path == null) return null;
  final trimmed = path.trim();
  if (trimmed.isEmpty) return null;
  if (trimmed.startsWith('http://') || trimmed.startsWith('https://'))
    return trimmed;
  if (baseUrl == null || baseUrl.isEmpty) return trimmed;
  final root = baseUrl.replaceAll(RegExp(r'/api/?$'), '');
  // Every relative media path returned by the API is stored under /uploads.
  final relative = trimmed.replaceFirst(RegExp(r'^/+'), '');
  final needsUploads =
      !relative.startsWith('uploads/') &&
      !relative.startsWith('storage/') &&
      !relative.startsWith('public/');
  return '$root/${needsUploads ? 'uploads/' : ''}$relative';
}
