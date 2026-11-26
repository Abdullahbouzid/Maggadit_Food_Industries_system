import '../l10n/l10n.dart';
import 'json_utils.dart';

class AppCategory {
  AppCategory({required this.id, required this.name, this.image});

  /// `/api/customer/categories` returns the raw model: name_ar/name_en.
  factory AppCategory.fromJson(Map<String, dynamic> json, {String? baseUrl}) {
    return AppCategory(
      id: asInt(json['id']),
      name: asString(json['name_ar']).isNotEmpty
          ? asString(json['name_ar'])
          : asString(json['name'] ?? json['name_en']),
      image: mediaUrl(
        asString(json['image_url'] ?? json['image']).isEmpty
            ? null
            : asString(json['image_url'] ?? json['image']),
        baseUrl: baseUrl,
      ),
    );
  }

  final int id;
  final String name;
  final String? image;
}

class AppBrand {
  AppBrand({required this.id, required this.name, this.logo});

  factory AppBrand.fromJson(Map<String, dynamic> json, {String? baseUrl}) {
    return AppBrand(
      id: asInt(json['id']),
      name: asString(json['name_ar']).isNotEmpty
          ? asString(json['name_ar'])
          : asString(json['name'] ?? json['name_en']),
      logo: mediaUrl(
        asString(json['logo_url'] ?? json['logo']).isEmpty
            ? null
            : asString(json['logo_url'] ?? json['logo']),
        baseUrl: baseUrl,
      ),
    );
  }

  final int id;
  final String name;
  final String? logo;
}

class ProductPack {
  ProductPack({
    required this.id,
    required this.name,
    required this.price,
    required this.quantity,
    required this.isDefault,
    this.uomName = '',
  });

  factory ProductPack.fromJson(Map<String, dynamic> json) {
    return ProductPack(
      id: asInt(json['id']),
      name: asString(json['name']).isNotEmpty
          ? asString(json['name'])
          : asString(json['display_name'], L10n.tr('عبوة')),
      price: asDouble(json['price']),
      quantity: asDouble(json['quantity'], 1),
      isDefault: asBool(json['is_default']),
      uomName: asString(json['uom_name']),
    );
  }

  final int id;
  final String name;
  final double price;
  final double quantity;
  final bool isDefault;
  final String uomName;
}

class Product {
  Product({
    required this.id,
    required this.name,
    required this.price,
    this.image,
    this.oldPrice,
    this.rate = 0,
    this.isFavorite = false,
    this.packs = const [],
    this.description = '',
    this.images = const [],
    this.categoryName,
    this.brandName,
  });

  factory Product.fromJson(Map<String, dynamic> json, {String? baseUrl}) {
    final packs = asMapList(json['packs']).map(ProductPack.fromJson).toList();
    final gallery = (json['images'] is List ? json['images'] as List : const [])
        .map((e) => mediaUrl(asString(e), baseUrl: baseUrl))
        .whereType<String>()
        .toList();
    final mainImage = mediaUrl(
      asString(json['image']).isEmpty ? null : asString(json['image']),
      baseUrl: baseUrl,
    );

    return Product(
      id: asInt(json['id']),
      name: asString(json['name']).isNotEmpty
          ? asString(json['name'])
          : asString(json['name_ar'] ?? json['name_en']),
      price: asDouble(json['price'], packs.isEmpty ? 0 : packs.first.price),
      image: mainImage ?? (gallery.isNotEmpty ? gallery.first : null),
      oldPrice: json['old_price'] == null ? null : asDouble(json['old_price']),
      rate: asDouble(json['rate']),
      isFavorite: asBool(json['is_favorite']),
      packs: packs,
      description: asString(json['description']),
      images: gallery,
      categoryName:
          asString(
            json['category'] is Map
                ? asMap(json['category'])['name']
                : json['category'],
          ).isEmpty
          ? null
          : asString(
              json['category'] is Map
                  ? asMap(json['category'])['name']
                  : json['category'],
            ),
      brandName:
          asString(
            json['brand'] is Map ? asMap(json['brand'])['name'] : json['brand'],
          ).isEmpty
          ? null
          : asString(
              json['brand'] is Map
                  ? asMap(json['brand'])['name']
                  : json['brand'],
            ),
    );
  }

