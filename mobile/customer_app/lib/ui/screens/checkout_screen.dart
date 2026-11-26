import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/api_client.dart';
import '../../core/formatters.dart';
import '../../models/account.dart';
import '../../state/app_dependencies.dart';
import '../../state/auth_controller.dart';
import '../../state/cart_controller.dart';
import '../router.dart';
import '../theme/app_theme.dart';
import '../widgets/chrome.dart';
import '../widgets/kit.dart';
import '../../l10n/l10n.dart';

class CheckoutScreen extends StatefulWidget {
  const CheckoutScreen({super.key});

  @override
  State<CheckoutScreen> createState() => _CheckoutScreenState();
}

class _CheckoutScreenState extends State<CheckoutScreen> {
  final _formKey = GlobalKey<FormState>();
  final _address = TextEditingController();
  final _phone = TextEditingController();
  final _notes = TextEditingController();

  DateTime? _scheduledDate;
  Branch? _branch;
  List<Branch> _branches = const [];
  bool _loadingBranches = false;
  bool _submitting = false;

  @override
  void initState() {
    super.initState();
    final customer = context.read<AuthController>().customer;
    if (customer != null) _phone.text = '';
    _loadBranches();
  }

  @override
  void dispose() {
    _address.dispose();
    _phone.dispose();
    _notes.dispose();
    super.dispose();
  }

  Future<void> _loadBranches() async {
    setState(() => _loadingBranches = true);
    try {
      final branches = await context
          .read<AppDependencies>()
          .catalogService
          .branches();
      if (!mounted) return;
      setState(() {
        _branches = branches;
        _branch = branches.where((b) => b.isActive).isEmpty
            ? null
            : branches.firstWhere((b) => b.isActive);
      });
    } on ApiException {
      // Branches are optional; delivery details still work.
    } finally {
      if (mounted) setState(() => _loadingBranches = false);
    }
  }

  Future<void> _pickDate() async {
    final now = DateTime.now();
    final picked = await showDatePicker(
      context: context,
      initialDate: _scheduledDate ?? now.add(const Duration(days: 1)),
      firstDate: now,
      lastDate: now.add(const Duration(days: 30)),
    );
    if (picked != null) setState(() => _scheduledDate = picked);
  }

