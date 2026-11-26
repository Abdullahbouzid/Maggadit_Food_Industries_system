import 'dart:typed_data';

import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/api_client.dart';
import '../../core/file_opener.dart';
import '../../core/formatters.dart';
import '../../models/orders.dart';
import '../../state/app_dependencies.dart';
import '../theme/app_theme.dart';
import '../widgets/chrome.dart';
import '../widgets/kit.dart';
import '../../l10n/l10n.dart';

class OrderDetailScreen extends StatefulWidget {
  const OrderDetailScreen({super.key, required this.orderId});

  final int orderId;

  @override
  State<OrderDetailScreen> createState() => _OrderDetailScreenState();
}

class _OrderDetailScreenState extends State<OrderDetailScreen> {
  late Future<OrderDetail> _future;
  bool _busy = false;

  @override
  void initState() {
    super.initState();
    _load();
  }

  void _load() {
    _future = context.read<AppDependencies>().orderService.order(
      widget.orderId,
    );
  }

  Future<void> _respond(String response) async {
    setState(() => _busy = true);
    try {
      await context.read<AppDependencies>().orderService.respondToQuotation(
        widget.orderId,
        response,
      );
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(
            response == 'approved'
                ? tr(context, 'تم اعتماد عرض السعر')
                : tr(context, 'تم رفض عرض السعر'),
            style: ts(11, color: Colors.white),
          ),
        ),
      );
      setState(_load);
    } on ApiException catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text(e.message)));
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  Future<void> _download(
    Future<List<int>> Function() loader,
    String fileName,
  ) async {
    setState(() => _busy = true);
    try {
      final bytes = await loader();
      final ok = await saveAndOpenPdf(
        bytes: Uint8List.fromList(bytes),
        fileName: fileName,
      );
      if (!mounted) return;
      if (!ok) {
        ScaffoldMessenger.of(context).showSnackBar(
          SnackBar(content: Text(tr(context, 'تم حفظ الملف لكن تعذّر فتحه'))),
        );
      }
    } on ApiException catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text(e.message)));
    } finally {
      if (mounted) setState(() => _busy = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return ScreenScaffold(
      title: tr(context, 'تفاصيل الطلب'),
      subtitle: tr(context, 'متابعة حالة الطلب والفاتورة'),
      onRefresh: () async => setState(_load),
      child: FutureView<OrderDetail>(
        future: _future,
        onRetry: () => setState(_load),
        loadingLabel: tr(context, 'جاري تحميل الطلب'),
        builder: (context, order) => ListView(
          padding: const EdgeInsets.only(bottom: 20),
          children: [
            _HeaderCard(order: order),
            const SizedBox(height: 12),
            if (order.steps.isNotEmpty) _StepsCard(order: order),
            const SizedBox(height: 12),
            if (order.hasDeliveryInfo) _DeliveryCard(order: order),
            if (order.hasDeliveryInfo) const SizedBox(height: 12),
            _ItemsCard(order: order),
            if (order.quotation != null) ...[
              const SizedBox(height: 12),
              _QuotationCard(
                quotation: order.quotation!,
                canRespond: order.canRespond,
                busy: _busy,
                onAccept: () => _respond('approved'),
                onReject: () => _respond('rejected'),
                onDownload: () => _download(
                  () => context
                      .read<AppDependencies>()
                      .orderService
                      .quotationPdf(order.id),
                  pdfFileName('quotation', order.number),
                ),
              ),
            ],
            const SizedBox(height: 12),
            _DocumentsCard(
              orderId: order.id,
              hasInvoice: order.hasInvoice,
              invoiceNumber: order.invoiceNumber,
              busy: _busy,
              onOrderPdf: () => _download(
                () => context.read<AppDependencies>().orderService.orderPdf(
                  order.id,
                ),
                pdfFileName('order', order.number),
              ),
              onInvoicePdf: order.invoiceNumber == null
                  ? null
                  : () => _download(
                      () => context
                          .read<AppDependencies>()
                          .orderService
                          .invoicePdf(order.id),
                      pdfFileName('invoice', order.invoiceNumber!),
                    ),
            ),
          ],
        ),
      ),
    );
  }
}

