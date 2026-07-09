import 'package:flutter/material.dart';
import '../core/api_client.dart';
import '../core/theme.dart';

class AddSupplierScreen extends StatefulWidget {
  const AddSupplierScreen({super.key});
  @override
  State<AddSupplierScreen> createState() => _AddSupplierScreenState();
}

class _AddSupplierScreenState extends State<AddSupplierScreen> {
  final _name = TextEditingController();
  final _category = TextEditingController();
  final _contact = TextEditingController();
  final _phone = TextEditingController();
  final _email = TextEditingController();
  final _rating = TextEditingController(text: '0');
  final _leadTime = TextEditingController(text: '0');

  bool _submitting = false;
  String? _error;

  Future<void> _submit() async {
    if (_name.text.trim().isEmpty) {
      setState(() => _error = 'Supplier name is required.');
      return;
    }
    setState(() {
      _submitting = true;
      _error = null;
    });

    final result = await ApiClient.supplierStore({
      'supplier_name': _name.text.trim(),
      'category': _category.text.trim(),
      'contact_person': _contact.text.trim(),
      'phone': _phone.text.trim(),
      'email': _email.text.trim(),
      'rating': double.tryParse(_rating.text) ?? 0,
      'lead_time_days': double.tryParse(_leadTime.text) ?? 0,
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
      appBar: AppBar(title: const Text('Add Supplier')),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            TextField(controller: _name, decoration: const InputDecoration(labelText: 'Supplier Name *')),
            const SizedBox(height: 12),
            TextField(controller: _category, decoration: const InputDecoration(labelText: 'Category')),
            const SizedBox(height: 12),
            TextField(controller: _contact, decoration: const InputDecoration(labelText: 'Contact Person')),
            const SizedBox(height: 12),
            TextField(controller: _phone, keyboardType: TextInputType.phone, decoration: const InputDecoration(labelText: 'Phone')),
            const SizedBox(height: 12),
            TextField(controller: _email, keyboardType: TextInputType.emailAddress, decoration: const InputDecoration(labelText: 'Email')),
            const SizedBox(height: 12),
            Row(
              children: [
                Expanded(
                  child: TextField(
                    controller: _rating,
                    keyboardType: const TextInputType.numberWithOptions(decimal: true),
                    decoration: const InputDecoration(labelText: 'Rating (0-5)'),
                  ),
                ),
                const SizedBox(width: 12),
                Expanded(
                  child: TextField(
                    controller: _leadTime,
                    keyboardType: TextInputType.number,
                    decoration: const InputDecoration(labelText: 'Lead Time (days)'),
                  ),
                ),
              ],
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
                  : const Text('Save Supplier'),
            ),
          ],
        ),
      ),
    );
  }
}
