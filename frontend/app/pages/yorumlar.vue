<template>
  <!-- Sayfayı user layout ile sar -->
  <NuxtLayout name="user">
    <!-- 🔹 Navbar butonları (Aktif sayfayı vurgulamak için class eklendi) -->
    <template #left-buttons>
      <NuxtLink to="/menu" class="btn btn-ghost">
        Ana Sayfa
      </NuxtLink>
      <NuxtLink to="/yorumlar" class="btn btn-ghost-active">
        Değerlendirmelerim
      </NuxtLink>
    </template>

    <template #right-buttons>
      <NuxtLink to="/hesap-hareketleri" class="btn btn-outline">
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
        Geçmiş Değerlendirmelerim
      </h1>

      <!-- Yükleniyor Durumu -->
      <div v-if="pending" class="text-center text-white py-10">
        <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-white mb-2"></div>
        <p>Yorumlar yükleniyor...</p>
      </div>

      <!-- Hata Durumu -->
      <div v-else-if="error" class="bg-red-200 text-red-800 p-4 rounded-xl text-center">
        Değerlendirmeler yüklenirken bir hata oluştu.
      </div>

      <!-- Yorum Yoksa -->
      <div v-else-if="!reviews || reviews.length === 0" 
           class="bg-white/30 backdrop-blur-2xl border border-white/30
                  rounded-3xl p-8 text-center shadow-lg text-gray-800">
        <p class="font-semibold text-lg">Henüz hiç değerlendirme yapmamışsınız.</p>
      </div>

      <!-- Yorum Listesi -->
      <div v-else class="space-y-6">
        <div 
          v-for="review in reviews" 
          :key="review.id"
          class="bg-white/30 backdrop-blur-2xl border border-white/30
                 rounded-3xl p-6 shadow-lg transition-all duration-300
                 transform hover:scale-[1.02]"
        >
          <!-- Kart Başlığı (Tarih ve Puan) -->
          <div class="flex flex-wrap justify-between items-center mb-4 border-b border-white/50 pb-3">
            <h3 class="text-xl font-bold text-gray-900 drop-shadow-sm">
              {{ formatDate(review.menu.date) }} Menüsü
            </h3>
            <!-- Yıldız Puanı -->
            <div class="flex items-center" title="Verdiğiniz Puan">
              <span 
                v-for="star in 5" 
                :key="star"
                class="text-2xl"
                :class="star <= review.rating ? 'text-yellow-400' : 'text-gray-400'"
              >
                ★
              </span>
            </div>
          </div>

          <!-- Yorum Metni -->
          <p v-if="review.comment" class="text-gray-800 italic mb-4">
            "{{ review.comment }}"
          </p>
          <p v-else class="text-gray-600 italic mb-4">
            (Yorum yazılmamış)
          </p>

          <!-- Yorum Yapılan Menü -->
          <h4 class="text-sm font-semibold text-gray-700 mb-2">Değerlendirilen Menü:</h4>
          <ul class="list-disc list-inside text-gray-700 space-y-1">
            <li v-for="(item, index) in review.menu.items" :key="index">
              {{ item.name }} <!-- 🌟 DEĞİŞİKLİK: 'item' yerine 'item.name' yazıldı -->
            </li>
          </ul>
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
const reviews = ref([])
const meta = ref({}) // Pagination bilgisi için
const pending = ref(true)
const error = ref(null)

// Veri çekme fonksiyonu
async function loadPage(page = 1) {
  pending.value = true
  error.value = null
  try {
    // Backend'den paginated veriyi çek
    const response = await $fetch('/api/reviews/my-reviews', {
      query: { page: page }
    })
    
    reviews.value = response.data
    meta.value = response.meta
    
  } catch (err) {
    console.error('Yorumlar yüklenemedi:', err)
    error.value = 'Veri yüklenemedi.'
  } finally {
    pending.value = false
  }
}

// Tarih formatlama
const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('tr-TR', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
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