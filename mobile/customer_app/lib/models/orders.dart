import '../l10n/l10n.dart';
import 'json_utils.dart';

class OrderLine {
  OrderLine({
    required this.name,
    required this.quantity,
    required this.unitPrice,
    required this.lineTotal,
    this.pack,
    this.image,
  });

  factory OrderLine.fromJson(Map<String, dynamic> json, {String? baseUrl}) {
    final item = asMap(json['item']);
    final packMap = asMap(json['pack']);
    final quantity = asDouble(json['quantity'] ?? json['quantity_returned']);
    final unitPrice = asDouble(json['unit_price'] ?? json['price']);
    final lineTotal = asDouble(
      json['line_total'] ?? json['subtotal'],
      quantity * unitPrice,
    );
    final name =
        [
              json['name'],
              json['item_name_ar'],
              json['item_name'],
              item['name_ar'],
              item['name_en'],
            ]
            .map((value) => asString(value))
            .firstWhere(
              (value) => value.isNotEmpty,
              orElse: () => L10n.tr('منتج'),
            );
    final packLabel = [json['pack'], json['pack_name'], packMap['display_name']]
        .whereType<String>()
        .map((value) => value.trim())
        .firstWhere((value) => value.isNotEmpty, orElse: () => '');

    return OrderLine(
      name: name,
      quantity: quantity,
      unitPrice: unitPrice,
      lineTotal: lineTotal == 0 && quantity != 0
          ? quantity * unitPrice
          : lineTotal,
      pack: packLabel.isEmpty ? null : packLabel,
      image: mediaUrl(
        asString(json['image']).isEmpty ? null : asString(json['image']),
        baseUrl: baseUrl,
      ),
    );
  }

  final String name;
  final double quantity;
  final double unitPrice;
  final double lineTotal;
  final String? pack;
  final String? image;
}

class OrderStep {
  OrderStep({
    required this.key,
    required this.label,
    required this.icon,
    required this.color,
    this.state = '',
    this.reached = false,
  });

  factory OrderStep.fromJson(Map<String, dynamic> json) {
    final state = asString(json['state']);
    return OrderStep(
      key: asString(json['key']),
      label: asString(json['label']),
      icon: asString(json['icon'], 'circle'),
      color: asString(json['color'], 'muted'),
      state: state,
      reached: state == 'done' || state == 'current' || asBool(json['reached']),
    );
  }

  final String key;
  final String label;
  final String icon;
  final String color;
  final String state;
  final bool reached;
}

class OrderTimelineEntry {
  OrderTimelineEntry({required this.status, this.by, this.date});

  factory OrderTimelineEntry.fromJson(Map<String, dynamic> json) =>
      OrderTimelineEntry(
        status: asString(json['to'] ?? json['status']),
        by: asString(json['by']).isEmpty ? null : asString(json['by']),
        date: asString(json['date']).isEmpty ? null : asString(json['date']),
      );

  final String status;
  final String? by;
  final String? date;
}

/// Item of `/api/customer/orders` list.
class OrderSummary {
  OrderSummary({
    required this.id,
    required this.number,
    required this.date,
    required this.total,
    required this.status,
    required this.statusText,
    required this.statusClass,
    required this.progress,
    required this.itemsCount,
    this.items = const [],
    this.branchName,
    this.invoiceNumber,
    this.quotationNumber,
    this.canRespond = false,
    this.hasQuotation = false,
    this.hasInvoice = false,
  });

  factory OrderSummary.fromJson(Map<String, dynamic> json, {String? baseUrl}) {
    return OrderSummary(
      id: asInt(json['id']),
      number: asString(json['order_number'] ?? json['number']),
      date: asString(json['date']),
      total: asDouble(json['total']),
      status: asString(json['status']),
      statusText: asString(json['status_text']),
      statusClass: asString(json['status_class'], 'muted'),
      progress: asInt(json['progress']),
      itemsCount: asInt(
        json['items_count'],
        (json['items'] is List ? (json['items'] as List).length : 0),
      ),
      items: asMapList(
        json['items'],
      ).map((e) => OrderLine.fromJson(e, baseUrl: baseUrl)).toList(),
      branchName: asString(json['branch_name']).isEmpty
          ? null
          : asString(json['branch_name']),
      invoiceNumber: asString(json['invoice_number']).isEmpty
          ? null
          : asString(json['invoice_number']),
      quotationNumber: asString(json['quotation_number']).isEmpty
          ? null
          : asString(json['quotation_number']),
      canRespond: asBool(json['can_respond']),
      hasQuotation: asBool(json['has_quotation']),
      hasInvoice: asBool(json['has_invoice']),
    );
  }

