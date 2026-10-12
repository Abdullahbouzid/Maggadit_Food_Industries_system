import 'package:flutter/material.dart';
import '../models/notification_model.dart';
import '../services/api_service.dart';

class NotificationsScreen extends StatefulWidget {
  final String token;
  final String userId;

  const NotificationsScreen({
    Key? key,
    required this.token,
    required this.userId,
  }) : super(key: key);

  @override
  _NotificationsScreenState createState() => _NotificationsScreenState();
}

class _NotificationsScreenState extends State<NotificationsScreen> {
  List<NotificationModel> _notifications = [];
  bool _isLoading = false;
  bool _hasMore = true;
  int _currentPage = 1;
  final int _pageSize = 20;

  @override
  void initState() {
    super.initState();
    _loadNotifications();
  }

  Future<void> _loadNotifications() async {
    if (_isLoading) return;
    setState(() => _isLoading = true);

    try {
      final api = ApiService();
      final notifications = await api.getNotifications(page: _currentPage);
      setState(() {
        _notifications = notifications;
        _hasMore = notifications.length >= _pageSize;
        _currentPage++;
      });
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
        title: Text('Notifications'),
        centerTitle: true,
        actions: [
          IconButton(
            icon: Icon(Icons.refresh),
            onPressed: () => setState(() => _loadNotifications()),
          ),
        ],
      ),
      body: _notifications.isEmpty
          ? _buildEmptyState()
          : RefreshIndicator(
              onRefresh: _refreshNotifications,
              child: ListView.separated(
                padding: EdgeInsets.all(16),
                itemCount: _notifications.length + (_hasMore ? 1 : 0),
                separatorBuilder: (context, index) => Divider(height: 1),
                itemBuilder: (context, index) {
                  if (index == _notifications.length) {
                    return _isLoading
                        ? Center(child: CircularProgressIndicator())
                        : SizedBox.shrink();
                  }
                  return _NotificationItem(notification: _notifications[index]);
                },
              ),
            ),
    );
  }

  Future<void> _refreshNotifications() async {
    setState(() {
      _notifications = [];
      _hasMore = true;
      _currentPage = 1;
    });
    await _loadNotifications();
  }

  Widget _buildEmptyState() {
    return Center(
      child: Column(
        mainAxisAlignment: MainAxisAlignment.center,
        children: [
          Icon(
            Icons.notifications_none,
            size: 64,
            color: Colors.grey[400],
          ),
          SizedBox(height: 16),
          Text(
            'No notifications yet',
            style: TextStyle(
              fontSize: 18,
              fontWeight: FontWeight.w500,
            ),
          ),
          Text(
            'You will receive notifications for orders, offers, and updates here',
            style: TextStyle(
              fontSize: 14,
              color: Colors.grey[600],
            ),
          ),
        ],
      ),
    );
  }
}

class _NotificationItem extends StatelessWidget {
  final NotificationModel notification;

  const _NotificationItem({required this.notification});

  @override
  Widget build(BuildContext context) {
    return Container(
      padding: EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(12),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 4,
            offset: Offset(0, 2),
          ),
        ],
      ),
      child: Row(
        children: [
          _buildNotificationIcon(notification.iconType),
          SizedBox(width: 12),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Text(
                  notification.subject,
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.w600,
                  ),
                ),
                SizedBox(height: 4),
                Text(
                  notification.body,
                  style: TextStyle(
                    fontSize: 14,
                    color: Colors.grey[700],
                  ),
                ),
                SizedBox(height: 6),
                Text(
                  _formatDate(notification.createdAt),
                  style: TextStyle(
                    fontSize: 12,
                    color: Colors.grey[500],
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  Widget _buildNotificationIcon(String type) {
    IconData icon;
    Color color;
    switch (type) {
      case 'offer':
        icon = Icons.local_offer;
        color = Colors.green;
        break;
      case 'delivery':
        icon = Icons.local_shipping;
        color = Colors.purple;
        break;
      case 'payment':
        icon = Icons.payment;
        color = Colors.blue;
        break;
      case 'receive':
        icon = Icons.inventory;
        color = Colors.orange;
        break;
      default:
        icon = Icons.notifications;
        color = Colors.grey;
    }
    return Container(
      padding: EdgeInsets.all(8),
      decoration: BoxDecoration(
        color: color.withOpacity(0.1),
        borderRadius: BorderRadius.circular(8),
      ),
      child: Icon(icon, color: color, size: 28),
    );
  }

  String _formatDate(String dateTime) {
    try {
      // Simple date formatting - in real app use intl package
      if (dateTime.length >= 10) {
        return dateTime.substring(0, 10).replaceAll('-', '/');
      }
      return dateTime;
    } catch (e) {
      return dateTime;
    }
  }
}
