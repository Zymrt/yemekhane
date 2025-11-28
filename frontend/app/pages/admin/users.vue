<template>
  <div class="max-w-[1600px] mx-auto animate-fade-in">
    
    <!-- HEADER & ARAMA -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
      <div>
        <h1 class="text-3xl font-bold text-white flex items-center gap-3">
          <span class="p-2 bg-blue-500/10 rounded-xl border border-blue-500/20 text-blue-400">
            <UsersIcon class="w-8 h-8" />
          </span>
          Kullanıcı Yönetimi
        </h1>
        <p class="text-slate-400 mt-2 ml-1">Sistemdeki onaylı kullanıcıları arayın ve düzenleyin.</p>
      </div>

      <!-- Neon Arama Kutusu -->
      <div class="relative w-full md:w-96 group">
        <div class="absolute -inset-0.5 bg-gradient-to-r from-blue-500 to-purple-500 rounded-xl opacity-20 group-hover:opacity-50 transition duration-500 blur"></div>
        <div class="relative bg-[#0a0a0a] rounded-xl flex items-center">
          <MagnifyingGlassIcon class="w-5 h-5 text-slate-500 absolute left-3 group-focus-within:text-blue-400 transition-colors" />
          <input 
            v-model="searchQuery"
            type="text" 
            placeholder="İsim (Maskeli aranır), E-posta veya Birim..."
            class="w-full bg-transparent border border-white/10 rounded-xl py-3 pl-10 pr-4 text-white placeholder-slate-600 focus:outline-none focus:border-blue-500/50 transition-all"
          >
        </div>
      </div>
    </div>

    <!-- YÜKLENİYOR -->
    <div v-if="loading" class="text-center py-20 text-slate-500">
      <div class="w-10 h-10 border-4 border-blue-500/30 border-t-blue-500 rounded-full animate-spin mx-auto mb-4"></div>
      Kullanıcılar getiriliyor...
    </div>
    
    <!-- KARTLAR -->
    <div v-else class="grid gap-4 sm:grid-cols-2 xl:grid-cols-3 2xl:grid-cols-4">
      <div 
        v-for="user in filteredUsers" 
        :key="user._id"
        class="group bg-[#121212]/60 border border-white/5 rounded-2xl p-5 hover:border-blue-500/30 transition-all duration-300 backdrop-blur-sm flex flex-col"
      >
        <!-- Üst Kısım -->
        <div class="flex justify-between items-start mb-4">
          <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-slate-800 border border-white/10 flex items-center justify-center text-slate-300 font-bold text-sm">
              {{ user.name?.charAt(0).toUpperCase() }}
            </div>
            <div>
              <!-- 🔒 KVKK: İsimler Maskelendi -->
              <h3 class="font-bold text-white group-hover:text-blue-300 transition-colors text-sm">
                {{ maskName(user.name) }} {{ maskName(user.surname) }}
              </h3>
              <p class="text-[10px] text-slate-500 font-mono">{{ user.phone || 'Tel Yok' }}</p>
            </div>
          </div>
          <span class="bg-emerald-500/10 text-emerald-400 text-[10px] px-2 py-1 rounded border border-emerald-500/20 font-mono">
            AKTİF
          </span>
        </div>

        <!-- Düzenleme Alanı -->
        <div class="bg-black/30 rounded-xl p-3 border border-white/5 mt-auto space-y-3">
          
          <!-- 1. Birim Değiştirme -->
          <div>
            <label class="text-[10px] uppercase tracking-wide text-slate-500 font-bold block mb-1">Birim</label>
            <div class="relative">
              <!-- 👇 BURAYA @change EKLENDİ -->
              <select 
                v-model="user.unit"
                @change="onUnitChange(user)"
                class="w-full bg-black/40 border border-white/10 rounded-lg py-1.5 pl-2 pr-6 text-xs text-white focus:border-blue-500 outline-none transition appearance-none cursor-pointer"
              >
                <option v-for="unit in availableUnits" :key="unit._id" :value="unit.name">
                  {{ unit.name }} ({{ unit.price }} ₺)
                </option>
              </select>
              <div class="absolute inset-y-0 right-0 flex items-center px-2 pointer-events-none text-slate-500">
                <ChevronDownIcon class="w-3 h-3" />
              </div>
            </div>
          </div>

          <!-- 2. Fiyat Değiştirme -->
          <div>
            <label class="text-[10px] uppercase tracking-wide text-slate-500 font-bold block mb-1">Yemek Ücreti</label>
            <div class="flex gap-2">
              <div class="relative flex-1">
                <span class="absolute left-2 top-1.5 text-slate-500 text-xs">₺</span>
                <input 
                  type="number" 
                  v-model="user.meal_price" 
                  class="w-full bg-black/40 border border-white/10 rounded-lg py-1.5 pl-5 pr-2 text-xs text-white focus:border-blue-500 outline-none transition font-mono"
                >
              </div>
              <button 
                @click="updateUser(user)"
                :disabled="updatingId === user._id"
                class="bg-blue-600 hover:bg-blue-500 text-white px-3 py-1 rounded-lg text-xs font-bold transition disabled:opacity-50 disabled:cursor-not-allowed flex items-center gap-1"
              >
                <span v-if="updatingId === user._id" class="animate-spin">⟳</span>
                <span v-else>Kaydet</span>
              </button>
            </div>
          </div>

        </div>

        <!-- Alt Bilgi -->
        <div class="mt-3 pt-3 border-t border-white/5 text-[10px] text-slate-500 flex justify-between font-mono">
           <span>Bakiye: <span class="text-emerald-400">{{ parseFloat(user.balance).toFixed(2) }} ₺</span></span>
        </div>
      </div>
    </div>
    
    <!-- Sonuç Bulunamadı -->
    <div v-if="!loading && filteredUsers.length === 0" class="text-center py-20 text-slate-500">
      Aradığınız kriterlere uygun kullanıcı bulunamadı.
    </div>

  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { UsersIcon, MagnifyingGlassIcon, ChevronDownIcon } from '@heroicons/vue/24/outline'

