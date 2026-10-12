class OrderModel {
  final int id;
  final String orderNumber;
  final String status;
  final String statusLabel;
  final String statusClass;
  final int progress;
  final String createdAt;
  final double totalAmount;
  final int itemsCount;
  final bool hasInvoice;
  final String? quotationNumber;

  OrderModel({
    required this.id,
    required this.orderNumber,
    required this.status,
    required this.statusLabel,
    required this.statusClass,
    required this.progress,
    required this.createdAt,
    required this.totalAmount,
    required this.itemsCount,
    required this.hasInvoice,
    this.quotationNumber,
  });

  factory OrderModel.fromJson(Map<String, dynamic> json) {
    return OrderModel(
      id: json['id'],
      orderNumber: json['order_number'] ?? json['id'].toString(),
      status: json['status'] ?? '',
      statusLabel: json['status_label'] ?? '',
      statusClass: json['status_class'] ?? '',
      progress: json['progress'] ?? 0,
      createdAt: json['created_at'] ?? '',
      totalAmount: (json['total_amount'] ?? 0).toDouble(),
      itemsCount: json['items_count'] ?? 0,
      hasInvoice: json['has_invoice'] ?? false,
      quotationNumber: json['quotation_number'],
    );
  }
}