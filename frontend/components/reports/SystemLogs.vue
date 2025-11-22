<template>
  <div class="space-y-6">
    <!-- BAŞLIK VE FİLTRELER -->
    <div class="flex flex-col md:flex-row justify-between items-end md:items-center gap-4">
      <div>
        <h2 class="text-3xl font-bold text-white flex items-center gap-3">
          <span class="p-2 rounded-xl bg-indigo-500/20 text-indigo-400 border border-indigo-500/30">
            <i class="i-lucide-activity"></i>
          </span>
          Sistem Logları
        </h2>
        <p class="text-white/50 mt-1 ml-1 text-sm">
          Sistemdeki tüm hareketleri, hataları ve kullanıcı işlemlerini buradan takip edebilirsiniz.
        </p>
      </div>

      <!-- Filtreler -->
      <div class="flex gap-3 w-full md:w-auto">
        <!-- Log Tipi Seçimi -->
        <select 
          v-model="filters.type" 
          @change="fetchLogs(1)"
          class="bg-[#0f172a] border border-white/10 text-white text-sm rounded-xl px-4 py-2.5 focus:outline-none focus:border-indigo-500/50"
        >
          <option value="">Tümü</option>
          <option value="info">Bilgi (Info)</option>
          <option value="warning">Uyarı (Warning)</option>
          <option value="error">Hata (Error)</option>
          <option value="critical">Kritik (Critical)</option>
        </select>

        <!-- Arama -->
        <div class="relative flex-1 md:w-64">
          <span class="absolute left-3 top-1/2 -translate-y-1/2 text-white/30">
            <i class="i-lucide-search"></i>
          </span>
          <input 
            v-model="filters.search"
            @input="debounceSearch"
            type="text" 
            placeholder="İşlem veya açıklama ara..." 
            class="w-full bg-[#0f172a] border border-white/10 text-white text-sm rounded-xl pl-10 pr-4 py-2.5 focus:outline-none focus:border-indigo-500/50 placeholder-white/30"
          >
        </div>
      </div>
    </div>

    <!-- 🧾 LOG TABLOSU -->
    <div class="bg-white/5 backdrop-blur-xl border border-white/10 rounded-2xl overflow-hidden shadow-2xl relative min-h-[400px]">
      
      <!-- Yükleniyor Ekranı -->
      <div v-if="loading" class="absolute inset-0 z-10 bg-[#0f172a]/80 backdrop-blur-sm flex items-center justify-center">
        <div class="flex flex-col items-center gap-3">
          <div class="w-10 h-10 border-4 border-indigo-500 border-t-transparent rounded-full animate-spin"></div>
          <span class="text-indigo-300 font-medium">Loglar taranıyor...</span>
        </div>
      </div>

      <div class="overflow-x-auto">
        <table class="w-full text-left border-collapse">
          <thead>
            <tr class="bg-white/5 border-b border-white/5 text-white/40 text-xs uppercase tracking-wider font-semibold">
              <th class="p-4 w-12">#</th>
              <th class="p-4">Tip / İşlem</th>
              <th class="p-4">Detay</th>
              <th class="p-4">Kullanıcı</th>
              <th class="p-4">IP / Cihaz</th>
              <th class="p-4 text-right">Tarih</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/5 text-sm">
            <tr 
              v-for="log in logs" 
              :key="log.id" 
              class="hover:bg-white/5 transition duration-150 group"
            >
              <!-- İkon -->
              <td class="p-4">
                <div class="w-8 h-8 rounded-lg flex items-center justify-center border shadow-lg" :class="getTypeStyle(log.type)">
                  <i :class="getTypeIcon(log.type)"></i>
                </div>
              </td>

              <!-- İşlem -->
              <td class="p-4">
                <div class="font-bold text-white">{{ log.action }}</div>
                <div class="text-xs uppercase font-mono mt-0.5" :class="getTypeTextColor(log.type)">{{ log.type }}</div>
              </td>

              <!-- Detay -->
              <td class="p-4">
                <p class="text-white/80 line-clamp-2" :title="log.description">{{ log.description }}</p>
              </td>

              <!-- Kullanıcı -->
              <td class="p-4">
                <div v-if="log.user" class="flex items-center gap-2">
                  <div class="w-6 h-6 rounded-full bg-gradient-to-tr from-gray-600 to-gray-500 flex items-center justify-center text-[10px] text-white font-bold">
                    {{ log.user.name?.[0] }}{{ log.user.surname?.[0] }}
                  </div>
                  <span class="text-white/70">{{ log.user.name }} {{ log.user.surname }}</span>
                </div>
                <div v-else class="text-white/30 italic flex items-center gap-1">
                  <i class="i-lucide-server"></i> Sistem
                </div>
              </td>

              <!-- IP -->
              <td class="p-4">
                <div class="font-mono text-xs text-sky-300">{{ log.ip_address || 'Unknown' }}</div>
                <div class="text-[10px] text-white/30 truncate w-32" :title="log.user_agent">{{ formatUserAgent(log.user_agent) }}</div>
              </td>

              <!-- Tarih -->
              <td class="p-4 text-right text-white/50 tabular-nums">
                {{ formatDate(log.created_at) }}
                <div class="text-[10px] text-white/30">{{ formatTime(log.created_at) }}</div>
              </td>
            </tr>

            <!-- Veri Yoksa -->
            <tr v-if="!loading && logs.length === 0">
              <td colspan="6" class="p-12 text-center">
                <div class="flex flex-col items-center gap-2 opacity-50">
                  <i class="i-lucide-file-x text-4xl text-white/30"></i>
                  <span class="text-white/60">Kayıt bulunamadı.</span>
                </div>
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <!-- Sayfalama (Pagination) -->
      <div v-if="pagination.total > pagination.per_page" class="border-t border-white/10 p-4 flex justify-between items-center">
        <span class="text-xs text-white/40">
          Toplam <strong class="text-white">{{ pagination.total }}</strong> kayıttan 
          <strong>{{ pagination.from }}-{{ pagination.to }}</strong> arası gösteriliyor
        </span>
        
        <div class="flex gap-2">
          <button 
            @click="changePage(pagination.current_page - 1)"
            :disabled="pagination.current_page === 1"
            class="px-3 py-1.5 rounded-lg border border-white/10 text-white text-xs hover:bg-white/10 disabled:opacity-30 disabled:cursor-not-allowed transition"
          >
            Önceki
          </button>
          
          <button 
            @click="changePage(pagination.current_page + 1)"
            :disabled="pagination.current_page === pagination.last_page"
            class="px-3 py-1.5 rounded-lg border border-white/10 text-white text-xs hover:bg-white/10 disabled:opacity-30 disabled:cursor-not-allowed transition"
          >
            Sonraki
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted } from 'vue'

