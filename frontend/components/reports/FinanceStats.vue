<template>
  <div class="space-y-6">
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
      
      <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 relative overflow-hidden group hover:border-indigo-500/50 transition">
        <div class="relative z-10">
          <div class="text-slate-400 text-sm font-medium mb-1">Toplam Kullanıcı Bakiyesi</div>
          <div class="text-3xl font-bold text-white tracking-tight">
            {{ formatCurrency(stats?.total_balance || 0) }}
          </div>
          <p class="text-xs text-slate-500 mt-2">Kullanıcıların hesabındaki toplam para</p>
        </div>
        <i class="i-lucide-wallet absolute -right-4 -bottom-4 text-8xl text-indigo-500/10 group-hover:text-indigo-500/20 transition"></i>
      </div>

      <div class="bg-slate-900/50 border border-slate-800 rounded-2xl p-6 relative overflow-hidden group hover:border-emerald-500/50 transition">
        <div class="relative z-10">
          <div class="text-slate-400 text-sm font-medium mb-1">Toplam Yüklenen Tutar</div>
          <div class="text-3xl font-bold text-emerald-400 tracking-tight">
            {{ formatCurrency(stats?.total_deposits || 0) }}
          </div>
          <p class="text-xs text-slate-500 mt-2">Sisteme giren toplam bakiye</p>
        </div>
        <i class="i-lucide-trending-up absolute -right-4 -bottom-4 text-8xl text-emerald-500/10 group-hover:text-emerald-500/20 transition"></i>
      </div>

      <div class="bg-gradient-to-br from-indigo-900/20 to-slate-900/50 border border-indigo-500/20 rounded-2xl p-6 flex flex-col justify-center items-center text-center">
        <div class="bg-indigo-500/20 p-3 rounded-full mb-3">
           <i class="i-lucide-banknote text-indigo-400 text-xl"></i>
        </div>
        <h3 class="text-white font-semibold">Bakiye Yönetimi</h3>
        <p class="text-xs text-slate-400 mt-1">Kullanıcılara bakiye eklemek veya çıkarmak için kullanıcı listesini kullanın.</p>
        <NuxtLink to="/admin/users" class="mt-3 text-xs bg-indigo-600 hover:bg-indigo-500 text-white px-3 py-1.5 rounded transition">
          Kullanıcılara Git
        </NuxtLink>
      </div>
    </div>

    <div class="bg-slate-900/50 border border-slate-800 rounded-2xl overflow-hidden">
      <div class="px-6 py-4 border-b border-slate-800 flex justify-between items-center">
        <h3 class="font-bold text-white flex items-center gap-2">
          <i class="i-lucide-history text-slate-400"></i> Son Finansal Hareketler
        </h3>
      </div>
      
      <div v-if="loading" class="p-8 text-center text-slate-500">
        Veriler yükleniyor...
      </div>

      <div v-else-if="stats?.recent_transactions?.length > 0" class="overflow-x-auto">
        <table class="w-full text-sm text-left text-slate-400">
          <thead class="text-xs text-slate-500 uppercase bg-slate-950/50">
            <tr>
              <th class="px-6 py-3">Kullanıcı</th>
              <th class="px-6 py-3">İşlem Tipi</th>
              <th class="px-6 py-3">Tutar</th>
              <th class="px-6 py-3">Tarih</th>
            </tr>
          </thead>
          <tbody class="divide-y divide-slate-800">
            <tr v-for="tx in stats.recent_transactions" :key="tx.id" class="hover:bg-slate-800/30 transition">
              <td class="px-6 py-4 font-medium text-white">
                {{ tx.user?.name }} {{ tx.user?.surname }}
                <div class="text-[10px] text-slate-500">{{ tx.user?.unit }}</div>
              </td>
              <td class="px-6 py-4">
                <span 
                  :class="tx.type === 'deposit' ? 'bg-emerald-500/10 text-emerald-400' : 'bg-red-500/10 text-red-400'"
                  class="px-2 py-1 rounded text-xs font-bold capitalize"
                >
                  {{ tx.type === 'deposit' ? 'Para Yükleme' : 'Harcama' }}
                </span>
              </td>
              <td class="px-6 py-4 font-mono text-white">
                {{ tx.type === 'deposit' ? '+' : '-' }}{{ formatCurrency(tx.amount) }}
              </td>
              <td class="px-6 py-4">
                {{ new Date(tx.created_at).toLocaleString('tr-TR') }}
              </td>
            </tr>
          </tbody>
        </table>
      </div>

      <div v-else class="p-8 text-center text-slate-500">
        Henüz kayıtlı işlem yok.
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'

const stats = ref(null)
const loading = ref(true)

// Para formatlama
const formatCurrency = (value) => {
  return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(value)
}

const fetchStats = async () => {
  try {
    const data = await $fetch('/api/admin/finance-stats')
    stats.value = data
  } catch (error) {
    console.error('Finans verileri çekilemedi:', error)
  } finally {
    loading.value = false
  }
}

onMounted(fetchStats)
</script>