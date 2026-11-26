import 'package:flutter/material.dart';

/// Brand tokens (mirrors the Laravel dashboard `nova` purple palette).
class C {
  const C._();

  static const primary = Color(0xFF9879B6);
  static const primaryDark = Color(0xFF6B4C8A);
  static const primaryDeep = Color(0xFF4B3168);
  static const purpleSoft = Color(0xFFF4EFF9);
  static const purpleSoft2 = Color(0xFFEADFF5);

  static const bg = Color(0xFFF6F7FB);
  static const surface = Colors.white;
  static const border = Color(0xFFE8ECF3);
  static const ink = Color(0xFF1E293B);
  static const inkSoft = Color(0xFF475569);
  static const muted = Color(0xFF8A94A6);

  static const success = Color(0xFF16A34A);
  static const successSoft = Color(0xFFE7F7EE);
  static const warning = Color(0xFFD97706);
  static const warningSoft = Color(0xFFFDF4E3);
  static const danger = Color(0xFFDC2626);
  static const dangerSoft = Color(0xFFFDECEC);
  static const info = Color(0xFF2563EB);
  static const infoSoft = Color(0xFFEAF1FE);

  static const amber = Color(0xFFF59E0B);
  static const teal = Color(0xFF14B8A6);
  static const rose = Color(0xFFF43F5E);
  static const indigo = Color(0xFF6366F1);
}

class Sh {
  const Sh._();

  static const card = [
    BoxShadow(color: Color(0x0F1E293B), blurRadius: 18, offset: Offset(0, 8)),
  ];
  static const soft = [
    BoxShadow(color: Color(0x0A1E293B), blurRadius: 10, offset: Offset(0, 4)),
  ];
  static const nav = [
    BoxShadow(color: Color(0x1A1E293B), blurRadius: 26, offset: Offset(0, 10)),
  ];
}

/// Text style helper: always the bundled Arabic family.
TextStyle ts(
  double size, {
  FontWeight weight = FontWeight.w500,
  Color color = C.ink,
  double height = 1.45,
  TextDecoration? decoration,
  double letterSpacing = 0,
}) {
  return TextStyle(
    fontFamily: 'AppFont',
    fontSize: size,
    fontWeight: weight,
    color: color,
    height: height,
    decoration: decoration,
    letterSpacing: letterSpacing,
  );
}

ThemeData buildTheme() {
  final base = ThemeData.light(useMaterial3: true);
  return base.copyWith(
    scaffoldBackgroundColor: C.bg,
    colorScheme: base.colorScheme.copyWith(
      primary: C.primary,
      secondary: C.primaryDark,
      surface: C.surface,
      error: C.danger,
    ),
    textTheme: base.textTheme.apply(
      fontFamily: 'AppFont',
      bodyColor: C.ink,
      displayColor: C.ink,
    ),
    appBarTheme: AppBarTheme(
      backgroundColor: C.surface,
      foregroundColor: C.ink,
      elevation: 0,
      centerTitle: true,
      titleTextStyle: ts(15, weight: FontWeight.w700),
    ),
    progressIndicatorTheme: const ProgressIndicatorThemeData(color: C.primary),
    snackBarTheme: SnackBarThemeData(
      backgroundColor: C.ink,
      contentTextStyle: ts(12, color: Colors.white),
      behavior: SnackBarBehavior.floating,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
    ),
    inputDecorationTheme: InputDecorationTheme(
      filled: true,
      fillColor: const Color(0xFFF8FAFC),
      hintStyle: ts(11.5, color: C.muted),
      contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(14),
        borderSide: const BorderSide(color: C.border),
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(14),
        borderSide: const BorderSide(color: C.border),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(14),
        borderSide: const BorderSide(color: C.primary, width: 1.4),
      ),
      errorBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(14),
        borderSide: const BorderSide(color: C.danger),
      ),
    ),
    dividerTheme: const DividerThemeData(
      color: C.border,
      thickness: 1,
      space: 1,
    ),
  );
}
