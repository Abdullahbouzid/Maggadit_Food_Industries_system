import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../state/auth_controller.dart';
import '../router.dart';
import '../theme/app_theme.dart';
import '../widgets/kit.dart';
import '../../l10n/l10n.dart';

class SplashScreen extends StatefulWidget {
  const SplashScreen({super.key});

  @override
  State<SplashScreen> createState() => _SplashScreenState();
}

class _SplashScreenState extends State<SplashScreen> {
  @override
  void initState() {
    super.initState();
    WidgetsBinding.instance.addPostFrameCallback((_) => _decide());
  }

  Future<void> _decide() async {
    final auth = context.read<AuthController>();
    if (auth.status == AuthStatus.unknown) {
      await auth.bootstrap();
    }
    if (!mounted) return;
    await Future<void>.delayed(const Duration(milliseconds: 350));
    if (!mounted) return;
    if (auth.isAuthenticated) {
      Routes.home();
    } else {
      Routes.login();
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: C.primaryDeep,
      body: Center(
        child: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            Container(
              width: 112,
              height: 112,
              decoration: BoxDecoration(
                borderRadius: BorderRadius.circular(30),
                border: Border.all(color: Colors.white.withValues(alpha: 0.35)),
                boxShadow: Sh.card,
              ),
              child: ClipRRect(
                borderRadius: BorderRadius.circular(30),
                child: Image.asset(
                  'assets/images/logo_tile.png',
                  fit: BoxFit.cover,
                ),
              ),
            ),
            const SizedBox(height: 20),
            Text(
              tr(context, 'ماغديت'),
              style: ts(26, weight: FontWeight.w700, color: Colors.white),
            ),
            const SizedBox(height: 4),
            Text(
              tr(context, 'للصناعات الغذائية'),
              style: ts(11, color: Colors.white.withValues(alpha: 0.8)),
            ),
            const SizedBox(height: 30),
            const SizedBox(
              width: 22,
              height: 22,
              child: CircularProgressIndicator(
                strokeWidth: 2.2,
                color: Colors.white,
              ),
            ),
          ],
        ),
      ),
    );
  }
}
