import 'package:flutter/material.dart';
import '../core/api_client.dart';
import '../core/glass.dart';
import '../core/session.dart';
import '../core/theme.dart';
import 'app_drawer.dart';
import 'login_screen.dart';

class DashboardScreen extends StatefulWidget {
  const DashboardScreen({super.key});
  @override
  State<DashboardScreen> createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen> {
  Map<String, dynamic>? _data;
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    final result = await ApiClient.dashboard();
    if (!mounted) return;
    setState(() {
      _loading = false;
      if (result.success) {
        _data = result.body;
      } else {
        _error = result.message;
      }
    });
  }

  Future<void> _logout() async {
    await ApiClient.logout();
    Session.clear();
    if (!mounted) return;
    Navigator.of(context).pushAndRemoveUntil(MaterialPageRoute(builder: (_) => const LoginScreen()), (route) => false);
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bgDark,
      extendBodyBehindAppBar: true,
      drawer: const AppDrawer(),
      appBar: AppBar(
        title: const Text('Dashboard'),
        actions: [IconButton(onPressed: _logout, icon: const Icon(Icons.logout))],
      ),
      body: GlassAmbientBackground(
        child: SafeArea(
          child: _loading
              ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
              : _error != null
                  ? Center(
                      child: Column(
                        mainAxisSize: MainAxisSize.min,
                        children: [
                          Text(_error!, style: const TextStyle(color: AppColors.critical)),
                          const SizedBox(height: 12),
                          ElevatedButton(onPressed: _load, child: const Text('Retry')),
                        ],
                      ),
                    )
                  : RefreshIndicator(
                      onRefresh: _load,
                      child: ListView(
                        padding: const EdgeInsets.fromLTRB(16, 70, 16, 16),
                        children: [
                          _buildStatCards(),
                          const SizedBox(height: 20),
                          const Text('Active Alerts', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Colors.white)),
                          const SizedBox(height: 8),
                          ..._buildAlerts(),
                        ],
                      ),
                    ),
        ),
      ),
    );
  }

  Widget _buildStatCards() {
    final stats = (_data?['stats'] as Map?) ?? {};
    return Row(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Expanded(child: _statCard('Total Stock', '${stats['total_stock'] ?? 0}', AppColors.primary, Icons.inventory_2_outlined)),
        const SizedBox(width: 10),
        Expanded(child: _statCard('Pending PO', 'RM ${stats['pending_po_value'] ?? 0}', AppColors.warning, Icons.receipt_long_outlined)),
        const SizedBox(width: 10),
        Expanded(child: _statCard('Critical', '${stats['critical_alerts'] ?? 0}', AppColors.critical, Icons.warning_amber_rounded)),
      ],
    );
  }

  Widget _statCard(String label, String value, Color color, IconData icon) {
    return GlassCard(
      blur: false,
      padding: const EdgeInsets.all(14),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Icon(icon, color: color, size: 18),
          const SizedBox(height: 10),
          Text(label, style: const TextStyle(color: AppColors.textFaint, fontSize: 11)),
          const SizedBox(height: 4),
          Text(value, style: TextStyle(fontSize: 17, fontWeight: FontWeight.bold, color: color), maxLines: 1, overflow: TextOverflow.ellipsis),
        ],
      ),
    );
  }

  List<Widget> _buildAlerts() {
    final alerts = (_data?['active_alerts'] as List?) ?? [];
    if (alerts.isEmpty) {
      return [
        const Padding(
          padding: EdgeInsets.all(12),
          child: Text('No active alerts.', style: TextStyle(color: AppColors.textMuted)),
        ),
      ];
    }
    return alerts.map((a) {
      final map = a as Map<String, dynamic>;
      final isCritical = map['alert_type'] == 'Critical';
      final color = isCritical ? AppColors.critical : AppColors.warning;
      return Padding(
        padding: const EdgeInsets.only(bottom: 10),
        child: GlassCard(
          blur: false,
          padding: const EdgeInsets.all(14),
          child: Row(
            children: [
              Container(
                width: 4,
                height: 40,
                decoration: BoxDecoration(color: color, borderRadius: BorderRadius.circular(4)),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(map['title']?.toString() ?? '', style: const TextStyle(fontWeight: FontWeight.bold, color: Colors.white, fontSize: 13)),
                    const SizedBox(height: 2),
                    Text(map['description']?.toString() ?? '', style: const TextStyle(color: AppColors.textMuted, fontSize: 12)),
                  ],
                ),
              ),
              Icon(Icons.warning_amber_rounded, color: color, size: 20),
            ],
          ),
        ),
      );
    }).toList();
  }
}
