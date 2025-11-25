<template>
  <div class="max-w-[1600px] mx-auto animate-fade-in">
    
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-8">
      <div>
        <h1 class="text-3xl font-bold text-white flex items-center gap-3">
          <span class="p-2 bg-cyan-500/10 rounded-xl border border-cyan-500/20 text-cyan-400">
            <!-- 👇 İKON DEĞİŞTİRİLDİ: UserPlusIcon -->
            <UserPlusIcon class="w-8 h-8" />
          </span>
          Onay Bekleyenler
        </h1>
        <p class="text-slate-400 mt-2 ml-1">Sisteme kayıt olan yeni kullanıcıları inceleyin ve onaylayın.</p>
      </div>
      
      <div class="flex items-center gap-4">
        <div class="px-4 py-2 bg-[#121212]/80 border border-white/10 rounded-xl text-sm text-slate-300 backdrop-blur-md">
          Bekleyen: <span class="text-cyan-400 font-bold ml-1">{{ users.length }}</span>
        </div>
        <NuxtLink to="/admin" class="px-4 py-2 bg-white/5 border border-white/10 rounded-xl text-sm hover:bg-white/10 transition flex items-center gap-2 text-slate-300">
          <ArrowLeftIcon class="w-4 h-4" /> Menüye Dön
        </NuxtLink>
      </div>
    </div>

    <!-- YÜKLENİYOR DURUMU -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-20">
      <div class="w-10 h-10 border-4 border-cyan-500/30 border-t-cyan-500 rounded-full animate-spin"></div>
      <p class="text-slate-500 mt-4 text-sm">Kayıtlar yükleniyor...</p>
    </div>

    <!-- BOŞ LİSTE -->
    <div v-else-if="users.length === 0" class="flex flex-col items-center justify-center py-20 bg-[#121212]/40 rounded-3xl border border-dashed border-white/10 text-slate-500">
       <CheckBadgeIcon class="w-16 h-16 mb-4 opacity-20" />
       <p>Harika! Onay bekleyen kayıt yok.</p>
    </div>

    <!-- KARTLAR GRID -->
    <transition-group v-else name="list" tag="div" class="grid sm:grid-cols-2 xl:grid-cols-3 gap-6">
      <div
        v-for="u in users"
        :key="u._id || u.id"
        class="group relative bg-[#121212]/60 border border-white/5 rounded-3xl p-6 flex flex-col justify-between hover:border-cyan-500/30 hover:shadow-[0_0_30px_rgba(6,182,212,0.1)] transition-all duration-300 backdrop-blur-sm"
      >
        <!-- Üst Kısım -->
        <div>
          <div class="flex justify-between items-start mb-6">
            <div class="flex items-center gap-4">
              <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-cyan-900/20 to-blue-900/20 border border-cyan-500/20 flex items-center justify-center text-cyan-400 font-bold text-xl shadow-inner">
                {{ u.name.charAt(0).toUpperCase() }}
              </div>
              <div>
                <h2 class="text-lg font-bold text-white group-hover:text-cyan-300 transition-colors">
                  {{ u.name }} {{ u.surname }}
                </h2>
                <p class="text-xs text-slate-500 font-mono">{{ u.email }}</p>
                <p class="text-xs text-slate-500 font-mono mt-0.5">{{ u.phone }}</p>
              </div>
            </div>
            <span class="text-[10px] font-bold uppercase tracking-wider bg-yellow-500/10 text-yellow-500 px-2 py-1 rounded border border-yellow-500/20 animate-pulse">
              Bekliyor
            </span>
          </div>

          <div class="space-y-4 mb-6">
            <!-- Birim Seçimi -->
            <div class="bg-black/20 p-3 rounded-xl border border-white/5">
              <label class="text-[10px] uppercase text-slate-500 font-bold mb-1.5 block flex justify-between">
                <span>Birim Atama</span>
                <span class="text-slate-600">{{ u.unit || 'Seçilmedi' }}</span>
              </label>
              <div class="relative">
                <select 
                  v-model="u.unit" 
                  @change="onUnitChange(u)"
                  class="w-full bg-black/40 border border-white/10 text-slate-200 text-sm rounded-lg pl-3 pr-8 py-2.5 outline-none focus:border-cyan-500 transition appearance-none cursor-pointer hover:bg-black/60"
                >
                  <option value="" disabled>Birim Seçiniz...</option>
                  <option v-for="unit in availableUnits" :key="unit._id" :value="unit.name">
                    {{ unit.name }} ({{ unit.price }} ₺)
                  </option>
                </select>
                <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-500">
                  <ChevronDownIcon class="w-4 h-4" />
                </div>
              </div>
            </div>

            <!-- Fiyat Alanı -->
            <div class="bg-black/20 p-3 rounded-xl border border-white/5">
              <label class="text-[10px] uppercase text-slate-500 font-bold mb-1.5 block">Yemek Ücreti</label>
              <div class="relative">
                <input 
                  type="number" 
                  v-model="u.tempPrice"
                  placeholder="0.00"
                  class="w-full bg-black/40 border border-white/10 rounded-lg pl-3 pr-10 py-2.5 text-white placeholder-slate-700 focus:border-cyan-500 focus:bg-black/60 transition outline-none font-mono"
                  @keydown.enter="approveUser(u)"
                >
                <span class="absolute right-3 top-2.5 text-slate-500 text-sm font-bold">₺</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Alt Butonlar -->
        <div class="flex gap-3 mt-auto">
          <button
            @click="viewDocument(u)"
            class="px-4 py-2.5 rounded-xl bg-white/5 hover:bg-white/10 text-slate-400 hover:text-white border border-white/5 transition flex-1 flex items-center justify-center gap-2 text-sm font-medium"
          >
            <DocumentTextIcon class="w-4 h-4" />
            <span class="hidden xl:inline">Belge</span>
          </button>

          <button
            @click="approveUser(u)"
            :disabled="actionLoadingId === (u._id || u.id)"
            class="px-4 py-2.5 rounded-xl bg-gradient-to-r from-cyan-600 to-blue-600 hover:from-cyan-500 hover:to-blue-500 text-white border border-cyan-500/30 transition flex-[2] flex items-center justify-center gap-2 text-sm font-bold shadow-lg shadow-cyan-900/20"
          >
            <span v-if="actionLoadingId === (u._id || u.id) && actionType === 'approve'" class="animate-spin">⟳</span>
            <span v-else>Onayla</span>
          </button>

          <button
            @click="rejectUser(u._id || u.id)"
            :disabled="actionLoadingId === (u._id || u.id)"
            class="px-3 py-2.5 rounded-xl bg-red-500/10 hover:bg-red-500/20 text-red-400 hover:text-red-300 border border-red-500/20 transition flex-none flex items-center justify-center"
            title="Reddet"
          >
            <XMarkIcon class="w-5 h-5" />
          </button>
        </div>

        <!-- Tarih Rozeti -->
        <div class="absolute top-6 right-6 text-[10px] text-slate-600 font-mono">
          {{ new Date(u.created_at).toLocaleDateString('tr-TR') }}
        </div>

      </div>
    </transition-group>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import useAuth from '../composables/useAuth'
