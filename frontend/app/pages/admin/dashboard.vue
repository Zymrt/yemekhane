<template>
  <div class="min-h-screen bg-gradient-to-br from-[#1e293b] via-[#1e293b] to-[#1e293b] text-white">
    <!-- ÜST NAV -->
    <header class="backdrop-blur-md bg-white/10 border-b border-white/10 sticky top-0 z-20">
      <div class="max-w-7xl mx-auto flex items-center justify-between px-6 py-4">
        <h1 class="text-2xl font-bold flex items-center gap-2">
          <i class="i-lucide-bar-chart-3 text-orange-400"></i>
          Yönetim Dashboard
        </h1>

        <!-- SEKME BUTONLARI -->
        <nav class="flex items-center gap-4 text-sm">
          <button
            v-for="tab in tabs"
            :key="tab.id"
            @click="activeTab = tab.id"
            :class="[
              'px-4 py-2 rounded-lg font-semibold transition-all duration-200',
              activeTab === tab.id
                ? 'bg-orange-500 text-white shadow-lg'
                : 'text-white/70 hover:text-white hover:bg-white/10'
            ]"
          >
            {{ tab.name }}
          </button>
        </nav>

        <NuxtLink
          to="/admin"
          class="text-xs text-white/60 hover:text-white transition"
        >
          ← Admin Paneli
        </NuxtLink>
      </div>
    </header>

    <!-- İÇERİK -->
    <main class="max-w-7xl mx-auto px-6 py-10 transition-all duration-300">
      <transition name="fade" mode="out-in">
        <component :is="currentComponent" />
      </transition>
    </main>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

definePageMeta({ layout: 'admin' })

// ----------------------------------------------------
// ⛔️ 'useAuthGuard' veya 'protectAdminPage' ile ilgili
// HİÇBİR KOD OLMAMALI. Hepsi silinmeli.
// ----------------------------------------------------

// Statik importlar (dinamik import yerine sabit componentler)
import UserReports from '../components/reports/UserReports.vue'
import MenuStats from '../components/reports/MenuStats.vue'
import SystemLogs from '../components/reports/SystemLogs.vue'
import ActivityChart from '../components/reports/ActivityChart.vue'

// Sekme yapısı
const tabs = [
  { id: 'users', name: 'Kullanıcı Raporları', component: UserReports },
  { id: 'menu', name: 'Menü İstatistikleri', component: MenuStats },
  { id: 'logs', name: 'Sistem Logları', component: SystemLogs },
  { id: 'activity', name: 'Aktivite Grafiği', component: ActivityChart }
]

const activeTab = ref('users')
const currentComponent = computed(() => {
  const tab = tabs.find(t => t.id === activeTab.value)
  return tab ? tab.component : null
})
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.4s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>
