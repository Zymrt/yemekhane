<template>
  <!-- Sayfayı user layout ile sar ve named slot'ları layout'a geçir -->
  <NuxtLayout name="user">
    <!-- 🔹 Navbar butonları -->
    <template #left-buttons>
      <NuxtLink to="/menu" class="btn btn-ghost-active"> <!-- Aktif sayfa vurgusu -->
        Ana Sayfa
      </NuxtLink>
      <NuxtLink to="/yorumlar" class="btn btn-ghost">
        Değerlendirmeler
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
    <div class="grid grid-cols-1 md:grid-cols-3 gap-10 mt-6">
      
      <!-- 👤 Profil Bilgileri (Burası Değişmedi) -->
      <div
        class="md:col-span-1 bg-white/30 backdrop-blur-2xl border border-white/30
               rounded-3xl p-6 shadow-lg hover:shadow-2xl transform hover:-translate-y-1
               transition-all duration-300"
      >
        <h2 class="text-2xl font-bold text-gray-900 mb-4 drop-shadow-sm">Profil Bilgileri</h2>
        <div class="space-y-3 text-gray-800">
          <p><strong>Ad Soyad:</strong> {{ user?.name }} {{ user?.surname }}</p>
          <p><strong>Birim:</strong> {{ user?.unit }}</p>
          <p><strong>Telefon:</strong> {{ user?.phone || '-' }}</p>
          <p><strong>Kayıt Tarihi:</strong> {{ formatDate(user?.created_at) }}</p>
          <p class="mt-3">
            <strong>Bakiye: </strong>
            <!-- Bakiye değişikliğini anında görmek için 'user.balance' kullandık -->
            <span class="text-emerald-600 font-bold text-lg">{{ user?.balance?.toFixed(2) || '0.00' }} ₺</span>
          </p>
        </div>
      </div>

      <!-- Sağ Sütun (Menü ve Yorumlar için bir sarmalayıcı) -->
      <div class="md:col-span-2 space-y-10"> <!-- 'classs' yazım hatası düzeltildi -->
        
        <!-- 🌟 YENİ: SATIN ALMA KARTI 🌟 -->
        <!-- Sadece menü varsa, yüklenmiyorsa ve henüz satın alınmadıysa göster -->
        <div 
          v-if="!pending && reviewData?.menu && !reviewData.has_order"
          class="bg-white/30 backdrop-blur-2xl border border-white/30
                 rounded-3xl p-6 shadow-lg hover:shadow-2xl transform hover:-translate-y-1
                 transition-all duration-300"
        >
          <div class="flex flex-col sm:flex-row items-center justify-between gap-4">
            <div>
              <h3 class="text-2xl font-bold text-gray-900 drop-shadow-sm">Menüyü Satın Al</h3>
              <p class="text-gray-700">
                Bugünün menü fiyatı: 
                <!-- 🌟 DEĞİŞİKLİK BURADA: Artık dinamik fiyatı okuyor -->
                <span class="font-bold text-emerald-700">{{ mealPrice.toFixed(2) }} ₺</span>
              </p>
            </div>
            
            <!-- Satın Al Butonu -->
            <button 
              @click="purchaseMenu" 
              :disabled="purchaseState.loading"
              class="btn btn-primary w-full sm:w-auto py-3 px-6 text-lg disabled:opacity-50"
            >
              {{ purchaseState.loading ? 'İşleniyor...' : 'Hemen Satın Al' }}
            </button>
          </div>
          <!-- Satın Alma Hata Mesajı -->
          <p v-if="purchaseState.error" class="text-red-700 font-medium mt-3 text-center sm:text-left">
            Hata: {{ purchaseState.error }}
          </p>
        </div>
        
        <!-- 🌟 YENİ: Zaten Satın Alınmış Kartı 🌟 -->
        <div
          v-if="!pending && reviewData.has_order"
          class="bg-emerald-100/50 backdrop-blur-2xl border border-emerald-300
                 rounded-3xl p-6 shadow-lg text-center"
        >
          <p class="text-2xl font-semibold text-emerald-800 drop-shadow-sm">
            ✅ Bugünün menüsü satın alındı.
          </p>
          <p class="text-emerald-700">Afiyet olsun! Yorum yapmayı unutmayın.</p>
        </div>


        <!-- 🍲 Günlük Menü -->
        <div
          class="bg-white/30 backdrop-blur-2xl border border-white/30
                 rounded-3xl p-6 shadow-lg hover:shadow-2xl transform hover:-translate-y-1
                 transition-all duration-300"
        >
          <div class="flex flex-wrap gap-3 items-center justify-between mb-4">
            <h2 class="text-2xl font-bold text-gray-900 drop-shadow-sm">Bugünün Menüsü</h2>
            <span class="text-sm font-medium text-gray-700">
              {{ reviewData?.menu?.date ? formatDate(reviewData.menu.date) : '' }}
            </span>
          </div>

          <!-- API Çağrısı Yüklenirken -->
          <div v-if="pending" class="text-center text-gray-600 py-6">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-gray-700 mb-2"></div>
            <p>Menü yükleniyor...</p>
          </div>
          <!-- API Hatası -->
          <div v-else-if="error" class="text-red-600 text-center py-6">Menü yüklenemedi.</div>
          
          <!-- Menü Başarıyla Geldiyse -->
          <ul v-else-if="reviewData?.menu?.items?.length > 0" class="divide-y divide-white/50">
            <li
              v-for="(item, index) in reviewData.menu.items"
              :key="index"
              class="py-3 flex justify-between items-center text-gray-800"
            >
              <!-- 🌟 DEĞİŞİKLİK: 'item' yerine 'item.name' yazıldı -->
              <span class="font-medium">{{ item.name }}</span>
            </li>
          </ul>

          <!-- Menü Boş Gelirse -->
          <div v-else class="text-center text-gray-600 py-6">
            Bugün için menü bulunamadı 🍽️
          </div>
        </div>

        <!-- 🌟 YORUM ALANI (Mevcuttu, yeri değişti) 🌟 -->
        <!-- Sadece menü yüklendiyse bu kartı göster -->
        <div 
          v-if="!pending && reviewData?.menu"
          class="bg-white/30 backdrop-blur-2xl border border-white/30
                 rounded-3xl p-6 shadow-lg hover:shadow-2xl transform hover:-translate-y-1
                 transition-all duration-300"
        >
          <h2 class="text-2xl font-bold text-gray-900 mb-4 drop-shadow-sm">Menüyü Değerlendir</h2>

          <!-- Durum: Yorum Gönderiliyor -->
          <div v-if="reviewState.loading" class="text-center text-gray-700 py-4">
            Yorumunuz gönderiliyor...
          </div>

          <!-- Durum: Başarılı veya Zaten Yorum Yapmış -->
          <div v-else-if="reviewState.success || reviewData.already" 
               class="p-4 rounded-xl bg-emerald-100 border border-emerald-300 text-center">
            <div class="text-3xl mb-2">✅</div>
            <p class="font-semibold text-emerald-800">
              {{ reviewState.success ? 'Yorumunuz alındı, teşekkürler!' : 'Bu menüyü zaten değerlendirdiniz.' }}
            </p>
            <p v-if="reviewData.my_review" class="text-sm text-emerald-700 mt-1">
              Verdiğiniz Puan: {{ reviewData.my_review.rating }} Yıldız
            </p>
          </div>
          
          <!-- Durum: Satın Almamış (Yorum için) -->
          <div v-else-if="!reviewData.has_order"
               class="p-4 rounded-xl bg-rose-100 border border-rose-300 text-center text-rose-800 font-medium">
            Yorum yapabilmek için bugünün menüsünü satın almış olmanız gerekmektedir.
          </div>

          <!-- Durum: Yorum Saati Gelmemiş -->
          <div v-else-if="!reviewData.after_start"
               class="p-4 rounded-xl bg-sky-100 border border-sky-300 text-center text-sky-800 font-medium">
            Değerlendirmeler {{ reviewData.review_start_raw }} itibarıyla başlayacaktır.
          </div>

          <!-- Durum: Yorum Yapabilir (Formu Göster) -->
          <div v-else-if="reviewData.can_review" class="space-y-4">
            <!-- Yıldız Puanlama -->
            <div class="flex items-center gap-2">
              <span class="text-gray-800 font-medium">Puanınız:</span>
              <div class="flex">
                <button
                  v-for="star in 5"
                  :key="star"
                  @click="reviewForm.rating = star"
                  type="button"
                  class="text-3xl focus:outline-none transition-transform hover:scale-110 active:scale-95"
                >
                  <span :class="star <= reviewForm.rating ? 'text-yellow-400' : 'text-gray-400'">★</span>
                </button>
              </div>
            </div>

            <!-- Yorum Kutusu -->
            <textarea
              v-model="reviewForm.comment"
              rows="3"
              class="w-full border border-white/50 rounded-xl p-3 text-gray-800
                     bg-white/50 placeholder-gray-600
                     focus:outline-none focus:ring-2 focus:ring-orange-500"
              placeholder="Menü hakkındaki düşünceleriniz (isteğe bağlı)..."
            ></textarea>

            <!-- Hata Mesajı -->
            <p v-if="reviewState.error" class="text-red-700 font-medium">
              Hata: {{ reviewState.error }}
            </p>

            <!-- Gönder Butonu -->
            <button @click="submitReview" class="btn btn-primary w-full py-3">
              Değerlendirmeyi Gönder
            </button>
          </div>
          
        </div>

      </div> <!-- Sağ Sütun Bitişi -->
    </div>
  </NuxtLayout>
