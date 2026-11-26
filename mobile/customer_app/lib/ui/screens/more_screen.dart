import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../state/auth_controller.dart';
import '../../state/cart_controller.dart';
import '../../state/notifications_controller.dart';
import '../router.dart';
import '../theme/app_theme.dart';
import '../widgets/kit.dart';
import '../../l10n/l10n.dart';

/// "المزيد" hub: every secondary section of the app.
class MoreScreen extends StatelessWidget {
  const MoreScreen({super.key, this.embedded = false});

  final bool embedded;

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthController>();
    final unread = context.watch<NotificationsController>().unreadCount;
    final cartCount = context.watch<CartController>().count;

    final content = ListView(
      padding: const EdgeInsets.fromLTRB(16, 12, 16, 20),
      children: [
        NovaCard(
          onTap: () => Routes.profile(),
          child: Row(
            children: [
              Container(
                width: 46,
                height: 46,
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [C.primary, C.primaryDeep],
                  ),
                  borderRadius: BorderRadius.circular(15),
                ),
                child: Center(
                  child: Text(
                    auth.initials,
                    style: ts(16, weight: FontWeight.w700, color: Colors.white),
                  ),
                ),
              ),
              const SizedBox(width: 12),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      auth.displayName,
                      style: ts(13, weight: FontWeight.w700),
                    ),
                    const SizedBox(height: 2),
                    Text(
                      auth.activeOrganization?.name ??
                          tr(context, 'ماغديت للصناعات الغذائية'),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: ts(9.5, color: C.muted),
                    ),
                  ],
                ),
              ),
              const Icon(Icons.chevron_left_rounded, size: 20, color: C.muted),
            ],
          ),
        ),
        const SizedBox(height: 12),
        _Group(
          title: tr(context, 'الطلبات والمشتريات'),
          items: [
            _Item(
              Icons.receipt_long_rounded,
              tr(context, 'طلباتي'),
              onTap: () => Routes.orders(),
            ),
            _Item(
              Icons.assignment_return_rounded,
              tr(context, 'المرتجعات'),
              onTap: () => Routes.returns(),
            ),
            _Item(
              Icons.inventory_2_rounded,
              tr(context, 'منتجاتي'),
              onTap: () => Routes.myProducts(),
            ),
            _Item(
              Icons.shopping_cart_rounded,
              tr(context, 'سلة التسوق'),
              onTap: () => Routes.cart(),
              badge: cartCount,
            ),
            _Item(
              Icons.favorite_rounded,
              tr(context, 'المفضلة'),
              onTap: () => Routes.favorites(),
            ),
          ],
        ),
        const SizedBox(height: 12),
        _Group(
          title: tr(context, 'المالية'),
          items: [
            _Item(
              Icons.account_balance_wallet_rounded,
              tr(context, 'كشف الحساب'),
              onTap: () => Routes.wallet(),
            ),
            _Item(
              Icons.payments_rounded,
              tr(context, 'الفواتير'),
              onTap: () => Routes.invoices(),
            ),
          ],
        ),
        const SizedBox(height: 12),
        _Group(
          title: tr(context, 'التواصل والمحتوى'),
          items: [
            _Item(
              Icons.notifications_rounded,
              tr(context, 'الإشعارات'),
              onTap: () => Routes.notifications(),
              badge: unread,
            ),
            _Item(
              Icons.assignment_rounded,
              tr(context, 'الاستبيانات'),
              onTap: () => Routes.surveys(),
            ),
            _Item(
              Icons.help_outline_rounded,
              tr(context, 'الأسئلة الشائعة'),
              onTap: () => Routes.faq(),
            ),
            _Item(
              Icons.info_outline_rounded,
              tr(context, 'من نحن'),
              onTap: () => Routes.about(),
            ),
            _Item(
              Icons.support_agent_rounded,
              tr(context, 'اتصل بنا'),
              onTap: () => Routes.contact(),
            ),
            _Item(
              Icons.shield_outlined,
              tr(context, 'سياسة الخصوصية'),
              onTap: () => Routes.privacy(),
            ),
          ],
        ),
        const SizedBox(height: 12),
        _Group(
          title: tr(context, 'الحساب'),
          items: [
            _Item(
              Icons.person_outline_rounded,
              tr(context, 'الملف الشخصي'),
              onTap: () => Routes.profile(),
            ),
            _Item(
              Icons.apartment_rounded,
              tr(context, 'الفروع والمنظمات'),
              onTap: () => Routes.organizations(),
            ),
            _Item(
              Icons.logout_rounded,
              tr(context, 'تسجيل الخروج'),
              danger: true,
              onTap: () async {
                await context.read<AuthController>().logout();
                if (context.mounted) Routes.login();
              },
            ),
          ],
        ),
        const SizedBox(height: 18),
        Center(
          child: Text(
            'Maggadit Food Industries · v1.0.0',
            style: ts(9, color: C.muted),
          ),
        ),
      ],
    );

    if (embedded) {
      return SafeArea(bottom: false, child: content);
    }
    return Scaffold(
      backgroundColor: C.bg,
      body: SafeArea(bottom: false, child: content),
    );
  }
}

class _Group extends StatelessWidget {
  const _Group({required this.title, required this.items});

  final String title;
  final List<_Item> items;

  @override
  Widget build(BuildContext context) {
    return NovaCard(
      padding: EdgeInsets.zero,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.fromLTRB(14, 12, 14, 6),
            child: Text(
              title,
              style: ts(9.5, weight: FontWeight.w700, color: C.muted),
            ),
          ),
          for (var i = 0; i < items.length; i++) ...[
            if (i > 0) const Divider(height: 1),
            items[i],
          ],
        ],
      ),
    );
  }
}

class _Item extends StatelessWidget {
  const _Item(
    this.icon,
    this.label, {
    required this.onTap,
    this.badge = 0,
    this.danger = false,
  });

  final IconData icon;
  final String label;
  final VoidCallback onTap;
  final int badge;
  final bool danger;

  @override
  Widget build(BuildContext context) {
    final color = danger ? C.danger : C.inkSoft;
    return InkWell(
      onTap: onTap,
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 13),
        child: Row(
          children: [
            Icon(icon, size: 17, color: color),
            const SizedBox(width: 10),
            Expanded(
              child: Text(
                label,
                style: ts(11.5, color: color, weight: FontWeight.w600),
              ),
            ),
            if (badge > 0)
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1),
                decoration: BoxDecoration(
                  color: C.danger,
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  '$badge',
                  style: ts(8, weight: FontWeight.w700, color: Colors.white),
                ),
              ),
            const SizedBox(width: 4),
            const Icon(Icons.chevron_left_rounded, size: 18, color: C.muted),
          ],
        ),
      ),
    );
  }
}
