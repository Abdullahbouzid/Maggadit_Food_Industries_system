import 'package:flutter/material.dart';
import '../../services/api_service.dart';

class ContactUsScreen extends StatefulWidget {
  final String token;
  final String userId;

  const ContactUsScreen({
    Key? key,
    required this.token,
    required this.userId,
  }) : super(key: key);

  @override
  State<ContactUsScreen> createState() => _ContactUsScreenState();
}

class _ContactUsScreenState extends State<ContactUsScreen> {
  final ApiService _apiService = ApiService();
  final _formKey = GlobalKey<FormState>();
  final TextEditingController _nameController = TextEditingController();
  final TextEditingController _phoneController = TextEditingController();
  final TextEditingController _emailController = TextEditingController();
  final TextEditingController _subjectController = TextEditingController();
  final TextEditingController _messageController = TextEditingController();
  late Future<Map<String, dynamic>> _contactInfoFuture;
  bool _isSubmitting = false;

  @override
  void initState() {
    super.initState();
    _contactInfoFuture = _loadContactInfo();
  }

  Future<Map<String, dynamic>> _loadContactInfo() async {
    try {
      return await _apiService.get('/customer/contact-info');
    } catch (e) {
      return {};
    }
  }

  Future<void> _submitForm() async {
    if (!_formKey.currentState!.validate()) return;

    setState(() => _isSubmitting = true);

    try {
      final response = await _apiService.post('/customer/contact-us', body: {
        'name': _nameController.text.trim(),
        'email': _emailController.text.trim(),
        'phone': _phoneController.text.trim(),
        'subject': _subjectController.text.trim(),
        'message': _messageController.text.trim(),
      });

      if (response['success'] == true) {
        _formKey.currentState?.reset();
        _nameController.clear();
        _phoneController.clear();
        _emailController.clear();
        _subjectController.clear();
        _messageController.clear();

        await showDialog(
          context: context,
          barrierDismissible: false,
          builder: (context) => _SuccessDialog(message: response['message'] ?? 'تم إرسال رسالتك بنجاح'),
        );
        Navigator.of(context).pop();
      } else {
        _showToast(response['message'] ?? 'فشل الإرسال', isError: true);
      }
    } catch (e) {
      _showToast('حدث خطأ في الاتصال', isError: true);
    } finally {
      if (mounted) setState(() => _isSubmitting = false);
    }
  }

