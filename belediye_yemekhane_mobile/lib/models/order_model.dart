// lib/models/order_model.dart

class OrderModel {
  final String id;
  final String userId;
  final String menuId;
  final DateTime date;
  final int qty;
  final double price;
  final double total;
  final String status;

  OrderModel({
    required this.id,
    required this.userId,
    required this.menuId,
    required this.date,
    required this.qty,
    required this.price,
    required this.total,
    required this.status,
  });

  // ----------------------------------------------------
  // JSON'dan Model Oluşturma (from Map)
  // ----------------------------------------------------
  factory OrderModel.fromJson(Map<String, dynamic> json) {
    return OrderModel(
      id: json['_id'] ?? '',
      userId: json['user_id'] ?? '',
      menuId: json['menu_id'] ?? '',
      date: DateTime.parse(json['date'] ?? DateTime.now().toIso8601String()),
      qty: (json['qty'] is String) ? int.tryParse(json['qty']) ?? 0 : json['qty'] ?? 0,
      price: (json['price'] ?? 0.0).toDouble(),
      total: (json['total'] ?? 0.0).toDouble(),
      status: json['status'] ?? 'pending',
    );
  }
}