import 'package:flutter/material.dart';

import '../core/session_store.dart';
import '../l10n/l10n.dart';

/// Holds the UI language (Arabic by default) and persists the user's choice.
class LocaleController extends ChangeNotifier {
  LocaleController(this._store) {
    final code = _store.languageCode;
    _locale = Locale(code);
    L10n.languageCode = code;
  }

  static const supported = <Locale>[Locale('ar'), Locale('en')];

  final SessionStore _store;
  late Locale _locale;

  Locale get locale => _locale;
  bool get isArabic => _locale.languageCode == 'ar';
  TextDirection get direction =>
      isArabic ? TextDirection.rtl : TextDirection.ltr;

  Future<void> setLanguage(String code) async {
    if (code == _locale.languageCode) return;
    _locale = Locale(code);
    L10n.languageCode = code;
    notifyListeners();
    await _store.saveLanguageCode(code);
  }

  Future<void> toggle() => setLanguage(isArabic ? 'en' : 'ar');
}
