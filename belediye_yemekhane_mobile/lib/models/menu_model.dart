// lib/models/menu_model.dart

import 'menu_item_model.dart';

class MenuModel {
  final String id;
  final DateTime date;
  final List<MenuItemModel> items; // MenuItemModel listesi

  MenuModel({
    required this.id,
    required this.date,
    required this.items,
  });

  // ----------------------------------------------------
  // JSON'dan Model Oluşturma (from Map)
  // ----------------------------------------------------
  factory MenuModel.fromJson(Map<String, dynamic> json) {
    // 1. JSON Listesi -> Dart Listesine Çevirme
    // JSON'dan gelen 'items' listesini (List<dynamic>), map (eşleme) kullanarak 
    // her bir öğeyi MenuItemModel.fromJson() ile MenuItemModel'e çeviriyoruz.
    var itemsList = json['items'] as List;
    List<MenuItemModel> menuItems = itemsList.map((itemJson) => 
      MenuItemModel.fromJson(itemJson as Map<String, dynamic>)).toList();

    return MenuModel(
      id: json['_id'] ?? '',
      date: DateTime.parse(json['date'] ?? DateTime.now().toIso8601String()),
      items: menuItems,
    );
  }
}