  final int id;
  final String name;
  final double price;
  final String? image;
  final double? oldPrice;
  final double rate;
  final bool isFavorite;
  final List<ProductPack> packs;
  final String description;
  final List<String> images;
  final String? categoryName;
  final String? brandName;

  ProductPack? get defaultPack =>
      packs.where((p) => p.isDefault).firstOrNull ?? packs.firstOrNull;

  Product copyWith({bool? isFavorite}) => Product(
    id: id,
    name: name,
    price: price,
    image: image,
    oldPrice: oldPrice,
    rate: rate,
    isFavorite: isFavorite ?? this.isFavorite,
    packs: packs,
    description: description,
    images: images,
    categoryName: categoryName,
    brandName: brandName,
  );
}

extension _FirstOrNull<T> on Iterable<T> {
  T? get firstOrNull => isEmpty ? null : first;
}

class Offer {
  Offer({
    required this.id,
    required this.title,
    required this.description,
    required this.itemId,
    required this.itemPackId,
    required this.productName,
    required this.oldPrice,
    required this.newPrice,
    required this.discountPercentage,
    this.image,
    this.endDate,
  });

  factory Offer.fromJson(Map<String, dynamic> json, {String? baseUrl}) {
    return Offer(
      id: asInt(json['id']),
      title: asString(json['title']),
      description: asString(json['description']),
      itemId: asInt(json['item_id']),
      itemPackId: json['item_pack_id'] == null
          ? null
          : asInt(json['item_pack_id']),
      productName: asString(json['name']),
      oldPrice: asDouble(json['old_price']),
      newPrice: asDouble(json['new_price']),
      discountPercentage: asDouble(json['discount_percentage']),
      image: mediaUrl(
        asString(json['image']).isEmpty ? null : asString(json['image']),
        baseUrl: baseUrl,
      ),
      endDate: asString(json['end_date']).isEmpty
          ? null
          : asString(json['end_date']),
    );
  }

  final int id;
  final String title;
  final String description;
  final int itemId;
  final int? itemPackId;
  final String productName;
  final double oldPrice;
  final double newPrice;
  final double discountPercentage;
  final String? image;
  final String? endDate;
}

class HomeBanner {
  HomeBanner({required this.id, this.image, this.url});

  factory HomeBanner.fromJson(Map<String, dynamic> json, {String? baseUrl}) {
    return HomeBanner(
      id: asInt(json['id']),
      image: mediaUrl(
        asString(json['image']).isEmpty ? null : asString(json['image']),
        baseUrl: baseUrl,
      ),
      url: asString(json['url']).isEmpty ? null : asString(json['url']),
    );
  }

  final int id;
  final String? image;
  final String? url;
}

/// `/api/customer/dashboard`
class HomeFeed {
  HomeFeed({
    required this.banners,
    required this.categories,
    required this.brands,
    required this.products,
    this.totalOrders = 0,
    this.totalFavorites = 0,
  });

  factory HomeFeed.fromJson(Map<String, dynamic> json, {String? baseUrl}) {
    final dashboard = asMap(json['dashboard']);
    return HomeFeed(
      banners: asMapList(
        json['banners'],
      ).map((e) => HomeBanner.fromJson(e, baseUrl: baseUrl)).toList(),
      categories: asMapList(
        json['categories'],
      ).map((e) => AppCategory.fromJson(e, baseUrl: baseUrl)).toList(),
      brands: asMapList(
        json['brands'],
      ).map((e) => AppBrand.fromJson(e, baseUrl: baseUrl)).toList(),
      products: asMapList(
        json['products'],
      ).map((e) => Product.fromJson(e, baseUrl: baseUrl)).toList(),
      totalOrders: asInt(dashboard['total_orders']),
      totalFavorites: asInt(dashboard['total_favorites']),
    );
  }

  final List<HomeBanner> banners;
  final List<AppCategory> categories;
  final List<AppBrand> brands;
  final List<Product> products;
  final int totalOrders;
  final int totalFavorites;
}

/// Laravel paginator wrapper for `/products`.
class Paged<T> {
  Paged({
    required this.items,
    required this.currentPage,
    required this.lastPage,
    required this.total,
  });

  final List<T> items;
  final int currentPage;
  final int lastPage;
  final int total;

  bool get hasMore => currentPage < lastPage;
}
