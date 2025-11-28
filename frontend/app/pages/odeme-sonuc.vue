<template>
  <!-- Sayfayı user layout ile sar -->
  <NuxtLayout name="user">
    <!-- Navbar butonları (Hepsini pasif yapıyoruz, burası bir sonuç ekranı) -->
    <template #left-buttons>
      <NuxtLink to="/menu" class="btn btn-ghost">
        Ana Sayfa
      </NuxtLink>
      <NuxtLink to="/yorumlar" class="btn btn-ghost">
        Değerlendirmelerim
      </NuxtLink>
    </template>
    <template #right-buttons>
      <NuxtLink to="/hesap-hareketleri" class="btn btn-outline">
        Hesap Hareketleri
      </NuxtLink>
      <NuxtLink to="/bakiye" class="btn btn-primary">
        Bakiye Yükle
      </NuxtLink>
    </template>

    <!-- 💫 İçerik -->
    <div class="max-w-2xl mx-auto mt-6">
      
      <!-- Başarılı Ödeme -->
      <div 
        v-if="status === 'success'"
        class="bg-white/30 backdrop-blur-2xl border border-white/30
               rounded-3xl p-8 shadow-lg text-center"
      >
        <div class="text-6xl mb-4 text-emerald-600">✅</div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Ödeme Başarılı!</h1>
        <p class="text-xl text-gray-800">
          Hesabınıza <strong>{{ amount.toFixed(2) }} ₺</strong> yüklendi.
        <br>
       <span class="text-sm"> (Bakiyenizin hesabınıza yansıması biraz zaman alabilir.)</span>
       </p>

        <NuxtLink to="/menu" class="btn btn-primary mt-6 py-3 px-6">
          Ana Sayfaya Dön
        </NuxtLink>
      </div>

      <!-- Başarısız Ödeme (Simülasyonda pek olmaz ama) -->
      <div 
        v-else
        class="bg-white/30 backdrop-blur-2xl border border-white/30
               rounded-3xl p-8 shadow-lg text-center"
      >
        <div class="text-6xl mb-4 text-rose-600">❌</div>
        <h1 class="text-3xl font-bold text-gray-900 mb-2">Ödeme Başarısız</h1>
        <p class="text-xl text-gray-800">
          İşlem sırasında bir hata oluştu.
        </p>
        <NuxtLink to="/bakiye" class="btn btn-primary mt-6 py-3 px-6">
          Tekrar Dene
        </NuxtLink>
      </div>

    </div>
  </NuxtLayout>
</template>

<script setup>
import { computed } from 'vue'
import useAuth from '../composables/useAuth'
import protectUserPage from '../composables/protectUserPage'

// Sayfa koruması
await protectUserPage()

const route = useRoute()

// URL'den gelen 'status' ve 'amount' parametrelerini al
const status = computed(() => route.query.status || 'error')
const amount = computed(() => parseFloat(route.query.amount || '0'))
</script>

<style scoped>
/* 🔘 Minimal buton sistemimiz (Tailwind @apply) */
.btn {
  @apply inline-flex items-center justify-center px-4 py-2 rounded-xl font-semibold transition
         focus:outline-none focus:ring-2 focus:ring-offset-0 active:scale-[.99];
}
.btn-ghost {
  @apply text-white/90 hover:text-white bg-white/0 hover:bg-white/10 border border-white/10;
}
.btn-outline {
  @apply text-white border border-white/40 bg-transparent hover:bg-white/10;
}
.btn-primary {
  @apply text-white bg-gradient-to-r from-orange-500 via-orange-500 to-orange-600
         hover:brightness-110 shadow-md;
}
</style>