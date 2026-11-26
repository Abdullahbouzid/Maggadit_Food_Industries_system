import 'package:cached_network_image/cached_network_image.dart';
import 'package:flutter/material.dart';

import '../theme/app_theme.dart';

class NovaCard extends StatelessWidget {
  const NovaCard({
    super.key,
    required this.child,
    this.padding = const EdgeInsets.all(14),
    this.margin = EdgeInsets.zero,
    this.onTap,
    this.color = C.surface,
    this.radius = 18,
    this.border = true,
  });

  final Widget child;
  final EdgeInsets padding;
  final EdgeInsets margin;
  final VoidCallback? onTap;
  final Color color;
  final double radius;
  final bool border;

  @override
  Widget build(BuildContext context) {
    final content = Container(
      margin: margin,
      padding: padding,
      decoration: BoxDecoration(
        color: color,
        borderRadius: BorderRadius.circular(radius),
        border: border ? Border.all(color: C.border) : null,
        boxShadow: Sh.card,
      ),
      child: child,
    );
    if (onTap == null) return content;
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(radius),
      child: content,
    );
  }
}

class PrimaryButton extends StatelessWidget {
  const PrimaryButton({
    super.key,
    required this.label,
    this.onPressed,
    this.icon,
    this.loading = false,
    this.expanded = true,
    this.height = 48,
    this.color = C.primary,
  });

  final String label;
  final VoidCallback? onPressed;
  final IconData? icon;
  final bool loading;
  final bool expanded;
  final double height;
  final Color color;

  @override
  Widget build(BuildContext context) {
    final disabled = onPressed == null || loading;
    final button = SizedBox(
      height: height,
      child: FilledButton(
        onPressed: disabled ? null : onPressed,
        style: FilledButton.styleFrom(
          backgroundColor: color,
          disabledBackgroundColor: color.withValues(alpha: 0.4),
          foregroundColor: Colors.white,
          elevation: 0,
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
        ),
        child: loading
            ? const SizedBox(
                width: 18,
                height: 18,
                child: CircularProgressIndicator(strokeWidth: 2, color: Colors.white),
              )
            : Row(
                mainAxisSize: MainAxisSize.min,
                children: [
                  if (icon != null) ...[
                    Icon(icon, size: 17, color: Colors.white),
                    const SizedBox(width: 8),
                  ],
                  Flexible(
                    child: Text(
                      label,
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: ts(13, weight: FontWeight.w700, color: Colors.white),
                    ),
                  ),
                ],
              ),
      ),
    );
    return expanded ? SizedBox(width: double.infinity, child: button) : button;
  }
}

class GhostButton extends StatelessWidget {
  const GhostButton({
    super.key,
    required this.label,
    this.onPressed,
    this.icon,
    this.color = C.primary,
    this.height = 44,
  });

  final String label;
  final VoidCallback? onPressed;
  final IconData? icon;
  final Color color;
  final double height;

  @override
  Widget build(BuildContext context) {
    return SizedBox(
      height: height,
      child: OutlinedButton(
        onPressed: onPressed,
        style: OutlinedButton.styleFrom(
          foregroundColor: color,
          side: BorderSide(color: color.withValues(alpha: 0.35)),
          shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(14)),
          backgroundColor: Colors.white,
        ),
        child: Row(
          mainAxisSize: MainAxisSize.min,
          children: [
            if (icon != null) ...[
              Icon(icon, size: 16, color: color),
              const SizedBox(width: 6),
            ],
            Text(label, style: ts(12, weight: FontWeight.w600, color: color)),
          ],
        ),
      ),
    );
  }
}

class Tag extends StatelessWidget {
  const Tag({
    super.key,
    required this.text,
    this.color = C.primaryDark,
    this.background = C.purpleSoft,
    this.icon,
  });

  final String text;
  final Color color;
  final Color background;
  final IconData? icon;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 4),
      decoration: BoxDecoration(
        color: background,
        borderRadius: BorderRadius.circular(30),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          if (icon != null) ...[
            Icon(icon, size: 11, color: color),
            const SizedBox(width: 4),
          ],
          Text(text, style: ts(9.5, weight: FontWeight.w700, color: color)),
        ],
      ),
    );
  }
}

/// Maps the API `status_class` / `color` strings to design tokens.
class StatusPalette {
  const StatusPalette._();

  static String _normalize(String key) {
    final value = key.toLowerCase();
    if (value.contains('emerald') || value.contains('green') || value.contains('success')) {
      return 'success';
    }
    if (value.contains('amber') || value.contains('warning') || value.contains('orange')) {
      return 'warning';
    }
    if (value.contains('red') || value.contains('danger') || value.contains('rose')) {
      return 'danger';
    }
    if (value.contains('blue') || value.contains('info') || value.contains('sky')) {
      return 'info';
    }
    if (value.contains('purple') || value.contains('indigo') || value.contains('primary')) {
      return 'primary';
    }
    return 'muted';
  }

