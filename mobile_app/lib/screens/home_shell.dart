import 'package:flutter/material.dart';
import '../core/theme.dart';
import 'dashboard_screen.dart';
import 'inventory_list_screen.dart';
import 'qr_scanner_screen.dart';

/// Post-login shell: bottom nav between Dashboard and Inventory, with a
/// center Scan action that pushes the camera screen full-screen (rather
/// than being a persistent third tab, since there's nothing to "stay on"
/// once a scan resolves).
class HomeShell extends StatefulWidget {
  const HomeShell({super.key});
  @override
  State<HomeShell> createState() => _HomeShellState();
}

class _HomeShellState extends State<HomeShell> {
  int _index = 0;
  final _inventoryKey = GlobalKey<InventoryListScreenState>();

  void _openScanner() async {
    await Navigator.of(context).push(MaterialPageRoute(builder: (_) => const QrScannerScreen()));
    // A scan may have added a new item — refresh the inventory tab so it's
    // up to date next time the user looks at it.
    _inventoryKey.currentState?.refresh();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: IndexedStack(
        index: _index,
        children: [
          const DashboardScreen(),
          InventoryListScreen(key: _inventoryKey),
        ],
      ),
      // MediaQuery override: pin the bottom bar's text scale to 1.0 no
      // matter what the accessibility Text Size setting is elsewhere in the
      // app. BottomNavigationBar has a fixed height — when the global
      // textScaler grew (Large/Extra Large), the label text no longer fit
      // that height, so Flutter clipped the overflowing icons/labels
      // entirely (looked like "Scan"/"Inventory" had vanished, but it was
      // really a layout overflow, not a missing widget). Root cause fixed
      // here rather than trying to grow the bar to match — this control is
      // chrome, not reading content, so it doesn't need to scale.
      bottomNavigationBar: MediaQuery(
        data: MediaQuery.of(context).copyWith(textScaler: TextScaler.noScaling),
        child: BottomNavigationBar(
          // Bar has 3 slots (Dashboard/Scan/Inventory) but the IndexedStack
          // only has 2 pages (Scan is a momentary push, not a page) — map
          // _index (0=Dashboard,1=Inventory) to the bar's 0/2 slots so the
          // highlight lands on the right icon instead of always on "Scan".
          currentIndex: _index == 1 ? 2 : 0,
          backgroundColor: AppColors.surfaceDark,
          selectedItemColor: AppColors.primary,
          unselectedItemColor: AppColors.textMuted,
          onTap: (i) {
            if (i == 1) {
              _openScanner();
              return;
            }
            setState(() => _index = i == 2 ? 1 : 0);
          },
          items: const [
            BottomNavigationBarItem(icon: Icon(Icons.dashboard_rounded), label: 'Dashboard'),
            BottomNavigationBarItem(icon: Icon(Icons.qr_code_scanner_rounded), label: 'Scan'),
            BottomNavigationBarItem(icon: Icon(Icons.inventory_2_rounded), label: 'Inventory'),
          ],
        ),
      ),
    );
  }
}
