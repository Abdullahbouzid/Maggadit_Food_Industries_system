import 'package:flutter/material.dart';
import 'package:provider/provider.dart';

import '../../core/formatters.dart';
import '../../state/auth_controller.dart';
import '../router.dart';
import '../theme/app_theme.dart';
import '../widgets/chrome.dart';
import '../widgets/kit.dart';
import '../../l10n/l10n.dart';

class ProfileScreen extends StatefulWidget {
  const ProfileScreen({super.key});

  @override
  State<ProfileScreen> createState() => _ProfileScreenState();
}

class _ProfileScreenState extends State<ProfileScreen> {
  final _formKey = GlobalKey<FormState>();
  final _name = TextEditingController();
  final _username = TextEditingController();
  final _email = TextEditingController();
  final _currentPassword = TextEditingController();
  final _newPassword = TextEditingController();
  final _confirmPassword = TextEditingController();
  bool _busy = false;
  bool _changePassword = false;

  @override
  void initState() {
    super.initState();
    final customer = context.read<AuthController>().customer;
    if (customer != null) {
      _name.text = customer.name;
      _username.text = customer.username;
      _email.text = customer.email;
    }
  }

  @override
  void dispose() {
    _name.dispose();
    _username.dispose();
    _email.dispose();
    _currentPassword.dispose();
    _newPassword.dispose();
    _confirmPassword.dispose();
    super.dispose();
  }