  static Color color(String key) {
    switch (_normalize(key)) {
      case 'success':
      case 'green':
        return C.success;
      case 'warning':
      case 'amber':
      case 'orange':
        return C.warning;
      case 'danger':
      case 'red':
        return C.danger;
      case 'info':
      case 'blue':
        return C.info;
      case 'primary':
      case 'purple':
        return C.primaryDark;
      default:
        return C.inkSoft;
    }
  }

  static Color background(String key) {
    switch (_normalize(key)) {
      case 'success':
      case 'green':
        return C.successSoft;
      case 'warning':
      case 'amber':
      case 'orange':
        return C.warningSoft;
      case 'danger':
      case 'red':
        return C.dangerSoft;
      case 'info':
      case 'blue':
        return C.infoSoft;
      case 'primary':
      case 'purple':
        return C.purpleSoft;
      default:
        return const Color(0xFFF1F5F9);
    }
  }
}

class SectionTitle extends StatelessWidget {
  const SectionTitle({
    super.key,
    required this.title,
    this.actionLabel,
    this.onAction,
    this.padding = const EdgeInsets.fromLTRB(0, 4, 0, 10),
  });

  final String title;
  final String? actionLabel;
  final VoidCallback? onAction;
  final EdgeInsets padding;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: padding,
      child: Row(
        children: [
          Text(title, style: ts(14, weight: FontWeight.w700)),
          const Spacer(),
          if (actionLabel != null)
            GestureDetector(
              onTap: onAction,
              behavior: HitTestBehavior.opaque,
              child: Row(
                children: [
                  Text(actionLabel!, style: ts(10.5, weight: FontWeight.w600, color: C.primaryDark)),
                  const SizedBox(width: 2),
                  const Icon(Icons.chevron_left_rounded, size: 16, color: C.primaryDark),
                ],
              ),
            ),
        ],
      ),
    );
  }
}

class EmptyState extends StatelessWidget {
  const EmptyState({
    super.key,
    this.icon = Icons.inbox_rounded,
    required this.title,
    this.message,
    this.actionLabel,
    this.onAction,
  });

  final IconData icon;
  final String title;
  final String? message;
  final String? actionLabel;
  final VoidCallback? onAction;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 32, vertical: 48),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 74,
              height: 74,
              decoration: const BoxDecoration(color: C.purpleSoft, shape: BoxShape.circle),
              child: Icon(icon, size: 32, color: C.primary),
            ),
            const SizedBox(height: 16),
            Text(title, textAlign: TextAlign.center, style: ts(14, weight: FontWeight.w700)),
            if (message != null) ...[
              const SizedBox(height: 6),
              Text(message!,
                  textAlign: TextAlign.center, style: ts(11, color: C.muted, height: 1.6)),
            ],
            if (actionLabel != null) ...[
              const SizedBox(height: 18),
              GhostButton(label: actionLabel!, onPressed: onAction),
            ],
          ],
        ),
      ),
    );
  }
}

class ErrorState extends StatelessWidget {
  const ErrorState({super.key, required this.message, this.onRetry});

  final String message;
  final VoidCallback? onRetry;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 28, vertical: 40),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            const Icon(Icons.cloud_off_rounded, size: 42, color: C.muted),
            const SizedBox(height: 14),
            Text(message,
                textAlign: TextAlign.center, style: ts(12, color: C.inkSoft, height: 1.7)),
            if (onRetry != null) ...[
              const SizedBox(height: 16),
              GhostButton(label: 'إعادة المحاولة', icon: Icons.refresh_rounded, onPressed: onRetry),
            ],
          ],
        ),
      ),
    );
  }
}

class LoadingState extends StatelessWidget {
  const LoadingState({super.key, this.label});

  final String? label;

  @override
  Widget build(BuildContext context) {
    return Center(
      child: Column(
        mainAxisSize: MainAxisSize.min,
        children: [
          const SizedBox(
            width: 26,
            height: 26,
            child: CircularProgressIndicator(strokeWidth: 2.4),
          ),
          if (label != null) ...[
            const SizedBox(height: 12),
            Text(label!, style: ts(11, color: C.muted)),
          ],
        ],
      ),
    );
  }
}

/// `FutureBuilder` wrapper with the three standard states.
class FutureView<T> extends StatelessWidget {
  const FutureView({
    super.key,
    required this.future,
    required this.builder,
    this.emptyCheck,
    this.emptyTitle = 'لا توجد بيانات',
    this.emptyMessage,
    this.emptyIcon = Icons.inbox_rounded,
    this.onRetry,
    this.loadingLabel,
  });

  final Future<T> future;
  final Widget Function(BuildContext context, T data) builder;
  final bool Function(T data)? emptyCheck;
  final String emptyTitle;
  final String? emptyMessage;
  final IconData emptyIcon;
  final VoidCallback? onRetry;
  final String? loadingLabel;

