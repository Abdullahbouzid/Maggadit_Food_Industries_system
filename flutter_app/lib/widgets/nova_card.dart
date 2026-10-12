import 'package:flutter/material.dart';

class NovaCard extends StatelessWidget {
  final Widget child;
  final EdgeInsets? padding;
  final Color? color;
  final double borderRadius;
  final List<BoxShadow>? boxShadow;

  const NovaCard({
    Key? key,
    required this.child,
    this.padding,
    this.color,
    this.borderRadius = 12,
    this.boxShadow,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: padding ?? EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: color ?? Colors.white,
        borderRadius: BorderRadius.circular(borderRadius),
        boxShadow: boxShadow ??
            [
              BoxShadow(
                color: Colors.black.withOpacity(0.05),
                blurRadius: 10,
                offset: Offset(0, 4),
              ),
            ],
      ),
      child: child,
    );
  }
}