import 'package:flutter/material.dart';
import 'package:url_launcher/url_launcher.dart';
import '../core/api_client.dart';
import '../core/glass.dart';
import '../core/session.dart';
import '../core/theme.dart';
import 'home_shell.dart';

/// Developer social links — same URLs as the "Connect with Developer" row
/// on resources/views/auth/login.php.
class _SocialLink {
  final IconData icon;
  final Color hoverColor;
  final String url;
  final String label;
  const _SocialLink(this.icon, this.hoverColor, this.url, this.label);
}

const _socialLinks = [
  _SocialLink(Icons.camera_alt_outlined, Color(0xFFDC2743), 'https://www.instagram.com/jooseon_987', 'Instagram'),
  _SocialLink(Icons.facebook, Color(0xFF1877F2), 'https://www.facebook.com/share/17jJYz8xq6/?mibextid=wwXIfr', 'Facebook'),
  _SocialLink(Icons.business_center_outlined, Color(0xFF0A66C2), 'https://www.linkedin.com/in/syukri-zainal-5589142ab', 'LinkedIn'),
  _SocialLink(Icons.code_rounded, Color(0xFF181717), 'https://github.com/SyukriZ', 'GitHub'),
];

/// Demo accounts — same 6 roles/credentials as the quick-fill dropdown on
/// resources/views/auth/login.php ("Click any role to auto-fill").
class _DemoAccount {
  final Color color;
  final String name;
  final String username;
  final String password;
  const _DemoAccount(this.color, this.name, this.username, this.password);
}

