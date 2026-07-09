import 'package:flutter/material.dart';
import '../core/theme.dart';

class ItemDetailScreen extends StatelessWidget {
  final Map<String, dynamic> item;
  const ItemDetailScreen({super.key, required this.item});

  Color _statusColor(String? status) {
    switch (status) {
      case 'Out of Stock':
        return AppColors.critical;
      case 'Low Stock':
        return AppColors.warning;
      default:
        return AppColors.success;
    }
  }

  Widget _row(String label, String value) {
    return Padding(
      padding: const EdgeInsets.symmetric(vertical: 8),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(width: 120, child: Text(label, style: const TextStyle(color: AppColors.textMuted))),
          Expanded(child: Text(value, style: const TextStyle(fontWeight: FontWeight.w600))),
        ],
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final status = item['status']?.toString();
    return Scaffold(
      appBar: AppBar(title: Text(item['item_name']?.toString() ?? 'Item')),
      body: Padding(
        padding: const EdgeInsets.all(16),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
              decoration: BoxDecoration(
                color: _statusColor(status).withOpacity(0.15),
                borderRadius: BorderRadius.circular(20),
              ),
              child: Text(status ?? '-', style: TextStyle(color: _statusColor(status), fontWeight: FontWeight.bold)),
            ),
            const SizedBox(height: 16),
            Card(
              child: Padding(
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _row('Item Code', item['item_code']?.toString().isNotEmpty == true
                        ? item['item_code'].toString()
                        : '7NV-${item['item_id'].toString().padLeft(4, '0')}'),
                    _row('Category', item['category']?.toString() ?? '-'),
                    _row('Location', item['location_name']?.toString() ?? '-'),
                    _row('Supplier', item['supplier_name']?.toString() ?? '-'),
                    _row('Quantity', '${item['quantity'] ?? 0}'),
                    _row('Par Level', '${item['par_level'] ?? 0}'),
                    _row('Unit Price', 'RM ${item['unit_price'] ?? 0}'),
                    if ((item['expiry_date'] ?? '').toString().isNotEmpty)
                      _row('Expiry Date', item['expiry_date'].toString()),
                  ],
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
