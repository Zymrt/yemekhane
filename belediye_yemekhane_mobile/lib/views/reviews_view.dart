// lib/views/home_view.dart

import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../controllers/home_controller.dart';
import '../controllers/auth_controller.dart';

import 'transactions_view.dart';
import 'reviews_view.dart';

class HomeView extends StatelessWidget {
  HomeView({super.key});

  final HomeController controller = Get.put(HomeController());
  final AuthController authController = Get.find<AuthController>();

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF0FDF4),
      appBar: AppBar(
        title: const Text('Yemekhane Sistemi'),
        centerTitle: true,
        backgroundColor: Colors.transparent,
        elevation: 0,
        flexibleSpace: Container(
          decoration: const BoxDecoration(
            gradient: LinearGradient(
              colors: [Color(0xFF2DD4BF), Color(0xFF14B8A6)],
              begin: Alignment.topLeft,
              end: Alignment.bottomRight,
            ),
          ),
        ),
        actions: [
          IconButton(
            icon: const Icon(Icons.refresh, color: Colors.white),
            onPressed: controller.fetchData,
          ),
          IconButton(
            icon: const Icon(Icons.logout, color: Colors.white),
            onPressed: authController.logout,
          ),
        ],
      ),
      body: Obx(() {
        if (controller.isLoading.value) {
          return const Center(child: CircularProgressIndicator());
        }

        if (controller.errorMessage.value.isNotEmpty) {
          return Center(child: Text("Hata: ${controller.errorMessage.value}"));
        }

        final user = controller.userProfile.value;
        final menu = controller.todayMenu.value;
        final hasMenu = menu != null && menu.items.isNotEmpty;

        return SingleChildScrollView(
          padding: const EdgeInsets.all(16.0),
          child: Column(
            children: [
              // 1. PROFİL KARTI
              _buildProfileCard(user),

              const SizedBox(height: 16),

              // 2. YOĞUNLUK KARTI
              _buildOccupancyCard(controller.occupancyRate.value),

              const SizedBox(height: 16),

              // 3. QR KOD KARTI (Sadece Satın Alınca Görünür)
              if (user?.hasPurchased == true) _buildQrCard(user!),

              const SizedBox(height: 16),

              // 4. MENÜ LİSTESİ + SATIN ALMA BUTONU
              _buildMenuSection(menu, hasMenu, user, controller),

              const SizedBox(height: 16),

              // 5. YORUM & PUANLAMA KARTI (Sadece satın alınca)
              if (user?.hasPurchased == true) _buildReviewCard(controller),

              const SizedBox(height: 30),
            ],
          ),
        );
      }),
    );
  }

  // 1. Profil ve Bakiye Kartı
  Widget _buildProfileCard(UserModel? user) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [Colors.teal.shade300, Colors.teal.shade600],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(20),
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
                  Text(
                    "Hoş Geldin,",
                    style: TextStyle(color: Colors.teal.shade50, fontSize: 14),
                  ),
                  Text(
                    user?.name ?? 'Misafir',
                    style: const TextStyle(
                      color: Colors.white,
                      fontSize: 22,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ],
              ),
              if ((user?.unitName ?? '').isNotEmpty)
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 12,
                    vertical: 6,
                  ),
                  decoration: BoxDecoration(
                    color: Colors.white24,
                    borderRadius: BorderRadius.circular(12),
                  ),
                  child: Text(
                    user?.unitName ?? '',
                    style: const TextStyle(
                      color: Colors.white,
                      fontWeight: FontWeight.bold,
                    ),
                  ),
                ),
            ],
          ),
          const SizedBox(height: 20),
          const Text(
            "CÜZDAN BAKİYESİ",
            style: TextStyle(
              color: Colors.white70,
              fontSize: 12,
              letterSpacing: 1,
            ),
          ),
          Text(
            "₺${user?.balance.toStringAsFixed(2) ?? '0.00'}",
            style: const TextStyle(
              color: Colors.white,
              fontSize: 32,
              fontWeight: FontWeight.bold,
            ),
          ),
        ],
      ),
    );
  }

  // 2. Yemekhane Yoğunluğu
  Widget _buildOccupancyCard(int rate) {
    return Container(
      padding: const EdgeInsets.all(16),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(16),
        boxShadow: const [BoxShadow(color: Colors.black12, blurRadius: 5)],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Text(
                "🔴 Yemekhane Yoğunluğu",
                style: TextStyle(
                  fontWeight: FontWeight.bold,
                  color: Colors.grey,
                ),
              ),
              Text(
                "%$rate",
                style: const TextStyle(
                  fontWeight: FontWeight.bold,
                  color: Colors.teal,
                ),
              ),
            ],
          ),
          const SizedBox(height: 10),
          ClipRRect(
            borderRadius: BorderRadius.circular(10),
            child: LinearProgressIndicator(
              value: rate / 100,
              backgroundColor: Colors.grey.shade200,
              color: rate > 80
                  ? Colors.red
                  : (rate > 50 ? Colors.orange : Colors.green),
              minHeight: 8,
            ),
          ),
        ],
      ),
    );
  }

  // 3. QR Kod Kartı
  Widget _buildQrCard(UserModel user) {
    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        border: Border.all(color: Colors.teal.shade100, width: 2),
        boxShadow: [
          BoxShadow(color: Colors.teal.withOpacity(0.1), blurRadius: 10),
        ],
      ),
      child: Column(
        children: [
          const Text(
            "Dijital Kartınız",
            style: TextStyle(fontWeight: FontWeight.bold, fontSize: 16),
          ),
          const SizedBox(height: 10),
          const Icon(Icons.qr_code_2, size: 120, color: Colors.black87),
          const SizedBox(height: 10),
          Text(
            "Görevliye gösteriniz",
            style: TextStyle(
              color: Colors.teal.shade600,
              fontWeight: FontWeight.bold,
              fontSize: 12,
            ),
          ),
        ],
      ),
    );
  }

  // 4. Menü Listesi + SATIN ALMA BUTONU (kartın içinde)
  Widget _buildMenuSection(
    MenuModel? menu,
    bool hasMenu,
    UserModel? user,
    HomeController controller,
  ) {
    final bool isPurchased = user?.hasPurchased ?? false;

    // Saat kontrolü (12:00 sonrası kilit)
    final now = DateTime.now();
    final deadline = DateTime(now.year, now.month, now.day, 12, 0);
    final bool isPastDeadline = now.isAfter(deadline);

    String buttonText = "";
    Color buttonColor = Colors.grey.shade600;
    IconData buttonIcon = Icons.lock_clock;
    VoidCallback? onPressed;

    if (!hasMenu) {
      // Menü yoksa zaten satın alma opsiyonu yok
      buttonText = "Bugün için menü bulunmuyor";
      buttonColor = Colors.grey.shade500;
      buttonIcon = Icons.info_outline;
      onPressed = null;
    } else if (isPurchased) {
      buttonText = "MENÜYÜ SATIN ALDINIZ";
      buttonColor = Colors.green.shade600;
      buttonIcon = Icons.check_circle;
      onPressed = null; // pasif
    } else if (isPastDeadline) {
      buttonText = "SATIN ALMA SÜRESİ DOLMUŞTUR (12:00)";
      buttonColor = Colors.grey.shade700;
      buttonIcon = Icons.timer_off;
      onPressed = null; // pasif
    } else {
      final price = user?.unitPrice ?? 0;

      if (price == 0) {
        // Stajyer / ücretsiz yemek durumu
        buttonText = "ÜCRETSİZ AL";
      } else {
        buttonText = "SATIN AL • ₺${price.toStringAsFixed(2)}";
      }

      buttonColor = Colors.orange.shade700;
      buttonIcon = Icons.shopping_cart_checkout;
      onPressed = () => controller.purchaseMeal();
    }

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        boxShadow: const [BoxShadow(color: Colors.black12, blurRadius: 5)],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          // Başlık
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              Column(
                crossAxisAlignment: CrossAxisAlignment.start,
                children: [
                  const Text(
                    "Bugünün Menüsü",
                    style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                  ),
                  Text(
                    DateTime.now().toString().split(' ')[0],
                    style: const TextStyle(color: Colors.grey, fontSize: 12),
                  ),
                ],
              ),
              if (isPurchased)
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 10,
                    vertical: 5,
                  ),
                  decoration: BoxDecoration(
                    color: Colors.green.shade100,
                    borderRadius: BorderRadius.circular(8),
                  ),
                  child: const Text(
                    "Menü Satın Alındı",
                    style: TextStyle(
                      color: Colors.green,
                      fontWeight: FontWeight.bold,
                      fontSize: 10,
                    ),
                  ),
                ),
            ],
          ),

          const Divider(height: 30),

          // Yemek Listesi
          if (!hasMenu)
            const Center(
              child: Text(
                "Menü yüklenmedi.",
                style: TextStyle(color: Colors.grey),
              ),
            )
          else
            ...menu!.items.asMap().entries.map((entry) {
              return Padding(
                padding: const EdgeInsets.only(bottom: 12),
                child: Row(
                  children: [
                    CircleAvatar(
                      backgroundColor: Colors.orange.shade50,
                      radius: 14,
                      child: Text(
                        "${entry.key + 1}",
                        style: TextStyle(
                          color: Colors.orange.shade800,
                          fontWeight: FontWeight.bold,
                          fontSize: 12,
                        ),
                      ),
                    ),
                    const SizedBox(width: 12),
                    Expanded(
                      child: Text(
                        entry.value.name,
                        style: const TextStyle(fontWeight: FontWeight.w500),
                      ),
                    ),
                    if (entry.value.calorie != null)
                      Container(
                        padding: const EdgeInsets.symmetric(
                          horizontal: 8,
                          vertical: 2,
                        ),
                        decoration: BoxDecoration(
                          color: Colors.grey.shade100,
                          borderRadius: BorderRadius.circular(4),
                        ),
                        child: Text(
                          "${entry.value.calorie} kcal",
                          style: const TextStyle(
                            fontSize: 10,
                            color: Colors.grey,
                          ),
                        ),
                      ),
                  ],
                ),
              );
            }).toList(),

          const SizedBox(height: 20),

          // SATIN AL / KİLİT / ALINDI BUTONU
          SizedBox(
            width: double.infinity,
            child: ElevatedButton.icon(
              onPressed: onPressed,
              style: ElevatedButton.styleFrom(
                backgroundColor: buttonColor,
                foregroundColor: Colors.white,
                disabledBackgroundColor: buttonColor.withOpacity(0.8),
                disabledForegroundColor: Colors.white,
                padding: const EdgeInsets.symmetric(vertical: 14),
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(12),
                ),
              ),
              icon: Icon(buttonIcon),
              label: Text(
                buttonText,
                style: const TextStyle(
                  fontSize: 14,
                  fontWeight: FontWeight.bold,
                ),
                overflow: TextOverflow.ellipsis,
              ),
            ),
          ),
        ],
      ),
    );
  }

  // Dinamik Liste Elemanı (şu an kullanılmıyor ama dursun istersen)
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
              style: TextStyle(
                color: Colors.teal.shade800,
                fontWeight: FontWeight.bold,
                fontSize: 12,
              ),
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

  // Yorum ve Puan Kartı
  Widget _buildReviewCard(HomeController controller) {
    return Container(
      padding: const EdgeInsets.all(20),
      decoration: BoxDecoration(
        color: Colors.white,
        borderRadius: BorderRadius.circular(20),
        boxShadow: const [BoxShadow(color: Colors.black12, blurRadius: 5)],
      ),
      child: Obx(
        () => Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            const Text(
              "⭐ Menüyü Değerlendir",
              style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
            ),
            const Divider(),

            // Yıldızlar
            Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: List.generate(5, (index) {
                int ratingValue = index + 1;
                bool isSubmitting = controller.isReviewSubmitting.value;
                return IconButton(
                  icon: Icon(
                    controller.currentRating.value >= ratingValue
                        ? Icons.star
                        : Icons.star_border,
                    color: controller.currentRating.value >= ratingValue
                        ? Colors.amber
                        : Colors.grey,
                    size: 36,
                  ),
                  onPressed: isSubmitting
                      ? null
                      : () => controller.currentRating.value = ratingValue
                            .toDouble(),
                );
              }),
            ),

            const SizedBox(height: 15),

            // Yorum alanı
            TextField(
              controller: controller.commentController,
              enabled: !controller.isReviewSubmitting.value,
              decoration: const InputDecoration(
                hintText: "Yorumunuzu buraya yazın (opsiyonel)",
                border: OutlineInputBorder(),
                contentPadding: EdgeInsets.all(12),
              ),
              minLines: 2,
              maxLines: 4,
            ),

            const SizedBox(height: 15),

            // Gönder butonu
            SizedBox(
              width: double.infinity,
              child: ElevatedButton.icon(
                onPressed: controller.isReviewSubmitting.value
                    ? null
                    : () => controller.submitReview(),
                icon: controller.isReviewSubmitting.value
                    ? const SizedBox(
                        width: 20,
                        height: 20,
                        child: CircularProgressIndicator(
                          color: Colors.white,
                          strokeWidth: 2,
                        ),
                      )
                    : const Icon(Icons.send),
                label: Text(
                  controller.isReviewSubmitting.value
                      ? "Gönderiliyor..."
                      : "Değerlendirmeyi Gönder",
                ),
                style: ElevatedButton.styleFrom(
                  backgroundColor: Colors.teal,
                  padding: const EdgeInsets.symmetric(vertical: 12),
                ),
              ),
            ),
          ],
        ),
      ),
    );
  }
}
