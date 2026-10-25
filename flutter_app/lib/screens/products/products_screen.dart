import 'package:flutter/material.dart';
import '../../services/api_service.dart';
import '../../services/notifications_service.dart';

class ProductsScreen extends StatefulWidget {
  final String token;
  final String userId;

  const ProductsScreen({
    Key? key,
    required this.token,
    required this.userId,
  }) : super(key: key);

  @override
  _ProductsScreenState createState() => _ProductsScreenState();
}

class _ProductsScreenState extends State<ProductsScreen> {
  final NotificationsService _notificationsService = NotificationsService();
  final ApiService _apiService = ApiService();
  late Future<List<Map<String, dynamic>>> _itemsFuture;
  bool _isLoading = true;
  String _searchQuery = '';
  int? _selectedCategory;
  int? _selectedBrand;
  List<Map<String, dynamic>> _categories = [];
  List<Map<String, dynamic>> _brands = [];

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
      final productsResponse = await _apiService.get(
        '/customer/products?${_buildQueryParams()}',
      );
      final categoriesResponse = await _apiService.get('/customer/categories');
      final brandsResponse = await _apiService.get('/customer/brands');

      setState(() {
        _itemsFuture = Future.value(
          ((productsResponse['products']['data'] as List?)?.cast<Map<String, dynamic>>()) ?? <Map<String, dynamic>>[]
        );
        _categories = ((categoriesResponse['categories'] as List?)?.cast<Map<String, dynamic>>()) ?? <Map<String, dynamic>>[];
        _brands = ((brandsResponse['brands'] as List?)?.cast<Map<String, dynamic>>()) ?? <Map<String, dynamic>>[];
        _isLoading = false;
      });
    } catch (e) {
      setState(() => _isLoading = false);
      if (!mounted) return;
      _showToast('Error loading products', isError: true);
    }
  }

  String _buildQueryParams() {
    final params = <String>[];
    if (_searchQuery.isNotEmpty) params.add('search=$_searchQuery');
    if (_selectedCategory != null) params.add('category_id=$_selectedCategory');
    if (_selectedBrand != null) params.add('brand_id=$_selectedBrand');
    return params.join('&');
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

  void _applyFilters() {
    _loadData();
  }

  void _resetFilters() {
    setState(() {
      _searchQuery = '';
      _selectedCategory = null;
      _selectedBrand = null;
    });
    _loadData();
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
                    _buildFilters(),
                    const SizedBox(height: 16),
                    _buildProductsGrid(),
                  ],
                ),
              ),
            ),
    );
  }

  Widget _buildFilters() {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(24),
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
          Padding(
            padding: const EdgeInsets.all(16),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                const Text(
                  'تصفية المنتجات',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                    color: Color(0xFF1E293B),
                  ),
                ),
                TextButton(
                  onPressed: _resetFilters,
                  child: const Text(
                    'مسح الفلاتر',
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                      color: Color(0xFF9879b6),
                    ),
                  ),
                ),
              ],
            ),
          ),
          Padding(
            padding: const EdgeInsets.symmetric(horizontal: 16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                const Text(
                  'الفئة',
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w600,
                    color: Color(0xFF6B7280),
                  ),
                ),
                const SizedBox(height: 4),
                DropdownButtonFormField<int>(
                  value: _selectedCategory,
                  hint: const Text('كل الفئات',
                      style: TextStyle(fontSize: 13, color: Color(0xFF9CA3AF))),
                  decoration: InputDecoration(
                    isDense: true,
                    filled: true,
                    fillColor: const Color(0xFFF9FAFB),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: BorderSide.none,
                    ),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                  ),
                  items: [
                    const DropdownMenuItem(
                      value: null,
                      child: Text('كل الفئات',
                          style: TextStyle(fontSize: 13, color: Color(0xFF9CA3AF))),
                    ),
                    ..._categories.map((cat) => DropdownMenuItem(
                          value: cat['id'] as int?,
                          child: Text(cat['name'] ?? '',
                              style: const TextStyle(fontSize: 13, color: Color(0xFF1E293B))),
                        )),
                  ],
                  onChanged: (value) {
                    setState(() => _selectedCategory = value);
                  },
                ),
                const SizedBox(height: 12),
                const Text(
                  'الماركة',
                  style: TextStyle(
                    fontSize: 11,
                    fontWeight: FontWeight.w600,
                    color: Color(0xFF6B7280),
                  ),
                ),
                const SizedBox(height: 4),
                DropdownButtonFormField<int>(
                  value: _selectedBrand,
                  hint: const Text('كل الماركات',
                      style: TextStyle(fontSize: 13, color: Color(0xFF9CA3AF))),
                  decoration: InputDecoration(
                    isDense: true,
                    filled: true,
                    fillColor: const Color(0xFFF9FAFB),
                    border: OutlineInputBorder(
                      borderRadius: BorderRadius.circular(12),
                      borderSide: BorderSide.none,
                    ),
                    contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 8),
                  ),
                  items: [
                    const DropdownMenuItem(
                      value: null,
                      child: Text('كل الماركات',
                          style: TextStyle(fontSize: 13, color: Color(0xFF9CA3AF))),
                    ),
                    ..._brands.map((brand) => DropdownMenuItem(
                          value: brand['id'] as int?,
                          child: Text(brand['name'] ?? '',
                              style: const TextStyle(fontSize: 13, color: Color(0xFF1E293B))),
                        )),
                  ],
                  onChanged: (value) {
                    setState(() => _selectedBrand = value);
                  },
                ),
              ],
            ),
          ),
          Padding(
            padding: const EdgeInsets.all(16),
            child: SizedBox(
              width: double.infinity,
              child: ElevatedButton(
                onPressed: _applyFilters,
                style: ElevatedButton.styleFrom(
                  backgroundColor: const Color(0xFF9879b6),
                  foregroundColor: Colors.white,
                  padding: const EdgeInsets.symmetric(vertical: 12),
                  shape: RoundedRectangleBorder(
                    borderRadius: BorderRadius.circular(24),
                  ),
                ),
                child: const Row(
                  mainAxisAlignment: MainAxisAlignment.center,
                  children: [
                    Icon(Icons.filter_list, size: 16, color: Colors.white),
                    SizedBox(width: 6),
                    Text(
                      'تطبيق الفلاتر',
                      style: TextStyle(
                        fontSize: 14,
                        fontWeight: FontWeight.w600,
                      ),
                    ),
                  ],
                ),
              ),
            ),
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
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(Icons.inventory_outlined, size: 64, color: Color(0xFF9CA3AF)),
                SizedBox(height: 16),
                Text('لا توجد منتجات متاحة حالياً'),
              ],
            ),
          );
        }

        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Padding(
              padding: const EdgeInsets.only(bottom: 12),
              child: Text(
                '${productCards.length} منتج',
                style: const TextStyle(
                  fontSize: 13,
                  color: Color(0xFF9CA3AF),
                ),
              ),
            ),
            GridView.builder(
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
            ),
          ],
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
              if (hasMultiplePacks)
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