</template>

<script setup>
import { ref, reactive, computed } from 'vue' // 'computed' eklendi
import useAuth from '../composables/useAuth'
import protectUserPage from '../composables/protectUserPage'

// Sayfa koruması ve kullanıcı bilgileri
await protectUserPage()
const { user } = useAuth()

// API'den gelen tüm veriyi (menü + yorum durumu) tutar
const { data: reviewData, pending, error, refresh } = await useFetch('/api/reviews/today')

// --- YENİ EKLENDİ: Satın Alma State ---
const purchaseState = reactive({
  loading: false,
  error: null
})

// Yorum formu ve gönderme durumu için state'ler
const reviewForm = reactive({
  rating: 5,
  comment: ''
})

const reviewState = reactive({
  loading: false,
  success: false,
  error: null
})

// --- 🌟 DEĞİŞİKLİK: YEMEK FİYATI DİNAMİK HALE GETİRİLDİ 🌟 ---
const mealPrice = computed(() => {
  // 1. Kullanıcının özel fiyatı var mı? (useAuth'tan gelen 'user' objesi)
  // (Not: 'user.value.meal_price' null veya 0 ise bir sonraki adıma geçer)
  if (user.value && user.value.meal_price) {
    return parseFloat(user.value.meal_price);
  }
  
  // 2. Menünün kendi özel fiyatı var mı? (API'den gelen 'reviewData')
  if (reviewData.value && reviewData.value.menu && reviewData.value.menu.price) {
    return parseFloat(reviewData.value.menu.price);
  }
  
  // 3. Hiçbiri yoksa, .env'deki varsayılan fiyatı kullan (VITE_MEAL_PRICE)
  // (OrderController'daki varsayılan ile aynı olmalı)
  return parseFloat(import.meta.env.VITE_MEAL_PRICE || 50.0); 
})


