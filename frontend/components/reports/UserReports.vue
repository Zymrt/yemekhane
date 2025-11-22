<template>
  <div>
    <h2 class="text-3xl font-bold mb-8 flex items-center gap-3">
      <span class="i-lucide-chart-pie text-orange-400"></span>
      Yönetim Raporları
    </h2>

    <p class="text-white/70 mb-10">
      Sistemdeki genel durumu, kayıtlı kullanıcıları ve bakiyelerini buradan inceleyebilirsin.
    </p>

    <!-- Yükleniyor Durumu -->
    <div v-if="loading" class="flex justify-center py-20 text-white/70">
      <div class="flex flex-col items-center gap-3">
        <div class="w-10 h-10 border-4 border-orange-400 border-t-transparent rounded-full animate-spin"></div>
        <span>Veriler analiz ediliyor...</span>
      </div>
    </div>

    <div v-else-if="error" class="bg-red-500/10 border border-red-500/50 text-red-200 p-6 rounded-2xl text-center">
      {{ error }}
    </div>

    <!-- İÇERİK -->
    <div v-else class="space-y-10">
      
      <!-- 1. BÖLÜM: ÖZET KARTLAR (İSTATİSTİKLER) -->
      <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <!-- Kullanıcı Özeti -->
        <div class="bg-white/5 backdrop-blur-md border border-white/10 p-6 rounded-2xl relative overflow-hidden group hover:bg-white/10 transition">
          <div class="absolute -right-6 -top-6 text-white/5 text-9xl group-hover:scale-110 transition duration-500">
            <span class="i-lucide-users"></span>
          </div>
          <div class="relative">
            <p class="text-white/60 font-medium mb-1">Toplam Kullanıcı</p>
            <h3 class="text-4xl font-bold text-white mb-2">{{ userStats.total }}</h3>
            <div class="flex gap-3 text-sm">
              <span class="text-emerald-400 flex items-center gap-1">
                <i class="i-lucide-check-circle"></i> {{ userStats.approved }} Onaylı
              </span>
              <span class="text-amber-400 flex items-center gap-1">
                <i class="i-lucide-clock"></i> {{ userStats.pending }} Bekleyen
              </span>
            </div>
          </div>
        </div>

        <!-- Menü Özeti -->
        <div class="bg-white/5 backdrop-blur-md border border-white/10 p-6 rounded-2xl relative overflow-hidden group hover:bg-white/10 transition">
          <div class="absolute -right-6 -top-6 text-white/5 text-9xl group-hover:scale-110 transition duration-500">
            <span class="i-lucide-utensils"></span>
          </div>
          <div class="relative">
            <p class="text-white/60 font-medium mb-1">Toplam Menü</p>
            <h3 class="text-4xl font-bold text-white mb-2">{{ menuStats.total }}</h3>
            <p class="text-sm text-white/60">
              Bugün menü: 
              <span :class="menuStats.today ? 'text-emerald-400 font-bold' : 'text-rose-400 font-bold'">
                {{ menuStats.today ? 'GİRİLMİŞ ✅' : 'GİRİLMEMİŞ ❌' }}
              </span>
            </p>
          </div>
        </div>

        <!-- Bakiye Özeti (Yeni Ekledim) -->
        <div class="bg-gradient-to-br from-emerald-900/40 to-emerald-600/10 backdrop-blur-md border border-emerald-500/20 p-6 rounded-2xl relative overflow-hidden">
          <div class="absolute -right-6 -top-6 text-emerald-500/10 text-9xl">
            <span class="i-lucide-wallet"></span>
          </div>
          <div class="relative">
            <p class="text-emerald-200/80 font-medium mb-1">Toplam Sistem Bakiyesi</p>
            <h3 class="text-4xl font-bold text-white mb-2">{{ totalSystemBalance.toFixed(2) }} ₺</h3>
            <p class="text-sm text-emerald-200/60">Kullanıcıların cüzdanındaki toplam para</p>
          </div>
        </div>
      </div>

      <!-- 2. BÖLÜM: DETAYLI KULLANICI LİSTESİ -->
      <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-2xl overflow-hidden shadow-2xl">
        <div class="p-6 border-b border-white/10 flex flex-col md:flex-row justify-between items-center gap-4">
          <h3 class="text-xl font-bold text-white flex items-center gap-2">
            <span class="i-lucide-list text-cyan-400"></span> Kullanıcı Listesi ve Bakiyeler
          </h3>
          
          <!-- Basit Arama Kutusu -->
          <div class="relative w-full md:w-64">
            <span class="absolute left-3 top-1/2 -translate-y-1/2 text-white/40 i-lucide-search"></span>
            <input 
              v-model="searchQuery" 
              type="text" 
              placeholder="İsim veya birim ara..." 
              class="w-full bg-black/20 border border-white/10 rounded-lg py-2 pl-10 pr-4 text-white placeholder-white/40 focus:outline-none focus:border-orange-500/50 transition"
            >
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left border-collapse">
            <thead>
              <tr class="bg-white/5 text-white/60 text-sm uppercase tracking-wider">
                <th class="p-4 font-semibold">Kullanıcı</th>
                <th class="p-4 font-semibold">Birim / Departman</th>
                <th class="p-4 font-semibold">Durum</th>
                <th class="p-4 font-semibold text-right">Yemek Ücreti</th>
                <th class="p-4 font-semibold text-right">Cüzdan Bakiyesi</th>
                <th class="p-4 font-semibold text-right">Kayıt Tarihi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
              <tr 
                v-for="user in filteredUsers" 
                :key="user._id || user.id" 
                class="hover:bg-white/5 transition duration-150 group"
              >
                <!-- Kullanıcı Adı & Rolü -->
                <td class="p-4">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-orange-400 to-rose-500 flex items-center justify-center text-white font-bold shadow-lg">
                      {{ user.name.charAt(0) }}{{ user.surname.charAt(0) }}
                    </div>
                    <div>
                      <div class="font-semibold text-white group-hover:text-orange-300 transition">{{ user.name }} {{ user.surname }}</div>
                      <div class="text-xs text-white/50">{{ user.role === 'admin' ? 'Yönetici 🛡️' : 'Kullanıcı' }}</div>
                    </div>
                  </div>
                </td>

                <!-- Birim -->
                <td class="p-4 text-white/80">
                  <span class="bg-white/10 px-3 py-1 rounded-lg text-sm">{{ user.unit || '-' }}</span>
                </td>

                <!-- Durum -->
                <td class="p-4">
                  <span 
                    class="px-3 py-1 rounded-full text-xs font-medium border"
                    :class="user.status === 'approved' 
                      ? 'bg-emerald-500/10 border-emerald-500/20 text-emerald-400' 
                      : 'bg-amber-500/10 border-amber-500/20 text-amber-400'"
                  >
                    {{ user.status === 'approved' ? 'Onaylı' : 'Bekliyor' }}
                  </span>
                </td>

                <!-- Yemek Ücreti -->
                <td class="p-4 text-right text-white/70 font-mono">
                  {{ user.meal_price || 50 }} ₺
                </td>

                <!-- Bakiye (En Önemlisi) -->
                <td class="p-4 text-right">
                  <span 
                    class="font-bold font-mono text-lg"
                    :class="(user.balance || 0) > 0 ? 'text-emerald-400' : 'text-red-400'"
                  >
                    {{ (user.balance || 0).toFixed(2) }} ₺
                  </span>
                </td>

                <!-- Tarih -->
                <td class="p-4 text-right text-white/50 text-sm">
                  {{ formatDate(user.created_at) }}
                </td>
              </tr>
              
              <!-- Arama Sonucu Bulunamadıysa -->
              <tr v-if="filteredUsers.length === 0">
                <td colspan="6" class="p-8 text-center text-white/40 italic">
                  Aradığınız kriterlere uygun kullanıcı bulunamadı.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'

