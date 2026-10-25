import 'package:flutter/material.dart';
import '../../services/api_service.dart';
import '../../routes/app_routes.dart';
import 'about_us_screen.dart';
import 'change_organization_screen.dart';
import 'contact_us_screen.dart';
import 'faq_screen.dart';
import 'my_info_screen.dart';
import 'my_products_screen.dart';
import 'privacy_policy_screen.dart';
import 'return_create_screen.dart';

class MoreMenuScreen extends StatefulWidget {
  final String token;
  final String userId;

  const MoreMenuScreen({
    Key? key,
    required this.token,
    required this.userId,
  }) : super(key: key);

  @override
  State<MoreMenuScreen> createState() => _MoreMenuScreenState();
}

class _MoreMenuScreenState extends State<MoreMenuScreen> {
  late Future<Map<String, dynamic>> _customerFuture;
  final ApiService _apiService = ApiService();

  @override
  void initState() {
    super.initState();
    _customerFuture = _loadCustomer();
  }

  Future<Map<String, dynamic>> _loadCustomer() async {
    try {
      return await _apiService.get('/customer/profile');
    } catch (e) {
      return {};
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      body: SafeArea(
        child: SingleChildScrollView(
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              _buildProfileHeader(),
              const SizedBox(height: 24),
              _buildMenuItems(),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildProfileHeader() {
    return FutureBuilder<Map<String, dynamic>>(
      future: _customerFuture,
      builder: (context, snapshot) {
        final customer = snapshot.data ?? {};
        final name = customer['name'] ?? 'مستخدم';
        final email = customer['email'] ?? '';
        final avatar = customer['avatar'] ?? '';

        return Container(
          padding: const EdgeInsets.all(20),
          decoration: BoxDecoration(
            gradient: const LinearGradient(
              colors: [Color(0xFF9879b6), Color(0xFF7c5ea8)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
            borderRadius: BorderRadius.circular(28),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(0.1),
                blurRadius: 12,
                offset: const Offset(0, 4),
              ),
            ],
          ),
          child: Row(
            children: [
              Container(
                width: 72,
                height: 72,
                decoration: BoxDecoration(
                  shape: BoxShape.circle,
                  border: Border.all(color: Colors.white, width: 3),
                  image: avatar.isNotEmpty
                      ? DecorationImage(
                          image: NetworkImage(avatar),
                          fit: BoxFit.cover,
                        )
                      : null,
                ),
                child: avatar.isEmpty
                    ? const Icon(Icons.person, color: Colors.white, size: 36)
                    : null,
              ),
              const SizedBox(width: 16),
              Expanded(
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      name,
                      style: const TextStyle(
                        fontSize: 20,
                        fontWeight: FontWeight.bold,
                        color: Colors.white,
                      ),
                      maxLines: 1,
                      overflow: TextOverflow.ellipsis,
                    ),
                    if (email.isNotEmpty)
                      Text(
                        email,
                        style: TextStyle(
                          fontSize: 13,
                          color: Colors.white.withOpacity(0.9),
                        ),
                        maxLines: 1,
                        overflow: TextOverflow.ellipsis,
                      ),
                    const SizedBox(height: 8),
                    Text(
                      'مشاهد الباقية',
                      style: TextStyle(
                        fontSize: 12,
                        color: Colors.white.withOpacity(0.8),
                      ),
                    ),
                  ],
                ),
              ),
              const Icon(Icons.chevron_left, color: Colors.white70, size: 24),
            ],
          ),
        );
      },
    );
  }

  Widget _buildMenuItems() {
    final items = [
      _MoreItem(
        icon: Icons.person_outline,
        title: 'معلوماتي',
        subtitle: 'إدارة حسابك الشخصي',
        onTap: () => _push(MyInfoScreen(token: widget.token, userId: widget.userId)),
      ),
      _MoreItem(
        icon: Icons.shield_outlined,
        title: 'سياسة الخصوصية',
        subtitle: 'كيف نستخدم بياناتك',
        onTap: () => _push(PrivacyPolicyScreen()),
      ),
      _MoreItem(
        icon: Icons.info_outline,
        title: 'نبذة عنا',
        subtitle: 'تعرف على ماجديت',
        onTap: () => _push(AboutUsScreen()),
      ),
      _MoreItem(
        icon: Icons.chat_bubble_outline,
        title: 'الأسئلة الشائعة',
        subtitle: 'حل المشكلات الشائعة',
        onTap: () => _push(FaqScreen()),
      ),
      _MoreItem(
        icon: Icons.account_balance_outlined,
        title: 'تغيير المنظمة',
        subtitle: 'اختيار منظمة أخرى',
        onTap: () => _push(ChangeOrganizationScreen(token: widget.token, userId: widget.userId)),
      ),
      _MoreItem(
        icon: Icons.history,
        title: 'سجل الطلبات',
        subtitle: 'طلباتك السابقة',
        onTap: () => Navigator.of(context).pushNamed(AppRoutes.orderHistory),
      ),
      _MoreItem(
        icon: Icons.local_offer_outlined,
        title: 'العروض',
        subtitle: 'العروض الخاصة',
        onTap: () => Navigator.of(context).pushNamed(AppRoutes.offers),
      ),
      _MoreItem(
        icon: Icons.description_outlined,
        title: 'طلبات الإرجاع',
        subtitle: 'إدارة إرجاعاتك',
        onTap: () => Navigator.of(context).pushNamed(AppRoutes.returns),
      ),
      _MoreItem(
        icon: Icons.star_outline,
        title: 'منتجاتي',
        subtitle: 'متابعة طلباتك',
        onTap: () => _push(MyProductsScreen(token: widget.token, userId: widget.userId)),
      ),
      _MoreItem(
        icon: Icons.notifications_outlined,
        title: 'الإشعارات',
        subtitle: 'إدارة التنبيهات',
        onTap: () => Navigator.of(context).pushNamed(AppRoutes.notifications),
      ),
      _MoreItem(
        icon: Icons.poll,
        title: 'الاستبيانات',
        subtitle: 'شارك في الاستبيانات',
        onTap: () => Navigator.of(context).pushNamed(AppRoutes.surveys),
      ),
      _MoreItem(
        icon: Icons.email_outlined,
        title: 'اتصل بنا',
        subtitle: 'نود أن نسمع منك',
        onTap: () => _push(ContactUsScreen(token: widget.token, userId: widget.userId)),
      ),
    ];

    return Column(
      children: [
        Container(
          decoration: BoxDecoration(
            color: Colors.white,
            borderRadius: BorderRadius.circular(20),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(0.04),
                blurRadius: 8,
                offset: const Offset(0, 2),
              ),
            ],
          ),
          child: Column(
            children: List.generate(items.length, (index) {
              return Column(
                children: [
                  _buildMenuItem(items[index]),
                  if (index < items.length - 1)
                    const Divider(height: 1, color: Color(0xFFF1F5F9)),
                ],
              );
            }),
          ),
        ),
        const SizedBox(height: 24),
        _buildLogoutSection(),
      ],
    );
  }

  Widget _buildMenuItem(_MoreItem item) {
    return ListTile(
      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 8),
      leading: Container(
        width: 44,
        height: 44,
        decoration: BoxDecoration(
          color: const Color(0xFFF1E6FF),
          borderRadius: BorderRadius.circular(12),
        ),
        child: Icon(item.icon, color: const Color(0xFF9879b6), size: 22),
      ),
      title: Text(
        item.title,
        style: const TextStyle(
          fontSize: 15,
          fontWeight: FontWeight.w600,
          color: Color(0xFF1E293B),
        ),
        textAlign: TextAlign.right,
      ),
      subtitle: Text(
        item.subtitle,
        style: TextStyle(fontSize: 12, color: Colors.grey.shade500),
        textAlign: TextAlign.right,
      ),
      trailing: const Icon(Icons.chevron_left, color: Color(0xFFCBD5E1), size: 20),
      onTap: item.onTap,
    );
  }

  Widget _buildLogoutSection() {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.04),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: TextButton(
        onPressed: _logout,
        style: TextButton.styleFrom(
          padding: const EdgeInsets.all(16),
        ),
        child: Row(
          mainAxisAlignment: MainAxisAlignment.center,
          children: const [
            Icon(Icons.logout, color: Color(0xFFDC2626), size: 20),
            SizedBox(width: 8),
            Text(
              'تسجيل الخروج',
              style: TextStyle(
                fontSize: 15,
                fontWeight: FontWeight.w600,
                color: Color(0xFFDC2626),
              ),
            ),
          ],
        ),
      ),
    );
  }

  void _push(Widget screen) {
    Navigator.of(context).push(MaterialPageRoute(builder: (_) => screen));
  }

  void _logout() async {
    final confirmed = await showDialog<bool>(
      context: context,
      builder: (context) => AlertDialog(
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
        title: const Text(
          'تسجيل الخروع',
          textAlign: TextAlign.right,
          style: TextStyle(fontSize: 18),
        ),
        content: const Text(
          'هل أنت متأكد أنك تريد تسجيل الخروج؟',
          textAlign: TextAlign.right,
        ),
        actionsAlignment: MainAxisAlignment.spaceEvenly,
        actions: [
          TextButton(
            onPressed: () => Navigator.of(context).pop(false),
            child: const Text('إلغاء', style: TextStyle(color: Color(0xFF64748B))),
          ),
          TextButton(
            onPressed: () => Navigator.of(context).pop(true),
            child: const Text('تسجيل الخروج', style: TextStyle(color: Color(0xFFDC2626))),
          ),
        ],
      ),
    );

    if (confirmed == true) {
      await _apiService.logout();
      Navigator.of(context).pushNamedAndRemoveUntil(AppRoutes.login, (route) => false);
    }
  }
}

class _MoreItem {
  final IconData icon;
  final String title;
  final String subtitle;
  final VoidCallback onTap;

  _MoreItem({
    required this.icon,
    required this.title,
    required this.subtitle,
    required this.onTap,
  });
}
