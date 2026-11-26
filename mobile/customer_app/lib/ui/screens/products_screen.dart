import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/api_client.dart';
import '../../models/catalog.dart';
import '../../state/app_dependencies.dart';
import '../../state/cart_controller.dart';
import '../../state/favorites_controller.dart';
import '../router.dart';
import '../theme/app_theme.dart';
import '../widgets/cards.dart';
import '../widgets/chrome.dart';
import '../widgets/kit.dart';
import '../../l10n/l10n.dart';

/// Product listing with search + infinite scroll.
/// Re-used for category / brand filtered lists.
class ProductsScreen extends StatefulWidget {
  const ProductsScreen({
    super.key,
    this.title,
    this.initialSearch,
    this.categoryId,
    this.brandId,
  });

  final String? title;
  final String? initialSearch;
  final int? categoryId;
  final int? brandId;

  @override
  State<ProductsScreen> createState() => _ProductsScreenState();
}

class _ProductsScreenState extends State<ProductsScreen> {
  late final TextEditingController _searchController = TextEditingController(
    text: widget.initialSearch ?? '',
  );
  final _scrollController = ScrollController();

  final List<Product> _products = [];
  int _page = 1;
  int _lastPage = 1;
  bool _loading = true;
  bool _loadingMore = false;
  String? _error;

  @override
  void initState() {
    super.initState();
    _scrollController.addListener(_onScroll);
    _load(page: 1);
  }

  @override
  void dispose() {
    _scrollController.dispose();
    _searchController.dispose();
    super.dispose();
  }

  void _onScroll() {
    if (_scrollController.position.pixels >
        _scrollController.position.maxScrollExtent - 320) {
      if (!_loadingMore && _page < _lastPage) _load(page: _page + 1);
    }
  }

  Future<void> _load({required int page, String? search}) async {
    setState(() {
      if (page == 1) {
        _loading = true;
        _error = null;
      } else {
        _loadingMore = true;
      }
    });

    try {
      final service = context.read<AppDependencies>().catalogService;
      final result = await service.products(
        page: page,
        search: (search ?? _searchController.text).trim().isEmpty
            ? null
            : (search ?? _searchController.text).trim(),
        categoryId: widget.categoryId,
        brandId: widget.brandId,
      );
      if (!mounted) return;
      setState(() {
        if (page == 1) _products.clear();
        _products.addAll(result.items);
        _page = result.currentPage;
        _lastPage = result.lastPage;
      });
    } on ApiException catch (e) {
      if (!mounted) return;
      setState(() => _error = e.message);
    } catch (e) {
      if (!mounted) return;
      setState(() => _error = e.toString());
    } finally {
      if (mounted) {
        setState(() {
          _loading = false;
          _loadingMore = false;
        });
      }
    }
  }

  @override
  Widget build(BuildContext context) {
    final favorites = context.watch<FavoritesController>();
    final cart = context.watch<CartController>();

    return ScreenScaffold(
      title: widget.title ?? tr(context, 'المنتجات'),
      subtitle: tArgs(context, '{0} منتج', [_products.length]),
      child: Column(
        children: [
          SearchField(
            controller: _searchController,
            onSubmitted: (value) => _load(page: 1, search: value),
            onChanged: (_) => setState(() {}),
            onClear: () => _load(page: 1, search: ''),
          ),
          const SizedBox(height: 12),
          Expanded(
            child: _loading
                ? LoadingState(label: tr(context, 'جاري تحميل المنتجات'))
                : _error != null
                ? ErrorState(message: _error!, onRetry: () => _load(page: 1))
                : _products.isEmpty
                ? EmptyState(
                    icon: Icons.search_off_rounded,
                    title: tr(context, 'لا توجد نتائج'),
                    message: tr(context, 'جرّب كلمة بحث أخرى أو تصفّح الفئات'),
                    actionLabel: tr(context, 'إعادة التحميل'),
                    onAction: () => _load(page: 1),
                  )
                : GridView.builder(
                    controller: _scrollController,
                    padding: const EdgeInsets.only(bottom: 20),
                    itemCount: _products.length + (_loadingMore ? 1 : 0),
                    gridDelegate:
                        const SliverGridDelegateWithFixedCrossAxisCount(
                          crossAxisCount: 2,
                          mainAxisSpacing: 12,
                          crossAxisSpacing: 12,
                          childAspectRatio: 0.62,
                        ),
                    itemBuilder: (context, index) {
                      if (index >= _products.length) {
                        return const Center(child: LoadingState());
                      }
                      final product = _products[index];
                      final pack = product.defaultPack;
                      return ProductCard(
                        product: product,
                        isFavorite: favorites.isFavorite(product.id),
                        onToggleFavorite: () => favorites.toggle(product.id),
                        onTap: () =>
                            Routes.product(product.id, preview: product),
                        onAdd: () => _add(context, product),
                        quantityInCart: cart.quantityOf(product.id, pack?.id),
                        onIncrement: () => _add(context, product),
                        onDecrement: () {
                          final existing = cart.items.where(
                            (e) =>
                                e.itemId == product.id && e.packId == pack?.id,
                          );
                          if (existing.isNotEmpty)
                            cart.decrement(existing.first);
                        },
                      );
                    },
                  ),
          ),
        ],
      ),
    );
  }

  void _add(BuildContext context, Product product) {
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
  }
}
