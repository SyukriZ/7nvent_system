/// Central place for the WebView entry point URL.
///
/// Points at the PC's LAN IP (same one the native ApiClient uses) instead
/// of a Cloudflare quick tunnel — the tunnel URL changes every time
/// `cloudflared` restarts and depends on that process staying alive, which
/// is fragile for a demo. This only needs XAMPP running and the phone on
/// the same Wi-Fi as the PC. Get the current IP with `ipconfig` (look for
/// "IPv4 Address" under the Wi-Fi adapter) if it ever changes.
class AppConfig {
  static const String webAppUrl = 'http://192.168.0.15/7nvent/public/';
}
