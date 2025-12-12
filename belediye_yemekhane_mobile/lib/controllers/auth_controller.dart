// lib/controllers/auth_controller.dart

import 'dart:io'; // File için
import 'package:get/get.dart';
import 'package:dio/dio.dart'
    as dio; // alias: dio.FormData, dio.MultipartFile, dio.DioException

import '../views/home_view.dart';
import '../views/login_view.dart';
import '../services/api_service.dart';

class AuthController extends GetxController {
  final ApiService _apiService = Get.find<ApiService>();

  // UI'nin dinleyeceği reaktif alanlar
  final RxBool isLoading = false.obs;
  final RxString errorMessage = ''.obs;

  /// ----------------------------------------------------------------
  /// 🟢 KULLANICI GİRİŞİ (LOGIN)
  /// ----------------------------------------------------------------
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

      errorMessage.value =
          'Giriş başarısız. Lütfen bilgilerinizi kontrol edin.';
      isLoading.value = false;
      return false;
    } on dio.DioException catch (e) {
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

  /// ----------------------------------------------------------------
  /// 🟠 KULLANICI KAYDI (REGISTER) - Dosya Yüklemeli
  /// ----------------------------------------------------------------
  Future<bool> register({
    required String name,
    required String surname,
    required String phone,
    required String unit,
    required String email,
    required String password,
    required String passwordConfirmation,
    File? document, // Seçilen dosya
  }) async {
    isLoading.value = true;
    errorMessage.value = '';

    try {
      // Backend validation: proof_document zorunluysa dosyasız devam etmeyelim
      if (document == null) {
        errorMessage.value = 'Lütfen belge (proof document) seçin.';
        isLoading.value = false;
        return false;
      }

      // 🔹 Backend’e göre field isimleri:
      // name, surname, email, phone, unit, password, password_confirmation
      final dio.FormData formData = dio.FormData();

      formData.fields.addAll([
        MapEntry('name', name),
        MapEntry('surname', surname),
        MapEntry('email', email),
        MapEntry('phone', phone),
        MapEntry('unit', unit),
        MapEntry('password', password),
        MapEntry('password_confirmation', passwordConfirmation),
      ]);

      // Dosya varsa FormData'ya ekle
      if (document != null) {
        final String fileName = document.path
            .split(Platform.pathSeparator)
            .last;

        formData.files.add(
          MapEntry(
            'proof_document', // 🔥 backend'in beklediği alan adı
            await dio.MultipartFile.fromFile(document.path, filename: fileName),
          ),
        );
      }

      // Servise gönder
      final response = await _apiService.registerUser(formData);
      final status = response.statusCode ?? 0;

      if (status == 200 || status == 201) {
        isLoading.value = false;
        return true;
      }

      errorMessage.value = 'Kayıt başarısız. Lütfen bilgileri kontrol edin.';
      return false;
    } on dio.DioException catch (e) {
      // Hata detayını console’a basalım
      print('🔴 REGISTER HATA STATUS: ${e.response?.statusCode}');
      print('🔴 REGISTER HATA DATA: ${e.response?.data}');

      String message = 'Kayıt sırasında hata oluştu.';

      final data = e.response?.data;
      if (data is Map<String, dynamic>) {
        // Laravel tipik validation cevabı:
        // { message: "...", errors: { field: ["mesaj"] } }
        if (data['errors'] != null && data['errors'] is Map) {
          final errors = data['errors'] as Map;
          if (errors.isNotEmpty) {
            final firstKey = errors.keys.first;
            final firstError = errors[firstKey];
            if (firstError is List && firstError.isNotEmpty) {
              message = firstError.first.toString();
            }
          }
        } else if (data['message'] != null) {
          message = data['message'].toString();
        }
      }

      errorMessage.value = message;
      return false;
    } catch (e) {
      errorMessage.value = 'Beklenmedik bir hata: $e';
      return false;
    } finally {
      isLoading.value = false;
    }
  }

  /// ----------------------------------------------------------------
  /// 🔴 ÇIKIŞ (LOGOUT)
  /// ----------------------------------------------------------------
  Future<void> logout() async {
    isLoading.value = true;

    try {
      await _apiService.logout();
    } catch (e) {
      // Hata olsa bile çıkış yapmış sayıp yönlendireceğiz
      print('Logout hatası: $e');
    } finally {
      isLoading.value = false;
      Get.offAll(() => LoginView());
    }
  }
}
