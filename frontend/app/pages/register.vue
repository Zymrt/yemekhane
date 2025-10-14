<template>
  <div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-blue-100 via-green-50 to-orange-100 p-6">
    <div class="bg-white p-10 rounded-2xl shadow-2xl w-full max-w-xl border border-gray-200">
      
      <!-- Logo ve Başlık -->
      <div class="flex flex-col items-center mb-8">
        <img 
          src="assets/logo.jpg" 
          alt="Mezitli Belediyesi" 
          class="w-20 h-20 object-contain mb-4"
        />
        <h2 class="text-3xl font-bold text-gray-800 text-center">Yeni Hesap Oluştur</h2>
        <p class="text-gray-500 text-sm mt-1">Mezitli Belediyesi yemekhane sistemine kayıt ol</p>
      </div>

      <!-- Form -->
      <form @submit.prevent="handleRegister" class="space-y-5">
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label for="name" class="block text-sm font-semibold text-gray-700 mb-1">Ad</label>
            <input 
              type="text" 
              id="name" 
              v-model="name" 
              required 
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 outline-none"
              placeholder="Adınız"
            >
          </div>
          <div>
            <label for="surname" class="block text-sm font-semibold text-gray-700 mb-1">Soyad</label>
            <input 
              type="text" 
              id="surname" 
              v-model="surname" 
              required 
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-orange-400 outline-none"
              placeholder="Soyadınız"
            >
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label for="phone" class="block text-sm font-semibold text-gray-700 mb-1">Telefon Numarası</label>
            <input 
              type="tel" 
              id="phone" 
              v-model="phone" 
              required 
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 outline-none"
              placeholder="5XX XXX XX XX"
            >
          </div>
          <div>
            <label for="unit" class="block text-sm font-semibold text-gray-700 mb-1">Bağlı Olduğu Birim</label>
            <input 
              type="text" 
              id="unit" 
              v-model="unit" 
              required 
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-green-400 outline-none"
              placeholder="Müdürlük adı"
            >
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div>
            <label for="password" class="block text-sm font-semibold text-gray-700 mb-1">Şifre</label>
            <input 
              type="password" 
              id="password" 
              v-model="password" 
              required 
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 outline-none"
              placeholder="••••••••"
            >
          </div>
          <div>
            <label for="password_confirmation" class="block text-sm font-semibold text-gray-700 mb-1">Şifre Tekrarı</label>
            <input 
              type="password" 
              id="password_confirmation" 
              v-model="password_confirmation" 
              required 
              class="w-full px-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-400 outline-none"
              placeholder="••••••••"
            >
          </div>
        </div>

        <div>
          <label for="proof_document" class="block text-sm font-semibold text-gray-700 mb-1">
            Kurum Kimlik / Belgesi (PDF, JPG/PNG, maks. 2MB)
          </label>
          <input 
            type="file" 
            id="proof_document" 
            @change="handleFileUpload" 
            required 
            class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none"
            accept=".pdf,.jpg,.jpeg,.png"
          >
        </div>

        <p v-if="error" class="text-red-600 text-sm text-center">Hata: {{ error }}</p>

        <!-- Kayıt Butonu -->
        <button 
          type="submit" 
          :disabled="loading"
          class="w-full py-3 rounded-xl bg-gradient-to-r from-orange-500 via-blue-500 to-green-500 text-white font-semibold shadow-md hover:scale-105 transition-all duration-300 disabled:opacity-60"
        >
          {{ loading ? 'Kayıt Olunuyor...' : 'Kayıt Ol' }}
        </button>
      </form>

      <!-- Alt Kısım -->
      <p class="mt-8 text-center text-sm text-gray-600">
        Zaten hesabın var mı?
        <NuxtLink to="/login" class="font-semibold text-blue-600 hover:text-blue-500 transition">
          Giriş Yap
        </NuxtLink>
      </p>
    </div>
  </div>
</template>


<script setup>
import { ref } from 'vue';

const name = ref('');
const surname = ref('');
const phone = ref('');
const unit = ref('');
const password = ref('');
const password_confirmation = ref(''); // YENİ: Backend'in 'confirmed' kuralı için
const proof_document = ref(null);      // YENİ: Dosya objesini tutmak için

const loading = ref(false);
const error = ref(null);

const inputClass = "mt-1 block w-full px-4 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-green-500 focus:border-green-500";

// API ENDPOINT'İNİZ
const REGISTER_API_URL = 'http://127.0.0.1:8000/api/register'; 
// NOT: Eğer CORS hatası devam ederse, bu URL'yi 'http://127.0.0.1:8000/api/register' yapmayı deneyin.

const handleFileUpload = (event) => {
  // Yüklenen dosyayı al
  proof_document.value = event.target.files[0];
};

const handleRegister = async () => {
  loading.value = true;
  error.value = null;

  // Şifre kontrolü (Backend yapsa da frontend'de kontrol etmek UX için iyidir)
  if (password.value !== password_confirmation.value) {
      error.value = 'Şifreler eşleşmiyor.';
      loading.value = false;
      return;
  }
  if (!proof_document.value) {
      error.value = 'Lütfen kimlik belgenizi yükleyin.';
      loading.value = false;
      return;
  }

  // FormData oluşturma: Dosya yüklemek için zorunludur.
  const formData = new FormData();
  formData.append('name', name.value);
  formData.append('surname', surname.value);
  formData.append('phone', phone.value);
  formData.append('unit', unit.value);
  formData.append('password', password.value);
  formData.append('password_confirmation', password_confirmation.value); // Backend'in beklediği isim
  formData.append('proof_document', proof_document.value); // Dosya objesi

  try {
    // $fetch kullanılırken, FormData gönderildiğinde 'Content-Type' otomatik olarak 
    // tarayıcı tarafından ayarlanır (multipart/form-data). Headers'ı manuel ayarlamayın.
    await $fetch(REGISTER_API_URL, {
      method: 'POST',
      body: formData, // JSON yerine FormData gönderiliyor
    });

    alert('Kayıt işleminiz başarıyla alındı! Yöneticiniz onayladıktan sonra giriş yapabilirsiniz.');
    await navigateTo('/login');

  } catch (err) {
    // API'den gelen hata mesajlarını veya Fetch hatasını göster
    // Laravel/PHP hataları genellikle err.data?.errors içinde detaylı gelir
    const apiErrors = err.data?.errors;
    if (apiErrors) {
        // Telefon numarasının unique olması gibi hataları listeleme
        error.value = Object.values(apiErrors).flat().join(' | ');
    } else {
        error.value = err.data?.message || 'Kayıt sırasında bir hata oluştu.';
    }
    
    console.error('Kayıt Hatası:', err);
  } finally {
    loading.value = false;
  }
};
</script>