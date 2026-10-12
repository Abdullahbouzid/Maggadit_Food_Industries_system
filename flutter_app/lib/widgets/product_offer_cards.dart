import 'package:flutter/material.dart';

class ProductCard extends StatefulWidget {
  final Map<String, dynamic> product;
  final VoidCallback onFavoriteTap;
  final VoidCallback onAddToCart;

  const ProductCard({
    Key? key,
    required this.product,
    required this.onFavoriteTap,
    required this.onAddToCart,
  }) : super(key: key);

  @override
  State<ProductCard> createState() => _ProductCardState();
}

class _ProductCardState extends State<ProductCard> {
  @override
  Widget build(BuildContext context) {
    final product = widget.product;
    return GestureDetector(
      onTap: widget.onAddToCart,
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: const Color(0xFFE2E8F0)),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.05),
              blurRadius: 8,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Image placeholder
            Container(
              height: 120,
              width: double.infinity,
              decoration: BoxDecoration(
                color: const Color(0xFFF1F5F9),
                borderRadius: const BorderRadius.only(
                  topLeft: Radius.circular(20),
                  topRight: Radius.circular(20),
                ),
              ),
              child: product['image'] != null && product['image']!.isNotEmpty
                  ? ClipRRect(
                      borderRadius: const BorderRadius.only(
                        topLeft: Radius.circular(20),
                        topRight: Radius.circular(20),
                      ),
                      child: Image.network(
                        product['image']!,
                        fit: BoxFit.cover,
                      ),
                    )
                  : Center(
                      child: Text(
                        '📦',
                        style: TextStyle(
                          fontSize: 50,
                          color: Colors.grey[400],
                        ),
                      ),
                    ),
            ),
            // Product details
            Padding(
              padding: const EdgeInsets.all(12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Name
                  Text(
                    product['name'] ?? 'منتج',
                    style: const TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.bold,
                      color: Color(0xFF1E293B),
                    ),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 6),
                  // Pack info
                  if (product['pack_name'] != null && product['pack_name']!.isNotEmpty)
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                      decoration: BoxDecoration(
                        color: const Color(0xFFf1e6ff),
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Text(
                        product['pack_name'] as String,
                        style: const TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.w500,
                          color: Color(0xFF9879b6),
                        ),
                      ),
                    ),
                  const SizedBox(height: 8),
                  // Price and buttons
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      // Price
                      Text(
                        '${(product['price'] as num?)?.toStringAsFixed(2) ?? '0.00'} ر.س',
                        style: const TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                          color: Color(0xFF9879b6),
                        ),
                      ),
                      // Buttons
                      Row(
                        children: [
                          // Favorite button
                          GestureDetector(
                            onTap: widget.onFavoriteTap,
                            child: Container(
                              padding: const EdgeInsets.all(8),
                              decoration: BoxDecoration(
                                color: (product['is_favorite'] as bool? ?? false)
                                    ? const Color(0xFFfee2e2)
                                    : const Color(0xFFF1F5F9),
                                borderRadius: BorderRadius.circular(12),
                              ),
                              child: Icon(
                                (product['is_favorite'] as bool? ?? false)
                                    ? Icons.favorite
                                    : Icons.favorite_border,
                                color: (product['is_favorite'] as bool? ?? false)
                                    ? Colors.red
                                    : const Color(0xFF9CA3AF),
                                size: 20,
                              ),
                            ),
                          ),
                          const SizedBox(width: 8),
                          // Add to cart button (small icon)
                          Container(
                            padding: const EdgeInsets.all(8),
                            decoration: BoxDecoration(
                              color: const Color(0xFF9879b6).withOpacity(0.1),
                              borderRadius: BorderRadius.circular(12),
                            ),
                            child: const Icon(
                              Icons.add_shopping_cart,
                              color: Color(0xFF9879b6),
                              size: 20,
                            ),
                          ),
                        ],
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class OfferCard extends StatefulWidget {
  final Map<String, dynamic> offer;
  final VoidCallback onAddToCart;

  const OfferCard({
    Key? key,
    required this.offer,
    required this.onAddToCart,
  }) : super(key: key);

  @override
  State<OfferCard> createState() => _OfferCardState();
}

class _OfferCardState extends State<OfferCard> {
  @override
  Widget build(BuildContext context) {
    final offer = widget.offer;
    final discount = (offer['discount_percentage'] as int? ?? 0);
    final oldPrice = (offer['old_price'] as num? ?? 0);
    final newPrice = (offer['new_price'] as num? ?? 0);
    final hasOldPrice = oldPrice > 0;
    final hasDiscount = discount > 0;

    return GestureDetector(
      onTap: widget.onAddToCart,
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(20),
          border: Border.all(color: const Color(0xFFE2E8F0)),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.05),
              blurRadius: 8,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Image
            Container(
              height: 140,
              width: double.infinity,
              decoration: BoxDecoration(
                color: const Color(0xFFF1F5F9),
                borderRadius: const BorderRadius.only(
                  topLeft: Radius.circular(20),
                  topRight: Radius.circular(20),
                ),
              ),
              child: offer['image'] != null && offer['image']!.isNotEmpty
                  ? ClipRRect(
                      borderRadius: const BorderRadius.only(
                        topLeft: Radius.circular(20),
                        topRight: Radius.circular(20),
                      ),
                      child: Image.network(
                        offer['image']!,
                        fit: BoxFit.cover,
                      ),
                    )
                  : Container(
                      color: const Color(0xFFF9FAFB),
                      child: const Icon(Icons.local_offer, size: 60, color: Color(0xFF9CA3AF)),
                    ),
            ),
            // Offer details
            Padding(
              padding: const EdgeInsets.all(12),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  // Title
                  Text(
                    offer['title'] ?? 'عرض خاص',
                    style: const TextStyle(
                      fontSize: 16,
                      fontWeight: FontWeight.bold,
                      color: Color(0xFF1E293B),
                    ),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 6),
                  // Discount badge
                  if (hasDiscount)
                    Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                      decoration: BoxDecoration(
                        color: Colors.red.shade100,
                        borderRadius: BorderRadius.circular(10),
                      ),
                      child: Text(
                        '-${discount}%',
                        style: TextStyle(
                          fontSize: 11,
                          fontWeight: FontWeight.bold,
                          color: Colors.red.shade800,
                        ),
                      ),
                    ),
                  const SizedBox(height: 8),
                  // Description
                  Text(
                    offer['description'] ?? '',
                    style: const TextStyle(
                      fontSize: 13,
                      color: Color(0xFF374151),
                      height: 1.4,
                    ),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                  const SizedBox(height: 12),
                  // Price row
                  Row(
                    children: [
                      // Old price (strikethrough)
                      if (hasOldPrice)
                        Text(
                          '${oldPrice.toStringAsFixed(2)} ر.س',
                          style: TextStyle(
                            fontSize: 13,
                            color: Colors.grey[500],
                            decoration: TextDecoration.lineThrough,
                            decorationColor: Colors.grey[400],
                            decorationThickness: 1.5,
                          ),
                        ),
                      if (hasOldPrice) const SizedBox(width: 8),
                      // New price
                      Text(
                        '${newPrice.toStringAsFixed(2)} ر.س',
                        style: const TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                          color: Color(0xFF9879b6),
                        ),
                      ),
                      const Spacer(),
                      // Add to cart button
                      Container(
                        padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                        decoration: BoxDecoration(
                          color: const Color(0xFF9879b6),
                          borderRadius: BorderRadius.circular(20),
                        ),
                        child: const Text(
                          'إضافة',
                          style: TextStyle(
                            fontSize: 13,
                            fontWeight: FontWeight.w600,
                            color: Colors.white,
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}