import 'package:flutter/material.dart';

import '../theme/app_theme.dart';
import 'kit.dart';

/// Standard page scaffold with the brand header (title + optional subtitle).
class ScreenScaffold extends StatelessWidget {
  const ScreenScaffold({
    super.key,
    required this.title,
    this.subtitle,
    this.child,
    this.actions = const [],
    this.bottomBar,
    this.showBack = true,
    this.padding = const EdgeInsets.fromLTRB(16, 14, 16, 18),
    this.backgroundColor = C.bg,
    this.automaticBackButton = true,
    this.onRefresh,
    this.floatingActionButton,
  });

  final String title;
  final String? subtitle;
  final Widget? child;
  final List<Widget> actions;
  final Widget? bottomBar;
  final bool showBack;
  final EdgeInsets padding;
  final Color backgroundColor;
  final bool automaticBackButton;
  final Future<void> Function()? onRefresh;
  final Widget? floatingActionButton;

  @override
  Widget build(BuildContext context) {
    final canPop = Navigator.of(context).canPop();

    Widget body = child ?? const SizedBox.shrink();
    if (onRefresh != null) {
      body = RefreshIndicator(
        color: C.primary,
        onRefresh: onRefresh!,
        child: body is SingleChildScrollView
            ? body
            : ListView(physics: const AlwaysScrollableScrollPhysics(), children: [body]),
      );
    }

    return Scaffold(
      backgroundColor: backgroundColor,
      floatingActionButton: floatingActionButton,
      appBar: PreferredSize(
        preferredSize: Size.fromHeight(subtitle == null ? 62 : 70),
        child: Container(
          decoration: const BoxDecoration(
            color: C.surface,
            borderRadius: BorderRadius.vertical(bottom: Radius.circular(20)),
            boxShadow: Sh.soft,
          ),
          child: SafeArea(
            bottom: false,
            child: Padding(
              padding: const EdgeInsets.fromLTRB(10, 6, 14, 8),
              child: Row(
                children: [
                  if (showBack && canPop && automaticBackButton)
                    IconButton(
                      onPressed: () => Navigator.of(context).maybePop(),
                      icon: const Icon(Icons.arrow_forward_rounded, size: 20),
                      color: C.ink,
                      style: IconButton.styleFrom(
                        backgroundColor: C.purpleSoft,
                        shape: RoundedRectangleBorder(
                          borderRadius: BorderRadius.circular(12),
                        ),
                      ),
                    ),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Text(
                          title,
                          maxLines: 1,
                          overflow: TextOverflow.ellipsis,
                          style: ts(15.5, weight: FontWeight.w700),
                        ),
                        if (subtitle != null)
                          Text(
                            subtitle!,
                            maxLines: 1,
                            overflow: TextOverflow.ellipsis,
                            style: ts(9.5, color: C.muted),
                          ),
                      ],
                    ),
                  ),
                  ...actions,
                ],
              ),
            ),
          ),
        ),
      ),
      body: Padding(padding: padding, child: body),
      bottomNavigationBar: bottomBar,
    );
  }
}

/// Sticky action bar used by cart / product detail / checkout.
class BottomActionBar extends StatelessWidget {
  const BottomActionBar({super.key, required this.child, this.padding});

  final Widget child;
  final EdgeInsets? padding;

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: padding ?? const EdgeInsets.fromLTRB(16, 12, 16, 12),
      decoration: const BoxDecoration(
        color: C.surface,
        boxShadow: Sh.nav,
        borderRadius: BorderRadius.vertical(top: Radius.circular(22)),
      ),
      child: SafeArea(top: false, child: child),
    );
  }
}

class IconBadge extends StatelessWidget {
  const IconBadge({
    super.key,
    required this.icon,
    this.onTap,
    this.count = 0,
    this.color = C.inkSoft,
  });

  final IconData icon;
  final VoidCallback? onTap;
  final int count;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return Stack(
      clipBehavior: Clip.none,
      children: [
        Container(
          width: 36,
          height: 36,
          decoration: BoxDecoration(
            color: const Color(0xFFF6F7FB),
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: C.border),
          ),
          child: IconButton(
            padding: EdgeInsets.zero,
            onPressed: onTap,
            icon: Icon(icon, size: 17, color: color),
          ),
        ),
        if (count > 0)
          Positioned(
            top: -4,
            left: -4,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 5, vertical: 1),
              constraints: const BoxConstraints(minWidth: 16),
              decoration: BoxDecoration(
                color: C.danger,
                borderRadius: BorderRadius.circular(20),
                border: Border.all(color: Colors.white, width: 1.4),
              ),
              child: Text(
                count > 99 ? '99+' : '$count',
                textAlign: TextAlign.center,
                style: ts(8, weight: FontWeight.w700, color: Colors.white, height: 1.3),
              ),
            ),
          ),
      ],
    );
  }
}

class SearchField extends StatelessWidget {
  const SearchField({
    super.key,
    required this.controller,
    this.onSubmitted,
    this.onChanged,
    this.hint = 'ابحث عن منتج...',
    this.onClear,
  });

  final TextEditingController controller;
  final ValueChanged<String>? onSubmitted;
  final ValueChanged<String>? onChanged;
  final String hint;
  final VoidCallback? onClear;

  @override
  Widget build(BuildContext context) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(14),
        border: Border.all(color: C.border),
        boxShadow: Sh.soft,
      ),
      child: TextField(
        controller: controller,
        textInputAction: TextInputAction.search,
        onSubmitted: onSubmitted,
        onChanged: onChanged,
        style: ts(12),
        decoration: InputDecoration(
          hintText: hint,
          filled: false,
          border: InputBorder.none,
          enabledBorder: InputBorder.none,
          focusedBorder: InputBorder.none,
          contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 12),
          prefixIcon: const Icon(Icons.search_rounded, size: 19, color: C.muted),
          suffixIcon: controller.text.isEmpty
              ? null
              : IconButton(
                  icon: const Icon(Icons.close_rounded, size: 17, color: C.muted),
                  onPressed: () {
                    controller.clear();
                    onClear?.call();
                    onChanged?.call('');
                  },
                ),
        ),
      ),
    );
  }
}
