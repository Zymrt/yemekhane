// lib/controllers/auth_controller.dart

import 'package:get/get.dart';
import 'package:dio/dio.dart';

import '../views/home_view.dart';
import '../views/login_view.dart';
import '../services/api_service.dart';

class AuthController extends GetxController {
  final ApiService _apiService = Get.find<ApiService>();

  // UI'nin dinleyeceği reaktif alanlar
  final RxBool isLoading = false.obs;
  final RxString errorMessage = ''.obs;

  /// Kullanıcı girişi
  Future<bool> login(String phone, String password) async {
    isLoading.value = true;
    errorMessage.value = '';

    try {
      final response = await _apiService.login(phone, password);

      if (response.statusCode == 200) {
        isLoading.value = false;

        // Token cookie olarak kaydedildi, HomeView'a geç
        Get.offAll(() => HomeView());
        return true;
      }

      errorMessage.value = 'Giriş başarısız. Lütfen bilgilerinizi kontrol edin.';
      isLoading.value = false;
      return false;
    } on DioException catch (e) {
      String message = 'Giriş başarısız. Telefon veya şifre hatalı.';

      final data = e.response?.data;
      if (data is Map<String, dynamic> && data['message'] != null) {
        message = data['message'].toString();
      }

      errorMessage.value = message;
      isLoading.value = false;
      return false;
    } catch (e) {
      errorMessage.value = 'Beklenmedik bir hata oluştu: $e';
      isLoading.value = false;
      return false;
    }
  }

  /// Çıkış
  Future<void> logout() async {
    isLoading.value = true;

    try {
      await _apiService.logout();
    } catch (e) {
      // log atmak istersen buraya print koy
    } finally {
      isLoading.value = false;
      Get.offAll(() => LoginView());
    }
  }
}
