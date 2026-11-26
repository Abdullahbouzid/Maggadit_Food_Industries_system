import 'package:flutter/material.dart';

import '../../core/formatters.dart';
import '../../models/catalog.dart';
import '../theme/app_theme.dart';
import 'kit.dart';

class ProductCard extends StatelessWidget {
  const ProductCard({
    super.key,
    required this.product,
    required this.onTap,
    this.onAdd,
    this.isFavorite = false,
    this.onToggleFavorite,
    this.quantityInCart = 0,
    this.onIncrement,
    this.onDecrement,
  });

  final Product product;
  final VoidCallback onTap;
  final VoidCallback? onAdd;
  final bool isFavorite;
  final VoidCallback? onToggleFavorite;
  final double quantityInCart;
  final VoidCallback? onIncrement;
  final VoidCallback? onDecrement;

  @override
  Widget build(BuildContext context) {
    final pack = product.defaultPack;
    final subtitle = pack == null
        ? null
        : (pack.name.isNotEmpty ? pack.name : pack.uomName);

    return NovaCard(
      padding: EdgeInsets.zero,
      onTap: onTap,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.stretch,
        children: [
          Stack(
            children: [
              AspectRatio(
                aspectRatio: 1.25,
                child: RemoteImage(
                  url: product.image,
                  width: double.infinity,
                  height: double.infinity,
                  radius: 17,
                ),
              ),
              if (onToggleFavorite != null)
                Positioned(
                  top: 6,
                  right: 6,
                  child: _FavoriteButton(
                    active: isFavorite,
                    onTap: onToggleFavorite!,
                  ),
                ),
              if (product.oldPrice != null && product.oldPrice! > product.price)
                Positioned(
                  top: 6,
                  left: 6,
                  child: Tag(
                    text: '${Fmt.percent((1 - product.price / product.oldPrice!) * 100)} -',
                    color: Colors.white,
                    background: C.danger,
                  ),
                ),
            ],
          ),
          Padding(
            padding: const EdgeInsets.fromLTRB(10, 8, 10, 10),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  product.name,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: ts(11.5, weight: FontWeight.w700),
                ),
                if (subtitle != null && subtitle.isNotEmpty) ...[
                  const SizedBox(height: 2),
                  Text(
                    subtitle,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: ts(9, color: C.muted),
                  ),
                ],
                const SizedBox(height: 8),
                Row(
                  children: [
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            Fmt.price(product.price),
                            style: ts(12, weight: FontWeight.w700, color: C.primaryDark),
                          ),
                          if (product.oldPrice != null && product.oldPrice! > product.price)
                            Text(
                              Fmt.price(product.oldPrice),
                              style: ts(8.5, color: C.muted).copyWith(
                                decoration: TextDecoration.lineThrough,
                              ),
                            ),
                        ],
                      ),
                    ),
                    if (quantityInCart > 0 && onIncrement != null)
                      QtyStepper(
                        quantity: quantityInCart,
                        compact: true,
                        onIncrement: onIncrement,
                        onDecrement: onDecrement,
                      )
                    else if (onAdd != null)
                      SizedBox(
                        height: 30,
                        child: FilledButton(
                          onPressed: onAdd,
                          style: FilledButton.styleFrom(
                            backgroundColor: C.primary,
                            padding: const EdgeInsets.symmetric(horizontal: 12),
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(20),
                            ),
                          ),
                          child: Text('إضافة',
                              style: ts(10, weight: FontWeight.w700, color: Colors.white)),
                        ),
                      ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}

class _FavoriteButton extends StatelessWidget {
  const _FavoriteButton({required this.active, required this.onTap});

  final bool active;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        width: 30,
        height: 30,
        decoration: BoxDecoration(
          color: Colors.white.withValues(alpha: 0.92),
          shape: BoxShape.circle,
          boxShadow: Sh.soft,
        ),
        child: Icon(
          active ? Icons.favorite_rounded : Icons.favorite_border_rounded,
          size: 15,
          color: active ? C.danger : C.muted,
        ),
      ),
    );
  }
}

