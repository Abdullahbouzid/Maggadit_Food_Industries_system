import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/formatters.dart';
import '../../models/catalog.dart';
import '../../state/app_dependencies.dart';
import '../../state/auth_controller.dart';
import '../../state/cart_controller.dart';
import '../../state/favorites_controller.dart';
import '../../state/notifications_controller.dart';
import '../router.dart';
import '../theme/app_theme.dart';
import '../widgets/cards.dart';
import '../widgets/chrome.dart';
import '../widgets/kit.dart';

class HomeScreen extends StatefulWidget {
  const HomeScreen({super.key, this.onNavigate});

  /// Lets the home page switch the bottom-nav tab.
  final ValueChanged<int>? onNavigate;

  @override
  State<HomeScreen> createState() => _HomeScreenState();
}

class _HomeScreenState extends State<HomeScreen> {
  late Future<HomeFeed> _feed;
  final _pageController = PageController(viewportFraction: 0.94);
  int _bannerIndex = 0;

  @override
  void initState() {
    super.initState();
    _load();
  }

  @override
  void dispose() {
    _pageController.dispose();
    super.dispose();
  }

  void _load() {
    final deps = context.read<AppDependencies>();
    _feed = deps.catalogService.dashboard();
  }

  Future<void> _refresh() async {
    final favorites = context.read<FavoritesController>();
    final notifications = context.read<NotificationsController>();
    setState(_load);
    await _feed;
    await favorites.refreshIds();
    await notifications.checkNew();
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthController>();
    final unread = context.watch<NotificationsController>().unreadCount;
    final cartCount = context.watch<CartController>().count;

    return Scaffold(
      backgroundColor: C.bg,
      body: SafeArea(
        bottom: false,
        child: RefreshIndicator(
          color: C.primary,
          onRefresh: _refresh,
          child: ListView(
            physics: const AlwaysScrollableScrollPhysics(),
            padding: const EdgeInsets.fromLTRB(16, 10, 16, 20),
            children: [
              _Header(
                name: auth.displayName,
                organization: auth.activeOrganization?.name,
                unread: unread,
                cartCount: cartCount,
                onNotifications: () => Routes.notifications(),
                onCart: () => Routes.cart(),
                onSearch: () => Routes.products(title: 'البحث'),
                onProfile: () => Routes.profile(),
              ),
              const SizedBox(height: 14),
              FutureBuilder<HomeFeed>(
                future: _feed,
                builder: (context, snapshot) {
                  if (snapshot.connectionState == ConnectionState.waiting) {
                    return const SizedBox(height: 320, child: LoadingState());
                  }
                  if (snapshot.hasError) {
                    return SizedBox(
                      height: 300,
                      child: ErrorState(
                        message: snapshot.error.toString(),
                        onRetry: () => setState(_load),
                      ),
                    );
                  }
                  final feed = snapshot.data;
                  if (feed == null) return const SizedBox.shrink();
                  return _FeedBody(
                    feed: feed,
                    pageController: _pageController,
                    bannerIndex: _bannerIndex,
                    onBannerChanged: (i) => setState(() => _bannerIndex = i),
                    onOpenCategories: () => Routes.categories(),
                    onOpenBrands: () => Routes.brands(),
                    onOpenAllProducts: () => Routes.products(title: 'كل المنتجات'),
                    onOpenOffers: () {
                      final navigate = widget.onNavigate;
                      if (navigate != null) {
                        navigate(1);
                      } else {
                        Routes.offers();
                      }
                    },
                  );
                },
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _Header extends StatelessWidget {
  const _Header({
    required this.name,
    required this.organization,
    required this.unread,
    required this.cartCount,
    required this.onNotifications,
    required this.onCart,
    required this.onSearch,
    required this.onProfile,
  });

  final String name;
  final String? organization;
  final int unread;
  final int cartCount;
  final VoidCallback onNotifications;
  final VoidCallback onCart;
  final VoidCallback onSearch;
  final VoidCallback onProfile;

  @override
  Widget build(BuildContext context) {
    return Row(
      children: [
        GestureDetector(
          onTap: onProfile,
          child: Container(
            width: 44,
            height: 44,
            decoration: BoxDecoration(
              gradient: const LinearGradient(colors: [C.primary, C.primaryDeep]),
              borderRadius: BorderRadius.circular(15),
              boxShadow: Sh.soft,
            ),
            child: Center(
              child: Text(Fmt.initials(name),
                  style: ts(15, weight: FontWeight.w700, color: Colors.white)),
            ),
          ),
        ),
        const SizedBox(width: 10),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text('مرحباً، $name',
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: ts(13.5, weight: FontWeight.w700)),
              Text(organization ?? 'ماغديت للصناعات الغذائية',
                  maxLines: 1,
                  overflow: TextOverflow.ellipsis,
                  style: ts(9.5, color: C.muted)),
            ],
          ),
        ),
        IconBadge(icon: Icons.search_rounded, onTap: onSearch),
        const SizedBox(width: 8),
        IconBadge(icon: Icons.notifications_none_rounded, onTap: onNotifications, count: unread),
        const SizedBox(width: 8),
        IconBadge(icon: Icons.shopping_cart_outlined, onTap: onCart, count: cartCount),
      ],
    );
  }
}

class _FeedBody extends StatelessWidget {
  const _FeedBody({
    required this.feed,
    required this.pageController,
    required this.bannerIndex,
    required this.onBannerChanged,
    required this.onOpenCategories,
    required this.onOpenBrands,
    required this.onOpenAllProducts,
    required this.onOpenOffers,
  });

  final HomeFeed feed;
  final PageController pageController;
  final int bannerIndex;
  final ValueChanged<int> onBannerChanged;
  final VoidCallback onOpenCategories;
  final VoidCallback onOpenBrands;
  final VoidCallback onOpenAllProducts;
  final VoidCallback onOpenOffers;

  @override
  Widget build(BuildContext context) {
    final favorites = context.watch<FavoritesController>();
    final cart = context.watch<CartController>();

    return Column(
      crossAxisAlignment: CrossAxisAlignment.stretch,
      children: [
        if (feed.banners.isNotEmpty) ...[
          SizedBox(
            height: 152,
            child: PageView.builder(
              controller: pageController,
              onPageChanged: onBannerChanged,
              itemCount: feed.banners.length,
              itemBuilder: (context, index) {
                final banner = feed.banners[index];
                return Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 4),
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(20),
                    child: Stack(
                      fit: StackFit.expand,
                      children: [
                        RemoteImage(url: banner.image, radius: 20,
                            icon: Icons.campaign_rounded),
                        if (banner.image == null)
                          Container(
                            decoration: const BoxDecoration(
                              gradient: LinearGradient(
                                begin: Alignment.topRight,
                                end: Alignment.bottomLeft,
                                colors: [C.primaryDeep, C.primary],
                              ),
                            ),
                            padding: const EdgeInsets.all(18),
                            child: Column(
                              crossAxisAlignment: CrossAxisAlignment.start,
                              mainAxisAlignment: MainAxisAlignment.center,
                              children: [
                                Text('ماغديت للصناعات الغذائية',
                                    style: ts(15, weight: FontWeight.w700, color: Colors.white)),
                                const SizedBox(height: 6),
                                Text('منتجات طازجة تُوصَل إلى بابك',
                                    style: ts(10,
                                        color: Colors.white.withValues(alpha: 0.85))),
                              ],
                            ),
                          ),
                      ],
                    ),
                  ),
                );
              },
            ),
          ),
          const SizedBox(height: 8),
          Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              for (var i = 0; i < feed.banners.length; i++)
                AnimatedContainer(
                  duration: const Duration(milliseconds: 220),
                  margin: const EdgeInsets.symmetric(horizontal: 2),
                  width: i == bannerIndex ? 16 : 6,
                  height: 6,
                  decoration: BoxDecoration(
                    color: i == bannerIndex ? C.primary : C.border,
                    borderRadius: BorderRadius.circular(6),
                  ),
                ),
            ],
          ),
          const SizedBox(height: 16),
        ],
        SectionTitle(title: 'الفئات', actionLabel: 'عرض الكل', onAction: onOpenCategories),
        SizedBox(
          height: 92,
          child: ListView.separated(
            scrollDirection: Axis.horizontal,
            itemCount: feed.categories.length,
            separatorBuilder: (_, __) => const SizedBox(width: 8),
            itemBuilder: (context, index) {
              final category = feed.categories[index];
              return CategoryChip(
                category: category,
                onTap: () => Routes.products(title: category.name, categoryId: category.id),
              );
            },
          ),
        ),
        const SizedBox(height: 14),
        if (feed.brands.isNotEmpty) ...[
          SectionTitle(title: 'ماركات شهيرة', actionLabel: 'عرض الكل', onAction: onOpenBrands),
          SizedBox(
            height: 96,
            child: ListView.separated(
              scrollDirection: Axis.horizontal,
              itemCount: feed.brands.length,
              separatorBuilder: (_, __) => const SizedBox(width: 8),
              itemBuilder: (context, index) {
                final brand = feed.brands[index];
                return BrandTile(
                  brand: brand,
                  onTap: () => Routes.products(title: brand.name, brandId: brand.id),
                );
              },
            ),
          ),
          const SizedBox(height: 14),
        ],
        SectionTitle(title: 'أفضل المنتجات', actionLabel: 'عرض الكل', onAction: onOpenAllProducts),
        if (feed.products.isEmpty)
          const EmptyState(
            icon: Icons.inventory_2_rounded,
            title: 'لا توجد منتجات',
            message: 'سيتم عرض المنتجات بعد إضافتها من لوحة التحكم',
          )
        else
          GridView.builder(
            shrinkWrap: true,
            physics: const NeverScrollableScrollPhysics(),
            itemCount: feed.products.length > 6 ? 6 : feed.products.length,
            gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
              crossAxisCount: 2,
              mainAxisSpacing: 12,
              crossAxisSpacing: 12,
              childAspectRatio: 0.62,
            ),
            itemBuilder: (context, index) {
              final product = feed.products[index];
              return ProductCard(
                product: product,
                isFavorite: favorites.isFavorite(product.id),
                onToggleFavorite: () => favorites.toggle(product.id),
                onTap: () => Routes.product(product.id, preview: product),
                onAdd: () => _addToCart(context, product),
                quantityInCart: cart.quantityOf(
                  product.id,
                  product.defaultPack?.id,
                ),
                onIncrement: () => _addToCart(context, product),
                onDecrement: () {
                  final pack = product.defaultPack;
                  final existing = cart.items.where(
                    (e) => e.itemId == product.id && e.packId == pack?.id,
                  );
                  if (existing.isNotEmpty) cart.decrement(existing.first);
                },
              );
            },
          ),
        const SizedBox(height: 10),
        _OffersShortcut(onTap: onOpenOffers),
      ],
    );
  }

  void _addToCart(BuildContext context, Product product) {
    final pack = product.defaultPack;
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
      ..showSnackBar(SnackBar(
        content: Text('تمت إضافة ${product.name} إلى السلة',
            style: ts(11, color: Colors.white)),
        duration: const Duration(milliseconds: 1400),
      ));
  }
}

class _OffersShortcut extends StatelessWidget {
  const _OffersShortcut({required this.onTap});

  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return GestureDetector(
      onTap: onTap,
      child: Container(
        padding: const EdgeInsets.all(14),
        decoration: BoxDecoration(
          gradient: const LinearGradient(colors: [C.primaryDark, C.primary]),
          borderRadius: BorderRadius.circular(18),
          boxShadow: Sh.card,
        ),
        child: Row(
          children: [
            const Icon(Icons.local_offer_rounded, color: Colors.white, size: 22),
            const SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('عروض حصرية هذا الأسبوع',
                      style: ts(12.5, weight: FontWeight.w700, color: Colors.white)),
                  Text('خصومات على تشكيلة المنتجات الطازجة',
                      style: ts(9.5, color: Colors.white.withValues(alpha: 0.85))),
                ],
              ),
            ),
            const Icon(Icons.chevron_left_rounded, color: Colors.white, size: 20),
          ],
        ),
      ),
    );
  }
}
