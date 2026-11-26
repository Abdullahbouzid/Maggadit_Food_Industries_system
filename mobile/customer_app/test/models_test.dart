import 'package:flutter_test/flutter_test.dart';
import 'package:maggadit_customer/models/account.dart';
import 'package:maggadit_customer/models/catalog.dart';
import 'package:maggadit_customer/models/orders.dart';

/// Payloads below are copied verbatim from the Laravel controllers in
/// `app/Http/Controllers/Api/CustomerApiController.php`.
void main() {
  group('productsIndex payload', () {
    final json = {
      'success': true,
      'products': {
        'current_page': 1,
        'last_page': 3,
        'total': 41,
        'data': [
          {
            'id': 12,
            'name': 'حليب ماغديت كامل الدسم',
            'image': 'uploads/items/milk.jpg',
            'price': 12.5,
            'old_price': null,
            'rate': 0,
            'is_favorite': false,
            'packs': [
              {
                'id': 30,
                'name': 'كرتون 12 عبوة',
                'display_name': 'كرتون 12 عبوة',
                'pack_type': 'كرتون',
                'quantity': 12.0,
                'uom_name': '1 لتر',
                'uom_symbol': 'L',
                'price': 12.5,
                'is_default': true,
                'barcode': '123456',
              },
            ],
          },
        ],
      },
    };

    test('parses paginator, pack and absolute image url', () {
      final paginator = json['products']! as Map;
      final rows = (paginator['data']! as List)
          .map((e) => Product.fromJson(Map<String, dynamic>.from(e as Map),
              baseUrl: 'https://maggadit.ly/api'))
          .toList();

      final product = rows.single;
      expect(product.id, 12);
      expect(product.name, 'حليب ماغديت كامل الدسم');
      expect(product.price, 12.5);
      expect(product.image, 'https://maggadit.ly/uploads/items/milk.jpg');
      expect(product.packs.single.id, 30);
      expect(product.defaultPack!.price, 12.5);
      expect(paginator['last_page'], 3);
    });
  });

  group('productsShow payload', () {
    test('falls back to the first pack price when the item has none', () {
      final product = Product.fromJson({
        'id': 7,
        'name': 'عصير برتقال',
        'description': 'طبيعي 100%',
        'images': ['https://cdn.maggadit.ly/a.png', 'https://cdn.maggadit.ly/b.png'],
        'packs': [
          {'id': 1, 'name': 'عبوة', 'price': 4.75, 'quantity': 1, 'is_default': false},
        ],
        'category': 'العصائر',
        'brand': 'ماغديت',
      });

      expect(product.price, 4.75);
      expect(product.images.length, 2);
      expect(product.categoryName, 'العصائر');
      expect(product.brandName, 'ماغديت');
    });
  });

  group('financialStatement payload', () {
    final json = {
      'success': true,
      'statement': [
        {
          'id': 5,
          'date': '2026-11-02',
          'type': 'purchase',
          'label': 'دفع فاتورة',
          'amount': 250.0,
          'direction': 'debit',
          'signed': -250.0,
          'balance_after': 750.0,
          'method': 'نقداً',
          'reference': 'INV-9001',
          'notes': null,
          'invoice_id': 33,
          'invoice_number': 'INV-9001',
        },
        {
          'id': 6,
          'date': '2026-11-05',
          'type': 'deposit',
          'label': 'إيداع',
          'amount': 100.0,
          'direction': 'credit',
          'signed': 100.0,
          'balance_after': 850.0,
          'method': 'تحويل بنكي',
          'reference': '#6',
          'notes': 'دفعة مقدمة',
          'invoice_id': null,
          'invoice_number': null,
        },
      ],
      'totals': {
        'deposits': 100.0,
        'debits': 250.0,
        'balance': 850.0,
        'invoiced': 1200.0,
        'paid': 950.0,
        'outstanding': 250.0,
      },
    };

    test('reads totals map and signed transactions', () {
      final statement = WalletStatement.fromJson(json);

      expect(statement.balance, 850.0);
      expect(statement.deposits, 100.0);
      expect(statement.debits, 250.0);
      expect(statement.outstanding, 250.0);
      expect(statement.transactions.length, 2);
      expect(statement.transactions.first.isDeposit, isFalse);
      expect(statement.transactions.first.amount, 250.0);
      expect(statement.transactions.last.isDeposit, isTrue);
      expect(statement.transactions.last.notes, 'دفعة مقدمة');
    });
  });

  group('orderHistory payload', () {
    test('unwraps data.rows and parses formatted totals', () {
      final history = OrderHistory.fromJson({
        'success': true,
        'data': {
          'stats': {'all': 4, 'pending': 1, 'delivered': 3},
          'rows': [
            {
              'id': 88,
              'number': 'ORD-2026-88',
              'date': '2026/11/12',
              'items_count': 3,
              'status_text': 'قيد المراجعة',
              'status_class': 'text-amber-700 bg-amber-50',
              'progress': 30,
              'total': '1,250.00',
              'can_respond': false,
              'has_invoice': true,
              'quotation_number': 'QT-14',
              'branch_name': 'فرع طرابلس',
            },
          ],
        },
      });

      final row = history.rows.single;
      expect(history.stats['pending'], 1);
      expect(row.number, 'ORD-2026-88');
      expect(row.total, 1250.0);
      expect(row.branchName, 'فرع طرابلس');
      expect(row.hasInvoice, isTrue);
      expect(row.itemsCount, 3);
    });
  });

  group('orderDetails payload', () {
    final json = {
      'success': true,
      'order': {
        'id': 55,
        'order_number': 'ORD-55',
        'date': '2026-11-10',
        'total': 480.5,
        'status': 'quotation_sent',
        'status_text': 'تم إرسال عرض السعر',
        'status_class': 'text-amber-700 bg-amber-50',
        'progress': 40,
        'items_count': 2,
        'has_quotation': true,
        'has_invoice': false,
        'invoice_number': null,
        'quotation_number': 'QT-9',
        'can_respond': true,
        'delivery_address': 'طرابلس - شارع الجمهورية',
        'delivery_phone': '0912345678',
        'customer_name': 'أحمد',
        'branch_name': 'فرع طرابلس',
        'items': [
          {
            'name': 'جبنة بيضاء',
            'code': 'CH-01',
            'pack': 'عبوة 500غ',
            'quantity': 4,
            'unit_price': 12.5,
            'line_total': 50,
            'warehouse': 'المخزن الرئيسي',
          },
          {
            'name': 'حليب',
            'quantity': 2,
            'unit_price': 10,
          },
        ],
        'quotation': {
          'id': 9,
          'quotation_number': 'QT-9',
          'status': 'sent',
          'customer_response': 'pending',
          'total': 480.5,
          'items': [
            {
              'name': 'جبنة بيضاء',
              'quantity': 4,
              'unit_price': 12.5,
              'line_total': 50,
            },
          ],
        },
        'invoice': null,
      },
      'steps': [
        {'key': 'submitted', 'label': 'تم إرسال الطلب', 'icon': 'fa-send', 'color': 'purple', 'state': 'done'},
        {'key': 'review', 'label': 'قيد المراجعة', 'icon': 'fa-magnifying-glass', 'color': 'blue', 'state': 'done'},
        {'key': 'quotation', 'label': 'عرض السعر', 'icon': 'fa-file-invoice-dollar', 'color': 'indigo', 'state': 'current'},
        {'key': 'approval', 'label': 'موافقة العميل', 'icon': 'fa-user-check', 'color': 'emerald', 'state': 'pending'},
      ],
      'timeline': [
        {'to': 'طلب جديد', 'by': 'النظام', 'date': '2026-11-10'},
        {'to': 'عرض السعر', 'by': 'الإدارة', 'date': '2026-11-10'},
      ],
    };

    test('reads the ordered payload including steps at root level', () {
      final order = OrderDetail.fromJson(json);

      expect(order.number, 'ORD-55');
      expect(order.canRespond, isTrue);
      expect(order.deliveryAddress, 'طرابلس - شارع الجمهورية');
      expect(order.steps.length, 4);
      expect(order.steps[2].reached, isTrue);
      expect(order.steps[3].reached, isFalse);
      expect(order.timeline.length, 2);
      expect(order.quotation!.number, 'QT-9');
      expect(order.items.first.lineTotal, 50);
      // line_total missing in payload -> computed from quantity * unit_price
      expect(order.items.last.lineTotal, 20);
      expect(order.items.last.pack, isNull);
    });
  });

  group('returns payload', () {
    test('reads the nested invoice and item shapes', () {
      final request = CustomerReturn.fromJson({
        'id': 3,
        'return_number': 'RET-3',
        'status': 'pending',
        'type': 'مرتجع كامل',
        'reason': 'منتج تالف',
        'total_refund': 75.0,
        'created_at': '2026-11-08',
        'branch_name': 'فرع بنغازي',
        'invoice': {'id': 21, 'invoice_number': 'INV-21'},
        'items': [
          {
            'id': 1,
            'item': {'name_ar': 'زبدة', 'name_en': 'Butter'},
            'quantity_returned': 3,
            'pack': {'display_name': 'عبوة 250غ'},
          },
        ],
      });

      expect(request.number, 'RET-3');
      expect(request.invoiceNumber, 'INV-21');
      expect(request.items.single.name, 'زبدة');
      expect(request.items.single.quantity, 3);
      expect(request.items.single.pack, 'عبوة 250غ');
    });
  });

  group('notifications payload', () {
    test('parses subject/body/icon flags', () {
      final notification = AppNotification.fromJson({
        'id': 77,
        'subject': 'تم إرسال عرض سعر جديد',
        'body': 'يرجى مراجعة عرض السعر للطلبية ORD-55',
        'image': null,
        'icon_type': 'quotation',
        'is_read': false,
        'is_starred': true,
        'is_archived': false,
        'created_at': 'قبل 5 دقائق',
      });

      expect(notification.subject, 'تم إرسال عرض سعر جديد');
      expect(notification.iconType, 'quotation');
      expect(notification.isStarred, isTrue);
      expect(notification.isRead, isFalse);
      expect(notification.copyWith(isRead: true).isRead, isTrue);
    });
  });

  group('surveys payload', () {
    test('parses options provided as a json string', () {
      final question = SurveyQuestion.fromJson({
        'id': 4,
        'question_text': 'كيف تقيّم جودة التوصيل؟',
        'question_type': 'single_choice',
        'options': '["ممتاز","جيد","ضعيف"]',
        'is_required': true,
        'sort_order': 1,
      });

      expect(question.isChoice, isTrue);
      expect(question.options, ['ممتاز', 'جيد', 'ضعيف']);
      expect(question.isRequired, isTrue);
    });
  });

  group('invoices payload', () {
    test('derives items count from the returned lines', () {
      final detail = InvoiceDetail.fromJson({
        'success': true,
        'data': {
          'id': 9,
          'invoice_number': 'INV-9',
          'invoice_date': '2026-11-01',
          'total_amount': 300.0,
          'status': 'paid',
          'notes': null,
          'items': [
            {'item_name_ar': 'شوكولاتة', 'quantity': 2, 'unit_price': 100, 'subtotal': 200},
            {'item_name_ar': 'بسكويت', 'quantity': 2, 'unit_price': 50, 'subtotal': 100},
          ],
        },
      });

      expect(detail.invoice.number, 'INV-9');
      expect(detail.invoice.itemsCount, 2);
      expect(detail.items.length, 2);
      expect(detail.items.first.lineTotal, 200);
    });
  });
}
