<template>
  <div class="space-y-10">
    <!-- HEADER -->
    <div class="p-8 backdrop-blur-lg bg-white/10 rounded-2xl shadow-xl border border-white/10">
      <h1 class="text-4xl font-bold text-white">
        Hoş Geldin, <span class="text-orange-500">{{ user?.name }}!</span>
      </h1>
      <p class="text-gray-300 mt-2">
        Yönetim paneline hoş geldin. Buradan tüm işlemleri kolayca yönetebilirsin.
      </p>
    </div>

    <!-- ANA KARTLAR -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">

      <!-- Kullanıcı Onayları -->
      <NuxtLink 
        to="/admin/onay" 
        class="group relative backdrop-blur-lg bg-white/10 hover:bg-white/20 p-8 rounded-2xl shadow-md border border-white/10 transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_0_25px_rgba(255,255,255,0.2)]"
      >
        <div class="flex items-center justify-center w-16 h-16 bg-cyan-400/10 rounded-full mb-5 transition-all duration-300 group-hover:scale-110 group-hover:bg-cyan-400/20">
          <UserGroupIcon class="w-8 h-8 text-cyan-300" />
        </div>
        <h2 class="text-2xl font-bold text-white mb-2">Kullanıcı Onayları</h2>
        <p class="text-gray-300">Yeni kayıt olan kullanıcıları onayla veya reddet.</p>
        <div class="absolute top-4 right-4 text-xs font-bold bg-orange-400/80 text-white px-2 py-1 rounded-full shadow-md">
          İşlem Gerekli
        </div>
      </NuxtLink>

      <!-- Menü Yönetimi -->
      <NuxtLink 
        to="/admin/add-menu" 
        class="group relative backdrop-blur-lg bg-white/10 hover:bg-white/20 p-8 rounded-2xl shadow-md border border-white/10 transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_0_25px_rgba(255,255,255,0.2)]"
      >
        <div class="flex items-center justify-center w-16 h-16 bg-teal-400/10 rounded-full mb-5 transition-all duration-300 group-hover:scale-110 group-hover:bg-teal-400/20">
          <ClipboardDocumentListIcon class="w-8 h-8 text-teal-300" />
        </div>
        <h2 class="text-2xl font-bold text-white mb-2">Menü Yönetimi</h2>
        <p class="text-gray-300">Günlük yemek menüsünü düzenle.</p>
      </NuxtLink>

      <!-- Menüleri Görüntüle -->
      <NuxtLink 
        to="/admin/menus" 
        class="group relative backdrop-blur-lg bg-white/10 hover:bg-white/20 p-8 rounded-2xl shadow-md border border-white/10 transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_0_25px_rgba(255,255,255,0.2)]"
      >
        <div class="flex items-center justify-center w-16 h-16 bg-emerald-400/10 rounded-full mb-5 transition-all duration-300 group-hover:scale-110 group-hover:bg-emerald-400/20">
          <ClipboardDocumentListIcon class="w-8 h-8 text-emerald-300" />
        </div>
        <h2 class="text-2xl font-bold text-white mb-2">Menüleri Görüntüle</h2>
        <p class="text-gray-300">Tüm geçmiş menüleri görüntüle ve gerekirse sil.</p>
      </NuxtLink>

      <!-- Raporlar -->
      <div
        class="group relative backdrop-blur-lg bg-white/10 hover:bg-white/20 p-8 rounded-2xl shadow-md border border-white/10 transition-all duration-300 hover:-translate-y-1 hover:shadow-[0_0_25px_rgba(255,255,255,0.2)] cursor-pointer"
        @click="goToDashboard"
      >
        <div class="flex items-center justify-center w-16 h-16 bg-indigo-400/10 rounded-full mb-5 transition-all duration-300 group-hover:scale-110 group-hover:bg-indigo-400/20">
          <ChartBarIcon class="w-8 h-8 text-indigo-300" />
        </div>
        <h2 class="text-2xl font-bold text-white mb-2">Raporlar</h2>
        <p class="text-gray-300">Kullanıcı ve sistem istatistiklerini görüntüle.</p>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRouter } from 'vue-router'
import useAuthGuard from '../composables/useAuthGuard'
import useAuth from '../composables/useAuth'
import { UserGroupIcon, ClipboardDocumentListIcon, ChartBarIcon } from '@heroicons/vue/24/outline'

definePageMeta({ layout: 'admin' })
const { protectAdminPage } = useAuthGuard()
protectAdminPage()

const router = useRouter()
const { user } = useAuth()

// ⏰ saat göstergesi
const currentTime = ref('')
const updateClock = () => {
  const now = new Date()
  currentTime.value = now.toLocaleTimeString('tr-TR', { hour12: false })
}
setInterval(updateClock, 1000)
updateClock()

// 📊 Raporlar yönlendirmesi
const goToDashboard = () => {
  router.push('/admin/dashboard')
}
</script>
