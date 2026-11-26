import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../state/cart_controller.dart';
import '../../state/favorites_controller.dart';
import '../router.dart';
import '../theme/app_theme.dart';
import '../widgets/cards.dart';
import '../widgets/chrome.dart';
import '../widgets/kit.dart';
import '../../l10n/l10n.dart';

class FavoritesScreen extends StatefulWidget {
  const FavoritesScreen({super.key, this.embedded = false});

  final bool embedded;

  @override
  State<FavoritesScreen> createState() => _FavoritesScreenState();
}

class _FavoritesScreenState extends State<FavoritesScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final controller = context.read<FavoritesController>();
      if (controller.products.isEmpty && !controller.loading) controller.load();
    });
  }

  Future<void> _reload() => context.read<FavoritesController>().load();

  @override
  Widget build(BuildContext context) {
    final favorites = context.watch<FavoritesController>();
    final cart = context.watch<CartController>();

    final content = favorites.loading && favorites.products.isEmpty
        ? LoadingState(label: tr(context, 'جاري تحميل المفضلة'))
        : favorites.products.isEmpty
        ? EmptyState(
            icon: Icons.favorite_border_rounded,
            title: tr(context, 'لا توجد منتجات في المفضلة'),
            message: tr(context, 'اضغط على القلب في أي منتج لحفظه هنا'),
            actionLabel: tr(context, 'تصفّح المنتجات'),
            onAction: () => Routes.products(),
          )
        : RefreshIndicator(
            color: C.primary,
            onRefresh: _reload,
            child: GridView.builder(
              padding: const EdgeInsets.only(bottom: 16),
              itemCount: favorites.products.length,
              gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
                crossAxisCount: 2,
                mainAxisSpacing: 12,
                crossAxisSpacing: 12,
                childAspectRatio: 0.62,
              ),
              itemBuilder: (context, index) {
                final product = favorites.products[index];
                final pack = product.defaultPack;
                return ProductCard(
                  product: product.copyWith(isFavorite: true),
                  isFavorite: true,
                  onToggleFavorite: () async {
                    await favorites.toggle(product.id);
                    if (context.mounted) await favorites.load();
                  },
                  onTap: () => Routes.product(product.id, preview: product),
                  onAdd: () => cart.add(
                    itemId: product.id,
                    name: product.name,
                    price: pack?.price ?? product.price,
                    packId: pack?.id,
                    packName: pack?.name ?? '',
                    image: product.image,
                    uomName: pack?.uomName,
                  ),
                  quantityInCart: cart.quantityOf(product.id, pack?.id),
                  onIncrement: () => cart.add(
                    itemId: product.id,
                    name: product.name,
                    price: pack?.price ?? product.price,
                    packId: pack?.id,
                    packName: pack?.name ?? '',
                    image: product.image,
                    uomName: pack?.uomName,
                  ),
                  onDecrement: () {
                    final existing = cart.items.where(
                      (e) => e.itemId == product.id && e.packId == pack?.id,
                    );
                    if (existing.isNotEmpty) cart.decrement(existing.first);
                  },
                );
              },
            ),
          );

    if (widget.embedded) {
      return SafeArea(
        bottom: false,
        child: Padding(
          padding: const EdgeInsets.fromLTRB(16, 12, 16, 0),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              Row(
                children: [
                  Text(
                    tr(context, 'المفضلة'),
                    style: ts(18, weight: FontWeight.w700),
                  ),
                  const Spacer(),
                  Text(
                    tArgs(context, '{0} منتج', [favorites.count]),
                    style: ts(10, color: C.muted),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              Expanded(child: content),
            ],
          ),
        ),
      );
    }

    return ScreenScaffold(
      title: tr(context, 'المفضلة'),
      subtitle: tArgs(context, '{0} منتج', [favorites.count]),
      onRefresh: _reload,
      child: content,
    );
  }
}
