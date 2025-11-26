<template>
  <div class="max-w-[1400px] mx-auto animate-fade-in">
    
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
      <div>
        <h1 class="text-3xl font-bold text-white flex items-center gap-3">
          <span class="p-2 bg-purple-500/10 rounded-xl border border-purple-500/20 text-purple-400">
            <UserIcon class="w-8 h-8" />
          </span>
          {{ maskName(userDetails.user?.name) }} {{ maskName(userDetails.user?.surname) }}
        </h1>
        <p class="text-slate-400 mt-2 ml-1">Kullanıcının detaylı bilgileri ve hareket geçmişi.</p>
      </div>
      <NuxtLink to="/admin/users" class="px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm hover:bg-white/10 transition flex items-center gap-2 text-slate-300">
        <ArrowLeftIcon class="w-4 h-4" /> Kullanıcı Listesi
      </NuxtLink>
    </div>

    <div v-if="loading" class="text-center py-20 text-slate-500">
      <div class="w-10 h-10 border-4 border-purple-500/30 border-t-purple-500 rounded-full animate-spin mx-auto mb-4"></div>
      Kullanıcı verileri yükleniyor...
    </div>

    <!-- İÇERİK: 3 Sütunlu Yapı -->
    <div v-else class="grid grid-cols-1 md:grid-cols-3 gap-8">
      
      <!-- SOL SÜTUN: PROFİL VE BAKIYE -->
      <div class="md:col-span-1 space-y-8">
        
        <!-- Genel Bilgiler -->
        <div class="bg-[#121212]/80 border border-white/5 rounded-3xl p-6 backdrop-blur-xl shadow-2xl">
          <h3 class="text-lg font-bold text-white mb-4 border-b border-white/5 pb-3">Profil Özeti</h3>
          <div class="space-y-3 text-sm text-slate-300">
            <p><strong>Birim:</strong> <span class="text-purple-300 font-medium">{{ userDetails.user?.unit || '-' }}</span></p>
            <p><strong>Telefon:</strong> {{ userDetails.user?.phone || '-' }}</p>
            <p><strong>Email:</strong> <span class="text-sm font-mono text-slate-500">{{ userDetails.user?.email || '-' }}</span></p>
            <p><strong>Kayıt Tarihi:</strong> {{ formatDate(userDetails.user?.created_at) }}</p>
            <p><strong>Yemek Ücreti:</strong> <span class="text-emerald-400 font-mono">{{ userDetails.user?.meal_price || 0 }} ₺</span></p>
          </div>
        </div>

        <!-- Bakiye Kartı -->
        <div class="bg-gradient-to-br from-purple-800/20 to-indigo-800/20 border border-purple-500/30 rounded-3xl p-6 shadow-xl relative overflow-hidden">
          <h3 class="text-sm font-bold text-purple-300 uppercase tracking-wider">Mevcut Bakiye</h3>
          <div class="text-5xl font-black text-white mt-2">
            {{ formatCurrency(userDetails.user?.balance) }}
          </div>
          <div class="text-xs text-slate-400 mt-3">
            <p>Son Harcama: {{ formatCurrency(getLastTransaction(userDetails.transactions, 'debit')?.amount) || '-' }}</p>
            <p>Son Yükleme: {{ formatCurrency(getLastTransaction(userDetails.transactions, 'deposit')?.amount) || '-' }}</p>
          </div>
          <WalletIcon class="absolute right-4 top-4 w-16 h-16 text-purple-500/20" />
        </div>

      </div>

      <!-- SAĞ SÜTUN: GEÇMİŞ HAREKETLER (ORDERS & LOGS) -->
      <div class="md:col-span-2 space-y-8">
        
        <!-- 1. SİPARİŞ GEÇMİŞİ -->
        <div class="bg-[#121212]/80 border border-white/5 rounded-3xl p-6 backdrop-blur-xl shadow-2xl">
          <h3 class="text-lg font-bold text-white mb-4 border-b border-white/5 pb-3 flex justify-between items-center">
            <span><ClipboardDocumentListIcon class="w-5 h-5 inline mr-2 text-indigo-400" /> Son Siparişler</span>
            <span class="text-xs text-slate-500 font-mono">{{ userDetails.orders.length }} Kayıt</span>
          </h3>
          <ul v-if="userDetails.orders.length" class="space-y-3 max-h-80 overflow-y-auto custom-scrollbar">
            <li v-for="order in userDetails.orders" :key="order._id" class="flex justify-between items-center p-3 bg-white/5 rounded-xl border border-white/5 text-sm">
              <span class="font-medium text-slate-300">{{ formatDate(order.date) }}</span>
              <span class="text-emerald-400 font-bold font-mono">{{ formatCurrency(order.total) }}</span>
            </li>
          </ul>
          <div v-else class="text-center py-6 text-slate-500">Hiç sipariş kaydı yok.</div>
        </div>
        
        <!-- 2. LOG HAREKETLERİ -->
        <div class="bg-[#121212]/80 border border-white/5 rounded-3xl p-6 backdrop-blur-xl shadow-2xl">
          <h3 class="text-lg font-bold text-white mb-4 border-b border-white/5 pb-3 flex justify-between items-center">
            <span><DocumentTextIcon class="w-5 h-5 inline mr-2 text-cyan-400" /> Son Sistem Hareketleri</span>
            <span class="text-xs text-slate-500 font-mono">{{ userDetails.logs.length }} Kayıt</span>
          </h3>
          <ul v-if="userDetails.logs.length" class="space-y-3 max-h-80 overflow-y-auto custom-scrollbar">
            <li v-for="log in userDetails.logs" :key="log._id" class="p-3 bg-white/5 rounded-xl border border-white/5 text-xs">
              <div class="flex justify-between items-start">
                <span class="font-bold" :class="log.action.includes('Yemek Dağıtımı') ? 'text-emerald-300' : 'text-cyan-300'">
                  {{ log.action }}
                </span>
                <span class="text-slate-600 font-mono">{{ formatTime(log.created_at) }}</span>
              </div>
              <p class="text-slate-400 mt-1">{{ log.details }}</p>
            </li>
          </ul>
          <div v-else class="text-center py-6 text-slate-500">Kullanıcıya ait log kaydı yok.</div>
        </div>

      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
