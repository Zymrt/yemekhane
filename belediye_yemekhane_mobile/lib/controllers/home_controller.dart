import 'package:get/get.dart';
import 'package:dio/dio.dart';
import '../services/api_service.dart';

/// KULLANICI MODELİ
class UserModel {
  final String name;
  final String unitName; 
  final double balance;  
  final double unitPrice; // Uygulama içinde fiyatı bu değişken tutar
  final bool hasPurchased; 

  UserModel({
    this.name = 'Misafir',
    this.unitName = 'Tanımsız',
    this.balance = 0.0,
    this.unitPrice = 0.0,
    this.hasPurchased = false,
  });

  factory UserModel.fromJson(Map<String, dynamic> json) {
    
    // 1. Birim Adını Güvenli Alma
    String getSafeUnitName(dynamic unitData) {
      if (unitData is Map<String, dynamic>) {
        return unitData['name'] ?? 'Genel';
      }
      return 'Genel'; 
    }

    // 2. Fiyatı Güvenli Alma (ÖNCELİK: meal_price)
    double getSafePrice(Map<String, dynamic> json) {
      // 🟢 1. ÖNCELİK: Kullanıcı tablosundaki 'meal_price'
      if (json['meal_price'] != null) {
        return double.tryParse(json['meal_price'].toString()) ?? 0.0;
      }

      // 🟡 2. ÖNCELİK: Eğer o yoksa 'unit_price'
      if (json['unit_price'] != null) {
        return double.tryParse(json['unit_price'].toString()) ?? 0.0;
      }

      // 🔴 3. ÖNCELİK: Hiçbiri yoksa 'price'
      if (json['price'] != null) {
        return double.tryParse(json['price'].toString()) ?? 0.0;
      }

      // Birim içindeki fiyata bak (Yedek)
      if (json['unit'] is Map<String, dynamic> && json['unit']['price'] != null) {
        return double.tryParse(json['unit']['price'].toString()) ?? 0.0;
      }

      return 0.0; // Hiçbiri yoksa 0.0
    }

    return UserModel(
      name: json['name'] ?? 'Kullanıcı',
      unitName: json['unit_name'] ?? getSafeUnitName(json['unit']),
      balance: double.tryParse(json['balance']?.toString() ?? '0') ?? 0.0,
      
      // 🟢 Fiyatı yukarıdaki yeni fonksiyondan çekiyoruz
      unitPrice: getSafePrice(json),

      // Satın alma durumu
      hasPurchased: json['has_purchased'] == true || json['has_purchased'] == 1,
    );
  }
}

/// MENÜ ÖĞESİ (Çorba, Pilav vs.)
class MenuItem {
  final String name;
  final int? calorie;

  MenuItem({required this.name, this.calorie});

  factory MenuItem.fromJson(dynamic json) {
    if (json is! Map<String, dynamic>) {
      return MenuItem(name: "Veri Hatası");
    }
    return MenuItem(
      name: json['name'] ?? '',
      calorie: json['calorie'] != null 
          ? int.tryParse(json['calorie'].toString()) 
          : null,
    );
  }
}

/// GÜNLÜK MENÜ MODELİ
class MenuModel {
  final List<MenuItem> items;
  final int totalCalories;
  final bool isMenuAvailable; 

  MenuModel({
    required this.items, 
    this.totalCalories = 0, 
    this.isMenuAvailable = true
  });

  factory MenuModel.fromJson(Map<String, dynamic> json) {
    var list = json['items'] as List? ?? [];
    
    List<MenuItem> menuItems = list.map((i) => MenuItem.fromJson(i)).toList();
    int calculatedCal = menuItems.fold(0, (sum, item) => sum + (item.calorie ?? 0));

    return MenuModel(
      items: menuItems,
      totalCalories: calculatedCal,
      isMenuAvailable: true,
    );
  }
}

/// HOME CONTROLLER
class HomeController extends GetxController {
  final ApiService _apiService = Get.find<ApiService>();

