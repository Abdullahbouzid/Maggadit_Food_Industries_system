import 'package:flutter/material.dart';

class NovaBadge extends StatelessWidget {
  final String label;
  final Color? backgroundColor;
  final Color? textColor;
  final double size;
  final bool hiddenIfZero;

  const NovaBadge({
    Key? key,
    required this.label,
    this.backgroundColor,
    this.textColor,
    this.size = 16,
    this.hiddenIfZero = true,
  }) : super(key: key);

  @override
  Widget build(BuildContext context) {
    final color = backgroundColor ?? Theme.of(context).colorScheme.secondary;
    final textCol = textColor ?? Colors.white;

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 2),
      decoration: BoxDecoration(
        color: color,
        borderRadius: BorderRadius.circular(10),
      ),
      constraints: BoxConstraints(
        minWidth: hiddenIfZero && int.parse(label) <= 0 ? 0 : 20,
        minHeight: size,
      ),
      alignment: Alignment.center,
      child: Text(
        hiddenIfZero && int.parse(label) <= 0 ? '' : label,
        style: TextStyle(
          color: textCol,
          fontSize: size - 4,
          fontWeight: FontWeight.bold,
        ),
      ),
    );
  }
}