import 'package:flutter/material.dart';
import 'package:mobile_scanner/mobile_scanner.dart';
import '../core/api_client.dart';
import '../core/theme.dart';
import 'item_detail_screen.dart';
import 'add_item_screen.dart';

/// Live camera barcode/QR scanner. Scope is deliberately narrow (per the
/// assignment ask): it only reads the tracking/item code out of the
/// barcode — no gallery-image upload/decode like the web version has.
/// Scanned code -> look up against /api/inventory/lookup -> either open
/// the matched item, or offer to register it as a new item.
class QrScannerScreen extends StatefulWidget {
  const QrScannerScreen({super.key});
  @override
  State<QrScannerScreen> createState() => _QrScannerScreenState();
}

class _QrScannerScreenState extends State<QrScannerScreen> {
  final MobileScannerController _controller = MobileScannerController();
  bool _handled = false; // guards against firing multiple times per scan

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  Future<void> _onDetect(BarcodeCapture capture) async {
    if (_handled) return;
    final barcode = capture.barcodes.firstOrNull;
    final code = barcode?.rawValue;
    if (code == null || code.isEmpty) return;

    _handled = true;
    await _controller.stop();

    final result = await ApiClient.inventoryLookup(code);
    if (!mounted) return;

    if (result.success && result.body['found'] == true) {
      final item = result.body['item'] as Map<String, dynamic>;
      await Navigator.of(context).pushReplacement(MaterialPageRoute(builder: (_) => ItemDetailScreen(item: item)));
    } else {
      _showNotFoundSheet(code);
    }
  }

  void _showNotFoundSheet(String code) {
    showModalBottomSheet(
      context: context,
      backgroundColor: AppColors.surfaceDark,
      builder: (ctx) => Padding(
        padding: const EdgeInsets.all(24),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: [
            const Icon(Icons.qr_code_2_rounded, size: 40, color: AppColors.warning),
            const SizedBox(height: 12),
            Text('Code "$code" is not in the system.', textAlign: TextAlign.center),
            const SizedBox(height: 20),
            ElevatedButton(
              onPressed: () {
                Navigator.of(ctx).pop();
                Navigator.of(context).pushReplacement(
                  MaterialPageRoute(builder: (_) => AddItemScreen(initialCode: code)),
                );
              },
              child: const Text('Add as New Item'),
            ),
            const SizedBox(height: 8),
            TextButton(
              onPressed: () {
                Navigator.of(ctx).pop();
                setState(() => _handled = false);
                _controller.start();
              },
              child: const Text('Scan Again'),
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(title: const Text('Scan Item Code')),
      body: Stack(
        children: [
          MobileScanner(controller: _controller, onDetect: _onDetect),
          Center(
            child: Container(
              width: 240,
              height: 240,
              decoration: BoxDecoration(
                border: Border.all(color: AppColors.primary, width: 3),
                borderRadius: BorderRadius.circular(16),
              ),
            ),
          ),
          Positioned(
            bottom: 32,
            left: 0,
            right: 0,
            child: Text(
              'Point the camera at an item\'s QR/barcode',
              textAlign: TextAlign.center,
              style: TextStyle(color: Colors.white, shadows: [Shadow(blurRadius: 8, color: Colors.black.withOpacity(0.8))]),
            ),
          ),
        ],
      ),
    );
  }
}

extension _FirstOrNull<T> on List<T> {
  T? get firstOrNull => isEmpty ? null : first;
}
