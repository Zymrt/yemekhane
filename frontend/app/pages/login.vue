<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100 p-4">
    <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-md">
      
      <h2 class="text-3xl font-bold text-center text-gray-800 mb-6">Kullanıcı Girişi</h2>
      
      <form @submit.prevent="handleLogin">
        
        <div class="mb-4">
          <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Telefon Numarası:</label>
          <input 
            type="tel" 
            id="phone" 
            v-model="phone" 
            required
            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
            placeholder="5XX XXX XX XX"
          >
        </div>
        
        <div class="mb-6">
          <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Şifre:</label>
          <input 
            type="password" 
            id="password" 
            v-model="password" 
            required
            class="mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500"
          >
        </div>
        
        <p v-if="error" class="text-red-600 text-sm mb-4">Hata: {{ error }}</p>
        
        <button 
          type="submit" 
          :disabled="loading"
          class="w-full flex justify-center py-2 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 disabled:opacity-50"
        >
          {{ loading ? 'Giriş Yapılıyor...' : 'Giriş Yap' }}
        </button>
      </form>
      
      <p class="mt-6 text-center text-sm text-gray-600">
        Hesabın yok mu? 
        <NuxtLink to="/register" class="font-medium text-blue-600 hover:text-blue-500">
          Kayıt Ol
        </NuxtLink>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
// import useAuth from '../composables/useAuth'; // CORS sorunu çözülene kadar bu import'u devre dışı bırakıyoruz.

const phone = ref(''); // E-posta yerine telefon numarası
const password = ref('');
const loading = ref(false);
const error = ref(null);

// const { isLoggedIn } = useAuth(); 

// *** API ENDPOINT'İ (Register rotanız /register olduğu için, Login rotasının da /login olduğunu varsayıyoruz) ***
const LOGIN_API_URL = 'http://127.0.0.1:8000/api/login'; 

const handleLogin = async () => {
  loading.value = true;
  error.value = null;

  try {
    const response = await $fetch(LOGIN_API_URL, {
      method: 'POST',
      body: { 
        phone: phone.value, // Backend'in beklediği alan adı
        password: password.value 
      }
    });

    // 1. Backend'den dönen 'token'ı kaydet.
    localStorage.setItem('authToken', response.token); 
    
    // 2. Kullanıcıyı ana sayfaya yönlendir.
    await navigateTo('/'); 

  } catch (err) {
    // Backend'den gelen ValidationException hatalarını yakala
    const apiErrors = err.data?.errors;
    if (apiErrors) {
        // Hata mesajını göster ('Telefon veya şifre hatalı' veya 'Hesabınız henüz onaylanmamış')
        // Laravel'de genellikle ilk hatayı alırız.
        error.value = Object.values(apiErrors).flat()[0]; 
    } else {
        // Diğer genel hataları veya Fetch hatasını göster
        error.value = 'Giriş sırasında bir hata oluştu.';
    }
    
    console.error('Giriş Hatası:', err);
    
    // Hata durumunda token'ı temizle (Gereksiz token kalmasını önler)
    if (process.client) {
      localStorage.removeItem('authToken');
    }
    
  } finally {
    loading.value = false;
  }
};
</script>