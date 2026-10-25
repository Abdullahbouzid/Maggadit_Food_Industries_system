import 'package:flutter/material.dart';
import '../../services/api_service.dart';

class ReturnCreateScreen extends StatefulWidget {
  final String token;
  final String userId;
  final int invoiceId;

  const ReturnCreateScreen({
    Key? key,
    required this.token,
    required this.userId,
    required this.invoiceId,
  }) : super(key: key);

  @override
  State<ReturnCreateScreen> createState() => _ReturnCreateScreenState();
}

class _ReturnCreateScreenState extends State<ReturnCreateScreen> {
  final ApiService _apiService = ApiService();
  late Future<Map<String, dynamic>> _invoiceFuture;
  String _selectedType = 'partial';
  final TextEditingController _reasonController = TextEditingController();
  Map<int, TextEditingController> _quantityControllers = {};
  bool _isSubmitting = false;

  static final List<Map<String, String>> _returnTypes = [
    {'value': 'full', 'label': 'استرجاع كامل'},
    {'value': 'partial', 'label': 'استرجاع جزئي'},
  ];

  @override
  void initState() {
    super.initState();
    _invoiceFuture = _loadInvoice();
  }

  Future<Map<String, dynamic>> _loadInvoice() async {
    try {
      final response = await _apiService.get('/customer/invoices/${widget.invoiceId}');
      final invoice = (response['invoice'] as Map<String, dynamic>?) ?? {};
      final items = (invoice['items'] as List? ?? [])
          .asMap()
          .entries
          .map((entry) {
            final item = entry.value as Map<String, dynamic>;
            final qty = (item['quantity'] as num? ?? 0).toDouble();
            return {
              'id': item['id'] as int? ?? entry.key,
              'item_id': item['item_id'] as int? ?? 0,
              'name': (item['item_name_ar'] as String? ?? item['item_name_en'] as String? ?? 'منتج').toString(),
              'code': (item['item_id']?.toString() ?? ''),
              'pack': 'وحدة',
              'quantity': qty,
              'returned': 0.0,
              'returnable': qty,
              'unit_price': (item['unit_price'] as num? ?? 0).toDouble(),
            };
          })
          .where((line) => (line['returnable'] as double) > 0)
          .toList();
      return {
        'success': response['success'] ?? true,
        'invoice': invoice,
        'lines': items,
        'types': _returnTypes,
      };
    } catch (e) {
      return {};
    }
  }

  String _formatNumber(dynamic value) {
    if (value == null) return '0';
    final num = double.tryParse(value.toString()) ?? 0;
    final str = num.toStringAsFixed(2);
    return str.replaceAll(RegExp(r'(\.\d*?)0*$'), '').replaceAll(RegExp(r'\.$'), '');
  }