const loading = ref(true)
const error = ref(null)
const searchQuery = ref('')

// Veri State'leri
const userStats = ref({ total: 0, pending: 0, approved: 0 })
const menuStats = ref({ total: 0, today: null })
const userList = ref([]) // Kullanıcı listesi burada tutulacak

// Arama Filtreleme
const filteredUsers = computed(() => {
  if (!searchQuery.value) return userList.value
  const query = searchQuery.value.toLowerCase()
  return userList.value.filter(u => 
    u.name.toLowerCase().includes(query) || 
    u.surname.toLowerCase().includes(query) ||
    (u.unit && u.unit.toLowerCase().includes(query))
  )
})

// Toplam Sistem Bakiyesi Hesaplama
const totalSystemBalance = computed(() => {
  return userList.value.reduce((acc, user) => acc + (parseFloat(user.balance) || 0), 0)
})

const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('tr-TR', { day: 'numeric', month: 'short', year: 'numeric' })
}

onMounted(async () => {
  try {
    // 1. İstatistikleri Çek
    const statsRes = await $fetch('/api/admin/dashboard')
    userStats.value = statsRes?.userStats ?? userStats.value
    menuStats.value = statsRes?.menuStats ?? menuStats.value

    // 2. Kullanıcı Listesini Çek (Yeni Eklediğimiz Endpoint)
    // Eğer backend'de bu route yoksa hata alabilirsin, önce backend'i ekle!
    const usersRes = await $fetch('/api/admin/users')
    userList.value = Array.isArray(usersRes) ? usersRes : []

  } catch (e) {
    console.error('❌ Veri hatası:', e)
    error.value = 'Veriler alınırken bir sorun oluştu.'
  } finally {
    loading.value = false
  }
})
</script>