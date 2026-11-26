import 'package:flutter/material.dart';
import 'package:provider/provider.dart';
import 'package:url_launcher/url_launcher.dart';

import '../../core/api_client.dart';
import '../../models/account.dart';
import '../../state/app_dependencies.dart';
import '../../state/auth_controller.dart';
import '../theme/app_theme.dart';
import '../widgets/chrome.dart';
import '../widgets/kit.dart';

class ContactScreen extends StatefulWidget {
  const ContactScreen({super.key});

  @override
  State<ContactScreen> createState() => _ContactScreenState();
}

class _ContactScreenState extends State<ContactScreen> {
  final _formKey = GlobalKey<FormState>();
  final _name = TextEditingController();
  final _email = TextEditingController();
  final _phone = TextEditingController();
  final _subject = TextEditingController();
  final _message = TextEditingController();

  late Future<ContactInfo> _info;
  bool _sending = false;

  @override
  void initState() {
    super.initState();
    _load();
    final customer = context.read<AuthController>().customer;
    if (customer != null) {
      _name.text = customer.name;
      _email.text = customer.email;
    }
  }

  @override
  void dispose() {
    _name.dispose();
    _email.dispose();
    _phone.dispose();
    _subject.dispose();
    _message.dispose();
    super.dispose();
  }

  void _load() {
    _info = context.read<AppDependencies>().catalogService.contactInfo();
  }

  Future<void> _send() async {
    if (!(_formKey.currentState?.validate() ?? false)) return;
    setState(() => _sending = true);
    try {
      await context.read<AppDependencies>().catalogService.contactUs(
            name: _name.text.trim(),
            email: _email.text.trim(),
            phone: _phone.text.trim(),
            subject: _subject.text.trim(),
            message: _message.text.trim(),
          );
      if (!mounted) return;
      _message.clear();
      _subject.clear();
      ScaffoldMessenger.of(context)
          .showSnackBar(const SnackBar(content: Text('تم إرسال رسالتك بنجاح')));
    } on ApiException catch (e) {
      if (!mounted) return;
      ScaffoldMessenger.of(context).showSnackBar(SnackBar(content: Text(e.message)));
    } finally {
      if (mounted) setState(() => _sending = false);
    }
  }

  Future<void> _open(String? value, {bool whatsapp = false, bool email = false}) async {
    if (value == null || value.isEmpty) return;
    final uri = email
        ? Uri(scheme: 'mailto', path: value)
        : whatsapp
            ? Uri.parse('https://wa.me/${value.replaceAll(RegExp(r'[^0-9]'), '')}')
            : Uri.parse('tel:$value');
    if (await canLaunchUrl(uri)) await launchUrl(uri, mode: LaunchMode.externalApplication);
  }

  @override
  Widget build(BuildContext context) {
    return ScreenScaffold(
      title: 'اتصل بنا',
      subtitle: 'نحن هنا لخدمتك',
      onRefresh: () async => setState(_load),
      child: Form(
        key: _formKey,
        child: ListView(
          padding: const EdgeInsets.only(bottom: 20),
          children: [
            FutureView<ContactInfo>(
              future: _info,
              loadingLabel: 'جاري تحميل بيانات التواصل',
              builder: (context, info) => NovaCard(
                child: Column(
                  children: [
                    if (info.phone != null)
                      _ContactRow(
                        icon: Icons.call_rounded,
                        label: 'هاتف',
                        value: info.phone!,
                        onTap: () => _open(info.phone),
                      ),
                    if (info.whatsapp != null)
                      _ContactRow(
                        icon: Icons.chat_rounded,
                        label: 'واتساب',
                        value: info.whatsapp!,
                        color: C.success,
                        onTap: () => _open(info.whatsapp, whatsapp: true),
                      ),
                    if (info.email != null)
                      _ContactRow(
                        icon: Icons.alternate_email_rounded,
                        label: 'البريد',
                        value: info.email!,
                        color: C.info,
                        onTap: () => _open(info.email, email: true),
                      ),
                    if (info.address != null)
                      _ContactRow(
                        icon: Icons.location_on_rounded,
                        label: 'العنوان',
                        value: info.address!,
                        color: C.rose,
                      ),
                  ],
                ),
              ),
            ),
            const SizedBox(height: 14),
            NovaCard(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text('أرسل رسالة', style: ts(13, weight: FontWeight.w700)),
                  const SizedBox(height: 12),
                  _Field(label: 'الاسم', controller: _name),
                  _Field(label: 'البريد الإلكتروني', controller: _email,
                      keyboard: TextInputType.emailAddress),
                  _Field(label: 'الهاتف', controller: _phone,
                      keyboard: TextInputType.phone, required: false),
                  _Field(label: 'الموضوع', controller: _subject),
                  _Field(label: 'الرسالة', controller: _message, lines: 4),
                  const SizedBox(height: 6),
                  PrimaryButton(
                    label: 'إرسال',
                    icon: Icons.send_rounded,
                    loading: _sending,
                    onPressed: _send,
                  ),
                ],
              ),
            ),
          ],
        ),
      ),
    );
  }
}

class _ContactRow extends StatelessWidget {
  const _ContactRow({
    required this.icon,
    required this.label,
    required this.value,
    this.onTap,
    this.color = C.primaryDark,
  });

  final IconData icon;
  final String label;
  final String value;
  final VoidCallback? onTap;
  final Color color;

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: onTap,
      child: Padding(
        padding: const EdgeInsets.symmetric(vertical: 8),
        child: Row(
          children: [
            Container(
              width: 34,
              height: 34,
              decoration: BoxDecoration(
                color: color.withValues(alpha: 0.1),
                borderRadius: BorderRadius.circular(11),
              ),
              child: Icon(icon, size: 16, color: color),
            ),
            const SizedBox(width: 10),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(label, style: ts(9, color: C.muted)),
                  Text(value,
                      style: ts(11, weight: FontWeight.w600),
                      textDirection: TextDirection.ltr),
                ],
              ),
            ),
            if (onTap != null)
              const Icon(Icons.chevron_left_rounded, size: 18, color: C.muted),
          ],
        ),
      ),
    );
  }
}

class _Field extends StatelessWidget {
  const _Field({
    required this.label,
    required this.controller,
    this.keyboard,
    this.lines = 1,
    this.required = true,
  });

  final String label;
  final TextEditingController controller;
  final TextInputType? keyboard;
  final int lines;
  final bool required;

  @override
  Widget build(BuildContext context) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 10),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Text(label, style: ts(9.5, color: C.muted)),
          const SizedBox(height: 5),
          TextFormField(
            controller: controller,
            keyboardType: keyboard,
            maxLines: lines,
            minLines: 1,
            style: ts(11.5),
            decoration: InputDecoration(hintText: label),
            validator: (value) => required && (value == null || value.trim().isEmpty)
                ? 'مطلوب'
                : null,
          ),
        ],
      ),
    );
  }
}
