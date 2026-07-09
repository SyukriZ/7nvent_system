import 'package:flutter_secure_storage/flutter_secure_storage.dart';

/// Thin wrapper around secure storage so the rest of the app never touches
/// the storage API directly — keeps the JWT out of SharedPreferences/plain
/// files, which is where it'd otherwise land.
class TokenStorage {
  static const _key = 'sevenvent_jwt';
  static const _storage = FlutterSecureStorage();

  static Future<void> save(String token) => _storage.write(key: _key, value: token);
  static Future<String?> read() => _storage.read(key: _key);
  static Future<void> clear() => _storage.delete(key: _key);
}
