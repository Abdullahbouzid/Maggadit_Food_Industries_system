import 'package:flutter/material.dart';

const Color novaPurple = Color(0xFF6B4BA7);
const Color novaGreen = Color(0xFF20C997);
const Color novaBlue = Color(0xFF0D6EFD);
const Color novaYellow = Color(0xFFFFC107);
const Color novaRed = Color(0xFFDC3545);
const Color novaDark = Color(0xFF212529);
const Color novaLight = Color(0xFFF8F9FA);
const Color novaGray = Color(0xFF6C757D);
const Color novaGrayLight = Color(0xFFE9ECEF);
const Color novaGrayDark = Color(0xFF343A40);

ThemeData appTheme({Brightness brightness = Brightness.light}) {
  final bool isDark = brightness == Brightness.dark;

  return ThemeData(
    brightness: brightness,
    colorScheme: ColorScheme.fromSeed(
      seedColor: novaPurple,
      primary: novaPurple,
      secondary: novaGreen,
      surface: isDark ? novaDark : Colors.white,
      onSurface: isDark ? Colors.white : novaDark,
    ),
    scaffoldBackgroundColor: isDark ? novaDark : Colors.white,
    appBarTheme: AppBarTheme(
      backgroundColor: isDark ? novaDark : novaPurple,
      foregroundColor: isDark ? Colors.white : Colors.white,
      elevation: 0,
      centerTitle: false,
    ),
    bottomNavigationBarTheme: BottomNavigationBarThemeData(
      backgroundColor: isDark ? novaDark : Colors.white,
      selectedItemColor: novaPurple,
      unselectedItemColor: novaGray,
      elevation: 8,
      type: BottomNavigationBarType.shifting,
    ),
    cardTheme: CardThemeData(
      color: isDark ? novaDark : Colors.white,
      elevation: 2,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
    ),
    elevatedButtonTheme: ElevatedButtonThemeData(
      style: ElevatedButton.styleFrom(
        backgroundColor: novaPurple,
        foregroundColor: Colors.white,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
        padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 12),
      ),
    ),
    textButtonTheme: TextButtonThemeData(
      style: TextButton.styleFrom(
        foregroundColor: novaPurple,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(8)),
      ),
    ),
    inputDecorationTheme: InputDecorationTheme(
      border: OutlineInputBorder(
        borderRadius: BorderRadius.circular(8),
        borderSide: BorderSide(color: novaGrayLight),
      ),
      enabledBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(8),
        borderSide: BorderSide(color: novaGrayLight),
      ),
      focusedBorder: OutlineInputBorder(
        borderRadius: BorderRadius.circular(8),
        borderSide: BorderSide(color: novaPurple, width: 2),
      ),
      filled: true,
      fillColor: isDark ? novaGrayDark.withOpacity(0.3) : novaGrayLight,
    ),
  );
}
