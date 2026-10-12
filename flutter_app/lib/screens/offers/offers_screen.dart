import 'package:flutter/material.dart';
import '../../services/api_service.dart';
import '../../services/notifications_service.dart';
import '../../widgets/product_offer_cards.dart';

class OffersScreen extends StatefulWidget {
  final String token;
  final String userId;

  const OffersScreen({
    Key? key,
    required this.token,
    required this.userId,
  }) : super(key: key);

  @override
  _OffersScreenState createState() => _OffersScreenState();
}

class _OffersScreenState extends State<OffersScreen> {
  final NotificationsService _notificationsService = NotificationsService();
  final ApiService _apiService = ApiService();
  late Future<List<Map<String, dynamic>>> _offersFuture;
  bool _isLoading = true;

  @override
  void initState() {
    super.initState();
    _initService();
    _loadOffers();
  }

  void _initService() {
    _notificationsService.init(context, widget.token);
  }

  Future<void> _loadOffers() async {
    setState(() => _isLoading = true);
    try {
      final response = await _apiService.get('/api/offers');
      setState(() {
        _offersFuture = Future.value(
          ((response['data'] as List?)?.cast<Map<String, dynamic>>()) ?? <Map<String, dynamic>>[]
        );
        _isLoading = false;
      });
    } catch (e) {
      setState(() => _isLoading = false);
      if (!mounted) return;
      _showToast('Error loading offers', isError: true);
    }
  }

