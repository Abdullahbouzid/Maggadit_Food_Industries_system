import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/api_client.dart';
import '../../models/account.dart';
import '../../state/app_dependencies.dart';
import '../theme/app_theme.dart';
import '../widgets/chrome.dart';
import '../widgets/kit.dart';
import '../../l10n/l10n.dart';

class SurveyFillScreen extends StatefulWidget {
  const SurveyFillScreen({super.key, required this.surveyId});

  final int surveyId;

  @override
  State<SurveyFillScreen> createState() => _SurveyFillScreenState();
}

class _SurveyFillScreenState extends State<SurveyFillScreen> {
  late Future<SurveyDetail> _future;
  final Map<int, String> _answers = {};
  final Map<int, TextEditingController> _controllers = {};
  bool _submitting = false;

  @override
  void initState() {
    super.initState();
    _future = context.read<AppDependencies>().surveyService.survey(
      widget.surveyId,
    );
  }

  @override
  void dispose() {
    for (final controller in _controllers.values) {
      controller.dispose();
    }
    super.dispose();
  }

  Future<void> _submit(SurveyDetail survey) async {
    final missing = survey.questions
        .where((q) => q.isRequired && (_answers[q.id] ?? '').trim().isEmpty)
        .toList();
    if (missing.isNotEmpty) {
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(
          content: Text(tr(context, 'يرجى الإجابة على جميع الأسئلة المطلوبة')),
        ),
      );
      return;
    }

    setState(() => _submitting = true);
    try {
      await context.read<AppDependencies>().surveyService.submit(
        survey.id,
        _answers,
      );
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(
        SnackBar(content: Text(tr(context, 'شكرًا لك، تم إرسال إجاباتك'))),
      );
      Navigator.of(context).maybePop();
    } on ApiException catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(
        context,
      ).showSnackBar(SnackBar(content: Text(e.message)));
    } finally {
      if (mounted) setState(() => _submitting = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return ScreenScaffold(
      title: tr(context, 'استبيان'),
      subtitle: tr(context, 'إجاباتك تُستخدم لتحسين الخدمة'),
      child: FutureView<SurveyDetail>(
        future: _future,
        loadingLabel: tr(context, 'جاري تحميل الاستبيان'),
        builder: (context, survey) => ListView(
          padding: const EdgeInsets.only(bottom: 20),
          children: [
            NovaCard(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(survey.title, style: ts(14, weight: FontWeight.w700)),
                  if (survey.description.isNotEmpty) ...[
                    const SizedBox(height: 6),
                    Text(
                      survey.description,
                      style: ts(10.5, color: C.inkSoft, height: 1.7),
                    ),
                  ],
                  const SizedBox(height: 8),
                  Text(
                    tArgs(context, '{0} أسئلة', [survey.questions.length]),
                    style: ts(9, color: C.muted),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 12),
            for (var i = 0; i < survey.questions.length; i++)
              Padding(
                padding: const EdgeInsets.only(bottom: 12),
                child: _QuestionCard(
                  index: i + 1,
                  question: survey.questions[i],
                  controller: _controllerFor(survey.questions[i]),
                  value: _answers[survey.questions[i].id],
                  onChanged: (value) =>
                      setState(() => _answers[survey.questions[i].id] = value),
                ),
              ),
            const SizedBox(height: 6),
            PrimaryButton(
              label: tr(context, 'إرسال الإجابات'),
              icon: Icons.send_rounded,
              loading: _submitting,
              onPressed: () => _submit(survey),
            ),
          ],
        ),
      ),
    );
  }

  TextEditingController _controllerFor(SurveyQuestion question) {
    return _controllers.putIfAbsent(question.id, () {
      final controller = TextEditingController();
      controller.addListener(() {
        _answers[question.id] = controller.text;
      });
      return controller;
    });
  }
}

class _QuestionCard extends StatelessWidget {
  const _QuestionCard({
    required this.index,
    required this.question,
    required this.controller,
    required this.onChanged,
    this.value,
  });

  final int index;
  final SurveyQuestion question;
  final TextEditingController controller;
  final ValueChanged<String> onChanged;
  final String? value;

  @override
  Widget build(BuildContext context) {
    return NovaCard(
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: 22,
                height: 22,
                alignment: Alignment.center,
                decoration: const BoxDecoration(
                  color: C.purpleSoft,
                  shape: BoxShape.circle,
                ),
                child: Text(
                  '$index',
                  style: ts(9.5, weight: FontWeight.w700, color: C.primaryDark),
                ),
              ),
              const SizedBox(width: 8),
              Expanded(
                child: Text(
                  question.text,
                  style: ts(11.5, weight: FontWeight.w600, height: 1.6),
                ),
              ),
              if (question.isRequired)
                Text(
                  '*',
                  style: ts(12, color: C.danger, weight: FontWeight.w700),
                ),
            ],
          ),
          const SizedBox(height: 10),
          if (question.isChoice)
            Wrap(
              spacing: 8,
              runSpacing: 8,
              children: question.options.map((option) {
                final selected = value == option;
                return GestureDetector(
                  onTap: () => onChanged(option),
                  child: Container(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 12,
                      vertical: 8,
                    ),
                    decoration: BoxDecoration(
                      color: selected ? C.primary : Colors.white,
                      borderRadius: BorderRadius.circular(30),
                      border: Border.all(
                        color: selected ? C.primary : C.border,
                      ),
                    ),
                    child: Text(
                      option,
                      style: ts(
                        10.5,
                        weight: FontWeight.w600,
                        color: selected ? Colors.white : C.inkSoft,
                      ),
                    ),
                  ),
                );
              }).toList(),
            )
          else
            TextField(
              controller: controller,
              maxLines: 3,
              minLines: 1,
              style: ts(11.5),
              decoration: InputDecoration(
                hintText: tr(context, 'اكتب إجابتك...'),
              ),
            ),
        ],
      ),
    );
  }
}