  void _showToast(String message, {bool isError = false}) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message, textAlign: TextAlign.center),
        backgroundColor: isError ? Colors.red : const Color(0xFF9879b6),
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        duration: const Duration(seconds: 2),
      ),
    );
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text('رجوع', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: Color(0xFF6B7280))),
        leading: const SizedBox(),
        actions: [
          IconButton(icon: const Icon(Icons.arrow_back_ios_new, color: Color(0xFF9879b6), size: 20), onPressed: () => Navigator.of(context).pop()),
        ],
        backgroundColor: Colors.white,
        elevation: 0,
      ),
      body: RefreshIndicator(
        onRefresh: () async => setState(() => _contactInfoFuture = _loadContactInfo()),
        child: SingleChildScrollView(
          physics: const BouncingScrollPhysics(),
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              // Contact info cards
              FutureBuilder<Map<String, dynamic>>(
                future: _contactInfoFuture,
                builder: (context, snapshot) {
                  if (snapshot.connectionState == ConnectionState.waiting) {
                    return const Center(child: Padding(padding: EdgeInsets.all(32), child: CircularProgressIndicator()));
                  }
                  final data = snapshot.data;
                  final info = (data?['data'] ?? {}) as Map<String, dynamic>;

                  if (info.isEmpty) {
                    return Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16), boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.04), blurRadius: 8, offset: const Offset(0, 2))]),
                      child: const Text('لا توجد معلومات تواصل متاحة حالياً', style: TextStyle(fontSize: 14, color: Color(0xFF9CA3AF))),
                    );
                  }

                  return Column(
                    children: [
                      _buildInfoCard(Icons.local_phone_outlined, 'اتصل بنا', info['phone'] ?? '-'),
                      const SizedBox(height: 12),
                      _buildInfoCard(Icons.email_outlined, 'بريد إلكتروني', info['email'] ?? '-'),
                      const SizedBox(height: 12),
                      _buildInfoCard(Icons.logo_dev, 'واتساب', info['whatsapp'] ?? '-'),
                      if (info['address'] != null && (info['address'] as String).isNotEmpty)
                        const SizedBox(height: 12),
                      if (info['address'] != null && (info['address'] as String).isNotEmpty)
                        _buildInfoCard(Icons.location_on_outlined, 'موقعنا', info['address'] as String),
                    ],
                  );
                },
              ),
              const SizedBox(height: 24),
              // Contact form
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(20),
                decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16), boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.04), blurRadius: 8, offset: const Offset(0, 2))]),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(children: const [
                      Icon(Icons.send, color: Color(0xFF9879b6), size: 20),
                      SizedBox(width: 8),
                      Text('أرسل رسالتك', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Color(0xFF1E293B))),
                    ]),
                    const SizedBox(height: 8),
                    const Text('سنقوم بالرد عليك في أقرب وقت ممكن', style: TextStyle(fontSize: 12, color: Color(0xFF9CA3AF))),
                    const SizedBox(height: 16),
                    Form(
                      key: _formKey,
                      child: Column(children: [
                        _buildTextField(_nameController, 'الاسم الكامل', 'أدخل اسمك', required: true),
                        const SizedBox(height: 16),
                        _buildTextField(_phoneController, 'رقم الهاتف', 'رقم هاتفك'),
                        const SizedBox(height: 16),
                        _buildTextField(_emailController, 'البريد الإلكتروني', 'بريدك الإلكتروني', required: true, isEmail: true),
                        const SizedBox(height: 16),
                        _buildTextField(_subjectController, 'الموضوع', 'موضوع الرسالة'),
                        const SizedBox(height: 16),
                        _buildTextField(_messageController, 'الرسالة', 'اكتب رسالتك هنا...', required: true, maxLines: 4),
                        const SizedBox(height: 20),
                        SizedBox(
                          width: double.infinity,
                          child: ElevatedButton(
                            onPressed: _isSubmitting ? null : _submitForm,
                            style: ElevatedButton.styleFrom(
                              backgroundColor: const Color(0xFF9879b6),
                              foregroundColor: Colors.white,
                              padding: const EdgeInsets.symmetric(vertical: 14),
                              shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                            ),
                            child: _isSubmitting
                                ? const SizedBox(width: 20, height: 20, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2))
                                : const Text('إرسال الرسالة', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600)),
                          ),
                        ),
                      ]),
                    ),
                  ],
                ),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildInfoCard(IconData icon, String label, String value) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        gradient: const LinearGradient(colors: [Color(0xFFF1E6FF), Color(0xFFE9D7FF)], begin: Alignment.topLeft, end: Alignment.bottomRight),
        borderRadius: BorderRadius.circular(16),
      ),
      child: Row(children: [
        Container(width: 40, height: 40, decoration: BoxDecoration(color: Colors.white, shape: BoxShape.circle), child: Icon(icon, color: const Color(0xFF9879b6), size: 20)),
        const SizedBox(width: 12),
        Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text(label, style: const TextStyle(fontSize: 11, color: Color(0xFF9CA3AF))),
          const SizedBox(height: 2),
          Text(value, style: const TextStyle(fontSize: 15, fontWeight: FontWeight.w600, color: Color(0xFF1E293B))),
        ])),
      ]),
    );
  }

  Widget _buildTextField(TextEditingController controller, String label, String placeholder, {bool required = false, bool isEmail = false, int maxLines = 1}) {
    return TextFormField(
      controller: controller,
      maxLines: maxLines,
      keyboardType: isEmail ? TextInputType.emailAddress : (label.contains('هاتف') ? TextInputType.phone : TextInputType.text),
      validator: (value) {
        if (required && (value == null || value.trim().isEmpty)) return 'هذا الحقل مطلوب';
        if (isEmail && value != null && !value.contains('@')) return 'البريد الإلكتروني غير صحيح';
        return null;
      },
      decoration: InputDecoration(
        labelText: label,
        hintText: placeholder,
        hintStyle: TextStyle(color: Colors.grey.shade400, fontSize: 13),
        labelStyle: const TextStyle(fontSize: 13, color: Color(0xFF6B7280)),
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: Color(0xFFE2E8F0))),
        focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(12), borderSide: const BorderSide(color: Color(0xFFC9B8F0))),
        contentPadding: const EdgeInsets.symmetric(horizontal: 14, vertical: 12),
      ),
    );
  }
}

class _SuccessDialog extends StatelessWidget {
  final String message;

  const _SuccessDialog({Key? key, required this.message}) : super(key: key);

  @override
  Widget build(BuildContext context) {
    return Dialog(
      backgroundColor: Colors.transparent,
      child: Container(
        padding: const EdgeInsets.all(24),
        decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(20), boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.2), blurRadius: 20)]),
        child: Column(mainAxisSize: MainAxisSize.min, children: [
          Container(width: 72, height: 72, decoration: BoxDecoration(shape: BoxShape.circle, gradient: const LinearGradient(colors: [Colors.green, Colors.green])), child: const Icon(Icons.check, color: Colors.white, size: 36)),
          const SizedBox(height: 16),
          const Text('تم الإرسال بنجاح!', style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: Color(0xFF1E293B))),
          const SizedBox(height: 8),
          Text(message, style: TextStyle(fontSize: 14, color: Colors.grey.shade600), textAlign: TextAlign.center),
          const SizedBox(height: 20),
          SizedBox(width: double.infinity, child: ElevatedButton(onPressed: () => Navigator.of(context).pop(), style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF9879b6), foregroundColor: Colors.white, padding: const EdgeInsets.symmetric(vertical: 12), shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12))), child: const Text('رائع، شكراً لك', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600)))),
        ]),
      ),
    );
  }
}
