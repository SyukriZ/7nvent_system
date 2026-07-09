import 'dart:convert';
import 'package:http/http.dart' as http;
import 'token_storage.dart';

/// Talks to the /api/* endpoints added to the 7nvent PHP backend
/// (app/Http/Controllers/Api/*). Mirrors the same JWT-bearer contract:
/// login() gets a token, everything else sends it as Authorization: Bearer.
///
/// IMPORTANT — set baseUrl for your setup:
///   - Android emulator -> host machine's XAMPP: use 10.0.2.2
///   - iOS simulator     -> host machine's XAMPP: localhost works
///   - Real physical device -> your PC's LAN IP (e.g. 192.168.x.x),
///     phone and PC must be on the same Wi-Fi, and Windows Firewall must
///     allow inbound on port 80 for Apache.
class ApiClient {
  // Set to the PC's LAN IP (from `ipconfig`, Wi-Fi adapter) so a phone on
  // the same Wi-Fi network can reach XAMPP on this machine. If you switch
  // back to Chrome/web on this same PC, 'http://localhost/7nvent/public/api'
  // also works. Android emulator (not a real phone) would use 10.0.2.2.
  static const String baseUrl = 'http://192.168.0.15/7nvent/public/api';

  static Future<Map<String, String>> _authHeaders() async {
    final token = await TokenStorage.read();
    return {
      'Content-Type': 'application/json',
      if (token != null) 'Authorization': 'Bearer $token',
    };
  }

  /// GET /api/public/stats — no bearer token, safe to call before login.
  static Future<ApiResult> publicStats() async {
    final res = await http.get(Uri.parse('$baseUrl/public/stats'));
    return ApiResult(res.statusCode, jsonDecode(res.body));
  }

  static Future<ApiResult> login(String username, String password) async {
    final res = await http.post(
      Uri.parse('$baseUrl/auth/login'),
      headers: {'Content-Type': 'application/json'},
      body: jsonEncode({'username': username, 'password': password}),
    );
    final body = jsonDecode(res.body) as Map<String, dynamic>;
    if (res.statusCode == 200 && body['success'] == true) {
      await TokenStorage.save(body['token']);
    }
    return ApiResult(res.statusCode, body);
  }

  static Future<ApiResult> me() async {
    final res = await http.get(Uri.parse('$baseUrl/auth/me'), headers: await _authHeaders());
    return ApiResult(res.statusCode, jsonDecode(res.body));
  }

  static Future<void> logout() async {
    try {
      await http.post(Uri.parse('$baseUrl/auth/logout'), headers: await _authHeaders());
    } catch (_) {
      // best-effort — clear local token regardless of network state
    }
    await TokenStorage.clear();
  }

  static Future<ApiResult> dashboard() async {
    final res = await http.get(Uri.parse('$baseUrl/dashboard'), headers: await _authHeaders());
    return ApiResult(res.statusCode, jsonDecode(res.body));
  }

  static Future<ApiResult> inventoryList({String? search, String? category, String? status}) async {
    final qp = <String, String>{
      if (search != null && search.isNotEmpty) 'search': search,
      if (category != null && category.isNotEmpty) 'category': category,
      if (status != null && status.isNotEmpty) 'status': status,
    };
    final uri = Uri.parse('$baseUrl/inventory').replace(queryParameters: qp.isEmpty ? null : qp);
    final res = await http.get(uri, headers: await _authHeaders());
    return ApiResult(res.statusCode, jsonDecode(res.body));
  }

  static Future<ApiResult> inventoryLookup(String code) async {
    final uri = Uri.parse('$baseUrl/inventory/lookup').replace(queryParameters: {'code': code});
    final res = await http.get(uri, headers: await _authHeaders());
    return ApiResult(res.statusCode, jsonDecode(res.body));
  }

  static Future<ApiResult> inventoryMeta() async {
    final res = await http.get(Uri.parse('$baseUrl/inventory/meta'), headers: await _authHeaders());
    return ApiResult(res.statusCode, jsonDecode(res.body));
  }

  static Future<ApiResult> inventoryQuickAdd(Map<String, dynamic> data) async {
    final res = await http.post(
      Uri.parse('$baseUrl/inventory/quick-add'),
      headers: await _authHeaders(),
      body: jsonEncode(data),
    );
    return ApiResult(res.statusCode, jsonDecode(res.body));
  }