class _HeaderCard extends StatelessWidget {
  const _HeaderCard({required this.order});

  final OrderDetail order;

  @override
  Widget build(BuildContext context) {
    final color = StatusPalette.color(order.statusClass);
    return NovaCard(
      child: Column(
        children: [
          Row(
            children: [
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      order.number,
                      style: ts(14, weight: FontWeight.w700),
                      textDirection: TextDirection.ltr,
                    ),
                    const SizedBox(height: 3),
                    Text(
                      tArgs(context, '{0} · {1} أصناف', [
                        Fmt.date(order.date),
                        order.itemsCount,
                      ]),
                      style: ts(9.5, color: C.muted),
                    ),
                  ],
                ),
              ),
              Tag(
                text: order.statusText,
                color: color,
                background: StatusPalette.background(order.statusClass),
              ),
            ],
          ),
          const SizedBox(height: 14),
          Row(
            children: [
              _Metric(
                label: tr(context, 'الإجمالي'),
                value: Fmt.price(order.total),
              ),
              _Divider(),
              _Metric(
                label: tr(context, 'التقدّم'),
                value: '${order.progress}%',
              ),
              _Divider(),
              _Metric(
                label: tr(context, 'الفرع'),
                value: order.branchName ?? '—',
              ),
            ],
          ),
        ],
      ),
    );
  }
}

class _Metric extends StatelessWidget {
  const _Metric({required this.label, required this.value});

  final String label;
  final String value;

  @override
  Widget build(BuildContext context) {
    return Expanded(
      child: Column(
        children: [
          Text(label, style: ts(8.5, color: C.muted)),
          const SizedBox(height: 4),
          Text(
            value,
            maxLines: 1,
            overflow: TextOverflow.ellipsis,
            textAlign: TextAlign.center,
            style: ts(11, weight: FontWeight.w700),
          ),
        ],
      ),
    );
  }
}

class _Divider extends StatelessWidget {
  const _Divider();

  @override
  Widget build(BuildContext context) =>
      Container(width: 1, height: 26, color: C.border);
}

class _StepsCard extends StatelessWidget {
  const _StepsCard({required this.order});

  final OrderDetail order;

  @override
  Widget build(BuildContext context) {
    final currentIndex = order.steps
        .toList()
        .asMap()
        .entries
        .where((entry) => entry.value.reached)
        .map((entry) => entry.key)
        .fold<int>(-1, (max, index) => index > max ? index : max);

    return NovaCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              Text(
                tr(context, 'مراحل الطلب'),
                style: ts(13, weight: FontWeight.w700),
              ),
              const Spacer(),
              Text(
                tArgs(context, '{0} من {1}', [
                  (currentIndex + 1).clamp(0, order.steps.length),
                  order.steps.length,
                ]),
                style: ts(9.5, color: C.muted),
              ),
            ],
          ),
          const SizedBox(height: 12),
          for (var i = order.steps.length - 1; i >= 0; i--)
            _StepRow(
              step: order.steps[i],
              isLast: i == 0,
              current: i == currentIndex,
            ),
        ],
      ),
    );
  }
}

class _StepRow extends StatelessWidget {
  const _StepRow({
    required this.step,
    required this.isLast,
    required this.current,
  });

  final OrderStep step;
  final bool isLast;
  final bool current;

