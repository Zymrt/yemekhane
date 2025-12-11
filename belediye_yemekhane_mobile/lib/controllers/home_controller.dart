// lib/controllers/home_controller.dart

import 'package:get/get.dart';
import 'package:dio/dio.dart';
import '../services/api_service.dart';
import 'package:flutter/material.dart';

/// ---------------------------------------------------------
/// 1. GÜVENLİ KULLANICI MODELİ
/// ---------------------------------------------------------
class UserModel {
  final String name;
  final String unitName;
  final double balance;
  final double unitPrice; 
  final bool hasPurchased;
  final String email;

  UserModel({
    this.name = 'Misafir',
    this.unitName = 'Tanımsız',
    this.balance = 0.0,
    this.unitPrice = 0.0,
    this.hasPurchased = false,
    this.email = '',
  });

  factory UserModel.fromJson(Map<String, dynamic> json) {
    String getSafeUnitName(dynamic unitData) {
      if (unitData is Map<String, dynamic>) {
        return unitData['name'] ?? 'Genel';
      }
      return 'Genel';
    }

    double getSafePrice(Map<String, dynamic> json) {
      if (json['meal_price'] != null) return double.tryParse(json['meal_price'].toString()) ?? 0.0;
      if (json['unit_price'] != null) return double.tryParse(json['unit_price'].toString()) ?? 0.0;
      if (json['price'] != null) return double.tryParse(json['price'].toString()) ?? 0.0;
      return 0.0;
    }

    return UserModel(
      name: json['name'] ?? 'Kullanıcı',
      email: json['email'] ?? '',
      unitName: json['unit_name'] ?? getSafeUnitName(json['unit']),
      balance: double.tryParse(json['balance']?.toString() ?? '0') ?? 0.0,
      unitPrice: getSafePrice(json),
      hasPurchased: json['has_purchased'] == true || json['has_purchased'] == 1,
    );
  }
}

/// ---------------------------------------------------------
/// 2. MENÜ ÖĞESİ MODELİ
/// ---------------------------------------------------------
class MenuItem {
  final String name;
  final int? calorie;

  MenuItem({required this.name, this.calorie});

  factory MenuItem.fromJson(dynamic json) {
    if (json is! Map<String, dynamic>) {
      return MenuItem(name: "Veri Yükleniyor...");
    }
    return MenuItem(
      name: json['name'] ?? '',
      calorie: json['calorie'] != null ? int.tryParse(json['calorie'].toString()) : null,
    );
  }
}

/// ---------------------------------------------------------
/// 3. MENÜ MODELİ
/// ---------------------------------------------------------
class MenuModel {
  final List<MenuItem> items;
  final int totalCalories;
  final String date;

  MenuModel({required this.items, this.totalCalories = 0, this.date = ''});

  factory MenuModel.fromJson(Map<String, dynamic> json) {
    var list = json['items'] as List? ?? [];
    List<MenuItem> menuItems = list.map((i) => MenuItem.fromJson(i)).toList();
    int calculatedCal = menuItems.fold(0, (sum, item) => sum + (item.calorie ?? 0));

    return MenuModel(
      items: menuItems,
      totalCalories: calculatedCal,
      date: json['date'] ?? '',
    );
  }
}

/// ---------------------------------------------------------
/// 4. CONTROLLER (Beyin)
/// ---------------------------------------------------------
class HomeController extends GetxController {
  final ApiService _apiService = Get.find<ApiService>();

  final RxBool isLoading = true.obs;
  final RxString errorMessage = ''.obs;
  final Rx<MenuModel?> todayMenu = Rx<MenuModel?>(null);
  final Rx<UserModel?> userProfile = Rx<UserModel?>(null);
  
  final RxInt occupancyRate = 4.obs; 

  // Yorum ve Puanlama Durumları
  final TextEditingController commentController = TextEditingController();
  final RxDouble currentRating = 0.0.obs;
  final RxBool isReviewSubmitting = false.obs;

  @override
  void onInit() {
    super.onInit();
    fetchData();
  }
  
  @override
  void onClose() {
    commentController.dispose();
    super.onClose();
  }

  Future<void> fetchData() async {
    errorMessage.value = '';
    isLoading.value = true;
    try {
      await _fetchProfile();
      await _fetchTodayMenu();
    } on DioException catch (e) {
      errorMessage.value = 'Bağlantı hatası.';
      print('Dio Hata: ${e.message}');
    } catch (e) {
      errorMessage.value = 'Hata: $e';
    } finally {
      isLoading.value = false;
    }
  }

