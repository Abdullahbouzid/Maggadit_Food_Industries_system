import 'package:flutter/material.dart';

class FinancialStatementScreen extends StatefulWidget {
  final String token;
  final String userId;

  const FinancialStatementScreen({
    Key? key,
    required this.token,
    required this.userId,
  }) : super(key: key);

  @override
  _FinancialStatementScreenState createState() => _FinancialStatementScreenState();
}

class _FinancialStatementScreenState extends State<FinancialStatementScreen> {
  bool _isLoading = true;
  List<Map<String, dynamic>> _transactions = [];

  @override
  void initState() {
    super.initState();
    _loadTransactions();
  }

  Future<void> _loadTransactions() async {
    // Mock data - in real implementation, call API
    await Future.delayed(const Duration(seconds: 1));
    setState(() {
      _transactions = [
        {
          'type': 'Payment',
          'amount': -1250.50,
          'date': '2024-01-15',
          'description': 'Customer order payment',
          'status': 'Completed',
        },
        {
          'type': 'Payment',
          'amount': -890.00,
          'date': '2024-01-10',
          'description': 'Invoice payment',
          'status': 'Completed',
        },
        {
          'type': 'Receipt',
          'amount': 5000.00,
          'date': '2024-01-20',
          'description': 'Cash deposit',
          'status': 'Completed',
        },
        {
          'type': 'Expense',
          'amount': -350.00,
          'date': '2024-01-18',
          'description': 'Operating expenses',
          'status': 'Completed',
        },
      ];
      _isLoading = false;
    });
  }

  @override
  Widget build(BuildContext context) {
    final double total = _transactions.fold(0.0, (sum, t) => sum + (t['amount'] as num).toDouble());

    return Scaffold(
      appBar: AppBar(
        title: Text('Financial Statement'),
        centerTitle: true,
      ),
      body: _isLoading
          ? Center(child: CircularProgressIndicator())
          : SingleChildScrollView(
              padding: EdgeInsets.all(16),
              child: Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  _buildSummaryCard(total),
                  SizedBox(height: 24),
                  Text(
                  'Transaction History',
                  style: TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                  ),
                ),
                  SizedBox(height: 12),
                  ListView.builder(
                    shrinkWrap: true,
                    physics: NeverScrollableScrollPhysics(),
                    itemCount: _transactions.length,
                    itemBuilder: (context, index) {
                      final tx = _transactions[index];
                      return _TransactionTile(tx: tx);
                    },
                  ),
                ],
              ),
            ),
    );
  }

  Widget _buildSummaryCard(double total) {
    return Container(
      padding: EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 8,
            offset: Offset(0, 4),
          ),
        ],
      ),
      child: Column(
        children: [
          Text(
            'Total Balance',
            style: TextStyle(
              fontSize: 14,
              color: Colors.grey[600],
            ),
          ),
          SizedBox(height: 8),
          Text(
            '${total.toStringAsFixed(2)} SAR',
            style: TextStyle(
              fontSize: 32,
              fontWeight: FontWeight.bold,
              color: total >= 0 ? Colors.green : Colors.red,
            ),
          ),
        ],
      ),
    );
  }
}

class _TransactionTile extends StatelessWidget {
  final Map<String, dynamic> tx;

  const _TransactionTile({required this.tx});

  @override
  Widget build(BuildContext context) {
    return Container(
      margin: EdgeInsets.only(bottom: 8),
      padding: EdgeInsets.all(12),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(10),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.03),
            blurRadius: 4,
            offset: Offset(0, 2),
          ),
        ],
      ),
      child: Row(
        mainAxisAlignment: MainAxisAlignment.spaceBetween,
        children: [
          Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                tx['description'] ?? 'Unknown',
                style: TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.w500,
                ),
              ),
              Text(
                tx['date'],
                style: TextStyle(
                  fontSize: 12,
                  color: Colors.grey[500],
                ),
              ),
            ],
          ),
          Column(
            crossAxisAlignment: CrossAxisAlignment.end,
            children: [
              Text(
                '${tx['amount'] > 0 ? '+' : ''}${tx['amount'].toStringAsFixed(2)} SAR',
                style: TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.bold,
                  color: tx['amount'] >= 0 ? Colors.green : Colors.red,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }
}
