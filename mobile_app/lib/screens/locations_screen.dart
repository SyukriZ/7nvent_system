import 'package:flutter/material.dart';
import '../core/api_client.dart';
import '../core/session.dart';
import '../core/theme.dart';
import 'app_drawer.dart';

class LocationsScreen extends StatefulWidget {
  const LocationsScreen({super.key});
  @override
  State<LocationsScreen> createState() => _LocationsScreenState();
}

class _LocationsScreenState extends State<LocationsScreen> {
  List locations = [];
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
    final result = await ApiClient.locations();
    if (!mounted) return;
    setState(() {
      _loading = false;
      if (result.success) {
        locations = result.body['locations'] ?? [];
      } else {
        _error = result.message;
      }
    });
  }

  Future<void> _editCapacity(Map<String, dynamic> loc) async {
    final controller = TextEditingController(text: '${loc['capacity'] ?? 0}');
    final newCapacity = await showDialog<int>(
      context: context,
      builder: (ctx) => AlertDialog(
        backgroundColor: AppColors.surfaceDark,
        title: Text('Edit capacity — ${loc['location_name']}'),
        content: TextField(
          controller: controller,
          keyboardType: TextInputType.number,
          decoration: const InputDecoration(labelText: 'Capacity'),
        ),
        actions: [
          TextButton(onPressed: () => Navigator.pop(ctx), child: const Text('Cancel')),
          ElevatedButton(
            onPressed: () => Navigator.pop(ctx, int.tryParse(controller.text)),
            child: const Text('Save'),
          ),
        ],
      ),
    );
    if (newCapacity == null) return;

    final result = await ApiClient.locationUpdate(loc['location_id'] as int, newCapacity);
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(result.message)));
    if (result.success) _load();
  }

  Color _statusColor(String? status) {
    switch (status) {
      case 'Low Stock':
        return AppColors.warning;
      default:
        return AppColors.success;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      drawer: const AppDrawer(),
      appBar: AppBar(title: const Text('Locations')),
      body: _loading
          ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
          : _error != null
              ? Center(child: Text(_error!, style: const TextStyle(color: AppColors.critical)))
              : RefreshIndicator(
                  onRefresh: _load,
                  child: locations.isEmpty
                      ? ListView(children: const [
                          Padding(
                            padding: EdgeInsets.all(24),
                            child: Center(child: Text('No locations found.', style: TextStyle(color: AppColors.textMuted))),
                          ),
                        ])
                      : ListView.builder(
                          padding: const EdgeInsets.all(12),
                          itemCount: locations.length,
                          itemBuilder: (context, i) {
                            final loc = locations[i] as Map<String, dynamic>;
                            final pct = (loc['capacity_pct'] as num?)?.toDouble() ?? 0;
                            return Card(
                              child: Padding(
                                padding: const EdgeInsets.all(14),
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Row(
                                      children: [
                                        Expanded(
                                          child: Text(loc['location_name']?.toString() ?? '',
                                              style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                                        ),
                                        Container(
                                          padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                          decoration: BoxDecoration(
                                            color: _statusColor(loc['status']?.toString()).withOpacity(0.15),
                                            borderRadius: BorderRadius.circular(20),
                                          ),
                                          child: Text(loc['status']?.toString() ?? '-',
                                              style: TextStyle(color: _statusColor(loc['status']?.toString()), fontWeight: FontWeight.bold, fontSize: 12)),
                                        ),
                                      ],
                                    ),
                                    const SizedBox(height: 8),
                                    Text('${loc['current_items'] ?? 0} / ${loc['capacity'] ?? 0} items (${pct.toStringAsFixed(0)}%)',
                                        style: const TextStyle(color: AppColors.textMuted)),
                                    const SizedBox(height: 8),
                                    ClipRRect(
                                      borderRadius: BorderRadius.circular(8),
                                      child: LinearProgressIndicator(
                                        value: (pct / 100).clamp(0, 1),
                                        minHeight: 6,
                                        backgroundColor: AppColors.surfaceDark,
                                        color: pct >= 90 ? AppColors.critical : pct >= 70 ? AppColors.warning : AppColors.primary,
                                      ),
                                    ),
                                    if (Session.canManageLocations) ...[
                                      const SizedBox(height: 8),
                                      Align(
                                        alignment: Alignment.centerRight,
                                        child: TextButton.icon(
                                          onPressed: () => _editCapacity(loc),
                                          icon: const Icon(Icons.edit, size: 16),
                                          label: const Text('Edit capacity'),
                                        ),
                                      ),
                                    ],
                                  ],
                                ),
                              ),
                            );
                          },
                        ),
                ),
    );
  }
}
