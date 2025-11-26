<template>
  <div class="max-w-[1600px] mx-auto animate-fade-in">
    
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-6 mb-8">
      <div>
        <h1 class="text-3xl font-bold text-white flex items-center gap-3">
          <span class="p-2 bg-blue-500/10 rounded-xl border border-blue-500/20 text-blue-400">
            <DocumentTextIcon class="w-8 h-8" />
          </span>
          Sistem Logları
        </h1>
        <p class="text-slate-400 mt-2 ml-1">Kullanıcı ve yönetici hareketlerinin iz kayıtları.</p>
      </div>
      <NuxtLink to="/admin/dashboard" class="px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm hover:bg-white/10 transition flex items-center gap-2 text-slate-300">
        <ArrowLeftIcon class="w-4 h-4" /> Raporlara Dön
      </NuxtLink>
    </div>

    <!-- LOG LİSTESİ -->
    <div class="bg-[#121212]/60 border border-white/5 rounded-3xl overflow-hidden backdrop-blur-md shadow-2xl">
      
      <!-- Yükleniyor ve Boş Durumlar -->
      <div v-if="loading" class="text-center py-20 text-slate-500">
        <div class="w-10 h-10 border-4 border-blue-500/30 border-t-blue-500 rounded-full animate-spin mx-auto mb-4"></div>
        Kayıtlar getiriliyor...
      </div>
      <div v-else-if="logs.length === 0" class="text-center py-20 text-slate-500">
        <ClipboardDocumentListIcon class="w-12 h-12 mb-3 opacity-20" />
        Kayıtlı log bulunamadı.
      </div>
      <div v-else class="overflow-x-auto">
        <table class="w-full text-left text-sm">
          <thead>
            <tr class="border-b border-white/5 bg-white/5">
              <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Tarih</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">İşlem Yapan</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Aksiyon</th>
              <th class="px-6 py-4 text-xs font-bold text-slate-400 uppercase tracking-wider">Detay</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-white/5">
            <tr v-for="log in logs" :key="log.id" class="hover:bg-white/5 transition group">
              <td class="px-6 py-4 font-mono text-xs text-slate-500">
                {{ formatDate(log.created_at) }}<br>
                <span class="text-white/30">{{ formatTime(log.created_at) }}</span>
              </td>
              <td class="px-6 py-4 font-bold text-white">
                {{ log.user_name || 'Sistem' }}
                <div class="text-[10px] text-slate-600 font-mono">{{ log.ip_address }}</div>
              </td>
              <td class="px-6 py-4">
                <span class="px-2 py-1 rounded text-xs font-bold border"
                  :class="getActionClass(log.action)">
                  {{ log.action }}
                </span>
              </td>
              <td class="px-6 py-4 text-slate-300">{{ log.details }}</td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { DocumentTextIcon, ArrowLeftIcon, ClipboardDocumentListIcon } from '@heroicons/vue/24/outline'

definePageMeta({ layout: 'admin' })
const logs = ref([])
const loading = ref(true)

const fetchLogs = async () => {
  try {
    // Logları çekme rotası
    const data = await $fetch('/api/admin/logs')
    // Logların en yeni olandan en eskiye sıralanmış gelmesini bekliyoruz
    logs.value = data?.data || []
  } catch(e) { 
    console.error("Log hatası:", e) 
  }
  finally { loading.value = false }
}

const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('tr-TR', { day: '2-digit', month: 'short' })
}

const formatTime = (dateStr) => {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' })
}

const getActionClass = (action) => {
  if (action && action.includes('Yemek Dağıtımı')) return 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30'
  if (action && action.includes('fiyatını değiştirdi')) return 'bg-purple-500/10 text-purple-400 border-purple-500/30'
  if (action && action.includes('Onaylama')) return 'bg-cyan-500/10 text-cyan-400 border-cyan-500/30'
  return 'bg-slate-700/50 text-slate-400 border-slate-700'
}

onMounted(fetchLogs)
</script>

<style scoped>
.animate-fade-in { animation: fadeIn 0.5s ease-out forwards; }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
</style>