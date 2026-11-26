import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../state/app_dependencies.dart';
import '../../state/auth_controller.dart';
import '../router.dart';
import '../theme/app_theme.dart';
import '../widgets/kit.dart';

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
      _snack(auth.error ?? 'تعذّر تسجيل الدخول');
    }
  }

  void _snack(String message) {
    ScaffoldMessenger.of(context)
      ..hideCurrentSnackBar()
      ..showSnackBar(SnackBar(content: Text(message)));
  }

  Future<void> _editServer() async {
    final deps = context.read<AppDependencies>();
    final authController = context.read<AuthController>();
    final controller = TextEditingController(text: deps.api.baseUrl);
    final result = await showDialog<String>(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(18)),
        title: Text('عنوان الخادم', style: ts(14, weight: FontWeight.w700)),
        content: Column(
          mainAxisSize: MainAxisSize.min,
          children: [
            TextField(
              controller: controller,
              textDirection: TextDirection.ltr,
              style: ts(12),
              decoration: const InputDecoration(hintText: 'https://example.com/api'),
            ),
            const SizedBox(height: 10),
            Text(
              'مثال: http://10.0.2.2:8000/api للمحاكي، أو رابط السيرفر الحقيقي مع /api',
              style: ts(9.5, color: C.muted, height: 1.6),
            ),
          ],
        ),
        actions: [
          TextButton(
            onPressed: () => Navigator.pop(context),
            child: Text('إلغاء', style: ts(12, color: C.muted)),
          ),
          FilledButton(
            onPressed: () => Navigator.pop(context, controller.text.trim()),
            style: FilledButton.styleFrom(backgroundColor: C.primary),
            child: Text('حفظ', style: ts(12, color: Colors.white)),
          ),
        ],
      ),
    );

    if (result != null && result.isNotEmpty) {
      await authController.changeServer(result);
      if (!mounted) return;
      _snack('تم تحديث عنوان الخادم');
    }
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
              Align(
                alignment: AlignmentDirectional.centerStart,
                child: TextButton.icon(
                  onPressed: _editServer,
                  icon: const Icon(Icons.dns_rounded, size: 15, color: C.muted),
                  label: Text('عنوان الخادم', style: ts(10, color: C.muted)),
                ),
              ),
              const SizedBox(height: 8),
              Center(
                child: Container(
                  width: 84,
                  height: 84,
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(colors: [C.primary, C.primaryDeep]),
                    borderRadius: BorderRadius.circular(24),
                    boxShadow: Sh.card,
                  ),
                  child: const Icon(Icons.local_mall_rounded, size: 38, color: Colors.white),
                ),
              ),
              const SizedBox(height: 16),
              Text('ماغديت',
                  textAlign: TextAlign.center, style: ts(24, weight: FontWeight.w700)),
              const SizedBox(height: 4),
              Text('للصناعات الغذائية',
                  textAlign: TextAlign.center, style: ts(11, color: C.muted)),
              const SizedBox(height: 26),
              NovaCard(
                padding: const EdgeInsets.fromLTRB(16, 18, 16, 18),
                child: Form(
                  key: _formKey,
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.stretch,
                    children: [
                      Text('تسجيل الدخول', style: ts(14, weight: FontWeight.w700)),
                      const SizedBox(height: 14),
                      _Label('اسم المستخدم'),
                      TextFormField(
                        controller: _username,
                        textInputAction: TextInputAction.next,
                        textDirection: TextDirection.ltr,
                        style: ts(12.5),
                        decoration: const InputDecoration(hintText: 'username'),
                        validator: (value) =>
                            (value == null || value.trim().isEmpty) ? 'مطلوب' : null,
                      ),
                      const SizedBox(height: 12),
                      _Label('كلمة المرور'),
                      TextFormField(
                        controller: _password,
                        obscureText: _obscure,
                        textDirection: TextDirection.ltr,
                        style: ts(12.5),
                        onFieldSubmitted: (_) => _submit(),
                        decoration: InputDecoration(
                          hintText: '••••••••',
                          suffixIcon: IconButton(
                            onPressed: () => setState(() => _obscure = !_obscure),
                            icon: Icon(
                              _obscure ? Icons.visibility_off_rounded : Icons.visibility_rounded,
                              size: 18,
                              color: C.muted,
                            ),
                          ),
                        ),
                        validator: (value) =>
                            (value == null || value.isEmpty) ? 'مطلوب' : null,
                      ),
                      const SizedBox(height: 18),
                      PrimaryButton(
                        label: 'تسجيل الدخول',
                        icon: Icons.login_rounded,
                        loading: _busy,
                        onPressed: _submit,
                      ),
                      const SizedBox(height: 12),
                      Center(
                        child: TextButton(
                          onPressed: _busy ? null : () => Routes.register(),
                          child: Text('ليس لديك حساب؟ إنشاء حساب جديد',
                              style: ts(11, color: C.primaryDark, weight: FontWeight.w600)),
                        ),
                      ),
                    ],
                  ),
                ),
              ),
              const SizedBox(height: 28),
              Text('Maggadit Food Industries · v1.0.0',
                  textAlign: TextAlign.center, style: ts(9, color: C.muted)),
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
      child: Text(text, style: ts(10.5, weight: FontWeight.w600, color: C.inkSoft)),
    );
  }
}
