import 'package:flutter/material.dart';
import '../../services/api_service.dart';

class FaqScreen extends StatefulWidget {
  const FaqScreen({Key? key}) : super(key: key);

  @override
  State<FaqScreen> createState() => _FaqScreenState();
}

class _FaqScreenState extends State<FaqScreen> {
  final ApiService _apiService = ApiService();
  late Future<List<Map<String, dynamic>>> _faqsFuture;
  String _searchQuery = '';
  String _selectedCategory = 'all';

  @override
  void initState() {
    super.initState();
    _faqsFuture = _loadFaqs();
  }

  Future<List<Map<String, dynamic>>> _loadFaqs() async {
    try {
      final response = await _apiService.get('/customer/faq');
      final faqs = response['faq'] as List? ?? [];
      return faqs.cast<Map<String, dynamic>>();
    } catch (e) {
      return [];
    }
  }

  List<Map<String, dynamic>> _filterFaqs(List<Map<String, dynamic>> faqs) {
    return faqs.where((faq) {
      final matchesSearch = _searchQuery.isEmpty ||
          (faq['question'] as String? ?? '').toLowerCase().contains(_searchQuery.toLowerCase()) ||
          (faq['answer'] as String? ?? '').toLowerCase().contains(_searchQuery.toLowerCase());

      final faqCategory = faq['category'] as String? ?? 'عام';
      final matchesCategory = _selectedCategory == 'all' || faqCategory == _selectedCategory;

      return matchesSearch && matchesCategory;
    }).toList();
  }

  List<String> _getCategories(List<Map<String, dynamic>> faqs) {
    final categories = faqs.map((f) => f['category'] as String? ?? 'عام').toSet().toList();
    return ['الكل', ...categories];
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
      body: Column(
        children: [
          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              children: [
                // Search bar
                Container(
                  decoration: BoxDecoration(color: Colors.white, borderRadius: BorderRadius.circular(12), boxShadow: [BoxShadow(color: Colors.black.withOpacity(0.04), blurRadius: 8, offset: const Offset(0, 2))]),
                  child: TextField(
                    onChanged: (value) => setState(() => _searchQuery = value),
                    decoration: InputDecoration(
                      hintText: 'ابحث في الأسئلة الشائعة...',
                      hintStyle: TextStyle(color: Colors.grey.shade400, fontSize: 13),
                      prefixIcon: const Icon(Icons.search, color: Color(0xFF9CA3AF), size: 20),
                      border: InputBorder.none,
                      contentPadding: const EdgeInsets.symmetric(horizontal: 16, vertical: 14),
                    ),
                  ),
                ),
                const SizedBox(height: 12),
                // Category filter
                FutureBuilder<List<Map<String, dynamic>>>(
                  future: _faqsFuture,
                  builder: (context, snapshot) {
                    final faqs = snapshot.data ?? [];
                    final categories = _getCategories(faqs);
                    return SingleChildScrollView(
                      scrollDirection: Axis.horizontal,
                      child: Row(children: categories.map((cat) {
                        final isActive = _selectedCategory == (cat == 'الكل' ? 'all' : cat);
                        return Padding(
                          padding: const EdgeInsets.only(left: 8),
                          child: ChoiceChip(
                            label: Text(cat, style: TextStyle(fontSize: 12, fontWeight: isActive ? FontWeight.bold : FontWeight.normal, color: isActive ? Colors.white : const Color(0xFF6B7280))),
                            selected: isActive,
                            onSelected: (_) => setState(() => _selectedCategory = cat == 'الكل' ? 'all' : cat),
                            backgroundColor: Colors.white,
                            selectedColor: const Color(0xFF9879b6),
                            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(20)),
                            side: BorderSide(color: isActive ? const Color(0xFF9879b6) : const Color(0xFFE2E8F0)),
                            padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                          ),
                        );
                      }).toList()),
                    );
                  },
                ),
              ],
            ),
          ),
          // FAQ list
          Expanded(
            child: FutureBuilder<List<Map<String, dynamic>>>(
              future: _faqsFuture,
              builder: (context, snapshot) {
                if (snapshot.connectionState == ConnectionState.waiting) {
                  return const Center(child: CircularProgressIndicator());
                }
                final allFaqs = snapshot.data ?? [];
                final faqs = _filterFaqs(allFaqs);

                if (faqs.isEmpty) {
                  return Center(
                    child: Column(mainAxisAlignment: MainAxisAlignment.center, children: [
                      Icon(Icons.question_answer_outlined, size: 64, color: Colors.grey.shade300),
                      const SizedBox(height: 16),
                      Text(_searchQuery.isNotEmpty ? 'لا توجد أسئلة مطابقة لبحثك' : 'لا توجد أسئلة شائعة', style: const TextStyle(fontSize: 16, color: Color(0xFF9CA3AF))),
                    ]),
                  );
                }

                return ListView.separated(
                  padding: const EdgeInsets.symmetric(horizontal: 16),
                  itemCount: faqs.length,
                  separatorBuilder: (context, index) => const SizedBox(height: 12),
                  itemBuilder: (context, index) {
                    final faq = faqs[index];
                    return _FaqItem(faq: faq);
                  },
                );
              },
            ),
          ),
        ],
      ),
    );
  }
}

class _FaqItem extends StatefulWidget {
  final Map<String, dynamic> faq;

  const _FaqItem({Key? key, required this.faq}) : super(key: key);

  @override
  State<_FaqItem> createState() => _FaqItemState();
}

class _FaqItemState extends State<_FaqItem> with SingleTickerProviderStateMixin {
  bool _isOpen = false;
  late AnimationController _controller;
  late Animation<double> _expandAnimation;

  @override
  void initState() {
    super.initState();
    _controller = AnimationController(duration: const Duration(milliseconds: 300), vsync: this);
    _expandAnimation = CurvedAnimation(parent: _controller, curve: Curves.easeInOut);
  }

  @override
  void dispose() {
    _controller.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Card(
      color: Colors.white,
      elevation: 0,
      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
      child: Column(
        children: [
          GestureDetector(
            onTap: () {
              setState(() {
                _isOpen = !_isOpen;
                if (_isOpen) {
                  _controller.forward();
                } else {
                  _controller.reverse();
                }
              });
            },
            child: Padding(
              padding: const EdgeInsets.all(16),
              child: Row(children: [
                Expanded(child: Text(widget.faq['question'] ?? '', style: const TextStyle(fontSize: 14, fontWeight: FontWeight.w600, color: Color(0xFF1E293B))),),
              AnimatedRotation(
                duration: const Duration(milliseconds: 300),
                turns: _isOpen ? 0.5 : 0,
                child: const Icon(Icons.keyboard_arrow_down, color: Color(0xFF9CA3AF), size: 20),
              ),
              ]),
            ),
          ),
          SizeTransition(sizeFactor: _expandAnimation, axisAlignment: 0, child: _isOpen ? Padding(padding: const EdgeInsets.fromLTRB(16, 0, 16, 16), child: Text(widget.faq['answer'] ?? '', style: const TextStyle(fontSize: 13, height: 1.6, color: Color(0xFF6B7280)), textAlign: TextAlign.right)) : null),
        ],
      ),
    );
  }
}
