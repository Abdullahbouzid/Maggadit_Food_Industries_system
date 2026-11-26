import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/formatters.dart';
import '../../models/orders.dart';
import '../../state/app_dependencies.dart';
import '../router.dart';
import '../theme/app_theme.dart';
import '../widgets/chrome.dart';
import '../widgets/kit.dart';
import '../../l10n/l10n.dart';

class OrdersScreen extends StatefulWidget {
  const OrdersScreen({super.key});

  @override
  State<OrdersScreen> createState() => _OrdersScreenState();
}

class _OrdersScreenState extends State<OrdersScreen> {
  late Future<OrderHistory> _future;
  int _tab = 0;

  List<String> _tabs(BuildContext context) => [
    tr(context, 'الكل'),
    tr(context, 'قيد التنفيذ'),
    tr(context, 'مكتملة'),
  ];

  @override
  void initState() {
    super.initState();
    _load();
  }

  void _load() {
    _future = context.read<AppDependencies>().orderService.history();
  }

  List<OrderSummary> _filter(List<OrderSummary> rows) {
    if (_tab == 0) return rows;
    final success = StatusPalette.color('success');
    final danger = StatusPalette.color('danger');
    if (_tab == 1) {
      return rows
          .where(
            (o) =>
                StatusPalette.color(o.statusClass) != success &&
                StatusPalette.color(o.statusClass) != danger,
          )
          .toList();
    }
    return rows
        .where((o) => StatusPalette.color(o.statusClass) == success)
        .toList();
  }

  @override
  Widget build(BuildContext context) {
    final tabs = _tabs(context);
    return ScreenScaffold(
      title: tr(context, 'طلباتي'),
      subtitle: tr(context, 'متابعة الطلبات والفواتير'),
      onRefresh: () async => setState(_load),
      child: Column(
        children: [
          Container(
            padding: const EdgeInsets.all(4),
            decoration: BoxDecoration(
              color: Colors.white,
              borderRadius: BorderRadius.circular(14),
              border: Border.all(color: C.border),
            ),
            child: Row(
              children: [
                for (var i = 0; i < tabs.length; i++)
                  Expanded(
                    child: GestureDetector(
                      onTap: () => setState(() => _tab = i),
                      child: AnimatedContainer(
                        duration: const Duration(milliseconds: 180),
                        padding: const EdgeInsets.symmetric(vertical: 9),
                        decoration: BoxDecoration(
                          color: _tab == i ? C.primary : Colors.transparent,
                          borderRadius: BorderRadius.circular(11),
                        ),
                        child: Text(
                          tabs[i],
                          textAlign: TextAlign.center,
                          style: ts(
                            11,
                            weight: FontWeight.w600,
                            color: _tab == i ? Colors.white : C.inkSoft,
                          ),
                        ),
                      ),
                    ),
                  ),
              ],
            ),
          ),
          const SizedBox(height: 12),
          Expanded(
            child: FutureView<OrderHistory>(
              future: _future,
              onRetry: () => setState(_load),
              emptyCheck: (data) => data.rows.isEmpty,
              emptyIcon: Icons.receipt_long_rounded,
              emptyTitle: tr(context, 'لا توجد طلبات'),
              emptyMessage: tr(context, 'ستظهر طلباتك هنا فور إرسالها'),
              loadingLabel: tr(context, 'جاري تحميل الطلبات'),
              builder: (context, history) {
                final rows = _filter(history.rows);
                if (rows.isEmpty) {
                  return EmptyState(
                    icon: Icons.filter_alt_off_rounded,
                    title: tr(context, 'لا توجد طلبات في هذا التصنيف'),
                    actionLabel: tr(context, 'عرض الكل'),
                    onAction: () => setState(() => _tab = 0),
                  );
                }
                return RefreshIndicator(
                  color: C.primary,
                  onRefresh: () async => setState(_load),
                  child: ListView.separated(
                    padding: const EdgeInsets.only(bottom: 16),
                    itemCount: rows.length,
                    separatorBuilder: (_, __) => const SizedBox(height: 10),
                    itemBuilder: (context, index) =>
                        _OrderCard(order: rows[index]),
                  ),
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}

class _OrderCard extends StatelessWidget {
  const _OrderCard({required this.order});

  final OrderSummary order;

  @override
  Widget build(BuildContext context) {
    final color = StatusPalette.color(order.statusClass);
    final background = StatusPalette.background(order.statusClass);

    return NovaCard(
      onTap: () => Routes.order(order.id),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      order.number,
                      style: ts(12.5, weight: FontWeight.w700),
                      textDirection: TextDirection.ltr,
                    ),
                    const SizedBox(height: 2),
                    Text(
                      tArgs(context, '{0} · {1} أصناف', [
                        Fmt.date(order.date),
                        order.itemsCount,
                      ]),
                      style: ts(9, color: C.muted),
                    ),
                  ],
                ),
              ),
              Tag(text: order.statusText, color: color, background: background),
            ],
          ),
          const SizedBox(height: 10),
          ClipRRect(
            borderRadius: BorderRadius.circular(6),
            child: LinearProgressIndicator(
              value: (order.progress / 100).clamp(0, 1).toDouble(),
              minHeight: 5,
              backgroundColor: C.border,
              valueColor: AlwaysStoppedAnimation<Color>(color),
            ),
          ),
          const SizedBox(height: 10),
          Row(
            children: [
              if (order.branchName != null) ...[
                const Icon(
                  Icons.store_mall_directory_rounded,
                  size: 13,
                  color: C.muted,
                ),
                const SizedBox(width: 3),
                Flexible(
                  child: Text(
                    order.branchName!,
                    maxLines: 1,
                    overflow: TextOverflow.ellipsis,
                    style: ts(9, color: C.muted),
                  ),
                ),
              ],
              const Spacer(),
              Text(
                Fmt.price(order.total),
                style: ts(13, weight: FontWeight.w700, color: C.primaryDark),
              ),
            ],
          ),
          if (order.canRespond) ...[
            const SizedBox(height: 10),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 8),
              decoration: BoxDecoration(
                color: C.warningSoft,
                borderRadius: BorderRadius.circular(10),
              ),
              child: Row(
                children: [
                  const Icon(
                    Icons.info_outline_rounded,
                    size: 14,
                    color: C.warning,
                  ),
                  const SizedBox(width: 6),
                  Expanded(
                    child: Text(
                      tr(context, 'يوجد عرض سعر بانتظار موافقتك'),
                      style: ts(9.5, color: C.warning, weight: FontWeight.w600),
                    ),
                  ),
                  Text(
                    tr(context, 'مراجعة'),
                    style: ts(9.5, weight: FontWeight.w700, color: C.warning),
                  ),
                ],
              ),
            ),
          ],
        ],
      ),
    );
  }
}
