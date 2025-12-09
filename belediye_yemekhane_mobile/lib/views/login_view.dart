// lib/views/login_view.dart

import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../controllers/auth_controller.dart';

class LoginView extends StatefulWidget {
  const LoginView({super.key});

  @override
  State<LoginView> createState() => _LoginViewState();
}

class _LoginViewState extends State<LoginView> {
  // AuthController'ı main.dart'ta zaten Get.put ettik, burada sadece find ediyoruz
  final AuthController authController = Get.find<AuthController>();

  late final TextEditingController phoneController;
  late final TextEditingController passwordController;

  @override
  void initState() {
    super.initState();

    phoneController = TextEditingController(text: '5366029433'); // test için
    passwordController = TextEditingController(text: '332211');  // test için
  }

  @override
  void dispose() {
    phoneController.dispose();
    passwordController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      appBar: AppBar(
        title: const Text('Yemekhane Giriş'),
        backgroundColor: Colors.blueGrey,
      ),
      body: SingleChildScrollView(
        padding: const EdgeInsets.all(24.0),
        child: Column(
          crossAxisAlignment: CrossAxisAlignment.stretch,
          children: <Widget>[
            // 📝 TELEFON NUMARASI
            TextField(
              controller: phoneController,
              keyboardType: TextInputType.phone,
              decoration: const InputDecoration(
                labelText: 'Telefon Numarası',
                prefixIcon: Icon(Icons.phone),
                border: OutlineInputBorder(),
              ),
            ),
            const SizedBox(height: 16.0),

            // 🔑 ŞİFRE
            TextField(
              controller: passwordController,
              obscureText: true,
              decoration: const InputDecoration(
                labelText: 'Şifre',
                prefixIcon: Icon(Icons.lock),
                border: OutlineInputBorder(),
              ),
            ),
            const SizedBox(height: 32.0),

            // 🚀 GİRİŞ BUTONU
            Obx(() {
              final loading = authController.isLoading.value;

              return ElevatedButton(
                onPressed: loading
                    ? null
                    : () async {
                        final phone = phoneController.text.trim();
                        final password = passwordController.text.trim();

                        final success = await authController.login(phone, password);

                        if (success) {
                          Get.snackbar(
                            'Başarılı',
                            'Giriş başarılı!',
                            snackPosition: SnackPosition.BOTTOM,
                            backgroundColor: Colors.green,
                            colorText: Colors.white,
                          );
                          // Yönlendirme AuthController içinde yapılıyor
                        }
                      },
                style: ElevatedButton.styleFrom(
                  minimumSize: const Size(double.infinity, 50),
                ),
                child: loading
                    ? const CircularProgressIndicator(color: Colors.white)
                    : const Text('Giriş Yap', style: TextStyle(fontSize: 18)),
              );
            }),
            const SizedBox(height: 16.0),

            // 🚨 HATA MESAJI
            Obx(() {
              final error = authController.errorMessage.value;
              if (error.isEmpty) return const SizedBox.shrink();

              return Text(
                error,
                textAlign: TextAlign.center,
                style: const TextStyle(
                  color: Colors.red,
                  fontWeight: FontWeight.bold,
                ),
              );
            }),

            // ❓ Şifremi Unuttum
            TextButton(
              onPressed: () {
                Get.snackbar(
                  'Bilgi',
                  'Şifremi Unuttum servisi yakında!',
                  snackPosition: SnackPosition.BOTTOM,
                );
              },
              child: const Text('Şifremi unuttum?'),
            ),
          ],
        ),
      ),
    );
  }
}
