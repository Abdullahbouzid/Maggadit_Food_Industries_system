import 'package:flutter/material.dart';

class NovaAlert extends StatelessWidget {
  final String message;
  final String? title;
  final AlertType type;
  final VoidCallback? onDismiss;

  const NovaAlert({
    Key? key,
    required this.message,
    this.title,
    this.type = AlertType.info,
    this.onDismiss,
  }) : super(key: key);

  final Color _infoColor = Colors.blue;
  final Color _successColor = Colors.green;
  final Color _warningColor = Colors.orange;
  final Color _errorColor = Colors.red;

  Color get _getColor {
    switch (type) {
      case AlertType.info:
        return _infoColor;
      case AlertType.success:
        return _successColor;
      case AlertType.warning:
        return _warningColor;
      case AlertType.error:
        return _errorColor;
    }
  }

  @override
  Widget build(BuildContext context) {
    IconData icon;
    switch (type) {
      case AlertType.success:
        icon = Icons.check_circle_outline;
        break;
      case AlertType.warning:
        icon = Icons.warning_amber_rounded;
        break;
      case AlertType.error:
        icon = Icons.error_outline;
        break;
      case AlertType.info:
      default:
        icon = Icons.info_outline;
    }

    return AnimatedContainer(
      duration: const Duration(milliseconds: 300),
      padding: const EdgeInsets.all(16),
      margin: const EdgeInsets.symmetric(vertical: 8, horizontal: 16),
      decoration: BoxDecoration(
        color: _getColor.withOpacity(0.1),
        borderRadius: BorderRadius.circular(12),
        border: Border.all(color: _getColor, width: 1),
      ),
      child: Row(
        children: [
          Icon(icon, color: _getColor, size: 24),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                if (title != null)
                  Text(
                    title!,
                    style: const TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                Text(
                  message,
                  style: TextStyle(
                    fontSize: 14,
                    color: _getColor.withOpacity(0.9),
                  ),
                ),
              ],
            ),
          ),
          if (onDismiss != null)
            IconButton(
              icon: const Icon(Icons.close),
              color: _getColor,
              onPressed: onDismiss,
            ),
        ],
      ),
    );
  }
}

enum AlertType { info, success, warning, error }