  Future<void> _submit() async {
    if (!(_formKey.currentState?.validate() ?? false)) return;
    final cart = context.read<CartController>();
    if (cart.isEmpty) {
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text(tr(context, 'السلة فارغة'))));
      return;
    }

    setState(() => _submitting = true);
    try {
      final result = await context
          .read<AppDependencies>()
          .orderService
          .checkout(
            items: cart.toCheckoutItems(),
            deliveryAddress: _address.text.trim(),
            deliveryPhone: _phone.text.trim(),
            scheduledDeliveryDate: _scheduledDate == null
                ? null
                : Fmt.apiDate(_scheduledDate!),
            branchId: _branch?.id,
            notes: _notes.text.trim(),
          );

      if (!mounted) return;
      cart.clear();
      await _showSuccess(
        orderNumber:
            (result['order_number'] ?? result['order']?['order_number'] ?? '')
                .toString(),
        rejected: (result['rejected'] as List?)?.length ?? 0,
      );
    } on ApiException catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text(e.message)));
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  Future<void> _showSuccess({
    required String orderNumber,
    required int rejected,
  }) async {
    await showDialog<void>(
      context: context,
      barrierDismissible: false,
      builder: (dialogContext) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 64,
              height: 64,
              decoration: const BoxDecoration(
                color: C.successSoft,
                shape: BoxShape.circle,
              ),
              child: const Icon(
                Icons.check_rounded,
                color: C.success,
                size: 32,
              ),
            ),
            const SizedBox(height: 14),
            Text(
              tr(context, 'تم إرسال الطلب'),
              style: ts(15, weight: FontWeight.w700),
            ),
            const SizedBox(height: 6),
            if (orderNumber.isNotEmpty)
              Text(
                tArgs(context, 'رقم الطلب: {0}', [orderNumber]),
                style: ts(11, color: C.inkSoft),
                textDirection: TextDirection.ltr,
              ),
            if (rejected > 0) ...[
              const SizedBox(height: 8),
              Text(
                tArgs(context, '{0} صنف لم يتوفر وتم استبعاده', [rejected]),
                textAlign: TextAlign.center,
                style: ts(10.5, color: C.warning),
              ),
            ],
            const SizedBox(height: 18),
            Row(
              children: [
                Expanded(
                  child: GhostButton(
                    label: tr(context, 'الرئيسية'),
                    onPressed: () {
                      Navigator.of(dialogContext).pop();
                      Routes.home();
                    },
                  ),
                ),
                const SizedBox(width: 10),
                Expanded(
                  child: PrimaryButton(
                    label: tr(context, 'تتبّع الطلب'),
                    height: 44,
                    onPressed: () {
                      Navigator.of(dialogContext).pop();
                      Routes.orders();
                    },
                  ),
                ),
              ],
            ),
          ],
        ),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    final cart = context.watch<CartController>();

    return ScreenScaffold(
      title: tr(context, 'إتمام الطلب'),
      subtitle: tArgs(context, '{0} أصناف · {1}', [
        cart.count,
        Fmt.price(cart.subtotal),
      ]),
      child: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.only(bottom: 16),
          children: [
            NovaCard(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    tr(context, 'تفاصيل التوصيل'),
                    style: ts(13, weight: FontWeight.w700),
                  ),
                  const SizedBox(height: 12),
                  Text(tr(context, 'الفرع'), style: ts(10, color: C.muted)),
                  const SizedBox(height: 6),
                  DropdownButtonFormField<Branch>(
                    initialValue: _branch,
                    isExpanded: true,
                    decoration: InputDecoration(
                      hintText: _loadingBranches
                          ? tr(context, 'جاري التحميل...')
                          : tr(context, 'اختر الفرع'),
                    ),
                    items: _branches
                        .map(
                          (branch) => DropdownMenuItem(
                            value: branch,
                            child: Text(
                              branch.name,
                              style: ts(11.5),
                              overflow: TextOverflow.ellipsis,
                            ),
                          ),
                        )
                        .toList(),
                    onChanged: (value) => setState(() => _branch = value),
                  ),
                  const SizedBox(height: 12),
                  Text(
                    tr(context, 'عنوان التوصيل'),
                    style: ts(10, color: C.muted),
                  ),
                  const SizedBox(height: 6),
                  TextFormField(
                    controller: _address,
                    maxLines: 2,
                    style: ts(12),
                    decoration: InputDecoration(
                      hintText: tr(context, 'الشارع، الحي، أقرب علامة'),
                    ),
                    validator: (value) =>
                        (value == null || value.trim().isEmpty)
                        ? tr(context, 'العنوان مطلوب')
                        : null,
                  ),
                  const SizedBox(height: 12),
                  Text(
                    tr(context, 'رقم الهاتف'),
                    style: ts(10, color: C.muted),
                  ),
                  const SizedBox(height: 6),
                  TextFormField(
                    controller: _phone,
                    keyboardType: TextInputType.phone,
                    textDirection: TextDirection.ltr,
                    style: ts(12),
                    decoration: const InputDecoration(hintText: '09xxxxxxxx'),
                    validator: (value) =>
                        (value == null || value.trim().length < 9)
                        ? tr(context, 'رقم غير صحيح')
                        : null,
                  ),
                  const SizedBox(height: 12),
                  Text(
                    tr(context, 'موعد التوصيل (اختياري)'),
                    style: ts(10, color: C.muted),
                  ),
                  const SizedBox(height: 6),
                  GestureDetector(
                    onTap: _pickDate,
                    child: Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 14,
                        vertical: 14,
                      ),
                      decoration: BoxDecoration(
                        color: const Color(0xFFF8FAFC),
                        borderRadius: BorderRadius.circular(14),
                        border: Border.all(color: C.border),
                      ),
                      child: Row(
                        children: [
                          const Icon(
                            Icons.calendar_month_rounded,
                            size: 17,
                            color: C.muted,
                          ),
                          const SizedBox(width: 8),
                          Text(
                            _scheduledDate == null
                                ? tr(context, 'اختر التاريخ')
                                : Fmt.date(_scheduledDate),
                            style: ts(
                              11.5,
                              color: _scheduledDate == null ? C.muted : C.ink,
                            ),
                          ),
                          const Spacer(),
                          if (_scheduledDate != null)
                            GestureDetector(
                              onTap: () =>
                                  setState(() => _scheduledDate = null),
                              child: const Icon(
                                Icons.close_rounded,
                                size: 15,
                                color: C.muted,
                              ),
                            ),
                        ],
                      ),
                    ),
                  ),
                  const SizedBox(height: 12),
                  Text(tr(context, 'ملاحظات'), style: ts(10, color: C.muted)),
                  const SizedBox(height: 6),
                  TextFormField(
                    controller: _notes,
                    maxLines: 2,
                    style: ts(12),
                    decoration: InputDecoration(
                      hintText: tr(context, 'أي تعليمات إضافية للطلب'),
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 12),
            NovaCard(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    tr(context, 'ملخص الطلب'),
                    style: ts(13, weight: FontWeight.w700),
                  ),
                  const SizedBox(height: 10),
                  for (final item in cart.items)
                    Padding(
                      padding: const EdgeInsets.only(bottom: 8),
                      child: Row(
                        children: [
                          Expanded(
                            child: Text(
                              '${item.name} × ${Fmt.quantity(item.quantity)}',
                              maxLines: 1,
                              overflow: TextOverflow.ellipsis,
                              style: ts(10.5, color: C.inkSoft),
                            ),
                          ),
                          Text(
                            Fmt.price(item.lineTotal),
                            style: ts(10.5, weight: FontWeight.w600),
                          ),
                        ],
                      ),
                    ),
                  const Divider(height: 18),
                  Row(
                    children: [
                      Text(
                        tr(context, 'الإجمالي التقديري'),
                        style: ts(12, weight: FontWeight.w700),
                      ),
                      const Spacer(),
                      Text(
                        Fmt.price(cart.subtotal),
                        style: ts(
                          15,
                          weight: FontWeight.w700,
                          color: C.primaryDark,
                        ),
                      ),
                    ],
                  ),
                  const SizedBox(height: 4),
                  Text(
                    tr(
                      context,
                      'الضرائب والخصومات تُحسب من الخادم عند تأكيد الطلب',
                    ),
                    style: ts(8.5, color: C.muted),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 16),
            PrimaryButton(
              label: tr(context, 'تأكيد الطلب'),
              icon: Icons.check_circle_outline_rounded,
              loading: _submitting,
              onPressed: _submit,
            ),
          ],
        ),
      ),
    );
  }
}
