<template>
  <div class="max-w-[1600px] mx-auto animate-fade-in">
    
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-10">
      <div>
        <h1 class="text-3xl font-bold text-white flex items-center gap-3">
          <span class="p-2 bg-indigo-500/10 rounded-xl border border-indigo-500/20 text-indigo-400">
            <ChartBarIcon class="w-8 h-8" />
          </span>
          Raporlar & Analiz
        </h1>
        <p class="text-slate-400 mt-2 ml-1">Sistem verilerini ve finansal durumu detaylı inceleyin.</p>
      </div>
      <NuxtLink 
        to="/admin" 
        class="px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm hover:bg-white/10 transition flex items-center gap-2 text-slate-300"
      >
        <ArrowLeftIcon class="w-4 h-4" /> Menüye Dön
      </NuxtLink>
    </div>

    <!-- SEKME (TAB) BUTONLARI -->
    <div class="mb-8 overflow-x-auto pb-2">
      <div class="flex space-x-2 bg-[#121212]/80 backdrop-blur-md p-1.5 rounded-2xl border border-white/10 w-max min-w-[320px]">
        
        <button 
          v-for="tab in tabs" 
          :key="tab.id"
          @click="activeTab = tab.id"
          class="px-6 py-2.5 rounded-xl text-sm font-bold transition-all flex items-center gap-2 whitespace-nowrap relative overflow-hidden"
          :class="activeTab === tab.id 
            ? 'bg-indigo-600 text-white shadow-lg shadow-indigo-500/20' 
            : 'text-slate-400 hover:text-white hover:bg-white/5'"
        >
          <component :is="tab.icon" class="w-4 h-4" />
          {{ tab.label }}
        </button>

      </div>
    </div>

    <!-- YÜKLENİYOR -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-40">
      <div class="w-12 h-12 border-4 border-indigo-500/30 border-t-indigo-500 rounded-full animate-spin mb-4"></div>
      <p class="text-slate-500 text-sm font-medium animate-pulse">Veriler analiz ediliyor...</p>
    </div>

    <!-- İÇERİK ALANI -->
    <div v-else class="min-h-[400px]">
      
      <!-- 1. SEKME: GENEL BAKIŞ -->
      <transition name="fade" mode="out-in">
        <div v-if="activeTab === 'genel'" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
          
          <!-- İstatistik Kartları -->
          <div class="stat-card group">
            <div class="flex justify-between items-start mb-4">
              <div class="text-slate-400 text-xs font-bold uppercase tracking-wider">Toplam Kullanıcı</div>
              <div class="p-2 bg-blue-500/10 rounded-lg text-blue-400 group-hover:bg-blue-500/20 transition">
                <UsersIcon class="w-5 h-5" />
              </div>
            </div>
            <div class="text-3xl font-black text-white mb-2">{{ stats?.userStats?.total || 0 }}</div>
            <div class="h-1.5 w-full bg-slate-800 rounded-full overflow-hidden">
              <div class="h-full bg-gradient-to-r from-blue-600 to-cyan-500" style="width: 100%"></div>
            </div>
          </div>

          <div class="stat-card group border-orange-500/20 bg-orange-500/5">
            <div class="flex justify-between items-start mb-4">
              <div class="text-orange-300/80 text-xs font-bold uppercase tracking-wider">Onay Bekleyen</div>
              <div class="p-2 bg-orange-500/10 rounded-lg text-orange-400 group-hover:bg-orange-500/20 transition animate-pulse">
                <UserGroupIcon class="w-5 h-5" />
              </div>
            </div>
            <div class="text-3xl font-black text-orange-400 mb-2">{{ stats?.userStats?.pending || 0 }}</div>
            <div class="text-[10px] text-orange-300/60 font-mono uppercase">Aksiyon Gerekiyor</div>
          </div>

          <div class="stat-card group">
            <div class="flex justify-between items-start mb-4">
              <div class="text-slate-400 text-xs font-bold uppercase tracking-wider">Aktif Hesap</div>
              <div class="p-2 bg-emerald-500/10 rounded-lg text-emerald-400 group-hover:bg-emerald-500/20 transition">
                <CheckBadgeIcon class="w-5 h-5" />
              </div>
            </div>
            <div class="text-3xl font-black text-white mb-2">{{ stats?.userStats?.approved || 0 }}</div>
            <div class="h-1.5 w-full bg-slate-800 rounded-full overflow-hidden">
               <div class="h-full bg-gradient-to-r from-emerald-600 to-teal-500" :style="`width: ${(stats?.userStats?.approved / stats?.userStats?.total) * 100}%`"></div>
            </div>
          </div>

          <div class="stat-card group">
            <div class="flex justify-between items-start mb-4">
              <div class="text-slate-400 text-xs font-bold uppercase tracking-wider">Toplam Menü</div>
              <div class="p-2 bg-purple-500/10 rounded-lg text-purple-400 group-hover:bg-purple-500/20 transition">
                <ClipboardDocumentListIcon class="w-5 h-5" />
              </div>
            </div>
            <div class="text-3xl font-black text-white mb-2">{{ stats?.menuStats?.total || 0 }}</div>
            <div class="text-[10px] text-slate-500 font-mono uppercase">Sistem Kaydı</div>
          </div>

          <!-- Bugünün Menüsü Kartı -->
          <div class="col-span-1 sm:col-span-2 lg:col-span-2 bg-[#121212]/60 border border-white/5 rounded-3xl p-6 hover:border-indigo-500/20 transition duration-300">
             <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-4 flex items-center gap-2">
               <CalendarIcon class="w-4 h-4 text-indigo-400" /> Bugünün Menüsü
             </h3>
             
             <div v-if="stats?.menuStats?.today" class="p-5 bg-indigo-500/5 border border-indigo-500/10 rounded-2xl relative overflow-hidden">
                <div class="absolute top-0 right-0 w-20 h-20 bg-indigo-500/10 rounded-full blur-2xl -mr-10 -mt-10"></div>
                <ul class="space-y-3 relative z-10">
                  <li v-for="(item, i) in stats.menuStats.today.items" :key="i" class="flex items-center gap-3 text-slate-200 text-sm">
                    <span class="w-2 h-2 bg-indigo-400 rounded-full shadow-[0_0_10px_rgba(129,140,248,0.5)]"></span>
                    <span class="font-medium">{{ item.name }}</span>
                    <span class="text-[10px] text-indigo-300 bg-indigo-500/10 px-2 py-0.5 rounded ml-auto font-mono">{{ item.cal }} cal</span>
                  </li>
                </ul>
             </div>
             <div v-else class="flex flex-col items-center justify-center py-8 text-slate-500 bg-white/5 rounded-2xl border border-dashed border-white/10">
               <ClipboardDocumentListIcon class="w-8 h-8 mb-2 opacity-20" />
               <span class="text-xs">Bugün için menü girişi yapılmadı.</span>
             </div>
          </div>

          <!-- Rapor İndir Kartı -->
          <div class="col-span-1 sm:col-span-2 lg:col-span-2 bg-gradient-to-br from-[#1a1a1a] to-[#121212] border border-white/5 rounded-3xl p-6 flex flex-col justify-center items-center text-center relative overflow-hidden group">
             <div class="absolute inset-0 bg-gradient-to-r from-indigo-600/10 to-purple-600/10 opacity-0 group-hover:opacity-100 transition duration-500"></div>
             
             <div class="relative z-10">
               <div class="w-12 h-12 bg-white/5 rounded-full flex items-center justify-center mx-auto mb-3 border border-white/10 group-hover:scale-110 transition-transform">
                 <DocumentArrowDownIcon class="w-6 h-6 text-slate-300" />
               </div>
               <h3 class="text-lg font-bold text-white mb-1">Verileri Dışa Aktar</h3>
               <p class="text-xs text-slate-400 mb-5 max-w-xs mx-auto">Tüm sistem verilerini Excel veya PDF formatında raporlayın.</p>
               <button class="bg-white text-black hover:bg-slate-200 px-6 py-2.5 rounded-xl text-sm font-bold transition shadow-lg shadow-white/10">
                 Rapor Oluştur
               </button>
             </div>
          </div>

        </div>
      </transition>

      <!-- 2. SEKME: BİRİM RAPORLARI -->
      <transition name="fade" mode="out-in">
        <div v-if="activeTab === 'birimler'" class="space-y-6">
          
          <!-- Banner -->
          <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 bg-purple-900/10 border border-purple-500/20 p-6 rounded-3xl relative overflow-hidden">
            <div class="absolute inset-0 bg-gradient-to-r from-purple-600/10 via-transparent to-transparent"></div>
            <div class="relative z-10">
               <h3 class="text-xl font-bold text-white flex items-center gap-3">
                 <div class="p-2 bg-purple-500/20 rounded-lg text-purple-300"><BuildingOffice2Icon class="w-6 h-6" /></div>
                 Birim Yönetimi
               </h3>
               <p class="text-sm text-purple-200/60 mt-2 max-w-lg">Birimlere özel fiyatlandırma yapabilir, yeni departmanlar ekleyebilir veya mevcut yapılandırmayı düzenleyebilirsiniz.</p>
            </div>
            <NuxtLink to="/admin/units" class="relative z-10 bg-purple-600 hover:bg-purple-500 text-white px-6 py-3 rounded-xl text-sm font-bold transition shadow-lg shadow-purple-900/30 flex items-center gap-2">
              Düzenle <ArrowRightIcon class="w-4 h-4" />
            </NuxtLink>
          </div>

          <!-- Tablo -->
          <div class="bg-[#121212]/60 border border-white/5 rounded-3xl overflow-hidden backdrop-blur-md">
            <table class="w-full text-left text-sm">
              <thead>
                <tr class="border-b border-white/5 bg-white/5">
                  <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Birim Adı</th>
                  <th class="px-6 py-4 text-center text-xs font-bold text-slate-400 uppercase tracking-wider">Personel</th>
                  <th class="px-6 py-4 text-right text-xs font-bold text-slate-400 uppercase tracking-wider">Toplam Bakiye</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-white/5">
                <tr v-for="(unit, index) in unitStats" :key="index" class="hover:bg-white/5 transition group">
                  <td class="px-6 py-4 font-medium text-white group-hover:text-indigo-300 transition-colors">{{ unit.unit }}</td>
                  <td class="px-6 py-4 text-center">
                    <span class="bg-[#1a1a1a] border border-white/10 text-slate-300 px-3 py-1 rounded-lg text-xs font-bold font-mono">
                      {{ unit.user_count }}
                    </span>
                  </td>
                  <td class="px-6 py-4 text-right font-mono text-emerald-400 font-bold">
                     {{ formatCurrency(unit.total_balance) }}
                  </td>
                </tr>
                <tr v-if="unitStats.length === 0">
                  <td colspan="3" class="px-6 py-12 text-center text-slate-500">Veri bulunamadı.</td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
      </transition>

      <!-- 3. SEKME: MENÜ ANALİZİ -->
      <transition name="fade" mode="out-in">
        <div v-if="activeTab === 'yemekler'" class="grid grid-cols-1 lg:grid-cols-2 gap-6">
          
          <!-- Popüler Yemekler -->
          <div class="bg-[#121212]/60 border border-white/5 rounded-3xl p-6 backdrop-blur-md h-full">
            <h3 class="text-sm font-bold text-slate-400 uppercase tracking-wider mb-6 flex items-center gap-2">
              <FireIcon class="w-4 h-4 text-orange-500" /> Popüler Lezzetler
            </h3>
            <div class="space-y-3">
              <div v-for="(item, index) in stats?.menuStats?.topItems" :key="index" class="flex items-center justify-between bg-white/5 p-4 rounded-2xl border border-white/5 hover:border-orange-500/30 transition group">
                <div class="flex items-center gap-4">
                  <span class="w-8 h-8 flex items-center justify-center bg-orange-500/10 text-orange-400 rounded-lg font-black text-sm group-hover:scale-110 transition-transform">
                    {{ index + 1 }}
                  </span>
                  <span class="text-slate-200 font-medium">{{ item.name }}</span>
                </div>
                <span class="text-xs text-slate-500 bg-black/20 px-2 py-1 rounded">{{ item.count }} kez</span>
              </div>
              <div v-if="!stats?.menuStats?.topItems?.length" class="text-slate-500 text-center py-10">Yeterli veri yok.</div>
            </div>
          </div>

          <!-- İstatistik Kutuları -->
          <div class="space-y-6">
             <div class="bg-[#121212]/60 border border-white/5 rounded-3xl p-8 text-center backdrop-blur-md relative overflow-hidden group">
                <div class="absolute top-0 right-0 w-40 h-40 bg-teal-500/10 rounded-full blur-[80px] -mr-10 -mt-10 transition group-hover:bg-teal-500/20"></div>
                <div class="text-5xl font-black text-white mb-2 tracking-tighter">{{ stats?.menuStats?.total || 0 }}</div>
                <div class="text-sm font-bold text-teal-400 uppercase tracking-wide">Toplam Kayıtlı Menü</div>
             </div>

             <div class="bg-[#121212]/60 border border-white/5 rounded-3xl p-8 text-center backdrop-blur-md relative overflow-hidden group">
                <div class="absolute bottom-0 left-0 w-40 h-40 bg-indigo-500/10 rounded-full blur-[80px] -ml-10 -mb-10 transition group-hover:bg-indigo-500/20"></div>
                <div class="text-5xl font-black text-white mb-2 tracking-tighter">30</div>
                <div class="text-sm font-bold text-indigo-400 uppercase tracking-wide">Son 30 Günlük Veri</div>
             </div>
          </div>

        </div>
      </transition>

      <!-- 4. SEKME: FİNANS -->
      <transition name="fade" mode="out-in">
        <div v-if="activeTab === 'finans'">
           <FinanceStats />
        </div>
      </transition>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import FinanceStats from '../components/reports/FinanceStats.vue'