  @override
  Widget build(BuildContext context) {
    final reached = step.reached;
    final color = reached ? StatusPalette.color(step.color) : C.border;
    return IntrinsicHeight(
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 26,
            child: Column(
              children: [
                Container(
                  width: 20,
                  height: 20,
                  decoration: BoxDecoration(
                    color: reached
                        ? StatusPalette.background(step.color)
                        : const Color(0xFFF1F5F9),
                    shape: BoxShape.circle,
                    border: Border.all(color: color, width: 1.4),
                  ),
                  child: Icon(
                    reached ? Icons.check_rounded : Icons.circle_outlined,
                    size: 11,
                    color: color,
                  ),
                ),
                if (!isLast)
                  Expanded(
                    child: Container(
                      width: 2,
                      color: reached ? color : C.border,
                    ),
                  ),
              ],
            ),
          ),
          const SizedBox(width: 6),
          Expanded(
            child: Padding(
              padding: EdgeInsets.only(bottom: isLast ? 0 : 14, top: 1),
              child: Row(
                children: [
                  Flexible(
                    child: Text(
                      step.label,
                      style: ts(
                        11,
                        weight: current ? FontWeight.w700 : FontWeight.w500,
                        color: reached ? C.ink : C.muted,
                      ),
                    ),
                  ),
                  if (current) ...[
                    const SizedBox(width: 6),
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 6,
                        vertical: 2,
                      ),
                      decoration: BoxDecoration(
                        color: C.purpleSoft,
                        borderRadius: BorderRadius.circular(20),
                      ),
                      child: Text(
                        tr(context, 'الآن'),
                        style: ts(
                          7.5,
                          weight: FontWeight.w700,
                          color: C.primaryDark,
                        ),
                      ),
                    ),
                  ],
                ],
              ),
            ),
          ),
        ],
      ),
    );
  }
}

class _DeliveryCard extends StatelessWidget {
  const _DeliveryCard({required this.order});

  final OrderDetail order;

  @override
  Widget build(BuildContext context) {
    return NovaCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Icon(
                Icons.local_shipping_rounded,
                size: 16,
                color: C.primaryDark,
              ),
              const SizedBox(width: 6),
              Text(
                tr(context, 'تفاصيل التوصيل'),
                style: ts(12.5, weight: FontWeight.w700),
              ),
            ],
          ),
          const SizedBox(height: 10),
          if (order.deliveryAddress != null)
            _Row(label: tr(context, 'العنوان'), value: order.deliveryAddress!),
          if (order.deliveryPhone != null)
            _Row(
              label: tr(context, 'الهاتف'),
              value: order.deliveryPhone!,
              ltr: true,
            ),
          if (order.customerName != null)
            _Row(label: tr(context, 'العميل'), value: order.customerName!),
        ],
      ),
    );
  }
}

class _Row extends StatelessWidget {
  const _Row({required this.label, required this.value, this.ltr = false});

  final String label;
  final String value;
  final bool ltr;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 6),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          SizedBox(
            width: 64,
            child: Text(label, style: ts(9.5, color: C.muted)),
          ),
          Expanded(
            child: Text(
              value,
              textDirection: ltr ? TextDirection.ltr : null,
              textAlign: ltr ? TextAlign.right : TextAlign.start,
              style: ts(10.5, color: C.inkSoft),
            ),
          ),
        ],
      ),
    );
  }
}

class _ItemsCard extends StatelessWidget {
  const _ItemsCard({required this.order});

  final OrderDetail order;

  @override
  Widget build(BuildContext context) {
    if (order.items.isEmpty) return const SizedBox.shrink();
    return NovaCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            tr(context, 'أصناف الطلب'),
            style: ts(12.5, weight: FontWeight.w700),
          ),
          const SizedBox(height: 10),
          for (final line in order.items)
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
                    style: ts(
                      11,
                      weight: FontWeight.w700,
                      color: C.primaryDark,
                    ),
                  ),
                ],
              ),
            ),
        ],
      ),
    );
  }
}

class _QuotationCard extends StatelessWidget {
  const _QuotationCard({
    required this.quotation,
    required this.canRespond,
    required this.busy,
    required this.onAccept,
    required this.onReject,
    required this.onDownload,
  });