  final RxBool isLoading = true.obs;
  final RxString errorMessage = ''.obs;
  final Rx<MenuModel?> todayMenu = Rx<MenuModel?>(null);
  final Rx<UserModel?> userProfile = Rx<UserModel?>(null);

  @override
  void onInit() {
    super.onInit();
    fetchData();
  }

  Future<void> fetchData() async {
    errorMessage.value = '';
    isLoading.value = true;

    try {
      await _fetchProfile();
      await _fetchTodayMenu();
    } on DioException catch (e) {
      errorMessage.value = 'Bağlantı hatası oluştu.';
      print('Dio Hata: ${e.message}');
    } catch (e) {
      errorMessage.value = 'Beklenmedik bir hata: $e';
    } finally {
      isLoading.value = false;
    }
  }

  Future<void> _fetchProfile() async {
    try {
      final response = await _apiService.getProfile();
      if (response.statusCode == 200 && response.data != null) {
        final data = response.data;
        // user key'i varsa içine gir, yoksa direkt data'yı al
        final userData = (data is Map<String, dynamic> && data.containsKey('user')) 
            ? data['user'] 
            : data;

        if (userData is Map<String, dynamic>) {
          userProfile.value = UserModel.fromJson(userData);
        }
      }
    } catch (e) {
      print("Profil çekme hatası: $e");
    }
  }

  Future<void> _fetchTodayMenu() async {
    try {
      final response = await _apiService.getMenuToday();
      if (response.statusCode == 200 && response.data != null) {
        if (response.data is Map<String, dynamic>) {
          todayMenu.value = MenuModel.fromJson(response.data);
        }
      }
    } on DioException catch (e) {
      // 404 ise menü girilmemiştir, boş liste döneriz
      if (e.response?.statusCode == 404) {
        todayMenu.value = MenuModel(items: [], isMenuAvailable: false);
      } else {
        rethrow;
      }
    }
  }

  // --- YEMEK SATIN ALMA İŞLEMİ ---
  Future<void> purchaseMeal() async {
    final user = userProfile.value;
    if (user == null) return;

    // Fiyat Kontrolü (UserModel içindeki meal_price buraya unitPrice olarak geldi)
    if (user.balance < user.unitPrice) {
      Get.snackbar(
        "Yetersiz Bakiye", 
        "Gereken: ${user.unitPrice} TL, Mevcut: ${user.balance} TL",
        backgroundColor: Get.theme.colorScheme.error,
        colorText: Get.theme.colorScheme.onError,
        snackPosition: SnackPosition.BOTTOM
      );
      return;
    }

    // Onay Penceresi
    Get.defaultDialog(
      title: "Yemek Satın Al",
      middleText: "${user.unitPrice} TL hesabınızdan düşülecektir. Onaylıyor musunuz?",
      textConfirm: "EVET, SATIN AL",
      textCancel: "VAZGEÇ",
      confirmTextColor: Get.theme.colorScheme.onPrimary,
      buttonColor: Get.theme.colorScheme.primary,
      onConfirm: () async {
        Get.back(); // Dialogu kapat
        
        try {
          isLoading.value = true;
          final response = await _apiService.purchaseOrder();

          if (response.statusCode == 200 || response.statusCode == 201) {
            Get.snackbar("Başarılı", "Afiyet olsun! İşlem tamamlandı.", 
              backgroundColor: Get.theme.colorScheme.primary, colorText: Get.theme.colorScheme.onPrimary);
            
            // Bakiyeyi ve durumu güncellemek için profili yenile
            await _fetchProfile(); 
          }
        } on DioException catch (e) {
          String errorMsg = "İşlem başarısız.";
          if (e.response?.statusCode == 400) {
            errorMsg = "Bakiye yetersiz veya zaten satın alındı.";
          }
          Get.snackbar("Hata", errorMsg, backgroundColor: Get.theme.colorScheme.error, colorText: Get.theme.colorScheme.onError);
        } finally {
          isLoading.value = false;
        }
      },
    );
  }
}