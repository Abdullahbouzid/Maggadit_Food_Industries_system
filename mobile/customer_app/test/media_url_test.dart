import 'package:flutter_test/flutter_test.dart';
import 'package:maggadit_customer/models/json_utils.dart';

void main() {
  const base = 'https://blue-beaver-923812.hostingersite.com/api';

  test('relative media paths resolve under /uploads', () {
    expect(
      mediaUrl('categories/chocolate.jpg', baseUrl: base),
      'https://blue-beaver-923812.hostingersite.com/uploads/categories/chocolate.jpg',
    );
  });

  test('paths that already start with uploads are not prefixed twice', () {
    expect(
      mediaUrl('uploads/items/milk.jpg', baseUrl: base),
      'https://blue-beaver-923812.hostingersite.com/uploads/items/milk.jpg',
    );
    expect(
      mediaUrl('/uploads/items/milk.jpg', baseUrl: base),
      'https://blue-beaver-923812.hostingersite.com/uploads/items/milk.jpg',
    );
  });

  test('absolute urls returned by the API stay untouched', () {
    expect(
      mediaUrl('https://cdn.example.com/a.jpg', baseUrl: base),
      'https://cdn.example.com/a.jpg',
    );
  });

  test('empty values resolve to null', () {
    expect(mediaUrl(null, baseUrl: base), isNull);
    expect(mediaUrl('  ', baseUrl: base), isNull);
  });
}
