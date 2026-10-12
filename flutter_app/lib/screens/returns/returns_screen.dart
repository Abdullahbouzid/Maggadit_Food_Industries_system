import 'package:flutter/material.dart';

class ReturnsScreen extends StatefulWidget {
  final String token;
  final String userId;

  const ReturnsScreen({
    Key? key,
    required this.token,
    required this.userId,
  }) : super(key: key);

  @override
  _ReturnsScreenState createState() => _ReturnsScreenState();
}

class _ReturnsScreenState extends State<ReturnsScreen> {
  bool _isLoading = true;
  List<Map<String, dynamic>> _returns = [];

  @override
  void initState() {
    super.initState();
    _loadReturns();
  }

  Future<void> _loadReturns() async {
    await Future.delayed(const Duration(seconds: 1));
    setState(() {
      _returns = [
        {
          'invoice': 'Invoice #111',
          'order': 'Order #111',
          'returnDate': '2024-01-20',
          'reason': 'Product defect',
          'status': 'Approved',
          'refund': 125.50,
        },
        {
          'invoice': 'Invoice #112',
          'order': 'Order #112',
          'returnDate': '2024-01-18',
          'reason': 'Wrong item received',
          'status': 'Pending',
          'refund': 89.00,
        },
      ];
      _isLoading = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Returns & Refunds'),
        centerTitle: true,
      ),
      body: _isLoading
          ? Center(child: CircularProgressIndicator())
          : _returns.isEmpty
              ? Center(
                  child: Column(
                    mainAxisAlignment: MainAxisAlignment.center,
                    children: [
                      Icon(
                        Icons.remove_shopping_cart_outlined,
                        size: 64,
                        color: Colors.grey[400],
                      ),
                      SizedBox(height: 16),
                      Text(
                        'No returns yet',
                        style: TextStyle(
                          fontSize: 18,
                          fontWeight: FontWeight.w500,
                        ),
                      ),
                    ],
                  ),
                )
              : ListView.builder(
                  padding: EdgeInsets.all(16),
                  itemCount: _returns.length,
                  itemBuilder: (context, index) {
                    final r = _returns[index];
                    return _ReturnCard(returnItem: r);
                  },
                ),
    );
  }
}

class _ReturnCard extends StatelessWidget {
  final Map<String, dynamic> returnItem;

  const _ReturnCard({required this.returnItem});

  @override
  Widget build(BuildContext context) {
    final Color statusColor = _returnStatusColor(returnItem['status']);

    return Card(
      margin: EdgeInsets.only(bottom: 12),
      child: Container(
        padding: EdgeInsets.all(16),
        decoration: BoxDecoration(
          color: Colors.white,
          borderRadius: BorderRadius.circular(12),
          boxShadow: [
            BoxShadow(
              color: Colors.black.withOpacity(0.05),
              blurRadius: 6,
              offset: Offset(0, 3),
            ),
          ],
        ),
        child: Row(
          children: [
            Container(
              padding: EdgeInsets.all(8),
              decoration: BoxDecoration(
                color: statusColor.withOpacity(0.1),
                borderRadius: BorderRadius.circular(8),
              ),
              child: Icon(
                _returnStatusIcon(returnItem['status']),
                color: statusColor,
              ),
            ),
            SizedBox(width: 12),
            Expanded(
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  Text(
                    returnItem['invoice'],
                    style: TextStyle(
                      fontSize: 14,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                  Text(
                    returnItem['order'],
                    style: TextStyle(
                      fontSize: 12,
                      color: Colors.grey[600],
                    ),
                  ),
                ],
              ),
            ),
            Column(
              crossAxisAlignment: CrossAxisAlignment.end,
              children: [
                Text(
                  returnItem['refund'].toStringAsFixed(2) + ' SAR',
                  style: TextStyle(
                    fontSize: 14,
                    fontWeight: FontWeight.bold,
                    color: statusColor,
                  ),
                ),
                SizedBox(height: 4),
                Text(
                  returnItem['returnDate'],
                  style: TextStyle(
                    fontSize: 12,
                    color: Colors.grey[500],
                  ),
                ),
              ],
            ),
            Icon(Icons.chevron_right, color: Colors.grey[400]),
          ],
        ),
      ),
    );
  }

  Color _returnStatusColor(String status) {
    switch (status) {
      case 'Approved':
        return Colors.green;
      case 'Pending':
        return Colors.orange;
      case 'Rejected':
        return Colors.red;
      default:
        return Colors.grey;
    }
  }

  IconData _returnStatusIcon(String status) {
    switch (status) {
      case 'Approved':
        return Icons.check_circle_outline;
      case 'Pending':
        return Icons.hourglass_empty;
      case 'Rejected':
        return Icons.close;
      default:
        return Icons.help_outline;
    }
  }
}