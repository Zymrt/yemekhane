<template>
  <!-- Sayfayı user layout ile sar -->
  <NuxtLayout name="user">
    <!-- 🔹 Navbar butonları (Aktif sayfayı vurgulamak için class eklendi) -->
    <template #left-buttons>
      <NuxtLink to="/menu" class="btn btn-ghost">
        ANA SAYFA
      </NuxtLink>
      <NuxtLink to="/yorumlar" class="btn btn-ghost">
        DEĞERLENDİRMELERİM
      </NuxtLink>
    </template>

    <template #right-buttons>
      <NuxtLink to="/hesap-hareketleri" class="btn btn-outline">
        HESAP HAREKETLERİ
      </NuxtLink>
      <NuxtLink to="/bakiye" class="btn btn-primary-active"> <!-- 🌟 Aktif -->
        BAKİYE YÜKLE
      </NuxtLink>
    </template>

    <!-- 💫 İçerik -->
    <div class="max-w-2xl mx-auto mt-6">
      <div 
        class="bg-white/30 backdrop-blur-2xl border border-white/30
               rounded-3xl p-8 shadow-lg"
      >
        <h1 class="text-3xl font-bold text-gray-900 mb-6 drop-shadow-sm text-center">
          Bakiye Yükle
        </h1>

        <!-- --------------------------------------- -->
        <!-- ADIM 1: Tutar Girme Ekranı -->
        <!-- --------------------------------------- -->
        <div v-if="currentStep === 1">
          <!-- Tutar Giriş Alanı -->
          <div class="mb-6">
            <label for="amount" class="block text-sm font-medium text-gray-800 mb-2">Yüklenecek Tutar (₺)</label>
            <input 
              type="number" 
              id="amount"
              v-model.number="amount"
              min="1"
              class="w-full border border-white/50 rounded-xl p-4 text-gray-800 text-2xl font-bold
                     bg-white/50 placeholder-gray-600
                     focus:outline-none focus:ring-2 focus:ring-orange-500"
              placeholder="0.00"
            >
          </div>

          <!-- Hızlı Tutar Butonları -->
          <div class="flex flex-wrap gap-3 mb-8">
            <button @click="amount = 50" class="btn btn-soft flex-1">50 ₺</button>
            <button @click="amount = 100" class="btn btn-soft flex-1">100 ₺</button>
            <button @click="amount = 250" class="btn btn-soft flex-1">250 ₺</button>
            <button @click="amount = 500" class="btn btn-soft flex-1">500 ₺</button>
          </div>

          <!-- İlerle Butonu -->
          <button 
            @click="goToPaymentStep"
            :disabled="amount <= 0"
            class="btn btn-primary w-full py-4 text-lg font-bold disabled:opacity-60"
          >
            Ödeme Adımına İlerle ({{ amount.toFixed(2) }} ₺)
          </button>
        </div>

        <!-- --------------------------------------- -->
        <!-- ADIM 2: Sahte Kart Formu Ekranı -->
        <!-- --------------------------------------- -->
        <div v-if="currentStep === 2">
          <!-- Sahte Banka Form Başlığı -->
          <div class="flex items-center justify-between mb-4">
            <span class="text-lg font-semibold text-gray-800">Sanal POS (Simülasyon)</span>
            <img src="https://placehold.co/100x30/004494/FFFFFF?text=İŞ+BANKASI" alt="İş Bankası" class="h-8 rounded">
          </div>
          
          <div class="space-y-4 p-4 border rounded-xl border-white/50 bg-white/20">
            
            <!-- 4'lü Kart Numarası Girişi -->
            <div>
              <label class="block text-sm font-medium text-gray-800">Kart Numarası (Sahte)</label>
              <div class="flex items-center gap-2 mt-1">
                <input 
                  type="text" 
                  :ref="el => inputs[0] = el"
                  @input="handleInput(0, $event)"
                  @keydown="handleKeydown(0, $event)"
                  maxlength="4" 
                  class="dummy-input text-center" 
                  placeholder="----"
                >
                <span class="text-gray-700 font-bold">-</span>
                <input 
                  type="text" 
                  :ref="el => inputs[1] = el"
                  @input="handleInput(1, $event)"
                  @keydown="handleKeydown(1, $event)"
                  maxlength="4" 
                  class="dummy-input text-center" 
                  placeholder="----"
                >
                <span class="text-gray-700 font-bold">-</span>
                <input 
                  type="text" 
                  :ref="el => inputs[2] = el"
                  @input="handleInput(2, $event)"
                  @keydown="handleKeydown(2, $event)"
                  maxlength="4" 
                  class="dummy-input text-center" 
                  placeholder="----"
                >
                <span class="text-gray-700 font-bold">-</span>
                <input 
                  type="text" 
                  :ref="el => inputs[3] = el"
                  @input="handleInput(3, $event)"
                  @keydown="handleKeydown(3, $event)"
                  maxlength="4" 
                  class="dummy-input text-center" 
                  placeholder="----"
                >
              </div>
            </div>

            <!-- 🌟 DEĞİŞİKLİK: Sahte SKT (AY/YIL) ve CVC -->
            <div class="flex gap-4">
              <!-- SKT (AY/YIL) -->
              <div class="flex-[2]">
                <label class="block text-sm font-medium text-gray-800">SKT (AA/YY)</label>
                <div class="flex items-center gap-1 mt-1">
                  <!-- AY -->
                  <input 
                    type="text" 
                    :ref="el => inputs[4] = el"
                    @input="handleInput(4, $event)"
                    @keydown="handleKeydown(4, $event)"
                    maxlength="2" 
                    class="dummy-input text-center" 
                    placeholder="AA"
                  >
                  <span class="text-gray-700 font-bold">/</span>
                  <!-- YIL -->
                  <input 
                    type="text" 
                    :ref="el => inputs[5] = el"
                    @input="handleInput(5, $event)"
                    @keydown="handleKeydown(5, $event)"
                    maxlength="2" 
                    class="dummy-input text-center" 
                    placeholder="YY"
                  >
                </div>
              </div>
              
              <!-- CVC -->
              <div class="flex-[1]">
                <label for="cvc" class="block text-sm font-medium text-gray-800">CVC</label>
                <input 
                  type="text" 
                  id="cvc" 
                  :ref="el => inputs[6] = el"
                  @input="handleInput(6, $event)"
                  @keydown="handleKeydown(6, $event)"
                  maxlength="3" 
                  class="dummy-input text-center mt-1" 
                  placeholder="---"
                >
              </div>
            </div>
            <!-- 🌟 DEĞİŞİKLİK BİTTİ -->
          </div>
          
          <!-- Hata Mesajı -->
          <div v-if="error" class="mt-4 p-3 bg-red-100 text-red-700 rounded-lg text-sm">
            {{ error }}
          </div>

          <!-- Onay Butonu (Simülasyonu Tetikler) -->
          <button 
            @click="simulatePayment"
            :disabled="loading"
            class="btn btn-primary w-full py-4 text-lg font-bold disabled:opacity-60 mt-6"
          >
            <span v-if="loading">İşleniyor...</span>
            <span v-else>Ödemeyi Onayla ({{ amount.toFixed(2) }} ₺)</span>
          </button>

          <!-- Geri Dön Butonu -->
          <button 
            @click="currentStep = 1"
            class="w-full text-center text-gray-700 mt-3 p-2 hover:underline"
          >
            Tutar değiştir
          </button>
        </div>

        <p class="text-xs text-gray-700 text-center mt-4">
          Bu bir ödeme simülasyonudur. Gerçek kart bilgilerinizi girmeyiniz.
        </p>
      </div>
    </div>
  </NuxtLayout>
