import 'package:flutter/material.dart';
import '../../services/api_service.dart';
import '../../services/notifications_service.dart';

class CategoryScreen extends StatefulWidget {
  final String token;
  final String userId;
  final int categoryId;
  final String categoryName;

  const CategoryScreen({
    Key? key,
    required this.token,
    required this.userId,
    required this.categoryId,
    required this.categoryName,
  }) : super(key: key);

  @override
  _CategoryScreenState createState() => _CategoryScreenState();
}

class _CategoryScreenState extends State<CategoryScreen> {
  final NotificationsService _notificationsService = NotificationsService();
  final ApiService _apiService = ApiService();
  Future<List<Map<String, dynamic>>>? _itemsFuture;
  Map<String, dynamic> _category = {};
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _initService();
    _loadData();
  }

  void _initService() {
    _notificationsService.init(context, widget.token);
  }

  Future<void> _loadData() async {
    setState(() => _isLoading = true);
    try {
      final categoryResponse =
          await _apiService.get('/customer/categories/${widget.categoryId}');
      final productsResponse = await _apiService.get(
          '/customer/products?category_id=${widget.categoryId}');

      setState(() {
        _category = (categoryResponse['category'] as Map<String, dynamic>?) ?? {};
        _itemsFuture = Future.value(
          ((productsResponse['products']['data'] as List?)?.cast<Map<String, dynamic>>()) ?? <Map<String, dynamic>>[]
        );
        _isLoading = false;
      });
    } catch (e) {
      setState(() => _isLoading = false);
      if (!mounted) return;
      _showToast('Error loading category', isError: true);
    }
  }

  void _showToast(String message, {bool isError = false}) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: isError ? Colors.red : const Color(0xFF9879b6),
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        duration: const Duration(seconds: 2),
      ),
    );
  }

  void _toggleFavorite(int productId) async {
    try {
      await _apiService.post('/customer/favorites/toggle', body: {'item_id': productId});
      _showToast('تم تحديث المفضلة');
    } catch (e) {
      _showToast('Error updating favorite', isError: true);
    }
  }

  @override
  void dispose() {
    _notificationsService.stopPolling();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text(
          'رجوع',
          style: TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.w600,
            color: Color(0xFF6B7280),
          ),
        ),
        leading: const SizedBox(),
        actions: [
          IconButton(
            icon: const Icon(Icons.arrow_back_ios_new,
                color: Color(0xFF9879b6), size: 20),
            onPressed: () => Navigator.of(context).pop(),
          ),
        ],
        backgroundColor: Colors.white,
        elevation: 0,
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _loadData,
              child: SingleChildScrollView(
                physics: const BouncingScrollPhysics(),
                padding: const EdgeInsets.all(16),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _buildCategoryHeader(),
                    const SizedBox(height: 24),
                    _buildProductsGrid(),
                    const SizedBox(height: 80),
                  ],
                ),
              ),
            ),
    );
  }

  Widget _buildCategoryHeader() {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(28),
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
        children: [
          Container(
            width: 80,
            height: 80,
            decoration: BoxDecoration(
              color: const Color(0xFFF1F5F9),
              borderRadius: BorderRadius.circular(50),
            ),
            child: _category['image'] != null && _category['image']!.isNotEmpty
                ? ClipRRect(
                    borderRadius: BorderRadius.circular(50),
                    child: Image.network(
                      _category['image']!,
                      fit: BoxFit.cover,
                    ),
                  )
                : const Icon(Icons.folder, size: 40, color: Color(0xFF9CA3AF)),
          ),
          const SizedBox(height: 16),
          Text(
            _category['name'] ?? widget.categoryName,
            style: const TextStyle(
              fontSize: 22,
              fontWeight: FontWeight.bold,
              color: Color(0xFF1E293B),
            ),
          ),
          const SizedBox(height: 12),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                decoration: BoxDecoration(
                  color: const Color(0xFFF3E8FF),
                  borderRadius: BorderRadius.circular(20),
                ),
                child: Text(
                  '${_category['products_count'] ?? 0} منتج',
                  style: const TextStyle(
                    fontSize: 13,
                    fontWeight: FontWeight.w500,
                    color: Color(0xFF9879b6),
                  ),
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  Widget _buildProductsGrid() {
    return FutureBuilder<List<Map<String, dynamic>>>(
      future: _itemsFuture,
      builder: (context, snapshot) {
        if (snapshot.connectionState == ConnectionState.waiting) {
          return const Center(child: CircularProgressIndicator());
        }
        if (snapshot.hasError) {
          return const Center(child: Text('Error loading products'));
        }
        final items = snapshot.data ?? [];
        final productCards = <Widget>[];
        for (final item in items) {
          final packs = (item['packs'] as List?)?.cast<Map<String, dynamic>>() ?? [];
          final hasMultiplePacks = packs.length > 1;
          if (packs.isEmpty) {
            productCards.add(_buildProductCard(item, null, hasMultiplePacks));
          } else {
            for (final pack in packs) {
              productCards.add(_buildProductCard(item, pack, hasMultiplePacks));
            }
          }
        }

        if (productCards.isEmpty) {
          return const Center(
            child: Text('لا توجد منتجات في هذه الفئة'),
          );
        }

        return GridView.builder(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: 2,
            crossAxisSpacing: 16,
            mainAxisSpacing: 16,
            childAspectRatio: 0.76,
          ),
          itemCount: productCards.length,
          itemBuilder: (context, index) => productCards[index],
        );
      },
    );
  }

  Widget _buildProductCard(Map<String, dynamic> item, Map<String, dynamic>? pack, bool hasMultiplePacks) {
    final itemId = item['id'] ?? 0;
    final itemName = item['name'] ?? 'منتج';
    final itemImage = item['image'] ?? '';
    final isFavorite = item['is_favorite'] ?? item['favorite'] ?? false;
    final packName = pack?['name'] as String? ?? '';
    final packPrice = pack?['price'] as num? ?? item['price'] as num? ?? 0;
    final displayImage = itemImage.isNotEmpty ? itemImage : null;

    return Container(
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
          Stack(
            children: [
              Container(
                height: 128,
                width: double.infinity,
                decoration: BoxDecoration(
                  color: Colors.grey.shade100,
                  borderRadius: const BorderRadius.only(
                    topLeft: Radius.circular(20),
                    topRight: Radius.circular(20),
                  ),
                ),
                child: displayImage != null
                    ? ClipRRect(
                        borderRadius: const BorderRadius.only(
                          topLeft: Radius.circular(20),
                          topRight: Radius.circular(20),
                        ),
                        child: Image.network(
                          displayImage,
                          width: double.infinity,
                          height: 128,
                          fit: BoxFit.cover,
                          errorBuilder: (context, error, stackTrace) =>
                              const Icon(Icons.inventory, size: 44, color: Color(0xFF9CA3AF)),
                        ),
                      )
                    : const Icon(Icons.inventory, size: 44, color: Color(0xFF9CA3AF)),
              ),
              if (hasMultiplePacks && packName.isNotEmpty)
                Positioned(
                  top: 8,
                  right: 8,
                  child: Container(
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                    decoration: BoxDecoration(
                      color: const Color(0xFF9879b6),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Text(
                      packName,
                      style: const TextStyle(
                        fontSize: 10,
                        fontWeight: FontWeight.w600,
                        color: Colors.white,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                  ),
                ),
              Positioned(
                top: 8,
                left: 8,
                child: Container(
                  width: 32,
                  height: 32,
                  decoration: BoxDecoration(
                    color: Colors.white.withOpacity(0.85),
                    shape: BoxShape.circle,
                  ),
                  child: IconButton(
                    padding: EdgeInsets.zero,
                    icon: Icon(
                      isFavorite ? Icons.favorite : Icons.favorite_border,
                      color: isFavorite ? Colors.red : const Color(0xFF9CA3AF),
                      size: 18,
                    ),
                    onPressed: () => _toggleFavorite(itemId as int),
                  ),
                ),
              ),
            ],
          ),
          Padding(
            padding: const EdgeInsets.all(12),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  itemName,
                  style: const TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.bold,
                    color: Color(0xFF1E293B),
                  ),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
                if (hasMultiplePacks && packName.isNotEmpty)
                  Container(
                    margin: const EdgeInsets.only(top: 4),
                    padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                    decoration: BoxDecoration(
                      color: const Color(0xFFF3F4F6),
                      borderRadius: BorderRadius.circular(20),
                    ),
                    child: Text(
                      packName,
                      style: const TextStyle(
                        fontSize: 10,
                        color: Color(0xFF6B7280),
                      ),
                    ),
                  ),
                const SizedBox(height: 8),
                Text(
                  '${packPrice.toStringAsFixed(2)} ر.س',
                  style: const TextStyle(
                    fontSize: 15,
                    fontWeight: FontWeight.bold,
                    color: Color(0xFF9879b6),
                  ),
                ),
                const SizedBox(height: 10),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: () {
                      _showToast('تمت إضافة المنتج إلى السلة');
                    },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF9879b6),
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 10),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(24),
                      ),
                    ),
                    child: const Text(
                      'أضف إلى السلة',
                      style: TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