  Future<void> _submitReturn() async {
    setState(() => _isSubmitting = true);

    final quantities = <String, dynamic>{};
    for (final entry in _quantityControllers.entries) {
      final qty = double.tryParse(entry.value.text.trim()) ?? 0;
      if (qty > 0) {
        quantities['${entry.key}'] = qty;
      }
    }

    try {
      final response = await _apiService.post('/customer/returns/${widget.invoiceId}', body: {
        'type': _selectedType,
        'reason': _reasonController.text.trim(),
        'quantities': quantities,
      });

      if (response['success'] == true) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(response['message'] ?? 'تم إرسال طلب الاسترجاع بنجاح', textAlign: TextAlign.center), backgroundColor: const Color(0xFF9879b6)),
        );
        Navigator.of(context).pop();
      } else {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(response['message'] ?? 'فشل إرسال طلب الاسترجاع', textAlign: TextAlign.center), backgroundColor: Colors.red),
        );
      }
    } catch (e) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: const Text('حدث خطأ في الاتصال', textAlign: TextAlign.center), backgroundColor: Colors.red),
      );
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }
  }

  @override
  void dispose() {
    _reasonController.dispose();
    _quantityControllers.values.forEach((c) => c.dispose());
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text('رجوع', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: Color(0xFF6B7280))),
        leading: const SizedBox(),
        actions: [
          IconButton(icon: const Icon(Icons.arrow_back_ios_new, color: Color(0xFF9879b6), size: 20), onPressed: () => Navigator.of(context).pop()),
        ],
        backgroundColor: Colors.white,
        elevation: 0,
      ),
      body: FutureBuilder<Map<String, dynamic>>(
        future: _invoiceFuture,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          }
          if (snapshot.hasError || snapshot.data == null) {
            return Center(child: Text('خطأ في تحميل البيانات', style: TextStyle(color: Colors.grey.shade600)));
          }
          final data = snapshot.data ?? {};

          if (data['success'] != true) {
            return Center(child: Text(data['message'] ?? 'حدث خطأ', style: const TextStyle(color: Color(0xFF9CA3AF), fontSize: 14)));
          }

          final invoice = data['invoice'] as Map<String, dynamic>? ?? {};
          final lines = data['lines'] as List? ?? [];

          if (lines.isEmpty) {
            return Center(
              child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
                Container(width: 80, height: 80, decoration: BoxDecoration(color: Colors.green.shade50, shape: BoxShape.circle), child: const Icon(Icons.check_circle, size: 48, color: Colors.green)),
                const SizedBox(height: 16),
                const Text('لا توجد كميات متاحة للاسترجاع من هذه الفاتورة', style: TextStyle(fontSize: 16, color: Color(0xFF9CA3AF))),
                const SizedBox(height: 16),
                TextButton(onPressed: () => Navigator.of(context).pop(), style: TextButton.styleFrom(backgroundColor: Colors.grey.shade100, padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 10), shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10))), child: const Text('العودة', style: TextStyle(color: Color(0xFF64748B)))),
              ]),
            );
          }

          final firstType = _returnTypes.first['value'] ?? 'exchange';

          return Form(
            child: ListView(
              padding: const EdgeInsets.all(16),
              children: [
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16), boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.04), blurRadius: 8, offset: const Offset(0, 2))]),
                  child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                    const Text('الفاتورة', style: TextStyle(fontSize: 11, color: Color(0xFF9CA3AF))),
                    Text(invoice['invoice_number']?.toString() ?? '', style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: Color(0xFF1E293B))),
                    Text('الإجمالي: ${_formatNumber(invoice['total_amount'])} ر.س', style: const TextStyle(fontSize: 12, color: Color(0xFF9CA3AF))),
                  ]),
                ),
                const SizedBox(height: 16),
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16), boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.04), blurRadius: 8, offset: const Offset(0, 2))]),
                  child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                    const Text('نوع الاسترجاع', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF1E293B))),
                    ..._returnTypes.map((type) => Padding(padding: const EdgeInsets.only(top: 8), child: _buildTypeOption(type['value']!, type['label']!, firstType))),
                  ]),
                ),
                const SizedBox(height: 16),
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16), boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.04), blurRadius: 8, offset: const Offset(0, 2))]),
                  child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                    const Text('سبب الاسترجاع', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF1E293B))),
                    const SizedBox(height: 8),
                    TextFormField(
                      controller: _reasonController,
                      decoration: InputDecoration(hintText: 'مثال: منتج تالف أو غير مطابق', hintStyle: TextStyle(color: Colors.grey.shade400, fontSize: 13), border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),),
                      maxLines: 2,
                    ),
                  ]),
                ),
                const SizedBox(height: 16),
                Container(
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16), boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.04), blurRadius: 8, offset: const Offset(0, 2))]),
                  child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                    const Text('الكميات المراد استرجاعها', style: TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF1E293B))),
                    const SizedBox(height: 4),
                    const Text('اترك الحقل فارغاً أو صفراً للكمميات التي لا تريد استرجاعها', style: TextStyle(fontSize: 11, color: Color(0xFF9CA3AF))),
                    const SizedBox(height: 12),
                    ...lines.asMap().entries.map((entry) {
                      final line = entry.value as Map<String, dynamic>;
                      final lineId = line['id'] as int? ?? entry.key;
                      return Padding(padding: const EdgeInsets.only(bottom: 12), child: _buildLineInput(line, lineId));
                    }),
                  ]),
                ),
                const SizedBox(height: 16),
                SizedBox(
                  width: double.infinity,
                  child: ElevatedButton(
                    onPressed: _isSubmitting ? null : _submitReturn,
                    style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF9879b6), foregroundColor: Colors.white, padding: const EdgeInsets.symmetric(vertical: 14), shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12))),
                    child: _isSubmitting ? const SizedBox(width: 18, height: 18, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2)) : const Text('إرسال طلب الاسترجاع', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600)),
                  ),
                ),
                const SizedBox(height: 32),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _buildTypeOption(String value, String label, String defaultValue) {
    final bool isSelected = _selectedType == value;
    return GestureDetector(
      onTap: () => setState(() => _selectedType = value),
      child: Container(
        margin: const EdgeInsets.only(top: 4),
        padding: const EdgeInsets.all(12),
        decoration: BoxDecoration(
          color: isSelected ? Colors.purple.shade50 : Colors.grey.shade50,
          borderRadius: BorderRadius.circular(12),
          border: Border.all(color: isSelected ? const Color(0xFF9879b6) : const Color(0xFFE2E8F0)),
        ),
        child: Row(children: [
          Radio<String>(value: value, groupValue: _selectedType, onChanged: (v) => setState(() => _selectedType = v!), activeColor: const Color(0xFF9879b6), materialTapTargetSize: MaterialTapTargetSize.shrinkWrap),
          const SizedBox(width: 6),
          Text(label, style: TextStyle(fontSize: 12, color: isSelected ? const Color(0xFF9879b6) : const Color(0xFF6B7280), fontWeight: isSelected ? FontWeight.bold : FontWeight.normal)),
        ]),
      ),
    );
  }

  Widget _buildLineInput(Map<String, dynamic> line, int lineId) {
    final controller = _quantityControllers.putIfAbsent(lineId, () => TextEditingController());
    final returnable = _formatNumber(line['returnable'] ?? 0);
    final returned = _formatNumber(line['returned'] ?? 0);
    final isReturned = (line['returned'] as num? ?? 0) > 0;

    return Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
      Text(line['name']?.toString() ?? 'منتج', style: const TextStyle(fontSize: 13, fontWeight: FontWeight.bold, color: Color(0xFF1E293B))),
      Text('${line['pack'] ?? ''} — متاح للاسترجاع: $returnable${isReturned ? ' (سبق استرجاع $returned)' : ''}', style: const TextStyle(fontSize: 10, color: Color(0xFF9CA3AF))),
      const SizedBox(height: 6),
      TextFormField(
        controller: controller,
        keyboardType: const TextInputType.numberWithOptions(decimal: true),
        decoration: InputDecoration(
          hintText: '0',
          hintStyle: TextStyle(color: Colors.grey.shade400),
          border: OutlineInputBorder(borderRadius: BorderRadius.circular(8)),
          contentPadding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
        ),
        style: const TextStyle(fontSize: 13),
        textAlign: TextAlign.center,
      ),
    ]);
  }
}
