import 'package:flutter/material.dart';
import '../../services/api_service.dart';
import 'package:shared_preferences/shared_preferences.dart';

class ChangeOrganizationScreen extends StatefulWidget {
  final String token;
  final String userId;

  const ChangeOrganizationScreen({
    Key? key,
    required this.token,
    required this.userId,
  }) : super(key: key);

  @override
  State<ChangeOrganizationScreen> createState() => _ChangeOrganizationScreenState();
}

class _ChangeOrganizationScreenState extends State<ChangeOrganizationScreen> {
  final ApiService _apiService = ApiService();
  late Future<Map<String, dynamic>> _organizationsFuture;

  @override
  void initState() {
    super.initState();
    _organizationsFuture = _loadOrganizations();
  }

  Future<Map<String, dynamic>> _loadOrganizations() async {
    try {
      return await _apiService.get('/customer/organizations');
    } catch (e) {
      return {'success': false};
    }
  }

  Future<void> _switchOrganization(int organizationId, String organizationName) async {
    try {
      final response = await _apiService.post('/customer/change-organization', body: {'organization_id': organizationId});
      if (response['success'] == true) {
        _showToast('تم التبديل إلى "$organizationName" بنجاح');
        setState(() {
          _organizationsFuture = _loadOrganizations();
        });
      } else {
        _showToast(response['message'] ?? 'فشل في تغيير المنظمة', isError: true);
      }
    } catch (e) {
      _showToast('حدث خطأ في الاتصال بالخادم', isError: true);
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
        onRefresh: () async => setState(() => _organizationsFuture = _loadOrganizations()),
        child: SingleChildScrollView(
          physics: const BouncingScrollPhysics(),
          padding: const EdgeInsets.all(16),
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16), boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.04), blurRadius: 8, offset: const Offset(0, 2))]),
                child: const Text('اختر المنظمة', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Color(0xFF1E293B))),
              ),
              const SizedBox(height: 16),
              FutureBuilder<Map<String, dynamic>>(
                future: _organizationsFuture,
                builder: (context, snapshot) {
                  if (snapshot.connectionState == ConnectionState.waiting) {
                    return const Center(child: Padding(padding: EdgeInsets.all(32), child: CircularProgressIndicator()));
                  }
                  if (snapshot.hasError) {
                    return _buildErrorState();
                  }
                  final data = snapshot.data;
                  if (data == null || data['success'] != true) {
                    return _buildErrorState();
                  }
                  final organizations = data['organizations'] as List? ?? [];
                  final activeId = data['active_organization_id']?.toString();

                  if (organizations.isEmpty) {
                    return Container(
                      padding: const EdgeInsets.all(24),
                      alignment: Alignment.center,
                      child: Column(children: const [
                        Icon(Icons.business_outlined, size: 48, color: Color(0xFFCBD5E1)),
                        SizedBox(height: 12),
                        Text('لا توجد منظمات مرتبطة', style: TextStyle(fontSize: 14, color: Color(0xFF9CA3AF)))
                      ]),
                    );
                  }

                  return Column(
                    children: organizations.map((org) {
                      final orgId = org['id'].toString();
                      final isActive = orgId == activeId || org['is_active'] == true;
                      final orgName = org['name'] ?? '';

                      return Container(
                        margin: const EdgeInsets.only(bottom: 12),
                        decoration: BoxDecoration(
                          color: Colors.white,
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(color: isActive ? const Color(0xFF9879b6) : const Color(0xFFE2E8F0)),
                          boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.04), blurRadius: 8, offset: const Offset(0, 2))],
                        ),
                        child: ListTile(
                          contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 10),
                          leading: Container(width: 44, height: 44, decoration: BoxDecoration(color: const Color(0xFFF1E6FF), borderRadius: BorderRadius.circular(12)), child: const Icon(Icons.business, color: Color(0xFF9879b6))),
                          title: Text(orgName, style: const TextStyle(fontSize: 15, fontWeight: FontWeight.bold, color: Color(0xFF1E293B)), maxLines: 1, overflow: TextOverflow.ellipsis),
                          subtitle: Text('${org['code'] ?? ''} ${org['organization_type']?.isNotEmpty == true ? '- ${org['organization_type']}' : ''}', style: const TextStyle(fontSize: 11, color: Color(0xFF9CA3AF))),
                          trailing: isActive
                              ? Container(
                                  padding: const EdgeInsets.symmetric(horizontal: 10, vertical: 4),
                                  decoration: BoxDecoration(color: Colors.green.shade50, borderRadius: BorderRadius.circular(20)),
                                  child: Row(children: const [
                                    Icon(Icons.check_circle, color: Colors.green, size: 16),
                                    SizedBox(width: 4),
                                    Text('نشط', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.green)),
                                  ]),
                                )
                              : TextButton(
                                  onPressed: () => _switchOrganization(org['id'] as int, orgName),
                                  style: TextButton.styleFrom(backgroundColor: const Color(0xFF9879b6), padding: const EdgeInsets.symmetric(horizontal: 14, vertical: 6)),
                                  child: const Text('تبديل', style: TextStyle(fontSize: 11, fontWeight: FontWeight.bold, color: Colors.white)),
                                ),
                        ),
                      );
                    }).toList(),
                  );
                },
              ),
              const SizedBox(height: 20),
              TextButton(
                onPressed: () async {
                  final prefs = await SharedPreferences.getInstance();
                  await prefs.clear();
                  if (!mounted) return;
                  Navigator.of(context).pushNamedAndRemoveUntil('/login', (route) => false);
                },
                style: TextButton.styleFrom(backgroundColor: Colors.transparent),
                child: Row(mainAxisAlignment: MainAxisAlignment.center, children: const [
                  Icon(Icons.logout, color: Color(0xFFDC2626), size: 18),
                  SizedBox(width: 6),
                  Text('تسجيل الخروج', style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: Color(0xFFDC2626))),
                ]),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildErrorState() {
    return Container(
      padding: const EdgeInsets.all(32),
      alignment: Alignment.center,
                    child: Column(children: const [
                      Icon(Icons.error_outline, size: 48, color: Colors.red),
                      SizedBox(height: 12),
                      Text('حدث خطأ في تحميل المنظمات', style: TextStyle(fontSize: 14, color: Color(0xFF6B7280)))
                    ]),
    );
  }
}
