/// In-memory holder for the logged-in user's profile (role_name,
/// access_level, full_name, department, ...) — populated right after
/// login/splash from AuthApiController's `user` payload. Used to gate
/// nav items and action buttons the same way the web app's
/// Auth::hasRole()/hasAccess() checks do server-side.
///
/// This is a client-side convenience only — every write endpoint still
/// re-checks the role server-side via ApiController::requireRole(), so
/// hiding a button here is a UX nicety, not the actual security boundary.
class Session {
  static Map<String, dynamic>? currentUser;

  static void set(Map<String, dynamic>? user) => currentUser = user;
  static void clear() => currentUser = null;

  static String get roleName => currentUser?['role_name']?.toString() ?? '';
  static String get fullName => currentUser?['full_name']?.toString() ?? '';

  static bool hasRole(List<String> roles) => roles.contains(roleName);

  static bool get canManageInventory => hasRole(['Inventory Manager', 'Housekeeping Manager']);
  static bool get canManageProcurement => hasRole(['Inventory Manager', 'Procurement Officer']);
  static bool get canManageUsers => hasRole(['Inventory Manager', 'IT Administrator']);
  static bool get canManageSettings => hasRole(['Inventory Manager', 'IT Administrator']);
  static bool get canDeleteInventory => hasRole(['Inventory Manager']);
  static bool get canManageLocations => hasRole(['Inventory Manager']);
}
