import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../models/account.dart';
import '../../state/app_dependencies.dart';
import '../router.dart';
import '../theme/app_theme.dart';
import '../widgets/chrome.dart';
import '../widgets/kit.dart';
import '../../l10n/l10n.dart';

class SurveysScreen extends StatefulWidget {
  const SurveysScreen({super.key});

  @override
  State<SurveysScreen> createState() => _SurveysScreenState();
}

class _SurveysScreenState extends State<SurveysScreen> {
  late Future<List<SurveySummary>> _future;

  @override
  void initState() {
    super.initState();
    _load();
  }

  void _load() {
    _future = context.read<AppDependencies>().surveyService.surveys();
  }

  @override
  Widget build(BuildContext context) {
    return ScreenScaffold(
      title: tr(context, 'الاستبيانات'),
      subtitle: tr(context, 'رأيك يساعدنا على التحسّن'),
      onRefresh: () async => setState(_load),
      child: FutureView<List<SurveySummary>>(
        future: _future,
        onRetry: () => setState(_load),
        emptyCheck: (data) => data.isEmpty,
        emptyIcon: Icons.assignment_rounded,
        emptyTitle: tr(context, 'لا توجد استبيانات حالياً'),
        loadingLabel: tr(context, 'جاري تحميل الاستبيانات'),
        builder: (context, surveys) => ListView.separated(
          padding: const EdgeInsets.only(bottom: 16),
          itemCount: surveys.length,
          separatorBuilder: (_, __) => const SizedBox(height: 10),
          itemBuilder: (context, index) {
            final survey = surveys[index];
            return NovaCard(
              onTap: survey.isAnswered
                  ? () => ScaffoldMessenger.of(context).showSnackBar(
                      SnackBar(
                        content: Text(
                          tr(context, 'تمت الإجابة على هذا الاستبيان مسبقاً'),
                        ),
                      ),
                    )
                  : () => Routes.survey(survey.id),
              child: Row(
                children: [
                  Container(
                    width: 40,
                    height: 40,
                    decoration: BoxDecoration(
                      color: survey.isAnswered ? C.successSoft : C.purpleSoft,
                      borderRadius: BorderRadius.circular(12),
                    ),
                    child: Icon(
                      survey.isAnswered
                          ? Icons.task_alt_rounded
                          : Icons.assignment_rounded,
                      size: 18,
                      color: survey.isAnswered ? C.success : C.primaryDark,
                    ),
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          survey.title,
                          maxLines: 2,
                          overflow: TextOverflow.ellipsis,
                          style: ts(12, weight: FontWeight.w700),
                        ),
                        const SizedBox(height: 3),
                        Text(
                          tArgs(context, '{0} أسئلة · ~{1} دقيقة', [
                            survey.questionsCount,
                            survey.estimatedMinutes,
                          ]),
                          style: ts(9, color: C.muted),
                        ),
                      ],
                    ),
                  ),
                  if (survey.isAnswered)
                    Tag(
                      text: tr(context, 'تم'),
                      color: C.success,
                      background: C.successSoft,
                    )
                  else
                    const Icon(
                      Icons.chevron_left_rounded,
                      size: 20,
                      color: C.muted,
                    ),
                ],
              ),
            );
          },
        ),
      ),
    );
  }
}