import { 
  ChartBarIcon, ArrowLeftIcon, HomeIcon, BuildingOffice2Icon, // HomeIcon EKLENDİ (Squares2x2Icon yerine)
  SparklesIcon, BanknotesIcon, UsersIcon, UserGroupIcon, CheckBadgeIcon,
  ClipboardDocumentListIcon, CalendarIcon, DocumentArrowDownIcon, ArrowRightIcon, FireIcon
} from '@heroicons/vue/24/outline'

definePageMeta({ layout: 'admin' })

const activeTab = ref('genel') 
const stats = ref(null)
const unitStats = ref([])
const loading = ref(true)

// Sekme Konfigürasyonu
const tabs = [
  { id: 'genel', label: 'Genel Bakış', icon: HomeIcon }, // İkon HomeIcon olarak değiştirildi
  { id: 'birimler', label: 'Birim Raporları', icon: BuildingOffice2Icon },
  { id: 'yemekler', label: 'Menü Analizi', icon: SparklesIcon },
  { id: 'finans', label: 'Bakiye & Finans', icon: BanknotesIcon },
]

const formatCurrency = (val) => new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(val || 0)

const fetchAllData = async () => {
  loading.value = true
  try {
    const [dashboardData, unitData] = await Promise.all([
      $fetch('/api/admin/dashboard-stats').catch(() => null),
      $fetch('/api/admin/unit-stats').catch(() => [])
    ])

    stats.value = dashboardData
    unitStats.value = unitData || []

  } catch (error) {
    console.error('Veri çekme hatası:', error)
  } finally {
    loading.value = false
  }
}

onMounted(fetchAllData)
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active { transition: opacity 0.2s ease; }
.fade-enter-from,
.fade-leave-to { opacity: 0; }

.stat-card {
  @apply bg-[#121212]/60 border border-white/5 rounded-3xl p-6 hover:border-white/10 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 backdrop-blur-sm relative overflow-hidden;
}

.animate-fade-in { animation: fadeIn 0.5s ease-out forwards; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>