const _demoAccounts = [
  _DemoAccount(Color(0xFF818CF8), 'Inventory Manager', 'elizabeth.lee', 'Admin@7nvent'),
  _DemoAccount(Color(0xFFFBBF24), 'Housekeeping Manager', 'alvin.yuan', 'House@7nvent'),
  _DemoAccount(Color(0xFFA78BFA), 'Procurement Officer', 'sarah.qinn', 'PO@7nvent123'),
  _DemoAccount(Color(0xFFF87171), 'IT Administrator', 'abdul.hakim', 'ITadmin@7nvent'),
  _DemoAccount(Color(0xFF4ADE80), 'Hotel GM', 'farah.nabilah', 'GM@7nvent2026'),
  _DemoAccount(Color(0xFFFB923C), 'Supervisor', 'melissa.yee', 'Super@7nvent'),
];

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});
  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _username = TextEditingController();
  final _password = TextEditingController();
  bool _loading = false;
  bool _obscure = true;
  bool _remember = false;
  String? _error;
  Map<String, dynamic>? _liveStats;

  @override
  void initState() {
    super.initState();
    _loadLiveStats();
  }

  Future<void> _loadLiveStats() async {
    final result = await ApiClient.publicStats();
    if (!mounted) return;
    if (result.success) setState(() => _liveStats = result.body);
  }

  Future<void> _openSocial(_SocialLink link) async {
    final uri = Uri.parse(link.url);
    final ok = await launchUrl(uri, mode: LaunchMode.externalApplication);
    if (!ok && mounted) {
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text('Could not open ${link.label}.')));
    }
  }

  void _fillDemo(_DemoAccount a) {
    setState(() {
      _username.text = a.username;
      _password.text = a.password;
    });
  }

  void _showDemoAccounts() {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.transparent,
      builder: (ctx) => GlassCard(
        strong: true,
        margin: const EdgeInsets.all(16),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text('DEMO ACCOUNTS — TAP TO AUTO-FILL',
                style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: AppColors.textFaint, letterSpacing: 1)),
            const SizedBox(height: 8),
            ..._demoAccounts.map((a) => InkWell(
                  onTap: () {
                    Navigator.pop(ctx);
                    _fillDemo(a);
                  },
                  borderRadius: BorderRadius.circular(10),
                  child: Padding(
                    padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 6),
                    child: Row(
                      children: [
                        Container(width: 8, height: 8, decoration: BoxDecoration(color: a.color, shape: BoxShape.circle)),
                        const SizedBox(width: 10),
                        Expanded(child: Text(a.name, style: const TextStyle(fontWeight: FontWeight.w600, fontSize: 13))),
                        Text(a.password, style: TextStyle(fontFamily: 'monospace', fontSize: 11, color: AppColors.accentA)),
                      ],
                    ),
                  ),
                )),
          ],
        ),
      ),
    );
  }

  Future<void> _submit() async {
    if (_username.text.trim().isEmpty || _password.text.isEmpty) {
      setState(() => _error = 'Please enter your username and password.');
      return;
    }
    setState(() {
      _loading = true;
      _error = null;
    });

    final result = await ApiClient.login(_username.text.trim(), _password.text);

    if (!mounted) return;
    setState(() => _loading = false);

    if (result.success) {
      Session.set(result.body['user'] as Map<String, dynamic>?);
      Navigator.of(context).pushReplacement(MaterialPageRoute(builder: (_) => const HomeShell()));
    } else {
      setState(() => _error = result.message);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: AppColors.bgDark,
      body: GlassAmbientBackground(
        child: SafeArea(
          child: SingleChildScrollView(
            padding: const EdgeInsets.all(20),
            child: Column(
              children: [
                const SizedBox(height: 24),
                _brandHeader(),
                const SizedBox(height: 28),
                GlassCard(
                  strong: true,
                  padding: const EdgeInsets.all(24),
                  child: _loginForm(),
                ),
                const SizedBox(height: 20),
                _socialRow(),
                const SizedBox(height: 16),
                const Text(
                  'Session expires after 30 minutes · PDPA Compliant',
                  textAlign: TextAlign.center,
                  style: TextStyle(fontSize: 10, color: AppColors.textFaint),
                ),
                const SizedBox(height: 12),
              ],
            ),
          ),
        ),
      ),
    );
  }

  Widget _brandHeader() {
    return Column(
      children: [
        Container(
          width: 64,
          height: 64,
          decoration: BoxDecoration(
            color: AppColors.primary.withOpacity(0.12),
            borderRadius: BorderRadius.circular(18),
            border: Border.all(color: AppColors.primary.withOpacity(0.35)),
            boxShadow: [BoxShadow(color: AppColors.primary.withOpacity(0.35), blurRadius: 24)],
          ),
          child: const Icon(Icons.business_rounded, color: Color(0xFF4FC3FF), size: 30),
        ),
        const SizedBox(height: 14),
        const BrandGlow(),
        const SizedBox(height: 4),
        const Text('HOTEL INVENTORY MANAGEMENT SYSTEM',
            style: TextStyle(fontSize: 9, color: Colors.white54, letterSpacing: 2)),
        const SizedBox(height: 8),
        const Text('★ ★ ★ ★ ★', style: TextStyle(color: Color(0xFF4FC3FF), fontSize: 13, letterSpacing: 4)),
        const SizedBox(height: 18),
        Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            _miniStat('6', 'Roles'),
            const SizedBox(width: 10),
            _miniStat('10', 'Modules'),
            const SizedBox(width: 10),
            _miniStat('24/7', 'Live'),
          ],
        ),
        const SizedBox(height: 12),
        _liveInventoryCards(),
      ],
    );
  }

  Widget _miniStat(String value, String label) {
    return Container(
      width: 74,
      padding: const EdgeInsets.symmetric(vertical: 10),
      decoration: BoxDecoration(
        color: Colors.white.withOpacity(0.08),
        border: Border.all(color: Colors.white.withOpacity(0.15)),
        borderRadius: BorderRadius.circular(14),
      ),
      child: Column(
        children: [
          Text(value, style: const TextStyle(fontSize: 17, fontWeight: FontWeight.w800, color: Colors.white)),
          const SizedBox(height: 2),
          Text(label, style: const TextStyle(fontSize: 9, color: Colors.white54)),
        ],
      ),
    );
  }

  /// Same 3 "live" figures the web login page shows (public, pre-auth) —
  /// fetched via GET /api/public/stats. Stays blank (no fake zeros flashing)
  /// until the real numbers arrive.
  Widget _liveInventoryCards() {
    if (_liveStats == null) return const SizedBox.shrink();
    final items = _liveStats!['total_items'] ?? 0;
    final alerts = _liveStats!['critical_alerts'] ?? 0;
    final pending = (_liveStats!['pending_value'] as num?)?.round() ?? 0;
    return Column(
      children: [
        _liveRow(const Color(0xFF4ADE80), 'Total Items in Stock', '$items'),
        const SizedBox(height: 6),
        _liveRow(const Color(0xFFF87171), 'Critical Alerts', '$alerts'),
        const SizedBox(height: 6),
        _liveRow(const Color(0xFFFBBF24), 'Pending Orders', 'RM $pending'),
      ],
    );
  }

  Widget _liveRow(Color dot, String label, String value) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
      decoration: BoxDecoration(
        color: Colors.white.withOpacity(0.06),
        border: Border.all(color: Colors.white.withOpacity(0.10)),
        borderRadius: BorderRadius.circular(10),
      ),
      child: Row(
        children: [
          Container(width: 8, height: 8, decoration: BoxDecoration(color: dot, shape: BoxShape.circle, boxShadow: [BoxShadow(color: dot.withOpacity(0.6), blurRadius: 6)])),
          const SizedBox(width: 10),
          Expanded(child: Text(label, style: const TextStyle(fontSize: 11, color: Colors.white60))),
          Text(value, style: const TextStyle(fontSize: 12, fontWeight: FontWeight.bold, color: Colors.white)),
        ],
      ),
    );
  }

  Widget _socialRow() {
    return Column(
      children: [
        Row(
          children: const [
            Expanded(child: Divider(color: Colors.white24)),
            Padding(
              padding: EdgeInsets.symmetric(horizontal: 12),
              child: Text('Connect with Developer', style: TextStyle(fontSize: 11, color: Colors.white38)),
            ),
            Expanded(child: Divider(color: Colors.white24)),
          ],
        ),
        const SizedBox(height: 14),
        Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: _socialLinks.map((link) {
            return Padding(
              padding: const EdgeInsets.symmetric(horizontal: 6),
              child: InkWell(
                onTap: () => _openSocial(link),
                borderRadius: BorderRadius.circular(13),
                child: Container(
                  width: 46,
                  height: 46,
                  decoration: BoxDecoration(
                    color: Colors.white.withOpacity(0.10),
                    border: Border.all(color: Colors.white.withOpacity(0.15)),
                    borderRadius: BorderRadius.circular(13),
                  ),
                  child: Icon(link.icon, color: Colors.white70, size: 20),
                ),
              ),
            );
          }).toList(),
        ),
      ],
    );
  }

  Widget _loginForm() {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        Row(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: const [
                  Text('WELCOME BACK 👋', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF4FC3FF), letterSpacing: 1)),
                  SizedBox(height: 4),
                  Text('Sign In to 7NVENT', style: TextStyle(fontSize: 22, fontWeight: FontWeight.w800, color: Colors.white)),
                  SizedBox(height: 4),
                  Text('Manage your hotel inventory with ease and precision.',
                      style: TextStyle(fontSize: 12, color: Colors.white54)),
                ],
              ),
            ),
            TextButton.icon(
              onPressed: _showDemoAccounts,
              icon: const Icon(Icons.people_outline, size: 16),
              label: const Text('Demo', style: TextStyle(fontSize: 12)),
            ),
          ],
        ),
        const SizedBox(height: 20),
        const Text('USERNAME', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Colors.white54, letterSpacing: 1)),
        const SizedBox(height: 6),
        TextField(
          controller: _username,
          style: const TextStyle(color: Colors.white),
          decoration: const InputDecoration(prefixIcon: Icon(Icons.person_outline, color: Colors.white38)),
        ),
        const SizedBox(height: 16),
        const Text('PASSWORD', style: TextStyle(fontSize: 10, fontWeight: FontWeight.bold, color: Colors.white54, letterSpacing: 1)),
        const SizedBox(height: 6),
        TextField(
          controller: _password,
          obscureText: _obscure,
          style: const TextStyle(color: Colors.white),
          onSubmitted: (_) => _submit(),
          decoration: InputDecoration(
            prefixIcon: const Icon(Icons.lock_outline, color: Colors.white38),
            suffixIcon: IconButton(
              icon: Icon(_obscure ? Icons.visibility_outlined : Icons.visibility_off_outlined, color: Colors.white38),
              onPressed: () => setState(() => _obscure = !_obscure),
            ),
          ),
        ),
        const SizedBox(height: 8),
        Row(
          children: [
            Checkbox(
              value: _remember,
              onChanged: (v) => setState(() => _remember = v ?? false),
              activeColor: AppColors.primary,
              side: const BorderSide(color: Colors.white38),
            ),
            const Text('Remember me', style: TextStyle(fontSize: 12, color: Colors.white54)),
            const Spacer(),
            const Icon(Icons.shield_outlined, size: 14, color: Colors.white24),
            const SizedBox(width: 4),
            const Text('Secure Login', style: TextStyle(fontSize: 11, color: Colors.white24)),
          ],
        ),
        if (_error != null) ...[
          const SizedBox(height: 8),
          Container(
            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
            decoration: BoxDecoration(
              color: AppColors.critical.withOpacity(0.15),
              border: Border.all(color: AppColors.critical.withOpacity(0.3)),
              borderRadius: BorderRadius.circular(10),
            ),
            child: Text(_error!, style: const TextStyle(color: Color(0xFFFCA5A5), fontSize: 12)),
          ),
        ],
        const SizedBox(height: 20),
        ElevatedButton(
          onPressed: _loading ? null : _submit,
          style: ElevatedButton.styleFrom(
            backgroundColor: AppColors.primary,
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            padding: const EdgeInsets.symmetric(vertical: 16),
          ),
          child: _loading
              ? const SizedBox(height: 20, width: 20, child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white))
              : const Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [Icon(Icons.login_rounded, size: 18), SizedBox(width: 8), Text('Sign In', style: TextStyle(fontWeight: FontWeight.bold))],
                ),
        ),
      ],
    );
  }
}
