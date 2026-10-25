import 'package:flutter/material.dart';
import '../../services/api_service.dart';

class AboutUsScreen extends StatefulWidget {
  const AboutUsScreen({Key? key}) : super(key: key);

  @override
  State<AboutUsScreen> createState() => _AboutUsScreenState();
}

class _AboutUsScreenState extends State<AboutUsScreen> {
  final ApiService _apiService = ApiService();
  late Future<Map<String, dynamic>> _aboutFuture;

  @override
  void initState() {
    super.initState();
    _aboutFuture = _apiService.get('/customer/about-us');
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF8FAFC),
      appBar: AppBar(
        title: const Text(
          'رجوع',
          style: TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: Color(0xFF6B7280)),
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
        future: _aboutFuture,
        builder: (context, snapshot) {
          if (snapshot.connectionState == ConnectionState.waiting) {
            return const Center(child: CircularProgressIndicator());
          }
          final content = snapshot.data?['content'] as Map<String, dynamic>?;

          if (content == null) {
            return _buildEmptyState();
          }

          return SingleChildScrollView(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Cover image
                if (content['cover_image_url'] != null && (content['cover_image_url'] as String).isNotEmpty)
                  Container(
                    height: 192,
                    width: double.infinity,
                    decoration: BoxDecoration(
                      borderRadius: BorderRadius.circular(16),
                      image: DecorationImage(
                        image: NetworkImage(content['cover_image_url']),
                        fit: BoxFit.cover,
                      ),
                    ),
                  ),
                if (content['cover_image_url'] != null && (content['cover_image_url'] as String).isNotEmpty)
                  const SizedBox(height: 20),

                // Logo and title
                _buildLogoTitle(content),

                // Story
                _buildSection(
                  icon: Icons.book_outlined,
                  title: 'قصتنا',
                  body: content['description'] ?? 'لا توجد قصة حالياً.',
                ),

                // Vision & Mission
                Container(
                  margin: const EdgeInsets.only(bottom: 20),
                  child: Row(
                    children: [
                      Expanded(
                        child: _buildStatCard(
                          icon: Icons.track_changes,
                          title: 'رؤيتنا',
                          body: content['vision'] ?? 'لا توجد رؤية محددة',
                        ),
                      ),
                      const SizedBox(width: 12),
                      Expanded(
                        child: _buildStatCard(
                          icon: Icons.favorite,
                          title: 'رسالتنا',
                          body: content['mission'] ?? 'لا توجد رسالة محددة',
                        ),
                      ),
                    ],
                  ),
                ),

                // Values
                if ((content['values'] as List? ?? []).isNotEmpty)
                  _buildValuesSection(content['values'] as List),

                // Statistics
                if ((content['statistics'] as List? ?? []).isNotEmpty)
                  _buildStatsSection(content['statistics'] as List),

                // Contact CTA
                Container(
                  padding: const EdgeInsets.all(20),
                  decoration: BoxDecoration(
                    gradient: const LinearGradient(
                      colors: [Color(0xFF9879b6), Color(0xFF7c5ea8)],
                      begin: Alignment.topLeft,
                      end: Alignment.bottomRight,
                    ),
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Column(
                    children: [
                      const Icon(Icons.headset, size: 36, color: Colors.white70),
                      const SizedBox(height: 8),
                      const Text('نحن هنا لخدمتك', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Colors.white)),
                      const SizedBox(height: 4),
                      Text('لأي استفسار أو اقتراح، لا تتردد في التواصل مع فريق الدعم', style: TextStyle(fontSize: 12, color: Colors.white.withOpacity(0.9)), textAlign: TextAlign.center),
                    ],
                  ),
                ),
              ],
            ),
          );
        },
      ),
    );
  }

  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          const Icon(Icons.info_outline, size: 64, color: Color(0xFFCBD5E1)),
          const SizedBox(height: 16),
          const Text('لا توجد نبذة', style: TextStyle(fontSize: 20, fontWeight: FontWeight.bold, color: Color(0xFF1E293B))),
          const SizedBox(height: 8),
          const Text('لم يتم إضافة نبذة لهذه المنظمة بعد.', style: TextStyle(fontSize: 14, color: Color(0xFF9CA3AF))),
        ],
      ),
    );
  }

  Widget _buildLogoTitle(Map<String, dynamic> content) {
    final imageUrl = content['image_url'] ?? '';
    final title = content['title'] ?? 'ماجاديت للصناعات الغذائية';
    final subtitle = content['subtitle'] ?? 'جودة تذوقها الثقة';

    return Column(
      children: [
        if (imageUrl.isNotEmpty)
          Container(
            margin: const EdgeInsets.only(bottom: 12),
            child: ClipRRect(
              borderRadius: BorderRadius.circular(16),
              child: Image.network(imageUrl, width: 96, height: 96, fit: BoxFit.cover, errorBuilder: (_, __, ___) => _buildLogoFallback(title)),
            ),
          )
        else
          _buildLogoFallback(title),
        Text(title, style: const TextStyle(fontSize: 24, fontWeight: FontWeight.bold, color: Color(0xFF1E293B))),
        const SizedBox(height: 4),
        Text(subtitle, style: const TextStyle(fontSize: 14, color: Color(0xFF9CA3AF))),
      ],
    );
  }

  Widget _buildLogoFallback(String title) {
    return Container(
      width: 96,
      height: 96,
      margin: const EdgeInsets.only(bottom: 12),
      decoration: BoxDecoration(
        gradient: const LinearGradient(colors: [Color(0xFF9879b6), Color(0xFF7c5ea8)], begin: Alignment.topLeft, end: Alignment.bottomRight),
        borderRadius: BorderRadius.circular(16),
      ),
      child: const Icon(Icons.restaurant, size: 48, color: Colors.white),
    );
  }

  Widget _buildSection({required IconData icon, required String title, required String body}) {
    return Container(
      margin: const EdgeInsets.only(bottom: 20),
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16), boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.04), blurRadius: 8, offset: const Offset(0, 2))]),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(children: [
            Icon(icon, color: const Color(0xFF9879b6), size: 20),
            const SizedBox(width: 8),
            Text(title, style: const TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Color(0xFF1E293B))),
          ]),
          const SizedBox(height: 12),
          Text(body, style: const TextStyle(fontSize: 14, height: 1.8, color: Color(0xFF6B7280)), textAlign: TextAlign.right),
        ],
      ),
    );
  }

  Widget _buildStatCard({required IconData icon, required String title, required String body}) {
    final iconMap = {'رؤيتنا': Icons.track_changes, 'رسالتنا': Icons.favorite};
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.grey.shade50,
        borderRadius: BorderRadius.circular(16),
      ),
      child: Column(children: [
        Container(width: 48, height: 48, decoration: BoxDecoration(color: Colors.white, shape: BoxShape.circle, boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.04), blurRadius: 4, offset: const Offset(0, 1))]), child: Icon(iconMap[title] ?? icon, color: const Color(0xFF9879b6), size: 24)),
        const SizedBox(height: 8),
        Text(title, style: const TextStyle(fontSize: 14, fontWeight: FontWeight.bold, color: Color(0xFF1E293B))),
        const SizedBox(height: 4),
        Text(body, style: const TextStyle(fontSize: 11, color: Color(0xFF9CA3AF)), textAlign: TextAlign.center, maxLines: 2, overflow: TextOverflow.ellipsis),
      ]),
    );
  }

  Widget _buildValuesSection(List values) {
    return Container(
      margin: const EdgeInsets.only(bottom: 20),
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16), boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.04), blurRadius: 8, offset: const Offset(0, 2))]),
      child: Column(crossAxisAlignment: CrossAxisAlignment.start, children: [
        Row(children: const [
          Icon(Icons.star, color: Color(0xFF9879b6), size: 20),
          SizedBox(width: 8),
          Text('قيمنا الأساسية', style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Color(0xFF1E293B))),
        ]),
        const SizedBox(height: 12),
        ...values.asMap().entries.map((entry) {
          final item = entry.value;
          final displayValue = item is Map ? (item['value'] ?? item['key'] ?? item['name'] ?? item['label'] ?? '') : item;
          return Padding(
            padding: const EdgeInsets.only(bottom: 10),
            child: Row(children: [
              const Icon(Icons.check_circle, color: Colors.green, size: 16),
              const SizedBox(width: 8),
              Expanded(child: Text(displayValue.toString(), style: const TextStyle(fontSize: 14, color: Color(0xFF6B7280)), textAlign: TextAlign.right)),
            ]),
          );
        }),
      ]),
    );
  }

  Widget _buildStatsSection(List stats) {
    return Container(
      margin: const EdgeInsets.only(bottom: 20),
      child: GridView.builder(
        shrinkWrap: true,
        physics: const NeverScrollableScrollPhysics(),
        gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(crossAxisCount: 2, crossAxisSpacing: 12, mainAxisSpacing: 12, childAspectRatio: 1.2),
        itemCount: stats.length,
        itemBuilder: (context, index) {
          final stat = stats[index] as Map;
          final key = stat['key'] ?? stat['title'] ?? stat['label'] ?? '';
          final value = stat['value'] ?? stat['count'] ?? stat['number'] ?? stat['amount'] ?? '';
          final displayValue = num.tryParse(value.toString()) != null ? num.parse(value.toString()).toStringAsFixed(0) : value.toString();
          final keyLower = key.toString().toLowerCase();
          IconData icon = Icons.pie_chart;
          if (keyLower.contains('عام') || keyLower.contains('سنة') || keyLower.contains('year')) icon = Icons.calendar_today;
          if (keyLower.contains('منتج') || keyLower.contains('product')) icon = Icons.inventory_2;
          if (keyLower.contains('عميل') || keyLower.contains('customer')) icon = Icons.people;
          if (keyLower.contains('شريك') || keyLower.contains('partner')) icon = Icons.handshake;
          if (keyLower.contains('طلب') || keyLower.contains('order')) icon = Icons.shopping_cart;

          return Container(
            padding: const EdgeInsets.all(16),
            decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(16), boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.04), blurRadius: 8, offset: const Offset(0, 2))]),
            child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
              Text(displayValue, style: const TextStyle(fontSize: 28, fontWeight: FontWeight.bold, color: Color(0xFF9879b6))),
              const SizedBox(height: 4),
              Text(key.toString(), style: const TextStyle(fontSize: 11, color: Color(0xFF9CA3AF)), textAlign: TextAlign.center, maxLines: 2, overflow: TextOverflow.ellipsis),
            ]),
          );
        },
      ),
    );
  }
}
