import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/formatters.dart';
import '../../models/account.dart';
import '../../state/app_dependencies.dart';
import '../router.dart';
import '../theme/app_theme.dart';
import '../widgets/chrome.dart';
import '../widgets/kit.dart';

class InvoicesScreen extends StatefulWidget {
  const InvoicesScreen({super.key});

  @override
  State<InvoicesScreen> createState() => _InvoicesScreenState();
}

class _InvoicesScreenState extends State<InvoicesScreen> {
  late Future<List<InvoiceSummary>> _future;

  @override
  void initState() {
    super.initState();
    _load();
  }

  void _load() {
    _future = context.read<AppDependencies>().orderService.invoices();
  }

  @override
  Widget build(BuildContext context) {
    return ScreenScaffold(
      title: 'الفواتير',
      subtitle: 'سجل الفواتير الصادرة',
      onRefresh: () async => setState(_load),
      child: FutureView<List<InvoiceSummary>>(
        future: _future,
        onRetry: () => setState(_load),
        emptyCheck: (data) => data.isEmpty,
        emptyIcon: Icons.receipt_long_rounded,
        emptyTitle: 'لا توجد فواتير',
        loadingLabel: 'جاري تحميل الفواتير',
        builder: (context, invoices) => ListView.separated(
          padding: const EdgeInsets.only(bottom: 16),
          itemCount: invoices.length,
          separatorBuilder: (_, __) => const SizedBox(height: 10),
          itemBuilder: (context, index) {
            final invoice = invoices[index];
            return NovaCard(
              onTap: () => Routes.invoice(invoice.id),
              child: Row(
                children: [
                  Container(
                    width: 40,
                    height: 40,
                    decoration: BoxDecoration(
                      color: C.purpleSoft,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: const Icon(Icons.description_rounded,
                        size: 18, color: C.primaryDark),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(invoice.number,
                            style: ts(12, weight: FontWeight.w700),
                            textDirection: TextDirection.ltr),
                        const SizedBox(height: 2),
                        Text('${Fmt.date(invoice.date)} · ${invoice.itemsCount} أصناف',
                            style: ts(9, color: C.muted)),
                      ],
                    ),
                  ),
                  Column(
                    crossAxisAlignment: CrossAxisAlignment.end,
                    children: [
                      Text(Fmt.price(invoice.total),
                          style: ts(12, weight: FontWeight.w700, color: C.primaryDark)),
                      const SizedBox(height: 3),
                      Tag(
                        text: invoice.status,
                        color: StatusPalette.color(_statusKey(invoice.status)),
                        background: StatusPalette.background(_statusKey(invoice.status)),
                      ),
                    ],
                  ),
                ],
              ),
            );
          },
        ),
      ),
    );
  }

  String _statusKey(String status) {
    final value = status.toLowerCase();
    if (value.contains('paid') || value.contains('مدفوع')) return 'success';
    if (value.contains('partial') || value.contains('جزئ')) return 'warning';
    if (value.contains('cancel') || value.contains('ملغ')) return 'danger';
    return 'info';
  }
}
