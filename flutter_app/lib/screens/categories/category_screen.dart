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
  late Future<List<Map<String, dynamic>>> _itemsFuture;
  late Map<String, dynamic> _category;
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
          await _apiService.get('/api/categories/${widget.categoryId}');
      final productsResponse = await _apiService.get(
          '/api/products?category_id=${widget.categoryId}');

      setState(() {
        _category = (categoryResponse['data'] as Map<String, dynamic>?) ?? {};
        _itemsFuture = Future.value(
          ((productsResponse['data'] as List?)?.cast<Map<String, dynamic>>()) ?? <Map<String, dynamic>>[]
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
              color: Color(0xFF6B7280)),
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
          // Category image
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
          // Category name
          Text(
            _category['name'] ?? widget.categoryName,
            style: const TextStyle(
              fontSize: 22,
              fontWeight: FontWeight.bold,
              color: Color(0xFF1E293B),
            ),
          ),
          const SizedBox(height: 12),
          // Info row
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              // Products count
              Container(
                padding:
                    const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                decoration: BoxDecoration(
                  color: const Color(0xFFf1e6ff),
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
        if (items.isEmpty) {
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
            childAspectRatio: 0.78,
          ),
          itemCount: items.length,
          itemBuilder: (context, index) {
            final item = items[index];
            return _buildProductCard(item);
          },
        );
      },
    );
  }

  Widget _buildProductCard(Map<String, dynamic> item) {
    final name = item['name'] as String? ?? 'منتج';
    final price = (item['price'] as num?)?.toStringAsFixed(2) ?? '0.00';
    final image = item['image'] as String?;

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
          Expanded(
            child: Container(
              width: double.infinity,
              decoration: const BoxDecoration(
                color: Color(0xFFF1F5F9),
                borderRadius: BorderRadius.only(
                  topLeft: Radius.circular(24),
                  topRight: Radius.circular(24),
                ),
              ),
              child: Center(
                child: image != null && image.isNotEmpty
                    ? ClipRRect(
                        borderRadius: const BorderRadius.only(
                          topLeft: Radius.circular(24),
                          topRight: Radius.circular(24),
                        ),
                        child: Image.network(
                          image,
                          fit: BoxFit.cover,
                        ),
                      )
                    : const Icon(Icons.inventory,
                        size: 50, color: Color(0xFF9CA3AF)),
              ),
            ),
          ),
          // Details
          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  name,
                  style: const TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.bold,
                    color: Color(0xFF1E293B),
                  ),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
                const SizedBox(height: 8),
                Text(
                  '$price ر.س',
                  style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                    color: Color(0xFF9879b6),
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