</template>

<script setup>
// 'watch' ve 'nextTick' eklendi
import { ref, watch, nextTick } from 'vue' 
import useAuth from '../composables/useAuth'
import protectUserPage from '../composables/protectUserPage'

// Sayfa koruması
await protectUserPage()
const router = useRouter() // Yönlendirme için

// State
const currentStep = ref(1) // 1: Tutar, 2: Sahte Form
const amount = ref(100.00) // Varsayılan tutar
const loading = ref(false)
const error = ref(null)

// 🌟 DEĞİŞİKLİK: Tüm input'ları (4 kart + 2 SKT + 1 CVC = 7) tek bir ref'te topluyoruz
const inputs = ref([])
// Her input'un maksimum karakter uzunluğu
const inputLengths = [4, 4, 4, 4, 2, 2, 3] 

// Adım 2'ye geçildiğinde ilk kutuya (Kart 1) otomatik odaklan
watch(currentStep, async (newStep) => {
  if (newStep === 2) {
    // DOM güncellendikten sonra focus yap
    await nextTick()
    if (inputs.value[0]) {
      inputs.value[0].focus()
    }
  }
})

// 🌟 DEĞİŞİKLİK: 'onCardInput' -> 'handleInput' (Genel fonksiyon)
// Maksimum karaktere ulaşıldığında otomatik sonraki kutuya atla
function handleInput(index, event) {
  const value = event.target.value
  const maxLength = inputLengths[index] // O anki input'un max uzunluğunu al
  
  // Eğer max uzunluğa ulaşıldıysa ve son input değilse
  if (value.length === maxLength && index < inputs.value.length - 1) {
    if (inputs.value[index + 1]) {
      inputs.value[index + 1].focus() // Bir sonrakine odaklan
    }
  }
}