  Future<void> _fetchProfile() async {
    try {
      final response = await _apiService.getProfile();
      print('I/flutter: Profil isteği status: ${response.statusCode}'); 
      
      if (response.statusCode == 200 && response.data != null) {
        final data = response.data;
        
        // KRİTİK DEBUG ÇIKTISI BURADA
        print("I/flutter: DEBUG | PROFILE JSON: $data"); 
        
        final userData = (data is Map<String, dynamic> && data.containsKey('user')) ? data['user'] : data;
        userProfile.value = UserModel.fromJson(userData);
      }
    } catch (e) {
      print("Profil çekme hatası: $e");
    }
  }

  Future<void> _fetchTodayMenu() async {
    try {
      final response = await _apiService.getMenuToday();
      if (response.statusCode == 200 && response.data != null) {
        todayMenu.value = MenuModel.fromJson(response.data);
      }
    } on DioException catch (e) {
      if (e.response?.statusCode == 404) {
        todayMenu.value = MenuModel(items: []);
      }
    }
  }

  // YORUM GÖNDERME FONKSİYONU
  Future<void> submitReview() async {
    if (currentRating.value == 0) {
      Get.snackbar("Hata", "Lütfen menüye en az bir yıldız verin.", backgroundColor: Colors.red);
      return;
    }
    
    isReviewSubmitting.value = true;
    try {
      await _apiService.postReview(
        currentRating.value.toInt(), 
        commentController.text
      );
      
      Get.snackbar("Teşekkürler!", "Değerlendirmeniz kaydedildi.", backgroundColor: Colors.green);
      
      commentController.clear();
      currentRating.value = 0.0;
      
    } on DioException catch (e) {
      String errorMsg = "Yorum gönderilirken bir hata oluştu.";
      if (e.response?.statusCode == 400) {
        errorMsg = "Bugün menüye zaten yorum yapmışsınız.";
      }
      Get.snackbar("Hata", errorMsg, backgroundColor: Colors.red);
    } catch (e) {
      Get.snackbar("Hata", "Beklenmedik bir hata oluştu.", backgroundColor: Colors.red);
    } finally {
      isReviewSubmitting.value = false;
    }
  }


  // SATIN ALMA FONKSİYONU
  Future<void> purchaseMeal() async {
    final user = userProfile.value;
    if (user == null) return;

    // 1. KISA YOL ZAMAN KONTROLÜ (UX İÇİN)
    final now = DateTime.now();
    final deadline = DateTime(now.year, now.month, now.day, 12, 0);
    if (now.isAfter(deadline)) {
        Get.snackbar(
            "Süre Doldu", 
            "Saat 12:00'den sonra alım yapılamaz.",
            backgroundColor: Colors.red,
            colorText: Colors.white
        );
        return;
    }

    // 2. Bakiye Kontrolü
    if (user.balance < user.unitPrice) {
      Get.snackbar(
        "Bakiye Yetersiz", 
        "Gereken: ${user.unitPrice} TL, Mevcut: ${user.balance} TL", 
        backgroundColor: Get.theme.colorScheme.error,
        colorText: Get.theme.colorScheme.onError
      );
      return;
    }

    // 3. ONAY VE İŞLEM
    Get.defaultDialog(
      title: "Satın Al",
      middleText: "${user.unitPrice} TL bakiyenizden düşülecek. Onaylıyor musunuz?",
      textConfirm: "ONAYLA",
      textCancel: "İPTAL",
      confirmTextColor: Get.theme.colorScheme.onPrimary,
      buttonColor: Get.theme.colorScheme.primary,
      onConfirm: () async {
        Get.back();
        
        try {
          isLoading.value = true;
          final response = await _apiService.purchaseOrder();

          if (response.statusCode == 200 || response.statusCode == 201) {
            Get.snackbar("Başarılı", "Yemek satın alındı!", backgroundColor: Colors.green);
            await _fetchProfile(); 
          }
        } on DioException catch (e) {
          String errorMsg = "İşlem başarısız.";
          
          if (e.response?.statusCode == 403) {
             errorMsg = "Satın alma süresi dolduğu için işlem reddedildi. (12:00 sınırı)";
          } else if (e.response?.statusCode == 400) {
            errorMsg = "Zaten satın alındı veya bakiye yetersiz.";
          } else if (e.response?.statusCode == 402) {
            errorMsg = "Yetersiz bakiye. Lütfen bakiye yükleyin.";
          }
          
          Get.snackbar("Hata", errorMsg, backgroundColor: Colors.red);
        } finally {
          isLoading.value = false;
        }
      },
    );
  }
}