  final int id;
  final String number;
  final String date;
  final double total;
  final String status;
  final String statusText;
  final String statusClass;
  final int progress;
  final int itemsCount;
  final List<OrderLine> items;
  final String? branchName;
  final String? invoiceNumber;
  final String? quotationNumber;
  final bool canRespond;
  final bool hasQuotation;
  final bool hasInvoice;
}

class OrderDetail extends OrderSummary {
  OrderDetail({
    required super.id,
    required super.number,
    required super.date,
    required super.total,
    required super.status,
    required super.statusText,
    required super.statusClass,
    required super.progress,
    required super.itemsCount,
    super.items,
    super.branchName,
    super.invoiceNumber,
    super.quotationNumber,
    super.canRespond,
    super.hasQuotation,
    super.hasInvoice,
    this.deliveryAddress,
    this.deliveryPhone,
    this.customerName,
    this.steps = const [],
    this.timeline = const [],
    this.quotation,
    this.customerResponse,
  });

  /// Accepts either the raw `orderDetails` payload (`{order: {...}, steps: []}`)
  /// or a bare order object.
  factory OrderDetail.fromJson(Map<String, dynamic> json, {String? baseUrl}) {
    final root = asMap(json['order']);
    final order = root.isEmpty ? json : root;
    final summary = OrderSummary.fromJson(order, baseUrl: baseUrl);
    return OrderDetail(
      id: summary.id,
      number: summary.number,
      date: summary.date,
      total: summary.total,
      status: summary.status,
      statusText: summary.statusText,
      statusClass: summary.statusClass,
      progress: summary.progress,
      itemsCount: summary.itemsCount,
      items: summary.items,
      branchName: summary.branchName,
      invoiceNumber: summary.invoiceNumber,
      quotationNumber: summary.quotationNumber,
      canRespond: summary.canRespond,
      hasQuotation: summary.hasQuotation,
      hasInvoice: summary.hasInvoice,
      deliveryAddress: asString(order['delivery_address']).isEmpty
          ? null
          : asString(order['delivery_address']),
      deliveryPhone: asString(order['delivery_phone']).isEmpty
          ? null
          : asString(order['delivery_phone']),
      customerName: asString(order['customer_name']).isEmpty
          ? null
          : asString(order['customer_name']),
      steps: asMapList(json['steps']).map(OrderStep.fromJson).toList(),
      timeline: asMapList(
        json['timeline'],
      ).map(OrderTimelineEntry.fromJson).toList(),
      quotation: asMap(order['quotation']).isEmpty
          ? null
          : Quotation.fromJson(asMap(order['quotation'])),
      customerResponse: asString(order['customer_response']).isEmpty
          ? null
          : asString(order['customer_response']),
    );
  }

  final String? deliveryAddress;
  final String? deliveryPhone;
  final String? customerName;
  final List<OrderStep> steps;
  final List<OrderTimelineEntry> timeline;
  final Quotation? quotation;
  final String? customerResponse;

  bool get hasDeliveryInfo =>
      (deliveryAddress ?? '').isNotEmpty || (deliveryPhone ?? '').isNotEmpty;
}

class QuotationLine {
  QuotationLine({
    required this.name,
    required this.quantity,
    required this.unitPrice,
    required this.lineTotal,
  });

  factory QuotationLine.fromJson(Map<String, dynamic> json) => QuotationLine(
    name: asString(json['item_name_ar'] ?? json['item_name_en']),
    quantity: asDouble(json['quantity']),
    unitPrice: asDouble(json['unit_price']),
    lineTotal: asDouble(json['line_total']),
  );

  final String name;
  final double quantity;
  final double unitPrice;
  final double lineTotal;
}

class Quotation {
  Quotation({
    required this.id,
    required this.number,
    required this.status,
    required this.total,
    this.subtotal = 0,
    this.taxTotal = 0,
    this.discountTotal = 0,
    this.validUntil,
    this.customerResponse,
    this.items = const [],
  });

