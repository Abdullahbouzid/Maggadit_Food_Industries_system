import 'package:flutter/material.dart';
import '../../services/api_service.dart';

class PrivacyPolicyScreen extends StatefulWidget {
  const PrivacyPolicyScreen({Key? key}) : super(key: key);

  @override
  State<PrivacyPolicyScreen> createState() => _PrivacyPolicyScreenState();
}

class _PrivacyPolicyScreenState extends State<PrivacyPolicyScreen> {
  final ApiService _apiService = ApiService();
  late Future<Map<String, dynamic>> _policyFuture;

  @override
  void initState() {
    super.initState();
    _policyFuture = _apiService.get('/customer/privacy-policy');
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text(
          'رجوع',
          style: TextStyle(
            fontSize: 14,
            fontWeight: FontWeight.w600,
            color: Color(0xFF6B7280),
          ),
        ),
        leading: const SizedBox(),
        actions: [
          IconButton(
            icon: const Icon(Icons.arrow_back_ios_new, color: Color(0xFF9879b6), size: 20),
            onPressed: () => Navigator.of(context).pop(),
          ),
        ],
        backgroundColor: Colors.white,
        elevation: 0,
      ),
      body: FutureBuilder<Map<String, dynamic>>(
        future: _policyFuture,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          }
          final content = snapshot.data?['content'] ?? '';
          final title = snapshot.data?['title'] ?? 'سياسة الخصوصية';

          if (content.isEmpty) {
            return _buildStaticContent();
          }

