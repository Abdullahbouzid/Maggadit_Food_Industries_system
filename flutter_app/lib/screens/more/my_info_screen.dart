import 'package:flutter/material.dart';
import '../../services/api_service.dart';
import 'package:shared_preferences/shared_preferences.dart';

class MyInfoScreen extends StatefulWidget {
  final String token;
  final String userId;

  const MyInfoScreen({
    Key? key,
    required this.token,
    required this.userId,
  }) : super(key: key);

  @override
  State<MyInfoScreen> createState() => _MyInfoScreenState();
}

class _MyInfoScreenState extends State<MyInfoScreen> {
  final ApiService _apiService = ApiService();
  late Future<Map<String, dynamic>> _profileFuture;

  @override
  void initState() {
    super.initState();
    _profileFuture = _loadProfile();
  }

  Future<Map<String, dynamic>> _loadProfile() async {
    try {
      final response = await _apiService.get('/customer/profile');
      return response;
    } catch (e) {
      return {};
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
        onRefresh: () async => setState(() => _profileFuture = _loadProfile()),
        child: SingleChildScrollView(
          physics: const BouncingScrollPhysics(),
          padding: const EdgeInsets.all(16),
          child: FutureBuilder<Map<String, dynamic>>(
            future: _profileFuture,
            builder: (context, snapshot) {
              if (snapshot.connectionState == ConnectionState.waiting) {
                return const Center(child: Padding(padding: EdgeInsets.all(32), child: CircularProgressIndicator()));
              }
              if (snapshot.hasError) {
                return Center(child: Text('خطأ في تحميل البيانات', style: TextStyle(color: Colors.grey.shade600)));
              }
              final data = snapshot.data;
              final customer = snapshot.data ?? {};

              if (customer.isEmpty) {
                return const Center(child: Text('لا توجد بيانات متاحة', style: TextStyle(color: Color(0xFF9CA3AF), fontSize: 14)));
              }

              final name = customer['name'] ?? 'مستخدم';
              final username = customer['username'] ?? '';
              final email = customer['email'] ?? '';
              final phone = customer['phone'] ?? '';
              final defaultAddress = customer['default_address'] ?? '';
              final avatar = customer['avatar'] ?? '';
              final createdAt = customer['created_at'] ?? '';

              return Column(
                children: [
                  // Avatar and welcome
                  Container(
                    alignment: Alignment.center,
                    margin: const EdgeInsets.only(bottom: 24),
                    child: Column(children: [
                      Container(
                        width: 96,
                        height: 96,
                        decoration: BoxDecoration(
                          shape: BoxShape.circle,
                          border: Border.all(color: Colors.white, width: 3),
                          image: avatar.isNotEmpty ? DecorationImage(image: NetworkImage(avatar), fit: BoxFit.cover) : null,
                        ),
                        child: avatar.isEmpty ? const Icon(Icons.person, color: Colors.white, size: 40) : null,
                      ),
                      const SizedBox(height: 12),
                      Text(name, style: const TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: Color(0xFF1E293B))),
                      Text('عضو منذ ${createdAt is String && createdAt.isNotEmpty ? createdAt.substring(0, 4) : DateTime.now().year}', style: const TextStyle(fontSize: 12, color: Color(0xFF9CA3AF))),
                    ]),
                  ),

                  // Info cards
                  _buildInfoCard(Icons.person_outline, 'الاسم الكامل', name),
                  const SizedBox(height: 12),
                  _buildInfoCard(Icons.person, 'اسم المستخدم', username),
                  const SizedBox(height: 12),
                  _buildInfoCard(Icons.email_outlined, 'البريد الإلكتروني', email),
                  const SizedBox(height: 12),
                  _buildInfoCard(Icons.phone_android, 'رقم الجوال', phone),
                  const SizedBox(height: 12),
                  if (defaultAddress.isNotEmpty) ...[
                    _buildInfoCard(Icons.location_on_outlined, 'العنوان الافتراضي', defaultAddress),
                    const SizedBox(height: 12),
                  ],

                  // Wallet
                  Container(
                    width: double.infinity,
                    padding: const EdgeInsets.all(16),
                    decoration: BoxDecoration(color: Colors.green.shade50, borderRadius: BorderRadius.circular(16), border: Border.all(color: Colors.green.shade200)),
                    child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
                      Row(children: const [
                        Icon(Icons.wallet_outlined, color: Colors.green, size: 20),
                        SizedBox(width: 8),
                        Text('المحفظة', style: TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Colors.green)),
                        SizedBox(width: 6),
                        Text('(للعرض فقط)', style: TextStyle(fontSize: 11, color: Colors.green)),
                      ]),
                      const SizedBox(height: 8),
                      Text('${customer['wallet']?['balance'] ?? '0.00'} ${customer['wallet']?['currency']?['code'] ?? ''}', style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF1E293B))),
                    ]),
                  ),
                  const SizedBox(height: 24),

                  // Edit button
                  SizedBox(
                    width: double.infinity,
                    child: ElevatedButton(
                      onPressed: () => _showEditDialog(customer),
                      style: ElevatedButton.styleFrom(
                        backgroundColor: const Color(0xFF9879b6),
                        foregroundColor: Colors.white,
                        padding: const EdgeInsets.symmetric(vertical: 14),
                        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
                      ),
                      child: const Text('تعديل البيانات الشخصية', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600)),
                    ),
                  ),
                ],
              );
            },
          ),
        ),
      ),
    );
  }

  Widget _buildInfoCard(IconData icon, String label, String value) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16), boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.04), blurRadius: 8, offset: const Offset(0, 2))]),
      child: Row(children: [
        Container(width: 40, height: 40, decoration: BoxDecoration(color: const Color(0xFFF1E6FF), shape: BoxShape.circle), child: Icon(icon, color: const Color(0xFF9879b6), size: 20)),
        const SizedBox(width: 12),
        Expanded(child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
          Text(label, style: const TextStyle(fontSize: 11, color: Color(0xFF9CA3AF))),
          const SizedBox(height: 2),
          Text(value.isNotEmpty ? value : '-', style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: Color(0xFF1E293B))),
        ])),
      ]),
    );
  }

  void _showEditDialog(Map<String, dynamic> customer) {
    final _formKey = GlobalKey<FormState>();
    final nameCtrl = TextEditingController(text: customer['name'] ?? '');
    final usernameCtrl = TextEditingController(text: customer['username'] ?? '');
    final emailCtrl = TextEditingController(text: customer['email'] ?? '');
    final phoneCtrl = TextEditingController(text: customer['phone'] ?? '');
    final addressCtrl = TextEditingController(text: customer['default_address'] ?? '');
    bool isSaving = false;

    showDialog(
      context: context,
      barrierDismissible: true,
      builder: (dialogContext) => StatefulBuilder(
        builder: (context, setDialogState) {
          return Dialog(
            backgroundColor: Colors.transparent,
            insetPadding: const EdgeInsets.all(20),
            child: Container(
              constraints: BoxConstraints(maxHeight: MediaQuery.of(context).size.height * 0.8),
              decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(20)),
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(20),
                child: Column(
                  mainAxisSize: MainAxisSize.min,
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Row(children: const [
                      Icon(Icons.edit_outlined, color: Color(0xFF9879b6), size: 20),
                      SizedBox(width: 8),
                      Text('تعديل المعلومات', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Color(0xFF1E293B))),
                    ]),
                    const SizedBox(height: 16),
                    Form(
                      key: _formKey,
                      child: Column(children: [
                        _buildEditField(nameCtrl, 'الاسم الكامل', true),
                        const SizedBox(height: 12),
                        _buildEditField(usernameCtrl, 'اسم المستخدم', true),
                        const SizedBox(height: 12),
                        _buildEditField(emailCtrl, 'البريد الإلكتروني', true, isEmail: true),
                        const SizedBox(height: 12),
                        _buildEditField(phoneCtrl, 'رقم الجوال', false),
                        const SizedBox(height: 12),
                        _buildEditField(addressCtrl, 'العنوان الافتراضي', false, maxLines: 2),
                        const SizedBox(height: 20),
                        Row(children: [
                          Expanded(child: TextButton(onPressed: () => Navigator.of(context).pop(), style: TextButton.styleFrom(backgroundColor: Colors.grey.shade100, padding: const EdgeInsets.symmetric(vertical: 12), shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12))), child: const Text('إلغاء', style: TextStyle(color: Color(0xFF64748B))))),
                          const SizedBox(width: 12),
                          Expanded(child: ElevatedButton(
                            onPressed: isSaving ? null : () async {
                              if (_formKey.currentState?.validate() != true) return;
                              setDialogState(() => isSaving = true);
                              try {
                                final response = await _apiService.post('/customer/profile/update', body: {
                                  'name': nameCtrl.text.trim(),
                                  'username': usernameCtrl.text.trim(),
                                  'email': emailCtrl.text.trim(),
                                  'phone': phoneCtrl.text.trim(),
                                  'default_address': addressCtrl.text.trim(),
                                });
                                Navigator.of(context).pop();
                                _showToast(response['message'] ?? 'تم تحديث البيانات بنجاح');
                                setState(() => _profileFuture = _loadProfile());
                              } catch (e) {
                                _showToast('فشل تحديث البيانات', isError: true);
                              }
                            },
                            style: ElevatedButton.styleFrom(backgroundColor: const Color(0xFF9879b6), foregroundColor: Colors.white, padding: const EdgeInsets.symmetric(vertical: 12), shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12))),
                            child: isSaving ? const SizedBox(width: 16, height: 16, child: CircularProgressIndicator(color: Colors.white, strokeWidth: 2)) : const Text('حفظ التغييرات', style: TextStyle(fontSize: 13, fontWeight: FontWeight.w600)),
                          )),
                        ]),
                      ]),
                    ),
                  ],
                ),
              ),
            ),
          );
        },
      ),
    );
  }

  Widget _buildEditField(TextEditingController controller, String label, bool required, {bool isEmail = false, int maxLines = 1}) {
    return TextFormField(
      controller: controller,
      maxLines: maxLines,
      keyboardType: isEmail ? TextInputType.emailAddress : TextInputType.text,
      validator: (value) {
        if (required && (value == null || value.trim().isEmpty)) return 'هذا الحقل مطلوب';
        if (isEmail && value != null && !value.contains('@')) return 'البريد الإلكتروني غير صحيح';
        return null;
      },
      decoration: InputDecoration(
        labelText: label,
        labelStyle: const TextStyle(fontSize: 12, color: Color(0xFF6B7280)),
        border: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFFE2E8F0))),
        focusedBorder: OutlineInputBorder(borderRadius: BorderRadius.circular(8), borderSide: const BorderSide(color: Color(0xFFC9B8F0))),
        contentPadding: const EdgeInsets.symmetric(horizontal: 12, vertical: 10),
      ),
    );
  }
}