  void _showToast(String message, {bool isError = false}) {
    if (!mounted) return;
    ScaffoldMessenger.of(context).showSnackBar(
      SnackBar(
        content: Text(message),
        backgroundColor: isError ? Colors.red : const Color(0xFF9879b6),
        behavior: SnackBarBehavior.floating,
        shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(10)),
        duration: const Duration(seconds: 2),
      ),
    );
  }

  @override
  void dispose() {
    _notificationsService.stopPolling();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('العروض الحصرية',
            style: const TextStyle(
                fontSize: 20,
                fontWeight: FontWeight.bold,
                color: Color(0xFF1E293B))),
        centerTitle: true,
        backgroundColor: Colors.white,
        elevation: 0,
        leading: IconButton(
          icon: const Icon(Icons.arrow_back_ios_new,
              color: Color(0xFF1E293B), size: 20),
          onPressed: () => Navigator.of(context).pop(),
        ),
      ),
      body: _isLoading
          ? const Center(child: CircularProgressIndicator())
          : RefreshIndicator(
              onRefresh: _loadOffers,
              child: SingleChildScrollView(
                physics: const BouncingScrollPhysics(),
                padding: const EdgeInsets.all(20),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    _buildHeader(),
                    const SizedBox(height: 30),
                    _buildOffersGrid(),
                  ],
                ),
              ),
            ),
    );
  }

  Widget _buildHeader() {
    return Row(
      mainAxisAlignment: MainAxisAlignment.spaceBetween,
      children: [
        const Text(
          'العروض الحصرية',
          style: TextStyle(
            fontSize: 18,
            fontWeight: FontWeight.bold,
            color: Color(0xFF1E293B),
          ),
        ),
        Container(
          padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
          decoration: BoxDecoration(
            color: Color(0xFFF1F5F9),
            borderRadius: BorderRadius.circular(20),
          ),
          child: const Text(
            'لا توجد عروض',
            style: TextStyle(
              fontSize: 12,
              fontWeight: FontWeight.w500,
              color: Color(0xFF9CA3AF),
            ),
          ),
        ),
      ],
    );
  }

  Widget _buildOffersGrid() {
    return FutureBuilder<List<Map<String, dynamic>>>(
      future: _offersFuture,
      builder: (context, snapshot) {
        if (snapshot.connectionState == ConnectionState.waiting) {
          return const Center(child: CircularProgressIndicator());
        }
        if (snapshot.hasError) {
          return const Center(child: Text('Error loading offers'));
        }
        final offers = snapshot.data ?? [];
        if (offers.isEmpty) {
          return const Center(
            child: Column(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(Icons.local_offer_outlined, size: 64, color: Color(0xFF9CA3AF)),
                const SizedBox(height: 16),
                Text('لا توجد عروض متاحة حالياً'),
              ],
            ),
          );
        }
        return GridView.builder(
          shrinkWrap: true,
          physics: const NeverScrollableScrollPhysics(),
          gridDelegate: const SliverGridDelegateWithFixedCrossAxisCount(
            crossAxisCount: 2,
            crossAxisSpacing: 16,
            mainAxisSpacing: 16,
            childAspectRatio: 0.78,
          ),
          itemCount: offers.length,
          itemBuilder: (context, index) {
            final offer = offers[index];
            return _buildOfferCard(offer);
          },
        );
      },
    );
  }

  Widget _buildOfferCard(Map<String, dynamic> offer) {
    final discount = (offer['discount_percentage'] as int? ?? 0);
    final oldPrice = (offer['old_price'] as num? ?? 0);
    final newPrice = (offer['new_price'] as num? ?? 0);
    final hasOldPrice = oldPrice > 0;
    final hasDiscount = discount > 0;

    return Container(
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(24),
        border: Border.all(color: const Color(0xFFE2E8F0)),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 8,
            offset: const Offset(0, 2),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Image
          Container(
            height: 140,
            width: double.infinity,
            decoration: BoxDecoration(
              color: const Color(0xFFF1F5F9),
              borderRadius: const BorderRadius.only(
                topLeft: Radius.circular(24),
                topRight: Radius.circular(24),
              ),
            ),
            child: offer['image'] != null && offer['image']!.isNotEmpty
                ? ClipRRect(
                    borderRadius: const BorderRadius.only(
                      topLeft: Radius.circular(24),
                      topRight: Radius.circular(24),
                    ),
                    child: Image.network(
                      offer['image']!,
                      fit: BoxFit.cover,
                    ),
                  )
                : Container(
                    color: const Color(0xFFF9FAFB),
                    child: const Icon(Icons.local_offer,
                        size: 60, color: Color(0xFF9CA3AF)),
                  ),
          ),
          // Details
          Padding(
            padding: const EdgeInsets.all(16),
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                // Title
                Text(
                  offer['title'] ?? 'عرض خاص',
                  style: const TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                    color: Color(0xFF1E293B),
                  ),
                  maxLines: 2,
                  overflow: TextOverflow.ellipsis,
                ),
                const SizedBox(height: 6),
                // Discount badge
                if (hasDiscount)
                  Container(
                    padding: const EdgeInsets.symmetric(
                        horizontal: 8, vertical: 2),
                    decoration: BoxDecoration(
                      color: Colors.red.shade100,
                      borderRadius: BorderRadius.circular(10),
                    ),
                    child: Text(
                      '-${discount}%',
                      style: TextStyle(
                        fontSize: 11,
                        fontWeight: FontWeight.bold,
                        color: Colors.red.shade800,
                      ),
                    ),
                  ),
                const SizedBox(height: 8),
                // Description
                if (offer['description'] != null &&
                    offer['description']!.isNotEmpty)
                  Text(
                    offer['description'],
                    style: const TextStyle(
                      fontSize: 13,
                      color: Color(0xFF374151),
                      height: 1.4,
                    ),
                    maxLines: 2,
                    overflow: TextOverflow.ellipsis,
                  ),
                const SizedBox(height: 12),
                // Price row
                Row(
                  children: [
                    if (hasOldPrice)
                      Text(
                        '${oldPrice.toStringAsFixed(2)} ر.س',
                        style: TextStyle(
                          fontSize: 13,
                          color: Colors.grey[500],
                          decoration: TextDecoration.lineThrough,
                          decorationColor: Colors.grey[400],
                          decorationThickness: 1.5,
                        ),
                      ),
                    if (hasOldPrice) const SizedBox(width: 8),
                    Text(
                      '${newPrice.toStringAsFixed(2)} ر.س',
                      style: const TextStyle(
                        fontSize: 16,
                        fontWeight: FontWeight.bold,
                        color: Color(0xFF9879b6),
                      ),
                    ),
                    const Spacer(),
                    // Add to cart button
                    Container(
                      padding: const EdgeInsets.symmetric(
                          horizontal: 14, vertical: 8),
                      decoration: BoxDecoration(
                        color: const Color(0xFF9879b6),
                        borderRadius: BorderRadius.circular(24),
                      ),
                      child: const Text(
                        'إضافة',
                        style: TextStyle(
                          fontSize: 13,
                          fontWeight: FontWeight.w600,
                          color: Colors.white,
                        ),
                      ),
                    ),
                  ],
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }
}