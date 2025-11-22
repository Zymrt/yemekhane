<template>
  <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
    
    <!-- 📊 SOL TARA: GRAFİK KARTI -->
    <div class="relative group">
      <div class="absolute -inset-0.5 bg-gradient-to-r from-orange-600 to-amber-600 rounded-3xl blur opacity-20 group-hover:opacity-40 transition duration-1000"></div>
      
      <div class="relative h-full bg-[#1e293b]/80 backdrop-blur-xl border border-white/10 rounded-2xl p-6 shadow-2xl flex flex-col">
        <div class="flex justify-between items-start mb-6">
          <div>
            <h3 class="text-xl font-bold text-white flex items-center gap-2">
              <span class="p-2 rounded-lg bg-orange-500/20 text-orange-400">
                <i class="i-lucide-bar-chart-2 text-xl"></i>
              </span>
              Haftalık Tüketim Analizi
            </h3>
            <p class="text-white/50 text-sm mt-1 ml-11">Son 7 günün menü giriş yoğunluğu</p>
          </div>
        </div>

        <div class="flex-1 min-h-[300px] relative w-full">
          <ClientOnly>
            <Bar v-if="!loading && chartData7Days" :data="chartData7Days" :options="chartOptions" />
            <div v-else class="flex items-center justify-center h-full text-white/30">
              Veri yükleniyor...
            </div>
          </ClientOnly>
        </div>
      </div>
    </div>

    <!-- 🏆 SAĞ TARAF: LİDERLİK TABLOSU (FİLTRELİ) -->
    <div class="relative group">
      <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-600 to-cyan-600 rounded-3xl blur opacity-20 group-hover:opacity-40 transition duration-1000"></div>

      <div class="relative h-full bg-[#1e293b]/80 backdrop-blur-xl border border-white/10 rounded-2xl p-6 shadow-2xl">
        <div class="mb-6">
          <h3 class="text-xl font-bold text-white flex items-center gap-2">
            <span class="p-2 rounded-lg bg-cyan-500/20 text-cyan-400">
              <i class="i-lucide-trophy text-xl"></i>
            </span>
            Favori Ana Yemekler
          </h3>
          <p class="text-white/50 text-sm mt-1 ml-11">
            Personelin en çok tercih ettiği yemekler
            <span class="text-xs text-white/30">(Yan ürünler hariç)</span>
          </p>
        </div>

        <div class="space-y-4 pr-2 custom-scrollbar overflow-y-auto max-h-[350px]">
          <div 
            v-for="(item, idx) in processedTopItems" 
            :key="idx"
            class="relative flex items-center gap-4 p-3 rounded-xl transition-all duration-300 hover:bg-white/5 border border-transparent hover:border-white/5 group/item"
          >
            <!-- Sıralama Rozeti -->
            <div 
              class="w-10 h-10 flex-shrink-0 flex items-center justify-center rounded-full font-bold text-lg shadow-lg border border-white/10"
              :class="getRankStyle(idx)"
            >
              {{ idx + 1 }}
            </div>

            <!-- Yemek Bilgisi -->
            <div class="flex-1">
              <div class="flex justify-between items-center mb-1">
                <span class="text-white font-medium group-hover/item:text-cyan-200 transition">{{ item.name }}</span>
                <span class="text-xs font-mono text-white/60 bg-white/10 px-2 py-0.5 rounded-md">
                  {{ item.count }} kez
                </span>
              </div>
              
              <div class="h-2 w-full bg-white/5 rounded-full overflow-hidden">
                <div 
                  class="h-full rounded-full transition-all duration-1000 ease-out"
                  :class="getProgressColor(idx)"
                  :style="{ width: item.percentage + '%' }"
                ></div>
              </div>
            </div>
          </div>

          <div v-if="!loading && processedTopItems.length === 0" class="text-center py-10 text-white/30 italic">
            Listelenecek ana yemek bulunamadı.
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { Bar } from 'vue-chartjs'
import { Chart as ChartJS, Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale } from 'chart.js'

ChartJS.register(Title, Tooltip, Legend, BarElement, CategoryScale, LinearScale)

const loading = ref(true)
const menuStats = ref({ last7Days: {}, topItems: [] })

