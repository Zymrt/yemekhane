<template>
  <NuxtLayout name="user">
    <!-- Üst Navigasyon Butonları -->
    <template #left-buttons>
      <NuxtLink to="/menu" class="btn btn-ghost-active">
        <i class="i-lucide-home mr-2"></i> Ana Sayfa
      </NuxtLink>
      <NuxtLink to="/yorumlar" class="btn btn-ghost">
        <i class="i-lucide-star mr-2"></i> Değerlendirmeler
      </NuxtLink>
    </template>

    <template #right-buttons>
      <NuxtLink to="/hesap-hareketleri" class="btn btn-outline">
        <i class="i-lucide-history mr-2"></i> Hareketler
      </NuxtLink>
      <NuxtLink to="/bakiye" class="btn btn-primary shadow-lg shadow-orange-500/20">
        <i class="i-lucide-wallet mr-2"></i> Bakiye Yükle
      </NuxtLink>
    </template>

    <div class="grid grid-cols-1 lg:grid-cols-12 gap-8 mt-8 pb-10">
      
      <!-- 🟢 SOL SÜTUN (Sidebar - 4/12) -->
      <div class="lg:col-span-4 space-y-6">
        
        <!-- 1. PROFİL KARTI -->
        <div class="glass-card p-6 relative overflow-hidden group">
          <!-- Arka plan dekorasyonu -->
          <div class="absolute top-0 right-0 p-4 opacity-10 group-hover:opacity-20 transition-opacity">
            <i class="i-lucide-user-circle text-8xl text-white transform translate-x-4 -translate-y-4"></i>
          </div>

          <h2 class="text-xl font-bold text-gray-900 mb-6 flex items-center gap-2 relative z-10">
            <span class="w-1.5 h-6 bg-purple-500 rounded-full"></span>
            Profilim
          </h2>

          <div class="space-y-4 relative z-10">
            <div>
              <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">Ad Soyad</p>
              <p class="text-lg font-semibold text-gray-800">{{ user?.name }} {{ user?.surname }}</p>
            </div>
            
            <div class="grid grid-cols-2 gap-4">
              <div>
                <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">Birim</p>
                <p class="text-sm font-medium text-gray-700 truncate" :title="user?.unit">{{ user?.unit || '-' }}</p>
              </div>
              <div>
                <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">Telefon</p>
                <p class="text-sm font-medium text-gray-700">{{ user?.phone || '-' }}</p>
              </div>
            </div>

            <!-- 🆕 EKLENEN BLOK: E-POSTA -->
            <div>
              <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">E-posta</p>
              <p class="text-sm font-medium text-gray-700 break-all">{{ user?.email || '-' }}</p>
            </div>

            <div class="pt-4 mt-2 border-t border-gray-200/40">
              <p class="text-xs text-gray-500 uppercase font-bold tracking-wider mb-1">Cüzdan Bakiyesi</p>
              <p 
                class="text-3xl font-black transition-all duration-700"
                :class="balanceHighlight ? 'text-green-500 scale-105' : 'text-emerald-600'"
              >
                {{ user?.balance?.toFixed(2) || '0.00' }} <span class="text-lg text-emerald-600/70">₺</span>
              </p>
            </div>
          </div>
        </div>

        <!-- 2. CANLI DOLULUK ORANI -->
        <div class="glass-card p-5">
          <div class="flex justify-between items-center mb-3">
            <h3 class="font-bold text-gray-900 flex items-center gap-2 text-sm">
              <span class="relative flex h-2.5 w-2.5">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-500"></span>
              </span>
              Yemekhane Yoğunluğu
            </h3>
            <span class="text-xs font-bold bg-white/50 text-gray-700 px-2 py-1 rounded border border-gray-200/50">
              %{{ occupancyRate.toFixed(0) }}
            </span>
          </div>
          
          <div class="h-2.5 w-full bg-gray-200/50 rounded-full overflow-hidden">
            <div 
              class="h-full bg-gradient-to-r from-emerald-400 via-yellow-400 to-red-500 transition-all duration-1000 ease-out shadow-[0_0_10px_rgba(239,68,68,0.3)]"
              :style="`width: ${occupancyRate}%`"
            ></div>
          </div>
          <p class="text-[10px] text-gray-500 mt-2 text-right">Anlık sensör verisi</p>
        </div>

        <!-- 3. DUYURULAR -->
        <div v-if="announcements.length > 0" class="glass-card p-5 max-h-[400px] flex flex-col">
          <h2 class="text-lg font-bold text-gray-900 mb-4 flex items-center gap-2 sticky top-0 bg-transparent z-10">
            <i class="i-lucide-bell-ring text-orange-500"></i> Duyurular
          </h2>
          
          <div class="space-y-3 overflow-y-auto custom-scrollbar pr-2 flex-1">
            <div 
              v-for="ann in announcements" 
              :key="ann._id" 
              class="bg-white/40 border-l-4 border-orange-400 rounded-r-lg p-3 hover:bg-white/70 transition-colors shadow-sm"
            >
              <div class="flex justify-between items-start mb-1">
                <h3 class="font-bold text-gray-800 text-xs uppercase tracking-wide line-clamp-1">{{ ann.title }}</h3>
                <span class="text-[9px] text-gray-400 whitespace-nowrap ml-2">
                  {{ new Date(ann.created_at).toLocaleDateString('tr-TR') }}
                </span>
              </div>
              <p class="text-gray-600 text-xs leading-relaxed line-clamp-3">{{ ann.content }}</p>
            </div>
          </div>
        </div>

      </div>

      <!-- 🔵 SAĞ SÜTUN (İçerik - 8/12) -->
      <div class="lg:col-span-8 space-y-6">
        
        <!-- ÜST BİLGİ & CTA KARTI (HERO) -->
        <div class="glass-card p-0 overflow-hidden relative min-h-[140px] flex flex-col justify-center">
          <div class="absolute top-0 right-0 w-64 h-64 bg-gradient-to-br from-orange-400/30 to-purple-500/30 rounded-full blur-3xl -mr-10 -mt-10 pointer-events-none"></div>
          
          <div class="p-6 sm:p-8 flex flex-col md:flex-row items-center justify-between gap-6 relative z-10">
            <div class="text-center md:text-left">
              <h2 class="text-3xl font-black text-gray-800 mb-1 tracking-tight">
                Bugünün Menüsü
              </h2>
              <p class="text-gray-500 text-sm flex items-center justify-center md:justify-start gap-2 font-medium">
                <i class="i-lucide-calendar text-purple-500"></i>
                {{ reviewData?.menu?.date ? formatDate(reviewData.menu.date) : 'Tarih Bekleniyor' }}
              </p>
            </div>

            <!-- Satın Alma Butonları -->
            <div class="flex flex-col items-center md:items-end gap-2 w-full md:w-auto">
              
              <!-- Durum 1: Zaten Alınmış -->
              <div v-if="!pending && reviewData?.has_order" class="text-center md:text-right animate-pulse-slow">
                <div class="inline-flex items-center gap-2 px-6 py-3 bg-emerald-100 text-emerald-700 rounded-2xl font-bold border border-emerald-200 shadow-sm">
                  <i class="i-lucide-check-circle text-xl"></i> Menü Satın Alındı
                </div>
              </div>

              <!-- Durum 2: Henüz Alınmamış -->
              <div v-else-if="reviewData?.menu" class="w-full md:w-auto flex flex-col items-center md:items-end">
                <div class="text-center md:text-right mb-1">
                  <span class="text-[10px] text-gray-400 uppercase font-bold tracking-widest">ÖDENECEK TUTAR</span>
                  <div class="text-3xl font-black text-gray-800 tracking-tighter leading-none">
                    {{ mealPrice.toFixed(2) }}<span class="text-lg text-gray-400 align-top ml-0.5">₺</span>
                  </div>
                </div>
                
                <button 
                  @click="purchaseMenu" 
                  :disabled="purchaseState.loading || isCutoffTimePassed"
                  class="btn w-full md:w-auto py-3 px-10 text-base shadow-xl shadow-orange-500/30 disabled:opacity-50 disabled:cursor-not-allowed transform hover:-translate-y-1 transition-all mt-2"
                  :class="isCutoffTimePassed ? 'bg-slate-500 text-white' : 'btn-primary'"
                >
                  <span v-if="isCutoffTimePassed">Süre Doldu (12:00)</span>
                  <span v-else>{{ purchaseState.loading ? 'İşleniyor...' : 'Hemen Satın Al' }}</span>
                </button>
                
                <p v-if="purchaseState.error" class="text-red-600 text-xs mt-2 font-bold bg-red-50 px-2 py-1 rounded border border-red-100">
                  {{ purchaseState.error }}
                </p>
              </div>
            </div>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            
            <!-- MENÜ LİSTESİ -->
            <div class="glass-card p-6 min-h-[300px] flex flex-col">
              <h3 class="font-bold text-gray-900 mb-5 flex items-center gap-2 border-b border-gray-200/50 pb-3">
                <i class="i-lucide-utensils text-purple-500"></i> Yemek Listesi
              </h3>

              <div v-if="pending" class="flex-1 flex flex-col items-center justify-center text-gray-400">
                <div class="animate-spin rounded-full h-8 w-8 border-2 border-current border-t-transparent mb-2"></div>
                <span class="text-xs">Yükleniyor...</span>
              </div>
              
              <div v-else-if="error || !reviewData?.menu?.items" class="flex-1 flex flex-col items-center justify-center text-gray-400 bg-gray-50/50 rounded-xl border border-dashed border-gray-200 mx-2 mb-2">
                <i class="i-lucide-chef-hat text-4xl mb-2 opacity-30"></i>
                <span class="text-sm font-medium">Bugün için menü bulunamadı</span>
              </div>

              <ul v-else class="space-y-3">
                <li 
                  v-for="(item, index) in reviewData.menu.items" 
                  :key="index"
                  class="flex justify-between items-center p-3 rounded-xl bg-white/50 border border-white/60 hover:bg-white/80 transition-all shadow-sm group"
                >
                  <div class="flex items-center gap-3">
                    <span class="w-7 h-7 rounded-lg bg-orange-100 text-orange-600 flex items-center justify-center text-xs font-bold group-hover:bg-orange-500 group-hover:text-white transition-colors">{{ index + 1 }}</span>
                    <span class="font-semibold text-gray-800 text-sm">{{ item.name }}</span>
                  </div>
                  <span v-if="item.calorie" class="text-[10px] font-bold text-gray-500 bg-gray-100 px-2 py-1 rounded-md border border-gray-200">
                    {{ item.calorie }} kcal
                  </span>
                </li>
              </ul>
            </div>

            <!-- QR KOD / DURUM KARTI -->
            <div class="glass-card p-6 flex flex-col items-center justify-center text-center relative overflow-hidden">
               <!-- Dekoratif Arka Plan -->
               <div class="absolute inset-0 bg-gradient-to-tr from-blue-500/5 to-purple-500/5 pointer-events-none"></div>

               <div v-if="reviewData?.has_order" class="relative z-10 w-full flex flex-col items-center">
                  <h3 class="font-bold text-gray-900 mb-4 flex items-center justify-center gap-2">
                    <i class="i-lucide-qr-code text-blue-500"></i> Dijital Kartınız
                  </h3>
                  
                  <div class="bg-white p-4 rounded-3xl shadow-xl border-4 border-blue-50 inline-block transform hover:scale-105 transition-transform duration-300">
                    <QrcodeVue :value="user?._id || user?.id" :size="160" level="H" background="#ffffff" foreground="#000000" />
                  </div>
                  
                  <div class="mt-6 flex flex-col gap-1">
                    <span class="text-xs font-bold text-blue-600 bg-blue-50 px-3 py-1 rounded-full border border-blue-100">
                      Görevliye gösteriniz!
                    </span>
                    <span class="text-[10px] text-gray-400">Okuyucuya gösteriniz</span>
                  </div>
               </div>

               <!-- KİLİTLİ QR -->
               <div v-else class="flex flex-col items-center justify-center h-full py-8 relative z-10">
                  <div class="w-20 h-20 bg-gray-100 rounded-full flex items-center justify-center mb-4 shadow-inner border border-gray-200">
                    <i class="i-lucide-lock text-gray-400 text-3xl"></i>
                  </div>
                  <h3 class="text-gray-900 font-bold mb-2">QR Kod Kilitli</h3>
                  <p class="text-xs text-gray-500 max-w-[220px] leading-relaxed">
                    Yemekhaneye giriş için gerekli QR kodu, <strong>menüyü satın aldığınızda</strong> burada görünecektir.
                  </p>
               </div>
            </div>
        </div>

        <!-- YORUM YAPMA ALANI -->
        <div v-if="!pending && reviewData?.menu" class="glass-card p-6">
           <h3 class="font-bold text-gray-900 mb-4 flex items-center gap-2">
              <i class="i-lucide-message-circle text-pink-500"></i> Değerlendirme
           </h3>

           <!-- Yorum Yapıldıysa -->
           <div v-if="reviewState.success || reviewData.already" class="bg-emerald-50 border border-emerald-100 rounded-2xl p-6 flex flex-col sm:flex-row items-center gap-4 text-center sm:text-left">
              <div class="w-12 h-12 rounded-full bg-emerald-100 flex items-center justify-center text-emerald-600 text-2xl shadow-sm">🎉</div>
              <div>
                <h4 class="font-bold text-emerald-800 text-base">Teşekkürler!</h4>
                <p class="text-sm text-emerald-600">Bu menü için değerlendirmeniz sistemimize kaydedildi.</p>
              </div>
           </div>

           <!-- Satın Alınmadıysa -->
           <div v-else-if="!reviewData.has_order" class="bg-gray-50 border border-gray-100 rounded-xl p-4 text-center text-gray-500 text-sm italic">
              Menüyü değerlendirmek için önce satın almalısınız.
           </div>

           <!-- Değerlendirme Formu -->
           <div v-else-if="reviewData.can_review" class="flex flex-col gap-4">
              <div class="flex items-center justify-between bg-white/40 p-3 rounded-xl border border-white/60">
                <span class="text-sm font-bold text-gray-600 ml-2">Puanınız:</span>
                <div class="flex gap-1">
                  <button v-for="star in 5" :key="star" @click="reviewForm.rating = star" class="text-3xl hover:scale-110 transition-transform focus:outline-none px-1">
                     <span :class="star <= reviewForm.rating ? 'text-yellow-400 drop-shadow-md' : 'text-gray-300'">★</span>
                  </button>
                </div>
              </div>
              
              <div class="relative">
                <textarea 
                  v-model="reviewForm.comment" 
                  rows="2"
                  placeholder="Yemek nasıldı? Düşüncelerinizi bizimle paylaşın..." 
                  class="w-full bg-white/50 border border-white/60 rounded-xl px-4 py-3 text-sm focus:outline-none focus:ring-2 focus:ring-purple-500/50 focus:bg-white transition-all placeholder-gray-400 resize-none"
                ></textarea>
                <button 
                  @click="submitReview" 
                  :disabled="reviewState.loading"
                  class="absolute right-2 bottom-2 px-4 py-1.5 bg-gray-900 text-white text-xs font-bold rounded-lg hover:bg-gray-800 transition-colors disabled:opacity-50 shadow-md"
                >
                  {{ reviewState.loading ? '...' : 'Gönder' }}
                </button>
              </div>
              <p v-if="reviewState.error" class="text-red-500 text-xs font-medium bg-red-50 p-2 rounded-lg border border-red-100 text-center">{{ reviewState.error }}</p>
           </div>

           <!-- 🕒 Satın alındı ama değerlendirme saati gelmediyse -->
           <div v-else class="bg-amber-50 border border-amber-100 rounded-xl p-4 text-center text-amber-700 text-sm">
             Bu menü için değerlendirme özelliği <strong>12:00'den sonra</strong> açılacaktır.
           </div>
        </div>

      </div>
    </div>
  </NuxtLayout>
