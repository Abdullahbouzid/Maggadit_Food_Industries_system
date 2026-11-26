import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/formatters.dart';
import '../../models/account.dart';
import '../../state/app_dependencies.dart';
import '../../state/notifications_controller.dart';
import '../theme/app_theme.dart';
import '../widgets/chrome.dart';
import '../widgets/kit.dart';

class NotificationsScreen extends StatefulWidget {
  const NotificationsScreen({super.key});

  @override
  State<NotificationsScreen> createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      context.read<NotificationsController>().load();
    });
  }

  @override
  Widget build(BuildContext context) {
    final controller = context.watch<NotificationsController>();
    final filters = NotificationFilter.values;

    return ScreenScaffold(
      title: 'الإشعارات',
      subtitle: controller.unreadCount > 0 ? '${controller.unreadCount} غير مقروء' : 'كل الإشعارات مقروءة',
      onRefresh: () => controller.load(),
      child: Column(
        children: [
          SizedBox(
            height: 34,
            child: ListView.separated(
              scrollDirection: Axis.horizontal,
              itemCount: filters.length,
              separatorBuilder: (_, __) => const SizedBox(width: 8),
              itemBuilder: (context, index) {
                final filter = filters[index];
                final selected = controller.filter == filter;
                return GestureDetector(
                  onTap: () => controller.setFilter(filter),
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 8),
                    decoration: BoxDecoration(
                      color: selected ? C.primary : Colors.white,
                      borderRadius: BorderRadius.circular(30),
                      border: Border.all(color: selected ? C.primary : C.border),
                    ),
                    child: Text(
                      _label(filter),
                      style: ts(10.5,
                          weight: FontWeight.w600, color: selected ? Colors.white : C.inkSoft),
                    ),
                  ),
                );
              },
            ),
          ),
          const SizedBox(height: 12),
          Expanded(
            child: controller.loading && controller.allItems.isEmpty
                ? const LoadingState(label: 'جاري تحميل الإشعارات')
                : controller.error != null && controller.allItems.isEmpty
                    ? ErrorState(message: controller.error!, onRetry: controller.load)
                    : controller.visibleItems.isEmpty
                        ? EmptyState(
                            icon: Icons.notifications_none_rounded,
                            title: 'لا توجد إشعارات',
                            message: 'ستصلك هنا تحديثات الطلبات والعروض والفواتير',
                            actionLabel: 'تحديث',
                            onAction: controller.load,
                          )
                        : RefreshIndicator(
                            color: C.primary,
                            onRefresh: controller.load,
                            child: ListView.separated(
                              padding: const EdgeInsets.only(bottom: 16),
                              itemCount: controller.visibleItems.length,
                              separatorBuilder: (_, __) => const SizedBox(height: 10),
                              itemBuilder: (context, index) {
                                final item = controller.visibleItems[index];
                                return _NotificationTile(notification: item);
                              },
                            ),
                          ),
          ),
        ],
      ),
    );
  }

  String _label(NotificationFilter filter) {
    switch (filter) {
      case NotificationFilter.all:
        return 'الكل';
      case NotificationFilter.unread:
        return 'غير مقروءة';
      case NotificationFilter.starred:
        return 'مهمة';
      case NotificationFilter.archived:
        return 'مؤرشفة';
    }
  }
}

class _NotificationTile extends StatelessWidget {
  const _NotificationTile({required this.notification});

  final AppNotification notification;

  @override
  Widget build(BuildContext context) {
    final controller = context.read<NotificationsController>();
    final isUnread = !notification.isRead;

    return NovaCard(
      color: isUnread ? C.purpleSoft : Colors.white,
      onTap: () => controller.markRead(notification),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Container(
                width: 34,
                height: 34,
                decoration: BoxDecoration(
                  color: _iconBackground(notification.iconType),
                  borderRadius: BorderRadius.circular(11),
                ),
                child: Icon(_icon(notification.iconType), size: 17, color: _iconColor(notification.iconType)),
              ),
              const SizedBox(width: 10),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      notification.subject,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: ts(12,
                          weight: isUnread ? FontWeight.w700 : FontWeight.w600),
                    ),
                    const SizedBox(height: 2),
                    Text(notification.createdAt, style: ts(8.5, color: C.muted)),
                  ],
                ),
              ),
              if (isUnread)
                Container(
                  width: 7,
                  height: 7,
                  decoration: const BoxDecoration(color: C.primary, shape: BoxShape.circle),
                ),
            ],
          ),
          if (notification.body.isNotEmpty) ...[
            const SizedBox(height: 8),
            Text(
              notification.body,
              maxLines: 3,
              overflow: TextOverflow.ellipsis,
              style: ts(10.5, color: C.inkSoft, height: 1.7),
            ),
          ],
          const SizedBox(height: 6),
          Row(
            children: [
              _Action(
                icon: notification.isStarred ? Icons.star_rounded : Icons.star_border_rounded,
                label: 'مهم',
                active: notification.isStarred,
                onTap: () => controller.toggleStar(notification),
              ),
              const SizedBox(width: 14),
              _Action(
                icon: notification.isArchived
                    ? Icons.unarchive_rounded
                    : Icons.archive_outlined,
                label: notification.isArchived ? 'استرجاع' : 'أرشفة',
                active: notification.isArchived,
                onTap: () => controller.toggleArchive(notification),
              ),
              const Spacer(),
              _Action(
                icon: notification.isRead
                    ? Icons.mark_email_unread_outlined
                    : Icons.mark_email_read_outlined,
                label: notification.isRead ? 'غير مقروء' : 'مقروء',
                active: false,
                onTap: () => controller.toggleRead(notification),
              ),
            ],
          ),
        ],
      ),
    );
  }

  IconData _icon(String type) {
    switch (type) {
      case 'order':
      case 'quotation':
        return Icons.receipt_long_rounded;
      case 'invoice':
      case 'payment':
        return Icons.request_quote_rounded;
      case 'offer':
        return Icons.local_offer_rounded;
      default:
        return Icons.notifications_rounded;
    }
  }

  Color _iconColor(String type) {
    switch (type) {
      case 'order':
      case 'quotation':
        return C.primaryDark;
      case 'invoice':
      case 'payment':
        return C.success;
      case 'offer':
        return C.warning;
      default:
        return C.info;
    }
  }

  Color _iconBackground(String type) {
    switch (type) {
      case 'order':
      case 'quotation':
        return C.purpleSoft;
      case 'invoice':
      case 'payment':
        return C.successSoft;
      case 'offer':
        return C.warningSoft;
      default:
        return C.infoSoft;
    }
  }
}

class _Action extends StatelessWidget {
  const _Action({
    required this.icon,
    required this.label,
    required this.active,
    required this.onTap,
  });

  final IconData icon;
  final String label;
  final bool active;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    final color = active ? C.warning : C.muted;
    return GestureDetector(
      onTap: onTap,
      behavior: HitTestBehavior.opaque,
      child: Row(
        children: [
          Icon(icon, size: 14, color: color),
          const SizedBox(width: 3),
          Text(label, style: ts(8.5, color: color, weight: FontWeight.w600)),
        ],
      ),
    );
  }
}
