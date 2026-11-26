import 'dart:typed_data';

import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/file_opener.dart';
import '../../core/formatters.dart';
import '../../models/account.dart';
import '../../state/app_dependencies.dart';
import '../theme/app_theme.dart';
import '../widgets/chrome.dart';
import '../widgets/kit.dart';
import '../../l10n/l10n.dart';

class InvoiceDetailScreen extends StatefulWidget {
  const InvoiceDetailScreen({super.key, required this.invoiceId});

  final int invoiceId;

  @override
  State<InvoiceDetailScreen> createState() => _InvoiceDetailScreenState();
}

class _InvoiceDetailScreenState extends State<InvoiceDetailScreen> {
  late Future<InvoiceDetail> _future;
  bool _busy = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  void _load() {
    _future = context.read<AppDependencies>().orderService.invoice(
      widget.invoiceId,
    );
  }

  Future<void> _download(InvoiceSummary invoice) async {
    setState(() => _busy = true);
    try {
      final bytes = await context
          .read<AppDependencies>()
          .orderService
          .invoicePdf(widget.invoiceId);
      await saveAndOpenPdf(
        bytes: Uint8List.fromList(bytes),
        fileName: pdfFileName('invoice', invoice.number),
      );
    } catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text(e.toString())));
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return ScreenScaffold(
      title: tr(context, 'تفاصيل الفاتورة'),
      onRefresh: () async => setState(_load),
      child: FutureView<InvoiceDetail>(
        future: _future,
        onRetry: () => setState(_load),
        loadingLabel: tr(context, 'جاري تحميل الفاتورة'),
        builder: (context, detail) {
          final invoice = detail.invoice;
          return ListView(
            padding: const EdgeInsets.only(bottom: 20),
            children: [
              NovaCard(
                child: Column(
                  children: [
                    Row(
                      children: [
                        Expanded(
                          child: Column(
                            crossAxisAlignment: CrossAxisAlignment.start,
                            children: [
                              Text(
                                invoice.number,
                                style: ts(14, weight: FontWeight.w700),
                                textDirection: TextDirection.ltr,
                              ),
                              const SizedBox(height: 3),
                              Text(
                                Fmt.date(invoice.date),
                                style: ts(9.5, color: C.muted),
                              ),
                            ],
                          ),
                        ),
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.end,
                          children: [
                            Text(
                              tr(context, 'الإجمالي'),
                              style: ts(8.5, color: C.muted),
                            ),
                            Text(
                              Fmt.price(invoice.total),
                              style: ts(
                                17,
                                weight: FontWeight.w700,
                                color: C.primaryDark,
                              ),
                            ),
                          ],
                        ),
                      ],
                    ),
                    if (invoice.notes != null) ...[
                      const SizedBox(height: 10),
                      Container(
                        width: double.infinity,
                        padding: const EdgeInsets.all(10),
                        decoration: BoxDecoration(
                          color: C.warningSoft,
                          borderRadius: BorderRadius.circular(10),
                        ),
                        child: Text(
                          invoice.notes!,
                          style: ts(9.5, color: C.inkSoft, height: 1.6),
                        ),
                      ),
                    ],
                  ],
                ),
              ),
              const SizedBox(height: 12),
              NovaCard(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      tArgs(context, 'الأصناف ({0})', [detail.items.length]),
                      style: ts(12.5, weight: FontWeight.w700),
                    ),
                    const SizedBox(height: 10),
                    if (detail.items.isEmpty)
                      Text(
                        tr(context, 'لا توجد أصناف مسجلة'),
                        style: ts(10, color: C.muted),
                      )
                    else
                      for (final line in detail.items)
                        Padding(
                          padding: const EdgeInsets.only(bottom: 10),
                          child: Row(
                            children: [
                              Expanded(
                                child: Column(
                                  crossAxisAlignment: CrossAxisAlignment.start,
                                  children: [
                                    Text(
                                      line.name,
                                      maxLines: 1,
                                      overflow: TextOverflow.ellipsis,
                                      style: ts(11, weight: FontWeight.w600),
                                    ),
                                    Text(
                                      '${Fmt.quantity(line.quantity)} × ${Fmt.price(line.unitPrice)}',
                                      style: ts(9, color: C.muted),
                                    ),
                                  ],
                                ),
                              ),
                              Text(
                                Fmt.price(line.lineTotal),
                                style: ts(11, weight: FontWeight.w700),
                              ),
                            ],
                          ),
                        ),
                  ],
                ),
              ),
              const SizedBox(height: 16),
              PrimaryButton(
                label: tr(context, 'تحميل الفاتورة PDF'),
                icon: Icons.picture_as_pdf_rounded,
                loading: _busy,
                onPressed: () => _download(invoice),
              ),
            ],
          );
        },
      ),
    );
  }
}