  factory Quotation.fromJson(Map<String, dynamic> json) => Quotation(
    id: asInt(json['id']),
    number: asString(json['quotation_number']),
    status: asString(json['status']),
    total: asDouble(json['total']),
    subtotal: asDouble(json['subtotal']),
    taxTotal: asDouble(json['tax_total']),
    discountTotal: asDouble(json['discount_total']),
    validUntil: asString(json['valid_until']).isEmpty
        ? null
        : asString(json['valid_until']),
    customerResponse: asString(json['customer_response']).isEmpty
        ? null
        : asString(json['customer_response']),
    items: asMapList(json['items']).map(QuotationLine.fromJson).toList(),
  );

  final int id;
  final String number;
  final String status;
  final double total;
  final double subtotal;
  final double taxTotal;
  final double discountTotal;
  final String? validUntil;
  final String? customerResponse;
  final List<QuotationLine> items;
}

/// `/api/customer/order-history` -> { stats, rows }
class OrderHistory {
  OrderHistory({required this.rows, required this.stats});

  factory OrderHistory.fromJson(Map<String, dynamic> json, {String? baseUrl}) {
    final payload = asMap(json['data']).isEmpty ? json : asMap(json['data']);
    return OrderHistory(
      rows: asMapList(
        payload['rows'],
      ).map((e) => OrderSummary.fromJson(e, baseUrl: baseUrl)).toList(),
      stats: Map<String, int>.fromEntries(
        asMap(
          payload['stats'],
        ).entries.map((e) => MapEntry(e.key, asInt(e.value))),
      ),
    );
  }

  final List<OrderSummary> rows;
  final Map<String, int> stats;
}

class CustomerReturn {
  CustomerReturn({
    required this.id,
    required this.number,
    required this.status,
    required this.type,
    required this.reason,
    required this.totalRefund,
    required this.createdAt,
    this.branchName,
    this.invoiceNumber,
    this.items = const [],
  });

  factory CustomerReturn.fromJson(
    Map<String, dynamic> json, {
    String? baseUrl,
  }) {
    final invoice = asMap(json['invoice']);
    return CustomerReturn(
      id: asInt(json['id']),
      number: asString(json['return_number']),
      status: asString(json['status']),
      type: asString(json['type']),
      reason: asString(json['reason']),
      totalRefund: asDouble(json['total_refund']),
      createdAt: asString(json['created_at']),
      branchName: asString(json['branch_name']).isEmpty
          ? null
          : asString(json['branch_name']),
      invoiceNumber:
          asString(json['invoice_number'] ?? invoice['invoice_number']).isEmpty
          ? null
          : asString(json['invoice_number'] ?? invoice['invoice_number']),
      items: asMapList(
        json['items'],
      ).map((e) => OrderLine.fromJson(e, baseUrl: baseUrl)).toList(),
    );
  }

  final int id;
  final String number;
  final String status;
  final String type;
  final String reason;
  final double totalRefund;
  final String createdAt;
  final String? branchName;
  final String? invoiceNumber;
  final List<OrderLine> items;
}

class OwnedBatch {
  OwnedBatch({
    required this.batchNumber,
    required this.quantity,
    this.expiryDate,
  });

  factory OwnedBatch.fromJson(Map<String, dynamic> json) => OwnedBatch(
    batchNumber: asString(json['batch_number']),
    quantity: asDouble(json['quantity']),
    expiryDate: asString(json['expiry_date']).isEmpty
        ? null
        : asString(json['expiry_date']),
  );

  final String batchNumber;
  final double quantity;
  final String? expiryDate;
}

class OwnedProduct {
  OwnedProduct({
    required this.itemId,
    required this.name,
    required this.code,
    required this.totalQuantity,
    required this.invoiceCount,
    required this.totalValue,
    this.batches = const [],
  });

  factory OwnedProduct.fromJson(Map<String, dynamic> json) => OwnedProduct(
    itemId: asInt(json['item_id']),
    name: asString(json['name']),
    code: asString(json['code']),
    totalQuantity: asDouble(json['total_quantity']),
    invoiceCount: asInt(json['invoice_count']),
    totalValue: asDouble(json['total_value']),
    batches: asMapList(json['batches']).map(OwnedBatch.fromJson).toList(),
  );

  final int itemId;
  final String name;
  final String code;
  final double totalQuantity;
  final int invoiceCount;
  final double totalValue;
  final List<OwnedBatch> batches;
}
