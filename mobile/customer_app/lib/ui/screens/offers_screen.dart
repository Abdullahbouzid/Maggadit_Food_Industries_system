import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../models/catalog.dart';
import '../../state/app_dependencies.dart';
import '../router.dart';
import '../theme/app_theme.dart';
import '../widgets/cards.dart';
import '../widgets/chrome.dart';
import '../widgets/kit.dart';
import '../../l10n/l10n.dart';

class OffersScreen extends StatefulWidget {
  const OffersScreen({super.key, this.embedded = false});

  final bool embedded;

  @override
  State<OffersScreen> createState() => _OffersScreenState();
}

class _OffersScreenState extends State<OffersScreen> {
  late Future<List<Offer>> _future;

  @override
  void initState() {
    super.initState();
    _load();
  }

  void _load() {
    _future = context.read<AppDependencies>().catalogService.offers();
  }

  @override
  Widget build(BuildContext context) {
    final body = FutureView<List<Offer>>(
      future: _future,
      onRetry: () => setState(_load),
      emptyCheck: (data) => data.isEmpty,
      emptyIcon: Icons.local_offer_outlined,
      emptyTitle: tr(context, 'لا توجد عروض حالياً'),
      emptyMessage: tr(context, 'تابعنا، سنضيف عروضاً جديدة قريباً'),
      loadingLabel: tr(context, 'جاري تحميل العروض'),
      builder: (context, offers) => ListView.separated(
        padding: const EdgeInsets.only(bottom: 16),
        itemCount: offers.length,
        separatorBuilder: (_, __) => const SizedBox(height: 10),
        itemBuilder: (context, index) {
          final offer = offers[index];
          return OfferCard(
            offer: offer,
            onTap: () => Routes.product(offer.itemId),
          );
        },
      ),
    );

    if (widget.embedded) {
      return SafeArea(
        bottom: false,
        child: ListView(
          padding: const EdgeInsets.fromLTRB(16, 12, 16, 16),
          children: [
            Row(
              children: [
                Text(
                  tr(context, 'العروض'),
                  style: ts(18, weight: FontWeight.w700),
                ),
                const SizedBox(width: 8),
                Tag(
                  text: tr(context, 'حصري'),
                  color: Colors.white,
                  background: C.danger,
                ),
              ],
            ),
            const SizedBox(height: 4),
            Text(
              tr(context, 'خصومات على منتجات مختارة لفترة محدودة'),
              style: ts(10, color: C.muted),
            ),
            const SizedBox(height: 14),
            SizedBox(height: 520, child: body),
          ],
        ),
      );
    }

    return ScreenScaffold(
      title: tr(context, 'العروض'),
      subtitle: tr(context, 'خصومات على منتجات مختارة لفترة محدودة'),
      onRefresh: () async => setState(_load),
      child: body,
    );
  }
}
