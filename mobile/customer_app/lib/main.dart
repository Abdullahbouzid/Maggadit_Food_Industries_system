import 'package:flutter/material.dart';

import 'app.dart';
import 'core/config.dart';
import 'state/app_dependencies.dart';
import 'ui/router.dart';

Future<void> main() async {
  WidgetsFlutterBinding.ensureInitialized();

  final dependencies = await AppDependencies.create();

  dependencies.onPushTap = (payload) {
    WidgetsBinding.instance.addPostFrameCallback((_) {
      final context = Routes.navigatorKey.currentContext;
      if (context != null) MaggaditApp.handlePushTap(context, payload);
    });
  };

  runApp(MaggaditApp(dependencies: dependencies));
  await dependencies.bootstrap();
  debugPrint('[${AppConfig.appName}] API: ${dependencies.api.baseUrl}');
}
