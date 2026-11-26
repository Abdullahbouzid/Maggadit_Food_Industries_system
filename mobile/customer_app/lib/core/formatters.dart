import 'package:intl/intl.dart';

/// Formatting helpers shared by every screen (Arabic friendly, latin digits).
class Fmt {
  const Fmt._();

  static final NumberFormat _moneyFormat = NumberFormat('#,##0.00', 'en');
  static final NumberFormat _integerFormat = NumberFormat('#,##0', 'en');
  static final DateFormat _apiDateFormat = DateFormat('yyyy-MM-dd');

  static const months = <String>[
    'يناير', 'فبراير', 'مارس', 'أبريل', 'مايو', 'يونيو',
    'يوليو', 'أغسطس', 'سبتمبر', 'أكتوبر', 'نوفمبر', 'ديسمبر',
  ];

  static String money(num? value) => _moneyFormat.format(value ?? 0);

  static String price(num? value) => '${money(value)} ر.س';

  static String integer(num? value) => _integerFormat.format(value ?? 0);

  static String quantity(num? value) {
    if (value == null) return '0';
    if (value == value.roundToDouble()) return _integerFormat.format(value);
    return value
        .toStringAsFixed(3)
        .replaceAll(RegExp(r'0+$'), '')
        .replaceAll(RegExp(r'\.$'), '');
  }

  /// "12 نوفمبر 2026"
  static String date(Object? value) {
    final parsed = _parse(value);
    if (parsed == null) return _raw(value);
    return '${parsed.day} ${months[parsed.month - 1]} ${parsed.year}';
  }

  /// "12 نوفمبر"
  static String dayMonth(Object? value) {
    final parsed = _parse(value);
    if (parsed == null) return _raw(value);
    return '${parsed.day} ${months[parsed.month - 1]}';
  }

  /// "12 نوفمبر 2026 · 09:15"
  static String dateTime(Object? value) {
    final parsed = _parse(value);
    if (parsed == null) return _raw(value);
    final hour = parsed.hour.toString().padLeft(2, '0');
    final minute = parsed.minute.toString().padLeft(2, '0');
    return '${parsed.day} ${months[parsed.month - 1]} ${parsed.year} · $hour:$minute';
  }

  static String apiDate(DateTime date) => _apiDateFormat.format(date);

  static String initials(String? name) {
    final trimmed = (name ?? '').trim();
    if (trimmed.isEmpty) return '؟';
    final parts = trimmed.split(RegExp(r'\s+'));
    if (parts.length == 1) return parts.first.substring(0, 1);
    return parts.first.substring(0, 1) + parts[1].substring(0, 1);
  }

  static String percent(num? value) => '${(value ?? 0).round()}%';

  static String _raw(Object? value) => (value ?? '').toString();

  static DateTime? _parse(Object? value) {
    if (value == null) return null;
    if (value is DateTime) return value;
    var text = value.toString().trim();
    if (text.isEmpty) return null;
    // Laravel's order history uses "2026/11/12"; normalize before parsing.
    if (RegExp(r'^\d{4}/\d{1,2}/\d{1,2}').hasMatch(text)) {
      text = text.replaceFirst('/', '-').replaceFirst('/', '-');
    }
    return DateTime.tryParse(text.contains(' ') ? text.replaceFirst(' ', 'T') : text);
  }
}
