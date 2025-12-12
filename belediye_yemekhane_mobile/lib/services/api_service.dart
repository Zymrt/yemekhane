// lib/services/api_service.dart

import 'dart:io';

import 'package:dio/dio.dart' as dio; // S A D E C E alias
import 'package:cookie_jar/cookie_jar.dart';
import 'package:dio_cookie_manager/dio_cookie_manager.dart';
import 'package:path_provider/path_provider.dart';
import 'package:get/get.dart' hide Response;
import '../views/login_view.dart';

// --------------------------------------------------------
// SABİTLER
// --------------------------------------------------------
const String BASE_URL = 'http://10.0.2.2:8000/api';
const String BASE_ORIGIN = 'http://10.0.2.2:8000';

// --------------------------------------------------------
// ANA SERVİS SINIFI
// --------------------------------------------------------
class ApiService {
  late final dio.Dio _dio; // aliaslı Dio
  late final PersistCookieJar cookieJar;

  ApiService() {
    _dio = dio.Dio(
      dio.BaseOptions(
        baseUrl: BASE_URL,
        connectTimeout: const Duration(seconds: 10),
        receiveTimeout: const Duration(seconds: 10),
        contentType: dio.Headers.jsonContentType,
        responseType: dio.ResponseType.json,
        headers: {
          'Accept': 'application/json', // 🔥 Laravel'e "JSON ver" de
        },
      ),
    );
  }

  // --------------------------------------------------------
  // COOKIE & AUTH SETUP
  // --------------------------------------------------------
  Future<void> initCookies() async {
    final appDocDir = await getApplicationDocumentsDirectory();
    final appDocPath = appDocDir.path;

    cookieJar = PersistCookieJar(storage: FileStorage("$appDocPath/.cookies/"));

    _dio.interceptors.clear();

    // 1. Cookie Yönetimi
    _dio.interceptors.add(CookieManager(cookieJar));

    // 2. Token Middleware
    _dio.interceptors.add(
      dio.QueuedInterceptorsWrapper(
        onRequest: (options, handler) async {
          // İstek atılmadan önce kayıtlı cookielerden token'ı bulmaya çalış
          final cookies = await cookieJar.loadForRequest(
            Uri.parse(BASE_ORIGIN),
          );

          final accessToken = cookies.firstWhere(
            (cookie) => cookie.name == 'access_token',
            orElse: () => Cookie('access_token', ''),
          );

          // Token varsa Header'a ekle (Laravel JWT/Sanctum için)
          if (accessToken.value.isNotEmpty) {
            options.headers['Authorization'] = 'Bearer ${accessToken.value}';
          }

          handler.next(options);
        },
        onError: (err, handler) async {
          // 401 (Yetkisiz) veya 403 (Yasaklı) gelirse çıkış yap
          if (err.response?.statusCode == 401 ||
              err.response?.statusCode == 403) {
            print("Oturum sona erdi → Logout yapılıyor...");
            await cookieJar.deleteAll();
            Get.offAll(() => LoginView());
          }
          handler.next(err);
        },
      ),
    );
  }

  // --------------------------------------------------------
  // AUTH İŞLEMLERİ
  // --------------------------------------------------------

  Future<dio.Response> login(String phone, String password) {
    return _dio.post('/login', data: {'phone': phone, 'password': password});
  }

  Future<dio.Response> logout() async {
    try {
      final response = await _dio.post('/logout');
      await cookieJar.deleteAll();
      return response;
    } catch (e) {
      await cookieJar.deleteAll();
      rethrow;
    }
  }

  // 🆕 KAYIT METODU (Dosya Yükleme İçin)
  // AuthController içinde hazırladığın dio.FormData buraya geliyor.
  Future<dio.Response> registerUser(dio.FormData formData) {
    return _dio.post(
      '/register',
      data: formData,
      options: dio.Options(
        followRedirects: false, // 302'yi takip etme
        validateStatus: (status) {
          // ✅ Sadece 2xx (200-299) başarılı
          return status != null && status >= 200 && status < 300;
        },
      ),
    );
  }

  // --------------------------------------------------------
  // USER / PROFILE
  // --------------------------------------------------------
  Future<dio.Response> getProfile() {
    return _dio.get('/user/profile');
  }

  // --------------------------------------------------------
  // MENÜ
  // --------------------------------------------------------
  Future<dio.Response> getMenuToday() {
    return _dio.get('/menu/today');
  }

  // --------------------------------------------------------
  // DUYURULAR
  // --------------------------------------------------------
  Future<dio.Response> getAnnouncements() {
    return _dio.get('/announcements');
  }

  // --------------------------------------------------------
  // YORUM SİSTEMİ
  // --------------------------------------------------------

  /// 🟢 BUGÜNÜN YORUMU (Varsa getirir)
  Future<dio.Response> getTodayReview() {
    return _dio.get('/reviews/today');
  }

  /// ⭐ Yorum Gönder / Güncelle
  Future<dio.Response> postReview(int rating, String comment) {
    return _dio.post('/reviews', data: {'rating': rating, 'comment': comment});
  }

  /// 🧾 Kullanıcının geçmiş yorumları
  Future<dio.Response> getMyReviews() {
    return _dio.get('/reviews/my-reviews');
  }

  // --------------------------------------------------------
  // SATIN ALMA / CÜZDAN
  // --------------------------------------------------------
  Future<dio.Response> purchaseOrder() {
    return _dio.post('/order/purchase');
  }

  Future<dio.Response> getTransactions() {
    return _dio.get('/transactions');
  }

  Future<dio.Response> startPayment(double amount) {
    return _dio.post('/payment/start', data: {'amount': amount});
  }
}
