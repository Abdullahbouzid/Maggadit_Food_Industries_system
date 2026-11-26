import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/formatters.dart';
import '../../models/account.dart';
import '../../state/app_dependencies.dart';
import '../router.dart';
import '../theme/app_theme.dart';
import '../widgets/chrome.dart';
import '../widgets/kit.dart';

class WalletScreen extends StatefulWidget {
  const WalletScreen({super.key});

  @override
  State<WalletScreen> createState() => _WalletScreenState();
}

class _WalletScreenState extends State<WalletScreen> {
  late Future<WalletStatement> _future;

  @override
  void initState() {
    super.initState();
    _load();
  }

  void _load() {
    _future = context.read<AppDependencies>().orderService.financialStatement();
  }

  @override
  Widget build(BuildContext context) {
    return ScreenScaffold(
      title: 'كشف الحساب المالي',
      subtitle: 'الرصيد والحركات والفواتير',
      onRefresh: () async => setState(_load),
      actions: [
        IconButton(
          onPressed: () => Routes.invoices(),
          icon: const Icon(Icons.receipt_rounded, size: 19, color: C.primaryDark),
          tooltip: 'الفواتير',
        ),
      ],
      child: FutureView<WalletStatement>(
        future: _future,
        onRetry: () => setState(_load),
        loadingLabel: 'جاري تحميل كشف الحساب',
        builder: (context, statement) => RefreshIndicator(
          color: C.primary,
          onRefresh: () async => setState(_load),
          child: ListView(
            padding: const EdgeInsets.only(bottom: 20),
            children: [
              Row(
                children: [
                  Expanded(
                    child: _SummaryTile(
                      label: 'إجمالي الإيداعات',
                      value: Fmt.price(statement.deposits),
                      color: C.success,
                      background: C.successSoft,
                      icon: Icons.south_west_rounded,
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: _SummaryTile(
                      label: 'إجمالي الصرف',
                      value: Fmt.price(statement.debits),
                      color: C.danger,
                      background: C.dangerSoft,
                      icon: Icons.north_east_rounded,
                    ),
                  ),
                ],
              ),
              const SizedBox(height: 12),
              Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  gradient: const LinearGradient(colors: [C.primaryDeep, C.primary]),
                  borderRadius: BorderRadius.circular(18),
                  boxShadow: Sh.card,
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(
                      children: [
                        Text('الرصيد الحالي',
                            style: ts(10.5, color: Colors.white.withValues(alpha: 0.85))),
                        const Spacer(),
                        Container(
                          padding: const EdgeInsets.symmetric(horizontal: 8, vertical: 3),
                          decoration: BoxDecoration(
                            color: Colors.white.withValues(alpha: 0.18),
                            borderRadius: BorderRadius.circular(20),
                          ),
                          child: Text('محفظة نشطة',
                              style: ts(8.5, color: Colors.white, weight: FontWeight.w600)),
                        ),
                      ],
                    ),
                    const SizedBox(height: 10),
                    Row(
                      crossAxisAlignment: CrossAxisAlignment.end,
                      children: [
                        Text(Fmt.money(statement.balance),
                            style: ts(26, weight: FontWeight.w700, color: Colors.white)),
                        const SizedBox(width: 6),
                        Padding(
                          padding: const EdgeInsets.only(bottom: 5),
                          child: Text('ر.س',
                              style: ts(11, color: Colors.white.withValues(alpha: 0.9))),
                        ),
                      ],
                    ),
                    const SizedBox(height: 12),
                    Row(
                      children: [
                        _MiniStat(label: 'مفوتر', value: Fmt.price(statement.invoiced)),
                        const SizedBox(width: 18),
                        _MiniStat(label: 'مدفوع', value: Fmt.price(statement.paid)),
                      ],
                    ),
                  ],
                ),
              ),
              const SizedBox(height: 12),
              NovaCard(
                onTap: () => Routes.invoices(),
                child: Row(
                  children: [
                    Container(
                      width: 38,
                      height: 38,
                      decoration: BoxDecoration(
                        color: C.purpleSoft,
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: const Icon(Icons.receipt_long_rounded,
                          size: 18, color: C.primaryDark),
                    ),
                    const SizedBox(width: 10),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text('الفواتير', style: ts(12.5, weight: FontWeight.w700)),
                          Text('عرض كل الفواتير وتحميلها PDF',
                              style: ts(9, color: C.muted)),
                        ],
                      ),
                    ),
                    const Icon(Icons.chevron_left_rounded, size: 20, color: C.muted),
                  ],
                ),
              ),
              const SizedBox(height: 16),
              const SectionTitle(title: 'الحركات', padding: EdgeInsets.zero),
              if (statement.transactions.isEmpty)
                const EmptyState(
                  icon: Icons.swap_vert_rounded,
                  title: 'لا توجد حركات',
                  message: 'ستظهر الإيداعات والمشتريات هنا',
                )
              else
                for (final transaction in statement.transactions)
                  Padding(
                    padding: const EdgeInsets.only(bottom: 10),
                    child: _TransactionTile(transaction: transaction),
                  ),
            ],
          ),
        ),
      ),
    );
  }
}

