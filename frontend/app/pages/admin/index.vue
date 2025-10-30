<template>
  <div class="min-h-screen bg-gradient-to-br from-[#0f172a] via-[#1e293b] to-[#0f172a] text-white py-10 px-6">
    <!-- HEADER -->
    <div class="max-w-7xl mx-auto mb-12">
      <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
          <h1 class="text-4xl font-extrabold tracking-tight flex items-center gap-3">
            <i class="i-lucide-shield-check text-orange-400 text-5xl"></i>
            Yönetim Paneli
          </h1>
          <p class="text-gray-300 mt-2 text-lg">
            Hoş geldin <span class="text-orange-400 font-semibold">{{ user?.name || 'Yönetici' }}</span> 👋  
            Sistem üzerinde tam yetkilisin.
          </p>
        </div>
        <div class="text-right">
          <span class="text-sm text-gray-400">Güncel Tarih</span>
          <div class="text-lg font-semibold">{{ new Date().toLocaleDateString('tr-TR') }}</div>
        </div>
      </div>
    </div>

    <!-- ANA MENÜ -->
    <div class="max-w-7xl mx-auto grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8">
      
      <!-- Kullanıcı Onayları -->
      <NuxtLink
        to="/admin/onay"
        class="group relative bg-gradient-to-br from-cyan-600/10 to-cyan-400/5 hover:from-cyan-500/20 hover:to-cyan-400/10 border border-cyan-400/30 rounded-2xl p-8 transition-all duration-300 hover:-translate-y-1 shadow-lg hover:shadow-cyan-500/20"
      >
        <div class="flex items-center justify-center w-16 h-16 bg-cyan-500/10 rounded-full mb-6 group-hover:bg-cyan-500/20 transition-all duration-300">
          <UserGroupIcon class="w-8 h-8 text-cyan-400" />
        </div>
        <h2 class="text-2xl font-bold mb-2">Kullanıcı Onayları</h2>
        <p class="text-gray-300 text-sm">Yeni kayıtları onayla veya reddet.</p>
        <div class="absolute top-4 right-4 text-xs font-semibold bg-orange-500 text-white px-2 py-1 rounded-full shadow-md">
          Bekleyen Var
        </div>
      </NuxtLink>

      <!-- Menü Yönetimi -->
      <NuxtLink
        to="/admin/add-menu"
        class="group relative bg-gradient-to-br from-teal-600/10 to-teal-400/5 hover:from-teal-500/20 hover:to-teal-400/10 border border-teal-400/30 rounded-2xl p-8 transition-all duration-300 hover:-translate-y-1 shadow-lg hover:shadow-teal-500/20"
      >
        <div class="flex items-center justify-center w-16 h-16 bg-teal-500/10 rounded-full mb-6 group-hover:bg-teal-500/20 transition-all duration-300">
          <ClipboardDocumentListIcon class="w-8 h-8 text-teal-400" />
        </div>
        <h2 class="text-2xl font-bold mb-2">Menü Yönetimi</h2>
        <p class="text-gray-300 text-sm">Günlük yemek menüsünü düzenle.</p>
      </NuxtLink>

      <!-- Menüleri Görüntüle -->
      <NuxtLink
        to="/admin/menus"
        class="group relative bg-gradient-to-br from-emerald-600/10 to-emerald-400/5 hover:from-emerald-500/20 hover:to-emerald-400/10 border border-emerald-400/30 rounded-2xl p-8 transition-all duration-300 hover:-translate-y-1 shadow-lg hover:shadow-emerald-500/20"
      >
        <div class="flex items-center justify-center w-16 h-16 bg-emerald-500/10 rounded-full mb-6 group-hover:bg-emerald-500/20 transition-all duration-300">
          <ClipboardDocumentListIcon class="w-8 h-8 text-emerald-400" />
        </div>
        <h2 class="text-2xl font-bold mb-2">Menüleri Görüntüle</h2>
        <p class="text-gray-300 text-sm">Tüm geçmiş menü kayıtlarını incele.</p>
      </NuxtLink>

      <!-- Raporlar -->
      <div
        @click="goToDashboard"
        class="group relative bg-gradient-to-br from-indigo-600/10 to-indigo-400/5 hover:from-indigo-500/20 hover:to-indigo-400/10 border border-indigo-400/30 rounded-2xl p-8 transition-all duration-300 hover:-translate-y-1 shadow-lg hover:shadow-indigo-500/20 cursor-pointer"
      >
        <div class="flex items-center justify-center w-16 h-16 bg-indigo-500/10 rounded-full mb-6 group-hover:bg-indigo-500/20 transition-all duration-300">
          <ChartBarIcon class="w-8 h-8 text-indigo-400" />
        </div>
        <h2 class="text-2xl font-bold mb-2">Raporlar</h2>
        <p class="text-gray-300 text-sm">Sistem istatistiklerini ve performans verilerini görüntüle.</p>
      </div>

    </div>
  </div>
</template>
<script setup>
import { ref, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import useAuth from '../composables/useAuth'
import {
  UserGroupIcon,
  ClipboardDocumentListIcon,
  ChartBarIcon,
} from '@heroicons/vue/24/outline'

// ✅ Sayfa bilgisi
definePageMeta({ layout: 'admin' })

const router = useRouter()
const { user } = useAuth()

// ⏰ Canlı saat
const currentTime = ref('')
const updateClock = () => {
  const now = new Date()
  currentTime.value = now.toLocaleTimeString('tr-TR', { hour12: false })
}
onMounted(() => {
  updateClock()
  setInterval(updateClock, 1000)
})

// 📊 Rapor yönlendirmesi
const goToDashboard = () => router.push('/admin/dashboard')
</script>
