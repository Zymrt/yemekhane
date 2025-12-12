// lib/controllers/profile_controller.dart

import 'package:get/get.dart';
import 'package:dio/dio.dart' as dio;

import '../services/api_service.dart';

class ProfileModel {
  final String id;
  final String name;
  final String surname;
  final String email;
  final String phone;
  final String unit;
  final String status; // pending / approved / ...
  final String? documentPath;

  ProfileModel({
    required this.id,
    required this.name,
    required this.surname,
    required this.email,
    required this.phone,
    required this.unit,
    required this.status,
    this.documentPath,
  });

  factory ProfileModel.fromJson(Map<String, dynamic> json) {
    return ProfileModel(
      id: json['_id']?.toString() ?? json['id']?.toString() ?? '',
      name: json['name']?.toString() ?? '',
      surname: json['surname']?.toString() ?? '',
      email: json['email']?.toString() ?? '',
      phone: json['phone']?.toString() ?? '',
      unit: json['unit']?.toString() ?? '',
      status: json['status']?.toString() ?? '',
      documentPath: json['document_path']?.toString(),
    );
  }
}

class ProfileController extends GetxController {
  final ApiService _apiService = Get.find<ApiService>();

  final RxBool isLoading = true.obs;
  final RxString errorMessage = ''.obs;
  final Rx<ProfileModel?> profile = Rx<ProfileModel?>(null);

  @override
  void onInit() {
    super.onInit();
    fetchProfile();
  }

  Future<void> fetchProfile() async {
    isLoading.value = true;
    errorMessage.value = '';

    try {
      final dio.Response response = await _apiService.getProfile();

      print('👤 PROFILE STATUS: ${response.statusCode}');
      print('👤 PROFILE DATA: ${response.data}');

      if (response.statusCode == 200) {
        final data = response.data;

        if (data is Map<String, dynamic>) {
          // Backend farklı şekillerde dönebilir:
          // { "user": { ... } }  veya  { "data": { ... } }  veya direkt { ... }
          Map<String, dynamic> payload;

          if (data['user'] is Map<String, dynamic>) {
            payload = data['user'] as Map<String, dynamic>;
          } else if (data['data'] is Map<String, dynamic>) {
            payload = data['data'] as Map<String, dynamic>;
          } else {
            payload = data;
          }

          profile.value = ProfileModel.fromJson(payload);
        } else {
          errorMessage.value = 'Profil verisi beklenmeyen formatta geldi.';
        }
      } else {
        errorMessage.value =
            'Profil bilgileri alınamadı. Kod: ${response.statusCode}';
      }
    } on dio.DioException catch (e) {
      print('👤 PROFILE ERROR: ${e.response?.data}');
      final resData = e.response?.data;

      if (resData is Map && resData['message'] != null) {
        errorMessage.value = resData['message'].toString();
      } else {
        errorMessage.value = 'Profil bilgileri alınırken bir hata oluştu.';
      }
    } catch (e) {
      errorMessage.value = 'Beklenmedik bir hata oluştu: $e';
    } finally {
      isLoading.value = false;
    }
  }
}
