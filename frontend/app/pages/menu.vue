<template>
  <div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-4xl mx-auto">
      
      <header class="py-4 border-b border-gray-200 mb-8">
        <div class="flex justify-between items-center">
          <h1 class="text-3xl font-extrabold text-blue-800">Günlük Menü</h1>
          
          <div class="flex items-center space-x-4">
            <NuxtLink 
              v-if="isAdmin" 
              to="/admin/add-menu"
              class="bg-purple-600 hover:bg-purple-700 text-white font-semibold py-2 px-4 rounded-lg shadow transition duration-200 whitespace-nowrap"
            >
              Menü Ekle (Admin)
            </NuxtLink>

            <button 
              @click="handleLogout"
              class="bg-red-500 hover:bg-red-600 text-white font-semibold py-2 px-4 rounded-lg shadow transition duration-200"
            >
              Çıkış Yap
            </button>
          </div>
        </div>

        <div v-if="user" class="mt-4 p-3 bg-white border border-gray-200 rounded-lg shadow-sm flex justify-between items-center text-sm">
          <p class="font-semibold text-gray-700">Merhaba, {{ user.name }} {{ user.surname }} ({{ user.unit }})</p>
          <div class="font-bold text-lg text-green-600">
            Bakiye: {{ user.balance?.toFixed(2) || 0.00 }} ₺ 
          </div>
        </div>
      </header>

      <div v-if="loading" class="text-center py-12 text-gray-500">
        <p class="text-lg">Menü yükleniyor...</p>
      </div>

      <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <strong class="font-bold">Hata!</strong>
        <span class="block sm:inline"> {{ error }}</span>
      </div>

      <div v-else>
        <h2 class="text-2xl font-bold mb-4 text-gray-700">Menü: {{ menu?.date || 'Bugün' }}</h2>
        
        <div class="bg-white shadow-lg rounded-lg p-6">
          <ul class="space-y-4">
            <li 
              v-for="(item, index) in menu.items" 
              :key="index"
              class="border-b pb-3 last:border-b-0"
            >
              <p class="text-xl font-medium text-gray-800">{{ item.name }}</p>
              <p v-if="item.description" class="text-sm text-gray-500">{{ item.description }}</p>
            </li>
          </ul>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import useAuth from '../composables/useAuth'; 

protectUserPage();

// --- Durum Yönetimi ---
const loading = ref(true);
const error = ref(null);
const menu = ref(null);

// ----------------------------------------------------
// ✏️ DEĞİŞİKLİK 1: 'token' kaldırıldı.
// ----------------------------------------------------
// Gerekli state ve fonksiyonları useAuth'dan alıyoruz
// ESKİ HALİ: const { user, logout, token } = useAuth();
const { user, logout } = useAuth(); // YENİ HALİ

// --- Veri Çekme Fonksiyonu ---
const fetchMenu = async () => {
  loading.value = true;
  error.value = null;

  // ----------------------------------------------------
  // ✏️ DEĞİŞİKLİK 2: Token kontrolü kaldırıldı.
  // 'protectUserPage()' bu işi zaten yaptı.
  // ----------------------------------------------------
  // if (!token.value) { ... } BLOKU SİLİNDİ

  try {
    // ----------------------------------------------------
    // ✏️ DEĞİŞİKLİK 3: API isteği proxy uyumlu hale getirildi.
    // ----------------------------------------------------
    const response = await $fetch('/api/menu/today', {
      // ESKİ URL: 'http://127.0.0.1:8000/api/menu/today'
      // ESKİ HEADERS: { 'Authorization': `Bearer ${token.value}` }
      
      // 'headers' bloğu tamamen kaldırıldı.
      // Proxy, cookie'yi otomatik olarak iletecek.
    });
    menu.value = response;
  } catch (err) {
    console.error('Menü Yükleme Hatası:', err);
    error.value = 'Menü yüklenirken bir hata oluştu veya yetkiniz yok.';
    if (err.statusCode === 401) {
      await logout();
    }
  } finally {
    loading.value = false;
  }
};

// --- Çıkış Fonksiyonu ---
const handleLogout = async () => {
  await logout(); // useAuth'daki global logout fonksiyonunu çağırır
};

// --- Sayfa Yüklendiğinde Menüyü Çekme ---
onMounted(() => {
  fetchMenu();
});
</script>