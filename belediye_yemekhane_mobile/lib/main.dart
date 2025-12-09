// lib/main.dart

import 'package:flutter/material.dart';
import 'package:get/get.dart';

import 'views/login_view.dart';
import 'services/api_service.dart';
import 'controllers/auth_controller.dart';

Future<void> main() async {
  // Flutter binding'lerini hazırla (async main kullanıyorsan şart)
  WidgetsFlutterBinding.ensureInitialized();

  // ApiService'i oluştur ve cookie sistemini başlat
  final apiService = ApiService();
  await apiService.initCookies();

  // ApiService'i GetX container'a kaydet
  Get.put<ApiService>(apiService, permanent: true);

  // AuthController'ı da global olarak kaydediyoruz
  Get.put<AuthController>(AuthController(), permanent: true);

  // Uygulamayı başlat
  runApp(const MyApp());
}

class MyApp extends StatelessWidget {
  const MyApp({super.key});

  @override
  Widget build(BuildContext context) {
    return GetMaterialApp(
      title: 'Belediye Yemekhane',
      debugShowCheckedModeBanner: false,
      theme: ThemeData(
        primarySwatch: Colors.teal,
      ),
      // İlk açılacak sayfa
      home: LoginView(),
    );
  }
}
