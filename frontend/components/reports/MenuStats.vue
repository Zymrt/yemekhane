<template>
  <div class="backdrop-blur-lg bg-white/10 border border-white/10 rounded-2xl p-6 shadow-lg">
    <h2 class="text-3xl font-extrabold mb-4 flex items-center gap-2">
      <i class="i-lucide-clipboard-list text-orange-400 text-2xl"></i>
      Menü İstatistikleri
    </h2>

    <p class="text-white/70 mb-8">
      Günlük menülerin tarihsel dağılımı ve son dönemde en çok çıkan yemeklerin özetini buradan görebilirsin.
    </p>

    <!-- DURUM -->
    <div v-if="loading" class="text-white/60 text-center py-8">Yükleniyor...</div>
    <div v-else-if="error" class="text-red-400 bg-red-900/40 border border-red-700/30 p-3 rounded-lg text-center">
      ⚠️ {{ error }}
    </div>

    <!-- 📊 GRAFİKLER -->
    <div v-else class="grid grid-cols-1 lg:grid-cols-2 gap-10">
      <!-- 7 Günlük Menü Grafiği -->
      <div class="rounded-xl bg-white/10 border border-white/10 p-6 shadow-md">
        <h3 class="text-xl font-semibold mb-4 text-white/90">📆 Son 7 Günlük Menü Dağılımı</h3>
        <Bar :chart-data="chartData7Days" :chart-options="chartOptions" />
      </div>

      <!-- En Popüler Yemekler -->
      <div class="rounded-xl bg-white/10 border border-white/10 p-6 shadow-md">
        <h3 class="text-xl font-semibold mb-4 text-white/90">🔥 En Popüler 8 Yemek</h3>
        <ul class="space-y-3">
          <li
            v-for="(item, idx) in topItems"
            :key="idx"
            class="flex justify-between items-center border-b border-white/10 pb-2"
          >
            <span class="text-white/90 font-medium">{{ item.name }}</span>
            <span class="text-orange-400 font-bold">{{ item.count }}x</span>
          </li>
        </ul>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { Bar } from 'vue3-chart-v2' // ✅ düzeltildi
import useAuth from '../composables/useAuth'

const { token } = useAuth()
const loading = ref(true)
const error = ref(null)
const menuStats = ref({ last7Days: {}, topItems: [] })

// Chart.js ayarları
const chartOptions = {
  responsive: true,
  scales: {
    x: {
      ticks: { color: '#ccc' },
      grid: { color: 'rgba(255,255,255,0.1)' }
    },
    y: {
      ticks: { color: '#ccc' },
      grid: { color: 'rgba(255,255,255,0.1)' }
    }
  },
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: 'rgba(0,0,0,0.7)',
      titleColor: '#fff',
      bodyColor: '#fff',
    }
  }
}

// Chart verisi
const chartData7Days = computed(() => {
  const entries = Object.entries(menuStats.value.last7Days || {})
  return {
    labels: entries.map(([date]) => date),
    datasets: [
      {
        label: 'Menü Sayısı',
        backgroundColor: '#f97316',
        borderRadius: 6,
        data: entries.map(([_, count]) => count),
      }
    ]
  }
})

const topItems = computed(() => menuStats.value.topItems || [])

onMounted(async () => {
  try {
    const res = await $fetch('http://127.0.0.1:8000/api/admin/dashboard', {
      headers: { Authorization: `Bearer ${token.value}` },
    })
    menuStats.value = res.menuStats || {}
  } catch (err) {
    console.error(err)
    error.value = 'Dashboard verisi çekilemedi.'
  } finally {
    loading.value = false
  }
})
</script>
