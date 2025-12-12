// lib/views/login_view.dart

import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../controllers/auth_controller.dart';
// Kayıt sayfasını import ediyoruz
import 'register_view.dart'; // ⬅️ EKLEDİK

class LoginView extends StatefulWidget {
  const LoginView({super.key});

  @override
  State<LoginView> createState() => _LoginViewState();
}

class _LoginViewState extends State<LoginView> {
  final AuthController authController = Get.find<AuthController>();

  late final TextEditingController phoneController;
  late final TextEditingController passwordController;

  // --- RENK PALETİ (HomeView ile aynı) ---
  final Color kPrimaryColor = const Color(0xFF064E3B); // Koyu Zümrüt
  final Color kAccentColor = const Color(0xFF10B981); // Canlı Yeşil
  final Color kSurfaceColor = const Color(0xFFFFFFFF); // Beyaz
  final Color kBackgroundColor = const Color(0xFFF3F4F6); // Açık Gri

  @override
  void initState() {
    super.initState();
    phoneController = TextEditingController(text: '5366029433'); // test için
    passwordController = TextEditingController(text: '332211'); // test için
  }

  @override
  void dispose() {
    phoneController.dispose();
    passwordController.dispose();
    super.dispose();
  }

  @override
  Widget build(BuildContext context) {
    // Klavye açılınca tasarım bozulmasın diye boyutu alıyoruz
    final size = MediaQuery.of(context).size;

    return Scaffold(
      backgroundColor: kBackgroundColor,
      body: Stack(
        children: [
          // 1. ARKA PLAN (Kavisli Gradient Header)
          Container(
            height: size.height * 0.45, // Ekranın %45'i
            decoration: BoxDecoration(
              gradient: LinearGradient(
                colors: [kPrimaryColor, kAccentColor],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: const BorderRadius.only(
                bottomLeft: Radius.circular(40),
                bottomRight: Radius.circular(40),
              ),
            ),
            child: SafeArea(
              child: Center(
                child: Column(
                  mainAxisAlignment: MainAxisAlignment.start,
                  children: [
                    const SizedBox(height: 40),
                    // LOGO veyaİKON
                    Container(
                      padding: const EdgeInsets.all(16),
                      decoration: BoxDecoration(
                        color: Colors.white.withOpacity(0.15),
                        shape: BoxShape.circle,
                        border: Border.all(
                          color: Colors.white.withOpacity(0.2),
                          width: 1,
                        ),
                      ),
                      child: const Icon(
                        Icons.restaurant_menu_rounded,
                        size: 50,
                        color: Colors.white,
                      ),
                    ),
                    const SizedBox(height: 15),
                    const Text(
                      "Yemekhane Sistemi",
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 24,
                        fontWeight: FontWeight.bold,
                        letterSpacing: 1,
                      ),
                    ),
                    const SizedBox(height: 5),
                    Text(
                      "Belediye Personel Girişi",
                      style: TextStyle(
                        color: Colors.white.withOpacity(0.8),
                        fontSize: 14,
                      ),
                    ),
                  ],
                ),
              ),
            ),
          ),

          // 2. GİRİŞ KARTI (Floating Card)
          Align(
            alignment: Alignment.bottomCenter,
            child: SingleChildScrollView(
              child: Padding(
                padding: const EdgeInsets.symmetric(
                  horizontal: 24.0,
                  vertical: 30,
                ),
                child: Column(
                  children: [
                    // Kartın kendisi
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(30),
                      decoration: BoxDecoration(
                        color: kSurfaceColor,
                        borderRadius: BorderRadius.circular(24),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.black.withOpacity(0.1),
                            blurRadius: 20,
                            offset: const Offset(0, 10),
                          ),
                        ],
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            "Giriş Yap",
                            style: TextStyle(
                              color: kPrimaryColor,
                              fontSize: 22,
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                          const SizedBox(height: 5),
                          Text(
                            "Hesabınıza erişmek için bilgilerinizi girin.",
                            style: TextStyle(
                              color: Colors.grey.shade500,
                              fontSize: 13,
                            ),
                          ),
                          const SizedBox(height: 30),

                          // 📝 TELEFON NUMARASI
                          _buildCustomTextField(
                            controller: phoneController,
                            label: "Telefon Numarası",
                            icon: Icons.phone_iphone_rounded,
                            inputType: TextInputType.phone,
                          ),

                          const SizedBox(height: 20),

                          // 🔑 ŞİFRE
                          _buildCustomTextField(
                            controller: passwordController,
                            label: "Şifre",
                            icon: Icons.lock_outline_rounded,
                            isPassword: true,
                          ),

                          const SizedBox(height: 30),

                          // 🚀 GİRİŞ BUTONU
                          Obx(() {
                            final loading = authController.isLoading.value;
                            return SizedBox(
                              width: double.infinity,
                              height: 56,
                              child: ElevatedButton(
                                onPressed: loading
                                    ? null
                                    : () async {
                                        final phone = phoneController.text
                                            .trim();
                                        final password = passwordController.text
                                            .trim();
                                        // Klavye kapat
                                        FocusManager.instance.primaryFocus
                                            ?.unfocus();

                                        await authController.login(
                                          phone,
                                          password,
                                        );
                                      },
                                style: ElevatedButton.styleFrom(
                                  backgroundColor: kPrimaryColor,
                                  foregroundColor: Colors.white,
                                  elevation: 8,
                                  shadowColor: kPrimaryColor.withOpacity(0.4),
                                  shape: RoundedRectangleBorder(
                                    borderRadius: BorderRadius.circular(16),
                                  ),
                                ),
                                child: loading
                                    ? const SizedBox(
                                        width: 24,
                                        height: 24,
                                        child: CircularProgressIndicator(
                                          color: Colors.white,
                                          strokeWidth: 2.5,
                                        ),
                                      )
                                    : const Text(
                                        'GİRİŞ YAP',
                                        style: TextStyle(
                                          fontSize: 16,
                                          fontWeight: FontWeight.bold,
                                          letterSpacing: 1,
                                        ),
                                      ),
                              ),
                            );
                          }),

                          const SizedBox(height: 15),

                          // ❓ ŞİFREMİ UNUTTUM
                          Center(
                            child: TextButton(
                              onPressed: () {
                                Get.snackbar(
                                  'Bilgi',
                                  'Şifre sıfırlama servisi yakında!',
                                  snackPosition: SnackPosition.BOTTOM,
                                  backgroundColor: Colors.grey.shade800,
                                  colorText: Colors.white,
                                  margin: const EdgeInsets.all(20),
                                );
                              },
                              child: Text(
                                'Şifremi Unuttum?',
                                style: TextStyle(
                                  color: Colors.grey.shade600,
                                  fontWeight: FontWeight.w600,
                                ),
                              ),
                            ),
                          ),

                          const SizedBox(height: 10),

                          // 🚨 HATA MESAJI
                          Obx(() {
                            final error = authController.errorMessage.value;
                            if (error.isEmpty) return const SizedBox.shrink();

                            return Container(
                              padding: const EdgeInsets.all(12),
                              decoration: BoxDecoration(
                                color: Colors.red.shade50,
                                borderRadius: BorderRadius.circular(12),
                                border: Border.all(color: Colors.red.shade100),
                              ),
                              child: Row(
                                children: [
                                  Icon(
                                    Icons.error_outline,
                                    color: Colors.red.shade700,
                                  ),
                                  const SizedBox(width: 10),
                                  Expanded(
                                    child: Text(
                                      error,
                                      style: TextStyle(
                                        color: Colors.red.shade800,
                                        fontSize: 13,
                                      ),
                                    ),
                                  ),
                                ],
                              ),
                            );
                          }),

                          const SizedBox(height: 20),

                          // 🆕 KAYIT OL LİNKİ (EKLENEN BÖLÜM)
                          Center(
                            child: TextButton(
                              onPressed: () {
                                // Kullanıcıyı Kayıt Ol sayfasına yönlendir
                                Get.to(() => const RegisterView());
                              },
                              child: Row(
                                mainAxisSize: MainAxisSize.min,
                                children: [
                                  Text(
                                    "Hesabın yok mu? ",
                                    style: TextStyle(
                                      color: Colors.grey.shade600,
                                      fontSize: 15,
                                    ),
                                  ),
                                  Text(
                                    "Hemen Kayıt Ol",
                                    style: TextStyle(
                                      color: kAccentColor,
                                      fontWeight: FontWeight.bold,
                                      fontSize: 15,
                                    ),
                                  ),
                                ],
                              ),
                            ),
                          ),
                        ],
                      ),
                    ),

                    // Alt boşluk (Scroll için)
                    SizedBox(height: size.height * 0.1),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  // --- ÖZEL TEXT FIELD WIDGET ---
  Widget _buildCustomTextField({
    required TextEditingController controller,
    required String label,
    required IconData icon,
    bool isPassword = false,
    TextInputType inputType = TextInputType.text,
  }) {
    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Text(
          label,
          style: const TextStyle(
            fontWeight: FontWeight.bold,
            color: Colors.black87,
            fontSize: 14,
          ),
        ),
        const SizedBox(height: 8),
        Container(
          decoration: BoxDecoration(
            color: Colors.grey.shade50,
            borderRadius: BorderRadius.circular(16),
            boxShadow: [
              BoxShadow(
                color: Colors.black.withOpacity(0.02),
                blurRadius: 5,
                offset: const Offset(0, 2),
              ),
            ],
          ),
          child: TextField(
            controller: controller,
            obscureText: isPassword,
            keyboardType: inputType,
            style: const TextStyle(fontWeight: FontWeight.w500),
            decoration: InputDecoration(
              prefixIcon: Icon(icon, color: kAccentColor),
              hintText: isPassword ? "******" : "Örn: 5301234567",
              hintStyle: TextStyle(color: Colors.grey.shade400, fontSize: 14),
              border: OutlineInputBorder(
                borderRadius: BorderRadius.circular(16),
                borderSide: BorderSide.none,
              ),
              filled: true,
              fillColor: Colors.transparent, // Container rengini kullanıyoruz
              contentPadding: const EdgeInsets.symmetric(vertical: 16),
            ),
          ),
        ),
      ],
    );
  }
}
