// lib/controllers/home_controller.dart

import 'package:get/get.dart';
import 'package:dio/dio.dart';
import '../services/api_service.dart';
import 'package:flutter/material.dart';
import 'package:qr_flutter/qr_flutter.dart';

/// ---------------------------------------------------------
/// 1. GÜVENLİ KULLANICI MODELİ
/// ---------------------------------------------------------
class UserModel {
  final String id;
  final String name;
  final String unitName;
  final double balance;
  final double unitPrice;
  final bool hasPurchased;
  final String email;

  UserModel({
    this.id = '',
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
        return unitData['name']?.toString() ?? 'Genel';
      }
      if (unitData is String && unitData.isNotEmpty) {
        return unitData;
      }
      return 'Genel';
    }

    double getSafePrice(Map<String, dynamic> json) {
      final keys = ['meal_price', 'unit_price', 'price'];
      for (final key in keys) {
        final value = json[key];
        if (value == null) continue;
        final parsed = double.tryParse(value.toString());
        if (parsed != null) return parsed;
      }
      return 0.0;
    }

    return UserModel(
      id: json['_id']?.toString() ?? json['id']?.toString() ?? '',
      name: json['name']?.toString() ?? 'Kullanıcı',
      email: json['email']?.toString() ?? '',
      unitName: json['unit_name']?.toString() ?? getSafeUnitName(json['unit']),
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
      calorie: json['calorie'] != null
          ? int.tryParse(json['calorie'].toString())
          : null,
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
    int calculatedCal = menuItems.fold(
      0,
      (sum, item) => sum + (item.calorie ?? 0),
    );

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

  // --- YORUM VE PUANLAMA STATE'LERİ ---
  final TextEditingController commentController = TextEditingController();

  // Anlık puan (kullanıcı yıldızlara tıkladığında değişen)
  final RxDouble currentRating = 0.0.obs;
  final RxBool isReviewSubmitting = false.obs;

  // Backend'den gelen kayıtlı yorum verisi
  final RxBool hasReviewToday = false.obs;
  final RxDouble lastReviewRating = 0.0.obs;
  final RxString lastReviewComment = ''.obs;

  // false = Sadece görüntüle (Yorum yapmış), true = Düzenle veya Yeni Ekle
  final RxBool isEditingReview = true.obs;

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
      // Paralel istek atarak hızlandıralım
      await Future.wait([
        _fetchProfile(),
        _fetchTodayMenu(),
        _fetchTodayReview(),
      ]);
    } catch (e) {
      errorMessage.value = 'Hata: $e';
    } finally {
      isLoading.value = false;
    }
  }

  Future<void> _fetchProfile() async {
    try {
      final response = await _apiService.getProfile();
      if (response.statusCode == 200 && response.data != null) {
        final data = response.data;
        final userData =
            (data is Map<String, dynamic> && data.containsKey('user'))
            ? data['user']
            : data;
        userProfile.value = UserModel.fromJson(
          Map<String, dynamic>.from(userData),
        );
      }
    } catch (e) {
      print("Profil çekme hatası: $e");
    }
  }

  Future<void> _fetchTodayMenu() async {
    try {
      final response = await _apiService.getMenuToday();
      if (response.statusCode == 200 && response.data != null) {
        todayMenu.value = MenuModel.fromJson(
          Map<String, dynamic>.from(response.data),
        );
      }
    } catch (e) {
      // 404 ise menü yok demektir, hata değil boş menü dönelim
      todayMenu.value = MenuModel(items: []);
    }
  }

  /// ---------------------------------------------------------
  /// ✅ KRİTİK DÜZELTME: GEÇMİŞ YORUMU SAĞLAM ÇEKME (my_review eklendi)
  /// ---------------------------------------------------------
  Future<void> _fetchTodayReview() async {
    try {
      final response = await _apiService.getTodayReview();
      print('DEBUG | /reviews/today RESPONSE DATA: ${response.data}');

      if (response.statusCode == 200 && response.data != null) {
        final data = response.data;

        // Backend'den gelen veri yapısını güvenli hale getiriyoruz
        // my_review -> review -> data anahtar sırasına göre kontrol ediyoruz.
        dynamic reviewData;
        if (data is Map) {
          if (data.containsKey('my_review'))
            reviewData =
                data['my_review']; // 👈 API cevabınızdaki KRİTİK DÜZELTME
          else if (data.containsKey('review'))
            reviewData = data['review'];
          else if (data.containsKey('data'))
            reviewData = data['data'];
        }

        if (reviewData != null && reviewData is Map) {
          // Puanı güvenli parse et
          final ratingVal =
              double.tryParse(
                reviewData['rating']?.toString() ??
                    reviewData['score']?.toString() ??
                    '0',
              ) ??
              0.0;
          // Yorumu güvenli al
          final commentVal =
              reviewData['comment']?.toString() ??
              reviewData['text']?.toString() ??
              '';

          if (ratingVal > 0) {
            // ✅ Veri bulundu, state'leri güncelle
            hasReviewToday.value = true;
            lastReviewRating.value = ratingVal;
            lastReviewComment.value = commentVal;

            // ✅ Yorum varsa "Görüntüleme Modu"na geç
            isEditingReview.value = false;
          } else {
            _resetReviewState();
          }
        } else {
          _resetReviewState();
        }
      } else {
        _resetReviewState();
      }
    } catch (e) {
      _resetReviewState();
      print("Yorum çekme hatası: $e");
    }
  }

  void _resetReviewState() {
    hasReviewToday.value = false;
    lastReviewRating.value = 0.0;
    lastReviewComment.value = '';
    isEditingReview.value = true;
    commentController.clear();
    currentRating.value = 0.0;
  }

  /// ---------------------------------------------------------
  /// 🆕 YORUM GÖNDERME
  /// ---------------------------------------------------------
  Future<void> submitReview() async {
    if (currentRating.value == 0) {
      Get.snackbar(
        "Uyarı",
        "Lütfen en az 1 yıldız verin.",
        backgroundColor: Colors.orange,
        colorText: Colors.white,
      );
      return;
    }

    isReviewSubmitting.value = true;
    try {
      await _apiService.postReview(
        currentRating.value.toInt(),
        commentController.text,
      );

      // Başarılı olursa local state'i güncelle (tekrar API çağırmaya gerek yok)
      lastReviewRating.value = currentRating.value;
      lastReviewComment.value = commentController.text;
      hasReviewToday.value = true;
      isEditingReview.value = false; // Görüntüleme moduna geç

      Get.snackbar(
        "Başarılı",
        "Yorumunuz kaydedildi.",
        backgroundColor: Colors.green,
        colorText: Colors.white,
      );

      // Formu temizle
      commentController.clear();
      currentRating.value = 0.0;
    } on DioException catch (e) {
      String errorMsg = "Yorum gönderilirken hata oluştu.";
      if (e.response?.statusCode == 400)
        errorMsg = "Bugün için yorum hakkın dolmuş olabilir.";
      Get.snackbar(
        "Hata",
        errorMsg,
        backgroundColor: Colors.red,
        colorText: Colors.white,
      );
    } catch (e) {
      Get.snackbar(
        "Hata",
        "Beklenmedik bir hata oluştu.",
        backgroundColor: Colors.red,
        colorText: Colors.white,
      );
    } finally {
      isReviewSubmitting.value = false;
    }
  }

  /// ---------------------------------------------------------
  /// 🆕 DÜZENLEME MODUNU BAŞLAT
  /// ---------------------------------------------------------
  void startEditReview() {
    // 1. Edit modunu aç
    isEditingReview.value = true;

    // 2. Yıldızları eski puana eşitle
    currentRating.value = lastReviewRating.value;

    // 3. Metin alanını eski yorumla doldur
    commentController.text = lastReviewComment.value;
  }

  // SATIN ALMA FONKSİYONU
  Future<void> purchaseMeal() async {
    final user = userProfile.value;
    if (user == null) return;

    final now = DateTime.now();
    final deadline = DateTime(now.year, now.month, now.day, 12, 0);
    if (now.isAfter(deadline)) {
      Get.snackbar(
        "Süre Doldu",
        "Saat 12:00'den sonra alım yapılamaz.",
        backgroundColor: Colors.red,
        colorText: Colors.white,
      );
      return;
    }

    if (user.unitPrice > 0 && user.balance < user.unitPrice) {
      Get.snackbar(
        "Bakiye Yetersiz",
        "Gereken: ${user.unitPrice} TL, Mevcut: ${user.balance} TL",
        backgroundColor: Colors.red,
        colorText: Colors.white,
      );
      return;
    }

    Get.defaultDialog(
      title: "Satın Al",
      middleText: user.unitPrice == 0
          ? "Bugünkü menü ücretsiz. Onaylıyor musun?"
          : "${user.unitPrice} TL bakiyenizden düşülecek.",
      textConfirm: "ONAYLA",
      textCancel: "İPTAL",
      confirmTextColor: Colors.white,
      buttonColor: Colors.teal,
      onConfirm: () async {
        Get.back();
        isLoading.value = true;
        try {
          final response = await _apiService.purchaseOrder();
          if (response.statusCode == 200 || response.statusCode == 201) {
            Get.snackbar(
              "Başarılı",
              "Yemek satın alındı!",
              backgroundColor: Colors.green,
              colorText: Colors.white,
            );
            await _fetchProfile();

            // QR Göster
            Get.dialog(
              AlertDialog(
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(16),
                ),
                title: const Text("Yemek Satın Alındı"),
                content: Column(
                  mainAxisSize: MainAxisSize.min,
                  children: [
                    QrImageView(
                      data: user.id,
                      version: QrVersions.auto,
                      size: 180,
                    ),
                    const SizedBox(height: 12),
                    const Text(
                      "Bu QR kodu görevliye göstererek giriş yapabilirsiniz.",
                      textAlign: TextAlign.center,
                    ),
                  ],
                ),
                actions: [
                  TextButton(
                    onPressed: () => Get.back(),
                    child: const Text("Kapat"),
                  ),
                ],
              ),
            );
          }
        } catch (e) {
          Get.snackbar(
            "Hata",
            "İşlem başarısız.",
            backgroundColor: Colors.red,
            colorText: Colors.white,
          );
        } finally {
          isLoading.value = false;
        }
      },
    );
  }
}
