import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/formatters.dart';
import '../../models/orders.dart';
import '../../state/app_dependencies.dart';
import '../theme/app_theme.dart';
import '../widgets/chrome.dart';
import '../widgets/kit.dart';

class MyProductsScreen extends StatefulWidget {
  const MyProductsScreen({super.key});

  @override
  State<MyProductsScreen> createState() => _MyProductsScreenState();
}

class _MyProductsScreenState extends State<MyProductsScreen> {
  late Future<List<OwnedProduct>> _future;

  @override
  void initState() {
    super.initState();
    _load();
  }

  void _load() {
    _future = context.read<AppDependencies>().orderService.myProducts();
  }

  @override
  Widget build(BuildContext context) {
    return ScreenScaffold(
      title: 'منتجاتي',
      subtitle: 'الكميات المشتراة حسب الدفعات',
      onRefresh: () async => setState(_load),
      child: FutureView<List<OwnedProduct>>(
        future: _future,
        onRetry: () => setState(_load),
        emptyCheck: (data) => data.isEmpty,
        emptyIcon: Icons.inventory_2_outlined,
        emptyTitle: 'لا توجد منتجات',
        emptyMessage: 'ستظهر منتجاتك المشتراة هنا بعد أول فاتورة',
        loadingLabel: 'جاري تحميل منتجاتك',
        builder: (context, products) => ListView.separated(
          padding: const EdgeInsets.only(bottom: 16),
          itemCount: products.length,
          separatorBuilder: (_, __) => const SizedBox(height: 10),
          itemBuilder: (context, index) {
            final product = products[index];
            return NovaCard(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: Text(product.name,
                            maxLines: 1, overflow: TextOverflow.ellipsis,
                            style: ts(12, weight: FontWeight.w700)),
                      ),
                      Tag(
                        text: '${Fmt.quantity(product.totalQuantity)} وحدة',
                        color: C.primaryDark,
                        background: C.purpleSoft,
                      ),
                    ],
                  ),
                  const SizedBox(height: 4),
                  Text('كود ${product.code} · ${product.invoiceCount} فاتورة',
                      style: ts(9, color: C.muted), textDirection: TextDirection.ltr),
                  const SizedBox(height: 10),
                  Row(
                    children: [
                      Text('القيمة', style: ts(9, color: C.muted)),
                      const SizedBox(width: 4),
                      Text(Fmt.price(product.totalValue),
                          style: ts(11.5, weight: FontWeight.w700, color: C.primaryDark)),
                    ],
                  ),
                  if (product.batches.isNotEmpty) ...[
                    const Divider(height: 18),
                    Text('الدفعات', style: ts(10, weight: FontWeight.w600, color: C.inkSoft)),
                    const SizedBox(height: 6),
                    for (final batch in product.batches.take(5))
                      Padding(
                        padding: const EdgeInsets.only(bottom: 4),
                        child: Row(
                          children: [
                            Expanded(
                              child: Text('دفعة ${batch.batchNumber}',
                                  style: ts(9.5, color: C.muted),
                                  textDirection: TextDirection.ltr),
                            ),
                            if (batch.expiryDate != null)
                              Text('انتهاء ${Fmt.date(batch.expiryDate)}',
                                  style: ts(8.5, color: C.muted)),
                            const SizedBox(width: 8),
                            Text(Fmt.quantity(batch.quantity),
                                style: ts(9.5, weight: FontWeight.w600)),
                          ],
                        ),
                      ),
                  ],
                ],
              ),
            );
          },
        ),
      ),
    );
  }
}
