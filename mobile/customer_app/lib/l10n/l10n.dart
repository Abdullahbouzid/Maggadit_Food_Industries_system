import 'package:flutter/widgets.dart';

import 'en_strings.dart';

/// Arabic/English dictionary lookup.
///
/// The Arabic text itself is the key, so a missing translation degrades to the
/// Arabic copy instead of showing an empty label.
class L10n {
  const L10n._();

  /// Language currently rendered by the app; kept in sync by `LocaleController`.
  static String languageCode = 'ar';

  static bool get isEnglish => languageCode == 'en';

  static String tr(String arabic) =>
      isEnglish ? (enStrings[arabic] ?? arabic) : arabic;

  /// Translates `arabic` and substitutes `{0}`, `{1}`, … with `values`.
  static String args(String arabic, List<Object?> values) {
    var text = tr(arabic);
    for (var i = 0; i < values.length; i++) {
      text = text.replaceAll('{$i}', '${values[i]}');
    }
    return text;
  }
}

/// Translates a UI string and subscribes the calling widget to locale changes.
String tr(BuildContext context, String arabic) {
  Localizations.localeOf(context);
  return L10n.tr(arabic);
}

/// Translated template with positional arguments, e.g.
/// `tArgs(context, 'رقم الطلب: {0}', [number])`.
String tArgs(BuildContext context, String arabic, List<Object?> values) {
  Localizations.localeOf(context);
  return L10n.args(arabic, values);
}
