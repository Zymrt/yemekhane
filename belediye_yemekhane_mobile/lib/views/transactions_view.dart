// lib/views/transactions_view.dart
import 'package:flutter/material.dart';
import 'package:get/get.dart';
import '../services/api_service.dart';

class TransactionItem {
  final String type;        // deposit / purchase vs.
  final double amount;
  final DateTime date;
  final String description;

  TransactionItem({
    required this.type,
    required this.amount,
    required this.date,
    required this.description,
  });

  factory TransactionItem.fromJson(Map<String, dynamic> json) {
    final rawAmount = json['amount'] ?? 0;
    final parsedAmount = double.tryParse(rawAmount.toString()) ?? 0.0;

    DateTime parsedDate =
        DateTime.tryParse(json['created_at']?.toString() ?? '') ??
            DateTime.now();

    return TransactionItem(
      type: json['type']?.toString() ?? 'unknown',
      amount: parsedAmount,
      date: parsedDate,
      description: json['description']?.toString() ?? '',
    );
  }
}

class TransactionsView extends StatefulWidget {
  const TransactionsView({super.key});

  @override
  State<TransactionsView> createState() => _TransactionsViewState();
}

class _TransactionsViewState extends State<TransactionsView> {
  final ApiService _api = Get.find<ApiService>();

  bool _loading = true;
  String? _error;
  List<TransactionItem> _items = [];

  @override
  void initState() {
    super.initState();
    _loadTransactions();
  }

  Future<void> _loadTransactions() async {
    setState(() {
      _loading = true;
      _error = null;
    });

    try {
      final response = await _api.getTransactions();
      final data = response.data;

      List listJson = [];
      if (data is List) {
        // direkt liste dönüyorsa
        listJson = data;
      } else if (data is Map && data['data'] is List) {
        // paginate/laravel resource şeklindeyse
        listJson = data['data'] as List;
      }

      _items = listJson
          .whereType<Map>()
          .map((e) => TransactionItem.fromJson(
              Map<String, dynamic>.from(e as Map)))
          .toList();
    } catch (e) {
      _error = 'Hesap hareketleri alınırken hata oluştu.';
      print('Transactions error: $e');
    } finally {
      setState(() {
        _loading = false;
      });
    }
  }

  Color _amountColor(double amount) {
    if (amount > 0) return Colors.green;
    if (amount < 0) return Colors.red;
    return Colors.grey.shade700;
  }

  String _formatAmount(double amount) {
    final sign = amount > 0 ? '+' : '';
    return '$sign${amount.toStringAsFixed(2)} TL';
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Hesap Hareketleri'),
        backgroundColor: Colors.teal,
      ),
      body: RefreshIndicator(
        onRefresh: _loadTransactions,
        child: _loading
            ? const Center(child: CircularProgressIndicator())
            : _error != null
                ? ListView(
                    children: [
                      Padding(
                        padding: const EdgeInsets.all(24.0),
                        child: Center(
                          child: Text(
                            _error!,
                            style: const TextStyle(color: Colors.red),
                            textAlign: TextAlign.center,
                          ),
                        ),
                      ),
                    ],
                  )
                : _items.isEmpty
                    ? ListView(
                        children: const [
                          SizedBox(height: 80),
                          Center(
                            child: Text(
                              'Henüz hiç hareket yok.',
                              style: TextStyle(color: Colors.grey),
                            ),
                          ),
                        ],
                      )
                    : ListView.separated(
                        padding: const EdgeInsets.all(16),
                        itemBuilder: (context, index) {
                          final tx = _items[index];
                          return ListTile(
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(12),
                            ),
                            tileColor: Colors.white,
                            leading: CircleAvatar(
                              backgroundColor:
                                  _amountColor(tx.amount).withOpacity(0.1),
                              child: Icon(
                                tx.amount >= 0
                                    ? Icons.arrow_downward
                                    : Icons.arrow_upward,
                                color: _amountColor(tx.amount),
                              ),
                            ),
                            title: Text(
                              tx.description.isEmpty
                                  ? tx.type.toUpperCase()
                                  : tx.description,
                              style:
                                  const TextStyle(fontWeight: FontWeight.w600),
                            ),
                            subtitle: Text(
                              '${tx.date.toLocal()}'
                                  .split('.')
                                  .first, // basit tarih-saat
                              style: TextStyle(color: Colors.grey.shade600),
                            ),
                            trailing: Text(
                              _formatAmount(tx.amount),
                              style: TextStyle(
                                fontWeight: FontWeight.bold,
                                color: _amountColor(tx.amount),
                              ),
                            ),
                          );
                        },
                        separatorBuilder: (_, __) => const SizedBox(height: 8),
                        itemCount: _items.length,
                      ),
      ),
      backgroundColor: Colors.grey[100],
    );
  }
}
