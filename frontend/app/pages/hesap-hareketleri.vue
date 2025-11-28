<template>
  <!-- Sayfayı user layout ile sar -->
  <NuxtLayout name="user">
    <!-- 🔹 Navbar butonları (Aktif sayfayı vurgulamak için class eklendi) -->
    <template #left-buttons>
      <NuxtLink to="/menu" class="btn btn-ghost">
        Ana Sayfa
      </NuxtLink>
      <NuxtLink to="/yorumlar" class="btn btn-ghost">
        Değerlendirmelerim
      </NuxtLink>
    </template>

    <template #right-buttons>
      <NuxtLink to="/hesap-hareketleri" class="btn btn-ghost-active"> <!-- 🌟 Aktif -->
        Hesap Hareketleri
      </NuxtLink>
      <NuxtLink to="/bakiye" class="btn btn-primary">
        Bakiye Yükle
      </NuxtLink>
    </template>

    <!-- 💫 İçerik -->
    <div class="max-w-4xl mx-auto mt-6">
      
      <!-- Başlık -->
      <h1 class="text-3xl font-bold text-white mb-6 drop-shadow-lg">
        Hesap Hareketleri
      </h1>

      <!-- Yükleniyor Durumu -->
      <div v-if="pending" class="text-center text-white py-10">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-white mb-2"></div>
        <p>Hesap hareketleri yükleniyor...</p>
      </div>

      <!-- Hata Durumu -->
      <div v-else-if="error" class="bg-red-200 text-red-800 p-4 rounded-xl text-center">
        Veriler yüklenirken bir hata oluştu.
      </div>

      <!-- Hareket Yoksa -->
      <div v-else-if="!transactions || transactions.length === 0" 
           class="bg-white/30 backdrop-blur-2xl border border-white/30
                  rounded-3xl p-8 text-center shadow-lg text-gray-800">
        <p class="font-semibold text-lg">Henüz görüntülenecek bir hesap hareketi yok.</p>
      </div>

      <!-- Hareket Listesi -->
      <div v-else class="space-y-4">
        <div 
          v-for="item in transactions" 
          :key="item._id"
          class="bg-white/30 backdrop-blur-2xl border border-white/30
                 rounded-3xl p-5 shadow-lg flex items-center gap-4"
        >
          <!-- İkon (Giriş / Çıkış) -->
          <div 
            class="w-12 h-12 rounded-full flex-shrink-0 flex items-center justify-center"
            :class="item.type === 'credit' ? 'bg-emerald-100 text-emerald-600' : 'bg-rose-100 text-rose-600'"
          >
            <!-- Credit (Bakiye Yükleme) İkonu -->
            <svg v-if="item.type === 'credit'" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
              <path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15" />
            </svg>
            <!-- Debit (Harcama) İkonu -->
            <svg v-else xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="2" stroke="currentColor" class="w-6 h-6">
              <path stroke-linecap="round" stroke-linejoin="round" d="M19.5 12h-15" />
            </svg>
          </div>

          <!-- Açıklama ve Tarih -->
          <div class="flex-1">
            <h3 class="font-bold text-gray-900 text-lg">
              {{ getTransactionDescription(item) }}
            </h3>
            <p class="text-sm text-gray-700">
              {{ formatDate(item.created_at) }}
            </p>
          </div>

          <!-- Tutar -->
          <div 
            class="text-xl font-bold"
            :class="item.type === 'credit' ? 'text-emerald-600' : 'text-rose-600'"
          >
            {{ item.type === 'credit' ? '+' : '-' }}{{ item.amount.toFixed(2) }} ₺
          </div>
        </div>
        
        <!-- Sayfalama (Pagination) -->
        <div v-if="meta.last_page > 1" class="flex justify-between items-center pt-4">
          <button 
            @click="loadPage(meta.current_page - 1)" 
            :disabled="meta.current_page === 1"
            class="btn btn-soft disabled:opacity-50"
          >
            ← Önceki
          </button>
          <span class="text-white font-medium">
            Sayfa {{ meta.current_page }} / {{ meta.last_page }}
          </span>
          <button 
            @click="loadPage(meta.current_page + 1)" 
            :disabled="meta.current_page === meta.last_page"
            class="btn btn-soft disabled:opacity-50"
          >
            Sonraki →
          </button>
        </div>

      </div>
    </div>
  </NuxtLayout>
</template>

<script setup>
import { ref, reactive } from 'vue'
import useAuth from '../composables/useAuth'
import protectUserPage from '../composables/protectUserPage'

// Sayfa koruması
await protectUserPage()

// Data state'leri
const transactions = ref([])
const meta = ref({}) // Pagination bilgisi için
const pending = ref(true)
const error = ref(null)

// Veri çekme fonksiyonu
async function loadPage(page = 1) {
  pending.value = true
  error.value = null
  try {
    // Backend'den paginated veriyi çek
    const response = await $fetch('/api/transactions', {
      query: { page: page }
    })
    
    transactions.value = response.data
    meta.value = response.meta
    
  } catch (err) {
    console.error('Hesap hareketleri yüklenemedi:', err)
    error.value = 'Veri yüklenemedi.'
  } finally {
    pending.value = false
  }
}

// Tarih formatlama
const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  // Saati de göstermek için
  return new Date(dateStr).toLocaleString('tr-TR', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit'
  })
}

// İşlem tipi için açıklama
const getTransactionDescription = (item) => {
  if (item.type === 'debit') {
    if (item.meta && item.meta.order_id) {
      return 'Menü Satın Alımı';
    }
    return 'Harcama';
  }
  if (item.type === 'credit') {
    if (item.meta && item.meta.payment_id) {
      return 'Online Bakiye Yüklemesi';
    }
    return 'Bakiye Yüklemesi';
  }
  return 'Diğer İşlem';
}

// Sayfa ilk yüklendiğinde verileri çek
onMounted(() => {
  loadPage(1)
})
</script>

<style scoped>
/* 🔘 Minimal buton sistemimiz (Tailwind @apply) */
.btn {
  @apply inline-flex items-center justify-center px-4 py-2 rounded-xl font-semibold transition
         focus:outline-none focus:ring-2 focus:ring-offset-0 active:scale-[.99];
}

/* Navbar için “ghost” (şeffaf) */
.btn-ghost {
  @apply text-white/90 hover:text-white bg-white/0 hover:bg-white/10 border border-white/10;
}
/* Navbar için "aktif ghost" */
.btn-ghost-active {
  @apply text-white bg-white/20 border border-white/20;
}

/* Sağ taraftaki “outline” */
.btn-outline {
  @apply text-white border border-white/40 bg-transparent hover:bg-white/10;
}

/* Vurgulu buton */
.btn-primary {
  @apply text-white bg-gradient-to-r from-orange-500 via-orange-500 to-orange-600
         hover:brightness-110 shadow-md;
}

/* İçerik alanındaki yumuşak buton (Pagination için) */
.btn-soft {
  @apply text-sky-900 bg-white/70 hover:bg-white/90 border border-white/80
         backdrop-blur-sm rounded-xl;
}

/* Küçük ekranlarda butonların nefes alması için */
@media (max-width: 768px) {
  .btn { @apply text-sm px-3 py-2; }
}
</style>