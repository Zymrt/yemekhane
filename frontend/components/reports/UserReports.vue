<template>
  <div class="p-10 text-white text-center">
    <h1 class="text-3xl font-bold mb-4">📊 Yönetim Raporları</h1>
    <p class="text-gray-400 mb-8">Bu sayfada sistem verilerini, kullanıcı istatistiklerini ve menü aktivitelerini görüntüleyebilirsin.</p>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
      <div class="bg-white/10 backdrop-blur-md rounded-2xl p-6 border border-white/10">
        <h2 class="text-lg font-semibold text-orange-300">👤 Toplam Kullanıcı</h2>
        <p class="text-4xl font-bold mt-2">128</p>
        <p class="text-sm text-gray-400 mt-1">+3 yeni kayıt bugün</p>
      </div>

      <div class="bg-white/10 backdrop-blur-md rounded-2xl p-6 border border-white/10">
        <h2 class="text-lg font-semibold text-green-300">🍽️ Toplam Menü</h2>
        <p class="text-4xl font-bold mt-2">42</p>
        <p class="text-sm text-gray-400 mt-1">Bu hafta 5 yeni menü eklendi</p>
      </div>

      <div class="bg-white/10 backdrop-blur-md rounded-2xl p-6 border border-white/10">
        <h2 class="text-lg font-semibold text-cyan-300">📅 Son Güncelleme</h2>
        <p class="text-xl font-medium mt-2">21 Ekim 2025</p>
        <p class="text-sm text-gray-400 mt-1">Son düzenleyen: Admin</p>
      </div>
    </div>
  </div>
</template>

<script setup>
  // İleride buraya API'den veriler gelecek
</script>
