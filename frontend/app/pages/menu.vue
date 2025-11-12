<template>
  <!-- Sayfayı user layout ile sar ve named slot'ları layout'a geçir -->
  <NuxtLayout name="user">
    <!-- 🔹 Navbar butonları -->
    <template #left-buttons>
      <NuxtLink to="/menu" class="btn btn-ghost">
        ANA SAYFA
      </NuxtLink>
      <NuxtLink to="/yorumlar" class="btn btn-ghost">
        DEĞERLENDİRMELERİM
      </NuxtLink>
    </template>

    <template #right-buttons>
      <NuxtLink to="/hesap-hareketleri" class="btn btn-outline">
        HESAP HAREKETLERİ
      </NuxtLink>
      <NuxtLink to="/bakiye" class="btn btn-primary">
        BAKİYE YÜKLE
      </NuxtLink>
    </template>

    <!-- 💫 İçerik -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-10 mt-6">
      <!-- 👤 Profil Bilgileri -->
      <div
        class="md:col-span-1 bg-white/30 backdrop-blur-2xl border border-white/30
               rounded-3xl p-6 shadow-lg hover:shadow-2xl transform hover:-translate-y-1
               transition-all duration-300"
      >
        <h2 class="text-2xl font-bold text-gray-900 mb-4 drop-shadow-sm">Profil Bilgileri</h2>
        <div class="space-y-3 text-gray-800">
          <p><strong>Ad Soyad:</strong> {{ user?.name }} {{ user?.surname }}</p>
          <p><strong>Birim:</strong> {{ user?.unit }}</p>
          <p><strong>Telefon:</strong> {{ user?.phone || '-' }}</p>
          <p><strong>Kayıt Tarihi:</strong> {{ formatDate(user?.created_at) }}</p>
          <p class="mt-3">
            <strong>Bakiye: </strong>
            <span class="text-emerald-600 font-bold text-lg">{{ user?.balance?.toFixed(2) || '0.00' }} ₺</span>
          </p>
        </div>
      </div>

      <!-- 🍲 Günlük Menü -->
      <div
        class="md:col-span-2 bg-white/30 backdrop-blur-2xl border border-white/30
               rounded-3xl p-6 shadow-lg hover:shadow-2xl transform hover:-translate-y-1
               transition-all duration-300"
      >
        <div class="flex flex-wrap gap-3 items-center justify-between mb-4">
          <h2 class="text-2xl font-bold text-gray-900 drop-shadow-sm">Bugünün Menüsü</h2>

          <!-- Linki butona çevirdik -->
          <NuxtLink to="/menu" class="btn btn-soft">
            Tüm menüyü gör →
          </NuxtLink>
        </div>

        <div v-if="loading" class="text-center text-gray-600 py-6">Menü yükleniyor...</div>
        <div v-else-if="error" class="text-red-600 text-center py-6">{{ error }}</div>

        <ul v-else class="divide-y divide-white/50">
          <li
            v-for="(item, index) in (menu?.items || menu || [])"
            :key="index"
            class="py-3 flex justify-between items-center text-gray-800"
          >
            <span class="font-medium">{{ item.name }}</span>
            <span v-if="item.calories" class="text-sm text-gray-500">{{ item.calories }} kcal</span>
          </li>
        </ul>

        <div v-if="!(menu?.items?.length || menu?.length)" class="text-center text-gray-600 py-6">
          Bugün için menü bulunamadı 🍽️
        </div>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import useAuth from '../composables/useAuth'
import protectUserPage from '../composables/protectUserPage'

protectUserPage()

const { user } = useAuth()
const menu = ref(null)
const loading = ref(true)
const error = ref(null)

const fetchMenu = async () => {
  try {
    const res = await $fetch('/api/menu/today')
    menu.value = res
  } catch (err) {
    console.error('Menü Hatası:', err)
    error.value = 'Menü yüklenemedi.'
  } finally {
    loading.value = false
  }
}

const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('tr-TR')
}

onMounted(fetchMenu)
</script>

<style scoped>
/* 🔘 Minimal buton sistemimiz (Tailwind @apply) */
.btn {
  @apply inline-flex items-center justify-center px-4 py-2 rounded-xl font-semibold transition
         focus:outline-none focus:ring-2 focus:ring-offset-0 active:scale-[.99];
}

/* Navbar için “ghost” (şeffaf) */
.btn-ghost {
  @apply text-white/90 hover:text-white bg-white/0 hover:bg-white/10 border border-white/10;
}

/* Sağ taraftaki “outline” */
.btn-outline {
  @apply text-white border border-white/40 bg-transparent hover:bg-white/10;
}

/* Vurgulu buton */
.btn-primary {
  @apply text-white bg-gradient-to-r from-orange-500 via-orange-500 to-orange-600
         hover:brightness-110 shadow-md;
}

/* İçerik alanındaki yumuşak buton */
.btn-soft {
  @apply text-sky-900 bg-white/70 hover:bg-white/90 border border-white/80
         backdrop-blur-sm rounded-xl;
}

/* Küçük ekranlarda butonların nefes alması için */
@media (max-width: 768px) {
  .btn { @apply text-sm px-3 py-2; }
}
</style>
