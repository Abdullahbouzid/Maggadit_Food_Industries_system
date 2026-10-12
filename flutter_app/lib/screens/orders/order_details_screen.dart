import 'package:flutter/material.dart';

class OrderDetailsScreen extends StatefulWidget {
  final int orderId;
  final String token;
  final String userId;
  final Map<String, dynamic>? initialArgs;

  const OrderDetailsScreen({
    Key? key,
    this.orderId = 0,
    required this.token,
    required this.userId,
    this.initialArgs,
  }) : super(key: key);

  @override
  _OrderDetailsScreenState createState() => _OrderDetailsScreenState();
}

class _OrderDetailsScreenState extends State<OrderDetailsScreen> {
  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: Text('Order Details'),
        centerTitle: true,
      ),
      body: Center(
        child: Column(
          mainAxisAlignment: MainAxisAlignment.center,
          children: [
            Icon(
              Icons.info_outline,
              size: 64,
              color: Colors.grey[400],
            ),
            SizedBox(height: 16),
            Text(
              'Order Details (ID: ${widget.orderId})',
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.w500,
              ),
            ),
            SizedBox(height: 12),
            Text(
              'This screen is under construction. Integration with Laravel API coming soon.',
              style: TextStyle(
                fontSize: 14,
                color: Colors.grey[600],
              ),
              textAlign: TextAlign.center,
            ),
          ],
        ),
      ),
    );
  }
}