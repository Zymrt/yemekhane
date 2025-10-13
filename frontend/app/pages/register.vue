<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-100 p-4">
    <div class="bg-white p-8 rounded-lg shadow-xl w-full max-w-xl"> <h2 class="text-3xl font-bold text-center text-gray-800 mb-6">Yeni Hesap Oluştur</h2>
      
      <form @submit.prevent="handleRegister">
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="mb-4">
            <label for="name" class="block text-sm font-medium text-gray-700 mb-1">Ad:</label>
            <input type="text" id="name" v-model="name" required :class="inputClass" placeholder="Adınız">
          </div>
          <div class="mb-4">
            <label for="surname" class="block text-sm font-medium text-gray-700 mb-1">Soyad:</label>
            <input type="text" id="surname" v-model="surname" required :class="inputClass" placeholder="Soyadınız">
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="mb-4">
            <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">Telefon Numarası:</label>
            <input type="tel" id="phone" v-model="phone" required :class="inputClass" placeholder="5XX XXX XX XX">
          </div>
          <div class="mb-4">
            <label for="unit" class="block text-sm font-medium text-gray-700 mb-1">Bağlı Olduğu Birim:</label>
            <input type="text" id="unit" v-model="unit" required :class="inputClass" placeholder="Müdürlük adı">
          </div>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <div class="mb-4">
            <label for="password" class="block text-sm font-medium text-gray-700 mb-1">Şifre:</label>
            <input type="password" id="password" v-model="password" required :class="inputClass">
          </div>
          <div class="mb-6">
            <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">Şifre Tekrarı:</label>
            <input type="password" id="password_confirmation" v-model="password_confirmation" required :class="inputClass">
          </div>
        </div>
        
        <div class="mb-6">
          <label for="proof_document" class="block text-sm font-medium text-gray-700 mb-1">Kurum Kimlik/Belgesi (PDF, JPG/PNG, maks 2MB):</label>
          <input 
            type="file" 
            id="proof_document" 
            @change="handleFileUpload" 
            required 
            class="block w-full text-sm text-gray-900 border border-gray-300 rounded-lg cursor-pointer bg-gray-50 focus:outline-none"
            accept=".pdf,.jpg,.jpeg,.png"
          >
        </div>

        <p v-if="error" class="text-red-600 text-sm mb-4">Hata: {{ error }}</p>
        
        <button 
          type="submit" 
          :disabled="loading"
          class="w-full flex justify-center py-2 px-4 border border-transparent rounded-lg shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 disabled:opacity-50"
        >
          {{ loading ? 'Kayıt Olunuyor...' : 'Kayıt Ol' }}
        </button>
      </form>

      <p class="mt-6 text-center text-sm text-gray-600">
        Zaten hesabın var mı? 
        <NuxtLink to="/login" class="font-medium text-green-600 hover:text-green-500">
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