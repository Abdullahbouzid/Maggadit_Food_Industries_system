import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/formatters.dart';
import '../../models/orders.dart';
import '../../state/app_dependencies.dart';
import '../theme/app_theme.dart';
import '../widgets/chrome.dart';
import '../widgets/kit.dart';
import '../../l10n/l10n.dart';

class ReturnsScreen extends StatefulWidget {
  const ReturnsScreen({super.key});

  @override
  State<ReturnsScreen> createState() => _ReturnsScreenState();
}

class _ReturnsScreenState extends State<ReturnsScreen> {
  late Future<List<CustomerReturn>> _future;

  @override
  void initState() {
    super.initState();
    _load();
  }

  void _load() {
    _future = context.read<AppDependencies>().orderService.returns();
  }

  @override
  Widget build(BuildContext context) {
    return ScreenScaffold(
      title: tr(context, 'المرتجعات'),
      subtitle: tr(context, 'سجل طلبات الإرجاع'),
      onRefresh: () async => setState(_load),
      child: FutureView<List<CustomerReturn>>(
        future: _future,
        onRetry: () => setState(_load),
        emptyCheck: (data) => data.isEmpty,
        emptyIcon: Icons.assignment_return_rounded,
        emptyTitle: tr(context, 'لا توجد مرتجعات'),
        loadingLabel: tr(context, 'جاري تحميل المرتجعات'),
        builder: (context, returns) => ListView.separated(
          padding: const EdgeInsets.only(bottom: 16),
          itemCount: returns.length,
          separatorBuilder: (_, __) => const SizedBox(height: 10),
          itemBuilder: (context, index) {
            final item = returns[index];
            return NovaCard(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Row(
                    children: [
                      Expanded(
                        child: Text(
                          item.number,
                          style: ts(12, weight: FontWeight.w700),
                          textDirection: TextDirection.ltr,
                        ),
                      ),
                      Tag(
                        text: item.status,
                        color: StatusPalette.color(_statusKey(item.status)),
                        background: StatusPalette.background(
                          _statusKey(item.status),
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 6),
                  Text(
                    '${Fmt.date(item.createdAt)} · ${item.type}',
                    style: ts(9, color: C.muted),
                  ),
                  if (item.reason.isNotEmpty) ...[
                    const SizedBox(height: 8),
                    Text(
                      item.reason,
                      maxLines: 2,
                      overflow: TextOverflow.ellipsis,
                      style: ts(10, color: C.inkSoft, height: 1.6),
                    ),
                  ],
                  const SizedBox(height: 10),
                  Row(
                    children: [
                      if (item.invoiceNumber != null)
                        Text(
                          tArgs(context, 'فاتورة {0}', [item.invoiceNumber]),
                          style: ts(9, color: C.muted),
                          textDirection: TextDirection.ltr,
                        ),
                      const Spacer(),
                      Text(
                        tr(context, 'قيمة الاسترجاع'),
                        style: ts(9, color: C.muted),
                      ),
                      Text(
                        Fmt.price(item.totalRefund),
                        style: ts(
                          12,
                          weight: FontWeight.w700,
                          color: C.primaryDark,
                        ),
                      ),
                    ],
                  ),
                  if (item.items.isNotEmpty) ...[
                    const Divider(height: 18),
                    for (final line in item.items.take(3))
                      Padding(
                        padding: const EdgeInsets.only(bottom: 6),
                        child: Row(
                          children: [
                            Expanded(
                              child: Text(
                                line.name,
                                maxLines: 1,
                                overflow: TextOverflow.ellipsis,
                                style: ts(10, color: C.inkSoft),
                              ),
                            ),
                            Text(
                              'x${Fmt.quantity(line.quantity)}',
                              style: ts(9.5, color: C.muted),
                            ),
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

  String _statusKey(String status) {
    final value = status.toLowerCase();
    if (value.contains('approv') ||
        value.contains('مقبول') ||
        value.contains('complet')) {
      return 'success';
    }
    if (value.contains('reject') || value.contains('مرفوض')) return 'danger';
    if (value.contains('pending') || value.contains('انتظار')) return 'warning';
    return 'info';
  }
}
