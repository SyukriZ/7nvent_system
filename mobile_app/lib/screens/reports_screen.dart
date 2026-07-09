import 'package:flutter/material.dart';
import '../core/api_client.dart';
import '../core/theme.dart';
import 'app_drawer.dart';

/// Same 6 report types as ReportController — data rendered as a scrollable
/// table in-app instead of a server-generated PDF/CSV file, since a mobile
/// client is a poor fit for print-oriented PDF layout. The underlying rows
/// are identical to what the web CSV/PDF export contains.
class ReportsScreen extends StatefulWidget {
  const ReportsScreen({super.key});
  @override
  State<ReportsScreen> createState() => _ReportsScreenState();
}

class _ReportType {
  final String key;
  final String label;
  final IconData icon;
  _ReportType(this.key, this.label, this.icon);
}

class _ReportsScreenState extends State<ReportsScreen> {
  static final _types = [
    _ReportType('stock-summary', 'Stock Summary', Icons.inventory_2_outlined),
    _ReportType('consumption', 'Consumption Analytics', Icons.trending_down_rounded),
    _ReportType('po-history', 'PO History', Icons.receipt_long_outlined),
    _ReportType('valuation', 'Inventory Valuation', Icons.payments_outlined),
    _ReportType('supplier', 'Supplier Performance', Icons.local_shipping_outlined),
    _ReportType('waste-expiry', 'Waste & Expiry', Icons.warning_amber_rounded),
  ];

  Map<String, dynamic>? _overview;
  bool _loadingOverview = true;

  @override
  void initState() {
    super.initState();
    _loadOverview();
  }

  Future<void> _loadOverview() async {
    final result = await ApiClient.reportsOverview();
    if (!mounted) return;
    setState(() {
      _loadingOverview = false;
      if (result.success) _overview = result.body;
    });
  }

  void _openReport(_ReportType type) {
    Navigator.of(context).push(MaterialPageRoute(builder: (_) => _ReportDataScreen(type: type)));
  }

  @override
  Widget build(BuildContext context) {
    final metrics = (_overview?['metrics'] as Map?) ?? {};
    return Scaffold(
      drawer: const AppDrawer(),
      appBar: AppBar(title: const Text('Reports')),
      body: ListView(
        padding: const EdgeInsets.all(16),
        children: [
          if (!_loadingOverview) ...[
            Row(
              children: [
                Expanded(child: _metricCard('Manual Time Reduced', '${metrics['manual_time_reduced'] ?? 0}%')),
                const SizedBox(width: 8),
                Expanded(child: _metricCard('Inventory Accuracy', '${metrics['inventory_accuracy'] ?? 0}%')),
                const SizedBox(width: 8),
                Expanded(child: _metricCard('Waste Reduction', '${metrics['waste_reduction'] ?? 0}%')),
              ],
            ),
            const SizedBox(height: 20),
          ],
          const Text('Generate Report', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
          const SizedBox(height: 8),
          ..._types.map((t) => Card(
                child: ListTile(
                  leading: Icon(t.icon, color: AppColors.primary),
                  title: Text(t.label),
                  trailing: const Icon(Icons.chevron_right),
                  onTap: () => _openReport(t),
                ),
              )),
        ],
      ),
    );
  }

  Widget _metricCard(String label, String value) {
    return Card(
      child: Padding(
        padding: const EdgeInsets.symmetric(vertical: 14, horizontal: 8),
        child: Column(
          children: [
            Text(value, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: AppColors.primary)),
            const SizedBox(height: 4),
            Text(label, textAlign: TextAlign.center, style: const TextStyle(fontSize: 10, color: AppColors.textMuted)),
          ],
        ),
      ),
    );
  }
}

class _ReportDataScreen extends StatefulWidget {
  final _ReportType type;
  const _ReportDataScreen({required this.type});

  @override
  State<_ReportDataScreen> createState() => _ReportDataScreenState();
}

class _ReportDataScreenState extends State<_ReportDataScreen> {
  List columns = [];
  List rows = [];
  bool _loading = true;
  String? _error;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    final result = await ApiClient.reportGenerate(widget.type.key);
    if (!mounted) return;
    setState(() {
      _loading = false;
      if (result.success) {
        columns = result.body['columns'] ?? [];
        rows = result.body['rows'] ?? [];
      } else {
        _error = result.message;
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(widget.type.label)),
      body: _loading
          ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
          : _error != null
              ? Center(child: Text(_error!, style: const TextStyle(color: AppColors.critical)))
              : rows.isEmpty
                  ? const Center(child: Text('No data for this report.', style: TextStyle(color: AppColors.textMuted)))
                  : SingleChildScrollView(
                      scrollDirection: Axis.horizontal,
                      child: SingleChildScrollView(
                        child: DataTable(
                          columns: columns.map<DataColumn>((c) => DataColumn(label: Text(c.toString(), style: const TextStyle(fontWeight: FontWeight.bold)))).toList(),
                          rows: rows.map<DataRow>((r) {
                            final map = r as Map<String, dynamic>;
                            return DataRow(cells: map.values.map((v) => DataCell(Text(v?.toString() ?? ''))).toList());
                          }).toList(),
                        ),
                      ),
                    ),
    );
  }
}