// İkonlar
import { 
  UserIcon, ArrowLeftIcon, WalletIcon, ClipboardDocumentListIcon, DocumentTextIcon 
} from '@heroicons/vue/24/outline'

// Composables
import { useMask } from '~/composables/useMask' 

definePageMeta({ layout: 'admin' })
const route = useRoute()
const API_BASE = '/api/admin'
const { maskName } = useMask()

const loading = ref(true)
const userDetails = ref({ user: null, orders: [], logs: [], transactions: [] })

const fetchDetails = async () => {
  const userId = route.params.id || route.query.id
  if (!userId) {
    loading.value = false
    return
  }
  
  try {
    const data = await $fetch(`${API_BASE}/users/${userId}/details`)
    userDetails.value = data
  } catch (e) {
    console.error(e)
  } finally {
    loading.value = false
  }
}

// Helper Fonksiyonlar
const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('tr-TR', { day: 'numeric', month: 'long', year: 'numeric' })
}

const formatTime = (dateStr) => {
  if (!dateStr) return ''
  return new Date(dateStr).toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' })
}

const formatCurrency = (value) => {
  return new Intl.NumberFormat('tr-TR', { style: 'currency', currency: 'TRY' }).format(value || 0)
}

const getLastTransaction = (transactions, type) => {
  if (!transactions) return null
  return transactions.find(t => t.type === type)
}

onMounted(fetchDetails)
</script>

<style scoped>
/* Scrollbar for lists */
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #333; border-radius: 10px; }

@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.animate-fade-in { animation: fadeIn 0.5s ease-out forwards; }
</style>