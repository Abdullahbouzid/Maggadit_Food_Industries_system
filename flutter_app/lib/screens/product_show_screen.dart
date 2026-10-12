import 'package:flutter/material.dart';
import '../../services/api_service.dart';
import '../../services/notifications_service.dart';

class ProductShowScreen extends StatefulWidget {
  final String token;
  final String userId;
  final int productId;

  const ProductShowScreen({
    Key? key,
    required this.token,
    required this.userId,
    required this.productId,
  }) : super(key: key);

  @override
  _ProductShowScreenState createState() => _ProductShowScreenState();
}

class _ProductShowScreenState extends State<ProductShowScreen> {
  final NotificationsService _notificationsService = NotificationsService();
  final ApiService _apiService = ApiService();
  bool _isLoading = true;
  Map<String, dynamic>? _product;
  List<Map<String, dynamic>> _packs = [];
  bool _isFavorite = false;
  int _selectedImageIndex = 0;

  @override
  void initState() {
    super.initState();
    _initService();
    _loadProduct();
  }

  void _initService() {
    _notificationsService.init(context, widget.token);
  }

  Future<void> _loadProduct() async {
    setState(() => _isLoading = true);
    try {
      final response = await _apiService.get('/api/products/${widget.productId}');
      setState(() {
        _product = response['data'] as Map<String, dynamic>?;
        _packs = ((_product?['packs'] as List?)?.cast<Map<String, dynamic>>()) ?? [];
        _isFavorite = _product?['is_favorite'] as bool? ?? false;
        _isLoading = false;
      });
    } catch (e) {
      setState(() => _isLoading = false);
      if (!mounted) return;
      _showToast('Error loading product', isError: true);
    }
  }

