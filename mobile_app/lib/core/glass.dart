import 'dart:ui';
import 'package:flutter/material.dart';
import 'theme.dart';

/// Reusable "liquid glass" surface — BackdropFilter blur + translucent fill +
/// hairline border + soft shadow, matching `.stat-card`/`.liquid-glass` in
/// resources/views/layouts/app.php. [blur] can be disabled for long lists
/// (each BackdropFilter is a real GPU cost — fine for a handful of cards on
/// screen, wasteful for 50 list rows) while still keeping the glass color/
/// border so the list still reads as part of the same visual language.
class GlassCard extends StatelessWidget {
  final Widget child;
  final EdgeInsetsGeometry padding;
  final double borderRadius;
  final bool blur;
  final bool strong;
  final EdgeInsetsGeometry? margin;

  const GlassCard({
    super.key,
    required this.child,
    this.padding = const EdgeInsets.all(16),
    this.borderRadius = 16,
    this.blur = true,
    this.strong = false,
    this.margin,
  });

  @override
  Widget build(BuildContext context) {
    final fill = strong ? AppColors.glassBgStrong : AppColors.glassBg;
    final content = Container(
      padding: padding,
      decoration: BoxDecoration(
        color: fill,
        borderRadius: BorderRadius.circular(borderRadius),
        border: Border.all(color: AppColors.glassBorder),
        boxShadow: [
          BoxShadow(color: AppColors.glassShadow, blurRadius: 24, offset: const Offset(0, 8)),
        ],
      ),
      child: child,
    );

    if (!blur) {
      return Padding(padding: margin ?? EdgeInsets.zero, child: content);
    }

    return Padding(
      padding: margin ?? EdgeInsets.zero,
      child: ClipRRect(
        borderRadius: BorderRadius.circular(borderRadius),
        child: BackdropFilter(
          filter: ImageFilter.blur(sigmaX: 18, sigmaY: 18),
          child: content,
        ),
      ),
    );
  }
}

/// Ambient background — soft blurred gradient blobs drifting slowly, same
/// spirit as `.animated-background` / `#glassAmbient` on the web app. Kept to
/// 3 blobs with simple looping Tween animations (not literal blob-for-blob
/// parity with the 6-blob web version) to stay cheap on a phone GPU.
class GlassAmbientBackground extends StatefulWidget {
  final Widget child;
  const GlassAmbientBackground({super.key, required this.child});

  @override
  State<GlassAmbientBackground> createState() => _GlassAmbientBackgroundState();
}

class _GlassAmbientBackgroundState extends State<GlassAmbientBackground> with SingleTickerProviderStateMixin {
  late final AnimationController _controller;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(vsync: this, duration: const Duration(seconds: 24))..repeat();
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Stack(
      children: [
        Positioned.fill(
          child: DecoratedBox(
            decoration: BoxDecoration(
              gradient: RadialGradient(
                center: Alignment.topCenter,
                radius: 1.3,
                colors: [Color(0xFF07142B), AppColors.bgDark, AppColors.bgDark],
              ),
            ),
          ),
        ),
        Positioned.fill(
          child: AnimatedBuilder(
            animation: _controller,
            builder: (context, _) {
              final t = _controller.value;
              return Stack(
                children: [
                  _blob(top: -0.12, left: -0.10, size: 0.62, color: AppColors.accentB.withOpacity(0.35), t: t, phase: 0),
                  _blob(top: null, bottom: -0.16, left: -0.06, size: 0.55, color: AppColors.primary.withOpacity(0.30), t: t, phase: 0.4),
                  _blob(top: 0.15, left: null, right: -0.10, size: 0.5, color: AppColors.accentIndigo.withOpacity(0.22), t: t, phase: 0.7),
                ],
              );
            },
          ),
        ),
        widget.child,
      ],
    );
  }

  Widget _blob({double? top, double? bottom, double? left, double? right, required double size, required Color color, required double t, required double phase}) {
    final screen = MediaQuery.of(context).size;
    final dx = 14 * (0.5 - (((t + phase) * 2) % 1.0 - 0.5).abs()) * screen.width / 100;
    final dy = 10 * (0.5 - (((t + phase) * 3) % 1.0 - 0.5).abs()) * screen.height / 100;
    return Positioned(
      top: top != null ? top * screen.height + dy : null,
      bottom: bottom != null ? bottom * screen.height - dy : null,
      left: left != null ? left * screen.width + dx : null,
      right: right != null ? right * screen.width - dx : null,
      width: size * screen.width,
      height: size * screen.width,
      child: ImageFiltered(
        imageFilter: ImageFilter.blur(sigmaX: 60, sigmaY: 60),
        child: DecoratedBox(
          decoration: BoxDecoration(shape: BoxShape.circle, color: color),
        ),
      ),
    );
  }
}

/// Glowing brand wordmark — "7NVENT" with the blue text-shadow pulse used on
/// both the sidebar logo and the login panel in the web app.
class BrandGlow extends StatelessWidget {
  final double fontSize;
  const BrandGlow({super.key, this.fontSize = 28});

  @override
  Widget build(BuildContext context) {
    return RichText(
      text: TextSpan(
        style: TextStyle(
          fontSize: fontSize,
          fontWeight: FontWeight.w900,
          letterSpacing: -1,
          color: Colors.white,
          shadows: const [
            Shadow(color: Color(0x8A0096FF), blurRadius: 18),
            Shadow(color: Color(0x400096FF), blurRadius: 40),
          ],
        ),
        children: [
          TextSpan(
            text: '7',
            style: TextStyle(
              color: AppColors.primary,
              shadows: const [
                Shadow(color: Color(0xE60096FF), blurRadius: 22),
                Shadow(color: Color(0x660096FF), blurRadius: 50),
              ],
            ),
          ),
          const TextSpan(text: 'NVENT'),
        ],
      ),
    );
  }
}
