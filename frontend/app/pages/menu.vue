<template>
  <NuxtLayout name="user">
    <template #left-buttons>
      <NuxtLink to="/menu" class="btn btn-ghost-active">
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

    <div class="grid grid-cols-1 md:grid-cols-3 gap-10 mt-6">
      
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
          
          <p class="text-xs text-gray-500">Tanımlı Yemek Ücreti: {{ user?.meal_price }} TL</p>
          
          <p class="mt-3">
            <strong>Bakiye: </strong>
            <span class="text-emerald-600 font-bold text-lg">{{ user?.balance?.toFixed(2) || '0.00' }} ₺</span>
          </p>
        </div>
      </div>

      <div class="md:col-span-2 space-y-10">
        
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
                <span class="font-bold text-emerald-700">{{ mealPrice.toFixed(2) }} ₺</span>
              </p>
            </div>
            
            <button 
              @click="purchaseMenu" 
              :disabled="purchaseState.loading"
              class="btn btn-primary w-full sm:w-auto py-3 px-6 text-lg disabled:opacity-50"
            >
              {{ purchaseState.loading ? 'İşleniyor...' : 'Hemen Satın Al' }}
            </button>
          </div>
          <p v-if="purchaseState.error" class="text-red-700 font-medium mt-3 text-center sm:text-left">
            Hata: {{ purchaseState.error }}
          </p>
        </div>
        
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

          <div v-if="pending" class="text-center text-gray-600 py-6">
            <div class="inline-block animate-spin rounded-full h-8 w-8 border-t-2 border-b-2 border-gray-700 mb-2"></div>
            <p>Menü yükleniyor...</p>
          </div>
          <div v-else-if="error" class="text-red-600 text-center py-6">Menü yüklenemedi.</div>
          
          <ul v-else-if="reviewData?.menu?.items?.length > 0" class="divide-y divide-white/50">
            <li
              v-for="(item, index) in reviewData.menu.items"
              :key="index"
              class="py-3 flex justify-between items-center text-gray-800"
            >
              <span class="font-medium">{{ item.name }}</span>
              <span class="text-sm text-gray-500">{{ item.calorie ? item.calorie + ' cal' : '' }}</span>
            </li>
          </ul>

          <div v-else class="text-center text-gray-600 py-6">
            Bugün için menü bulunamadı 🍽️
          </div>
        </div>

        <div 
          v-if="!pending && reviewData?.menu"
          class="bg-white/30 backdrop-blur-2xl border border-white/30
                 rounded-3xl p-6 shadow-lg hover:shadow-2xl transform hover:-translate-y-1
                 transition-all duration-300"
        >
          <h2 class="text-2xl font-bold text-gray-900 mb-4 drop-shadow-sm">Menüyü Değerlendir</h2>

          <div v-if="reviewState.loading" class="text-center text-gray-700 py-4">
            Yorumunuz gönderiliyor...
          </div>

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
          
          <div v-else-if="!reviewData.has_order"
               class="p-4 rounded-xl bg-rose-100 border border-rose-300 text-center text-rose-800 font-medium">
            Yorum yapabilmek için bugünün menüsünü satın almış olmanız gerekmektedir.
          </div>

          <div v-else-if="!reviewData.after_start"
               class="p-4 rounded-xl bg-sky-100 border border-sky-300 text-center text-sky-800 font-medium">
            Değerlendirmeler {{ reviewData.review_start_raw }} itibarıyla başlayacaktır.
          </div>

          <div v-else-if="reviewData.can_review" class="space-y-4">
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

            <textarea
              v-model="reviewForm.comment"
              rows="3"
              class="w-full border border-white/50 rounded-xl p-3 text-gray-800
                     bg-white/50 placeholder-gray-600
                     focus:outline-none focus:ring-2 focus:ring-orange-500"
              placeholder="Menü hakkındaki düşünceleriniz (isteğe bağlı)..."
            ></textarea>

            <p v-if="reviewState.error" class="text-red-700 font-medium">
              Hata: {{ reviewState.error }}
            </p>

            <button @click="submitReview" class="btn btn-primary w-full py-3">
              Değerlendirmeyi Gönder
            </button>
          </div>
        </div>

      </div>
    </div>
  </NuxtLayout>
