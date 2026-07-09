import 'package:flutter/material.dart';
import 'core/text_scale.dart';
import 'core/theme.dart';
import 'screens/splash_screen.dart';

// Full native flow: every module (Inventory, Locations, Suppliers, Purchase
// Orders, Alerts, Analytics, Reports, Users, Settings) now has its own
// JWT-backed API controller and native screen — see app_drawer.dart for the
// full module list. The app used to boot straight into WebViewScreen (a
// full-website WebView wrapper); that trivially matched "same functions as
// the website" since it WAS the website, but wasn't a real native app.
// WebViewScreen/webview_screen.dart is kept in lib/screens/ in case a
// WebView fallback is ever wanted again for a module that hasn't been
// natively built yet, but nothing currently routes to it.

void main() {
  TextScaleController.load(); // fire-and-forget; defaults to Normal until this resolves
  runApp(const SevenventApp());
}

class SevenventApp extends StatelessWidget {
  const SevenventApp({super.key});

  @override
  Widget build(BuildContext context) {
    return ValueListenableBuilder<AppTextSize>(
      valueListenable: TextScaleController.notifier,
      builder: (context, textSize, _) {
        return MaterialApp(
          title: '7NVENT',
          debugShowCheckedModeBanner: false,
          theme: AppTheme.dark,
          darkTheme: AppTheme.dark,
          themeMode: ThemeMode.dark,
          // Text-size accessibility override — same idea as the web app's
          // a11y panel toggling a body font-size class.
          builder: (context, child) {
            final mq = MediaQuery.of(context);
            return MediaQuery(
              data: mq.copyWith(textScaler: TextScaler.linear(textSize.scaleFactor)),
              child: child!,
            );
          },
          home: const SplashScreen(),
        );
      },
    );
  }
}