          return SingleChildScrollView(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                      colors: [Color(0xFF7C3AED), Color(0xFF6365F1)],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Column(
                    children: const [
                      Icon(Icons.shield_outlined, size: 48, color: Colors.white70),
                      SizedBox(height: 8),
                      Text(
                        'خصوصيتك تهمنا',
                        style: TextStyle(
                          fontSize: 20,
                          fontWeight: FontWeight.bold,
                          color: Colors.white,
                        ),
                      ),
                      SizedBox(height: 4),
                      Text(
                        'نحن في ماجديت نلتزم بحماية بياناتك الشخصية وخصوصيتك',
                        style: TextStyle(fontSize: 12, color: Colors.white70),
                        textAlign: TextAlign.center,
                      ),
                    ],
                  ),
                ),
                const SizedBox(height: 20),
                Container(
                  width: double.infinity,
                  padding: const EdgeInsets.all(16),
                  decoration: BoxDecoration(
                    color: Colors.white,
                    borderRadius: BorderRadius.circular(16),
                    boxShadow: [
                      BoxShadow(color: Colors.black.withOpacity(0.04), blurRadius: 8, offset: const Offset(0, 2)),
                    ],
                  ),
                  child: Text(
                    content,
                    style: const TextStyle(fontSize: 14, height: 1.8, color: Color(0xFF1E293B)),
                    textAlign: TextAlign.right,
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _buildStaticContent() {
    return SingleChildScrollView(
      padding: const EdgeInsets.all(16),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: double.infinity,
            padding: const EdgeInsets.all(20),
            decoration: BoxDecoration(
              gradient: const LinearGradient(
                colors: [Color(0xFF7C3AED), Color(0xFF6365F1)],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: BorderRadius.circular(20),
            ),
            child: const Column(
              children: [
                Icon(Icons.shield_outlined, size: 48, color: Colors.white70),
                SizedBox(height: 8),
                Text(
                  'خصوصيتك تهمنا',
                  style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: Colors.white),
                ),
                SizedBox(height: 4),
                Text(
                  'نحن في ماجديت نلتزم بحماية بياناتك الشخصية وخصوصيتك',
                  style: TextStyle(fontSize: 12, color: Colors.white70),
                  textAlign: TextAlign.center,
                ),
              ],
            ),
          ),
          const SizedBox(height: 20),
          _buildSection(
            icon: Icons.storage,
            iconColor: Colors.blue,
            bgColor: Colors.blue.shade50,
            title: 'جمع المعلومات',
            body: 'نقوم بجمع المعلومات التي تقدمها لنا طواعية عند التسجيل، مثل الاسم، البريد الإلكتروني، رقم الجوال، وعنوان التوصيل. كما قد نجمع معلومات غير شخصية مثل نوع الجهاز ونظام التشغيل لتحسين أداء التطبيق.',
          ),
          const SizedBox(height: 16),
          _buildSection(
            icon: Icons.bar_chart_rounded,
            iconColor: Colors.green,
            bgColor: Colors.green.shade50,
            title: 'كيف نستخدم بياناتك',
            body: 'معالجة طلباتك وتوصيلها، تحسين تجربتك وتخصيص المحتوى، التواصل معك بشأن العروض والتحديثات (بموافقتك)، دعم العملاء وحل المشكلات.',
            bodyIsList: true,
          ),
          const SizedBox(height: 16),
          _buildSection(
            icon: Icons.lock_outline,
            iconColor: Colors.amber,
            bgColor: Colors.amber.shade50,
            title: 'حماية معلوماتك',
            body: 'نستخدم إجراءات أمنية متقدمة (تشفير SSL، جدران حماية) لحماية بياناتك من الوصول غير المصرح به أو التعديل أو الكشف.',
          ),
          const SizedBox(height: 16),
          _buildSection(
            icon: Icons.share_outlined,
            iconColor: Colors.purple,
            bgColor: Colors.purple.shade50,
            title: 'مشاركة البيانات مع أطراف خارجية',
            body: 'لا نقوم ببيع أو تأجير بياناتك الشخصية. قد نشاركها فقط مع شركات النقل والشحن لتوصيل طلباتك، أو مع مزودي خدمات الدفع، أو عند اقتضاء القانون ذلك.',
          ),
          const SizedBox(height: 16),
          _buildSection(
            icon: Icons.person_outline,
            iconColor: Colors.red,
            bgColor: Colors.red.shade50,
            title: 'حقوقك',
            body: 'الوصول إلى بياناتك، تعديل أو تحديث معلوماتك، طلب حذف حسابك وبياناتك، إلغاء الاشتراك في الرسائل التسويقية.',
            bodyIsList: true,
          ),
          const SizedBox(height: 16),
          _buildSection(
            icon: Icons.cookie_outlined,
            iconColor: Colors.indigo,
            bgColor: Colors.indigo.shade50,
            title: 'ملفات تعريف الارتباط (Cookies)',
            body: 'نستخدم ملفات تعريف الارتباط لتحسين أداء التطبيق وتذكر تفضيلاتك. يمكنك تعطيلها من إعدادات متصفحك.',
          ),
        ],
      ),
    );
  }

  Widget _buildSection({
    required IconData icon,
    required Color iconColor,
    required Color bgColor,
    required String title,
    required String body,
    bool bodyIsList = false,
  }) {
    return Container(
      width: double.infinity,
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(color: Colors.black.withOpacity(0.04), blurRadius: 8, offset: const Offset(0, 2)),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: Row(
              children: [
                Container(
                  width: 40,
                  height: 40,
                  decoration: BoxDecoration(color: bgColor, shape: BoxShape.circle),
                  child: Icon(icon, color: iconColor, size: 20),
                ),
                const SizedBox(width: 12),
                Text(title, style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold, color: Color(0xFF1E293B))),
              ],
            ),
          ),
          if (bodyIsList)
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: body.split('، ').map((item) => Padding(
                  padding: const EdgeInsets.only(bottom: 8),
                  child: Row(
                    children: [
                      Icon(Icons.check_circle, color: Colors.green.shade500, size: 14),
                      const SizedBox(width: 6),
                      Expanded(child: Text(item, style: const TextStyle(fontSize: 13, color: Color(0xFF6B7280)))),
                    ],
                  ),
                )).toList(),
              ),
            ),
          if (!bodyIsList)
            Padding(
              padding: const EdgeInsets.fromLTRB(16, 0, 16, 16),
              child: Text(body, style: const TextStyle(fontSize: 13, height: 1.8, color: Color(0xFF6B7280)), textAlign: TextAlign.right),
            ),
        ],
      ),
    );
  }
}
