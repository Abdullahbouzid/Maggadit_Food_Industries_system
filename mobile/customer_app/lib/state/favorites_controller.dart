import 'package:flutter/foundation.dart';

import '../models/catalog.dart';
import '../services/catalog_service.dart';

class FavoritesController extends ChangeNotifier {
  FavoritesController(this._service);

  final CatalogService _service;

  final Set<int> _ids = <int>{};
  List<Product> _products = const [];
  bool _loading = false;
  String? _error;

  Set<int> get ids => Set.unmodifiable(_ids);
  List<Product> get products => _products;
  bool get loading => _loading;
  String? get error => _error;
  int get count => _ids.length;

  bool isFavorite(int itemId) => _ids.contains(itemId);

  Future<void> refreshIds() async {
    try {
      final ids = await _service.favoriteIds();
      _ids
        ..clear()
        ..addAll(ids);
      notifyListeners();
    } catch (_) {
      // Silently keep previous state.
    }
  }

  Future<void> load() async {
    _loading = true;
    _error = null;
    notifyListeners();
    try {
      _products = await _service.favorites();
      _ids
        ..clear()
        ..addAll(_products.where((p) => p.isFavorite).map((p) => p.id));
      if (_ids.isEmpty) await refreshIds();
    } catch (e) {
      _error = e.toString();
    } finally {
      _loading = false;
      notifyListeners();
    }
  }

  /// Optimistic toggle; reverts when the request fails.
  Future<bool> toggle(int itemId) async {
    final wasFavorite = _ids.contains(itemId);
    if (wasFavorite) {
      _ids.remove(itemId);
    } else {
      _ids.add(itemId);
    }
    notifyListeners();

    try {
      final state = await _service.toggleFavorite(itemId);
      if (state) {
        _ids.add(itemId);
      } else {
        _ids.remove(itemId);
      }
      notifyListeners();
      return state;
    } catch (_) {
      if (wasFavorite) {
        _ids.add(itemId);
      } else {
        _ids.remove(itemId);
      }
      notifyListeners();
      return wasFavorite;
    }
  }

  void clear() {
    _ids.clear();
    _products = const [];
    notifyListeners();
  }
}
