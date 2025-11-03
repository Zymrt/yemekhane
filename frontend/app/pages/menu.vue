<template>
  <!-- Sayfayı user layout ile sar ve named slot'ları layout'a geçir -->
  <NuxtLayout name="user">
    <!-- 🔹 Navbar butonları -->
    <template #left-buttons>
      <NuxtLink to="/menu" class="text-white font-semibold hover:text-orange-200 transition">
        ANA SAYFA
      </NuxtLink>
      <NuxtLink to="/reports" class="text-white font-semibold hover:text-orange-200 transition">
        DEĞERLENDİRMELERİM
      </NuxtLink>
    </template>

    <template #right-buttons>
      <NuxtLink to="/notifications" class="text-white font-semibold hover:text-orange-200 transition">
       HESAP HAREKETLERİ
      </NuxtLink>
      <NuxtLink to="/profile" class="text-white font-semibold hover:text-orange-200 transition">
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
        <div class="flex justify-between items-center mb-4">
          <h2 class="text-2xl font-bold text-gray-900 drop-shadow-sm">Bugünün Menüsü</h2>
          <NuxtLink
            to="/menu"
            class="text-sky-700 hover:text-sky-900 font-medium underline decoration-sky-400"
          >
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

// ⚠️ BUNU KALDIRDIK: definePageMeta({ layout: 'user' })
// Çünkü named slotları layout'a geçmek için <NuxtLayout name="user"> kullanıyoruz.

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
