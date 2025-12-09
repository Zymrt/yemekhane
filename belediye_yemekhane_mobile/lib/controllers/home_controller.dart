// lib/controllers/home_controller.dart

import 'package:get/get.dart';
import 'package:dio/dio.dart';

import '../services/api_service.dart';

/// Basit UserModel (backend'e göre sonra genişletebilirsin)
class UserModel {
  final String name;

  UserModel({this.name = 'Misafir'});

  factory UserModel.fromJson(Map<String, dynamic> json) {
    return UserModel(
      name: json['name'] ?? 'Kullanıcı',
    );
  }
}

/// Basit MenuModel
class MenuModel {
  final String meal;

  MenuModel({this.meal = 'Yemek Yüklendi'});

  factory MenuModel.fromJson(Map<String, dynamic> json) {
    return MenuModel(
      meal: json['meal'] ?? 'Yemek Yüklendi',
    );
  }
}

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
    print('HomeController -> fetchData başladı');
    errorMessage.value = '';
    isLoading.value = true;

    try {
      await _fetchProfile();
      await _fetchTodayMenu();
    } on DioException catch (e) {
      errorMessage.value =
          'Sunucuya erişilemiyor veya zaman aşımı: Bağlantıyı kontrol edin.';
      print('Dio Hata: ${e.response?.statusCode} - ${e.message}');
    } catch (e) {
      errorMessage.value = 'Beklenmedik bir hata oluştu: $e';
      print('Genel Hata: $e');
    } finally {
      isLoading.value = false;
      print('HomeController -> fetchData bitti, isLoading = ${isLoading.value}');
    }
  }

  Future<void> _fetchProfile() async {
    final response = await _apiService.getProfile();
    print('Profil isteği status: ${response.statusCode}');

    if (response.statusCode == 200 && response.data != null) {
      final data = response.data;

      // Backend bazen { user: {...} } dönebilir, bazen direk {...}
      if (data is Map<String, dynamic>) {
        userProfile.value =
            UserModel.fromJson(data['user'] ?? data);
      } else {
        print('Profil datası beklenmeyen formatta: ${response.data}');
      }
    } else {
      print('Profil yüklenemedi: ${response.statusCode}');
    }
  }

  Future<void> _fetchTodayMenu() async {
    try {
      final response = await _apiService.getMenuToday();
      print('Menü isteği status: ${response.statusCode}');

      if (response.statusCode == 200 && response.data != null) {
        final data = response.data;
        if (data is Map<String, dynamic>) {
          todayMenu.value = MenuModel.fromJson(data);
        }
      }
    } on DioException catch (e) {
      // EĞER HATA 404 İSE (Yani bugün yemek yoksa)
      if (e.response?.statusCode == 404) {
        print("Bugün için menü girilmemiş (404).");
        // Hata fırlatma, sadece menüyü boşalt ve devam et.
        todayMenu.value = MenuModel(meal: 'Bugün için menü bulunamadı.'); 
      } else {
        // Başka bir hataysa (500 vs) yukarı fırlat ki genel catch yakalasın
        rethrow;
      }
    }
  }
}
