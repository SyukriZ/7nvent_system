import 'package:flutter/material.dart';
import '../core/api_client.dart';
import '../core/theme.dart';
import 'app_drawer.dart';
import 'user_form_screen.dart';

class UsersScreen extends StatefulWidget {
  const UsersScreen({super.key});
  @override
  State<UsersScreen> createState() => _UsersScreenState();
}

class _UsersScreenState extends State<UsersScreen> {
  List users = [];
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
    final result = await ApiClient.users();
    if (!mounted) return;
    setState(() {
      _loading = false;
      if (result.success) {
        users = result.body['users'] ?? [];
      } else {
        _error = result.message;
      }
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      drawer: const AppDrawer(),
      appBar: AppBar(title: const Text('Users')),
      floatingActionButton: FloatingActionButton(
        backgroundColor: AppColors.primary,
        onPressed: () async {
          final added = await Navigator.of(context).push(MaterialPageRoute(builder: (_) => const UserFormScreen()));
          if (added == true) _load();
        },
        child: const Icon(Icons.person_add_alt_rounded),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
          : _error != null
              ? Center(child: Text(_error!, style: const TextStyle(color: AppColors.critical)))
              : RefreshIndicator(
                  onRefresh: _load,
                  child: users.isEmpty
                      ? ListView(children: const [
                          Padding(
                            padding: EdgeInsets.all(24),
                            child: Center(child: Text('No users found.', style: TextStyle(color: AppColors.textMuted))),
                          ),
                        ])
                      : ListView.builder(
                          padding: const EdgeInsets.symmetric(horizontal: 12),
                          itemCount: users.length,
                          itemBuilder: (context, i) {
                            final u = users[i] as Map<String, dynamic>;
                            final active = u['status'] == 'Active';
                            return Card(
                              child: ListTile(
                                leading: CircleAvatar(
                                  backgroundColor: (active ? AppColors.success : AppColors.textMuted).withOpacity(0.2),
                                  child: Icon(Icons.person, color: active ? AppColors.success : AppColors.textMuted),
                                ),
                                title: Text(u['full_name']?.toString() ?? ''),
                                subtitle: Text('${u['role_name'] ?? ''} • ${u['department'] ?? '-'}'),
                                trailing: Text(u['status']?.toString() ?? '',
                                    style: TextStyle(color: active ? AppColors.success : AppColors.textMuted, fontSize: 12, fontWeight: FontWeight.bold)),
                                onTap: () async {
                                  final updated = await Navigator.of(context).push(
                                    MaterialPageRoute(builder: (_) => UserFormScreen(userId: u['user_id'] as int)),
                                  );
                                  if (updated == true) _load();
                                },
                              ),
                            );
                          },
                        ),
                ),
    );
  }
}