import { 
  UserPlusIcon, ArrowLeftIcon, CheckBadgeIcon, ChevronDownIcon, // 👈 UserPlusIcon olarak değiştirdik
  DocumentTextIcon, XMarkIcon 
} from '@heroicons/vue/24/outline'

definePageMeta({ layout: 'admin' })
const { logout } = useAuth()

const API_BASE = '/api/admin'
const users = ref([])
const availableUnits = ref([])
const loading = ref(true)
const actionLoadingId = ref(null)
const actionType = ref(null)

// Kullanıcıları Çek
const fetchPending = async () => {
  loading.value = true
  try {
    const data = await $fetch(`${API_BASE}/users/pending`)
    users.value = Array.isArray(data) ? data.map(user => ({ ...user, tempPrice: '' })) : []
  } catch (err) {
    console.error("Kullanıcı listesi hatası:", err)
    if (err?.statusCode === 401) {
      await logout()
      return navigateTo('/login')
    }
  } finally {
    loading.value = false
  }
}

// Birimleri Çek
const fetchUnits = async () => {
  try {
    const data = await $fetch(`${API_BASE}/units`)
    availableUnits.value = Array.isArray(data) ? data : []
  } catch (e) { console.error("Birimler çekilemedi:", e) }
}

// Birim Değişince Fiyat Güncelle
const onUnitChange = (user) => {
  const selectedUnit = availableUnits.value.find(u => u.name === user.unit)
  if (selectedUnit) user.tempPrice = selectedUnit.price
}

// Belge Aç
const viewDocument = async (u) => {
  try {
    const blob = await $fetch(`${API_BASE}/users/${u._id || u.id}/document`, { responseType: 'blob' })
    window.open(URL.createObjectURL(blob), '_blank')
  } catch (err) { alert('Belge görüntülenemedi.') }
}

// Onayla
const approveUser = async (user) => {
  const userId = user._id || user.id
  
  // Fiyat 0 olabilir (stajyer vs), sadece boş veya negatif olmasın
  if (user.tempPrice === '' || user.tempPrice === null || user.tempPrice < 0) {
    return alert(`Lütfen ${user.name} için geçerli bir yemek ücreti girin!`)
  }

  actionLoadingId.value = userId
  actionType.value = 'approve'
  
  try {
    await $fetch(`${API_BASE}/users/${userId}/approve`, {
      method: 'POST',
      body: { meal_price: user.tempPrice, unit: user.unit }
    })
    users.value = users.value.filter(u => (u._id || u.id) !== userId)
  } catch (err) { alert('Hata oluştu: ' + (err.data?.message || 'Bilinmeyen hata')) } 
  finally { 
    actionLoadingId.value = null
    actionType.value = null
  }
}

// Reddet
const rejectUser = async (id) => {
  if (!confirm('Reddedilsin mi?')) return
  actionLoadingId.value = id
  actionType.value = 'reject'
  try {
    await $fetch(`${API_BASE}/users/${id}/reject`, { method: 'DELETE' })
    users.value = users.value.filter(u => (u._id || u.id) !== id)
  } catch (err) { alert('Hata oluştu.') } 
  finally { 
    actionLoadingId.value = null
    actionType.value = null
  }
}

onMounted(async () => {
  await fetchPending()
  await fetchUnits()
})
</script>

<style scoped>
.list-enter-active, .list-leave-active { transition: all 0.4s ease; }
.list-enter-from, .list-leave-to { opacity: 0; transform: scale(0.95); }
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.animate-fade-in { animation: fadeIn 0.5s ease-out forwards; }
</style>