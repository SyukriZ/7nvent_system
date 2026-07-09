import 'package:flutter/material.dart';
import '../core/api_client.dart';
import '../core/theme.dart';
import 'app_drawer.dart';
import 'item_detail_screen.dart';
import 'add_item_screen.dart';

class InventoryListScreen extends StatefulWidget {
  const InventoryListScreen({super.key});
  @override
  State<InventoryListScreen> createState() => InventoryListScreenState();
}

class InventoryListScreenState extends State<InventoryListScreen> {
  List items = [];
  bool _loading = true;
  String? _error;
  final _search = TextEditingController();

  @override
  void initState() {
    super.initState();
    refresh();
  }

  // Public so the home shell / scanner flow can trigger a refresh after
  // adding an item from elsewhere in the app.
  Future<void> refresh() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    final result = await ApiClient.inventoryList(search: _search.text.trim());
    if (!mounted) return;
    setState(() {
      _loading = false;
      if (result.success) {
        items = result.body['items'] ?? [];
      } else {
        _error = result.message;
      }
    });
  }

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

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      drawer: const AppDrawer(),
      appBar: AppBar(title: const Text('Inventory')),
      floatingActionButton: FloatingActionButton(
        backgroundColor: AppColors.primary,
        onPressed: () async {
          final added = await Navigator.of(context).push(MaterialPageRoute(builder: (_) => const AddItemScreen()));
          if (added == true) refresh();
        },
        child: const Icon(Icons.add),
      ),
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(12),
            child: TextField(
              controller: _search,
              decoration: InputDecoration(
                hintText: 'Search items...',
                prefixIcon: const Icon(Icons.search),
                suffixIcon: IconButton(icon: const Icon(Icons.arrow_forward), onPressed: refresh),
              ),
              onSubmitted: (_) => refresh(),
            ),
          ),
          Expanded(
            child: _loading
                ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
                : _error != null
                    ? Center(child: Text(_error!, style: const TextStyle(color: AppColors.critical)))
                    : items.isEmpty
                        ? const Center(child: Text('No items found.', style: TextStyle(color: AppColors.textMuted)))
                        : RefreshIndicator(
                            onRefresh: refresh,
                            child: ListView.builder(
                              padding: const EdgeInsets.symmetric(horizontal: 12),
                              itemCount: items.length,
                              itemBuilder: (context, i) {
                                final item = items[i] as Map<String, dynamic>;
                                return Card(
                                  child: ListTile(
                                    leading: CircleAvatar(
                                      backgroundColor: _statusColor(item['status']?.toString()).withOpacity(0.15),
                                      child: Icon(Icons.inventory_2_outlined, color: _statusColor(item['status']?.toString())),
                                    ),
                                    title: Text(item['item_name']?.toString() ?? ''),
                                    subtitle: Text('${item['category'] ?? ''} • Qty: ${item['quantity'] ?? 0}'),
                                    trailing: Text(item['status']?.toString() ?? '', style: TextStyle(color: _statusColor(item['status']?.toString()), fontWeight: FontWeight.bold, fontSize: 12)),
                                    onTap: () => Navigator.of(context).push(MaterialPageRoute(builder: (_) => ItemDetailScreen(item: item))),
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