// 🚫 KARA LİSTE: Bu kelimeleri içeren yemekler sıralamaya girmez
const ignoredKeywords = [
  'Salata', 'Cacık', 'Ayran', 'Su', 'Ekmek', 'Yoğurt', 
  // İstersen buraya 'Çorba'yı da ekleyebilirsin, sadece ana yemek kalsın diye.
]

const chartOptions = {
  responsive: true,
  maintainAspectRatio: false,
  plugins: {
    legend: { display: false },
    tooltip: {
      backgroundColor: 'rgba(15, 23, 42, 0.9)',
      titleColor: '#fff',
      bodyColor: '#cbd5e1',
      borderColor: 'rgba(255,255,255,0.1)',
      borderWidth: 1,
      padding: 10,
      cornerRadius: 8,
      displayColors: false,
      callbacks: { label: (context) => ` ${context.raw} Menü` }
    }
  },
  scales: {
    x: { grid: { display: false }, ticks: { color: '#94a3b8', font: { size: 11 } } },
    y: { grid: { color: 'rgba(255,255,255,0.05)', borderDash: [5, 5] }, ticks: { color: '#94a3b8', stepSize: 1 }, beginAtZero: true }
  },
  animation: { duration: 2000, easing: 'easeOutQuart' }
}

const chartData7Days = computed(() => {
  const rawData = menuStats.value.last7Days || {}
  const sortedDates = Object.keys(rawData).sort()
  const labels = sortedDates.map(date => {
    const d = new Date(date)
    return d.toLocaleDateString('tr-TR', { day: 'numeric', month: 'short' })
  })
  const data = sortedDates.map(date => rawData[date])

  return {
    labels,
    datasets: [{
      label: 'Menü Sayısı',
      data: data,
      backgroundColor: (ctx) => {
        const canvas = ctx.chart.ctx;
        const gradient = canvas.createLinearGradient(0, 0, 0, 400);
        gradient.addColorStop(0, '#f97316');
        gradient.addColorStop(1, 'rgba(249, 115, 22, 0.2)');
        return gradient;
      },
      borderRadius: 6,
      barThickness: 30,
      hoverBackgroundColor: '#fb923c'
    }]
  }
})

// 🏆 FİLTRELENMİŞ LİDERLİK TABLOSU
const processedTopItems = computed(() => {
  let items = menuStats.value.topItems || []
  
  // 1. ADIM: Filtreleme (Salata vb. çıkar)
  items = items.filter(item => {
    const name = item.name.toLowerCase()
    // Eğer isim, yasaklı kelimelerden herhangi birini içeriyorsa listeden at
    return !ignoredKeywords.some(keyword => name.includes(keyword.toLowerCase()))
  })

  // Eğer hepsi silinirse boş dön
  if (items.length === 0) return []

  // 2. ADIM: Yeniden Sıralama (Backend zaten sıralı gönderiyor ama filtre sonrası garanti olsun)
  items.sort((a, b) => b.count - a.count)

  // 3. ADIM: İlk 8'i al (Filtreleme sonrası liste kısalmış olabilir)
  items = items.slice(0, 8)

  // 4. ADIM: Yüzdelik Hesapla
  const maxCount = Math.max(...items.map(i => i.count))

  return items.map(item => ({
    ...item,
    percentage: (item.count / maxCount) * 100
  }))
})

const getRankStyle = (index) => {
  if (index === 0) return 'bg-yellow-500/20 text-yellow-400 border-yellow-500/50'
  if (index === 1) return 'bg-slate-400/20 text-slate-300 border-slate-400/50'
  if (index === 2) return 'bg-orange-700/20 text-orange-400 border-orange-700/50'
  return 'bg-white/5 text-white/40 border-white/5'
}

const getProgressColor = (index) => {
  if (index === 0) return 'bg-gradient-to-r from-yellow-500 to-amber-600'
  if (index === 1) return 'bg-gradient-to-r from-slate-400 to-slate-500'
  if (index === 2) return 'bg-gradient-to-r from-orange-600 to-orange-800'
  return 'bg-white/20'
}

onMounted(async () => {
  try {
    const res = await $fetch('/api/admin/dashboard')
    menuStats.value = res.menuStats || {}
  } catch (err) {
    console.error('Stats error:', err)
  } finally {
    loading.value = false
  }
})
</script>

<style scoped>
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: rgba(255, 255, 255, 0.05); }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(255, 255, 255, 0.2); border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(255, 255, 255, 0.4); }
</style>