import 'package:flutter/material.dart';
import '../core/api_client.dart';
import '../core/session.dart';
import '../core/theme.dart';
import 'app_drawer.dart';
import 'create_purchase_order_screen.dart';
import 'purchase_order_detail_screen.dart';

class PurchaseOrdersScreen extends StatefulWidget {
  const PurchaseOrdersScreen({super.key});
  @override
  State<PurchaseOrdersScreen> createState() => _PurchaseOrdersScreenState();
}

class _PurchaseOrdersScreenState extends State<PurchaseOrdersScreen> {
  List orders = [];
  bool _loading = true;
  String? _error;
  String _statusFilter = '';

  static const _statuses = ['', 'Pending', 'In Transit', 'Delivered', 'Cancelled'];

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
    final result = await ApiClient.purchaseOrders(status: _statusFilter);
    if (!mounted) return;
    setState(() {
      _loading = false;
      if (result.success) {
        orders = result.body['orders'] ?? [];
      } else {
        _error = result.message;
      }
    });
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
      drawer: const AppDrawer(),
      appBar: AppBar(title: const Text('Purchase Orders')),
      floatingActionButton: Session.canManageProcurement
          ? FloatingActionButton(
              backgroundColor: AppColors.primary,
              onPressed: () async {
                final created = await Navigator.of(context).push(MaterialPageRoute(builder: (_) => const CreatePurchaseOrderScreen()));
                if (created == true) _load();
              },
              child: const Icon(Icons.add),
            )
          : null,
      body: Column(
        children: [
          SizedBox(
            height: 44,
            child: ListView(
              scrollDirection: Axis.horizontal,
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
              children: _statuses.map((s) {
                final selected = _statusFilter == s;
                return Padding(
                  padding: const EdgeInsets.only(right: 8),
                  child: ChoiceChip(
                    label: Text(s.isEmpty ? 'All' : s),
                    selected: selected,
                    selectedColor: AppColors.primary,
                    onSelected: (_) {
                      setState(() => _statusFilter = s);
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
                    : orders.isEmpty
                        ? const Center(child: Text('No purchase orders found.', style: TextStyle(color: AppColors.textMuted)))
                        : RefreshIndicator(
                            onRefresh: _load,
                            child: ListView.builder(
                              padding: const EdgeInsets.symmetric(horizontal: 12),
                              itemCount: orders.length,
                              itemBuilder: (context, i) {
                                final po = orders[i] as Map<String, dynamic>;
                                return Card(
                                  child: ListTile(
                                    title: Text(po['po_number']?.toString() ?? ''),
                                    subtitle: Text('${po['supplier_name'] ?? ''} • ${po['total_items'] ?? 0} items'),
                                    trailing: Column(
                                      mainAxisAlignment: MainAxisAlignment.center,
                                      crossAxisAlignment: CrossAxisAlignment.end,
                                      children: [
                                        Text('RM ${po['total_value'] ?? 0}', style: const TextStyle(fontWeight: FontWeight.bold)),
                                        Text(po['status']?.toString() ?? '-',
                                            style: TextStyle(color: _statusColor(po['status']?.toString()), fontSize: 12, fontWeight: FontWeight.bold)),
                                      ],
                                    ),
                                    onTap: () => Navigator.of(context).push(
                                      MaterialPageRoute(builder: (_) => PurchaseOrderDetailScreen(poId: po['po_id'] as int)),
                                    ).then((_) => _load()),
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
}
