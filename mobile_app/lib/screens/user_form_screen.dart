import 'package:flutter/material.dart';
import '../core/api_client.dart';
import '../core/theme.dart';

/// Handles both create (userId == null) and edit (userId != null) — mirrors
/// the web app having separate create.php/edit.php but the same underlying
/// fields, since the API's store()/update() rules match (edit here matches
/// UserController::update()'s narrower field set: role/department/status
/// only — changing username/password requires the web app, same as it
/// always has).
class UserFormScreen extends StatefulWidget {
  final int? userId;
  const UserFormScreen({super.key, this.userId});

  @override
  State<UserFormScreen> createState() => _UserFormScreenState();
}

class _UserFormScreenState extends State<UserFormScreen> {
  final _username = TextEditingController();
  final _password = TextEditingController();
  final _fullName = TextEditingController();
  final _email = TextEditingController();
  final _department = TextEditingController();

  List roles = [];
  int? _roleId;
  String _status = 'Active';

  bool _loading = true;
  bool _submitting = false;
  String? _error;

  bool get _isEdit => widget.userId != null;

  @override
  void initState() {
    super.initState();
    _load();
  }

  Future<void> _load() async {
    final metaResult = await ApiClient.userMeta();
    Map<String, dynamic>? existing;
    if (_isEdit) {
      final detail = await ApiClient.userDetail(widget.userId!);
      if (detail.success) existing = detail.body['user'] as Map<String, dynamic>?;
    }
    if (!mounted) return;
    setState(() {
      _loading = false;
      if (metaResult.success) {
        roles = metaResult.body['roles'] ?? [];
      } else {
        _error = metaResult.message;
      }
      if (existing != null) {
        _username.text = existing['username']?.toString() ?? '';
        _fullName.text = existing['full_name']?.toString() ?? '';
        _email.text = existing['email']?.toString() ?? '';
        _department.text = existing['department']?.toString() ?? '';
        _roleId = existing['role_id'] as int?;
        _status = existing['status']?.toString() ?? 'Active';
      }
    });
  }

  Future<void> _submit() async {
    setState(() {
      _submitting = true;
      _error = null;
    });

    final result = _isEdit
        ? await ApiClient.userUpdate({
            'user_id': widget.userId,
            'role_id': _roleId,
            'department': _department.text.trim(),
            'status': _status,
          })
        : await ApiClient.userStore({
            'username': _username.text.trim(),
            'password': _password.text,
            'full_name': _fullName.text.trim(),
            'email': _email.text.trim(),
            'role_id': _roleId,
            'department': _department.text.trim(),
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
      appBar: AppBar(title: Text(_isEdit ? 'Edit User' : 'Add User')),
      body: _loading
          ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
          : SingleChildScrollView(
              padding: const EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.stretch,
                children: [
                  TextField(
                    controller: _username,
                    enabled: !_isEdit,
                    decoration: const InputDecoration(labelText: 'Username *'),
                  ),
                  if (!_isEdit) ...[
                    const SizedBox(height: 12),
                    TextField(
                      controller: _password,
                      obscureText: true,
                      decoration: const InputDecoration(labelText: 'Password * (min 8 chars)'),
                    ),
                  ],
                  const SizedBox(height: 12),
                  TextField(
                    controller: _fullName,
                    enabled: !_isEdit,
                    decoration: const InputDecoration(labelText: 'Full Name *'),
                  ),
                  const SizedBox(height: 12),
                  TextField(
                    controller: _email,
                    enabled: !_isEdit,
                    keyboardType: TextInputType.emailAddress,
                    decoration: const InputDecoration(labelText: 'Email'),
                  ),
                  const SizedBox(height: 12),
                  TextField(controller: _department, decoration: const InputDecoration(labelText: 'Department')),
                  const SizedBox(height: 12),
                  DropdownButtonFormField<int>(
                    initialValue: _roleId,
                    decoration: const InputDecoration(labelText: 'Role *'),
                    items: roles.map<DropdownMenuItem<int>>((r) {
                      final map = r as Map<String, dynamic>;
                      return DropdownMenuItem(value: map['role_id'] as int, child: Text(map['role_name'].toString()));
                    }).toList(),
                    onChanged: (v) => setState(() => _roleId = v),
                  ),
                  if (_isEdit) ...[
                    const SizedBox(height: 12),
                    DropdownButtonFormField<String>(
                      initialValue: _status,
                      decoration: const InputDecoration(labelText: 'Status'),
                      items: const [
                        DropdownMenuItem(value: 'Active', child: Text('Active')),
                        DropdownMenuItem(value: 'Inactive', child: Text('Inactive')),
                      ],
                      onChanged: (v) => setState(() => _status = v ?? 'Active'),
                    ),
                  ],
                  if (_error != null) ...[
                    const SizedBox(height: 12),
                    Text(_error!, style: const TextStyle(color: AppColors.critical)),
                  ],
                  const SizedBox(height: 24),
                  ElevatedButton(
                    onPressed: _submitting ? null : _submit,
                    child: _submitting
                        ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
                        : Text(_isEdit ? 'Save Changes' : 'Create User'),
                  ),
                ],
              ),
            ),
    );
  }
}
