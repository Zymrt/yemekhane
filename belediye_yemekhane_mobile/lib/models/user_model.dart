// lib/models/user_model.dart

class UserModel {
  final String id;
  final String name;
  final String surname;
  final String phone;
  final String email;
  final String unit;
  final double balance;
  final String role;
  final double mealPrice;
  final DateTime createdAt;

  UserModel({
    required this.id,
    required this.name,
    required this.surname,
    required this.phone,
    required this.email,
    required this.unit,
    required this.balance,
    required this.role,
    required this.mealPrice,
    required this.createdAt,
  });

  // ----------------------------------------------------
  // JSON'dan Model Oluşturma (from Map)
  // ----------------------------------------------------
  factory UserModel.fromJson(Map<String, dynamic> json) {
    // Senin UserController'dan gelen alan adlarını kullanıyoruz:
    return UserModel(
      id: json['_id'] ?? '',
      name: json['name'] ?? '',
      surname: json['surname'] ?? '',
      phone: json['phone'] ?? '',
      email: json['email'] ?? '',
      unit: json['unit'] ?? '',
      // MongoDB'de bazen int/String gelebilir, double'a çeviriyoruz
      balance: (json['balance'] ?? 0.0).toDouble(), 
      role: json['role'] ?? 'user',
      mealPrice: (json['meal_price'] ?? 0.0).toDouble(),
      // ISO String formatını DateTime objesine çeviriyoruz
      createdAt: DateTime.parse(json['created_at']), 
    );
  }
}