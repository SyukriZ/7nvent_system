import 'package:flutter/material.dart';
import 'package:image_picker/image_picker.dart';
import 'package:permission_handler/permission_handler.dart';
import 'package:webview_flutter/webview_flutter.dart';
import 'package:webview_flutter_android/webview_flutter_android.dart';

import '../core/app_config.dart';

class WebViewScreen extends StatefulWidget {
  const WebViewScreen({super.key});

  @override
  State<WebViewScreen> createState() => _WebViewScreenState();
}

class _WebViewScreenState extends State<WebViewScreen> {
  late final WebViewController _controller;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _requestCameraPermission();
    _controller = WebViewController(
      // Without this, Android WebView silently denies every
      // navigator.mediaDevices.getUserMedia() call from the page's JS —
      // the scan overlay UI still shows (it's just CSS), but no real
      // camera stream ever starts, so nothing is ever there to decode
      // and "flip camera" has no stream to switch. Granting here is what
      // actually lets the QR/barcode scanner's camera work at all.
      onPermissionRequest: (request) => request.grant(),
    )
      ..setJavaScriptMode(JavaScriptMode.unrestricted)
      ..setBackgroundColor(const Color(0x00000000))
      ..setNavigationDelegate(
        NavigationDelegate(
          onPageStarted: (_) => setState(() => _isLoading = true),
          onPageFinished: (_) => setState(() => _isLoading = false),
        ),
      )
      ..loadRequest(Uri.parse(AppConfig.webAppUrl));

    // Android WebView has no built-in file chooser UI — <input type="file">
    // (the "Upload Image" QR button, and the item photo upload button) does
    // nothing unless the host app supplies one via setOnShowFileSelector.
    if (_controller.platform is AndroidWebViewController) {
      final androidController = _controller.platform as AndroidWebViewController;
      androidController.setOnShowFileSelector(_androidFileSelector);
    }
  }

  Future<List<String>> _androidFileSelector(FileSelectorParams params) async {
    final picker = ImagePicker();
    final XFile? file = await picker.pickImage(source: ImageSource.gallery);
    if (file == null) return <String>[];
    return <String>[Uri.file(file.path).toString()];
  }

  Future<void> _requestCameraPermission() async {
    await Permission.camera.request();
  }

  Future<bool> _handleBack() async {
    if (await _controller.canGoBack()) {
      await _controller.goBack();
      return false;
    }
    return true;
  }

  @override
  Widget build(BuildContext context) {
    return PopScope(
      canPop: false,
      onPopInvokedWithResult: (didPop, result) async {
        if (didPop) return;
        if (await _handleBack()) {
          if (context.mounted) Navigator.of(context).maybePop();
        }
      },
      child: Scaffold(
        body: SafeArea(
          child: Stack(
            children: [
              WebViewWidget(controller: _controller),
              if (_isLoading) const Center(child: CircularProgressIndicator()),
            ],
          ),
        ),
      ),
    );
  }
}
