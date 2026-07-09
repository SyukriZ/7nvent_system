import 'package:flutter/material.dart';

/// Brand palette + glass tokens lifted directly from the web app's CSS
/// (resources/views/layouts/app.php :root / [data-bs-theme="dark"], and
/// resources/views/auth/login.php) so the mobile app is visually the same
/// product, not a reskin. Dark theme only — the web app's dark mode is the
/// one built around the "liquid glass" look; light mode there is mostly flat
/// panels, so dark is the correct match for a glass-styled mobile app.
class AppColors {
  static const primary = Color(0xFF0096FF); // --accent-blue
  static const accentA = Color(0xFF38BDF8); // blob-2 / login gradient
  static const accentB = Color(0xFF0EA5E9); // blob-5 / login gradient
  static const accentIndigo = Color(0xFF6366F1); // chat gradient / brand glow

  static const bgDark = Color(0xFF0A0E1A); // login body bg
  static const bgPage = Color(0xFF0F1320); // [data-bs-theme=dark] --bg-page
  static const surfaceDark = Color(0xFF1B2030); // --bg-card (dark)
  static const cardDark = Color(0xFF242A3D); // --bg-subtle (dark)
  static const sidebarBg = Color(0xFF1A1A2E); // --sidebar-bg

  static const success = Color(0xFF22C55E);
  static const warning = Color(0xFFF59E0B);
  static const critical = Color(0xFFEF4444);

  static const textPrimary = Color(0xFFE8EAF0); // --text-primary (dark)
  static const textSecondary = Color(0xFFB8BDD0);
  static const textMuted = Color(0xFF8E94AB); // --text-muted (dark)
  static const textFaint = Color(0xFF6B7190); // --text-faint (dark)

  // ---- Glass tokens (dark) ----
  static const glassBg = Color(0x8C1B2030); // rgba(27,32,48,0.55)
  static const glassBgStrong = Color(0xB81B2030); // rgba(27,32,48,0.72)
  static const glassBorder = Color(0x1AFFFFFF); // rgba(255,255,255,0.10)
  static const glassHighlight = Color(0x2EFFFFFF); // rgba(255,255,255,0.18)
  static const glassShadow = Color(0x73000000); // rgba(0,0,0,0.45)
}

/// Text-size accessibility levels — mirrors the web app's a11y panel
/// (Normal 14px / Large 18px / Extra Large 22px baseline).
enum AppTextSize { normal, large, xlarge }

extension AppTextSizeScale on AppTextSize {
  double get scaleFactor {
    switch (this) {
      case AppTextSize.normal:
        return 1.0;
      case AppTextSize.large:
        return 18 / 14;
      case AppTextSize.xlarge:
        return 22 / 14;
    }
  }

  String get label {
    switch (this) {
      case AppTextSize.normal:
        return 'Normal';
      case AppTextSize.large:
        return 'Large';
      case AppTextSize.xlarge:
        return 'Extra Large';
    }
  }
}

class AppTheme {
  static ThemeData get dark {
    final base = ThemeData.dark(useMaterial3: true);
    return base.copyWith(
      scaffoldBackgroundColor: AppColors.bgPage,
      colorScheme: base.colorScheme.copyWith(
        primary: AppColors.primary,
        secondary: AppColors.accentA,
        surface: AppColors.surfaceDark,
        error: AppColors.critical,
      ),
      appBarTheme: const AppBarTheme(
        backgroundColor: Colors.transparent,
        elevation: 0,
        foregroundColor: AppColors.textPrimary,
      ),
      cardTheme: CardThemeData(
        color: AppColors.glassBgStrong,
        elevation: 0,
        shape: RoundedRectangleBorder(
          borderRadius: BorderRadius.circular(16),
          side: const BorderSide(color: AppColors.glassBorder),
        ),
        margin: const EdgeInsets.symmetric(vertical: 6),
      ),
      inputDecorationTheme: InputDecorationTheme(
        filled: true,
        fillColor: AppColors.surfaceDark,
        border: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: BorderSide.none,
        ),
        focusedBorder: OutlineInputBorder(
          borderRadius: BorderRadius.circular(12),
          borderSide: const BorderSide(color: AppColors.primary, width: 1.5),
        ),
        labelStyle: const TextStyle(color: AppColors.textMuted),
        contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
      ),
      elevatedButtonTheme: ElevatedButtonThemeData(
        style: ElevatedButton.styleFrom(
          backgroundColor: AppColors.primary,
          foregroundColor: Colors.white,
          padding: const EdgeInsets.symmetric(vertical: 16),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
        ),
      ),
      textTheme: base.textTheme.apply(
        bodyColor: AppColors.textPrimary,
        displayColor: AppColors.textPrimary,
      ),
    );
  }
}
