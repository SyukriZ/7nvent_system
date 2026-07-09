import 'package:flutter/material.dart';
import '../core/api_client.dart';
import '../core/glass.dart';
import '../core/session.dart';
import '../core/text_scale.dart';
import '../core/theme.dart';
import 'alerts_screen.dart';
import 'analytics_screen.dart';
import 'dashboard_screen.dart';
import 'inventory_list_screen.dart';
import 'locations_screen.dart';
import 'login_screen.dart';
import 'purchase_orders_screen.dart';
import 'reports_screen.dart';
import 'settings_screen.dart';
import 'suppliers_screen.dart';
import 'users_screen.dart';

/// Shared side navigation — same module set + section grouping (Main /
/// Management / System) as #sidebar in resources/views/layouts/app.php,
/// same dark navy background and blue active-pill treatment. Gated by the
/// same roles Auth::hasRole() checks server-side (Users/Settings hidden
/// from anyone who isn't Inventory Manager/IT Administrator).
class AppDrawer extends StatelessWidget {
  const AppDrawer({super.key});

  void _go(BuildContext context, Widget screen) {
    Navigator.of(context).pop();
    Navigator.of(context).push(MaterialPageRoute(builder: (_) => screen));
  }

  void _showAccessibility(BuildContext context) {
    Navigator.of(context).pop();
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      builder: (ctx) => GlassCard(
        strong: true,
        margin: const EdgeInsets.all(16),
        child: ValueListenableBuilder<AppTextSize>(
          valueListenable: TextScaleController.notifier,
          builder: (context, current, _) {
            return Column(
              mainAxisSize: MainAxisSize.min,
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Row(
                  children: [
                    Icon(Icons.accessibility_new_rounded, size: 16, color: AppColors.primary),
                    SizedBox(width: 6),
                    Text('Text Size', style: TextStyle(fontWeight: FontWeight.bold, fontSize: 14)),
                  ],
                ),
                const SizedBox(height: 4),
                const Text('Choose a comfortable reading size', style: TextStyle(fontSize: 11, color: AppColors.textFaint)),
                const SizedBox(height: 12),
                ...AppTextSize.values.map((size) {
                  final selected = size == current;
                  return Padding(
                    padding: const EdgeInsets.only(bottom: 8),
                    child: InkWell(
                      borderRadius: BorderRadius.circular(12),
                      onTap: () => TextScaleController.set(size),
                      child: Container(
                        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
                        decoration: BoxDecoration(
                          borderRadius: BorderRadius.circular(12),
                          border: Border.all(color: selected ? AppColors.primary : AppColors.glassBorder, width: selected ? 2 : 1),
                          color: selected ? AppColors.primary.withOpacity(0.12) : Colors.transparent,
                        ),
                        child: Row(
                          children: [
                            Text('A', style: TextStyle(fontSize: 14 + size.index * 4.0, fontWeight: FontWeight.bold)),
                            const SizedBox(width: 12),
                            Text(size.label, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13)),
                            if (selected) ...[const Spacer(), const Icon(Icons.check_circle, color: AppColors.primary, size: 18)],
                          ],
                        ),
                      ),
                    ),
                  );
                }),
              ],
            );
          },
        ),
      ),
    );
  }

  Future<void> _logout(BuildContext context) async {
    Navigator.of(context).pop();
    await ApiClient.logout();
    Session.clear();
    if (context.mounted) {
      Navigator.of(context).pushAndRemoveUntil(
        MaterialPageRoute(builder: (_) => const LoginScreen()),
        (route) => false,
      );
    }
  }

  @override
  Widget build(BuildContext context) {
    return Drawer(
      backgroundColor: AppColors.sidebarBg,
      child: SafeArea(
        child: Column(
          children: [
            Container(
              width: double.infinity,
              padding: const EdgeInsets.fromLTRB(18, 24, 18, 16),
              decoration: const BoxDecoration(
                border: Border(bottom: BorderSide(color: Color(0xFF2D2D44))),
              ),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const BrandGlow(fontSize: 22),
                  const SizedBox(height: 4),
                  const Text('HOTEL INVENTORY SYSTEM',
                      style: TextStyle(fontSize: 9, color: Color(0xFF888888), letterSpacing: 1)),
                ],
              ),
            ),
            Expanded(
              child: ListView(
                padding: const EdgeInsets.symmetric(vertical: 8),
                children: [
                  _sectionLabel('MAIN'),
                  _item(context, Icons.dashboard_rounded, 'Dashboard', () => _go(context, const DashboardScreen())),
                  _item(context, Icons.inventory_2_outlined, 'Inventory', () => _go(context, const InventoryListScreen())),
                  _item(context, Icons.receipt_long_outlined, 'Purchase Orders', () => _go(context, const PurchaseOrdersScreen())),
                  _item(context, Icons.warning_amber_rounded, 'Alerts', () => _go(context, const AlertsScreen())),
                  _sectionLabel('MANAGEMENT'),
                  _item(context, Icons.local_shipping_outlined, 'Suppliers', () => _go(context, const SuppliersScreen())),
                  _item(context, Icons.map_outlined, 'Locations', () => _go(context, const LocationsScreen())),
                  _item(context, Icons.description_outlined, 'Reports', () => _go(context, const ReportsScreen())),
                  _sectionLabel('SYSTEM'),
                  if (Session.canManageUsers)
                    _item(context, Icons.people_outline, 'Users & Roles', () => _go(context, const UsersScreen())),
                  if (Session.canManageSettings)
                    _item(context, Icons.settings_outlined, 'Settings', () => _go(context, const SettingsScreen())),
                  _item(context, Icons.bar_chart_rounded, 'Analytics', () => _go(context, const AnalyticsScreen())),
                  _sectionLabel('ACCESSIBILITY'),
                  _item(context, Icons.accessibility_new_rounded, 'Text Size', () => _showAccessibility(context)),
                  _item(context, Icons.logout_rounded, 'Exit', () => _logout(context)),
                ],
              ),
            ),
            Container(
              width: double.infinity,
              padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
              decoration: const BoxDecoration(
                border: Border(top: BorderSide(color: Color(0xFF2D2D44))),
              ),
              child: Row(
                children: [
                  CircleAvatar(
                    backgroundColor: AppColors.primary,
                    child: Text(
                      Session.fullName.isEmpty ? 'U' : Session.fullName.substring(0, 1).toUpperCase(),
                      style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.white, fontSize: 13),
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(Session.fullName, style: const TextStyle(color: Colors.white, fontSize: 12, fontWeight: FontWeight.w600), overflow: TextOverflow.ellipsis),
                        Text(Session.roleName, style: const TextStyle(color: Color(0xFF888888), fontSize: 10)),
                      ],
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _sectionLabel(String label) => Padding(
        padding: const EdgeInsets.fromLTRB(18, 14, 18, 4),
        child: Text(label, style: const TextStyle(fontSize: 10, color: Color(0xFF555555), letterSpacing: 1, fontWeight: FontWeight.bold)),
      );

  Widget _item(BuildContext context, IconData icon, String label, VoidCallback onTap) {
    return Padding(
      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 1),
      child: Material(
        color: Colors.transparent,
        child: InkWell(
          borderRadius: BorderRadius.circular(8),
          onTap: onTap,
          child: Padding(
            padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 10),
            child: Row(
              children: [
                Icon(icon, size: 18, color: const Color(0xFFC5C5D3)),
                const SizedBox(width: 12),
                Text(label, style: const TextStyle(fontSize: 13, color: Color(0xFFC5C5D3))),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
