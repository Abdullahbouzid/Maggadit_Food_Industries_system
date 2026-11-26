import 'package:flutter/material.dart';
import 'package:flutter_localizations/flutter_localizations.dart';
import 'package:flutter_test/flutter_test.dart';
import 'package:maggadit_customer/core/session_store.dart';
import 'package:maggadit_customer/l10n/l10n.dart';
import 'package:maggadit_customer/state/locale_controller.dart';
import 'package:maggadit_customer/ui/widgets/chrome.dart';
import 'package:provider/provider.dart';
import 'package:shared_preferences/shared_preferences.dart';

void main() {
  TestWidgetsFlutterBinding.ensureInitialized();

  setUp(() => SharedPreferences.setMockInitialValues({}));
  tearDown(() => L10n.languageCode = 'ar');

  Future<LocaleController> build() async =>
      LocaleController(await SessionStore.open());

  test('starts in Arabic and persists the English choice', () async {
    final controller = await build();
    expect(controller.locale, const Locale('ar'));
    expect(controller.direction, TextDirection.rtl);

    await controller.setLanguage('en');
    expect(L10n.languageCode, 'en');

    final restored = await build();
    expect(restored.locale, const Locale('en'));
    expect(restored.direction, TextDirection.ltr);
  });

  testWidgets('switching the language re-renders the same screen in English', (
    tester,
  ) async {
    final controller = await build();

    await tester.pumpWidget(
      ChangeNotifierProvider<LocaleController>.value(
        value: controller,
        child: Consumer<LocaleController>(
          builder: (context, locale, _) => MaterialApp(
            locale: locale.locale,
            supportedLocales: LocaleController.supported,
            localizationsDelegates: const [
              GlobalMaterialLocalizations.delegate,
              GlobalWidgetsLocalizations.delegate,
              GlobalCupertinoLocalizations.delegate,
            ],
            builder: (context, child) =>
                Directionality(textDirection: locale.direction, child: child!),
            home: const _Probe(),
          ),
        ),
      ),
    );

    expect(find.text('تسجيل الدخول'), findsOneWidget);
    expect(find.text('سلة التسوق'), findsOneWidget);

    await tester.tap(find.text('English'));
    await tester.pumpAndSettle();

    expect(find.text('Sign in'), findsOneWidget);
    expect(find.text('Cart'), findsOneWidget);
    expect(
      Directionality.of(tester.element(find.text('Sign in'))),
      TextDirection.ltr,
    );
  });
}

class _Probe extends StatelessWidget {
  const _Probe();

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      body: Column(
        children: [
          Text(tr(context, 'تسجيل الدخول')),
          Text(tr(context, 'سلة التسوق')),
          const LanguageSwitcher(),
        ],
      ),
    );
  }
}