const loading = ref(false)
const logs = ref([])
const filters = reactive({
  search: '',
  type: ''
})

const pagination = reactive({
  current_page: 1,
  last_page: 1,
  total: 0,
  per_page: 20,
  from: 0,
  to: 0
})

let searchTimeout = null

// Verileri Çek
const fetchLogs = async (page = 1) => {
  loading.value = true
  try {
    const res = await $fetch('/api/admin/logs', {
      params: {
        page: page,
        search: filters.search,
        type: filters.type
      }
    })

    // Laravel Pagination Yapısına Uygun Atama
    logs.value = res.data
    pagination.current_page = res.current_page
    pagination.last_page = res.last_page
    pagination.total = res.total
    pagination.per_page = res.per_page
    pagination.from = res.from
    pagination.to = res.to

  } catch (err) {
    console.error('Log hatası:', err)
  } finally {
    loading.value = false
  }
}

// Arama Geciktirme (Debounce)
const debounceSearch = () => {
  if (searchTimeout) clearTimeout(searchTimeout)
  searchTimeout = setTimeout(() => {
    fetchLogs(1)
  }, 500)
}

const changePage = (page) => {
  if (page >= 1 && page <= pagination.last_page) {
    fetchLogs(page)
  }
}

// 🎨 Stil Yardımcıları
const getTypeStyle = (type) => {
  switch (type) {
    case 'error': return 'bg-red-500/10 border-red-500/30 text-red-400'
    case 'warning': return 'bg-amber-500/10 border-amber-500/30 text-amber-400'
    case 'critical': return 'bg-rose-600/20 border-rose-500/50 text-rose-500 animate-pulse'
    default: return 'bg-sky-500/10 border-sky-500/30 text-sky-400' // info
  }
}

const getTypeTextColor = (type) => {
  switch (type) {
    case 'error': return 'text-red-400'
    case 'warning': return 'text-amber-400'
    case 'critical': return 'text-rose-500'
    default: return 'text-sky-400'
  }
}

const getTypeIcon = (type) => {
  switch (type) {
    case 'error': return 'i-lucide-alert-circle'
    case 'warning': return 'i-lucide-alert-triangle'
    case 'critical': return 'i-lucide-siren'
    default: return 'i-lucide-info'
  }
}

const formatUserAgent = (ua) => {
  if (!ua) return '-'
  if (ua.includes('Windows')) return 'Windows PC'
  if (ua.includes('Macintosh')) return 'Mac'
  if (ua.includes('Android')) return 'Android'
  if (ua.includes('iPhone')) return 'iPhone'
  return 'Diğer Cihaz'
}

const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('tr-TR', { day: '2-digit', month: 'short', year: 'numeric' })
}

const formatTime = (dateStr) => {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' })
}

onMounted(() => {
  fetchLogs()
})
</script>