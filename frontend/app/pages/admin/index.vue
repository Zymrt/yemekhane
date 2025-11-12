<template>
  <div class="min-h-screen bg-slate-950 text-slate-100">
    <!-- HEADER -->
    <header class="max-w-7xl mx-auto px-6 py-8">
      <div class="flex flex-col md:flex-row md:items-end md:justify-between gap-4">
        <div>
          <h1 class="text-3xl font-extrabold tracking-tight flex items-center gap-3">
            <i class="i-lucide-shield-check text-orange-400 text-4xl"></i>
            Yönetim Paneli
          </h1>
          <p class="text-slate-400 mt-2">
            Hoş geldin <span class="text-orange-400 font-semibold">{{ user?.name || 'Yönetici' }}</span>.
            Yetkilerin aktif.
          </p>
        </div>
        <div class="text-right">
          <div class="text-xs uppercase tracking-wide text-slate-500">Bugün</div>
          <div class="text-sm font-medium text-slate-200">{{ currentDate }}</div>
          <div class="text-xs text-slate-500">{{ currentTime }}</div>
        </div>
      </div>
    </header>

    <!-- ANA MENÜ -->
    <main class="max-w-7xl mx-auto px-6 pb-14">
      <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
        <!-- Kullanıcı Onayları -->
        <NuxtLink
          to="/admin/onay"
          class="card group"
        >
          <div class="card-icon bg-cyan-500/10 border-cyan-400/20">
            <UserGroupIcon class="w-6 h-6 text-cyan-300" />
          </div>
          <h2 class="card-title">Kullanıcı Onayları</h2>
          <p class="card-desc">Yeni kayıtları onayla veya reddet.</p>
          <span class="badge">Bekleyen Var</span>
        </NuxtLink>

        <!-- Menü Yönetimi -->
        <NuxtLink
          to="/admin/add-menu"
          class="card group"
        >
          <div class="card-icon bg-teal-500/10 border-teal-400/20">
            <ClipboardDocumentListIcon class="w-6 h-6 text-teal-300" />
          </div>
          <h2 class="card-title">Menü Yönetimi</h2>
          <p class="card-desc">Günlük yemek menüsünü düzenle.</p>
        </NuxtLink>

        <!-- Menüleri Görüntüle -->
        <NuxtLink
          to="/admin/menus"
          class="card group"
        >
          <div class="card-icon bg-emerald-500/10 border-emerald-400/20">
            <ClipboardDocumentListIcon class="w-6 h-6 text-emerald-300" />
          </div>
          <h2 class="card-title">Menüleri Görüntüle</h2>
          <p class="card-desc">Geçmiş menü kayıtlarını incele.</p>
        </NuxtLink>

        <!-- Raporlar -->
        <button
          type="button"
          @click="goToDashboard"
          class="card group text-left"
        >
          <div class="card-icon bg-indigo-500/10 border-indigo-400/20">
            <ChartBarIcon class="w-6 h-6 text-indigo-300" />
          </div>
          <h2 class="card-title">Raporlar</h2>
          <p class="card-desc">İstatistikler ve performans ekranı.</p>
        </button>
      </div>
    </main>
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

definePageMeta({ layout: 'admin' })

const router = useRouter()
const { user } = useAuth()

// Tarih & saat (sade)
const currentDate = new Date().toLocaleDateString('tr-TR')
const currentTime = ref('')
const tick = () => (currentTime.value = new Date().toLocaleTimeString('tr-TR', { hour12: false }))

onMounted(() => {
  tick()
  setInterval(tick, 1000)
})

const goToDashboard = () => router.push('/admin/dashboard')
</script>

<style scoped>
/* Minimal card set (sade, profesyonel) */
.card {
  @apply relative bg-slate-900/60 border border-slate-800 rounded-2xl p-6
         hover:border-slate-700 hover:bg-slate-900/70 transition-colors
         focus:outline-none focus-visible:ring-2 focus-visible:ring-orange-500/40;
}
.card-icon {
  @apply w-12 h-12 rounded-xl border flex items-center justify-center mb-4;
}
.card-title {
  @apply text-lg font-semibold text-slate-100;
}
.card-desc {
  @apply text-sm text-slate-400 mt-1;
}
.badge {
  @apply absolute top-4 right-4 text-[10px] font-semibold bg-orange-500 text-white
         px-2 py-0.5 rounded-full;
}
</style>