class CategoryChip extends StatelessWidget {
  const CategoryChip({
    super.key,
    required this.category,
    required this.onTap,
    this.selected = false,
  });

  final AppCategory category;
  final VoidCallback onTap;
  final bool selected;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: SizedBox(
        width: 66,
        child: Column(
          children: [
            Container(
              width: 56,
              height: 56,
              decoration: BoxDecoration(
                color: selected ? C.primary : Colors.white,
                shape: BoxShape.circle,
                border: Border.all(color: selected ? C.primary : C.border),
                boxShadow: Sh.soft,
              ),
              child: category.image == null
                  ? Icon(Icons.category_rounded,
                      size: 22, color: selected ? Colors.white : C.primary)
                  : ClipOval(
                      child: RemoteImage(
                        url: category.image,
                        width: 56,
                        height: 56,
                        radius: 28,
                        icon: Icons.category_rounded,
                      ),
                    ),
            ),
            const SizedBox(height: 6),
            Text(
              category.name,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              textAlign: TextAlign.center,
              style: ts(9, color: selected ? C.primaryDark : C.inkSoft,
                  weight: selected ? FontWeight.w700 : FontWeight.w500),
            ),
          ],
        ),
      ),
    );
  }
}

class BrandTile extends StatelessWidget {
  const BrandTile({super.key, required this.brand, required this.onTap});

  final AppBrand brand;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Column(
        children: [
          Container(
            width: 62,
            height: 62,
            padding: const EdgeInsets.all(10),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(18),
              border: Border.all(color: C.border),
              boxShadow: Sh.soft,
            ),
            child: Center(
              child: brand.logo == null
                  ? Text(brand.name.substring(0, 1), style: ts(16, weight: FontWeight.w700, color: C.primaryDark))
                  : RemoteImage(url: brand.logo, width: 44, height: 44, radius: 10,
                      icon: Icons.storefront_rounded),
            ),
          ),
          const SizedBox(height: 6),
          SizedBox(
            width: 66,
            child: Text(
              brand.name,
              maxLines: 1,
              overflow: TextOverflow.ellipsis,
              textAlign: TextAlign.center,
              style: ts(9, color: C.inkSoft),
            ),
          ),
        ],
      ),
    );
  }
}

class OfferCard extends StatelessWidget {
  const OfferCard({super.key, required this.offer, required this.onTap});

  final Offer offer;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return NovaCard(
      onTap: onTap,
      padding: const EdgeInsets.all(10),
      child: Row(
        children: [
          RemoteImage(url: offer.image, width: 76, height: 76, radius: 14),
          const SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  offer.title.isNotEmpty ? offer.title : offer.productName,
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: ts(12.5, weight: FontWeight.w700),
                ),
                const SizedBox(height: 3),
                Text(
                  offer.description.isEmpty ? offer.productName : offer.description,
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                  style: ts(9.5, color: C.muted, height: 1.5),
                ),
                const SizedBox(height: 7),
                Row(
                  children: [
                    Text(Fmt.price(offer.newPrice),
                        style: ts(12.5, weight: FontWeight.w700, color: C.primaryDark)),
                    const SizedBox(width: 6),
                    if (offer.oldPrice > offer.newPrice)
                      Text(
                        Fmt.price(offer.oldPrice),
                        style: ts(9, color: C.muted).copyWith(
                          decoration: TextDecoration.lineThrough,
                        ),
                      ),
                    const SizedBox(width: 6),
                    if (offer.discountPercentage > 0)
                      Tag(
                        text: 'خصم ${Fmt.percent(offer.discountPercentage)}',
                        color: Colors.white,
                        background: C.danger,
                      ),
                  ],
                ),
                if (offer.endDate != null) ...[
                  const SizedBox(height: 4),
                  Text('ينتهي ${Fmt.dateTime(offer.endDate)}',
                      style: ts(8.5, color: C.warning)),
                ],
              ],
            ),
          ),
        ],
      ),
    );
  }
}