  void _toggleFavorite() async {
    setState(() => _isFavorite = !_isFavorite);
    try {
      await _apiService.post('/api/products/${widget.productId}/favorite');
      _showToast(_isFavorite ? 'تمت الإضافة إلى المفضلة' : 'تمت الإزالة من المفضلة');
    } catch (e) {
      setState(() => _isFavorite = !_isFavorite);
      _showToast('Error updating favorite', isError: true);
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
        title: Text(_product?['name'] ?? 'تفاصيل المنتج',
            style: const TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.bold,
                color: Color(0xFF1E293B))),
        centerTitle: true,
        backgroundColor: Colors.white,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new,
              color: Color(0xFF1E293B), size: 20),
          onPressed: () => Navigator.of(context).pop(),
        ),
        actions: [
          IconButton(
            icon: Icon(
              _isFavorite ? Icons.favorite : Icons.favorite_border,
              color: _isFavorite ? Colors.red : const Color(0xFF9CA3AF),
              size: 24,
            ),
            onPressed: _toggleFavorite,
          ),
        ],
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              physics: const BouncingScrollPhysics(),
              padding: const EdgeInsets.all(20),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _buildProductImage(),
                  const SizedBox(height: 24),
                  if (_product != null) ...[
                    _buildProductInfo(),
                  ],
                ],
              ),
            ),
    );
  }

  Widget _buildProductImage() {
    final images = (_product?['images'] as List?)?.cast<String>() ?? [];
    final mainImage = _product?['image'] as String?;
    final hasImages = images.isNotEmpty;

    return Column(
      children: [
        Container(
          width: double.infinity,
          height: 300,
          decoration: BoxDecoration(
            color: const Color(0xFFF1F5F9),
            borderRadius: BorderRadius.circular(20),
          ),
          child: ClipRRect(
            borderRadius: BorderRadius.circular(20),
            child: _buildImageWidget(
              mainImage ?? (images.isNotEmpty ? images[0] : null),
              placeholderIcon: Icons.inventory,
              placeholderSize: 80,
            ),
          ),
        ),
        if (hasImages) ...[
          const SizedBox(height: 12),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: List.generate(images.length, (index) {
              final isSelected = _selectedImageIndex == index;
              return GestureDetector(
                onTap: () {
                  setState(() => _selectedImageIndex = index);
                },
                child: Container(
                  width: 60,
                  height: 60,
                  margin: const EdgeInsets.symmetric(horizontal: 4),
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(12),
                    border: Border.all(
                      color: isSelected ? const Color(0xFF9879b6) : Colors.transparent,
                      width: 2,
                    ),
                  ),
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(10),
                    child: _buildImageWidget(
                      images[index],
                      placeholderIcon: Icons.image,
                      placeholderSize: 24,
                    ),
                  ),
                ),
              );
            }),
          ),
        ],
      ],
    );
  }

  Widget _buildImageWidget(String? imageUrl, {required IconData placeholderIcon, required double placeholderSize}) {
    if (imageUrl != null && imageUrl.isNotEmpty) {
      return Image.network(
        imageUrl,
        fit: BoxFit.cover,
        errorBuilder: (context, error, stackTrace) => Icon(
          placeholderIcon,
          size: placeholderSize,
          color: const Color(0xFF9CA3AF),
        ),
      );
    }
    return Icon(
      placeholderIcon,
      size: placeholderSize,
      color: const Color(0xFF9CA3AF),
    );
  }

  Widget _buildProductInfo() {
    final productName = _product?['name'] ?? 'منتج';
    final price = (_product?['price'] as num? ?? 0);
    final oldPrice = _product?['old_price'] as num?;
    final hasOldPrice = oldPrice != null && oldPrice > 0;
    final description = _product?['description'] as String?;

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          productName,
          style: const TextStyle(
            fontSize: 24,
            fontWeight: FontWeight.bold,
            color: Color(0xFF1E293B),
          ),
        ),
        const SizedBox(height: 8),
        Row(
          children: [
            Text(
              '${price.toStringAsFixed(2)} ر.س',
              style: const TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.bold,
                color: Color(0xFF9879b6),
              ),
            ),
            if (hasOldPrice) ...[
              const SizedBox(width: 8),
              Text(
                '${oldPrice!.toStringAsFixed(2)} ر.س',
                style: TextStyle(
                  fontSize: 16,
                  color: Colors.grey[500],
                  decoration: TextDecoration.lineThrough,
                  decorationColor: Colors.grey[400],
                  decorationThickness: 1.5,
                ),
              ),
            ],
          ],
        ),
        if (description != null && description.isNotEmpty) ...[
          const SizedBox(height: 16),
          Text(
            description,
            style: const TextStyle(
              fontSize: 14,
              color: Color(0xFF374151),
              height: 1.5,
            ),
          ),
        ],
        const SizedBox(height: 24),

        // Packs section
        if (_packs.isNotEmpty) ...[
          _buildPacksTitle(),
          const SizedBox(height: 12),
          ..._packs.asMap().entries.map((entry) {
            final index = entry.key;
            final pack = entry.value;
            final isActive = index == 0;
            return _buildPackCard(pack, index, isActive: isActive);
          }).toList(),
        ] else ...[
          _buildStandardPack(),
        ],
        const SizedBox(height: 24),

        // Add to cart button
        SizedBox(
          width: double.infinity,
          child: ElevatedButton(
            onPressed: () {
              _showToast('تمت الإضافة إلى السلة');
            },
            style: ElevatedButton.styleFrom(
              backgroundColor: const Color(0xFF9879b6),
              foregroundColor: Colors.white,
              padding: const EdgeInsets.symmetric(vertical: 16),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(28),
              ),
            ),
            child: const Text(
              'أضف إلى السلة',
              style: TextStyle(
                fontSize: 16,
                fontWeight: FontWeight.w600,
              ),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildPacksTitle() {
    return Row(
      children: [
        Container(
          width: 28,
          height: 28,
          decoration: BoxDecoration(
            borderRadius: BorderRadius.circular(9),
            gradient: const LinearGradient(
              colors: [Color(0xFF9879b6), Color(0xFF6b21a8)],
            ),
          ),
          child: const Icon(
            Icons.inventory,
            color: Colors.white,
            size: 14,
          ),
        ),
        const SizedBox(width: 8),
        Text(
          'العبوات المتاحة',
          style: const TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.w800,
            color: Color(0xFF1E293B),
          ),
        ),
        const Spacer(),
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 9, vertical: 3),
          decoration: BoxDecoration(
            color: const Color(0xFFf3e8ff),
            borderRadius: BorderRadius.circular(999),
          ),
          child: Text(
            '${_packs.length} خيار',
            style: const TextStyle(
              fontSize: 11,
              fontWeight: FontWeight.w600,
              color: Color(0xFF9879b6),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildPackCard(Map<String, dynamic> pack, int index, {bool isActive = false}) {
    final packName = pack['name'] as String? ?? 'الوحدة';
    final packPrice = (pack['price'] as num? ?? 0);
    final uomName = pack['uom_name'] as String? ?? '';
    final quantity = pack['quantity'] as num?;
    final isPopular = index == 0 && _packs.length > 1;

    return Stack(
      children: [
        Container(
          margin: const EdgeInsets.only(bottom: 12),
          padding: const EdgeInsets.all(16),
          decoration: BoxDecoration(
            color: isActive
                ? const Color(0xFFfaf5ff)
                : Colors.transparent,
            border: Border.all(
              color: isActive ? const Color(0xFF9333ea) : const Color(0xFFE2E8F0),
              width: 1.5,
            ),
            borderRadius: BorderRadius.circular(20),
            boxShadow: [
              if (isActive)
                BoxShadow(
                  color: const Color(0xFF9333ea).withOpacity(0.35),
                  blurRadius: 20,
                  offset: const Offset(0, 8),
                ),
              if (!isActive)
                BoxShadow(
                  color: Colors.black.withOpacity(0.05),
                  blurRadius: 6,
                  offset: const Offset(0, 2),
                ),
            ],
          ),
          child: Row(
            children: [
              // Pack number badge
              Container(
                width: 36,
                height: 36,
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(12),
                  gradient: const LinearGradient(
                    colors: [Color(0xFF9879b6), Color(0xFF6b21a8)],
                  ),
                  boxShadow: [
                    BoxShadow(
                      color: const Color(0xFF9333ea).withOpacity(0.5),
                      blurRadius: 12,
                      offset: const Offset(0, 6),
                    ),
                  ],
                ),
                child: Center(
                  child: Text(
                    (index + 1).toString(),
                    style: const TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.bold,
                      color: Colors.white,
                    ),
                  ),
                ),
              ),
              const SizedBox(width: 16),
              // Pack details
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      packName,
                      style: const TextStyle(
                        fontSize: 15,
                        fontWeight: FontWeight.w800,
                        color: Color(0xFF1E293B),
                      ),
                    ),
                    const SizedBox(height: 4),
                    if (uomName.isNotEmpty)
                      Text(
                        uomName,
                        style: TextStyle(
                          fontSize: 11,
                          color: Colors.grey[600],
                        ),
                      ),
                    if (quantity != null)
                      Padding(
                        padding: const EdgeInsets.only(top: 2),
                        child: Text(
                          '${quantity.toInt()} قطعة',
                          style: TextStyle(
                            fontSize: 11,
                            color: Colors.grey[600],
                          ),
                        ),
                      ),
                  ],
                ),
              ),
              // Pack price
              Text(
                '${packPrice.toStringAsFixed(2)}',
                style: const TextStyle(
                  fontSize: 16,
                  fontWeight: FontWeight.bold,
                  color: Color(0xFF9879b6),
                ),
              ),
              const SizedBox(width: 4),
              const Text(
                'ر.س',
                style: TextStyle(
                  fontSize: 12,
                  color: Color(0xFF9CA3AF),
                ),
              ),
            ],
          ),
        ),
        if (isPopular)
          Positioned(
            top: 0,
            left: 16,
            child: Container(
              padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(0),
                gradient: const LinearGradient(
                  colors: [Color(0xFFF59E0B), Color(0xFFD97706)],
                ),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withOpacity(0.2),
                    blurRadius: 6,
                    offset: const Offset(0, 2),
                  ),
                ],
              ),
              child: const Text(
                'الأكثر طلبًا',
                style: TextStyle(
                  fontSize: 9.5,
                  fontWeight: FontWeight.w800,
                  color: Colors.white,
                  letterSpacing: 0.3,
                ),
              ),
            ),
          ),
      ],
    );
  }

  Widget _buildStandardPack() {
    final price = (_product?['price'] as num? ?? 0);

    return Container(
      margin: const EdgeInsets.only(bottom: 12),
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        borderRadius: BorderRadius.circular(18),
        gradient: const LinearGradient(
          colors: [Color(0xFFfaf5ff), Color(0xFFf3e8ff)],
        ),
        border: Border.all(
          color: const Color(0xFFe9d5ff),
          width: 1.5,
        ),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 6,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Stack(
        children: [
          Positioned(
            top: -20,
            left: -20,
            child: Container(
              width: 80,
              height: 80,
              decoration: BoxDecoration(
                color: const Color(0xFFc084fc),
                borderRadius: BorderRadius.circular(50),
              ),
              child: const SizedBox.shrink(),
              foregroundDecoration: BoxDecoration(
                borderRadius: BorderRadius.circular(50),
              ),
            ),
          ),
          Row(
            crossAxisAlignment: CrossAxisAlignment.center,
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        const Icon(Icons.grid_on, size: 14, color: Color(0xFF6b21a8)),
                        const SizedBox(width: 5),
                        Text(
                          'الوحدة القياسية',
                          style: const TextStyle(
                            fontSize: 12.5,
                            fontWeight: FontWeight.w700,
                            color: Color(0xFF6b21a8),
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 4),
                    Text(
                      '${price.toStringAsFixed(2)} ر.س',
                      style: const TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.w900,
                        color: Color(0xFF9879b6),
                        letterSpacing: -0.5,
                      ),
                    ),
                  ],
                ),
              ),
              Container(
                padding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                decoration: BoxDecoration(
                  borderRadius: BorderRadius.circular(14),
                  gradient: const LinearGradient(
                    colors: [Color(0xFF9879b6), Color(0xFF6b21a8)],
                  ),
                  boxShadow: [
                    BoxShadow(
                      color: const Color(0xFF9333ea).withOpacity(0.65),
                      blurRadius: 18,
                      offset: const Offset(0, 8),
                    ),
                  ],
                ),
                child: const Row(
                  children: [
                    Icon(Icons.shopping_cart, color: Colors.white, size: 16),
                    SizedBox(width: 6),
                    Text(
                      'أضف للسلة',
                      style: TextStyle(
                        fontSize: 13,
                        fontWeight: FontWeight.w800,
                        color: Colors.white,
                      ),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}
