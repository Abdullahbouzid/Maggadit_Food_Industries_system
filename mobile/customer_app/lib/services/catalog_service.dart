import '../core/api_client.dart';
import '../models/account.dart';
import '../models/catalog.dart';
import '../models/json_utils.dart';

/// Catalog + favorites + static content endpoints (routes/api.php).
class CatalogService {
  CatalogService(this._api);

  static const _prefix = '/customer';
  final ApiClient _api;

  String? get _baseUrl => _api.baseUrl;

  Future<HomeFeed> dashboard() async {
    final json = await _api.get('$_prefix/dashboard');
    return HomeFeed.fromJson(json, baseUrl: _baseUrl);
  }

  Future<List<AppCategory>> categories() async {
    final json = await _api.get('$_prefix/categories');
    return asMapList(json['categories'])
        .map((e) => AppCategory.fromJson(e, baseUrl: _baseUrl))
        .toList();
  }

  Future<List<AppBrand>> brands() async {
    final json = await _api.get('$_prefix/brands');
    return asMapList(json['brands'])
        .map((e) => AppBrand.fromJson(e, baseUrl: _baseUrl))
        .toList();
  }

  Future<Paged<Product>> products({
    String? search,
    int? categoryId,
    int? brandId,
    int page = 1,
  }) async {
    final json = await _api.get(
      '$_prefix/products',
      query: {
        'search': search,
        'category_id': categoryId,
        'brand_id': brandId,
        'page': page,
      },
    );
    final paginator = asMap(json['products']);
    final rows = asMapList(paginator['data'])
        .map((e) => Product.fromJson(e, baseUrl: _baseUrl))
        .toList();
    return Paged(
      items: rows,
      currentPage: asInt(paginator['current_page'], page),
      lastPage: asInt(paginator['last_page'], page),
      total: asInt(paginator['total'], rows.length),
    );
  }

  Future<Product> product(int id) async {
    final json = await _api.get('$_prefix/products/$id');
    return Product.fromJson(asMap(json['product']), baseUrl: _baseUrl);
  }

  /// Products of a category (the API returns them inside the category payload).
  Future<List<Product>> categoryProducts(int id) async {
    final json = await _api.get('$_prefix/categories/$id');
    return asMapList(json['items'])
        .map((e) => Product.fromJson(e, baseUrl: _baseUrl))
        .toList();
  }

  Future<List<Product>> brandProducts(int id) async {
    final json = await _api.get('$_prefix/brands/$id');
    return asMapList(json['items'])
        .map((e) => Product.fromJson(e, baseUrl: _baseUrl))
        .toList();
  }

  Future<List<Offer>> offers() async {
    final json = await _api.get('$_prefix/offers');
    return asMapList(json['offers'])
        .map((e) => Offer.fromJson(e, baseUrl: _baseUrl))
        .toList();
  }

  Future<List<Product>> favorites() async {
    final json = await _api.get('$_prefix/favorites');
    return asMapList(json['data'])
        .map((e) => Product.fromJson(e, baseUrl: _baseUrl))
        .toList();
  }

  Future<Set<int>> favoriteIds() async {
    final json = await _api.get('$_prefix/favorites/ids');
    final ids = json['ids'];
    if (ids is! List) return <int>{};
    return ids.map(asInt).toSet();
  }

  /// Returns the new favorite state.
  Future<bool> toggleFavorite(int itemId) async {
    final json = await _api.post('$_prefix/favorites/toggle', body: {'item_id': itemId});
    return asBool(json['is_favorite']);
  }

  Future<List<FaqItem>> faq() async {
    final json = await _api.get('$_prefix/faq');
    final raw = json['faq'];
    if (raw is Map) {
      // {question: answer}
      return raw.entries
          .map((e) => FaqItem(question: e.key.toString(), answer: e.value.toString()))
          .toList();
    }
    return asMapList(raw).map(FaqItem.fromJson).toList();
  }

  Future<String> privacyPolicy() async {
    final json = await _api.get('$_prefix/privacy-policy');
    return asString(json['content'] ?? json['message']);
  }

  Future<AboutContent> aboutUs() async {
    final json = await _api.get('$_prefix/about-us');
    return AboutContent.fromJson(json, baseUrl: _baseUrl);
  }

  Future<ContactInfo> contactInfo() async {
    final json = await _api.get('$_prefix/contact-info');
    return ContactInfo.fromJson(json);
  }

  Future<void> contactUs({
    required String name,
    required String email,
    required String phone,
    required String subject,
    required String message,
  }) async {
    await _api.post('$_prefix/contact-us', body: {
      'name': name,
      'email': email,
      'phone': phone,
      'subject': subject,
      'message': message,
    });
  }

  Future<List<Branch>> branches() async {
    final json = await _api.get('$_prefix/branches');
    return asMapList(json['branches']).map(Branch.fromJson).toList();
  }
}
