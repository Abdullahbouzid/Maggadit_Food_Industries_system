import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/formatters.dart';
import '../../models/catalog.dart';
import '../../state/app_dependencies.dart';
import '../../state/cart_controller.dart';
import '../../state/favorites_controller.dart';
import '../router.dart';
import '../theme/app_theme.dart';
import '../widgets/chrome.dart';
import '../widgets/kit.dart';
import '../../l10n/l10n.dart';

class ProductDetailScreen extends StatefulWidget {
  const ProductDetailScreen({super.key, required this.productId, this.preview});

  final int productId;

  /// Product already known from the listing (used to paint instantly).
  final Product? preview;

  @override
  State<ProductDetailScreen> createState() => _ProductDetailScreenState();
}

class _ProductDetailScreenState extends State<ProductDetailScreen> {
  late Future<Product> _future;
  Product? _product;
  int _imageIndex = 0;
  int _packIndex = 0;

  @override
  void initState() {
    super.initState();
    _product = widget.preview;
    _future = context.read<AppDependencies>().catalogService.product(
      widget.productId,
    );
    _future
        .then((value) {
          if (!mounted) return;
          setState(() {
            _product = value;
            _packIndex = value.packs.indexWhere((p) => p.isDefault);
            if (_packIndex < 0) _packIndex = 0;
          });
        })
        .catchError((_) {});
  }

  Future<void> _reload() async {
    setState(() {
      _future = context.read<AppDependencies>().catalogService.product(
        widget.productId,
      );
    });
    _future
        .then((value) {
          if (!mounted) return;
          setState(() => _product = value);
        })
        .catchError((_) {});
  }

  ProductPack? get _selectedPack {
    final packs = _product?.packs ?? const <ProductPack>[];
    if (packs.isEmpty) return null;
    return packs[_packIndex.clamp(0, packs.length - 1)];
  }

  void _addToCart() {
    final product = _product;
    if (product == null) return;
    final pack = _selectedPack;
    context.read<CartController>().add(
      itemId: product.id,
      name: product.name,
      price: pack?.price ?? product.price,
      packId: pack?.id,
      packName: pack?.name ?? '',
      image: product.image,
      uomName: pack?.uomName,
    );
    ScaffoldMessenger.of(context)
      ..hideCurrentSnackBar()
      ..showSnackBar(
        SnackBar(
          content: Text(
            tr(context, 'تمت الإضافة إلى السلة'),
            style: ts(11, color: Colors.white),
          ),
          action: SnackBarAction(
            label: tr(context, 'السلة'),
            textColor: Colors.white,
            onPressed: () => Routes.cart(),
          ),
        ),
      );
  }

