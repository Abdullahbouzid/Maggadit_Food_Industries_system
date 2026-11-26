import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../models/catalog.dart';
import '../../state/app_dependencies.dart';
import '../router.dart';
import '../theme/app_theme.dart';
import '../widgets/chrome.dart';
import '../widgets/kit.dart';

enum TaxonomyKind { categories, brands }

/// Categories / brands browser.
class TaxonomyScreen extends StatefulWidget {
  const TaxonomyScreen({super.key, required this.kind});

  final TaxonomyKind kind;

  @override
  State<TaxonomyScreen> createState() => _TaxonomyScreenState();
}

class _TaxonomyScreenState extends State<TaxonomyScreen> {
  late Future<List<Object>> _future;

  @override
  void initState() {
    super.initState();
    _load();
  }

  void _load() {
    final service = context.read<AppDependencies>().catalogService;
    _future = widget.kind == TaxonomyKind.categories
        ? service.categories().then((value) => value.cast<Object>())
        : service.brands().then((value) => value.cast<Object>());
  }

  @override
  Widget build(BuildContext context) {
    final isCategories = widget.kind == TaxonomyKind.categories;
    return ScreenScaffold(
      title: isCategories ? 'الفئات' : 'الماركات',
      subtitle: isCategories ? 'تصفّح المنتجات حسب الفئة' : 'تصفّح المنتجات حسب الماركة',
      onRefresh: () async => setState(_load),
      child: FutureView<List<Object>>(
        future: _future,
        onRetry: () => setState(_load),
        emptyCheck: (data) => data.isEmpty,
        emptyTitle: isCategories ? 'لا توجد فئات' : 'لا توجد ماركات',
        builder: (context, data) => ListView.separated(
          itemCount: data.length,
          separatorBuilder: (_, __) => const SizedBox(height: 10),
          itemBuilder: (context, index) {
            if (isCategories) {
              final category = data[index] as AppCategory;
              return _TaxonomyTile(
                title: category.name,
                image: category.image,
                icon: Icons.category_rounded,
                onTap: () => Routes.products(title: category.name, categoryId: category.id),
              );
            }
            final brand = data[index] as AppBrand;
            return _TaxonomyTile(
              title: brand.name,
              image: brand.logo,
              icon: Icons.storefront_rounded,
              onTap: () => Routes.products(title: brand.name, brandId: brand.id),
            );
          },
        ),
      ),
    );
  }
}

class _TaxonomyTile extends StatelessWidget {
  const _TaxonomyTile({
    required this.title,
    required this.icon,
    required this.onTap,
    this.image,
  });

  final String title;
  final String? image;
  final IconData icon;
  final VoidCallback onTap;

  @override
  Widget build(BuildContext context) {
    return NovaCard(
      onTap: onTap,
      padding: const EdgeInsets.all(10),
      child: Row(
        children: [
          RemoteImage(url: image, width: 52, height: 52, radius: 14, icon: icon),
          const SizedBox(width: 12),
          Expanded(
            child: Text(title,
                maxLines: 1, overflow: TextOverflow.ellipsis,
                style: ts(13, weight: FontWeight.w600)),
          ),
          const Icon(Icons.chevron_left_rounded, size: 20, color: C.muted),
        ],
      ),
    );
  }
}
