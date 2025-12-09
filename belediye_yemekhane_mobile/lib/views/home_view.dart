// lib/views/home_view.dart

import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../controllers/home_controller.dart';
import '../controllers/auth_controller.dart';

class HomeView extends StatelessWidget {
  HomeView({super.key});

  final HomeController homeController = Get.put(HomeController());
  final AuthController authController = Get.find<AuthController>();

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: Colors.grey[100],
      appBar: AppBar(
        title: const Text('Belediye Yemekhane'),
        centerTitle: true,
        backgroundColor: Colors.teal,
        elevation: 0,
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
        if (homeController.isLoading.value) {
          return const Center(child: CircularProgressIndicator());
        }

        if (homeController.errorMessage.value.isNotEmpty) {
          return Center(
            child: Padding(
              padding: const EdgeInsets.all(24.0),
              child: Text(
                'Hata: ${homeController.errorMessage.value}',
                style: const TextStyle(color: Colors.red),
                textAlign: TextAlign.center,
              ),
            ),
          );
        }

        final menu = homeController.todayMenu.value;
        final user = homeController.userProfile.value;
        final bool hasMenu = menu != null && menu.items.isNotEmpty;

        return Column(
          children: [
            Expanded(
              child: SingleChildScrollView(
                padding: const EdgeInsets.all(16.0),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    // --- 1. CÜZDAN & PROFİL KARTI ---
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.all(20),
                      decoration: BoxDecoration(
                        gradient: LinearGradient(
                          colors: [Colors.teal.shade800, Colors.teal.shade400],
                          begin: Alignment.topLeft,
                          end: Alignment.bottomRight,
                        ),
                        borderRadius: BorderRadius.circular(16),
                        boxShadow: [
                          BoxShadow(
                            color: Colors.teal.withOpacity(0.3),
                            blurRadius: 10,
                            offset: const Offset(0, 5),
                          ),
                        ],
                      ),
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              Column(
                                crossAxisAlignment: CrossAxisAlignment.start,
                                children: [
                                  Text("Merhaba,", style: TextStyle(color: Colors.teal.shade100, fontSize: 14)),
                                  const SizedBox(height: 4),
                                  Text(
                                    user?.name ?? 'Misafir',
                                    style: const TextStyle(color: Colors.white, fontSize: 20, fontWeight: FontWeight.bold),
                                  ),
                                ],
                              ),
                              Container(
                                padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
                                decoration: BoxDecoration(
                                  color: Colors.white.withOpacity(0.2),
                                  borderRadius: BorderRadius.circular(20),
                                  border: Border.all(color: Colors.white30)
                                ),
                                child: Text(
                                  user?.unitName ?? 'Birim Yok',
                                  style: const TextStyle(color: Colors.white, fontWeight: FontWeight.bold, fontSize: 12),
                                ),
                              ),
                            ],
                          ),
                          const SizedBox(height: 20),
                          const Divider(color: Colors.white24),
                          const SizedBox(height: 10),
                          Row(
                            mainAxisAlignment: MainAxisAlignment.spaceBetween,
                            children: [
                              const Text("Mevcut Bakiye", style: TextStyle(color: Colors.white70)),
                              Text(
                                "₺${user?.balance.toStringAsFixed(2)}",
                                style: const TextStyle(color: Colors.white, fontSize: 26, fontWeight: FontWeight.bold),
                              ),
                            ],
                          ),
                        ],
                      ),
                    ),

                    const SizedBox(height: 24),

                    // --- 2. MENÜ DURUMU ---
                    Container(
                      width: double.infinity,
                      padding: const EdgeInsets.symmetric(vertical: 12, horizontal: 16),
                      decoration: BoxDecoration(
                        color: hasMenu ? Colors.green.shade50 : Colors.orange.shade50,
                        borderRadius: BorderRadius.circular(10),
                        border: Border.all(
                          color: hasMenu ? Colors.green.shade200 : Colors.orange.shade200,
                        ),
                      ),
                      child: Row(
                        children: [
                          Icon(
                            hasMenu ? Icons.restaurant_menu : Icons.info_outline,
                            color: hasMenu ? Colors.green[700] : Colors.orange[700],
                          ),
                          const SizedBox(width: 10),
                          Text(
                            hasMenu ? "Menü Servise Hazır" : "Menü Bulunamadı",
                            style: TextStyle(
                              color: hasMenu ? Colors.green[800] : Colors.orange[800],
                              fontWeight: FontWeight.bold,
                            ),
                          ),
                        ],
                      ),
                    ),

                    const SizedBox(height: 16),

