import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../state/cart_controller.dart';
import '../../state/notifications_controller.dart';
import '../theme/app_theme.dart';
import 'cart_screen.dart';
import 'favorites_screen.dart';
import 'home_screen.dart';
import 'more_screen.dart';
import 'offers_screen.dart';

/// Bottom navigation shell (الرئيسية / العروض / السلة / المفضلة / المزيد).
class MainShell extends StatefulWidget {
  const MainShell({super.key, this.initialIndex = 0});

  final int initialIndex;

  @override
  State<MainShell> createState() => _MainShellState();
}

class _MainShellState extends State<MainShell> {
  late int _index = widget.initialIndex;

  static const _tabs = [
    _TabSpec('الرئيسية', Icons.home_rounded, Icons.home_outlined),
    _TabSpec('العروض', Icons.local_offer_rounded, Icons.local_offer_outlined),
    _TabSpec('السلة', Icons.shopping_cart_rounded, Icons.shopping_cart_outlined),
    _TabSpec('المفضلة', Icons.favorite_rounded, Icons.favorite_border_rounded),
    _TabSpec('المزيد', Icons.menu_rounded, Icons.menu_rounded),
  ];

  void _select(int index) {
    if (_index == index) return;
    setState(() => _index = index);
  }

  @override
  Widget build(BuildContext context) {
    final cartCount = context.watch<CartController>().count;
    final unread = context.watch<NotificationsController>().unreadCount;

    return Scaffold(
      backgroundColor: C.bg,
      body: IndexedStack(
        index: _index,
        children: [
          HomeScreen(onNavigate: _select),
          const OffersScreen(embedded: true),
          const CartScreen(embedded: true),
          const FavoritesScreen(embedded: true),
          const MoreScreen(embedded: true),
        ],
      ),
      bottomNavigationBar: _BottomBar(
        index: _index,
        onTap: _select,
        cartCount: cartCount,
        unreadCount: unread,
        tabs: _tabs,
      ),
    );
  }
}

class _TabSpec {
  const _TabSpec(this.label, this.activeIcon, this.icon);
  final String label;
  final IconData activeIcon;
  final IconData icon;
}

class _BottomBar extends StatelessWidget {
  const _BottomBar({
    required this.index,
    required this.onTap,
    required this.cartCount,
    required this.unreadCount,
    required this.tabs,
  });

  final int index;
  final ValueChanged<int> onTap;
  final int cartCount;
  final int unreadCount;
  final List<_TabSpec> tabs;

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: const EdgeInsets.fromLTRB(12, 0, 12, 12),
      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 8),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(24),
        boxShadow: Sh.nav,
        border: Border.all(color: C.border),
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceAround,
        children: [
          for (var i = 0; i < tabs.length; i++)
            _BottomItem(
              spec: tabs[i],
              active: i == index,
              badge: i == 2 ? cartCount : (i == 4 ? unreadCount : 0),
              onTap: () => onTap(i),
            ),
        ],
      ),
    );
  }
}

class _BottomItem extends StatelessWidget {
  const _BottomItem({
    required this.spec,
    required this.active,
    required this.onTap,
    this.badge = 0,
  });

  final _TabSpec spec;
  final bool active;
  final VoidCallback onTap;
  final int badge;

  @override
  Widget build(BuildContext context) {
    final color = active ? C.primaryDark : C.muted;
    return InkWell(
      onTap: onTap,
      borderRadius: BorderRadius.circular(16),
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Stack(
              clipBehavior: Clip.none,
              children: [
                Container(
                  width: 34,
                  height: 30,
                  decoration: BoxDecoration(
                    color: active ? C.purpleSoft : Colors.transparent,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Icon(active ? spec.activeIcon : spec.icon, size: 19, color: color),
                ),
                if (badge > 0)
                  Positioned(
                    top: -3,
                    left: -2,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 4, vertical: 1),
                      constraints: const BoxConstraints(minWidth: 15),
                      decoration: BoxDecoration(
                        color: C.danger,
                        borderRadius: BorderRadius.circular(20),
                        border: Border.all(color: Colors.white, width: 1.2),
                      ),
                      child: Text(
                        badge > 99 ? '99+' : '$badge',
                        textAlign: TextAlign.center,
                        style: ts(7.5, weight: FontWeight.w700, color: Colors.white, height: 1.3),
                      ),
                    ),
                  ),
              ],
            ),
            const SizedBox(height: 2),
            Text(
              spec.label,
              style: ts(8.5, weight: active ? FontWeight.w700 : FontWeight.w500, color: color),
            ),
          ],
        ),
      ),
    );
  }
}
