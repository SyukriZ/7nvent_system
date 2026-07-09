import 'package:flutter/material.dart';
import '../core/api_client.dart';
import '../core/session.dart';
import '../core/theme.dart';
import 'app_drawer.dart';

class AlertsScreen extends StatefulWidget {
  const AlertsScreen({super.key});
  @override
  State<AlertsScreen> createState() => _AlertsScreenState();
}

class _AlertsScreenState extends State<AlertsScreen> {
  List alerts = [];
  Map<String, dynamic>? counts;
  bool _loading = true;
  bool _scanning = false;
  String? _error;
  String _typeFilter = '';

  static const _types = ['', 'Critical', 'Warning', 'Info'];

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
    final result = await ApiClient.alerts(type: _typeFilter);
    if (!mounted) return;
    setState(() {
      _loading = false;
      if (result.success) {
        alerts = result.body['alerts'] ?? [];
        counts = result.body['counts'] as Map<String, dynamic>?;
      } else {
        _error = result.message;
      }
    });
  }

  Future<void> _resolve(int alertId, String action) async {
    final result = await ApiClient.alertResolve(alertId, action);
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(result.message)));
    if (result.success) _load();
  }

  Future<void> _scan() async {
    setState(() => _scanning = true);
    final result = await ApiClient.alertScan();
    if (!mounted) return;
    setState(() => _scanning = false);
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(result.message)));
    if (result.success) _load();
  }

  Color _typeColor(String? type) {
    switch (type) {
      case 'Critical':
        return AppColors.critical;
      case 'Warning':
        return AppColors.warning;
      default:
        return AppColors.primary;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      drawer: const AppDrawer(),
      appBar: AppBar(
        title: const Text('Alerts'),
        actions: [
          if (Session.canManageProcurement)
            IconButton(
              onPressed: _scanning ? null : _scan,
              icon: _scanning
                  ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(strokeWidth: 2))
                  : const Icon(Icons.sync_rounded),
              tooltip: 'Run inventory scan',
            ),
        ],
      ),
      body: Column(
        children: [
          if (counts != null)
            Padding(
              padding: const EdgeInsets.all(12),
              child: Row(
                children: [
                  Expanded(child: _countChip('Critical', counts?['critical'], AppColors.critical)),
                  const SizedBox(width: 8),
                  Expanded(child: _countChip('Warning', counts?['warning'], AppColors.warning)),
                  const SizedBox(width: 8),
                  Expanded(child: _countChip('Info', counts?['info'], AppColors.primary)),
                ],
              ),
            ),
          SizedBox(
            height: 44,
            child: ListView(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 12),
              children: _types.map((t) {
                final selected = _typeFilter == t;
                return Padding(
                  padding: const EdgeInsets.only(right: 8),
                  child: ChoiceChip(
                    label: Text(t.isEmpty ? 'All' : t),
                    selected: selected,
                    selectedColor: AppColors.primary,
                    onSelected: (_) {
                      setState(() => _typeFilter = t);
                      _load();
                    },
                  ),
                );
              }).toList(),
            ),
          ),
          Expanded(
            child: _loading
                ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
                : _error != null
                    ? Center(child: Text(_error!, style: const TextStyle(color: AppColors.critical)))
                    : alerts.isEmpty
                        ? const Center(child: Text('No active alerts.', style: TextStyle(color: AppColors.textMuted)))
                        : RefreshIndicator(
                            onRefresh: _load,
                            child: ListView.builder(
                              padding: const EdgeInsets.symmetric(horizontal: 12),
                              itemCount: alerts.length,
                              itemBuilder: (context, i) {
                                final a = alerts[i] as Map<String, dynamic>;
                                final color = _typeColor(a['alert_type']?.toString());
                                return Card(
                                  child: Padding(
                                    padding: const EdgeInsets.all(12),
                                    child: Column(
                                      crossAxisAlignment: CrossAxisAlignment.start,
                                      children: [
                                        Row(
                                          children: [
                                            Icon(Icons.warning_amber_rounded, color: color),
                                            const SizedBox(width: 8),
                                            Expanded(
                                              child: Text(a['title']?.toString() ?? '',
                                                  style: const TextStyle(fontWeight: FontWeight.bold)),
                                            ),
                                          ],
                                        ),
                                        const SizedBox(height: 6),
                                        Text(a['description']?.toString() ?? '', style: const TextStyle(color: AppColors.textMuted)),
                                        if (Session.canManageProcurement) ...[
                                          const SizedBox(height: 10),
                                          Row(
                                            mainAxisAlignment: MainAxisAlignment.end,
                                            children: [
                                              TextButton(
                                                onPressed: () => _resolve(a['alert_id'] as int, 'dismiss'),
                                                child: const Text('Dismiss'),
                                              ),
                                              const SizedBox(width: 4),
                                              ElevatedButton(
                                                onPressed: () => _resolve(a['alert_id'] as int, 'approve'),
                                                child: const Text('Approve'),
                                              ),
                                            ],
                                          ),
                                        ],
                                      ],
                                    ),
                                  ),
                                );
                              },
                            ),
                          ),
          ),
        ],
      ),
    );
  }

  Widget _countChip(String label, dynamic value, Color color) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.symmetric(vertical: 10),
        child: Column(
          children: [
            Text('${value ?? 0}', style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: color)),
            Text(label, style: const TextStyle(fontSize: 11, color: AppColors.textMuted)),
          ],
        ),
      ),
    );
  }
}