</template>

<script setup>
import { reactive, computed, ref, onMounted } from 'vue'
import useAuth from '../composables/useAuth'
import protectUserPage from '../composables/protectUserPage'
import QrcodeVue from 'qrcode.vue' 
import { io } from 'socket.io-client' 

await protectUserPage()

const { user } = useAuth()
const { data: reviewData, pending, error, refresh } = await useFetch('/api/reviews/today')

const purchaseState = reactive({ loading: false, error: null })
const reviewForm = reactive({ rating: 5, comment: '' })
const reviewState = reactive({ loading: false, success: false, error: null })
const announcements = ref([])
const occupancyRate = ref(0)
const balanceHighlight = ref(false) // Bakiye animasyonu için

// 📢 Duyuruları Çek
const fetchAnnouncements = async () => {
  try {
    announcements.value = await $fetch('/api/announcements')
  } catch (e) {
    console.error("Duyurular alınamadı")
  }
}

// ⏰ Saat Kontrolü (12:00)
const isCutoffTimePassed = computed(() => {
  const now = new Date()
  return now.getHours() >= 12 
})

// Fiyat Hesaplama
const mealPrice = computed(() => {
  if (user.value && user.value.meal_price !== undefined && user.value.meal_price !== null) {
    return parseFloat(user.value.meal_price);
  }
  if (reviewData.value?.menu?.price) {
    return parseFloat(reviewData.value.menu.price);
  }
  return parseFloat(import.meta.env.VITE_MEAL_PRICE || 50.0); 
})

