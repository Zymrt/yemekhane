<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-400 via-emerald-500 to-orange-400 p-6">
    
    <!-- Cam Efektli Login Kutusu -->
    <div class="backdrop-blur-xl bg-white/30 p-8 rounded-2xl shadow-2xl w-full max-w-md border border-white/20">
      
      <!-- Logo ve Başlık -->
      <div class="flex flex-col items-center mb-8">
        <div class="w-20 h-20 bg-white rounded-full shadow-md flex items-center justify-center overflow-hidden">
          <img 
            src="assets/logo.jpg" 
            alt="Logo" 
            class="object-contain w-16 h-16 transition-transform duration-500 hover:scale-110"
          />
        </div>
        <h2 class="text-3xl font-bold text-white mt-5 drop-shadow-md">Kullanıcı Girişi</h2>
      </div>
      
      <!-- Form -->
      <form @submit.prevent="handleLogin" class="space-y-5">
        
        <div>
          <label for="phone" class="block text-sm font-semibold text-white mb-1">Telefon Numarası</label>
          <input 
            type="tel" 
            id="phone" 
            v-model="phone" 
            required
            class="w-full px-4 py-2 rounded-lg border-none focus:ring-2 focus:ring-yellow-400 outline-none shadow-sm"
            placeholder="5XX XXX XX XX"
          >
        </div>
        
        <div>
          <label for="password" class="block text-sm font-semibold text-white mb-1">Şifre</label>
          <input 
            type="password" 
            id="password" 
            v-model="password" 
            required
            class="w-full px-4 py-2 rounded-lg border-none focus:ring-2 focus:ring-yellow-400 outline-none shadow-sm"
            placeholder="••••••••"
          >
        </div>
        
        <p v-if="error" class="text-red-200 text-sm text-center mt-2">⚠️ {{ error }}</p>
        
        <button 
          type="submit" 
          :disabled="loading"
          class="w-full py-3 rounded-xl bg-gradient-to-r from-orange-500 via-orange-500 to-orange-600 text-white font-semibold shadow-md hover:scale-105 transition-all duration-300 disabled:opacity-60"
        >
          {{ loading ? 'Giriş Yapılıyor...' : 'Giriş Yap' }}
        </button>
      </form>
      
      <p class="mt-6 text-center text-sm text-white/90">
        Hesabın yok mu?
        <NuxtLink to="/register" class="font-semibold text-orange-200 hover:text-orange-100 transition">
          Kayıt Ol
        </NuxtLink>
      </p>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue';
import useAuth from '../composables/useAuth';
import useAuthGuard from '../composables/useAuthGuard';

// Sayfa koruması: Giriş yapmış kullanıcı bu sayfayı göremez.
const { protectGuestPage } = useAuthGuard();
protectGuestPage();

// Gerekli state ve fonksiyonları yeni useAuth'dan alıyoruz
const { login } = useAuth();
// useRouter'a artık ihtiyacımız yok, sildik!

const phone = ref('');
const password = ref('');
const loading = ref(false);
const error = ref(null);

const LOGIN_API_URL = 'http://127.0.0.1:8000/api/login';

const handleLogin = async () => {
  loading.value = true;
  error.value = null;

  try {
    const response = await $fetch(LOGIN_API_URL, {
      method: 'POST',
      body: { phone: phone.value, password: password.value }
    });

    // Merkezi state'imizi güncelliyoruz
    login({ user: response.user, token: response.token });
    
    // AKILLI YÖNLENDİRME (navigateTo ile güncellendi)
    if (response.user.role === 'admin') {
      // router.push yerine navigateTo kullanıyoruz
      await navigateTo('/admin');
    } else {
      // router.push yerine navigateTo kullanıyoruz
      await navigateTo('/menu');
    }

  } catch (err) {
    error.value = err.data?.errors?.phone?.[0] || 'Giriş sırasında bir hata oluştu.';
    console.error('Giriş Hatası:', err);
  } finally {
    loading.value = false;
  }
};
</script> 