import 'package:flutter/material.dart';
import '../core/api_client.dart';
import '../core/theme.dart';

/// Add / quick-add item form. Reused by both the Inventory tab's "+" button
/// and the QR scanner's "not recognized -> add as new item" flow (which
/// prefills [initialCode]).
class AddItemScreen extends StatefulWidget {
  final String? initialCode;
  const AddItemScreen({super.key, this.initialCode});

  @override
  State<AddItemScreen> createState() => _AddItemScreenState();
}

class _AddItemScreenState extends State<AddItemScreen> {
  final _name = TextEditingController();
  final _code = TextEditingController();
  final _quantity = TextEditingController(text: '0');
  final _parLevel = TextEditingController(text: '0');
  final _unitPrice = TextEditingController(text: '0');

  List categories = [];
  List locations = [];
  List suppliers = [];
  String? _category;
  int? _locationId;
  int? _supplierId;

  bool _loadingMeta = true;
  bool _submitting = false;
  String? _error;

  @override
  void initState() {
    super.initState();
    _code.text = widget.initialCode ?? '';
    _loadMeta();
  }

  Future<void> _loadMeta() async {
    final result = await ApiClient.inventoryMeta();
    if (!mounted) return;
    setState(() {
      _loadingMeta = false;
      if (result.success) {
        categories = result.body['categories'] ?? [];
        locations = result.body['locations'] ?? [];
        suppliers = result.body['suppliers'] ?? [];
      } else {
        _error = result.message;
      }
    });
  }

  Future<void> _submit() async {
    if (_name.text.trim().isEmpty) {
      setState(() => _error = 'Item name is required.');
      return;
    }
    if (_category == null) {
      setState(() => _error = 'Please select a category.');
      return;
    }
    if (_locationId == null) {
      setState(() => _error = 'Please select a location.');
      return;
    }

    setState(() {
      _submitting = true;
      _error = null;
    });

    final result = await ApiClient.inventoryQuickAdd({
      'item_name': _name.text.trim(),
      'item_code': _code.text.trim(),
      'category': _category,
      'location_id': _locationId,
      'supplier_id': _supplierId,
      'quantity': int.tryParse(_quantity.text) ?? 0,
      'par_level': int.tryParse(_parLevel.text) ?? 0,
      'unit_price': double.tryParse(_unitPrice.text) ?? 0,
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
      appBar: AppBar(title: const Text('Add Item')),
      body: _loadingMeta
          ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
          : SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  TextField(controller: _name, decoration: const InputDecoration(labelText: 'Item Name *')),
                  const SizedBox(height: 12),
                  TextField(controller: _code, decoration: const InputDecoration(labelText: 'Item Code (from QR/barcode)')),
                  const SizedBox(height: 12),
                  DropdownButtonFormField<String>(
                    initialValue: _category,
                    decoration: const InputDecoration(labelText: 'Category *'),
                    items: categories.map<DropdownMenuItem<String>>((c) => DropdownMenuItem(value: c.toString(), child: Text(c.toString()))).toList(),
                    onChanged: (v) => setState(() => _category = v),
                  ),
                  const SizedBox(height: 12),
                  DropdownButtonFormField<int>(
                    initialValue: _locationId,
                    decoration: const InputDecoration(labelText: 'Location *'),
                    items: locations.map<DropdownMenuItem<int>>((l) {
                      final map = l as Map<String, dynamic>;
                      return DropdownMenuItem(value: map['location_id'] as int, child: Text(map['location_name'].toString()));
                    }).toList(),
                    onChanged: (v) => setState(() => _locationId = v),
                  ),
                  const SizedBox(height: 12),
                  DropdownButtonFormField<int>(
                    initialValue: _supplierId,
                    decoration: const InputDecoration(labelText: 'Supplier (optional)'),
                    items: suppliers.map<DropdownMenuItem<int>>((s) {
                      final map = s as Map<String, dynamic>;
                      return DropdownMenuItem(value: map['supplier_id'] as int, child: Text(map['supplier_name'].toString()));
                    }).toList(),
                    onChanged: (v) => setState(() => _supplierId = v),
                  ),
                  const SizedBox(height: 12),
                  Row(
                    children: [
                      Expanded(child: TextField(controller: _quantity, keyboardType: TextInputType.number, decoration: const InputDecoration(labelText: 'Quantity'))),
                      const SizedBox(width: 12),
                      Expanded(child: TextField(controller: _parLevel, keyboardType: TextInputType.number, decoration: const InputDecoration(labelText: 'Par Level'))),
                    ],
                  ),
                  const SizedBox(height: 12),
                  TextField(controller: _unitPrice, keyboardType: const TextInputType.numberWithOptions(decimal: true), decoration: const InputDecoration(labelText: 'Unit Price (RM)')),
                  if (_error != null) ...[
                    const SizedBox(height: 12),
                    Text(_error!, style: const TextStyle(color: AppColors.critical)),
                  ],
                  const SizedBox(height: 24),
                  ElevatedButton(
                    onPressed: _submitting ? null : _submit,
                    child: _submitting
                        ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                        : const Text('Save Item'),
                  ),
                ],
              ),
            ),
    );
  }
}