async function purchaseMenu() {
  if (isCutoffTimePassed.value) {
    alert('Üzgünüz, yemek sipariş saati (12:00) dolmuştur.')
    return
  }
  purchaseState.loading = true;
  purchaseState.error = null;
  try {
    const response = await $fetch('/api/order/purchase', { method: 'POST' });
    if (user.value) user.value.balance = response.new_balance;
    await refresh();
  } catch (err) {
    purchaseState.error = err.data?.message || 'İşlem başarısız oldu.';
  } finally {
    purchaseState.loading = false;
  }
}

async function submitReview() {
  const menuId = reviewData.value?.menu?._id || reviewData.value?.menu?.id;
  if (!menuId) { reviewState.error = 'Menü ID bulunamadı.'; return; }
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
    day: 'numeric', month: 'long', year: 'numeric', weekday: 'long'
  });
}

onMounted(async () => {
  // 1. Verileri yükle
  await fetchAnnouncements()

  // 2. 🔥 SOCKET BAĞLANTISI
  const socket = io('http://localhost:3001') 

  // A) Doluluk Dinle
  socket.on('occupancy_update', (data) => {
    occupancyRate.value = data.percentage
  })

  // B) Yeni Duyuru Dinle
  socket.on('new_announcement', () => {
    fetchAnnouncements() 
  })

  // C) Duyuru Silindiğinde
  socket.on('announcement_deleted', (data) => {
    announcements.value = announcements.value.filter(ann => ann._id !== data.id && ann.id !== data.id)
  })

  // D) 🔥 YENİ: Menü Güncellendiğinde
  socket.on('menu_updated', () => {
    console.log('Menü güncellendi, yenileniyor...')
    refresh()
  })

  // E) 🔥 YENİ: Bakiye Güncellendiğinde (ID Kontrolü ile)
  socket.on('balance_updated', (data) => {
    const currentUserId = user.value?._id || user.value?.id;
    // Güvenli String karşılaştırması
    if (currentUserId && String(currentUserId) === String(data.user_id)) {
      console.log('Bakiye Eşleşti! Güncelleniyor...', data.new_balance)
      
      user.value.balance = parseFloat(data.new_balance)
      
      // ✨ Yeşil yanıp sönme efekti
      balanceHighlight.value = true
      setTimeout(() => { balanceHighlight.value = false }, 2000)
    }
  })
})
</script>

