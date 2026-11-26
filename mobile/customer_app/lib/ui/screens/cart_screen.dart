import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/formatters.dart';
import '../../state/cart_controller.dart';
import '../router.dart';
import '../theme/app_theme.dart';
import '../widgets/chrome.dart';
import '../widgets/kit.dart';

class CartScreen extends StatelessWidget {
  const CartScreen({super.key, this.embedded = false});

  final bool embedded;

  @override
  Widget build(BuildContext context) {
    final cart = context.watch<CartController>();

    final content = cart.isEmpty
        ? EmptyState(
            icon: Icons.shopping_cart_outlined,
            title: 'سلة التسوق فارغة',
            message: 'أضف منتجات من الرئيسية أو العروض للبدء بالطلب',
            actionLabel: 'تصفّح المنتجات',
            onAction: () => Routes.products(),
          )
        : ListView.separated(
            padding: EdgeInsets.only(bottom: embedded ? 130 : 16),
            itemCount: cart.items.length,
            separatorBuilder: (_, __) => const SizedBox(height: 10),
            itemBuilder: (context, index) => _CartTile(item: cart.items[index]),
          );

    final summary = cart.isEmpty
        ? null
        : BottomActionBar(
            child: Row(
              children: [
                Expanded(
                  child: PrimaryButton(
                    label: 'إتمام الطلب',
                    icon: Icons.arrow_back_rounded,
                    onPressed: () => Routes.checkout(),
                  ),
                ),
                const SizedBox(width: 14),
                Column(
                  crossAxisAlignment: CrossAxisAlignment.end,
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Text('${cart.count} أصناف', style: ts(8.5, color: C.muted)),
                    Text(Fmt.price(cart.subtotal),
                        style: ts(15, weight: FontWeight.w700, color: C.primaryDark)),
                  ],
                ),
              ],
            ),
          );

    if (embedded) {
      return SafeArea(
        bottom: false,
        child: Scaffold(
          backgroundColor: C.bg,
          body: Padding(
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 0),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.stretch,
              children: [
                Row(
                  children: [
                    Text('سلة التسوق', style: ts(18, weight: FontWeight.w700)),
                    const Spacer(),
                    TextButton(
                      onPressed: cart.isEmpty ? null : () => cart.clear(),
                      child: Text('تفريغ السلة', style: ts(10, color: C.danger)),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                Expanded(child: content),
              ],
            ),
          ),
          bottomNavigationBar: summary,
        ),
      );
    }

    return ScreenScaffold(
      title: 'سلة التسوق',
      subtitle: cart.isEmpty ? null : '${cart.count} أصناف',
      actions: [
        if (!cart.isEmpty)
          IconButton(
            onPressed: () => cart.clear(),
            icon: const Icon(Icons.delete_outline_rounded, size: 19, color: C.danger),
          ),
      ],
      child: content,
      bottomBar: summary,
    );
  }
}

class _CartTile extends StatelessWidget {
  const _CartTile({required this.item});

  final CartItem item;

  @override
  Widget build(BuildContext context) {
    final cart = context.read<CartController>();

    return NovaCard(
      padding: const EdgeInsets.all(10),
      child: Row(
        children: [
          RemoteImage(url: item.image, width: 68, height: 68, radius: 14),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(item.name,
                    maxLines: 1, overflow: TextOverflow.ellipsis,
                    style: ts(12.5, weight: FontWeight.w700)),
                if (item.packName.isNotEmpty) ...[
                  const SizedBox(height: 2),
                  Text(item.packName, style: ts(9, color: C.muted)),
                ],
                const SizedBox(height: 6),
                Row(
                  children: [
                    Text(Fmt.price(item.price),
                        style: ts(11.5, weight: FontWeight.w700, color: C.primaryDark)),
                    const Spacer(),
                    QtyStepper(
                      quantity: item.quantity,
                      compact: true,
                      onIncrement: () => cart.increment(item),
                      onDecrement: () => cart.decrement(item),
                    ),
                  ],
                ),
              ],
            ),
          ),
          const SizedBox(width: 4),
          IconButton(
            onPressed: () => cart.remove(item),
            icon: const Icon(Icons.close_rounded, size: 16, color: C.muted),
            tooltip: 'إزالة',
          ),
        ],
      ),
    );
  }
}
