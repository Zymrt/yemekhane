// lib/views/home_view.dart

import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../controllers/home_controller.dart';
import '../controllers/auth_controller.dart';

class HomeView extends StatelessWidget {
  HomeView({super.key});

  // HomeController bu ekranda oluşturulup GetX'e kaydediliyor
  final HomeController homeController = Get.put(HomeController());
  // AuthController zaten main.dart'ta put edildi, burada sadece buluyoruz
  final AuthController authController = Get.find<AuthController>();

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Belediye Yemekhane'),
        backgroundColor: Colors.teal,
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh),
            onPressed: homeController.fetchData,
          ),
          IconButton(
            icon: const Icon(Icons.logout),
            onPressed: authController.logout,
          ),
        ],
      ),
      body: Obx(() {
        // 1) Yükleniyor
        if (homeController.isLoading.value) {
          return const Center(child: CircularProgressIndicator());
        }

        // 2) Hata
        if (homeController.errorMessage.value.isNotEmpty) {
          return Center(
            child: Padding(
              padding: const EdgeInsets.all(24.0),
              child: Text(
                'Yükleme Hatası: ${homeController.errorMessage.value}',
                textAlign: TextAlign.center,
                style: const TextStyle(
                  color: Colors.red,
                  fontSize: 16,
                ),
              ),
            ),
          );
        }

        // 3) Normal içerik
        return Center(
          child: Column(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Text(
                'Hoş Geldin, ${homeController.userProfile.value?.name ?? 'Kullanıcı'}!',
                style: const TextStyle(
                  fontSize: 20,
                  fontWeight: FontWeight.bold,
                ),
              ),
              const SizedBox(height: 20),
              Text(
                'Menü Durumu: ${homeController.todayMenu.value?.meal ?? 'Menü Yüklenemedi.'}',
                style: const TextStyle(
                  fontSize: 16,
                ),
              ),
            ],
          ),
        );
      }),
    );
  }
}
