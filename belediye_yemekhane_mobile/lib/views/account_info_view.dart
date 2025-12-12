// lib/views/account_info_view.dart

import 'package:flutter/material.dart';
import 'package:get/get.dart';

import '../controllers/profile_controller.dart';

class AccountInfoView extends StatelessWidget {
  AccountInfoView({super.key});

  final ProfileController controller = Get.put(ProfileController());

  Color _statusColor(String status) {
    switch (status.toLowerCase()) {
      case 'approved':
      case 'onaylandı':
        return Colors.green.shade600;
      case 'pending':
      case 'beklemede':
        return Colors.orange.shade600;
      case 'rejected':
      case 'reddedildi':
        return Colors.red.shade600;
      default:
        return Colors.blueGrey.shade600;
    }
  }

  String _statusText(String status) {
    switch (status.toLowerCase()) {
      case 'approved':
        return 'Onaylandi';
      case 'pending':
        return 'Onay Bekliyor';
      case 'rejected':
        return 'Reddedildi';
      default:
        return status.isEmpty ? 'Bilinmiyor' : status;
    }
  }

  @override
  Widget build(BuildContext context) {
    return Scaffold(
      backgroundColor: const Color(0xFFF0FDF4),
      appBar: AppBar(
        title: const Text('Hesap Bilgileri'),
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
      ),
      body: Obx(() {
        if (controller.isLoading.value) {
          return const Center(child: CircularProgressIndicator());
        }

        if (controller.errorMessage.value.isNotEmpty) {
          return Center(
            child: Padding(
              padding: const EdgeInsets.all(24.0),
              child: Column(
                mainAxisSize: MainAxisSize.min,
                children: [
                  const Icon(Icons.error_outline, size: 48, color: Colors.red),
                  const SizedBox(height: 12),
                  Text(
                    controller.errorMessage.value,
                    textAlign: TextAlign.center,
                    style: const TextStyle(fontSize: 16),
                  ),
                  const SizedBox(height: 16),
                  ElevatedButton.icon(
                    onPressed: controller.fetchProfile,
                    icon: const Icon(Icons.refresh),
                    label: const Text('Tekrar Dene'),
                  ),
                ],
              ),
            ),
          );
        }

        final profile = controller.profile.value;
        if (profile == null) {
          return const Center(child: Text('Profil bilgisi bulunamadı.'));
        }

        return SingleChildScrollView(
          padding: const EdgeInsets.all(16.0),
          child: Column(
            children: [
              // Üst kart: isim + durum
              Container(
                width: double.infinity,
                padding: const EdgeInsets.all(16),
                decoration: BoxDecoration(
                  gradient: const LinearGradient(
                    colors: [Color(0xFF22C55E), Color(0xFF16A34A)],
                    begin: Alignment.topLeft,
                    end: Alignment.bottomRight,
                  ),
                  borderRadius: BorderRadius.circular(16),
                  boxShadow: [
                    BoxShadow(
                      color: Colors.green.withOpacity(0.3),
                      blurRadius: 12,
                      offset: const Offset(0, 6),
                    ),
                  ],
                ),
                child: Row(
                  children: [
                    const CircleAvatar(
                      radius: 26,
                      child: Icon(Icons.person, size: 28),
                    ),
                    const SizedBox(width: 16),
                    Expanded(
                      child: Column(
                        crossAxisAlignment: CrossAxisAlignment.start,
                        children: [
                          Text(
                            '${profile.name} ${profile.surname}',
                            style: const TextStyle(
                              fontSize: 18,
                              fontWeight: FontWeight.bold,
                              color: Colors.white,
                            ),
                          ),
                          const SizedBox(height: 4),
                          Text(
                            profile.unit,
                            style: TextStyle(
                              fontSize: 14,
                              color: Colors.white.withOpacity(0.9),
                            ),
                          ),
                        ],
                      ),
                    ),
                    Container(
                      padding: const EdgeInsets.symmetric(
                        horizontal: 10,
                        vertical: 6,
                      ),
                      decoration: BoxDecoration(
                        color: Colors.white,
                        borderRadius: BorderRadius.circular(999),
                      ),
                      child: Row(
                        children: [
                          Icon(
                            Icons.circle,
                            size: 8,
                            color: _statusColor(profile.status),
                          ),
                          const SizedBox(width: 6),
                          Text(
                            _statusText(profile.status),
                            style: TextStyle(
                              fontSize: 12,
                              fontWeight: FontWeight.w600,
                              color: _statusColor(profile.status),
                            ),
                          ),
                        ],
                      ),
                    ),
                  ],
                ),
              ),

              const SizedBox(height: 20),

              // Bilgiler kartı
              Card(
                elevation: 2,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(16),
                ),
                child: Padding(
                  padding: const EdgeInsets.symmetric(
                    horizontal: 16,
                    vertical: 12,
                  ),
                  child: Column(
                    children: [
                      _infoRow(
                        icon: Icons.badge_outlined,
                        label: 'Ad Soyad',
                        value: '${profile.name} ${profile.surname}',
                      ),
                      const Divider(height: 16),
                      _infoRow(
                        icon: Icons.email_outlined,
                        label: 'E-posta',
                        value: profile.email,
                      ),
                      const Divider(height: 16),
                      _infoRow(
                        icon: Icons.phone_outlined,
                        label: 'Telefon',
                        value: profile.phone,
                      ),
                      const Divider(height: 16),
                      _infoRow(
                        icon: Icons.apartment_outlined,
                        label: 'Bağlı Birim',
                        value: profile.unit,
                      ),
                    ],
                  ),
                ),
              ),

              const SizedBox(height: 20),

              // Belge bilgisi
              Card(
                elevation: 1,
                shape: RoundedRectangleBorder(
                  borderRadius: BorderRadius.circular(16),
                ),
                child: ListTile(
                  leading: const Icon(Icons.description_outlined),
                  title: const Text('Yüklenen Belge'),
                  subtitle: Text(
                    profile.documentPath != null &&
                            profile.documentPath!.isNotEmpty
                        ? profile.documentPath!
                        : 'Herhangi bir belge bulunamadı',
                  ),
                ),
              ),
            ],
          ),
        );
      }),
    );
  }

  Widget _infoRow({
    required IconData icon,
    required String label,
    required String value,
  }) {
    return Row(
      children: [
        Icon(icon, size: 22, color: Colors.teal),
        const SizedBox(width: 12),
        Expanded(
          child: Column(
            crossAxisAlignment: CrossAxisAlignment.start,
            children: [
              Text(
                label,
                style: TextStyle(fontSize: 12, color: Colors.grey.shade600),
              ),
              const SizedBox(height: 2),
              Text(
                value.isEmpty ? '-' : value,
                style: const TextStyle(
                  fontSize: 15,
                  fontWeight: FontWeight.w500,
                ),
              ),
            ],
          ),
        ),
      ],
    );
  }
}
