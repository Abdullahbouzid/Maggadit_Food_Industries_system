import 'package:flutter_test/flutter_test.dart';
import 'package:maggadit_customer/core/formatters.dart';
import 'package:maggadit_customer/core/session_store.dart';
import 'package:maggadit_customer/state/cart_controller.dart';
import 'package:shared_preferences/shared_preferences.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  group('Fmt', () {
    test('formats money with latin digits and two decimals', () {
      expect(Fmt.money(1250), '1,250.00');
      expect(Fmt.price(12.5), '12.50 ر.س');
      expect(Fmt.quantity(12), '12');
      expect(Fmt.quantity(2.5), '2.5');
    });

    test('renders Arabic month names', () {
      expect(Fmt.date('2026-11-12'), '12 نوفمبر 2026');
      expect(Fmt.date('2026/11/12'), '12 نوفمبر 2026');
      expect(Fmt.dateTime('2026-11-12 09:05'), '12 نوفمبر 2026 · 09:05');
    });

    test('builds initials for avatars', () {
      expect(Fmt.initials('أحمد محمد'), 'أم');
      expect(Fmt.initials('ماغديت'), 'م');
      expect(Fmt.initials(null), '؟');
    });
  });

  group('CartController', () {
    setUp(() => SharedPreferences.setMockInitialValues({}));

    Future<CartController> buildCart() async =>
        CartController(await SessionStore.open());

    test('merges the same pack and keeps separate packs apart', () async {
      final cart = await buildCart();

      cart.add(itemId: 1, name: 'حليب', price: 10, packId: 5, packName: 'عبوة');
      cart.add(itemId: 1, name: 'حليب', price: 10, packId: 5, packName: 'عبوة');
      cart.add(
        itemId: 1,
        name: 'حليب',
        price: 100,
        packId: 6,
        packName: 'كرتون',
      );

      expect(cart.count, 2);
      expect(cart.quantityOf(1, 5), 2);
      expect(cart.quantityOf(1, 6), 1);
      expect(cart.subtotal, 120);
    });

    test('decrementing to zero removes the line', () async {
      final cart = await buildCart();
      cart.add(itemId: 3, name: 'جبنة', price: 25, quantity: 1);

      cart.decrement(cart.items.single);

      expect(cart.isEmpty, isTrue);
      expect(cart.subtotal, 0);
    });

    test('persists and restores the cart across sessions', () async {
      final cart = await buildCart();
      cart.add(
        itemId: 9,
        name: 'زبدة',
        price: 30,
        packId: 2,
        packName: 'عبوة',
        quantity: 3,
      );

      final restored = await buildCart();
      expect(restored.count, 1);
      final line = restored.items.single;
      expect(line.itemId, 9);
      expect(line.packId, 2);
      expect(line.quantity, 3);
      expect(line.lineTotal, 90);
    });

    test(
      'builds the checkout payload expected by POST /customer/checkout',
      () async {
        final cart = await buildCart();
        cart.add(
          itemId: 4,
          name: 'عصير',
          price: 6.5,
          packId: 8,
          packName: 'كرتون',
          quantity: 2,
        );

        final payload = cart.toCheckoutItems().single;
        expect(payload['id'], 4);
        expect(payload['quantity'], 2);
        expect(payload['pack_id'], 8);
        expect(payload['pack_name'], 'كرتون');
      },
    );
  });
}
