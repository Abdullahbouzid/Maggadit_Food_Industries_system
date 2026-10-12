import 'package:flutter/material.dart';
import '../models/order_model.dart';
import '../services/api_service.dart';
import 'orders/order_details_screen.dart';

class OrderHistoryScreen extends StatefulWidget {
  final String token;
  final String userId;

  const OrderHistoryScreen({
    Key? key,
    required this.token,
    required this.userId,
  }) : super(key: key);

  @override
  _OrderHistoryScreenState createState() => _OrderHistoryScreenState();
}

class _OrderHistoryScreenState extends State<OrderHistoryScreen> {
  List<OrderModel> _orders = [];
  bool _isLoading = false;
  bool _hasMore = true;
  int _currentPage = 1;

  @override
  void initState() {
    super.initState();
    _loadOrders();
  }

  Future<void> _loadOrders() async {
    if (_isLoading) return;
    setState(() => _isLoading = true);

    try {
      final api = ApiService();
      final orders = await api.getOrderHistory(page: _currentPage);
      if (orders.isEmpty) {
        _hasMore = false;
      } else {
        setState(() {
          _orders.addAll(orders);
          _currentPage++;
        });
      }
    } catch (e) {
      // Silently fail
    } finally {
      setState(() => _isLoading = false);
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('My Orders'),
        centerTitle: true,
      ),
      body: _orders.isEmpty
          ? _buildEmptyState()
          : RefreshIndicator(
              onRefresh: _refreshOrders,
              child: NotificationListener<ScrollNotification>(
                onNotification: (scrollInfo) {
                  if (!_hasMore || _isLoading) return false;
                  final maxScroll = scrollInfo.metrics.maxScrollExtent;
                  final currentScroll = scrollInfo.metrics.pixels;
                  if (currentScroll >= maxScroll * 0.8) {
                    _loadOrders();
                  }
                  return true;
                },
                child: ListView.separated(
                  padding: EdgeInsets.all(16),
                  itemCount: _orders.length + (_hasMore ? 1 : 0),
                  separatorBuilder: (context, index) => SizedBox(height: 12),
                  itemBuilder: (context, index) {
                    if (index == _orders.length) {
                      return _isLoading
                          ? Center(child: CircularProgressIndicator())
                          : SizedBox.shrink();
                    }
                    final order = _orders[index];
                    return _OrderCard(
                      order: order,
                      token: widget.token,
                      userId: widget.userId,
                    );
                  },
                ),
              ),
            ),
    );
  }

  Future<void> _refreshOrders() async {
    setState(() {
      _orders = [];
      _hasMore = true;
      _currentPage = 1;
    });
    await _loadOrders();
  }

  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            Icons.shopping_bag_outlined,
            size: 64,
            color: Colors.grey[400],
          ),
          SizedBox(height: 16),
          Text(
            'No orders yet',
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.w500,
            ),
          ),
          Text(
            'Start shopping to see your orders here',
            style: TextStyle(
              fontSize: 14,
              color: Colors.grey[600],
            ),
          ),
          SizedBox(height: 24),
          ElevatedButton(
            onPressed: () => Navigator.of(context).pop(),
            child: Text('Continue Shopping'),
          ),
        ],
      ),
    );
  }
}

class _OrderCard extends StatelessWidget {
  final OrderModel order;
  final String token;
  final String userId;

  const _OrderCard({
    required this.order,
    required this.token,
    required this.userId,
  });

  @override
  Widget build(BuildContext context) {
    return InkWell(
      onTap: () {
        Navigator.of(context).push(
          MaterialPageRoute(
            builder: (context) => OrderDetailsScreen(
              orderId: order.id,
              token: token,
              userId: userId,
            ),
          ),
        );
      },
      borderRadius: BorderRadius.circular(12),
      child: Container(
        padding: EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.05),
              blurRadius: 8,
              offset: Offset(0, 2),
            ),
          ],
        ),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Expanded(
                  child: Column(
                    crossAxisAlignment: CrossAxisAlignment.start,
                    children: [
                      Text(
                        'Order #${order.orderNumber}',
                        style: TextStyle(
                          fontSize: 16,
                          fontWeight: FontWeight.bold,
                        ),
                      ),
                      SizedBox(height: 4),
                      Text(
                        '${order.createdAt.substring(0, 10)}',
                        style: TextStyle(
                          fontSize: 12,
                          color: Colors.grey[600],
                        ),
                      ),
                    ],
                  ),
                ),
                Container(
                  padding: EdgeInsets.symmetric(horizontal: 8, vertical: 4),
                  decoration: BoxDecoration(
                    color: _getStatusColor(order.status).withOpacity(0.1),
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Text(
                    order.statusLabel,
                    style: TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.bold,
                      color: _getStatusColor(order.status),
                    ),
                  ),
                ),
              ],
            ),
            SizedBox(height: 12),
            Row(
              mainAxisAlignment: MainAxisAlignment.spaceBetween,
              children: [
                Text(
                  '${order.itemsCount} items',
                  style: TextStyle(
                    fontSize: 14,
                    color: Colors.grey[600],
                  ),
                ),
                Text(
                  'Total: ${order.totalAmount.toStringAsFixed(2)} SAR',
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                  ),
                ),
              ],
            ),
            SizedBox(height: 12),
            LinearProgressIndicator(
              value: order.progress / 100,
              backgroundColor: Colors.grey[300]!,
              valueColor: AlwaysStoppedAnimation<Color>(
                _getProgressColor(order.progress),
              ),
              minHeight: 4,
              borderRadius: BorderRadius.circular(2),
            ),
          ],
        ),
      ),
    );
  }

  Color _getStatusColor(String status) {
    switch (status.toLowerCase()) {
      case 'new':
        return Colors.blue;
      case 'pending_review':
        return Colors.orange;
      case 'customer_approved':
        return Colors.green;
      case 'customer_requested_changes':
        return Colors.purple;
      case 'customer_rejected':
        return Colors.red;
      case 'delivered':
        return Colors.teal;
      default:
        return Colors.grey;
    }
  }

  Color _getProgressColor(int progress) {
    if (progress < 33) return Colors.orange;
    if (progress < 66) return Colors.blue;
    return Colors.green;
  }
}
