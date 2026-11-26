import 'dart:convert';

import 'package:flutter/foundation.dart';

import '../core/session_store.dart';

class CartItem {
  CartItem({
    required this.itemId,
    required this.packId,
    required this.name,
    required this.packName,
    required this.price,
    required this.quantity,
    this.image,
    this.uomName,
  });

  factory CartItem.fromJson(Map<String, dynamic> json) => CartItem(
        itemId: (json['item_id'] as num).toInt(),
        packId: (json['pack_id'] as num?)?.toInt(),
        name: json['name'] as String,
        packName: (json['pack_name'] ?? '') as String,
        price: (json['price'] as num).toDouble(),
        quantity: (json['quantity'] as num).toDouble(),
        image: json['image'] as String?,
        uomName: json['uom_name'] as String?,
      );

  final int itemId;
  final int? packId;
  final String name;
  final String packName;
  final double price;
  final double quantity;
  final String? image;
  final String? uomName;

  String get key => '$itemId-${packId ?? 0}';

  double get lineTotal => price * quantity;

  CartItem copyWith({double? quantity}) => CartItem(
        itemId: itemId,
        packId: packId,
        name: name,
        packName: packName,
        price: price,
        quantity: quantity ?? this.quantity,
        image: image,
        uomName: uomName,
      );

  Map<String, dynamic> toJson() => {
        'item_id': itemId,
        'pack_id': packId,
        'name': name,
        'pack_name': packName,
        'price': price,
        'quantity': quantity,
        'image': image,
        'uom_name': uomName,
      };

  /// Shape expected by `POST /api/customer/checkout`.
  Map<String, dynamic> toCheckoutJson() => {
        'id': itemId,
        'quantity': quantity,
        'pack_id': packId,
        'pack_name': packName.isEmpty ? null : packName,
        'uom_name': uomName,
        'name': name,
      };
}

class CartController extends ChangeNotifier {
  CartController(this._store) {
    _restore();
  }

  final SessionStore _store;
  final List<CartItem> _items = [];

  List<CartItem> get items => List.unmodifiable(_items);
  int get count => _items.length;
  bool get isEmpty => _items.isEmpty;

  double get subtotal =>
      _items.fold(0, (sum, item) => sum + item.lineTotal);

  /// The backend applies taxes/discounts; this is a display estimate only.
  double get estimatedTotal => subtotal;

  double quantityOf(int itemId, int? packId) {
    for (final item in _items) {
      if (item.itemId == itemId && item.packId == packId) return item.quantity;
    }
    return 0;
  }

  void add({
    required int itemId,
    required String name,
    required double price,
    int? packId,
    String packName = '',
    String? image,
    String? uomName,
    double quantity = 1,
  }) {
    final key = '$itemId-${packId ?? 0}';
    final index = _items.indexWhere((item) => item.key == key);
    if (index >= 0) {
      final current = _items[index];
      _items[index] = current.copyWith(quantity: current.quantity + quantity);
    } else {
      _items.add(CartItem(
        itemId: itemId,
        packId: packId,
        name: name,
        packName: packName,
        price: price,
        quantity: quantity,
        image: image,
        uomName: uomName,
      ));
    }
    _persist();
    notifyListeners();
  }

  void setQuantity(CartItem item, double quantity) {
    final index = _items.indexWhere((e) => e.key == item.key);
    if (index < 0) return;
    if (quantity <= 0) {
      _items.removeAt(index);
    } else {
      _items[index] = _items[index].copyWith(quantity: quantity);
    }
    _persist();
    notifyListeners();
  }

  void increment(CartItem item) => setQuantity(item, item.quantity + 1);

  void decrement(CartItem item) => setQuantity(item, item.quantity - 1);

  void remove(CartItem item) {
    _items.removeWhere((e) => e.key == item.key);
    _persist();
    notifyListeners();
  }

  void clear() {
    _items.clear();
    _persist();
    notifyListeners();
  }

  List<Map<String, dynamic>> toCheckoutItems() =>
      _items.map((e) => e.toCheckoutJson()).toList();

  void _persist() {
    _store.saveCart(jsonEncode(_items.map((e) => e.toJson()).toList()));
  }

  void _restore() {
    final raw = _store.cart;
    if (raw == null || raw.isEmpty) return;
    try {
      final decoded = jsonDecode(raw);
      if (decoded is! List) return;
      for (final row in decoded.whereType<Map>()) {
        _items.add(CartItem.fromJson(Map<String, dynamic>.from(row)));
      }
    } catch (_) {
      // Corrupted cache: start with an empty cart.
    }
  }
}