class _SummaryTile extends StatelessWidget {
  const _SummaryTile({
    required this.label,
    required this.value,
    required this.color,
    required this.background,
    required this.icon,
  });

  final String label;
  final String value;
  final Color color;
  final Color background;
  final IconData icon;

  @override
  Widget build(BuildContext context) {
    return NovaCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Text(label, maxLines: 1, overflow: TextOverflow.ellipsis,
                  style: ts(9.5, color: C.muted)),
              const Spacer(),
              Container(
                width: 26,
                height: 26,
                decoration: BoxDecoration(color: background, borderRadius: BorderRadius.circular(9)),
                child: Icon(icon, size: 13, color: color),
              ),
            ],
          ),
          const SizedBox(height: 8),
          Text(value, style: ts(14, weight: FontWeight.w700, color: color)),
        ],
      ),
    );
  }
}

class _MiniStat extends StatelessWidget {
  const _MiniStat({required this.label, required this.value});

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(label, style: ts(8, color: Colors.white.withValues(alpha: 0.7))),
        Text(value, style: ts(10, weight: FontWeight.w600, color: Colors.white)),
      ],
    );
  }
}

class _TransactionTile extends StatelessWidget {
  const _TransactionTile({required this.transaction});

  final WalletTransaction transaction;

  @override
  Widget build(BuildContext context) {
    final color = transaction.isDeposit ? C.success : C.danger;
    final background = transaction.isDeposit ? C.successSoft : C.dangerSoft;

    return NovaCard(
      padding: const EdgeInsets.all(12),
      child: Row(
        children: [
          Container(
            width: 34,
            height: 34,
            decoration: BoxDecoration(color: background, borderRadius: BorderRadius.circular(11)),
            child: Icon(
              transaction.isDeposit ? Icons.south_west_rounded : Icons.north_east_rounded,
              size: 16,
              color: color,
            ),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(transaction.label,
                    maxLines: 1, overflow: TextOverflow.ellipsis,
                    style: ts(11.5, weight: FontWeight.w600)),
                const SizedBox(height: 2),
                Row(
                  children: [
                    Text(Fmt.date(transaction.date), style: ts(8.5, color: C.muted)),
                    if (transaction.reference != null) ...[
                      const SizedBox(width: 6),
                      Flexible(
                        child: Text(transaction.reference!,
                            maxLines: 1, overflow: TextOverflow.ellipsis,
                            style: ts(8.5, color: C.muted),
                            textDirection: TextDirection.ltr),
                      ),
                    ],
                  ],
                ),
              ],
            ),
          ),
          Row(
            children: [
              Text(
                transaction.isDeposit ? '+' : '-',
                style: ts(11, weight: FontWeight.w700, color: color),
              ),
              Text(Fmt.money(transaction.amount),
                  style: ts(11.5, weight: FontWeight.w700, color: color)),
              const SizedBox(width: 3),
              Text('ر.س', style: ts(8.5, color: color)),
            ],
          ),
        ],
      ),
    );
  }
}