</template>

<script setup>
import { reactive, computed } from 'vue'
import useAuth from '../composables/useAuth'
import protectUserPage from '../composables/protectUserPage'

// Sayfa koruması
await protectUserPage()

// Kullanıcı verisini al (meal_price burada)
const { user } = useAuth()

// Menü ve Yorum Durumunu Çek
const { data: reviewData, pending, error, refresh } = await useFetch('/api/reviews/today')

const purchaseState = reactive({ loading: false, error: null })
const reviewForm = reactive({ rating: 5, comment: '' })
const reviewState = reactive({ loading: false, success: false, error: null })

// 🌟 DÜZELTİLMİŞ FİYAT HESAPLAMA MANTIĞI 🌟
const mealPrice = computed(() => {
  // 1. Kullanıcının özel fiyatı var mı?
  // user.value varsa VE meal_price undefined/null değilse (0 olabilir, 0'ı kabul etmeliyiz)
  if (user.value && user.value.meal_price !== undefined && user.value.meal_price !== null) {
    return parseFloat(user.value.meal_price);
  }
  
  // 2. Menüye özel fiyat var mı?
  if (reviewData.value?.menu?.price) {
    return parseFloat(reviewData.value.menu.price);
  }
  
  // 3. Hiçbiri yoksa varsayılan
  return parseFloat(import.meta.env.VITE_MEAL_PRICE || 50.0); 
})

async function purchaseMenu() {
  purchaseState.loading = true;
  purchaseState.error = null;
  try {
    const response = await $fetch('/api/order/purchase', { method: 'POST' });
    
    // Bakiye güncelle (Auth state'ini güncelle ki header'da da değişsin)
    if (user.value) {
      user.value.balance = response.new_balance;
    }
    // Verileri yenile (has_order: true olacak)
    await refresh();
  } catch (err) {
    purchaseState.error = err.data?.message || 'İşlem başarısız oldu.';
  } finally {
    purchaseState.loading = false;
  }
}

async function submitReview() {
  const menuId = reviewData.value?.menu?._id || reviewData.value?.menu?.id;
  if (!menuId) {
    reviewState.error = 'Menü ID bulunamadı.';
    return;
  }
  reviewState.loading = true;
  reviewState.error = null;
  try {
    await $fetch('/api/reviews', {
      method: 'POST',
      body: { menu_id: menuId, rating: reviewForm.rating, comment: reviewForm.comment }
    });
    reviewState.success = true;
    await refresh();
  } catch (err) {
    reviewState.error = err.data?.message || 'Yorum gönderilemedi.';
  } finally {
    reviewState.loading = false;
  }
}

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  return new Date(dateStr).toLocaleDateString('tr-TR', {
    day: 'numeric', month: 'long', year: 'numeric'
  });
}
</script>

<style scoped>
.btn {
  @apply inline-flex items-center justify-center px-4 py-2 rounded-xl font-semibold transition
         focus:outline-none focus:ring-2 focus:ring-offset-0 active:scale-[.99];
}
.btn-ghost { @apply text-white/90 hover:text-white bg-white/0 hover:bg-white/10 border border-white/10; }
.btn-ghost-active { @apply text-white bg-white/20 border border-white/20; }
.btn-outline { @apply text-white border border-white/40 bg-transparent hover:bg-white/10; }
.btn-primary { @apply text-white bg-gradient-to-r from-orange-500 via-orange-500 to-orange-600 hover:brightness-110 shadow-md; }
@media (max-width: 768px) { .btn { @apply text-sm px-3 py-2; } }
</style>