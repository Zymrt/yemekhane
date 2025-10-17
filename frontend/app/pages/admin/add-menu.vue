<template>
  <div class="space-y-8">
    
    <header>
      <h1 class="text-3xl font-bold text-gray-800">Menü Yönetimi</h1>
      <NuxtLink to="/admin" class="text-sm text-indigo-600 hover:underline mt-1 inline-block">
        &larr; Admin Paneline Geri Dön
      </NuxtLink>
    </header>

    <form @submit.prevent="submitMenu" class="bg-white shadow-xl rounded-2xl p-8">
      
      <div class="mb-6">
        <label for="menuDate" class="block text-lg font-medium text-gray-700 mb-2">Menü Tarihi:</label>
        <input 
          type="date" 
          id="menuDate" 
          v-model="menuDate" 
          required
          class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-indigo-500 focus:border-indigo-500"
        >
      </div>

      <h3 class="text-xl font-semibold text-gray-700 mb-4 border-b pb-2">Menü Öğeleri</h3>
      
      <div v-for="(item, index) in menuItems" :key="index" class="p-4 border rounded-lg mb-4 bg-gray-50 relative">
        <button 
          type="button" 
          @click="removeItem(index)" 
          class="absolute top-2 right-2 text-red-500 hover:text-red-700 text-sm font-medium"
          v-if="menuItems.length > 1"
        >
          Kaldır
        </button>
        
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
        
        <div>
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
        class="w-full py-2 border-2 border-dashed border-gray-300 rounded-lg text-gray-600 hover:bg-gray-100 transition duration-150 mb-6"
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
        class="w-full flex justify-center py-3 px-4 rounded-lg shadow-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 font-bold"
      >
        {{ loading ? 'Kaydediliyor...' : 'Menüyü Kaydet' }}
      </button>
    </form>
  </div>
</template>

<script setup>
// Script içeriğin zaten neredeyse mükemmeldi, sadece temizledik ve guard'ı ekledik.
import { ref } from 'vue';
import useAuth from '../composables/useAuth';
import useAuthGuard from '../composables/useAuthGuard';

definePageMeta({
  layout: 'admin'
});
const { protectAdminPage } = useAuthGuard();
protectAdminPage();

const { logout, token } = useAuth();
const loading = ref(false);
const error = ref(null);
const successMessage = ref(null);
const menuDate = ref(new Date().toISOString().substring(0, 10)); 
const menuItems = ref([
    { name: '', description: '' },
]);

const ADD_MENU_API_URL = 'http://127.0.0.1:8000/api/admin/menu/add'; // API URL'sini basitleştirdik

const addItem = () => menuItems.value.push({ name: '', description: '' });
const removeItem = (index) => {
  if (menuItems.value.length > 1) menuItems.value.splice(index, 1);
};

const submitMenu = async () => {
  loading.value = true;
  error.value = null;
  successMessage.value = null;

  const validItems = menuItems.value.filter(item => item.name.trim() !== '');

  if (validItems.length === 0) {
    error.value = 'Lütfen menüye en az bir yemek öğesi ekleyin.';
    loading.value = false;
    return;
  }
  
  if (!token.value) {
    await logout();
    return;
  }

  const payload = {
    date: menuDate.value,
    items: validItems,
  };

  try {
    const response = await $fetch(ADD_MENU_API_URL, {
      method: 'POST',
      headers: {
        'Authorization': `Bearer ${token.value}`,
        'Content-Type': 'application/json'
      },
      body: payload,
    });

    successMessage.value = response.message || 'Menü başarıyla kaydedildi.';
    menuItems.value = [{ name: '', description: '' }];

  } catch (err) {
    console.error('Menü Ekleme Hatası:', err);
    if (err.statusCode === 401) {
      error.value = 'Oturum süresi doldu.';
      await logout();
    } else if (err.data?.message) {
      error.value = err.data.message;
    } else {
      error.value = 'Menü kaydedilirken beklenmedik bir hata oluştu.';
    }
  } finally {
    loading.value = false;
    setTimeout(() => {
      successMessage.value = null;
      error.value = null;
    }, 5000);
  }
};
</script>