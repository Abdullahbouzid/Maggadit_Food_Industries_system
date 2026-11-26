import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../state/auth_controller.dart';
import '../router.dart';
import '../theme/app_theme.dart';
import '../widgets/chrome.dart';
import '../widgets/kit.dart';

class RegisterScreen extends StatefulWidget {
  const RegisterScreen({super.key});

  @override
  State<RegisterScreen> createState() => _RegisterScreenState();
}

class _RegisterScreenState extends State<RegisterScreen> {
  final _formKey = GlobalKey<FormState>();
  final _name = TextEditingController();
  final _username = TextEditingController();
  final _email = TextEditingController();
  final _password = TextEditingController();
  final _confirm = TextEditingController();
  bool _busy = false;

  @override
  void dispose() {
    _name.dispose();
    _username.dispose();
    _email.dispose();
    _password.dispose();
    _confirm.dispose();
    super.dispose();
  }

  Future<void> _submit() async {
    if (!(_formKey.currentState?.validate() ?? false)) return;
    FocusScope.of(context).unfocus();
    setState(() => _busy = true);

    final auth = context.read<AuthController>();
    final ok = await auth.register(
      name: _name.text.trim(),
      username: _username.text.trim(),
      email: _email.text.trim(),
      password: _password.text,
      passwordConfirmation: _confirm.text,
    );

    if (!mounted) return;
    setState(() => _busy = false);
    if (ok) {
      Routes.home();
    } else {
      ScaffoldMessenger.of(context)
          .showSnackBar(SnackBar(content: Text(auth.error ?? 'تعذّر إنشاء الحساب')));
    }
  }

  @override
  Widget build(BuildContext context) {
    return ScreenScaffold(
      title: 'حساب جديد',
      subtitle: 'سجّل بياناتك للبدء بالطلب',
      child: SingleChildScrollView(
        child: Form(
          key: _formKey,
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.stretch,
            children: [
              const SizedBox(height: 6),
              _field('الاسم الكامل', _name, hint: 'الاسم'),
              _field('اسم المستخدم', _username, hint: 'username'),
              _field('البريد الإلكتروني', _email, hint: 'name@example.com',
                  keyboard: TextInputType.emailAddress),
              _field('كلمة المرور', _password, hint: '••••••••', obscure: true),
              _field('تأكيد كلمة المرور', _confirm, hint: '••••••••', obscure: true,
                  validator: (value) {
                if (value != _password.text) return 'كلمتا المرور غير متطابقتين';
                return null;
              }),
              const SizedBox(height: 8),
              PrimaryButton(
                label: 'إنشاء الحساب',
                icon: Icons.person_add_alt_1_rounded,
                loading: _busy,
                onPressed: _submit,
              ),
              const SizedBox(height: 10),
              Center(
                child: TextButton(
                  onPressed: _busy ? null : () => Navigator.of(context).maybePop(),
                  child: Text('لديك حساب؟ تسجيل الدخول',
                      style: ts(11, color: C.primaryDark, weight: FontWeight.w600)),
                ),
              ),
              const SizedBox(height: 20),
            ],
          ),
        ),
      ),
    );
  }

  Widget _field(
    String label,
    TextEditingController controller, {
    String? hint,
    bool obscure = false,
    TextInputType? keyboard,
    String? Function(String?)? validator,
  }) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(label, style: ts(10.5, weight: FontWeight.w600, color: C.inkSoft)),
          const SizedBox(height: 6),
          TextFormField(
            controller: controller,
            obscureText: obscure,
            keyboardType: keyboard,
            style: ts(12.5),
            decoration: InputDecoration(hintText: hint),
            validator: validator ??
                (value) => (value == null || value.trim().isEmpty) ? 'مطلوب' : null,
          ),
        ],
      ),
    );
  }
}
