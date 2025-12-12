// lib/views/home_view.dart

import 'package:flutter/material.dart';
import 'package:get/get.dart';
import 'package:qr_flutter/qr_flutter.dart';

import '../controllers/home_controller.dart';
import '../controllers/auth_controller.dart';

// 🔥 Yeni sayfalar için importlar
import 'account_info_view.dart';
import 'transactions_view.dart';
// import 'balance_view.dart'; // Bakiye sayfan hazır olunca açarsın

class HomeView extends StatelessWidget {
  HomeView({super.key});

  final HomeController controller = Get.put(HomeController());
  final AuthController authController = Get.find<AuthController>();

  // --- RENK PALETİ (Premium Green Theme) ---
  final Color kPrimaryColor = const Color(0xFF064E3B); // Koyu Zümrüt
  final Color kAccentColor = const Color(0xFF10B981); // Canlı Yeşil
  final Color kSecondaryColor = const Color(0xFFECFDF5); // Açık Nane
  final Color kSurfaceColor = const Color(0xFFFFFFFF); // Beyaz
  final Color kGoldColor = const Color(0xFFF59E0B); // Altın Sarısı

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF3F4F6), // Hafif gri arka plan
      body: Stack(
        children: [
          // 1. ARKA PLAN DEKORASYONU (Gradient Header)
          Container(
            height: 280,
            decoration: BoxDecoration(
              gradient: LinearGradient(
                colors: [kPrimaryColor, kAccentColor],
                begin: Alignment.topLeft,
                end: Alignment.bottomRight,
              ),
              borderRadius: const BorderRadius.only(
                bottomLeft: Radius.circular(30),
                bottomRight: Radius.circular(30),
              ),
            ),
          ),

          // 2. ANA İÇERİK
          SafeArea(
            child: Obx(() {
              if (controller.isLoading.value) {
                return Center(
                  child: CircularProgressIndicator(color: kSurfaceColor),
                );
              }

              final user = controller.userProfile.value;
              final menu = controller.todayMenu.value;
              final hasMenu = menu != null && menu.items.isNotEmpty;

              return Column(
                children: [
                  // HEADER (İsim ve Sağda ikonlar)
                  Padding(
                    padding: const EdgeInsets.symmetric(
                      horizontal: 20,
                      vertical: 10,
                    ),
                    child: Row(
                      mainAxisAlignment: MainAxisAlignment.spaceBetween,
                      children: [
                        Column(
                          crossAxisAlignment: CrossAxisAlignment.start,
                          children: [
                            Text(
                              "Hoş Geldin,",
                              style: TextStyle(
                                color: kSurfaceColor.withOpacity(0.8),
                                fontSize: 14,
                              ),
                            ),
                            const SizedBox(height: 4),
                            Text(
                              user?.name ?? 'Misafir',
                              style: TextStyle(
                                color: kSurfaceColor,
                                fontSize: 24,
                                fontWeight: FontWeight.bold,
                              ),
                            ),
                          ],
                        ),
                        Row(
                          children: [
                            // 🔥 HAMBURGER MENÜ BUTONU
                            _buildIconButton(
                              Icons.menu,
                              () => _openQuickMenu(context),
                            ),
                            const SizedBox(width: 10),
                            _buildIconButton(
                              Icons.refresh,
                              controller.fetchData,
                            ),
                            const SizedBox(width: 10),
                            _buildIconButton(
                              Icons.logout,
                              authController.logout,
                              isRed: true,
                            ),
                          ],
                        ),
                      ],
                    ),
                  ),

                  const SizedBox(height: 10),

                  // SCROLL EDİLEBİLİR ALAN
                  Expanded(
                    child: SingleChildScrollView(
                      physics: const BouncingScrollPhysics(),
                      padding: const EdgeInsets.symmetric(
                        horizontal: 20,
                        vertical: 10,
                      ),
                      child: Column(
                        children: [
                          // 1. PREMIUM KART (Bakiye)
                          _buildPremiumCard(user),

                          const SizedBox(height: 20),

                          // 2. YOĞUNLUK & DURUM
                          _buildOccupancySection(
                            controller.occupancyRate.value,
                          ),

                          const SizedBox(height: 20),

                          // 3. QR TICKET (Varsa)
                          if (user?.hasPurchased == true && user != null)
                            _buildModernQrTicket(user),

                          if (user?.hasPurchased == true)
                            const SizedBox(height: 20),

                          // 4. MENÜ LİSTESİ
                          _buildMenuSection(menu, hasMenu, user, controller),

                          const SizedBox(height: 20),

                          // 5. YORUM ALANI
                          if (user?.hasPurchased == true)
                            _buildModernReviewCard(controller),

                          const SizedBox(height: 40),
                        ],
                      ),
                    ),
                  ),
                ],
              );
            }),
          ),
        ],
      ),
    );
  }

  // 🔥 HAMBURGER MENÜ → ALT SHEET
  void _openQuickMenu(BuildContext context) {
    showModalBottomSheet(
      context: context,
      backgroundColor: Colors.white,
      shape: const RoundedRectangleBorder(
        borderRadius: BorderRadius.vertical(top: Radius.circular(24)),
      ),
      builder: (ctx) {
        return SafeArea(
          child: Padding(
            padding: const EdgeInsets.fromLTRB(16, 12, 16, 24),
            child: Column(
              mainAxisSize: MainAxisSize.min,
              children: [
                Container(
                  width: 40,
                  height: 4,
                  margin: const EdgeInsets.only(bottom: 12),
                  decoration: BoxDecoration(
                    color: Colors.grey.shade300,
                    borderRadius: BorderRadius.circular(999),
                  ),
                ),
                const Text(
                  'Menü',
                  style: TextStyle(fontSize: 18, fontWeight: FontWeight.bold),
                ),
                const SizedBox(height: 12),

                // Hesap Bilgileri
                ListTile(
                  leading: const Icon(Icons.person_outline),
                  title: const Text('Hesap Bilgileri'),
                  onTap: () {
                    Navigator.pop(ctx);
                    Get.to(() => AccountInfoView());
                  },
                ),

                // Bakiye Yükle
                ListTile(
                  leading: const Icon(Icons.account_balance_wallet_outlined),
                  title: const Text('Bakiye Yükle'),
                  onTap: () {
                    Navigator.pop(ctx);
                    // TODO: Bakiye ekranın hazır olunca aç:
                    // Get.to(() => BalanceView());
                  },
                ),

                // Hesap Hareketleri
                ListTile(
                  leading: const Icon(Icons.history),
                  title: const Text('Hesap Hareketleri'),
                  onTap: () {
                    Navigator.pop(ctx);
                    Get.to(() => TransactionsView());
                  },
                ),

                const Divider(),

                // Çıkış Yap
                ListTile(
                  leading: const Icon(Icons.logout, color: Colors.red),
                  title: const Text(
                    'Çıkış Yap',
                    style: TextStyle(color: Colors.red),
                  ),
                  onTap: () async {
                    Navigator.pop(ctx);
                    await authController.logout();
                  },
                ),
              ],
            ),
          ),
        );
      },
    );
  }

  // --- TASARIM BİLEŞENLERİ ---

  Widget _buildIconButton(
    IconData icon,
    VoidCallback onPressed, {
    bool isRed = false,
  }) {
    return Container(
      decoration: BoxDecoration(
        color: Colors.white.withOpacity(0.2),
        borderRadius: BorderRadius.circular(12),
      ),
      child: IconButton(
        icon: Icon(icon, color: Colors.white, size: 20),
        onPressed: onPressed,
        constraints: const BoxConstraints(minWidth: 40, minHeight: 40),
        padding: EdgeInsets.zero,
      ),
    );
  }

  // 1. PREMIUM BALANCE CARD
  Widget _buildPremiumCard(UserModel? user) {
    final balanceText = (user?.balance ?? 0).toStringAsFixed(2);

    return Container(
      width: double.infinity,
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        gradient: LinearGradient(
          colors: [Colors.black87, Colors.grey.shade900],
          begin: Alignment.topLeft,
          end: Alignment.bottomRight,
        ),
        borderRadius: BorderRadius.circular(24),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.3),
            blurRadius: 20,
            offset: const Offset(0, 10),
          ),
        ],
      ),
      child: Column(
        crossAxisAlignment: CrossAxisAlignment.start,
        children: [
          Row(
            mainAxisAlignment: MainAxisAlignment.spaceBetween,
            children: [
              const Icon(Icons.nfc, color: Colors.white54, size: 30),
              if ((user?.unitName ?? '').isNotEmpty)
                Container(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 10,
                    vertical: 4,
                  ),
                  decoration: BoxDecoration(
                    color: kGoldColor,
                    borderRadius: BorderRadius.circular(20),
                  ),
                  child: Text(
                    user?.unitName.toUpperCase() ?? '',
                    style: const TextStyle(
                      color: Colors.black,
                      fontWeight: FontWeight.bold,
                      fontSize: 10,
                    ),
                  ),
                ),
            ],
          ),
          const SizedBox(height: 20),
          Text(
            "CÜZDAN BAKİYESİ",
            style: TextStyle(
              color: Colors.white.withOpacity(0.6),
              fontSize: 12,
              letterSpacing: 2,
            ),
          ),
          const SizedBox(height: 5),
          Row(
            crossAxisAlignment: CrossAxisAlignment.baseline,
            textBaseline: TextBaseline.alphabetic,
            children: [
              const Text(
                "₺",
                style: TextStyle(
                  color: Colors.white,
                  fontSize: 24,
                  fontWeight: FontWeight.w300,
                ),
              ),
              const SizedBox(width: 4),
              Text(
                balanceText,
                style: const TextStyle(
                  color: Colors.white,
                  fontSize: 40,
                  fontWeight: FontWeight.bold,
                ),
              ),
            ],
          ),
          const SizedBox(height: 20),
          Row(
            children: [
              Text(
                // buradaki null kontrolünü sen sonra istersen iyileştirirsin
                "**** **** **** ${user?.id.substring(user!.id.length - 4) ?? '0000'}",
                style: TextStyle(
                  color: Colors.white.withOpacity(0.5),
                  fontSize: 16,
                ),
              ),
            ],
          ),
        ],
      ),
    );
  }

  // 2. OCCUPANCY BAR
  Widget _buildOccupancySection(int rate) {
    Color barColor = kAccentColor;
    if (rate > 50) barColor = Colors.orange;
    if (rate > 80) barColor = Colors.red;

    return Container(
      padding: const EdgeInsets.symmetric(horizontal: 20, vertical: 15),
      decoration: BoxDecoration(
        color: kSurfaceColor,
        borderRadius: BorderRadius.circular(16),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 10,
            offset: const Offset(0, 4),
          ),
        ],
      ),
      child: Row(
        children: [
          Container(
            padding: const EdgeInsets.all(8),
            decoration: BoxDecoration(
              color: barColor.withOpacity(0.1),
              shape: BoxShape.circle,
            ),
            child: Icon(Icons.people_alt, color: barColor, size: 20),
          ),
          const SizedBox(width: 15),
          Expanded(
            child: Column(
              crossAxisAlignment: CrossAxisAlignment.start,
              children: [
                Row(
                  mainAxisAlignment: MainAxisAlignment.spaceBetween,
                  children: [
                    const Text(
                      "Yemekhane Doluluğu",
                      style: TextStyle(
                        fontWeight: FontWeight.bold,
                        fontSize: 14,
                      ),
                    ),
                    Text(
                      "%$rate",
                      style: TextStyle(
                        fontWeight: FontWeight.bold,
                        color: barColor,
                      ),
                    ),
                  ],
                ),
                const SizedBox(height: 8),
                ClipRRect(
                  borderRadius: BorderRadius.circular(10),
                  child: LinearProgressIndicator(
                    value: rate / 100,
                    backgroundColor: Colors.grey.shade100,
                    color: barColor,
                    minHeight: 8,
                  ),
                ),
              ],
            ),
          ),
        ],
      ),
    );
  }

  // 3. MODERN QR TICKET
  Widget _buildModernQrTicket(UserModel user) {
    return Container(
      decoration: BoxDecoration(
        color: kSurfaceColor,
        borderRadius: BorderRadius.circular(24),
        boxShadow: [
          BoxShadow(
            color: kAccentColor.withOpacity(0.2),
            blurRadius: 15,
            offset: const Offset(0, 8),
          ),
        ],
      ),
      child: Column(
        children: [
          // Üst Kısım (Başlık)
          Container(
            padding: const EdgeInsets.symmetric(vertical: 12),
            width: double.infinity,
            decoration: BoxDecoration(
              color: kAccentColor.withOpacity(0.1),
              borderRadius: const BorderRadius.vertical(
                top: Radius.circular(24),
              ),
            ),
            child: Center(
              child: Text(
                "DİJİTAL GİRİŞ KARTI",
                style: TextStyle(
                  color: kPrimaryColor,
                  fontWeight: FontWeight.bold,
                  letterSpacing: 1.5,
                  fontSize: 12,
                ),
              ),
            ),
          ),

          const SizedBox(height: 20),

          QrImageView(
            data: user.id,
            version: QrVersions.auto,
            size: 180,
            eyeStyle: QrEyeStyle(
              eyeShape: QrEyeShape.circle,
              color: kPrimaryColor,
            ),
            dataModuleStyle: QrDataModuleStyle(
              dataModuleShape: QrDataModuleShape.circle,
              color: kPrimaryColor,
            ),
          ),

          const SizedBox(height: 10),

          Text(
            "Görevliye Gösteriniz",
            style: TextStyle(color: Colors.grey.shade500, fontSize: 12),
          ),
          const SizedBox(height: 20),

          // Tırtıklı Alt Kısım Efekti
          Row(
            children: List.generate(
              20,
              (index) => Expanded(
                child: Padding(
                  padding: const EdgeInsets.symmetric(horizontal: 2),
                  child: Container(height: 2, color: Colors.grey.shade200),
                ),
              ),
            ),
          ),
          const SizedBox(height: 10),
        ],
      ),
    );
  }

  // 4. MENU SECTION
  Widget _buildMenuSection(
    MenuModel? menu,
    bool hasMenu,
    UserModel? user,
    HomeController controller,
  ) {
    final bool isPurchased = user?.hasPurchased ?? false;
    final now = DateTime.now();
    final deadline = DateTime(now.year, now.month, now.day, 12, 0);
    final bool isPastDeadline = now.isAfter(deadline);

    String btnText;
    Color btnColor;
    IconData btnIcon;
    VoidCallback? onPressed;

    if (!hasMenu) {
      btnText = "Menü Hazırlanıyor";
      btnColor = Colors.grey;
      btnIcon = Icons.hourglass_empty;
      onPressed = null;
    } else if (isPurchased) {
      btnText = "SATIN ALINDI";
      btnColor = kAccentColor;
      btnIcon = Icons.check_circle;
      onPressed = null;
    } else if (isPastDeadline) {
      btnText = "SÜRE DOLDU";
      btnColor = Colors.red.shade400;
      btnIcon = Icons.timer_off;
      onPressed = null;
    } else {
      btnText = "SATIN AL • ₺${user?.unitPrice.toStringAsFixed(2)}";
      btnColor = kGoldColor;
      btnIcon = Icons.shopping_bag;
      onPressed = () => controller.purchaseMeal();
    }

    return Column(
      crossAxisAlignment: CrossAxisAlignment.start,
      children: [
        Row(
          mainAxisAlignment: MainAxisAlignment.spaceBetween,
          children: [
            const Text(
              "Bugünün Menüsü",
              style: TextStyle(
                fontSize: 18,
                fontWeight: FontWeight.bold,
                color: Colors.black87,
              ),
            ),
            Container(
              padding: const EdgeInsets.symmetric(horizontal: 12, vertical: 6),
              decoration: BoxDecoration(
                color: Colors.white,
                borderRadius: BorderRadius.circular(20),
              ),
              child: Row(
                children: [
                  const Icon(
                    Icons.calendar_today,
                    size: 14,
                    color: Colors.grey,
                  ),
                  const SizedBox(width: 4),
                  Text(
                    DateTime.now().toString().split(' ')[0],
                    style: const TextStyle(
                      fontSize: 12,
                      fontWeight: FontWeight.w600,
                      color: Colors.grey,
                    ),
                  ),
                ],
              ),
            ),
          ],
        ),
        const SizedBox(height: 15),

        // YEMEK KARTLARI LİSTESİ
        if (!hasMenu)
          Container(
            padding: const EdgeInsets.all(30),
            width: double.infinity,
            decoration: BoxDecoration(
              color: kSurfaceColor,
              borderRadius: BorderRadius.circular(16),
            ),
            child: Column(
              children: [
                Icon(
                  Icons.restaurant_menu,
                  size: 40,
                  color: Colors.grey.shade300,
                ),
                const SizedBox(height: 10),
                const Text(
                  "Henüz menü girilmedi.",
                  style: TextStyle(color: Colors.grey),
                ),
              ],
            ),
          )
        else
          ListView.separated(
            physics: const NeverScrollableScrollPhysics(),
            shrinkWrap: true,
            itemCount: menu!.items.length,
            separatorBuilder: (c, i) => const SizedBox(height: 10),
            itemBuilder: (context, index) {
              final item = menu.items[index];
              return Container(
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: kSurfaceColor,
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.black.withOpacity(0.03),
                      blurRadius: 10,
                      offset: const Offset(0, 4),
                    ),
                  ],
                ),
                child: Row(
                  children: [
                    Container(
                      height: 40,
                      width: 40,
                      decoration: BoxDecoration(
                        color: kSecondaryColor,
                        borderRadius: BorderRadius.circular(12),
                      ),
                      child: Center(
                        child: Text(
                          "${index + 1}",
                          style: TextStyle(
                            color: kPrimaryColor,
                            fontWeight: FontWeight.bold,
                            fontSize: 16,
                          ),
                        ),
                      ),
                    ),
                    const SizedBox(width: 16),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            item.name,
                            style: const TextStyle(
                              fontWeight: FontWeight.bold,
                              fontSize: 15,
                              color: Colors.black87,
                            ),
                          ),
                          if (item.calorie != null)
                            Text(
                              "${item.calorie} kcal",
                              style: TextStyle(
                                fontSize: 12,
                                color: Colors.grey.shade500,
                              ),
                            ),
                        ],
                      ),
                    ),
                  ],
                ),
              );
            },
          ),

        const SizedBox(height: 20),

        // SATIN ALMA BUTONU (BÜYÜK & GÖLGELİ)
        SizedBox(
          width: double.infinity,
          height: 56,
          child: ElevatedButton(
            onPressed: onPressed,
            style: ElevatedButton.styleFrom(
              backgroundColor: btnColor,
              elevation: onPressed == null ? 0 : 8,
              shadowColor: btnColor.withOpacity(0.4),
              shape: RoundedRectangleBorder(
                borderRadius: BorderRadius.circular(16),
              ),
            ),
            child: Row(
              mainAxisAlignment: MainAxisAlignment.center,
              children: [
                Icon(
                  btnIcon,
                  color: isPurchased ? Colors.white : Colors.black87,
                ),
                const SizedBox(width: 10),
                Text(
                  btnText,
                  style: TextStyle(
                    fontSize: 16,
                    fontWeight: FontWeight.bold,
                    color: isPurchased ? Colors.white : Colors.black87,
                  ),
                ),
              ],
            ),
          ),
        ),
      ],
    );
  }

  // 5. MODERN REVIEW SECTION
  Widget _buildModernReviewCard(HomeController controller) {
    return Container(
      padding: const EdgeInsets.all(24),
      decoration: BoxDecoration(
        color: kSurfaceColor,
        borderRadius: BorderRadius.circular(24),
        boxShadow: [
          BoxShadow(
            color: Colors.black.withOpacity(0.05),
            blurRadius: 20,
            offset: const Offset(0, 5),
          ),
        ],
      ),
      child: Obx(() {
        final bool isEditing = controller.isEditingReview.value;
        final bool hasReview = controller.hasReviewToday.value;

        return Column(
          crossAxisAlignment: CrossAxisAlignment.start,
          children: [
            Row(
              children: [
                Container(
                  padding: const EdgeInsets.all(8),
                  decoration: BoxDecoration(
                    color: Colors.amber.shade50,
                    shape: BoxShape.circle,
                  ),
                  child: const Icon(
                    Icons.star_rounded,
                    color: Colors.amber,
                    size: 24,
                  ),
                ),
                const SizedBox(width: 12),
                Text(
                  hasReview && !isEditing ? "Değerlendirmen" : "Menüyü Puanla",
                  style: const TextStyle(
                    fontSize: 18,
                    fontWeight: FontWeight.bold,
                    color: Colors.black87,
                  ),
                ),
              ],
            ),
            const SizedBox(height: 20),

            // YILDIZLAR
            Center(
              child: Row(
                mainAxisSize: MainAxisSize.min,
                children: List.generate(5, (index) {
                  int score = index + 1;
                  double displayRating = (!isEditing && hasReview)
                      ? controller.lastReviewRating.value
                      : controller.currentRating.value;

                  return GestureDetector(
                    onTap: (!isEditing && hasReview)
                        ? null
                        : () {
                            controller.currentRating.value = score.toDouble();
                          },
                    child: Padding(
                      padding: const EdgeInsets.symmetric(horizontal: 4),
                      child: Icon(
                        displayRating >= score
                            ? Icons.star_rounded
                            : Icons.star_outline_rounded,
                        color: displayRating >= score
                            ? Colors.amber
                            : Colors.grey.shade300,
                        size: 40,
                      ),
                    ),
                  );
                }),
              ),
            ),

            const SizedBox(height: 20),

            // FORM ALANI VEYA YORUM GÖSTERİMİ
            if (hasReview && !isEditing)
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  color: Colors.grey.shade50,
                  borderRadius: BorderRadius.circular(12),
                  border: Border.all(color: Colors.grey.shade200),
                ),
                child: Column(
                  crossAxisAlignment: CrossAxisAlignment.start,
                  children: [
                    Text(
                      controller.lastReviewComment.value.isNotEmpty
                          ? controller.lastReviewComment.value
                          : "Yorum yazılmadı.",
                      style: TextStyle(
                        color: Colors.grey.shade800,
                        fontSize: 14,
                        height: 1.5,
                      ),
                    ),
                    const SizedBox(height: 10),
                    Align(
                      alignment: Alignment.centerRight,
                      child: InkWell(
                        onTap: controller.startEditReview,
                        child: Text(
                          "Düzenle",
                          style: TextStyle(
                            color: kPrimaryColor,
                            fontWeight: FontWeight.bold,
                            fontSize: 13,
                          ),
                        ),
                      ),
                    ),
                  ],
                ),
              )
            else
              Column(
                children: [
                  TextField(
                    controller: controller.commentController,
                    style: const TextStyle(fontSize: 14),
                    decoration: InputDecoration(
                      hintText: "Yemek nasıldı? Fikrini paylaş...",
                      hintStyle: TextStyle(color: Colors.grey.shade400),
                      filled: true,
                      fillColor: Colors.grey.shade50,
                      border: OutlineInputBorder(
                        borderRadius: BorderRadius.circular(12),
                        borderSide: BorderSide.none,
                      ),
                      contentPadding: const EdgeInsets.all(16),
                    ),
                    maxLines: 3,
                  ),
                  const SizedBox(height: 16),
                  Row(
                    children: [
                      if (hasReview)
                        Expanded(
                          child: TextButton(
                            onPressed: () {
                              controller.isEditingReview.value = false;
                              controller.commentController.clear();
                            },
                            child: const Text(
                              "Vazgeç",
                              style: TextStyle(color: Colors.grey),
                            ),
                          ),
                        ),
                      if (hasReview) const SizedBox(width: 10),
                      Expanded(
                        flex: 2,
                        child: SizedBox(
                          height: 48,
                          child: ElevatedButton(
                            onPressed: controller.isReviewSubmitting.value
                                ? null
                                : () => controller.submitReview(),
                            style: ElevatedButton.styleFrom(
                              backgroundColor: kPrimaryColor,
                              foregroundColor: Colors.white,
                              shape: RoundedRectangleBorder(
                                borderRadius: BorderRadius.circular(12),
                              ),
                            ),
                            child: controller.isReviewSubmitting.value
                                ? const SizedBox(
                                    width: 20,
                                    height: 20,
                                    child: CircularProgressIndicator(
                                      color: Colors.white,
                                      strokeWidth: 2,
                                    ),
                                  )
                                : const Text("Gönder"),
                          ),
                        ),
                      ),
                    ],
                  ),
                ],
              ),
          ],
        );
      }),
    );
  }
}
