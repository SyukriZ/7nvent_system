import 'package:flutter/material.dart';
import '../core/api_client.dart';
import '../core/theme.dart';
import 'app_drawer.dart';

/// Same 5 datasets AnalyticsController's web view renders as animated
/// Chart.js/JS Vector Map widgets. Rendered here as simple native lists/bars
/// instead of pulling in a charting package — the numbers are identical,
/// only the visual chrome differs (a deliberate scope call, not a data gap).
class AnalyticsScreen extends StatefulWidget {
  const AnalyticsScreen({super.key});
  @override
  State<AnalyticsScreen> createState() => _AnalyticsScreenState();
}

class _AnalyticsScreenState extends State<AnalyticsScreen> {
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
    final result = await ApiClient.analytics();
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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      drawer: const AppDrawer(),
      appBar: AppBar(title: const Text('Analytics')),
      body: _loading
          ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
          : _error != null
              ? Center(child: Text(_error!, style: const TextStyle(color: AppColors.critical)))
              : RefreshIndicator(
                  onRefresh: _load,
                  child: ListView(
                    padding: const EdgeInsets.all(16),
                    children: [
                      _sectionTitle('Inventory Valuation (by weekday)'),
                      _valuationTrend(),
                      const SizedBox(height: 20),
                      _sectionTitle('Fast vs Slow-Moving Items'),
                      _fastSlowMovers(),
                      const SizedBox(height: 20),
                      _sectionTitle('Supplier Delivery Performance'),
                      _supplierPerformance(),
                      const SizedBox(height: 20),
                      _sectionTitle('Supplier Locations'),
                      _supplierMap(),
                    ],
                  ),
                ),
    );
  }

  Widget _sectionTitle(String title) => Padding(
        padding: const EdgeInsets.only(bottom: 8),
        child: Text(title, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
      );

  Widget _valuationTrend() {
    final series = (_data?['valuation_trend'] as List?) ?? [];
    if (series.isEmpty) return const Text('No valuation data.', style: TextStyle(color: AppColors.textMuted));
    final maxClose = series.fold<double>(0, (m, e) => (e['close'] as num).toDouble() > m ? (e['close'] as num).toDouble() : m);
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(
          children: series.map<Widget>((e) {
            final close = (e['close'] as num).toDouble();
            final ratio = maxClose == 0 ? 0.0 : close / maxClose;
            return Padding(
              padding: const EdgeInsets.symmetric(vertical: 4),
              child: Row(
                children: [
                  SizedBox(width: 36, child: Text(e['day']?.toString() ?? '')),
                  Expanded(
                    child: ClipRRect(
                      borderRadius: BorderRadius.circular(6),
                      child: LinearProgressIndicator(value: ratio.clamp(0, 1), minHeight: 14, backgroundColor: AppColors.surfaceDark, color: AppColors.primary),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Text('RM ${close.toStringAsFixed(0)}', style: const TextStyle(fontSize: 12)),
                ],
              ),
            );
          }).toList(),
        ),
      ),
    );
  }

  Widget _fastSlowMovers() {
    final movers = (_data?['fast_slow_movers'] as List?) ?? [];
    if (movers.isEmpty) return const Text('No consumption data yet.', style: TextStyle(color: AppColors.textMuted));
    final maxOut = movers.fold<double>(0, (m, e) => (e['total_out'] as num).toDouble() > m ? (e['total_out'] as num).toDouble() : m);
    return Card(
      child: Padding(
        padding: const EdgeInsets.all(12),
        child: Column(
          children: movers.map<Widget>((m) {
            final out = (m['total_out'] as num).toDouble();
            final ratio = maxOut == 0 ? 0.0 : out / maxOut;
            return Padding(
              padding: const EdgeInsets.symmetric(vertical: 4),
              child: Row(
                children: [
                  Expanded(flex: 2, child: Text(m['item_name']?.toString() ?? '', overflow: TextOverflow.ellipsis)),
                  Expanded(
                    flex: 3,
                    child: ClipRRect(
                      borderRadius: BorderRadius.circular(6),
                      child: LinearProgressIndicator(value: ratio.clamp(0, 1), minHeight: 12, backgroundColor: AppColors.surfaceDark, color: AppColors.accentA),
                    ),
                  ),
                  const SizedBox(width: 8),
                  Text('${out.toStringAsFixed(0)}', style: const TextStyle(fontSize: 12)),
                ],
              ),
            );
          }).toList(),
        ),
      ),
    );
  }

  Widget _supplierPerformance() {
    final perf = (_data?['supplier_performance'] as List?) ?? [];
    if (perf.isEmpty) return const Text('No purchase order data yet.', style: TextStyle(color: AppColors.textMuted));
    return Card(
      child: Column(
        children: perf.map<Widget>((p) {
          return ListTile(
            title: Text(p['supplier_name']?.toString() ?? ''),
            subtitle: Text('Delivered ${p['delivered'] ?? 0} • Pending ${p['pending'] ?? 0} • Cancelled ${p['cancelled'] ?? 0}'),
          );
        }).toList(),
      ),
    );
  }

  Widget _supplierMap() {
    final markers = (_data?['supplier_map'] as List?) ?? [];
    if (markers.isEmpty) return const Text('No suppliers found.', style: TextStyle(color: AppColors.textMuted));
    return Card(
      child: Column(
        children: markers.map<Widget>((m) {
          return ListTile(
            leading: const Icon(Icons.location_on_outlined, color: AppColors.primary),
            title: Text(m['name']?.toString() ?? ''),
            subtitle: Text('${m['city'] ?? ''} • ${m['category'] ?? ''}'),
            trailing: Row(
              mainAxisSize: MainAxisSize.min,
              children: [
                const Icon(Icons.star_rounded, size: 16, color: AppColors.warning),
                Text('${m['rating'] ?? 0}'),
              ],
            ),
          );
        }).toList(),
      ),
    );
  }
}