<style scoped>
/* Glass Effect Helper Class */
.glass-card {
  @apply bg-white/40 backdrop-blur-xl border border-white/50 rounded-[2rem] shadow-lg transition-all duration-300 hover:shadow-xl hover:bg-white/50;
}

/* Buttons */
.btn {
  @apply inline-flex items-center justify-center px-4 py-2 rounded-xl font-bold transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-offset-1 active:scale-95;
}
.btn-ghost { @apply text-white/80 hover:text-white hover:bg-white/10; }
.btn-ghost-active { @apply text-white bg-white/20 border border-white/10 shadow-inner; }
.btn-outline { @apply text-white border border-white/30 hover:bg-white/10; }
.btn-primary { 
  @apply text-white bg-gradient-to-r from-orange-500 to-orange-600 hover:brightness-110 shadow-md border-t border-orange-400/50; 
}

/* Custom Scrollbar */
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: rgba(0,0,0,0.1); border-radius: 10px; }
.custom-scrollbar::-webkit-scrollbar-thumb:hover { background: rgba(0,0,0,0.2); }

@keyframes pulse-slow {
  0%, 100% { opacity: 1; }
  50% { opacity: 0.8; }
}
.animate-pulse-slow {
  animation: pulse-slow 3s cubic-bezier(0.4, 0, 0.6, 1) infinite;
}
</style>