  static Future<ApiResult> inventoryUpdate(Map<String, dynamic> data) async {
    final res = await http.post(
      Uri.parse('$baseUrl/inventory/update'),
      headers: await _authHeaders(),
      body: jsonEncode(data),
    );
    return ApiResult(res.statusCode, jsonDecode(res.body));
  }

  static Future<ApiResult> inventoryDelete(int itemId) async {
    final res = await http.post(
      Uri.parse('$baseUrl/inventory/delete'),
      headers: await _authHeaders(),
      body: jsonEncode({'item_id': itemId}),
    );
    return ApiResult(res.statusCode, jsonDecode(res.body));
  }

  // ── Locations ────────────────────────────────────────────────────────
  static Future<ApiResult> locations() async {
    final res = await http.get(Uri.parse('$baseUrl/locations'), headers: await _authHeaders());
    return ApiResult(res.statusCode, jsonDecode(res.body));
  }

  static Future<ApiResult> locationUpdate(int locationId, int capacity) async {
    final res = await http.post(
      Uri.parse('$baseUrl/locations/update'),
      headers: await _authHeaders(),
      body: jsonEncode({'location_id': locationId, 'capacity': capacity}),
    );
    return ApiResult(res.statusCode, jsonDecode(res.body));
  }

  // ── Suppliers ────────────────────────────────────────────────────────
  static Future<ApiResult> suppliers() async {
    final res = await http.get(Uri.parse('$baseUrl/suppliers'), headers: await _authHeaders());
    return ApiResult(res.statusCode, jsonDecode(res.body));
  }

  static Future<ApiResult> supplierStore(Map<String, dynamic> data) async {
    final res = await http.post(
      Uri.parse('$baseUrl/suppliers/store'),
      headers: await _authHeaders(),
      body: jsonEncode(data),
    );
    return ApiResult(res.statusCode, jsonDecode(res.body));
  }

  // ── Purchase Orders ──────────────────────────────────────────────────
  static Future<ApiResult> purchaseOrders({String? status, int? supplier}) async {
    final qp = <String, String>{
      if (status != null && status.isNotEmpty) 'status': status,
      if (supplier != null) 'supplier': '$supplier',
    };
    final uri = Uri.parse('$baseUrl/purchase-orders').replace(queryParameters: qp.isEmpty ? null : qp);
    final res = await http.get(uri, headers: await _authHeaders());
    return ApiResult(res.statusCode, jsonDecode(res.body));
  }

  static Future<ApiResult> purchaseOrderMeta() async {
    final res = await http.get(Uri.parse('$baseUrl/purchase-orders/meta'), headers: await _authHeaders());
    return ApiResult(res.statusCode, jsonDecode(res.body));
  }

  static Future<ApiResult> purchaseOrderView(int poId) async {
    final uri = Uri.parse('$baseUrl/purchase-orders/view').replace(queryParameters: {'id': '$poId'});
    final res = await http.get(uri, headers: await _authHeaders());
    return ApiResult(res.statusCode, jsonDecode(res.body));
  }

  static Future<ApiResult> purchaseOrderStore(Map<String, dynamic> data) async {
    final res = await http.post(
      Uri.parse('$baseUrl/purchase-orders/store'),
      headers: await _authHeaders(),
      body: jsonEncode(data),
    );
    return ApiResult(res.statusCode, jsonDecode(res.body));
  }

  static Future<ApiResult> purchaseOrderUpdate(int poId, String status) async {
    final res = await http.post(
      Uri.parse('$baseUrl/purchase-orders/update'),
      headers: await _authHeaders(),
      body: jsonEncode({'po_id': poId, 'status': status}),
    );
    return ApiResult(res.statusCode, jsonDecode(res.body));
  }

  // ── Alerts ───────────────────────────────────────────────────────────
  static Future<ApiResult> alerts({String? type}) async {
    final qp = <String, String>{if (type != null && type.isNotEmpty) 'type': type};
    final uri = Uri.parse('$baseUrl/alerts').replace(queryParameters: qp.isEmpty ? null : qp);
    final res = await http.get(uri, headers: await _authHeaders());
    return ApiResult(res.statusCode, jsonDecode(res.body));
  }

