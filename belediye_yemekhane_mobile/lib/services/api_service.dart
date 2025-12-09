// lib/services/api_service.dart

import 'dart:io';

import 'package:dio/dio.dart';
import 'package:cookie_jar/cookie_jar.dart';
import 'package:dio_cookie_manager/dio_cookie_manager.dart';
import 'package:path_provider/path_provider.dart';
import 'package:get/get.dart' hide Response;

import '../views/login_view.dart';

// --------------------------------------------------------
// SABİTLER
// --------------------------------------------------------
// NOT: Laravel rotaları '/api' ile başlar.
// Burası doğru: http://10.0.2.2:8000/api
const String BASE_URL = 'http://10.0.2.2:8000/api';
// Cookie'ler domain bazlıdır, '/api' içermez.
const String BASE_ORIGIN = 'http://10.0.2.2:8000';

// --------------------------------------------------------
// ANA SERVİS SINIFI
// --------------------------------------------------------
class ApiService {
  late final Dio dio;
  late final PersistCookieJar cookieJar;

  ApiService() {
    dio = Dio(
      BaseOptions(
        baseUrl: BASE_URL,
        connectTimeout: const Duration(seconds: 10),
        receiveTimeout: const Duration(seconds: 10),
        // Laravel genelde JSON bekler
        contentType: Headers.jsonContentType, 
        responseType: ResponseType.json,
      ),
    );
  }

  Future<void> initCookies() async {
    final appDocDir = await getApplicationDocumentsDirectory();
    final String appDocPath = appDocDir.path;

    cookieJar = PersistCookieJar(
      storage: FileStorage("$appDocPath/.cookies/"),
    );

    dio.interceptors.clear();

    // 1. Cookie Yönetimi
    dio.interceptors.add(CookieManager(cookieJar));

    // 2. Token ve Hata Yönetimi
    dio.interceptors.add(
      QueuedInterceptorsWrapper(
        onRequest: (options, handler) async {
          // Cookie'den token'ı alıp Header'a ekleyelim
          final cookies = await cookieJar.loadForRequest(Uri.parse(BASE_ORIGIN));

          final accessToken = cookies.firstWhere(
            (cookie) => cookie.name == 'access_token',
            orElse: () => Cookie('access_token', ''),
          );

          if (accessToken.value.isNotEmpty) {
            options.headers['Authorization'] = 'Bearer ${accessToken.value}';
          }

          handler.next(options);
        },
        onError: (DioException err, handler) async {
          // 401 (Yetkisiz) hatasında çıkış yap
          if (err.response?.statusCode == 401 || err.response?.statusCode == 403) {
            print("Oturum süresi doldu, çıkış yapılıyor...");
            await cookieJar.deleteAll();
            Get.offAll(() => const LoginView());
          }
          handler.next(err);
        },
      ),
    );
  }

  // --------------------------------------------------------
  // AUTH (KİMLİK) İŞLEMLERİ
  // --------------------------------------------------------

  Future<Response> login(String phone, String password) {
    return dio.post(
      '/login',
      data: {'phone': phone, 'password': password},
    );
  }

  Future<Response> logout() async {
    try {
      final response = await dio.post('/logout');
      await cookieJar.deleteAll();
      return response;
    } catch (e) {
      // Hata alsa bile local cookie'leri silelim
      await cookieJar.deleteAll();
      rethrow;
    }
  }

  Future<Response> register(Map<String, dynamic> data) {
    return dio.post('/register', data: data);
  }

  // --------------------------------------------------------
  // KULLANICI & PROFİL
  // --------------------------------------------------------

  Future<Response> getProfile() {
    return dio.get('/user/profile');
  }

  // --------------------------------------------------------
  // MENÜ İŞLEMLERİ
  // --------------------------------------------------------

  Future<Response> getMenuToday() {
    return dio.get('/menu/today');
  }

  // --------------------------------------------------------
  // DUYURULAR (HERKESE AÇIK)
  // --------------------------------------------------------

  Future<Response> getAnnouncements() {
    return dio.get('/announcements');
  }

  // --------------------------------------------------------
  // YORUM SİSTEMİ
  // --------------------------------------------------------

  // Günün yemeklerine yapılan yorumlar
  Future<Response> getTodayReviews() {
    return dio.get('/reviews/today');
  }

  // Yeni yorum gönder
  Future<Response> postReview(int menuId, String comment, int rating) {
    return dio.post(
      '/reviews',
      data: {
        'menu_id': menuId,
        'comment': comment,
        'rating': rating,
      },
    );
  }

  // Benim yaptığım yorumlar
  Future<Response> getMyReviews() {
    return dio.get('/reviews/my-reviews');
  }

  // --------------------------------------------------------
  // SATIN ALMA VE CÜZDAN
  // --------------------------------------------------------

  // Yemek satın al (Bakiyeden düşer)
  Future<Response> purchaseOrder() {
    return dio.post('/order/purchase');
  }

  // Geçmiş hesap hareketleri (Transactions)
  Future<Response> getTransactions() {
    return dio.get('/transactions');
  }

  // Para Yükleme Başlat (Mock Payment)
  Future<Response> startPayment(double amount) {
    return dio.post(
      '/payment/start',
      data: {'amount': amount},
    );
  }
}