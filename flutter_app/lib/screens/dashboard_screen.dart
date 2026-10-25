import 'package:flutter/material.dart';
import 'dart:convert';
import 'package:shared_preferences/shared_preferences.dart';
import '../models/user_model.dart';
import '../models/notification_model.dart';
import '../services/api_service.dart';
import '../services/notifications_service.dart';
import '../theme/app_theme.dart';
import '../screens/products/products_screen.dart';
import '../screens/offers/offers_screen.dart';
import '../screens/profile_screen.dart';
import '../screens/notifications_screen.dart';
import '../screens/cart/cart_screen.dart';
import '../screens/categories/category_screen.dart';
import '../screens/brands/brand_screen.dart';
import '../screens/product_show_screen.dart';
import '../screens/more/more_menu_screen.dart';
import '../widgets/product_offer_cards.dart';
import 'package:flutter_svg/flutter_svg.dart';

class DashboardScreen extends StatefulWidget {
  final String token;
  final String userId;

  const DashboardScreen({
    super.key,
    required this.token,
    required this.userId,
  });

  @override
  _DashboardScreenState createState() => _DashboardScreenState();
}

class _DashboardScreenState extends State<DashboardScreen>
    with TickerProviderStateMixin {
  int _currentIndex = 0;
  UserModel? _user;
  final List<NotificationModel> _notifications = [];
  final int _unreadCount = 0;
  final NotificationsService _notificationsService = NotificationsService();

  // Controllers for page view
  late PageController _pageController;

  // Dashboard data - loaded from /dashboard API endpoint
  late Future<Map<String, dynamic>> _dashboardDataFuture;

  @override
  void initState() {
    super.initState();
    _pageController = PageController(initialPage: _currentIndex);
    _dashboardDataFuture = _loadDashboard();
    _initService();
  }

  void _initService() {
    _notificationsService.init(context, widget.token);
  }

  Future<Map<String, dynamic>> _loadDashboard() async {
    final api = ApiService();
    try {
      final response = await api.get('/customer/dashboard');
      return response;
    } catch (e) {
      return {'success': false, 'banners': [], 'categories': [], 'brands': [], 'products': []};
    }
  }

  void _switchPage(int index) {
    if (_currentIndex != index) {
      setState(() => _currentIndex = index);
      _pageController.jumpToPage(index);
    }
  }

  // Navigate to a specific route
  void _navigateTo(String route) {
    // Navigate and reset the page to index 0
    // For simplicity, just change the current index and let the page view handle it
    // In a full implementation, would use Navigator.pushNamed
    setState(() {
      // Map indices: 0=home, 1=offers, 2=cart, 3=favorites, 4=profile
      // But currently we only have home + offers + cart logic
      if (route == '/offers') {
        _currentIndex = 1;
      } else if (route == '/cart') {
        _currentIndex = 2;
      } else {
        _currentIndex = 0;
      }
      _pageController.jumpToPage(_currentIndex);
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Color(0xFFf8fafc),
      body: PageView(
        controller: _pageController,
        physics: const NeverScrollableScrollPhysics(),
        children: [
          // Index 0: Home screen with categories, brands, products
          _buildHomeScreen(),
          // Index 1: Offers screen
          OffersScreen(
            token: widget.token,
            userId: widget.userId,
          ),
          // Index 2: Cart screen
          CartScreen(
            token: widget.token,
            userId: widget.userId,
          ),
          // Index 3: Favorites (placeholder)
          _buildFavoriteScreen(),
          // Index 4: More menu
          MoreMenuScreen(
            token: widget.token,
            userId: widget.userId,
          ),
        ],
      ),
      bottomNavigationBar: _buildBottomNav(),
    );
  }

  Widget _buildHomeScreen() {
    return SingleChildScrollView(
      physics: AlwaysScrollableScrollPhysics(parent: BouncingScrollPhysics()),
      padding: const EdgeInsets.all(16),
      child: FutureBuilder<Map<String, dynamic>>(
        future: _dashboardDataFuture,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          }
          final data = snapshot.data ?? {};
          if (snapshot.data?['success'] != true) {
            return _buildEmptyState();
          }

          final banners = (data['banners'] as List? ?? [])
              .cast<Map<String, dynamic>>();
          final categories = (data['categories'] as List? ?? [])
              .cast<Map<String, dynamic>>();
          final brands = (data['brands'] as List? ?? [])
              .cast<Map<String, dynamic>>();
          final products = (data['products'] as List? ?? [])
              .cast<Map<String, dynamic>>();

          return Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Header
              _buildHeader(),
              const SizedBox(height: 20),

// Banner carousel
              _buildBannerCarousel(banners: banners),
              const SizedBox(height: 20),

              // Categories section
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _buildSectionTitle('الفئات'),
                  _buildGridItems(categories, _buildCategoryItem),
                  const SizedBox(height: 24),
                ],
              ),
              const SizedBox(height: 16),

              // Brands section
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _buildSectionTitle('ماركات شهيرة'),
                  _buildGridItems(brands, _buildBrandItem),
                  const SizedBox(height: 24),
                ],
              ),
              const SizedBox(height: 16),

              // Products section
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _buildSectionTitle('أفضل المنتجات'),
                  _buildGridItems(products, _buildProductItem),
                  const SizedBox(height: 80),
                ],
              ),
            ],
          );
        },
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.inbox, size: 64, color: Color(0xFFC4B5FD)),
          const SizedBox(height: 16),
          const Text(
            'لا توجد بيانات متاحة حالياً',
            style: TextStyle(fontSize: 16, color: Color(0xFF64748B)),
          ),
        ],
      ),
    );
  }

  Widget _buildSectionTitle(String title) {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        Text(
          title,
          style: const TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.bold,
            color: Color(0xFF1E293B),
          ),
        ),
        TextButton(
          onPressed: () {
            // View all
          },
          child: const Text(
            'عرض الكل',
            style: TextStyle(
              color: Color(0xFF9879b6),
              fontSize: 13,
              fontWeight: FontWeight.w600,
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildGridItems(List<Map<String, dynamic>> items,
      Widget Function(Map<String, dynamic>) itemBuilder) {
    if (items.isEmpty) {
      return const Center(
        child: Text('لا توجد عناصر متاحة'),
      );
    }
    return SizedBox(
      height: 110,
      child: ListView.separated(
        scrollDirection: Axis.horizontal,
        shrinkWrap: true,
        physics: const BouncingScrollPhysics(),
        padding: EdgeInsets.zero,
        itemCount: items.length,
        separatorBuilder: (context, index) => const SizedBox(width: 12),
        itemBuilder: (context, index) => itemBuilder(items[index]),
      ),
    );
  }

  Widget _buildCategoryItem(Map<String, dynamic> category) {
    final name = category['name'] as String? ?? 'فئة';
    final image = category['image'] as String?;
    final productsCount = category['products_count'] as int? ?? 0;

    return GestureDetector(
      onTap: () {
        final id = category['id'] as int? ?? 0;
        Navigator.of(context).push(
          MaterialPageRoute(
            builder: (context) => CategoryScreen(
              token: widget.token,
              userId: widget.userId,
              categoryId: id,
              categoryName: name,
            ),
          ),
        );
      },
      child: Column(
        children: [
          Container(
            width: 64,
            height: 64,
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(32),
              border: Border.all(color: const Color(0xFFE2E8F0)),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.05),
                  blurRadius: 8,
                  offset: const Offset(0, 2),
                ),
              ],
            ),
            child: Center(
              child: image != null && image.isNotEmpty
                  ? Image.network(
                      image,
                      width: 48,
                      height: 48,
                      fit: BoxFit.cover,
                      errorBuilder: (context, error, stackTrace) =>
                          const Icon(Icons.broken_image,
                              size: 32, color: Color(0xFF9CA3AF)),
                    )
                  : const Icon(Icons.folder, size: 32, color: Color(0xFF9CA3AF)),
            ),
          ),
          const SizedBox(height: 6),
          Text(
            name,
            style: const TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.bold,
              color: Color(0xFF1E293B),
            ),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
          const SizedBox(height: 2),
          Text(
            '$productsCount منتج',
            style: const TextStyle(fontSize: 10, color: Color(0xFF9CA3AF)),
          ),
        ],
      ),
    );
  }

  Widget _buildBrandItem(Map<String, dynamic> brand) {
    final name = brand['name'] as String? ?? 'براند';
    final logo = brand['logo'] as String?;

    return GestureDetector(
      onTap: () {
        Navigator.of(context).push(
          MaterialPageRoute(
            builder: (context) => BrandScreen(
              token: widget.token,
              userId: widget.userId,
              brandId: brand['id'] as int? ?? 0,
              brandName: name,
            ),
          ),
        );
      },
      child: Column(
        children: [
          Container(
            width: 64,
            height: 64,
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(32),
              border: Border.all(color: const Color(0xFFE2E8F0)),
              boxShadow: [
                BoxShadow(
                  color: Colors.black.withValues(alpha: 0.05),
                  blurRadius: 8,
                  offset: const Offset(0, 2),
                ),
              ],
            ),
            child: Center(
              child: logo != null && logo.isNotEmpty
                  ? Image.network(
                      logo,
                      width: 48,
                      height: 48,
                      fit: BoxFit.contain,
                      errorBuilder: (context, error, stackTrace) =>
                          const Icon(Icons.store, size: 32, color: Color(0xFF9CA3AF)),
                    )
                  : const Icon(Icons.store, size: 32, color: Color(0xFF9CA3AF)),
            ),
          ),
          const SizedBox(height: 6),
          Text(
            name,
            style: const TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.bold,
              color: Color(0xFF1E293B),
            ),
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
          ),
        ],
      ),
    );
  }

  Widget _buildProductItem(Map<String, dynamic> product) {
    final name = product['name'] as String? ?? 'منتج';
    final price = (product['price'] as num?)?.toStringAsFixed(2) ?? '0.00';
    final oldPrice = product['old_price'];
    final image = product['image'] as String?;
    final isFavorite = product['is_favorite'] as bool? ?? false;
    final packName = product['pack_name'] as String?;

    return GestureDetector(
      onTap: () {
        Navigator.of(context).push(
          MaterialPageRoute(
            builder: (context) => ProductShowScreen(
              token: widget.token,
              userId: widget.userId,
              productId: product['id'] as int? ?? 0,
            ),
          ),
        );
      },
      child: Container(
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(24),
          border: Border.all(color: const Color(0xFFE2E8F0)),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withValues(alpha: 0.05),
              blurRadius: 8,
              offset: const Offset(0, 2),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            // Image
            Stack(
              children: [
                Container(
                  height: 140,
                  width: double.infinity,
                  decoration: BoxDecoration(
                    color: const Color(0xFFF1F5F9),
                    borderRadius: const BorderRadius.only(
                      topLeft: Radius.circular(24),
                      topRight: Radius.circular(24),
                    ),
                  ),
                  child: image != null && image.isNotEmpty
                      ? ClipRRect(
                          borderRadius: const BorderRadius.only(
                            topLeft: Radius.circular(24),
                            topRight: Radius.circular(24),
                          ),
                          child: Image.network(
                            image,
                            fit: BoxFit.cover,
                            width: double.infinity,
                            errorBuilder: (context, error, stackTrace) =>
                                const Icon(Icons.broken_image,
                                    size: 50, color: Color(0xFF9CA3AF)),
                          ),
                        )
                      : Container(
                          color: const Color(0xFFF9FAFB),
                          child: const Icon(Icons.inventory,
                              size: 50, color: Color(0xFF9CA3AF)),
                        ),
                ),
                if (packName != null && packName.isNotEmpty)
                  Positioned(
                    top: 8,
                    right: 8,
                    child: Container(
                      padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 2),
                      decoration: BoxDecoration(
                        color: const Color(0xFF9879b6),
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Text(
                        packName,
                        style: const TextStyle(
                          color: Colors.white,
                          fontSize: 10,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                    ),
                  ),
                Positioned(
                  top: 8,
                  left: 8,
                  child: Container(
                    width: 28,
                    height: 28,
                    decoration: BoxDecoration(
                      color: Colors.white.withValues(alpha: 0.9),
                      borderRadius: BorderRadius.circular(14),
                    ),
                    child: IconButton(
                      icon: Icon(
                        isFavorite ? Icons.favorite : Icons.favorite_border,
                        color: isFavorite ? const Color(0xFFEF4444) : const Color(0xFF9CA3AF),
                        size: 16,
                      ),
                      onPressed: () {},
                      padding: EdgeInsets.zero,
                      constraints: const BoxConstraints(),
                    ),
                  ),
                ),
              ],
            ),
            // Details
            Padding(
              padding: const EdgeInsets.all(12),
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
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                  ),
                  if (packName != null && packName.isNotEmpty)
                    Container(
                      margin: const EdgeInsets.only(top: 2),
                      padding: const EdgeInsets.symmetric(horizontal: 6, vertical: 1),
                      decoration: BoxDecoration(
                        color: const Color(0xFFF1E6FF),
                        borderRadius: BorderRadius.circular(8),
                      ),
                      child: Text(
                        packName,
                        style: const TextStyle(
                          color: Color(0xFF9879b6),
                          fontSize: 9,
                          fontWeight: FontWeight.w600,
                        ),
                      ),
                    ),
                  const SizedBox(height: 6),
                  Row(
                    mainAxisAlignment: MainAxisAlignment.spaceBetween,
                    children: [
                      Row(
                        crossAxisAlignment: CrossAxisAlignment.baseline,
                        textBaseline: TextBaseline.alphabetic,
                        children: [
                          Text(
                            '$price ر.س',
                            style: const TextStyle(
                              fontSize: 13,
                              fontWeight: FontWeight.bold,
                              color: Color(0xFF9879b6),
                            ),
                          ),
                          if (oldPrice != null && oldPrice.toString().isNotEmpty)
                            Padding(
                              padding: const EdgeInsets.only(left: 6),
                              child: Text(
                                '$oldPrice ر.س',
                                style: const TextStyle(
                                  fontSize: 11,
                                  color: Color(0xFF9CA3AF),
                                  decoration: TextDecoration.lineThrough,
                                ),
                              ),
                            ),
                        ],
                      ),
                      Container(
                        decoration: BoxDecoration(
                          color: const Color(0xFF9879b6),
                          borderRadius: BorderRadius.circular(12),
                        ),
                        child: TextButton(
                          onPressed: () {},
                          child: const Text(
                            'إضافة',
                            style: TextStyle(
                              color: Colors.white,
                              fontSize: 11,
                              fontWeight: FontWeight.bold,
                            ),
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

  Widget _buildFavoriteScreen() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(Icons.favorite, size: 64, color: Colors.red[200]),
          const SizedBox(height: 16),
          const Text('المفضلة'),
          const Text('سيتم عرض المنتجات المفضلة هنا'),
        ],
      ),
    );
  }

  Widget _buildBannerCarousel({required List<dynamic> banners}) {
    if (banners.isEmpty) {
      return const _ShimmerWidget(height: 180);
    }
    return SizedBox(
      height: 180,
      child: PageView(
        children: banners.map((banner) {
          final imageUrl = banner['image'] as String? ?? '';
          final url = banner['url'] as String? ?? '#';
          return GestureDetector(
            onTap: () {
              // Navigate to product/page based on banner URL
            },
            child: Container(
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(20),
                boxShadow: [
                  BoxShadow(
                    color: Colors.black.withValues(alpha: 0.05),
                    blurRadius: 8,
                    offset: const Offset(0, 2),
                  ),
                ],
              ),
              child: ClipRRect(
                borderRadius: BorderRadius.circular(20),
                child: imageUrl.isNotEmpty
                    ? Image.network(
                        imageUrl,
                        fit: BoxFit.cover,
                        width: double.infinity,
                        errorBuilder: (context, error, stackTrace) =>
                            const Icon(Icons.broken_image,
                                size: 50, color: Color(0xFF9CA3AF)),
                      )
                    : Container(
                        color: const Color(0xFFF1F5FB),
                        child: const Icon(Icons.image,
                            size: 50, color: Color(0xFF9CA3AF)),
                      ),
              ),
            ),
          );
        }).toList(),
      ),
    );
  }

  Widget _buildHeader() {
    return Row(
      children: [
        // Logo
        Image.asset(
          'assets/images/logo.png',
          width: 48,
          height: 48,
          errorBuilder: (context, error, stackTrace) {
            return Container(
              width: 48,
              height: 48,
              decoration: BoxDecoration(
                color: Color(0xFF9879b6),
                borderRadius: BorderRadius.circular(24),
              ),
              child: Icon(Icons.restaurant, color: Colors.white, size: 28),
            );
          },
        ),
        const SizedBox(width: 12),
        // User info
        Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              'ماغاديت',
              style: TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.bold,
                color: Color(0xFF9879b6),
              ),
            ),
            Text(
              'User',
              style: TextStyle(
                fontSize: 14,
                color: Colors.grey[600],
              ),
            ),
          ],
        ),
        const Spacer(),
        // Search button
        GestureDetector(
          onTap: () {
            // Open search
          },
          child: Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: Color(0xFFf1e6ff).withValues(alpha: 0.5),
              borderRadius: BorderRadius.circular(50),
            ),
            child: const Icon(Icons.search, color: Color(0xFF9879b6), size: 20),
          ),
        ),
      ],
    );
  }

  Widget _buildBottomNav() {
    return Container(
      margin: const EdgeInsets.only(left: 16, right: 16, bottom: 16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(32),
        boxShadow: [
          BoxShadow(
            color: Color(0xFF9879b6).withValues(alpha: 0.15),
            blurRadius: 20,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceEvenly,
        children: [
          _BottomNavItem(
            icon: Icons.home_outlined,
            activeIcon: Icons.home,
            label: 'الرئيسية',
            isActive: _currentIndex == 0,
            onTap: () => _switchPage(0),
          ),
          _BottomNavItem(
            icon: Icons.local_offer_outlined,
            activeIcon: Icons.local_offer,
            label: 'العروض',
            isActive: _currentIndex == 1,
            onTap: () => _switchPage(1),
          ),
          _BottomNavItem(
            icon: Icons.shopping_cart_outlined,
            activeIcon: Icons.shopping_cart,
            label: 'السلة',
            isActive: _currentIndex == 2,
            onTap: () => _switchPage(2),
          ),
          _BottomNavItem(
            icon: Icons.favorite_border_outlined,
            activeIcon: Icons.favorite,
            label: 'المفضلة',
            isActive: _currentIndex == 3,
            onTap: () => _switchPage(3),
          ),
          _BottomNavItem(
            icon: Icons.menu_outlined,
            activeIcon: Icons.menu,
            label: 'المزيد',
            isActive: _currentIndex == 4,
            onTap: () => _switchPage(4),
          ),
        ],
      ),
    );
  }
}

class _BottomNavItem extends StatelessWidget {
  final IconData icon;
  final IconData activeIcon;
  final String label;
  final bool isActive;
  final VoidCallback onTap;

  const _BottomNavItem({
    required this.icon,
    required this.activeIcon,
    required this.label,
    required this.isActive,
    required this.onTap,
  });

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: AnimatedContainer(
        duration: const Duration(milliseconds: 250),
        margin: const EdgeInsets.symmetric(horizontal: 4),
        padding: const EdgeInsets.symmetric(vertical: 10, horizontal: 12),
        decoration: BoxDecoration(
          color: isActive
              ? Color(0xFFf1e6ff).withValues(alpha: 0.3)
              : Colors.transparent,
          borderRadius: BorderRadius.circular(26),
        ),
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Icon(
              isActive ? activeIcon : icon,
              color: isActive ? Color(0xFF9879b6) : Color(0xFF94A3B8),
              size: 24,
            ),
            const SizedBox(height: 2),
            Text(
              label,
              style: TextStyle(
                fontSize: 10,
                fontWeight: isActive ? FontWeight.bold : FontWeight.normal,
                color: isActive ? Color(0xFF9879b6) : Color(0xFF94A3B8),
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _ShimmerWidget extends StatelessWidget {
  final double height;

  const _ShimmerWidget({required this.height});

  @override
  Widget build(BuildContext context) {
    return Container(
      height: height,
      width: double.infinity,
      decoration: BoxDecoration(
        color: Color(0xFFF1F5F9),
        borderRadius: BorderRadius.circular(12),
      ),
    );
  }
}
