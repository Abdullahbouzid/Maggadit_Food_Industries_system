import 'dart:typed_data';

import '../core/api_client.dart';
import '../models/account.dart';
import '../models/json_utils.dart';
import '../models/orders.dart';

class OrderService {
  OrderService(this._api);

  static const _prefix = '/customer';
  final ApiClient _api;

  String? get _baseUrl => _api.baseUrl;

  Future<List<OrderSummary>> orders() async {
    final json = await _api.get('$_prefix/orders');
    return asMapList(
      json['orders'],
    ).map((e) => OrderSummary.fromJson(e, baseUrl: _baseUrl)).toList();
  }

  Future<OrderDetail> order(int id) async {
    final json = await _api.get('$_prefix/orders/$id');
    return OrderDetail.fromJson(asMap(json['order']), baseUrl: _baseUrl);
  }

  Future<OrderHistory> history() async {
    final json = await _api.get('$_prefix/order-history');
    return OrderHistory.fromJson(json, baseUrl: _baseUrl);
  }

  Future<Quotation> quotation(int orderId) async {
    final json = await _api.get('$_prefix/orders/$orderId/quotation');
    return Quotation.fromJson(
      asMap(json['quotation']).isEmpty ? json : asMap(json['quotation']),
    );
  }

  Future<void> respondToQuotation(
    int orderId,
    String response, {
    String? notes,
  }) async {
    await _api.post(
      '$_prefix/orders/$orderId/quotation/respond',
      body: {'response': response, 'notes': notes},
    );
  }

  Future<List<CustomerReturn>> returns() async {
    final json = await _api.get('$_prefix/returns');
    final returns = json['returns'];
    final rows = returns is Map
        ? asMapList(returns['data'])
        : asMapList(returns ?? json['data']);
    return rows
        .map((e) => CustomerReturn.fromJson(e, baseUrl: _baseUrl))
        .toList();
  }

  Future<List<OwnedProduct>> myProducts() async {
    final json = await _api.get('$_prefix/my-products');
    return asMapList(json['rows']).map(OwnedProduct.fromJson).toList();
  }

  /// Places the order: items[{id, quantity, pack_id, name}] + delivery info.
  Future<Map<String, dynamic>> checkout({
    required List<Map<String, dynamic>> items,
    String? deliveryAddress,
    String? deliveryPhone,
    String? scheduledDeliveryDate,
    int? branchId,
    String? notes,
  }) async {
    return _api.post(
      '$_prefix/checkout',
      body: {
        'items': items,
        'delivery_address': deliveryAddress,
        'delivery_phone': deliveryPhone,
        'scheduled_delivery_date': scheduledDeliveryDate,
        'branch_id': branchId,
        'notes': notes,
      },
    );
  }

  Future<WalletStatement> financialStatement() async {
    final json = await _api.get('$_prefix/financial-statement');
    return WalletStatement.fromJson(json);
  }

  Future<List<InvoiceSummary>> invoices() async {
    final json = await _api.get('$_prefix/invoices');
    final data = json['data'];
    if (data is Map) {
      return asMapList(data['data']).map(InvoiceSummary.fromJson).toList();
    }
    return asMapList(data).map(InvoiceSummary.fromJson).toList();
  }

  Future<InvoiceDetail> invoice(int id) async {
    final json = await _api.get('$_prefix/invoices/$id');
    return InvoiceDetail.fromJson(json, baseUrl: _baseUrl);
  }

  Future<Uint8List> orderPdf(int id) async {
    final bytes = await _api.download('$_prefix/orders/$id/pdf');
    return Uint8List.fromList(bytes);
  }

  Future<Uint8List> invoicePdf(int id) async {
    final bytes = await _api.download('$_prefix/invoices/$id/pdf');
    return Uint8List.fromList(bytes);
  }

  Future<Uint8List> quotationPdf(int id) async {
    final bytes = await _api.download('$_prefix/orders/$id/quotation-pdf');
    return Uint8List.fromList(bytes);
  }
}

class SurveyService {
  SurveyService(this._api);

  static const _prefix = '/customer';
  final ApiClient _api;

  Future<List<SurveySummary>> surveys() async {
    final json = await _api.get('$_prefix/surveys');
    return asMapList(json['surveys']).map(SurveySummary.fromJson).toList();
  }

  Future<SurveyDetail> survey(int id) async {
    final json = await _api.get('$_prefix/surveys/$id');
    return SurveyDetail.fromJson(json);
  }

  /// answers: {questionId: answer} — matches `answers.*` validation on the API.
  Future<void> submit(int id, Map<int, String> answers) async {
    await _api.post(
      '$_prefix/surveys/$id/respond',
      body: {
        'answers': answers.map((key, value) => MapEntry(key.toString(), value)),
      },
    );
  }
}
