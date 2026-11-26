import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../state/app_dependencies.dart';
import '../theme/app_theme.dart';
import '../widgets/chrome.dart';
import '../widgets/kit.dart';
import '../../l10n/l10n.dart';

class PrivacyScreen extends StatefulWidget {
  const PrivacyScreen({super.key});

  @override
  State<PrivacyScreen> createState() => _PrivacyScreenState();
}

class _PrivacyScreenState extends State<PrivacyScreen> {
  late Future<String> _future;

  @override
  void initState() {
    super.initState();
    _load();
  }

  void _load() {
    _future = context.read<AppDependencies>().catalogService.privacyPolicy();
  }

  @override
  Widget build(BuildContext context) {
    return ScreenScaffold(
      title: tr(context, 'سياسة الخصوصية'),
      subtitle: tr(context, 'كيف نتعامل مع بياناتك'),
      onRefresh: () async => setState(_load),
      child: FutureView<String>(
        future: _future,
        onRetry: () => setState(_load),
        emptyCheck: (data) => data.trim().isEmpty,
        emptyTitle: tr(context, 'لم تُنشر سياسة الخصوصية بعد'),
        loadingLabel: tr(context, 'جاري التحميل'),
        builder: (context, content) => SingleChildScrollView(
          child: NovaCard(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  children: [
                    const Icon(
                      Icons.shield_rounded,
                      size: 18,
                      color: C.primaryDark,
                    ),
                    const SizedBox(width: 8),
                    Text(
                      tr(context, 'التزامنا بخصوصيتك'),
                      style: ts(13, weight: FontWeight.w700),
                    ),
                  ],
                ),
                const SizedBox(height: 12),
                Text(
                  content.replaceAll(RegExp(r'<[^>]*>'), '\n').trim(),
                  style: ts(11, color: C.inkSoft, height: 1.95),
                ),
              ],
            ),
          ),
        ),
      ),
    );
  }
}