definePageMeta({ layout: 'admin' })

const users = ref([])
const availableUnits = ref([]) 
const searchQuery = ref('')
const loading = ref(true)
const updatingId = ref(null)
const API_BASE = '/api/admin'

// 👇 MASKELEME FONKSİYONUNU BURAYA KOYDUK (ARTIK IMPORT DERDİ YOK)
const maskName = (fullName) => {
  if (!fullName) return '***';
  return fullName.trim().split(' ').map(part => {
    return part.charAt(0).toUpperCase() + '***';
  }).join(' ');
};

// Verileri Çek
const fetchData = async () => {
  loading.value = true
  try {
    const [userData, unitData] = await Promise.all([
      $fetch(`${API_BASE}/users/all`),
      $fetch(`${API_BASE}/units`)
    ])

    users.value = userData.filter(u => u.status === 'approved')
    availableUnits.value = unitData || []

  } catch (error) { 
    console.error(error) 
  } finally { 
    loading.value = false 
  }
}

// Arama Filtresi
const filteredUsers = computed(() => {
  if (!searchQuery.value) return users.value
  const q = searchQuery.value.toLowerCase()
  
  return users.value.filter(u => 
    u.name?.toLowerCase().includes(q) ||
    u.surname?.toLowerCase().includes(q) ||
    u.email?.toLowerCase().includes(q) ||
    u.unit?.toLowerCase().includes(q)
  )
})

// 👇 YENİ EKLENEN: BİRİM DEĞİŞİNCE FİYATI GÜNCELLE
const onUnitChange = (user) => {
  const selectedUnit = availableUnits.value.find(u => u.name === user.unit)
  if (selectedUnit) {
    // Seçilen birimin fiyatını kullanıcının yemek ücreti alanına kopyala
    user.meal_price = selectedUnit.price
  }
}

// Güncelleme (Fiyat + Birim)
const updateUser = async (user) => {
  updatingId.value = user.id  

  try {
    await $fetch(`${API_BASE}/users/${user.id}/update-price`, {
      method: 'POST',
      body: { 
        meal_price: user.meal_price,
        unit: user.unit 
      }
    })
    alert(`${maskName(user.name)} kullanıcısı güncellendi!`)
  } catch (error) { 
    console.error(error) 
    alert('Güncelleme hatası!') 
  } finally { 
    updatingId.value = null 
  }
}
onMounted(fetchData)
</script>

<style scoped>
@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.animate-fade-in { animation: fadeIn 0.5s ease-out forwards; }
</style>