  static Future<ApiResult> alertResolve(int alertId, String action) async {
    final res = await http.post(
      Uri.parse('$baseUrl/alerts/resolve'),
      headers: await _authHeaders(),
      body: jsonEncode({'alert_id': alertId, 'action': action}),
    );
    return ApiResult(res.statusCode, jsonDecode(res.body));
  }

  static Future<ApiResult> alertScan() async {
    final res = await http.post(
      Uri.parse('$baseUrl/alerts/resolve'),
      headers: await _authHeaders(),
      body: jsonEncode({'action': 'scan'}),
    );
    return ApiResult(res.statusCode, jsonDecode(res.body));
  }

  // ── Users ────────────────────────────────────────────────────────────
  static Future<ApiResult> users() async {
    final res = await http.get(Uri.parse('$baseUrl/users'), headers: await _authHeaders());
    return ApiResult(res.statusCode, jsonDecode(res.body));
  }

  static Future<ApiResult> userMeta() async {
    final res = await http.get(Uri.parse('$baseUrl/users/meta'), headers: await _authHeaders());
    return ApiResult(res.statusCode, jsonDecode(res.body));
  }

  static Future<ApiResult> userDetail(int userId) async {
    final uri = Uri.parse('$baseUrl/users/detail').replace(queryParameters: {'id': '$userId'});
    final res = await http.get(uri, headers: await _authHeaders());
    return ApiResult(res.statusCode, jsonDecode(res.body));
  }

  static Future<ApiResult> userStore(Map<String, dynamic> data) async {
    final res = await http.post(
      Uri.parse('$baseUrl/users/store'),
      headers: await _authHeaders(),
      body: jsonEncode(data),
    );
    return ApiResult(res.statusCode, jsonDecode(res.body));
  }

  static Future<ApiResult> userUpdate(Map<String, dynamic> data) async {
    final res = await http.post(
      Uri.parse('$baseUrl/users/update'),
      headers: await _authHeaders(),
      body: jsonEncode(data),
    );
    return ApiResult(res.statusCode, jsonDecode(res.body));
  }

  // ── Settings ─────────────────────────────────────────────────────────
  static Future<ApiResult> settings() async {
    final res = await http.get(Uri.parse('$baseUrl/settings'), headers: await _authHeaders());
    return ApiResult(res.statusCode, jsonDecode(res.body));
  }

  static Future<ApiResult> settingsUpdate(String tab, Map<String, dynamic> fields) async {
    final uri = Uri.parse('$baseUrl/settings/update').replace(queryParameters: {'tab': tab});
    final res = await http.post(uri, headers: await _authHeaders(), body: jsonEncode({'fields': fields}));
    return ApiResult(res.statusCode, jsonDecode(res.body));
  }

  static Future<ApiResult> settingsManualBackup() async {
    final res = await http.post(Uri.parse('$baseUrl/settings/manual-backup'), headers: await _authHeaders());
    return ApiResult(res.statusCode, jsonDecode(res.body));
  }

  // ── Analytics ────────────────────────────────────────────────────────
  static Future<ApiResult> analytics() async {
    final res = await http.get(Uri.parse('$baseUrl/analytics'), headers: await _authHeaders());
    return ApiResult(res.statusCode, jsonDecode(res.body));
  }

  // ── Reports ──────────────────────────────────────────────────────────
  static Future<ApiResult> reportsOverview() async {
    final res = await http.get(Uri.parse('$baseUrl/reports'), headers: await _authHeaders());
    return ApiResult(res.statusCode, jsonDecode(res.body));
  }

  static Future<ApiResult> reportGenerate(String type) async {
    final uri = Uri.parse('$baseUrl/reports/generate').replace(queryParameters: {'type': type});
    final res = await http.get(uri, headers: await _authHeaders());
    return ApiResult(res.statusCode, jsonDecode(res.body));
  }
}

class ApiResult {
  final int statusCode;
  final Map<String, dynamic> body;
  ApiResult(this.statusCode, this.body);
  bool get success => statusCode >= 200 && statusCode < 300 && body['success'] == true;
  String get message => body['message']?.toString() ?? 'Something went wrong.';
}
