import 'package:flutter/material.dart';
import '../core/api_client.dart';
import '../core/session.dart';
import '../core/theme.dart';
import 'add_supplier_screen.dart';
import 'app_drawer.dart';

class SuppliersScreen extends StatefulWidget {
  const SuppliersScreen({super.key});
  @override
  State<SuppliersScreen> createState() => _SuppliersScreenState();
}

class _SuppliersScreenState extends State<SuppliersScreen> {
  List suppliers = [];
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
    final result = await ApiClient.suppliers();
    if (!mounted) return;
    setState(() {
      _loading = false;
      if (result.success) {
        suppliers = result.body['suppliers'] ?? [];
      } else {
        _error = result.message;
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      drawer: const AppDrawer(),
      appBar: AppBar(title: const Text('Suppliers')),
      floatingActionButton: Session.canManageProcurement
          ? FloatingActionButton(
              backgroundColor: AppColors.primary,
              onPressed: () async {
                final added = await Navigator.of(context).push(MaterialPageRoute(builder: (_) => const AddSupplierScreen()));
                if (added == true) _load();
              },
              child: const Icon(Icons.add),
            )
          : null,
      body: _loading
          ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
          : _error != null
              ? Center(child: Text(_error!, style: const TextStyle(color: AppColors.critical)))
              : RefreshIndicator(
                  onRefresh: _load,
                  child: suppliers.isEmpty
                      ? ListView(children: const [
                          Padding(
                            padding: EdgeInsets.all(24),
                            child: Center(child: Text('No suppliers found.', style: TextStyle(color: AppColors.textMuted))),
                          ),
                        ])
                      : ListView.builder(
                          padding: const EdgeInsets.all(12),
                          itemCount: suppliers.length,
                          itemBuilder: (context, i) {
                            final s = suppliers[i] as Map<String, dynamic>;
                            final rating = (s['rating'] as num?)?.toDouble() ?? 0;
                            return Card(
                              child: ListTile(
                                leading: const CircleAvatar(
                                  backgroundColor: AppColors.primary,
                                  child: Icon(Icons.local_shipping_outlined, color: Colors.white),
                                ),
                                title: Text(s['supplier_name']?.toString() ?? ''),
                                subtitle: Text('${s['category'] ?? '-'} • ${s['contact_person'] ?? '-'} • ${s['total_orders'] ?? 0} orders'),
                                trailing: Row(
                                  mainAxisSize: MainAxisSize.min,
                                  children: [
                                    const Icon(Icons.star_rounded, size: 16, color: AppColors.warning),
                                    Text(rating.toStringAsFixed(1)),
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