// 🌟 DEĞİŞİKLİK: 'onCardKeydown' -> 'handleKeydown' (Genel fonksiyon)
// Silme (Backspace) tuşuna basınca ve kutu boşsa bir önceki kutuya git
function handleKeydown(index, event) {
  if (event.key === 'Backspace' && event.target.value.length === 0 && index > 0) {
    if (inputs.value[index - 1]) {
      inputs.value[index - 1].focus() // Bir öncekine odaklan
    }
  }
}

// Adım 1 -> Adım 2 geçişi
function goToPaymentStep() {
  if (amount.value > 0) {
    currentStep.value = 2
  }
}

// Adım 2: Sahte ödeme onayı
async function simulatePayment() {
  loading.value = true
  error.value = null

  try {
    // Backend'deki simülasyon fonksiyonunu çağır
    // Bu fonksiyon (PaymentController@startPayment) bakiyeyi yükler.
    const response = await $fetch('/api/payment/start', {
      method: 'POST',
      body: {
        amount: amount.value
      }
    })

    // Başarılı. Sonuç sayfasına yönlendir.
    router.push({ 
      path: '/odeme-sonuc', 
      query: { status: 'success', amount: amount.value } 
    })

  } catch (err) {
    // Backend hatası (örn: 500)
    error.value = err.data?.message || 'Ödeme simülasyonu başarısız oldu.'
    loading.value = false
  }
}
</script>

<style scoped>
/* 🔘 Minimal buton sistemimiz (Tailwind @apply) */
.btn {
  @apply inline-flex items-center justify-center px-4 py-2 rounded-xl font-semibold transition
         focus:outline-none focus:ring-2 focus:ring-offset-0 active:scale-[.99];
}
.btn-ghost {
  @apply text-white/90 hover:text-white bg-white/0 hover:bg-white/10 border border-white/10;
}
.btn-ghost-active {
  @apply text-white bg-white/20 border border-white/20;
}
.btn-outline {
  @apply text-white border border-white/40 bg-transparent hover:bg-white/10;
}
.btn-primary {
  @apply text-white bg-gradient-to-r from-orange-500 via-orange-500 to-orange-600
         hover:brightness-110 shadow-md;
}
.btn-primary-active {
  @apply text-white bg-gradient-to-r from-orange-600 via-orange-600 to-orange-700
         hover:brightness-110 shadow-lg ring-2 ring-white/50;
}
.btn-soft {
  @apply text-sky-900 bg-white/70 hover:bg-white/90 border border-white/80
         backdrop-blur-sm rounded-xl;
}

/* Sahte Kart Formu için Input Stili */
.dummy-input {
  @apply w-full border border-white/50 rounded-xl p-3 text-gray-800
         bg-white/50 placeholder-gray-600
         focus:outline-none focus:ring-2 focus:ring-orange-500;
}
</style>