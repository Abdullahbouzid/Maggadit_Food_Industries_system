import 'dart:io';

import 'package:flutter_test/flutter_test.dart';
import 'package:maggadit_customer/l10n/en_strings.dart';
import 'package:maggadit_customer/l10n/l10n.dart';

void main() {
  tearDown(() => L10n.languageCode = 'ar');

  test('falls back to Arabic while the language is Arabic', () {
    L10n.languageCode = 'ar';
    expect(L10n.tr('سلة التسوق'), 'سلة التسوق');
  });

  test('returns the English copy once the language is switched', () {
    L10n.languageCode = 'en';
    expect(L10n.tr('سلة التسوق'), 'Cart');
    expect(L10n.tr('مطلوب'), 'Required');
  });

  test('keeps unknown strings untouched instead of showing a blank label', () {
    L10n.languageCode = 'en';
    expect(L10n.tr('نص غير مترجم'), 'نص غير مترجم');
  });

  test('substitutes positional placeholders in both languages', () {
    L10n.languageCode = 'ar';
    expect(L10n.args('{0} أصناف', [3]), '3 أصناف');
    L10n.languageCode = 'en';
    expect(L10n.args('{0} أصناف', [3]), '3 items');
    expect(L10n.args('{0} أسئلة · ~{1} دقيقة', [5, 2]), '5 questions · ~2 min');
  });

  test('every Arabic string used by the UI has an English translation', () {
    final usage = RegExp(
      r"(?:tr\(\s*context\s*,|tArgs\(\s*context\s*,)\s*'([^']+)'",
    );
    final missing = <String>{};
    for (final file in Directory('lib').listSync(recursive: true)) {
      if (file is! File || !file.path.endsWith('.dart')) continue;
      if (file.path.contains('lib/l10n')) continue;
      for (final match in usage.allMatches(file.readAsStringSync())) {
        final key = match.group(1)!;
        if (!RegExp(r'[\u0600-\u06FF]').hasMatch(key)) continue;
        if (!enStrings.containsKey(key)) missing.add('$key (${file.path})');
      }
    }
    expect(missing, isEmpty, reason: 'untranslated: ${missing.join(', ')}');
  });
}
