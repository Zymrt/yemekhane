<template>
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    
    <!-- 🏢 SOL: BİRİM DAĞILIMI (PASTA GRAFİK) -->
    <div class="relative group">
      <div class="absolute -inset-0.5 bg-gradient-to-r from-indigo-500 to-purple-600 rounded-3xl blur opacity-20 group-hover:opacity-40 transition duration-1000"></div>
      
      <div class="relative h-full bg-[#1e293b]/80 backdrop-blur-xl border border-white/10 rounded-2xl p-6 shadow-2xl flex flex-col">
        <div class="mb-6">
          <h3 class="text-xl font-bold text-white flex items-center gap-2">
            <span class="p-2 rounded-lg bg-indigo-500/20 text-indigo-400">
              <i class="i-lucide-pie-chart text-xl"></i>
            </span>
            Personel Dağılımı
          </h3>
          <p class="text-white/50 text-sm mt-1 ml-11">Hangi birimde kaç personel kayıtlı?</p>
        </div>

        <div class="flex-1 min-h-[300px] relative w-full flex items-center justify-center">
          <ClientOnly>
            <Doughnut v-if="!loading && unitData.labels.length" :data="doughnutData" :options="doughnutOptions" />
            <div v-else class="text-white/30">Veri yükleniyor...</div>
          </ClientOnly>
        </div>
      </div>
    </div>

    <!-- 💰 SAĞ: BAKİYE ANALİZİ (BAR GRAFİK) -->
    <div class="relative group">
      <div class="absolute -inset-0.5 bg-gradient-to-r from-emerald-500 to-teal-600 rounded-3xl blur opacity-20 group-hover:opacity-40 transition duration-1000"></div>

      <div class="relative h-full bg-[#1e293b]/80 backdrop-blur-xl border border-white/10 rounded-2xl p-6 shadow-2xl flex flex-col">
        <div class="mb-6">
          <h3 class="text-xl font-bold text-white flex items-center gap-2">
            <span class="p-2 rounded-lg bg-emerald-500/20 text-emerald-400">
              <i class="i-lucide-wallet text-xl"></i>
            </span>
            Birim Bazlı Bakiye
          </h3>
          <p class="text-white/50 text-sm mt-1 ml-11">Birimlerin toplam cüzdan hacmi (TL)</p>
        </div>

        <div class="flex-1 min-h-[300px] relative w-full">
          <ClientOnly>
            <Bar v-if="!loading && unitData.labels.length" :data="barData" :options="barOptions" />
            <div v-else class="text-white/30 flex items-center justify-center h-full">Veri yükleniyor...</div>
          </ClientOnly>
        </div>
      </div>
    </div>

    <!-- 📋 ALT: ÖZET TABLO -->
    <div class="col-span-1 lg:col-span-2 bg-[#1e293b]/80 backdrop-blur-xl border border-white/10 rounded-2xl p-6 shadow-2xl">
      <h4 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
        <i class="i-lucide-list text-white/50"></i> Detaylı Liste
      </h4>
      <div class="overflow-x-auto">
        <table class="w-full text-left text-sm text-white/70">
          <thead class="bg-white/5 text-white/90 uppercase text-xs font-semibold">
            <tr>
              <th class="p-3 rounded-l-lg">Birim Adı</th>
              <th class="p-3 text-center">Personel Sayısı</th>
              <th class="p-3 text-right rounded-r-lg">Toplam Bakiye</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/5">
            <tr v-for="(item, idx) in rawData" :key="idx" class="hover:bg-white/5 transition">
              <td class="p-3 font-medium text-white">{{ item.unit }}</td>
              <td class="p-3 text-center">
                <span class="bg-white/10 px-2 py-1 rounded text-xs text-white">{{ item.user_count }} kişi</span>
              </td>
              <td class="p-3 text-right font-mono text-emerald-400">
                {{ item.total_balance.toFixed(2) }} ₺
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { Doughnut, Bar } from 'vue-chartjs'
import { Chart as ChartJS, ArcElement, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js'

// Chart.js Bileşenlerini Kaydet
ChartJS.register(ArcElement, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

const loading = ref(true)
const rawData = ref([])

// 🎨 Renk Paletleri
const colors = [
  '#6366f1', '#8b5cf6', '#ec4899', '#f43f5e', '#f97316', '#eab308', '#22c55e', '#06b6d4', '#3b82f6'
]

// Verileri İşle
const unitData = computed(() => {
  if (!rawData.value.length) return { labels: [], counts: [], balances: [] }
  
  return {
    labels: rawData.value.map(i => i.unit),
    counts: rawData.value.map(i => i.user_count),
    balances: rawData.value.map(i => i.total_balance)
  }
})

// 🍩 PASTA GRAFİK VERİSİ
const doughnutData = computed(() => ({
  labels: unitData.value.labels,
  datasets: [{
    data: unitData.value.counts,
    backgroundColor: colors,
    borderWidth: 0,
    hoverOffset: 10
  }]
}))

const doughnutOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { position: 'right', labels: { color: '#cbd5e1', font: { size: 11 }, padding: 20 } }
  }
}

// 📊 BAR GRAFİK VERİSİ
const barData = computed(() => ({
  labels: unitData.value.labels,
  datasets: [{
    label: 'Toplam Bakiye (TL)',
    data: unitData.value.balances,
    backgroundColor: '#10b981',
    borderRadius: 6,
    barThickness: 40
  }]
}))

const barOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: { legend: { display: false } },
  scales: {
    x: { ticks: { color: '#94a3b8' }, grid: { display: false } },
    y: { ticks: { color: '#94a3b8' }, grid: { color: 'rgba(255,255,255,0.05)' } }
  }
}

onMounted(async () => {
  try {
    // API isteği
    const res = await $fetch('/api/admin/stats/units')
    rawData.value = res || []
  } catch (err) {
    console.error('Unit stats error:', err)
  } finally {
    loading.value = false
  }
})
</script>