// --- YENİ EKLENDİ: Satın Alma Fonksiyonu ---
async function purchaseMenu() {
  purchaseState.loading = true
  purchaseState.error = null

  try {
    const response = await $fetch('/api/order/purchase', {
      method: 'POST'
      // Token (cookie) otomatik olarak $fetch ile gider
    })
    
    // Satın alma başarılı
    // 1. Auth composable'daki bakiye bilgisini (Profil Kartı) anında güncelle
    if (user.value) {
      user.value.balance = response.new_balance
    }
    
    // 2. Sayfadaki veriyi yenile (API'den "has_order: true" gelsin)
    // Bu, "Satın Al" butonunu gizleyip "Yorum Yap" formunu gösterecek.
    await refresh()

  } catch (err) {
    // Yetersiz bakiye (402) veya zaten alınmış (400) gibi hataları yakala
    purchaseState.error = err.data?.message || 'İşlem başarısız oldu.'
  } finally {
    purchaseState.loading = false
  }
}


// Yorum gönderme fonksiyonu (Mevcuttu)
async function submitReview() {
  const menuId = reviewData.value?.menu?._id || reviewData.value?.menu?.id
  if (!menuId) {
    reviewState.error = 'Menü ID bulunamadı.'
    return
  }

  reviewState.loading = true
  reviewState.error = null

  try {
    await $fetch('/api/reviews', {
      method: 'POST',
      body: {
        menu_id: menuId,
        rating: reviewForm.rating,
        comment: reviewForm.comment
      }
    })
    
    reviewState.success = true
    // Yorum yaptıktan sonra sayfayı yenileyerek "already: true" durumunu al
    await refresh()
    
  } catch (err) {
    reviewState.error = err.data?.message || 'Yorum gönderilemedi.'
  } finally {
    reviewState.loading = false
  }
}

// Tarih formatlama fonksiyonu (Mevcuttu)
const formatDate = (dateStr) => {
  if (!dateStr) return '-'
  return new Date(dateStr).toLocaleDateString('tr-TR', {
    day: 'numeric',
    month: 'long',
    year: 'numeric'
  })
}
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
/* 🌟 YENİ: Navbar için "aktif ghost" */
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

/* İçerik alanındaki yumuşak buton */
.btn-soft {
  @apply text-sky-900 bg-white/70 hover:bg-white/90 border border-white/80
         backdrop-blur-sm rounded-xl;
}

/* Küçük ekranlarda butonların nefes alması için */
@media (max-width: 768px) {
  .btn { @apply text-sm px-3 py-2; }
}
</style>