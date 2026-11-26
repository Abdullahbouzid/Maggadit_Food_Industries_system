import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../state/app_dependencies.dart';
import '../../state/auth_controller.dart';
import '../router.dart';
import '../theme/app_theme.dart';
import '../widgets/chrome.dart';
import '../widgets/kit.dart';
import '../../l10n/l10n.dart';

class LoginScreen extends StatefulWidget {
  const LoginScreen({super.key});

  @override
  State<LoginScreen> createState() => _LoginScreenState();
}

class _LoginScreenState extends State<LoginScreen> {
  final _formKey = GlobalKey<FormState>();
  final _username = TextEditingController();
  final _password = TextEditingController();
  bool _busy = false;
  bool _obscure = true;

  @override
  void dispose() {
    _username.dispose();
    _password.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!(_formKey.currentState?.validate() ?? false)) return;
    FocusScope.of(context).unfocus();
    setState(() => _busy = true);

    final auth = context.read<AuthController>();
    final ok = await auth.login(
      username: _username.text.trim(),
      password: _password.text,
    );

    if (!mounted) return;
    setState(() => _busy = false);

    if (ok) {
      Routes.home();
    } else {
      _snack(auth.error ?? tr(context, 'تعذّر تسجيل الدخول'));
    }
  }

  void _snack(String message) {
    ScaffoldMessenger.of(context)
      ..hideCurrentSnackBar()
      ..showSnackBar(SnackBar(content: Text(message)));
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: C.bg,
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.fromLTRB(22, 12, 22, 24),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const Align(
                alignment: AlignmentDirectional.centerEnd,
                child: LanguageSwitcher(),
              ),
              const SizedBox(height: 4),
              Center(
                child: Container(
                  width: 92,
                  height: 92,
                  decoration: BoxDecoration(
                    borderRadius: BorderRadius.circular(26),
                    boxShadow: Sh.card,
                  ),
                  child: ClipRRect(
                    borderRadius: BorderRadius.circular(26),
                    child: Image.asset(
                      'assets/images/logo_tile.png',
                      fit: BoxFit.cover,
                    ),
                  ),
                ),
              ),
              const SizedBox(height: 16),
              Text(
                tr(context, 'ماغديت'),
                textAlign: TextAlign.center,
                style: ts(24, weight: FontWeight.w700),
              ),
              const SizedBox(height: 4),
              Text(
                tr(context, 'للصناعات الغذائية'),
                textAlign: TextAlign.center,
                style: ts(11, color: C.muted),
              ),
              const SizedBox(height: 26),
              NovaCard(
                padding: const EdgeInsets.fromLTRB(16, 18, 16, 18),
                child: Form(
                  key: _formKey,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      Text(
                        tr(context, 'تسجيل الدخول'),
                        style: ts(14, weight: FontWeight.w700),
                      ),
                      const SizedBox(height: 14),
                      _Label(tr(context, 'اسم المستخدم')),
                      TextFormField(
                        controller: _username,
                        textInputAction: TextInputAction.next,
                        textDirection: TextDirection.ltr,
                        style: ts(12.5),
                        decoration: const InputDecoration(hintText: 'username'),
                        validator: (value) =>
                            (value == null || value.trim().isEmpty)
                            ? tr(context, 'مطلوب')
                            : null,
                      ),
                      const SizedBox(height: 12),
                      _Label(tr(context, 'كلمة المرور')),
                      TextFormField(
                        controller: _password,
                        obscureText: _obscure,
                        textDirection: TextDirection.ltr,
                        style: ts(12.5),
                        onFieldSubmitted: (_) => _submit(),
                        decoration: InputDecoration(
                          hintText: '••••••••',
                          suffixIcon: IconButton(
                            onPressed: () =>
                                setState(() => _obscure = !_obscure),
                            icon: Icon(
                              _obscure
                                  ? Icons.visibility_off_rounded
                                  : Icons.visibility_rounded,
                              size: 18,
                              color: C.muted,
                            ),
                          ),
                        ),
                        validator: (value) => (value == null || value.isEmpty)
                            ? tr(context, 'مطلوب')
                            : null,
                      ),
                      const SizedBox(height: 18),
                      PrimaryButton(
                        label: tr(context, 'تسجيل الدخول'),
                        icon: Icons.login_rounded,
                        loading: _busy,
                        onPressed: _submit,
                      ),
                      const SizedBox(height: 12),
                      Center(
                        child: TextButton(
                          onPressed: _busy ? null : () => Routes.register(),
                          child: Text(
                            tr(context, 'ليس لديك حساب؟ إنشاء حساب جديد'),
                            style: ts(
                              11,
                              color: C.primaryDark,
                              weight: FontWeight.w600,
                            ),
                          ),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 28),
              Text(
                'Maggadit Food Industries · v1.0.0',
                textAlign: TextAlign.center,
                style: ts(9, color: C.muted),
              ),
            ],
          ),
        ),
      ),
    );
  }
}

class _Label extends StatelessWidget {
  const _Label(this.text);
  final String text;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 6),
      child: Text(
        text,
        style: ts(10.5, weight: FontWeight.w600, color: C.inkSoft),
      ),
    );
  }
}
