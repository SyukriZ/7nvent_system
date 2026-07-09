import 'package:flutter/material.dart';
import '../core/api_client.dart';
import '../core/session.dart';
import '../core/theme.dart';

class PurchaseOrderDetailScreen extends StatefulWidget {
  final int poId;
  const PurchaseOrderDetailScreen({super.key, required this.poId});

  @override
  State<PurchaseOrderDetailScreen> createState() => _PurchaseOrderDetailScreenState();
}

class _PurchaseOrderDetailScreenState extends State<PurchaseOrderDetailScreen> {
  Map<String, dynamic>? _order;
  List _lineItems = [];
  bool _loading = true;
  String? _error;
  bool _updating = false;

  static const _statuses = ['Pending', 'In Transit', 'Delivered', 'Cancelled'];

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
    final result = await ApiClient.purchaseOrderView(widget.poId);
    if (!mounted) return;
    setState(() {
      _loading = false;
      if (result.success) {
        _order = result.body['order'] as Map<String, dynamic>?;
        _lineItems = result.body['line_items'] ?? [];
      } else {
        _error = result.message;
      }
    });
  }

  Future<void> _changeStatus(String status) async {
    setState(() => _updating = true);
    final result = await ApiClient.purchaseOrderUpdate(widget.poId, status);
    if (!mounted) return;
    setState(() => _updating = false);
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(result.message)));
    if (result.success) _load();
  }

  Color _statusColor(String? status) {
    switch (status) {
      case 'Delivered':
        return AppColors.success;
      case 'Cancelled':
        return AppColors.critical;
      case 'In Transit':
        return AppColors.primary;
      default:
        return AppColors.warning;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: Text(_order?['po_number']?.toString() ?? 'Purchase Order')),
      body: _loading
          ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
          : _error != null
              ? Center(child: Text(_error!, style: const TextStyle(color: AppColors.critical)))
              : ListView(
                  padding: const EdgeInsets.all(16),
                  children: [
                    Card(
                      child: Padding(
                        padding: const EdgeInsets.all(16),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              children: [
                                Expanded(
                                  child: Text(_order?['supplier_name']?.toString() ?? '',
                                      style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold)),
                                ),
                                Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                  decoration: BoxDecoration(
                                    color: _statusColor(_order?['status']?.toString()).withOpacity(0.15),
                                    borderRadius: BorderRadius.circular(20),
                                  ),
                                  child: Text(_order?['status']?.toString() ?? '-',
                                      style: TextStyle(color: _statusColor(_order?['status']?.toString()), fontWeight: FontWeight.bold)),
                                ),
                              ],
                            ),
                            const SizedBox(height: 12),
                            _row('Raised By', _order?['raised_by_name']?.toString() ?? '-'),
                            _row('PO Date', _order?['po_date']?.toString() ?? '-'),
                            _row('Expected Delivery', _order?['expected_delivery']?.toString() ?? 'N/A'),
                            _row('Total Items', '${_order?['total_items'] ?? 0}'),
                            _row('Total Value', 'RM ${_order?['total_value'] ?? 0}'),
                            if ((_order?['notes'] ?? '').toString().isNotEmpty) _row('Notes', _order?['notes'].toString() ?? ''),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(height: 16),
                    const Text('Line Items', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                    const SizedBox(height: 8),
                    if (_lineItems.isEmpty)
                      const Padding(
                        padding: EdgeInsets.all(12),
                        child: Text('No line items recorded for this PO.', style: TextStyle(color: AppColors.textMuted)),
                      )
                    else
                      ..._lineItems.map((l) {
                        final line = l as Map<String, dynamic>;
                        return Card(
                          child: ListTile(
                            title: Text(line['item_name']?.toString() ?? ''),
                            subtitle: Text('${line['category'] ?? ''} • Qty: ${line['quantity_ordered'] ?? 0}'),
                            trailing: Text('RM ${line['unit_price'] ?? 0}'),
                          ),
                        );
                      }),
                    if (Session.canManageProcurement) ...[
                      const SizedBox(height: 20),
                      const Text('Update Status', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                      const SizedBox(height: 8),
                      Wrap(
                        spacing: 8,
                        runSpacing: 8,
                        children: _statuses.map((s) {
                          final isCurrent = _order?['status'] == s;
                          return ChoiceChip(
                            label: Text(s),
                            selected: isCurrent,
                            onSelected: _updating || isCurrent ? null : (_) => _changeStatus(s),
                            selectedColor: AppColors.primary,
                          );
                        }).toList(),
                      ),
                    ],
                  ],
                ),
    );
  }

  Widget _row(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 4),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(width: 130, child: Text(label, style: const TextStyle(color: AppColors.textMuted))),
          Expanded(child: Text(value, style: const TextStyle(fontWeight: FontWeight.w600))),
        ],
      ),
    );
  }
}