  @override
  Widget build(BuildContext context) {
    return FutureBuilder<T>(
      future: future,
      builder: (context, snapshot) {
        if (snapshot.connectionState == ConnectionState.waiting) {
          return LoadingState(label: loadingLabel);
        }
        if (snapshot.hasError) {
          return ErrorState(message: snapshot.error.toString(), onRetry: onRetry);
        }
        final data = snapshot.data;
        if (data == null) {
          return EmptyState(icon: emptyIcon, title: emptyTitle, message: emptyMessage);
        }
        if (emptyCheck != null && emptyCheck!(data)) {
          return EmptyState(icon: emptyIcon, title: emptyTitle, message: emptyMessage);
        }
        return builder(context, data);
      },
    );
  }
}

class RemoteImage extends StatelessWidget {
  const RemoteImage({
    super.key,
    this.url,
    this.width,
    this.height,
    this.radius = 14,
    this.icon = Icons.local_mall_rounded,
    this.fit = BoxFit.cover,
  });

  final String? url;
  final double? width;
  final double? height;
  final double radius;
  final IconData icon;
  final BoxFit fit;

  @override
  Widget build(BuildContext context) {
    final placeholder = Container(
      width: width,
      height: height,
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(radius),
        gradient: const LinearGradient(
          begin: Alignment.topRight,
          end: Alignment.bottomLeft,
          colors: [Color(0xFFEDE4F6), Color(0xFFF7F3FB)],
        ),
      ),
      child: Icon(icon, size: (width ?? 60) * 0.36, color: C.primary.withValues(alpha: 0.55)),
    );

    if (url == null || url!.isEmpty) return placeholder;

    return ClipRRect(
      borderRadius: BorderRadius.circular(radius),
      child: CachedNetworkImage(
        imageUrl: url!,
        width: width,
        height: height,
        fit: fit,
        placeholder: (_, __) => placeholder,
        errorWidget: (_, __, ___) => placeholder,
      ),
    );
  }
}

class QtyStepper extends StatelessWidget {
  const QtyStepper({
    super.key,
    required this.quantity,
    this.onIncrement,
    this.onDecrement,
    this.compact = false,
  });

  final double quantity;
  final VoidCallback? onIncrement;
  final VoidCallback? onDecrement;
  final bool compact;

  @override
  Widget build(BuildContext context) {
    final size = compact ? 26.0 : 30.0;
    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 3),
      decoration: BoxDecoration(
        color: C.purpleSoft,
        borderRadius: BorderRadius.circular(30),
      ),
      child: Row(
        mainAxisSize: MainAxisSize.min,
        children: [
          _RoundIcon(icon: Icons.remove_rounded, size: size, onTap: onDecrement),
          SizedBox(
            width: compact ? 28 : 36,
            child: Text(
              quantity == quantity.roundToDouble()
                  ? quantity.toInt().toString()
                  : quantity.toStringAsFixed(1),
              textAlign: TextAlign.center,
              style: ts(compact ? 11 : 12, weight: FontWeight.w700),
            ),
          ),
          _RoundIcon(icon: Icons.add_rounded, size: size, onTap: onIncrement),
        ],
      ),
    );
  }
}

class _RoundIcon extends StatelessWidget {
  const _RoundIcon({required this.icon, required this.size, this.onTap});

  final IconData icon;
  final double size;
  final VoidCallback? onTap;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      customBorder: const CircleBorder(),
      child: Container(
        width: size,
        height: size,
        decoration: const BoxDecoration(color: Colors.white, shape: BoxShape.circle),
        child: Icon(icon, size: size * 0.55, color: C.primaryDark),
      ),
    );
  }
}

class ReadMoreText extends StatefulWidget {
  const ReadMoreText({super.key, required this.text, this.maxLines = 4});

  final String text;
  final int maxLines;

  @override
  State<ReadMoreText> createState() => _ReadMoreTextState();
}

class _ReadMoreTextState extends State<ReadMoreText> {
  bool _expanded = false;

  @override
  Widget build(BuildContext context) {
    final style = ts(11.5, color: C.inkSoft, height: 1.85);
    return LayoutBuilder(
      builder: (context, constraints) {
        final painter = TextPainter(
          text: TextSpan(text: widget.text, style: style),
          maxLines: widget.maxLines,
          textDirection: Directionality.of(context),
        )..layout(maxWidth: constraints.maxWidth);
        final overflows = painter.didExceedMaxLines;
        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Text(
              widget.text,
              style: style,
              maxLines: _expanded ? null : widget.maxLines,
              overflow: _expanded ? TextOverflow.visible : TextOverflow.ellipsis,
            ),
            if (overflows)
              GestureDetector(
                onTap: () => setState(() => _expanded = !_expanded),
                child: Padding(
                  padding: const EdgeInsets.only(top: 6),
                  child: Text(
                    _expanded ? 'عرض أقل' : 'عرض المزيد',
                    style: ts(10.5, weight: FontWeight.w700, color: C.primaryDark),
                  ),
                ),
              ),
          ],
        );
      },
    );
  }
}
