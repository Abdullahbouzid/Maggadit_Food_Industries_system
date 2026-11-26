import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../models/account.dart';
import '../../state/app_dependencies.dart';
import '../theme/app_theme.dart';
import '../widgets/chrome.dart';
import '../widgets/kit.dart';
import '../../l10n/l10n.dart';

class FaqScreen extends StatefulWidget {
  const FaqScreen({super.key});

  @override
  State<FaqScreen> createState() => _FaqScreenState();
}

class _FaqScreenState extends State<FaqScreen> {
  late Future<List<FaqItem>> _future;

  @override
  void initState() {
    super.initState();
    _load();
  }

  void _load() {
    _future = context.read<AppDependencies>().catalogService.faq();
  }

  @override
  Widget build(BuildContext context) {
    return ScreenScaffold(
      title: tr(context, 'الأسئلة الشائعة'),
      subtitle: tr(context, 'أجوبة سريعة لأكثر ما يسأل عنه العملاء'),
      onRefresh: () async => setState(_load),
      child: FutureView<List<FaqItem>>(
        future: _future,
        onRetry: () => setState(_load),
        emptyCheck: (data) => data.isEmpty,
        emptyIcon: Icons.help_outline_rounded,
        emptyTitle: tr(context, 'لا توجد أسئلة منشورة'),
        loadingLabel: tr(context, 'جاري تحميل الأسئلة'),
        builder: (context, faqs) => ListView.separated(
          padding: const EdgeInsets.only(bottom: 16),
          itemCount: faqs.length,
          separatorBuilder: (_, __) => const SizedBox(height: 10),
          itemBuilder: (context, index) => NovaCard(
            padding: EdgeInsets.zero,
            child: Theme(
              data: Theme.of(
                context,
              ).copyWith(dividerColor: Colors.transparent),
              child: ExpansionTile(
                tilePadding: const EdgeInsets.symmetric(
                  horizontal: 14,
                  vertical: 2,
                ),
                childrenPadding: const EdgeInsets.fromLTRB(14, 0, 14, 14),
                iconColor: C.primaryDark,
                collapsedIconColor: C.muted,
                title: Text(
                  faqs[index].question,
                  style: ts(11.5, weight: FontWeight.w700, height: 1.6),
                ),
                children: [
                  Text(
                    faqs[index].answer,
                    style: ts(10.5, color: C.inkSoft, height: 1.85),
                  ),
                ],
              ),
            ),
          ),
        ),
      ),
    );
  }
}
