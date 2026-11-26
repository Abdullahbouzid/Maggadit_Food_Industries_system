import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../models/account.dart';
import '../../state/app_dependencies.dart';
import '../theme/app_theme.dart';
import '../widgets/chrome.dart';
import '../widgets/kit.dart';

class AboutScreen extends StatefulWidget {
  const AboutScreen({super.key});

  @override
  State<AboutScreen> createState() => _AboutScreenState();
}

class _AboutScreenState extends State<AboutScreen> {
  late Future<AboutContent> _future;

  @override
  void initState() {
    super.initState();
    _load();
  }

  void _load() {
    _future = context.read<AppDependencies>().catalogService.aboutUs();
  }

  @override
  Widget build(BuildContext context) {
    return ScreenScaffold(
      title: 'من نحن',
      subtitle: 'ماغديت للصناعات الغذائية',
      onRefresh: () async => setState(_load),
      child: FutureView<AboutContent>(
        future: _future,
        onRetry: () => setState(_load),
        loadingLabel: 'جاري تحميل البيانات',
        builder: (context, about) => ListView(
          padding: const EdgeInsets.only(bottom: 20),
          children: [
            if (about.coverImage != null)
              ClipRRect(
                borderRadius: BorderRadius.circular(18),
                child: RemoteImage(
                  url: about.coverImage,
                  width: double.infinity,
                  height: 150,
                  radius: 18,
                  icon: Icons.image_rounded,
                ),
              ),
            if (about.coverImage != null) const SizedBox(height: 12),
            NovaCard(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(about.title.isEmpty ? 'ماغديت' : about.title,
                      style: ts(15, weight: FontWeight.w700)),
                  if (about.subtitle.isNotEmpty) ...[
                    const SizedBox(height: 2),
                    Text(about.subtitle, style: ts(10, color: C.muted)),
                  ],
                  if (about.description.isNotEmpty) ...[
                    const SizedBox(height: 10),
                    Text(about.description,
                        style: ts(11, color: C.inkSoft, height: 1.9)),
                  ],
                ],
              ),
            ),
            if (about.vision.isNotEmpty) ...[
              const SizedBox(height: 12),
              _Block(
                icon: Icons.visibility_rounded,
                title: 'رؤيتنا',
                text: about.vision,
                color: C.info,
                background: C.infoSoft,
              ),
            ],
            if (about.mission.isNotEmpty) ...[
              const SizedBox(height: 12),
              _Block(
                icon: Icons.flag_rounded,
                title: 'رسالتنا',
                text: about.mission,
                color: C.success,
                background: C.successSoft,
              ),
            ],
            if (about.values.isNotEmpty) ...[
              const SizedBox(height: 12),
              NovaCard(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text('قيمنا', style: ts(12.5, weight: FontWeight.w700)),
                    const SizedBox(height: 10),
                    Wrap(
                      spacing: 8,
                      runSpacing: 8,
                      children: about.values
                          .map((value) => Tag(text: value))
                          .toList(),
                    ),
                  ],
                ),
              ),
            ],
            const SizedBox(height: 18),
            Center(
              child: Text('Maggadit Food Industries · v1.0.0',
                  style: ts(9, color: C.muted), textDirection: TextDirection.ltr),
            ),
          ],
        ),
      ),
    );
  }
}

class _Block extends StatelessWidget {
  const _Block({
    required this.icon,
    required this.title,
    required this.text,
    required this.color,
    required this.background,
  });

  final IconData icon;
  final String title;
  final String text;
  final Color color;
  final Color background;

  @override
  Widget build(BuildContext context) {
    return NovaCard(
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 34,
            height: 34,
            decoration: BoxDecoration(color: background, borderRadius: BorderRadius.circular(11)),
            child: Icon(icon, size: 16, color: color),
          ),
          const SizedBox(width: 10),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(title, style: ts(12, weight: FontWeight.w700)),
                const SizedBox(height: 4),
                Text(text, style: ts(10.5, color: C.inkSoft, height: 1.85)),
              ],
            ),
          ),
        ],
      ),
    );
  }
}
