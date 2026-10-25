import 'package:flutter/material.dart';
import '../../services/api_service.dart';

class MyProductsScreen extends StatefulWidget {
  final String token;
  final String userId;

  const MyProductsScreen({
    Key? key,
    required this.token,
    required this.userId,
  }) : super(key: key);

  @override
  State<MyProductsScreen> createState() => _MyProductsScreenState();
}

class _MyProductsScreenState extends State<MyProductsScreen> {
  final ApiService _apiService = ApiService();
  late Future<Map<String, dynamic>> _productsFuture;

  @override
  void initState() {
    super.initState();
    _productsFuture = _loadProducts();
  }

  Future<Map<String, dynamic>> _loadProducts() async {
    try {
      final response = await _apiService.get('/customer/my-products');
      return response;
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
      body: RefreshIndicator(
        onRefresh: () async => setState(() => _productsFuture = _loadProducts()),
        child: SingleChildScrollView(
          physics: const BouncingScrollPhysics(),
          padding: const EdgeInsets.all(16),
          child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
            // Summary cards
            FutureBuilder<Map<String, dynamic>>(
              future: _productsFuture,
              builder: (context, snapshot) {
                final data = snapshot.data;
                final totalItems = data?['total_items'] ?? 0;
                final totalQuantity = data?['total_quantity'] ?? 0.0;

                return Row(children: [
                  Expanded(child: _buildSummaryCard('${_formatNumber(totalItems)}', 'عدد المنتجات', Colors.grey.shade50, Colors.grey.shade500)),
                  const SizedBox(width: 12),
                  Expanded(child: _buildSummaryCard(_formatNumber(totalQuantity), 'إجمالي الكميات', Colors.purple.shade50, const Color(0xFF9879b6))),
                ]);
              },
            ),
            const SizedBox(height: 16),

            Container(
              width: double.infinity,
              padding: const EdgeInsets.all(12),
              decoration: BoxDecoration(color: Colors.purple.shade50, borderRadius: BorderRadius.circular(12), border: Border.all(color: Colors.purple.shade100)),
              child: const Text('يوضّح الجدول أدناه الكمية التي طلبتها من كل دفعة إنتاج (باتش) على حدة، حتى تعرف مصدر المنتج وتاريخ صلاحيته.', style: TextStyle(fontSize: 11, color: Color(0xFF7C3AED))),
            ),
            const SizedBox(height: 16),

            // Products list
            FutureBuilder<Map<String, dynamic>>(
              future: _productsFuture,
              builder: (context, snapshot) {
                if (snapshot.connectionState == ConnectionState.waiting) {
                  return const Center(child: Padding(padding: EdgeInsets.all(32), child: CircularProgressIndicator()));
                }
                final data = snapshot.data;
                final rows = data?['rows'] as List? ?? [];

                if (rows.isEmpty) {
                  return Container(
                    padding: const EdgeInsets.all(32),
                    alignment: Alignment.center,
                    child: Column(children: const [
                      Icon(Icons.inventory_2, size: 56, color: Color(0xFFCBD5E1)),
                      SizedBox(height: 12),
                      Text('لا توجد منتجات في طلباتك بعد', style: TextStyle(fontSize: 14, color: Color(0xFF9CA3AF))),
                    ]),
                  );
                }

                return ListView.separated(
                  shrinkWrap: true,
                  physics: const NeverScrollableScrollPhysics(),
                  itemCount: rows.length,
                  separatorBuilder: (context, index) => const SizedBox(height: 12),
                  itemBuilder: (context, index) {
                    final row = rows[index] as Map<String, dynamic>;
                    final batches = row['batches'] as List? ?? [];
                    return Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16), boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.04), blurRadius: 8, offset: const Offset(0, 2))]),
                      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                        Row(mainAxisAlignment: MainAxisAlignment.spaceBetween, children: [
                          Text(row['name'] ?? '', style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: Color(0xFF1E293B))),
                          Text(_formatNumber(row['total_quantity']), style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: Color(0xFF7C3AED))),
                        ]),
                        const SizedBox(height: 2),
                        Text('${row['code'] ?? '—'} — ${row['invoice_count']} فاتورة', style: const TextStyle(fontSize: 11, color: Color(0xFF9CA3AF))),
                        if (batches.isNotEmpty)
                          Container(
                            margin: const EdgeInsets.only(top: 12),
                            child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                              const Text('الكميات حسب دفعة الإنتاج', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF64748B))),
                              const SizedBox(height: 8),
                              ...batches.map((batch) => Padding(
                                padding: const EdgeInsets.only(bottom: 6),
                                child: Row(children: [
                                  Expanded(flex: 2, child: Text(batch['batch_number']?.toString() ?? '—', style: const TextStyle(fontSize: 11, color: Color(0xFF1E293B), fontWeight: FontWeight.w600))),
                                  Expanded(flex: 2, child: Text(batch['expiry_date'] ?? '—', style: const TextStyle(fontSize: 11, color: Color(0xFF9CA3AF)))),
                                  Expanded(flex: 1, child: Text(_formatNumber(batch['quantity']), style: const TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Color(0xFF7C3AED)), textAlign: TextAlign.right)),
                                ]),
                              )),
                            ]),
                          ),
                        if (row['total_value'] != null)
                          Padding(padding: const EdgeInsets.only(top: 8), child: Text('القيمة الإجمالية: ${_formatNumber(row['total_value'])} ر.س', style: const TextStyle(fontSize: 11, color: Color(0xFF9CA3AF)))),
                      ]),
                    );
                  },
                );
              },
            ),
          ]),
        ),
      ),
    );
  }

  Widget _buildSummaryCard(String value, String label, Color bgColor, Color textColor) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16), boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.04), blurRadius: 8, offset: const Offset(0, 2))]),
      child: Column(children: [
        Text(value, style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: textColor == Colors.grey.shade500 ? const Color(0xFF1E293B) : const Color(0xFF7C3AED))),
        const SizedBox(height: 4),
        Text(label, style: const TextStyle(fontSize: 10, color: Color(0xFF9CA3AF))),
      ]),
    );
  }
}
