<template>
  <div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-4xl mx-auto">
      
      <header class="mb-8">
        <h1 class="text-3xl font-extrabold text-purple-700">Yeni Menü Ekle (Yönetici)</h1>
        <NuxtLink to="/menu" class="text-sm text-blue-600 hover:underline mt-2 inline-block">
          &larr; Günlük Menüye Geri Dön
        </NuxtLink>
      </header>

      <div v-if="!isAuthenticated" class="bg-red-100 border border-red-400 text-red-700 p-4 rounded mb-6">
        <p class="font-bold">Erişim Engellendi!</p>
        <p>Giriş yapınız.</p>
      </div>
      <div v-else-if="!isAdmin" class="bg-yellow-100 border border-yellow-400 text-yellow-700 p-4 rounded mb-6">
        <p class="font-bold">Yetki Reddedildi!</p>
        <p>Sadece yöneticiler bu sayfaya menü ekleyebilir.</p>
      </div>

      <form v-else @submit.prevent="submitMenu" class="bg-white shadow-xl rounded-lg p-8">
        
        <div class="mb-6">
          <label for="menuDate" class="block text-lg font-medium text-gray-700 mb-2">Menü Tarihi:</label>
          <input 
            type="date" 
            id="menuDate" 
            v-model="menuDate" 
            required
            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-purple-500 focus:border-purple-500"
          >
        </div>

        <h3 class="text-xl font-semibold text-gray-700 mb-4 border-b pb-2">Menü Öğeleri</h3>
        
        <div v-for="(item, index) in menuItems" :key="index" class="p-4 border rounded-lg mb-4 bg-gray-50">
          <div class="flex justify-end">
            <button 
              type="button" 
              @click="removeItem(index)" 
              class="text-red-500 hover:text-red-700 text-sm font-medium"
              v-if="menuItems.length > 1"
            >
              Kaldır
            </button>
          </div>
          
          <div class="mb-3">
            <label :for="'name-' + index" class="block text-sm font-medium text-gray-700">Yemek Adı (Zorunlu):</label>
            <input 
              type="text" 
              :id="'name-' + index" 
              v-model="item.name" 
              required
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg"
              placeholder="Örn: Mercimek Çorbası"
            >
          </div>
          
          <div class="mb-3">
            <label :for="'desc-' + index" class="block text-sm font-medium text-gray-700">Açıklama (Opsiyonel):</label>
            <input 
              type="text" 
              :id="'desc-' + index" 
              v-model="item.description" 
              class="mt-1 block w-full px-3 py-2 border border-gray-300 rounded-lg"
              placeholder="Örn: (Terbiyeli, etli)"
            >
          </div>
        </div>
        
        <button 
          type="button" 
          @click="addItem" 
          class="w-full py-2 border border-gray-300 rounded-lg text-gray-600 hover:bg-gray-100 transition duration-150 mb-6"
        >
          + Yeni Yemek Öğesi Ekle
        </button>

        <p v-if="error" class="text-red-600 text-sm mb-4 bg-red-100 p-3 rounded border border-red-400">
          Hata: {{ error }}
        </p>
        <p v-if="successMessage" class="text-green-600 text-sm mb-4 bg-green-100 p-3 rounded border border-green-400">
          {{ successMessage }}
        </p>
        
        <button 
          type="submit" 
          :disabled="loading"
          class="w-full flex justify-center py-3 px-4 rounded-lg shadow-md text-white bg-purple-600 hover:bg-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-purple-500 disabled:opacity-50 font-bold"
        >
          {{ loading ? 'Kaydediliyor...' : 'Menüyü Kaydet' }}
        </button>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import useAuth from '../composables/useAuth'; 

// --- STATE VE COMPOSABLES ---
const { isAdmin, isAuthenticated, logout } = useAuth(); // Admin rolünü ve oturum durumunu al
const loading = ref(false);
const error = ref(null);
const successMessage = ref(null);

// Menü verileri
const menuDate = ref(new Date().toISOString().substring(0, 10)); // Varsayılan olarak bugünün tarihi (YYYY-MM-DD)
const menuItems = ref([
    { name: '', description: '' }, // Başlangıçta boş bir yemek öğesi
]);

// Backend API Endpoint'i
const ADD_MENU_API_URL = 'http://127.0.0.1:8000/api/admin/menu/add';
const API_BASE_URL = 'http://127.0.0.1:8000'; 


// --- DİNAMİK FORM İŞLEMLERİ ---
const addItem = () => {
  menuItems.value.push({ name: '', description: '' });
};

const removeItem = (index) => {
  if (menuItems.value.length > 1) {
    menuItems.value.splice(index, 1);
  }
};


// --- API İSTEĞİ VE LOGIC ---
const submitMenu = async () => {
  loading.value = true;
  error.value = null;
  successMessage.value = null;

  // Boş öğeleri filtreleme (Zorunlu adı olanları koruyalım)
  const validItems = menuItems.value.filter(item => item.name.trim() !== '');

  if (validItems.length === 0) {
    error.value = 'Lütfen menüye en az bir yemek öğesi ekleyin.';
    loading.value = false;
    return;
  }
  
  const token = process.client ? localStorage.getItem('authToken') : null;

  if (!token) {
    await logout();
    return;
  }

  // Backend'e göndereceğimiz veri formatı
  const payload = {
    menu_date: menuDate.value,
    items: validItems,
  };

  try {
    const response = await $fetch(ADD_MENU_API_URL, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token}`,
        'Content-Type': 'application/json'
      },
      body: payload,
      baseURL: API_BASE_URL,
    });

    successMessage.value = response.message || 'Menü başarıyla kaydedildi.';
    // Formu sıfırlama
    menuItems.value = [{ name: '', description: '' }];

  } catch (err) {
    console.error('Menü Ekleme Hatası:', err);
    
    if (err.statusCode === 403) {
      error.value = 'Yetkisiz erişim! Yönetici olmanız gerekiyor.';
    } else if (err.statusCode === 401) {
      error.value = 'Oturum süresi doldu.';
      await logout();
    } else if (err.data?.message) {
      error.value = err.data.message; // Laravel doğrulama hataları
    } else {
      error.value = 'Menü kaydedilirken beklenmedik bir hata oluştu.';
    }

  } finally {
    loading.value = false;
    // Bildirim mesajını birkaç saniye sonra temizle
    setTimeout(() => successMessage.value = null, 5000);
  }
};
</script>