                    // --- 3. MENÜ LİSTESİ ---
                    Card(
                      elevation: 3,
                      shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(16)),
                      color: Colors.white,
                      child: Padding(
                        padding: const EdgeInsets.all(20.0),
                        child: Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Row(
                              mainAxisAlignment: MainAxisAlignment.spaceBetween,
                              children: [
                                Text(
                                  "🍽️ Günün Menüsü",
                                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold, color: Colors.grey[800]),
                                ),
                                if (hasMenu && menu.totalCalories > 0)
                                  Chip(
                                    label: Text("${menu.totalCalories} kcal", style: const TextStyle(color: Colors.white, fontSize: 12)),
                                    backgroundColor: Colors.teal.shade300,
                                    padding: EdgeInsets.zero,
                                    visualDensity: VisualDensity.compact,
                                  ),
                              ],
                            ),
                            const Divider(thickness: 1, height: 25),

                            if (!hasMenu)
                              const Padding(
                                padding: EdgeInsets.symmetric(vertical: 20),
                                child: Center(
                                  child: Text(
                                    "Bugün için yemek servisi bulunmuyor.",
                                    style: TextStyle(color: Colors.grey),
                                  ),
                                ),
                              )
                            else
                              ...menu.items.asMap().entries.map((entry) {
                                return _buildDynamicItem(entry.key + 1, entry.value);
                              }).toList(),
                          ],
                        ),
                      ),
                    ),
                    const SizedBox(height: 80),
                  ],
                ),
              ),
            ),

            // --- 4. AKILLI SATIN ALMA BUTONU (EN ALTTA) ---
            if (hasMenu)
              _buildSmartPurchaseButton(user, homeController),
          ],
        );
      }),
    );
  }

  // 🟢 YENİ: Akıllı Buton Mantığı (Saat ve Satın Alma Durumuna Göre)
  Widget _buildSmartPurchaseButton(UserModel? user, HomeController controller) {
    // 1. Şu anki zamanı al
    final now = DateTime.now();
    // 2. Bugünün saat 12:00'sini oluştur
    final deadline = DateTime(now.year, now.month, now.day, 12, 0);
    
    // 3. Kontroller
    final bool isPurchased = user?.hasPurchased ?? false; // Zaten aldı mı?
    final bool isPastDeadline = now.isAfter(deadline);    // Saat 12'yi geçti mi?

    String buttonText = "";
    Color buttonColor = Colors.grey;
    IconData buttonIcon = Icons.lock_clock;
    VoidCallback? onPressed;

    if (isPurchased) {
      // DURUM 1: Zaten Satın Almış
      buttonText = "MENÜYÜ SATIN ALDINIZ";
      buttonColor = Colors.green.shade600;
      buttonIcon = Icons.check_circle;
      onPressed = null; // Tıklanamaz
    } else if (isPastDeadline) {
      // DURUM 2: Saat 12'yi Geçmiş
      buttonText = "SATIN ALMA ZAMANI DIŞINDASINIZ";
      buttonColor = Colors.grey.shade600;
      buttonIcon = Icons.timer_off;
      onPressed = null; // Tıklanamaz
    } else {
      // DURUM 3: Satın Alabilir
      buttonText = "SATIN AL • ₺${user?.unitPrice.toStringAsFixed(2) ?? '0.00'}";
      buttonColor = Colors.orange.shade700;
      buttonIcon = Icons.shopping_cart_checkout;
      onPressed = () => controller.purchaseMeal(); // Tıklanabilir
    }

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 10,
            offset: const Offset(0, -5),
          ),
        ],
      ),
      child: SafeArea(
        child: ElevatedButton(
          onPressed: onPressed,
          style: ElevatedButton.styleFrom(
            backgroundColor: buttonColor,
            foregroundColor: Colors.white, // Yazı rengi
            disabledBackgroundColor: buttonColor.withOpacity(0.7), // Pasifkenki renk
            disabledForegroundColor: Colors.white, // Pasifkenki yazı rengi
            padding: const EdgeInsets.symmetric(vertical: 16),
            shape: RoundedRectangleBorder(borderRadius: BorderRadius.circular(12)),
            elevation: 2,
          ),
          child: Row(
            mainAxisAlignment: MainAxisAlignment.center,
            children: [
              Icon(buttonIcon),
              const SizedBox(width: 10),
              Text(
                buttonText,
                style: const TextStyle(fontSize: 16, fontWeight: FontWeight.bold),
              ),
            ],
          ),
        ),
      ),
    );
  }

  Widget _buildDynamicItem(int index, MenuItem item) {
    return Padding(
      padding: const EdgeInsets.only(bottom: 12.0),
      child: Row(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Container(
            width: 24,
            height: 24,
            alignment: Alignment.center,
            decoration: BoxDecoration(
              color: Colors.teal.shade50,
              shape: BoxShape.circle,
            ),
            child: Text(
              "$index",
              style: TextStyle(color: Colors.teal.shade800, fontWeight: FontWeight.bold, fontSize: 12),
            ),
          ),
          const SizedBox(width: 12),
          Expanded(
            child: Text(
              item.name,
              style: const TextStyle(fontSize: 16, fontWeight: FontWeight.w500),
            ),
          ),
          if (item.calorie != null && item.calorie! > 0)
            Text(
              "${item.calorie} kcal",
              style: TextStyle(fontSize: 13, color: Colors.grey.shade500),
            ),
        ],
      ),
    );
  }
}