  @override
  Widget build(BuildContext context) {
    final favorites = context.watch<FavoritesController>();

    return FutureBuilder<Product>(
      future: _future,
      builder: (context, snapshot) {
        final product = _product ?? snapshot.data;
        final gallery = product?.images.isNotEmpty == true
            ? product!.images
            : [if (product?.image != null) product!.image!];
        final price = _selectedPack?.price ?? product?.price ?? 0;

        return ScreenScaffold(
          title: product?.name ?? tr(context, 'تفاصيل المنتج'),
          subtitle: product?.categoryName ?? tr(context, 'ماغديت'),
          padding: EdgeInsets.zero,
          bottomBar: product == null
              ? null
              : BottomActionBar(
                  child: Row(
                    children: [
                      GestureDetector(
                        onTap: () => favorites.toggle(product.id),
                        child: Container(
                          width: 46,
                          height: 46,
                          decoration: BoxDecoration(
                            color: favorites.isFavorite(product.id)
                                ? C.dangerSoft
                                : C.purpleSoft,
                            borderRadius: BorderRadius.circular(14),
                          ),
                          child: Icon(
                            favorites.isFavorite(product.id)
                                ? Icons.favorite_rounded
                                : Icons.favorite_border_rounded,
                            size: 20,
                            color: favorites.isFavorite(product.id)
                                ? C.danger
                                : C.primaryDark,
                          ),
                        ),
                      ),
                      const SizedBox(width: 10),
                      Expanded(
                        child: PrimaryButton(
                          label: tr(context, 'أضف إلى السلة'),
                          icon: Icons.add_shopping_cart_rounded,
                          onPressed: _addToCart,
                        ),
                      ),
                      const SizedBox(width: 12),
                      Column(
                        crossAxisAlignment: CrossAxisAlignment.end,
                        mainAxisAlignment: MainAxisAlignment.center,
                        children: [
                          Text(
                            tr(context, 'الإجمالي'),
                            style: ts(8.5, color: C.muted),
                          ),
                          Text(
                            Fmt.price(price),
                            style: ts(
                              14,
                              weight: FontWeight.w700,
                              color: C.primaryDark,
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ),
          child:
              snapshot.connectionState == ConnectionState.waiting &&
                  product == null
              ? LoadingState(label: tr(context, 'جاري تحميل المنتج'))
              : snapshot.hasError && product == null
              ? ErrorState(message: snapshot.error.toString(), onRetry: _reload)
              : product == null
              ? EmptyState(title: tr(context, 'المنتج غير موجود'))
              : ListView(
                  padding: const EdgeInsets.only(bottom: 20),
                  children: [
                    Container(
                      margin: const EdgeInsets.fromLTRB(16, 14, 16, 0),
                      decoration: BoxDecoration(
                        color: C.surface,
                        borderRadius: BorderRadius.circular(20),
                        border: Border.all(color: C.border),
                      ),
                      child: Column(
                        children: [
                          SizedBox(
                            height: 250,
                            child: PageView.builder(
                              itemCount: gallery.isEmpty ? 1 : gallery.length,
                              onPageChanged: (i) =>
                                  setState(() => _imageIndex = i),
                              itemBuilder: (context, index) => Padding(
                                padding: const EdgeInsets.all(10),
                                child: RemoteImage(
                                  url: gallery.isEmpty ? null : gallery[index],
                                  width: double.infinity,
                                  height: 230,
                                  radius: 16,
                                  icon: Icons.local_mall_rounded,
                                  fit: BoxFit.contain,
                                ),
                              ),
                            ),
                          ),
                          if (gallery.length > 1)
                            Padding(
                              padding: const EdgeInsets.only(bottom: 12),
                              child: Row(
                                mainAxisAlignment: MainAxisAlignment.center,
                                children: [
                                  for (var i = 0; i < gallery.length; i++)
                                    AnimatedContainer(
                                      duration: const Duration(
                                        milliseconds: 200,
                                      ),
                                      margin: const EdgeInsets.symmetric(
                                        horizontal: 2,
                                      ),
                                      width: i == _imageIndex ? 14 : 6,
                                      height: 6,
                                      decoration: BoxDecoration(
                                        color: i == _imageIndex
                                            ? C.primary
                                            : C.border,
                                        borderRadius: BorderRadius.circular(6),
                                      ),
                                    ),
                                ],
                              ),
                            ),
                        ],
                      ),
                    ),
                    Padding(
                      padding: const EdgeInsets.fromLTRB(16, 14, 16, 0),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.stretch,
                        children: [
                          Row(
                            children: [
                              if (product.packs.isNotEmpty)
                                Tag(
                                  text: product
                                      .packs[_packIndex.clamp(
                                        0,
                                        product.packs.length - 1,
                                      )]
                                      .name,
                                  color: C.primaryDark,
                                  background: C.purpleSoft,
                                ),
                              const Spacer(),
                              const Icon(
                                Icons.star_rounded,
                                size: 15,
                                color: C.amber,
                              ),
                              const SizedBox(width: 3),
                              Text(
                                Fmt.quantity(product.rate),
                                style: ts(10.5, weight: FontWeight.w600),
                              ),
                            ],
                          ),
                          const SizedBox(height: 8),
                          Text(
                            product.name,
                            style: ts(17, weight: FontWeight.w700),
                          ),
                          if (product.brandName != null) ...[
                            const SizedBox(height: 4),
                            Text(
                              tArgs(context, 'الماركة · {0}', [
                                product.brandName,
                              ]),
                              style: ts(10, color: C.muted),
                            ),
                          ],
                          const SizedBox(height: 10),
                          Row(
                            crossAxisAlignment: CrossAxisAlignment.end,
                            children: [
                              Text(
                                Fmt.price(price),
                                style: ts(
                                  21,
                                  weight: FontWeight.w700,
                                  color: C.primaryDark,
                                ),
                              ),
                              const SizedBox(width: 8),
                              if (product.oldPrice != null &&
                                  product.oldPrice! > price)
                                Padding(
                                  padding: const EdgeInsets.only(bottom: 3),
                                  child: Text(
                                    Fmt.price(product.oldPrice),
                                    style: ts(10, color: C.muted).copyWith(
                                      decoration: TextDecoration.lineThrough,
                                    ),
                                  ),
                                ),
                            ],
                          ),
                        ],
                      ),
                    ),
                    if (product.packs.isNotEmpty) ...[
                      const SizedBox(height: 18),
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 16),
                        child: SectionTitle(
                          title: tr(context, 'اختر العبوة'),
                          actionLabel: tArgs(context, '{0} خيارات', [
                            product.packs.length,
                          ]),
                          padding: EdgeInsets.zero,
                        ),
                      ),
                      const SizedBox(height: 8),
                      for (var i = 0; i < product.packs.length; i++)
                        Padding(
                          padding: const EdgeInsets.fromLTRB(16, 0, 16, 8),
                          child: _PackTile(
                            pack: product.packs[i],
                            selected: i == _packIndex,
                            onTap: () => setState(() => _packIndex = i),
                          ),
                        ),
                    ],
                    if (product.description.isNotEmpty) ...[
                      const SizedBox(height: 16),
                      Padding(
                        padding: const EdgeInsets.symmetric(horizontal: 16),
                        child: NovaCard(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                tr(context, 'الوصف'),
                                style: ts(13, weight: FontWeight.w700),
                              ),
                              const SizedBox(height: 8),
                              ReadMoreText(text: product.description),
                            ],
                          ),
                        ),
                      ),
                    ],
                  ],
                ),
        );
      },
    );
  }
}

class _PackTile extends StatelessWidget {
  const _PackTile({
    required this.pack,
    required this.selected,
    required this.onTap,
  });

  final ProductPack pack;
  final bool selected;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
        decoration: BoxDecoration(
          color: selected ? C.purpleSoft : Colors.white,
          borderRadius: BorderRadius.circular(16),
          border: Border.all(
            color: selected ? C.primary : C.border,
            width: selected ? 1.4 : 1,
          ),
        ),
        child: Row(
          children: [
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    pack.name,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: ts(12, weight: FontWeight.w600),
                  ),
                  if (pack.uomName.isNotEmpty)
                    Text(pack.uomName, style: ts(9, color: C.muted)),
                ],
              ),
            ),
            Text(
              Fmt.price(pack.price),
              style: ts(12, weight: FontWeight.w700, color: C.primaryDark),
            ),
            const SizedBox(width: 8),
            Icon(
              selected ? Icons.check_circle_rounded : Icons.circle_outlined,
              size: 18,
              color: selected ? C.primary : C.border,
            ),
          ],
        ),
      ),
    );
  }
}
