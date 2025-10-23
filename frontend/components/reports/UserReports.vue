<!-- frontend/components/reports/UserReports.vue -->
<template>
  <div>
    <h2 class="text-3xl font-bold mb-8 flex items-center gap-3">
      <span class="i-lucide-chart-pie text-orange-400"></span>
      Yönetim Raporları
    </h2>

    <p class="text-white/70 mb-10">
      Bu sayfada sistem verilerini, kullanıcı istatistiklerini ve menü aktivitelerini görüntüleyebilirsin.
    </p>

    <!-- Durum -->
    <div v-if="loading" class="text-white/70">Yükleniyor…</div>
    <div v-else-if="error" class="text-red-300">{{ error }}</div>

    <!-- Özet kutular -->
    <div v-else class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-8">
      <!-- Toplam Kullanıcı -->
      <div class="rounded-2xl backdrop-blur-md bg-white/5 border border-white/10 p-7 shadow-lg">
        <div class="flex items-center gap-3 mb-3">
          <span class="i-lucide-users text-cyan-300 text-xl"></span>
          <span class="text-white/80 font-semibold">Toplam Kullanıcı</span>
        </div>
        <div class="text-5xl font-extrabold tracking-tight text-white">
          {{ userStats.total }}
        </div>
        <div class="text-sm mt-2 text-white/60">
          Onaylı: <span class="font-semibold text-white">{{ userStats.approved }}</span> ·
          Bekleyen: <span class="font-semibold text-white">{{ userStats.pending }}</span>
        </div>
      </div>

      <!-- Toplam Menü -->
      <div class="rounded-2xl backdrop-blur-md bg-white/5 border border-white/10 p-7 shadow-lg">
        <div class="flex items-center gap-3 mb-3">
          <span class="i-lucide-clipboard-list text-emerald-300 text-xl"></span>
          <span class="text-white/80 font-semibold">Toplam Menü</span>
        </div>
        <div class="text-5xl font-extrabold tracking-tight text-white">
          {{ menuStats.total }}
        </div>
        <div class="text-sm mt-2 text-white/60">
          Bugün: <span class="font-semibold text-white">{{ menuStats.today ? 'Var' : 'Yok' }}</span>
        </div>
      </div>

      <!-- Son Güncelleme -->
      <div class="rounded-2xl backdrop-blur-md bg-white/5 border border-white/10 p-7 shadow-lg">
        <div class="flex items-center gap-3 mb-3">
          <span class="i-lucide-calendar-days text-violet-300 text-xl"></span>
          <span class="text-white/80 font-semibold">Son Güncelleme</span>
        </div>
        <div class="text-2xl font-bold text-white">
          {{ lastUpdateText }}
        </div>
        <div class="text-sm mt-2 text-white/60">
          Son düzenleyen: <span class="font-semibold text-white">Admin</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import useAuth from '../composables/useAuth'

const { token } = useAuth()

const loading = ref(true)
const error = ref(null)

const userStats = ref({ total: 0, pending: 0, approved: 0 })
const menuStats = ref({ total: 0, today: null, last7Days: {}, byMonth: [], topItems: [] })

const lastUpdateText = computed(() => {
  // today veya byMonth verisinden insani bir yazı üretelim
  if (menuStats.value.today?.updated_at) {
    try {
      const d = new Date(menuStats.value.today.updated_at)
      return d.toLocaleDateString('tr-TR', { day: '2-digit', month: 'long', year: 'numeric' })
    } catch { /* noop */ }
  }
  // byMonth’tan en son tarih
  if (menuStats.value.byMonth?.length) {
    const last = menuStats.value.byMonth[menuStats.value.byMonth.length - 1]
    const d = new Date(`${last.year}-${String(last.month).padStart(2,'0')}-01`)
    return d.toLocaleDateString('tr-TR', { month: 'long', year: 'numeric' })
  }
  return '—'
})

onMounted(async () => {
  try {
    const res = await $fetch('http://127.0.0.1:8000/api/admin/dashboard', {
      headers: { Authorization: `Bearer ${token.value}` },
    })

    // Backend dönen format: { userStats: {...}, menuStats: {...} }
    userStats.value = res?.userStats ?? userStats.value
    menuStats.value = res?.menuStats ?? menuStats.value
  } catch (e) {
    console.error(e)
    error.value = 'Dashboard verileri alınamadı.'
  } finally {
    loading.value = false
  }
})
</script>
