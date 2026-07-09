import 'package:flutter/material.dart';
import 'package:flutter_secure_storage/flutter_secure_storage.dart';
import 'theme.dart';

/// App-wide text-size accessibility control — mirrors the web app's a11y
/// panel (Normal/Large/Extra Large). A single ValueNotifier that main.dart
/// listens to and applies as a MediaQuery textScaler override, so every
/// screen picks it up automatically without each one wiring this itself.
class TextScaleController {
  static const _key = 'sevenvent_text_size';
  static const _storage = FlutterSecureStorage();

  static final ValueNotifier<AppTextSize> notifier = ValueNotifier(AppTextSize.normal);

  static Future<void> load() async {
    final saved = await _storage.read(key: _key);
    notifier.value = AppTextSize.values.firstWhere(
      (e) => e.name == saved,
      orElse: () => AppTextSize.normal,
    );
  }

  static Future<void> set(AppTextSize size) async {
    notifier.value = size;
    await _storage.write(key: _key, value: size.name);
  }
}