  final Quotation quotation;
  final bool canRespond;
  final bool busy;
  final VoidCallback onAccept;
  final VoidCallback onReject;
  final VoidCallback onDownload;

  @override
  Widget build(BuildContext context) {
    return NovaCard(
      color: C.purpleSoft,
      border: false,
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            children: [
              const Icon(
                Icons.description_rounded,
                size: 16,
                color: C.primaryDeep,
              ),
              const SizedBox(width: 6),
              Text(
                tArgs(context, 'عرض السعر {0}', [quotation.number]),
                style: ts(12.5, weight: FontWeight.w700, color: C.primaryDeep),
              ),
              const Spacer(),
              Text(
                Fmt.price(quotation.total),
                style: ts(13, weight: FontWeight.w700, color: C.primaryDeep),
              ),
            ],
          ),
          if (quotation.validUntil != null) ...[
            const SizedBox(height: 6),
            Text(
              tArgs(context, 'صالح حتى {0}', [Fmt.date(quotation.validUntil)]),
              style: ts(9, color: C.primaryDark),
            ),
          ],
          const SizedBox(height: 10),
          for (final line in quotation.items.take(4))
            Padding(
              padding: const EdgeInsets.only(bottom: 4),
              child: Row(
                children: [
                  Expanded(
                    child: Text(
                      '${line.name} × ${Fmt.quantity(line.quantity)}',
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                      style: ts(10, color: C.inkSoft),
                    ),
                  ),
                  Text(
                    Fmt.price(line.lineTotal),
                    style: ts(10, color: C.inkSoft),
                  ),
                ],
              ),
            ),
          const SizedBox(height: 8),
          Row(
            children: [
              Expanded(
                child: GhostButton(
                  label: tr(context, 'تحميل PDF'),
                  icon: Icons.picture_as_pdf_rounded,
                  color: C.primaryDeep,
                  onPressed: busy ? null : onDownload,
                ),
              ),
              if (canRespond) ...[
                const SizedBox(width: 8),
                Expanded(
                  child: GhostButton(
                    label: tr(context, 'رفض'),
                    color: C.danger,
                    onPressed: busy ? null : onReject,
                  ),
                ),
                const SizedBox(width: 8),
                Expanded(
                  child: PrimaryButton(
                    label: tr(context, 'موافقة'),
                    height: 44,
                    loading: busy,
                    color: C.success,
                    onPressed: onAccept,
                  ),
                ),
              ],
            ],
          ),
        ],
      ),
    );
  }
}

class _DocumentsCard extends StatelessWidget {
  const _DocumentsCard({
    required this.orderId,
    required this.hasInvoice,
    required this.invoiceNumber,
    required this.busy,
    required this.onOrderPdf,
    required this.onInvoicePdf,
  });

  final int orderId;
  final bool hasInvoice;
  final String? invoiceNumber;
  final bool busy;
  final VoidCallback onOrderPdf;
  final VoidCallback? onInvoicePdf;

  @override
  Widget build(BuildContext context) {
    return NovaCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(
            tr(context, 'المستندات'),
            style: ts(12.5, weight: FontWeight.w700),
          ),
          const SizedBox(height: 10),
          Row(
            children: [
              Expanded(
                child: GhostButton(
                  label: tr(context, 'طلب PDF'),
                  icon: Icons.receipt_long_rounded,
                  onPressed: busy ? null : onOrderPdf,
                ),
              ),
              if (hasInvoice && invoiceNumber != null) ...[
                const SizedBox(width: 10),
                Expanded(
                  child: GhostButton(
                    label: tArgs(context, 'فاتورة {0}', [invoiceNumber]),
                    icon: Icons.picture_as_pdf_rounded,
                    onPressed: busy ? null : onInvoicePdf,
                  ),
                ),
              ],
            ],
          ),
        ],
      ),
    );
  }
}
