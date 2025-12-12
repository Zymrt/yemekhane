// lib/views/register_view.dart

import 'dart:io';
import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:file_picker/file_picker.dart'; // pubspec.yaml'a eklemeyi unutma!

import '../controllers/auth_controller.dart';

class RegisterView extends StatefulWidget {
  const RegisterView({super.key});

  @override
  State<RegisterView> createState() => _RegisterViewState();
}

class _RegisterViewState extends State<RegisterView> {
  final AuthController authController = Get.find<AuthController>();

  // Controllerlar
  final TextEditingController nameController = TextEditingController();
  final TextEditingController surnameController = TextEditingController();
  final TextEditingController phoneController = TextEditingController();
  final TextEditingController unitController = TextEditingController();
  final TextEditingController emailController = TextEditingController();
  final TextEditingController passwordController = TextEditingController();
  final TextEditingController passwordConfirmController =
      TextEditingController();

  // Dosya Seçimi
  File? selectedFile;
  String? selectedFileName;

  // Renkler (Login ile aynı)
  final Color kPrimaryColor = const Color(0xFF064E3B);
  final Color kAccentColor = const Color(0xFF10B981);
  final Color kBackgroundColor = const Color(0xFFF3F4F6);
  final Color kSurfaceColor = const Color(0xFFFFFFFF);

  @override
  void dispose() {
    nameController.dispose();
    surnameController.dispose();
    phoneController.dispose();
    unitController.dispose();
    emailController.dispose();
    passwordController.dispose();
    passwordConfirmController.dispose();
    super.dispose();
  }

  // Dosya Seçme Fonksiyonu
  Future<void> _pickFile() async {
    FilePickerResult? result = await FilePicker.platform.pickFiles(
      type: FileType.custom,
      allowedExtensions: ['jpg', 'png', 'pdf'],
    );

    if (result != null) {
      setState(() {
        selectedFile = File(result.files.single.path!);
        selectedFileName = result.files.single.name;
      });
    }
  }

