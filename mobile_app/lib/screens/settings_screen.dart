import 'package:flutter/material.dart';
import '../core/api_client.dart';
import '../core/theme.dart';
import 'app_drawer.dart';

/// Mirrors SettingsController's tab/field layout exactly (see
/// SettingsApiController::TOGGLES_BY_TAB / TEXTS_BY_TAB) so a value changed
/// here is the same setting_key row the web Settings page reads/writes.
class SettingsScreen extends StatefulWidget {
  const SettingsScreen({super.key});
  @override
  State<SettingsScreen> createState() => _SettingsScreenState();
}

class _ToggleField {
  final String key;
  final String label;
  _ToggleField(this.key, this.label);
}

class _TextField_ {
  final String key;
  final String label;
  _TextField_(this.key, this.label);
}

class _SettingsScreenState extends State<SettingsScreen> with SingleTickerProviderStateMixin {
  late final TabController _tabController;
  Map<String, dynamic> _settings = {};
  bool _loading = true;
  bool _saving = false;
  String? _error;

  static const _tabs = ['general', 'notifications', 'inventory', 'integrations', 'security', 'backup'];
  static const _tabLabels = ['General', 'Notifications', 'Inventory', 'Integrations', 'Security', 'Backup'];

  static final Map<String, List<_ToggleField>> _toggles = {
    'general': [
      _ToggleField('automated_reorder_alerts', 'Automated reorder alerts'),
      _ToggleField('expiry_notifications', 'Expiry notifications'),
      _ToggleField('fifo_enforcement', 'FIFO enforcement'),
      _ToggleField('data_backup_frequency', 'Daily data backup'),
      _ToggleField('pdpa_compliance_mode', 'PDPA compliance mode'),
    ],
    'notifications': [
      _ToggleField('notif_low_stock', 'Low stock notifications'),
      _ToggleField('notif_expiry', 'Expiry notifications'),
      _ToggleField('notif_po_update', 'PO update notifications'),
    ],
    'inventory': [
      _ToggleField('inv_adjustment_approval', 'Require adjustment approval'),
      _ToggleField('inv_auto_po', 'Auto-generate PO from alerts'),
      _ToggleField('fifo_enforcement', 'FIFO enforcement'),
    ],
  };

  static final Map<String, List<_TextField_>> _texts = {
    'notifications': [
      _TextField_('notif_email_recipient', 'Notification email recipient'),
      _TextField_('notif_frequency', 'Notification frequency'),
      _TextField_('notif_threshold_pct', 'Threshold (%)'),
    ],
    'inventory': [
      _TextField_('inv_expiry_warning_days', 'Expiry warning (days)'),
      _TextField_('inv_warning_threshold', 'Warning threshold'),
      _TextField_('inv_critical_threshold', 'Critical threshold'),
    ],
    'integrations': [
      _TextField_('smtp_host', 'SMTP host'),
      _TextField_('smtp_port', 'SMTP port'),
      _TextField_('smtp_username', 'SMTP username'),
      _TextField_('smtp_password', 'SMTP password'),
      _TextField_('scanner_type', 'Scanner type'),
      _TextField_('cloud_backup_url', 'Cloud backup URL'),
    ],
    'security': [
      _TextField_('security_session_timeout', 'Session timeout (min)'),
      _TextField_('security_min_password', 'Min password length'),
      _TextField_('security_max_attempts', 'Max login attempts'),
      _TextField_('security_lockout_duration', 'Lockout duration (min)'),
    ],
    'backup': [
      _TextField_('backup_frequency', 'Backup frequency'),
      _TextField_('backup_retention_days', 'Retention (days)'),
      _TextField_('backup_storage', 'Backup storage'),
    ],
  };

  final Map<String, TextEditingController> _controllers = {};

  @override
  void initState() {
    super.initState();
    _tabController = TabController(length: _tabs.length, vsync: this);
    _load();
  }

  @override
  void dispose() {
    _tabController.dispose();
    for (final c in _controllers.values) c.dispose();
    super.dispose();
  }

  Future<void> _load() async {
    setState(() {
      _loading = true;
      _error = null;
    });
    final result = await ApiClient.settings();
    if (!mounted) return;
    setState(() {
      _loading = false;
      if (result.success) {
        _settings = Map<String, dynamic>.from(result.body['settings'] ?? {});
        for (final list in _texts.values) {
          for (final f in list) {
            _controllers[f.key] = TextEditingController(text: _settings[f.key]?.toString() ?? '');
          }
        }
      } else {
        _error = result.message;
      }
    });
  }

  bool _toggleValue(String key) => _settings[key] == '1' || _settings[key] == true;

  Future<void> _saveTab(String tab) async {
    setState(() => _saving = true);
    final fields = <String, dynamic>{};
    for (final t in _toggles[tab] ?? []) {
      fields[t.key] = _toggleValue(t.key);
    }
    for (final t in _texts[tab] ?? []) {
      fields[t.key] = _controllers[t.key]?.text ?? '';
    }
    final result = await ApiClient.settingsUpdate(tab, fields);
    if (!mounted) return;
    setState(() => _saving = false);
    ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(result.message)));
    if (result.success) _load();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      drawer: const AppDrawer(),
      appBar: AppBar(
        title: const Text('Settings'),
        bottom: TabBar(
          controller: _tabController,
          isScrollable: true,
          tabs: _tabLabels.map((l) => Tab(text: l)).toList(),
        ),
      ),
      body: _loading
          ? const Center(child: CircularProgressIndicator(color: AppColors.primary))
          : _error != null
              ? Center(child: Text(_error!, style: const TextStyle(color: AppColors.critical)))
              : TabBarView(
                  controller: _tabController,
                  children: _tabs.map((tab) => _buildTab(tab)).toList(),
                ),
    );
  }

  Widget _buildTab(String tab) {
    final toggles = _toggles[tab] ?? [];
    final texts = _texts[tab] ?? [];
    return ListView(
      padding: const EdgeInsets.all(16),
      children: [
        ...toggles.map((t) => SwitchListTile(
              title: Text(t.label),
              value: _toggleValue(t.key),
              activeThumbColor: AppColors.primary,
              onChanged: (v) => setState(() => _settings[t.key] = v),
            )),
        ...texts.map((t) => Padding(
              padding: const EdgeInsets.symmetric(vertical: 6),
              child: TextField(
                controller: _controllers[t.key],
                obscureText: t.key == 'smtp_password',
                decoration: InputDecoration(labelText: t.label),
              ),
            )),
        const SizedBox(height: 20),
        ElevatedButton(
          onPressed: _saving ? null : () => _saveTab(tab),
          child: _saving
              ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
              : const Text('Save'),
        ),
        if (tab == 'backup') ...[
          const SizedBox(height: 12),
          OutlinedButton(
            onPressed: () async {
              final result = await ApiClient.settingsManualBackup();
              if (!mounted) return;
              ScaffoldMessenger.of(context).showSnackBar(
                SnackBar(content: Text(result.success ? 'Backup run at ${result.body['time']}' : result.message)),
              );
            },
            child: const Text('Run Manual Backup Now'),
          ),
        ],
      ],
    );
  }
}
