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
  late Future<List<Map<String, dynamic>>> _productsFuture;
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
      final productsResponse = await _apiService.get('/api/products');
      final categoriesResponse = await _apiService.get('/api/categories');
      final brandsResponse = await _apiService.get('/api/brands');

      setState(() {
        _productsFuture = Future.value(
          ((productsResponse['data'] as List?)?.cast<Map<String, dynamic>>()) ?? <Map<String, dynamic>>[]
        );
        _categories = ((categoriesResponse['data'] as List?)?.cast<Map<String, dynamic>>()) ?? <Map<String, dynamic>>[];
        _brands = ((brandsResponse['data'] as List?)?.cast<Map<String, dynamic>>()) ?? <Map<String, dynamic>>[];
        _isLoading = false;
      });
    } catch (e) {
      setState(() => _isLoading = false);
      if (!mounted) return;
      _showToast('Error loading products', isError: true);
    }
  }

  void _showToast(String message, {bool isError = false}) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: isError ? Colors.red : Color(0xFF9879b6),
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        duration: const Duration(seconds: 2),
      ),
    );
  }

  void _toggleFavorite(int productId) async {
    try {
      await _apiService.post('/api/products/$productId/favorite');
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
          'جميع المنتجات',
          style: TextStyle(
            fontSize: 20,
            fontWeight: FontWeight.bold,
            color: Color(0xFF1E293B),
          ),
        ),
        centerTitle: true,
        backgroundColor: Colors.white,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new,
              color: Color(0xFF1E293B), size: 20),
          onPressed: () => Navigator.of(context).pop(),
        ),
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
                    _buildSearchAndFilters(),
                    const SizedBox(height: 20),
                    _buildProductsGrid(),
                  ],
                ),
              ),
            ),
    );
  }

  Widget _buildSearchAndFilters() {
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
        children: [
          // Search bar
          Padding(
            padding: const EdgeInsets.all(16),
            child: TextField(
              decoration: InputDecoration(
                hintText: 'ابحث عن منتج...',
                prefixIcon: const Icon(Icons.search, color: Color(0xFF6B7280)),
                suffixIcon: _searchQuery.isNotEmpty
                    ? IconButton(
                        icon: const Icon(Icons.clear, color: Color(0xFF9CA3AF)),
                        onPressed: () {
                          setState(() => _searchQuery = '');
                        },
                      )
                    : null,
                filled: true,
                fillColor: const Color(0xFFF9FAFB),
                border: OutlineInputBorder(
                  borderRadius: BorderRadius.circular(12),
                  borderSide: BorderSide.none,
                ),
                contentPadding: const EdgeInsets.symmetric(horizontal: 16),
              ),
              onChanged: (value) {
                setState(() => _searchQuery = value);
              },
            ),
          ),
          // Filter chips
          if (_categories.isNotEmpty || _brands.isNotEmpty)
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
              child: Wrap(
                spacing: 8,
                runSpacing: 8,
                children: [
                  // Category filter
                  if (_categories.isNotEmpty)
                    ChoiceChip(
                      label: const Text('الفئة'),
                      selected: _selectedCategory != null,
                      onSelected: (selected) {
                        setState(() {
                          _selectedCategory = selected ? _categories.first['id'] : null;
                        });
                      },
                      labelStyle: const TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w500,
                      ),
                      selectedColor: Color(0xFFf1e6ff),
                      backgroundColor: const Color(0xFFf8fafc),
                    ),
                  // Brand filter
                  if (_brands.isNotEmpty)
                    ChoiceChip(
                      label: const Text('الماركة'),
                      selected: _selectedBrand != null,
                      onSelected: (selected) {
                        setState(() {
                          _selectedBrand = selected ? _brands.first['id'] : null;
                        });
                      },
                      labelStyle: const TextStyle(
                        fontSize: 12,
                        fontWeight: FontWeight.w500,
                      ),
                      selectedColor: Color(0xFFf1e6ff),
                      backgroundColor: const Color(0xFFf8fafc),
                    ),
                ],
              ),
            ),
        ],
      ),
    );
  }

  Widget _buildProductsGrid() {
    return FutureBuilder<List<Map<String, dynamic>>>(
      future: _productsFuture,
      builder: (context, snapshot) {
        if (snapshot.connectionState == ConnectionState.waiting) {
          return const Center(child: CircularProgressIndicator());
        }
        if (snapshot.hasError) {
          return const Center(child: Text('Error loading products'));
        }
        final products = snapshot.data ?? [];
        if (products.isEmpty) {
          return const Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(Icons.inventory_outlined, size: 64, color: Color(0xFF9CA3AF)),
                const SizedBox(height: 16),
                Text('لا توجد منتجات متاحة حالياً'),
              ],
            ),
          );
        }
        return GridView.builder(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: 2,
            crossAxisSpacing: 16,
            mainAxisSpacing: 16,
            childAspectRatio: 0.78,
          ),
          itemCount: products.length,
          itemBuilder: (context, index) {
            final product = products[index];
            return _buildProductCard(product);
          },
        );
      },
    );
  }

  Widget _buildProductCard(Map<String, dynamic> product) {
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
          // Image
          Container(
            height: 120,
            width: double.infinity,
            decoration: BoxDecoration(
              color: const Color(0xFFF1F5F9),
              borderRadius: const BorderRadius.only(
                topLeft: Radius.circular(24),
                topRight: Radius.circular(24),
              ),
            ),
            child: product['image'] != null && product['image']!.isNotEmpty
                ? ClipRRect(
                    borderRadius: const BorderRadius.only(
                      topLeft: Radius.circular(24),
                      topRight: Radius.circular(24),
                    ),
                    child: Image.network(
                      product['image']!,
                      fit: BoxFit.cover,
                    ),
                  )
                : Container(
                    color: const Color(0xFFF9FAFB),
                    child: const Icon(Icons.inventory,
                        size: 50, color: Color(0xFF9CA3AF)),
                  ),
          ),
          // Details
          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Name
                Text(
                  product['name'] ?? 'منتج',
                  style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                    color: Color(0xFF1E293B),
                  ),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
                const SizedBox(height: 8),
                // Price
                Text(
                  '${(product['price'] as num? ?? 0).toStringAsFixed(2)} ر.س',
                  style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                    color: Color(0xFF9879b6),
                  ),
                ),
                const SizedBox(height: 12),
                // Add to cart button
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: () {
                      _showToast('تمت إضافة المنتج إلى السلة');
                    },
                    style: ElevatedButton.styleFrom(
                      backgroundColor: const Color(0xFF9879b6),
                      foregroundColor: Colors.white,
                      padding: const EdgeInsets.symmetric(vertical: 12),
                      shape: RoundedRectangleBorder(
                        borderRadius: BorderRadius.circular(24),
                      ),
                    ),
                    child: const Text(
                      'أضف إلى السلة',
                      style: TextStyle(
                        fontSize: 14,
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