import 'package:flutter/material.dart';
import '../core/api_client.dart';
import '../core/theme.dart';

/// One line the user has added to the PO being drafted.
class _PoLine {
  final int itemId;
  final String itemName;
  final double unitPrice;
  int qty;
  _PoLine({required this.itemId, required this.itemName, required this.unitPrice, this.qty = 1});
}

class CreatePurchaseOrderScreen extends StatefulWidget {
  const CreatePurchaseOrderScreen({super.key});
  @override
  State<CreatePurchaseOrderScreen> createState() => _CreatePurchaseOrderScreenState();
}

class _CreatePurchaseOrderScreenState extends State<CreatePurchaseOrderScreen> {
  List suppliers = [];
  List items = [];
  int? _supplierId;
  final _notes = TextEditingController();
  final List<_PoLine> _lines = [];

  bool _loadingMeta = true;
  bool _submitting = false;
  String? _error;

  @override
  void initState() {
    super.initState();
    _loadMeta();
  }

  Future<void> _loadMeta() async {
    final result = await ApiClient.purchaseOrderMeta();
    if (!mounted) return;
    setState(() {
      _loadingMeta = false;
      if (result.success) {
        suppliers = result.body['suppliers'] ?? [];
        items = result.body['items'] ?? [];
      } else {
        _error = result.message;
      }
    });
  }

  void _addItem() {
    showModalBottomSheet(
      context: context,
      backgroundColor: AppColors.surfaceDark,
      builder: (ctx) => ListView(
        padding: const EdgeInsets.all(12),
        shrinkWrap: true,
        children: items.map((i) {
          final map = i as Map<String, dynamic>;
          return ListTile(
            title: Text(map['item_name']?.toString() ?? ''),
            subtitle: Text('${map['location_name'] ?? ''} • RM ${map['unit_price'] ?? 0}'),
            onTap: () {
              Navigator.pop(ctx);
              setState(() {
                final existing = _lines.where((l) => l.itemId == map['item_id']).toList();
                if (existing.isNotEmpty) {
                  existing.first.qty += 1;
                } else {
                  _lines.add(_PoLine(
                    itemId: map['item_id'] as int,
                    itemName: map['item_name'].toString(),
                    unitPrice: (map['unit_price'] as num?)?.toDouble() ?? 0,
                  ));
                }
              });
            },
          );
        }).toList(),
      ),
    );
  }

  double get _totalValue => _lines.fold(0, (sum, l) => sum + l.qty * l.unitPrice);

  Future<void> _submit() async {
    if (_supplierId == null) {
      setState(() => _error = 'Please select a supplier.');
      return;
    }
    if (_lines.isEmpty) {
      setState(() => _error = 'Please add at least one item.');
      return;
    }
    setState(() {
      _submitting = true;
      _error = null;
    });

    final result = await ApiClient.purchaseOrderStore({
      'supplier_id': _supplierId,
      'po_date': DateTime.now().toIso8601String().split('T').first,
      'expected_delivery': '',
      'notes': _notes.text.trim(),
      'lines': _lines.map((l) => {'item_id': l.itemId, 'qty': l.qty}).toList(),
    });

    if (!mounted) return;
    setState(() => _submitting = false);

    if (result.success) {
      Navigator.of(context).pop(true);
    } else {
      setState(() => _error = result.message);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Create Purchase Order')),
      body: _loadingMeta
          ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
          : ListView(
              padding: const EdgeInsets.all(16),
              children: [
                DropdownButtonFormField<int>(
                  initialValue: _supplierId,
                  decoration: const InputDecoration(labelText: 'Supplier *'),
                  items: suppliers.map<DropdownMenuItem<int>>((s) {
                    final map = s as Map<String, dynamic>;
                    return DropdownMenuItem(value: map['supplier_id'] as int, child: Text(map['supplier_name'].toString()));
                  }).toList(),
                  onChanged: (v) => setState(() => _supplierId = v),
                ),
                const SizedBox(height: 12),
                TextField(controller: _notes, decoration: const InputDecoration(labelText: 'Notes'), maxLines: 2),
                const SizedBox(height: 16),
                Row(
                  children: [
                    const Text('Items', style: TextStyle(fontSize: 16, fontWeight: FontWeight.bold)),
                    const Spacer(),
                    TextButton.icon(onPressed: _addItem, icon: const Icon(Icons.add), label: const Text('Add item')),
                  ],
                ),
                if (_lines.isEmpty)
                  const Padding(
                    padding: EdgeInsets.all(12),
                    child: Text('No items added yet.', style: TextStyle(color: AppColors.textMuted)),
                  )
                else
                  ..._lines.map((l) => Card(
                        child: ListTile(
                          title: Text(l.itemName),
                          subtitle: Text('RM ${l.unitPrice.toStringAsFixed(2)} each'),
                          trailing: Row(
                            mainAxisSize: MainAxisSize.min,
                            children: [
                              IconButton(
                                icon: const Icon(Icons.remove_circle_outline),
                                onPressed: () => setState(() => l.qty > 1 ? l.qty-- : _lines.remove(l)),
                              ),
                              Text('${l.qty}'),
                              IconButton(
                                icon: const Icon(Icons.add_circle_outline),
                                onPressed: () => setState(() => l.qty++),
                              ),
                            ],
                          ),
                        ),
                      )),
                const SizedBox(height: 8),
                Align(
                  alignment: Alignment.centerRight,
                  child: Text('Total: RM ${_totalValue.toStringAsFixed(2)}',
                      style: const TextStyle(fontWeight: FontWeight.bold, fontSize: 16)),
                ),
                if (_error != null) ...[
                  const SizedBox(height: 12),
                  Text(_error!, style: const TextStyle(color: AppColors.critical)),
                ],
                const SizedBox(height: 24),
                ElevatedButton(
                  onPressed: _submitting ? null : _submit,
                  child: _submitting
                      ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                      : const Text('Create Purchase Order'),
                ),
              ],
            ),
    );
  }
}