  Future<void> _save() async {
    if (!(_formKey.currentState?.validate() ?? false)) return;
    setState(() => _busy = true);
    final ok = await context.read<AuthController>().updateProfile(
      name: _name.text.trim(),
      email: _email.text.trim(),
      username: _username.text.trim(),
      oldPassword: _changePassword ? _currentPassword.text : null,
      newPassword: _changePassword ? _newPassword.text : null,
      newPasswordConfirmation: _changePassword ? _confirmPassword.text : null,
    );
    if (!mounted) return;
    setState(() => _busy = false);
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(
          ok
              ? tr(context, 'تم تحديث البيانات')
              : (context.read<AuthController>().error ??
                    tr(context, 'تعذّر التحديث')),
        ),
      ),
    );
    if (ok) {
      _currentPassword.clear();
      _newPassword.clear();
      _confirmPassword.clear();
      setState(() => _changePassword = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    final auth = context.watch<AuthController>();
    final controller = context.read<AuthController>();
    final customer = auth.customer;

    return ScreenScaffold(
      title: tr(context, 'الملف الشخصي'),
      subtitle: customer == null ? null : '@${customer.username}',
      child: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.only(bottom: 20),
          children: [
            NovaCard(
              child: Row(
                children: [
                  Container(
                    width: 56,
                    height: 56,
                    decoration: BoxDecoration(
                      gradient: const LinearGradient(
                        colors: [C.primary, C.primaryDeep],
                      ),
                      borderRadius: BorderRadius.circular(18),
                    ),
                    child: Center(
                      child: Text(
                        Fmt.initials(customer?.name),
                        style: ts(
                          18,
                          weight: FontWeight.w700,
                          color: Colors.white,
                        ),
                      ),
                    ),
                  ),
                  const SizedBox(width: 12),
                  Expanded(
                    child: Column(
                      crossAxisAlignment: CrossAxisAlignment.start,
                      children: [
                        Text(
                          customer?.name ?? '—',
                          style: ts(14, weight: FontWeight.w700),
                        ),
                        const SizedBox(height: 2),
                        Text(
                          customer?.email ?? '',
                          style: ts(9.5, color: C.muted),
                        ),
                        const SizedBox(height: 6),
                        if (auth.activeOrganization != null)
                          Tag(
                            text: auth.activeOrganization!.name,
                            icon: Icons.apartment_rounded,
                          ),
                      ],
                    ),
                  ),
                ],
              ),
            ),
            const SizedBox(height: 14),
            NovaCard(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    tr(context, 'البيانات الأساسية'),
                    style: ts(13, weight: FontWeight.w700),
                  ),
                  const SizedBox(height: 12),
                  _label(tr(context, 'الاسم')),
                  TextFormField(
                    controller: _name,
                    style: ts(12),
                    decoration: InputDecoration(
                      hintText: tr(context, 'الاسم الكامل'),
                    ),
                    validator: (value) =>
                        (value == null || value.trim().isEmpty)
                        ? tr(context, 'مطلوب')
                        : null,
                  ),
                  const SizedBox(height: 12),
                  _label(tr(context, 'اسم المستخدم')),
                  TextFormField(
                    controller: _username,
                    style: ts(12),
                    decoration: const InputDecoration(hintText: 'username'),
                    validator: (value) =>
                        (value == null || value.trim().isEmpty)
                        ? tr(context, 'مطلوب')
                        : null,
                  ),
                  const SizedBox(height: 12),
                  _label(tr(context, 'البريد الإلكتروني')),
                  TextFormField(
                    controller: _email,
                    keyboardType: TextInputType.emailAddress,
                    style: ts(12),
                    decoration: const InputDecoration(
                      hintText: 'name@example.com',
                    ),
                    validator: (value) {
                      final text = value?.trim() ?? '';
                      if (text.isEmpty) return tr(context, 'مطلوب');
                      if (!text.contains('@'))
                        return tr(context, 'بريد غير صحيح');
                      return null;
                    },
                  ),
                  const SizedBox(height: 14),
                  SwitchListTile.adaptive(
                    value: _changePassword,
                    onChanged: (value) =>
                        setState(() => _changePassword = value),
                    contentPadding: EdgeInsets.zero,
                    activeThumbColor: C.primaryDark,
                    title: Text(
                      tr(context, 'تغيير كلمة المرور'),
                      style: ts(11.5, weight: FontWeight.w600),
                    ),
                  ),
                  if (_changePassword) ...[
                    _label(tr(context, 'كلمة المرور الحالية')),
                    TextFormField(
                      controller: _currentPassword,
                      obscureText: true,
                      style: ts(12),
                      decoration: const InputDecoration(hintText: '••••••••'),
                      validator: (value) =>
                          _changePassword && (value == null || value.isEmpty)
                          ? tr(context, 'مطلوب')
                          : null,
                    ),
                    const SizedBox(height: 12),
                    _label(tr(context, 'كلمة المرور الجديدة')),
                    TextFormField(
                      controller: _newPassword,
                      obscureText: true,
                      style: ts(12),
                      decoration: InputDecoration(
                        hintText: tr(context, '8 أحرف على الأقل'),
                      ),
                      validator: (value) {
                        if (!_changePassword) return null;
                        if (value == null || value.length < 8)
                          return tr(context, '8 أحرف على الأقل');
                        return null;
                      },
                    ),
                    const SizedBox(height: 12),
                    _label(tr(context, 'تأكيد كلمة المرور')),
                    TextFormField(
                      controller: _confirmPassword,
                      obscureText: true,
                      style: ts(12),
                      decoration: const InputDecoration(hintText: '••••••••'),
                      validator: (value) {
                        if (!_changePassword) return null;
                        if (value != _newPassword.text)
                          return tr(context, 'غير متطابقة');
                        return null;
                      },
                    ),
                  ],
                  const SizedBox(height: 18),
                  PrimaryButton(
                    label: tr(context, 'حفظ التعديلات'),
                    icon: Icons.save_rounded,
                    loading: _busy,
                    onPressed: _save,
                  ),
                ],
              ),
            ),
            const SizedBox(height: 12),
            NovaCard(
              child: Row(
                children: [
                  const Icon(
                    Icons.translate_rounded,
                    size: 17,
                    color: C.inkSoft,
                  ),
                  const SizedBox(width: 10),
                  Expanded(
                    child: Text(
                      tr(context, 'اللغة'),
                      style: ts(
                        11.5,
                        color: C.inkSoft,
                        weight: FontWeight.w600,
                      ),
                    ),
                  ),
                  const LanguageSwitcher(compact: false),
                ],
              ),
            ),
            const SizedBox(height: 12),
            NovaCard(
              padding: EdgeInsets.zero,
              child: Column(
                children: [
                  _MenuTile(
                    icon: Icons.apartment_rounded,
                    label: tr(context, 'الفروع والمنظمات'),
                    onTap: () => Routes.organizations(),
                  ),
                  const Divider(height: 1),
                  _MenuTile(
                    icon: Icons.shield_outlined,
                    label: tr(context, 'سياسة الخصوصية'),
                    onTap: () => Routes.privacy(),
                  ),
                  const Divider(height: 1),
                  _MenuTile(
                    icon: Icons.logout_rounded,
                    label: tr(context, 'تسجيل الخروج'),
                    color: C.danger,
                    onTap: () async {
                      final confirmed = await showDialog<bool>(
                        context: context,
                        builder: (dialogContext) => AlertDialog(
                          shape: RoundedRectangleBorder(
                            borderRadius: BorderRadius.circular(18),
                          ),
                          title: Text(
                            tr(context, 'تسجيل الخروج'),
                            style: ts(14, weight: FontWeight.w700),
                          ),
                          content: Text(
                            tr(context, 'هل تريد الخروج من حسابك؟'),
                            style: ts(11, color: C.inkSoft),
                          ),
                          actions: [
                            TextButton(
                              onPressed: () =>
                                  Navigator.pop(dialogContext, false),
                              child: Text(
                                tr(context, 'إلغاء'),
                                style: ts(12, color: C.muted),
                              ),
                            ),
                            FilledButton(
                              onPressed: () =>
                                  Navigator.pop(dialogContext, true),
                              style: FilledButton.styleFrom(
                                backgroundColor: C.danger,
                              ),
                              child: Text(
                                tr(context, 'خروج'),
                                style: ts(12, color: Colors.white),
                              ),
                            ),
                          ],
                        ),
                      );
                      if (confirmed == true) {
                        await controller.logout();
                        if (!context.mounted) return;
                        Routes.login();
                      }
                    },
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }

  Widget _label(String text) => Padding(
    padding: const EdgeInsets.only(bottom: 6),
    child: Text(text, style: ts(9.5, color: C.muted)),
  );
}

class _MenuTile extends StatelessWidget {
  const _MenuTile({
    required this.icon,
    required this.label,
    required this.onTap,
    this.color = C.inkSoft,
  });

  final IconData icon;
  final String label;
  final VoidCallback onTap;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      child: Padding(
        padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 14),
        child: Row(
          children: [
            Icon(icon, size: 17, color: color),
            const SizedBox(width: 10),
            Expanded(
              child: Text(
                label,
                style: ts(11.5, color: color, weight: FontWeight.w600),
              ),
            ),
            const Icon(Icons.chevron_left_rounded, size: 18, color: C.muted),
          ],
        ),
      ),
    );
  }
}