  @override
  Widget build(BuildContext context) {
    final size = MediaQuery.of(context).size;

    return Scaffold(
      backgroundColor: kBackgroundColor,
      body: Stack(
        children: [
          // 1. HEADER (Gradient Arka Plan)
          Container(
            height: size.height * 0.35, // Biraz daha kısa tuttum form uzun diye
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
                    const SizedBox(height: 20),
                    const Text(
                      "Yeni Hesap Oluştur",
                      style: TextStyle(
                        color: Colors.white,
                        fontSize: 24,
                        fontWeight: FontWeight.bold,
                        letterSpacing: 1,
                      ),
                    ),
                    const SizedBox(height: 5),
                    Text(
                      "Yemekhane sistemine kayıt ol",
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

          // 2. FORM KARTI
          Align(
            alignment: Alignment.bottomCenter,
            child: Container(
              height: size.height * 0.82, // Ekranın %82'sini kaplasın
              margin: const EdgeInsets.only(
                left: 20,
                right: 20,
                bottom: 20,
              ), // Alt ve yanlardan boşluk
              padding: const EdgeInsets.all(24),
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
              child: SingleChildScrollView(
                physics: const BouncingScrollPhysics(),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.stretch,
                  children: [
                    // ROW 1: Ad & Soyad
                    Row(
                      children: [
                        Expanded(
                          child: _buildInput(
                            nameController,
                            "Ad",
                            Icons.person_outline,
                          ),
                        ),
                        const SizedBox(width: 15),
                        Expanded(
                          child: _buildInput(
                            surnameController,
                            "Soyad",
                            Icons.person_outline,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 15),

                    // ROW 2: Telefon & Birim
                    Row(
                      children: [
                        Expanded(
                          child: _buildInput(
                            phoneController,
                            "Telefon",
                            Icons.phone_iphone,
                            inputType: TextInputType.phone,
                          ),
                        ),
                        const SizedBox(width: 15),
                        Expanded(
                          child: _buildInput(
                            unitController,
                            "Bağlı Birim",
                            Icons.business,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 15),

                    // E-Posta
                    _buildInput(
                      emailController,
                      "E-posta Adresi",
                      Icons.email_outlined,
                      inputType: TextInputType.emailAddress,
                    ),
                    const SizedBox(height: 15),

                    // ROW 3: Şifreler
                    Row(
                      children: [
                        Expanded(
                          child: _buildInput(
                            passwordController,
                            "Şifre",
                            Icons.lock_outline,
                            isPassword: true,
                          ),
                        ),
                        const SizedBox(width: 15),
                        Expanded(
                          child: _buildInput(
                            passwordConfirmController,
                            "Şifre Tekrar",
                            Icons.lock_outline,
                            isPassword: true,
                          ),
                        ),
                      ],
                    ),
                    const SizedBox(height: 20),

                    // DOSYA YÜKLEME ALANI
                    const Text(
                      "Kurum Kimlik / Belgesi (PDF, JPG/PNG)",
                      style: TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 13,
                        color: Colors.black87,
                      ),
                    ),
                    const SizedBox(height: 8),
                    GestureDetector(
                      onTap: _pickFile,
                      child: Container(
                        padding: const EdgeInsets.all(20),
                        decoration: BoxDecoration(
                          color: kPrimaryColor.withOpacity(
                            0.05,
                          ), // Hafif yeşil zemin
                          borderRadius: BorderRadius.circular(16),
                          border: Border.all(
                            color: kPrimaryColor.withOpacity(0.3),
                            style: BorderStyle.solid,
                            width: 1.5,
                          ),
                        ),
                        child: Row(
                          mainAxisAlignment: MainAxisAlignment.center,
                          children: [
                            Icon(
                              selectedFile == null
                                  ? Icons.cloud_upload_outlined
                                  : Icons.check_circle,
                              color: kPrimaryColor,
                              size: 30,
                            ),
                            const SizedBox(width: 15),
                            Expanded(
                              child: Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text(
                                    selectedFileName ?? "Dosya yükle veya seç",
                                    style: TextStyle(
                                      fontWeight: FontWeight.bold,
                                      color: kPrimaryColor,
                                      fontSize: 14,
                                    ),
                                    maxLines: 1,
                                    overflow: TextOverflow.ellipsis,
                                  ),
                                  if (selectedFile == null)
                                    Text(
                                      "PDF, JPG, PNG (max ~10MB)",
                                      style: TextStyle(
                                        color: Colors.grey.shade500,
                                        fontSize: 11,
                                      ),
                                    ),
                                ],
                              ),
                            ),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(height: 25),

                    // KAYIT OL BUTONU
                    Obx(() {
                      final loading = authController.isLoading.value;
                      return SizedBox(
                        height: 56,
                        child: ElevatedButton(
                          onPressed: loading ? null : _handleRegister,
                          style: ElevatedButton.styleFrom(
                            backgroundColor:
                                kPrimaryColor, // Buton rengi görseldeki gibi yeşil/sarı tonuna da çekilebilir ama primary tutarlı durur
                            foregroundColor: Colors.white,
                            elevation: 5,
                            shape: RoundedRectangleBorder(
                              borderRadius: BorderRadius.circular(16),
                            ),
                          ),
                          child: loading
                              ? const CircularProgressIndicator(
                                  color: Colors.white,
                                )
                              : const Text(
                                  "KAYIT OL",
                                  style: TextStyle(
                                    fontWeight: FontWeight.bold,
                                    fontSize: 16,
                                  ),
                                ),
                        ),
                      );
                    }),

                    const SizedBox(height: 15),

                    // GİRİŞ YAP LINK
                    Row(
                      mainAxisAlignment: MainAxisAlignment.center,
                      children: [
                        Text(
                          "Zaten hesabın var mı? ",
                          style: TextStyle(color: Colors.grey.shade600),
                        ),
                        GestureDetector(
                          onTap: () => Get.back(), // Login sayfasına dön
                          child: Text(
                            "Giriş Yap",
                            style: TextStyle(
                              color: kPrimaryColor,
                              fontWeight: FontWeight.bold,
                              decoration: TextDecoration.underline,
                            ),
                          ),
                        ),
                      ],
                    ),

                    const SizedBox(height: 10),
                    // HATA MESAJI
                    Obx(() {
                      if (authController.errorMessage.value.isNotEmpty) {
                        return Padding(
                          padding: const EdgeInsets.only(top: 10),
                          child: Text(
                            authController.errorMessage.value,
                            style: const TextStyle(
                              color: Colors.red,
                              fontSize: 13,
                              fontWeight: FontWeight.bold,
                            ),
                            textAlign: TextAlign.center,
                          ),
                        );
                      }
                      return const SizedBox.shrink();
                    }),
                  ],
                ),
              ),
            ),
          ),
        ],
      ),
    );
  }

  // --- YARDIMCI METHOD: INPUT OLUŞTURUCU ---
  Widget _buildInput(
    TextEditingController controller,
    String label,
    IconData icon, {
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
            fontSize: 13,
          ),
        ),
        const SizedBox(height: 6),
        Container(
          decoration: BoxDecoration(
            color: Colors.grey.shade50,
            borderRadius: BorderRadius.circular(12),
            border: Border.all(color: Colors.grey.shade300),
          ),
          child: TextField(
            controller: controller,
            obscureText: isPassword,
            keyboardType: inputType,
            style: const TextStyle(fontSize: 14),
            decoration: InputDecoration(
              isDense: true, // Daha kompakt görünüm
              prefixIcon: Icon(icon, color: Colors.grey.shade400, size: 20),
              border: InputBorder.none,
              contentPadding: const EdgeInsets.symmetric(
                vertical: 12,
                horizontal: 10,
              ),
              hintText: label == "Bağlı Birim"
                  ? "Müdürlük adı"
                  : (label == "Telefon" ? "5XX XXX XX XX" : null),
              hintStyle: TextStyle(color: Colors.grey.shade400, fontSize: 13),
            ),
          ),
        ),
      ],
    );
  }

  void _handleRegister() async {
    // 1. Basit validasyonlar
    if (nameController.text.isEmpty ||
        surnameController.text.isEmpty ||
        emailController.text.isEmpty ||
        passwordController.text.isEmpty) {
      Get.snackbar(
        "Eksik Bilgi",
        "Lütfen zorunlu alanları doldurun.",
        backgroundColor: Colors.orange,
        colorText: Colors.white,
      );
      return;
    }

    if (passwordController.text != passwordConfirmController.text) {
      Get.snackbar(
        "Hata",
        "Şifreler eşleşmiyor.",
        backgroundColor: Colors.red,
        colorText: Colors.white,
      );
      return;
    }

    // 2. Controller üzerinden kayıt isteği
    bool success = await authController.register(
      name: nameController.text.trim(),
      surname: surnameController.text.trim(),
      phone: phoneController.text.trim(),
      unit: unitController.text.trim(),
      email: emailController.text.trim(),
      password: passwordController.text.trim(),
      passwordConfirmation: passwordConfirmController.text.trim(),
      document: selectedFile,
    );

    if (success) {
      Get.snackbar(
        "Başarılı",
        "Kayıt oluşturuldu! Yönetici onayı bekleniyor.",
        backgroundColor: Colors.green,
        colorText: Colors.white,
        duration: const Duration(seconds: 4),
      );

      // Kayıt başarılıysa Login ekranına dön
      await Future.delayed(const Duration(seconds: 2));
      Get.back();
    }
  }
}
