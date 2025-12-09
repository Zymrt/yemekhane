// lib/models/menu_item_model.dart

class MenuItemModel {
  final String name;
  final String description;
  final int calories;
  // Menü öğesinin başka özellikleri varsa buraya eklenebilir.

  MenuItemModel({
    required this.name,
    required this.description,
    required this.calories,
  });

  // JSON'dan Model Oluşturma
  factory MenuItemModel.fromJson(Map<String, dynamic> json) {
    return MenuItemModel(
      name: json['name'] ?? 'Yemek Adı Yok',
      description: json['description'] ?? '',
      // Kalori değeri bazen string gelebilir, int'e çeviriyoruz
      calories: json['calories'] is String 
          ? int.tryParse(json['calories']) ?? 0 
          : json['calories'] ?? 0,
    );
  }
}