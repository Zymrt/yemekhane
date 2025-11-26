<template>
  <div class="min-h-screen bg-[#050505] flex flex-col items-center justify-center p-6 relative overflow-hidden">
    
    <!-- Arka Plan Efektleri -->
    <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[600px] bg-purple-900/20 rounded-full blur-[120px] animate-pulse pointer-events-none"></div>

    <!-- ANA KART -->
    <div class="w-full max-w-md bg-[#121212]/80 border border-white/10 rounded-3xl p-8 shadow-2xl backdrop-blur-xl relative z-10">
      
      <!-- Başlık -->
      <div class="text-center mb-8">
        <div class="inline-flex p-3 bg-purple-500/10 rounded-2xl border border-purple-500/20 mb-4 shadow-[0_0_15px_rgba(168,85,247,0.3)]">
          <QrCodeIcon class="w-10 h-10 text-purple-400" />
        </div>
        <h1 class="text-2xl font-black text-white tracking-tight">QR Kod Tarama</h1>
        <p class="text-slate-400 text-sm mt-2">QR kodunu okutunuz.</p>
      </div>
      
      <!-- KAMERA ALANI -->
      <div class="relative rounded-2xl overflow-hidden border-2 border-white/10 bg-black shadow-inner min-h-[300px]">
        <!-- Html5Qrcode kütüphanesi buraya render edecek -->
        <div id="reader" class="w-full h-full"></div>
        
        <!-- Tarama Çizgisi Animasyonu -->
        <div class="absolute inset-0 pointer-events-none border-[20px] border-[#050505]/50 z-10"></div>
        <div class="absolute top-0 left-0 w-full h-1 bg-purple-500 shadow-[0_0_20px_rgba(168,85,247,1)] animate-scan z-20"></div>
      </div>

      <!-- SONUÇ BİLDİRİMİ veya KAMERA AKTİF YAZISI (DÜZELTİLEN KISIM) -->
      <transition name="fade" mode="out-in">
        
        <!-- Durum 1: Sonuç varsa bunu göster -->
        <div v-if="result" key="result" class="mt-6 p-5 rounded-2xl border text-center transition-all duration-300 transform"
          :class="success 
            ? 'bg-emerald-500/10 border-emerald-500/30 text-emerald-400' 
            : 'bg-red-500/10 border-red-500/30 text-red-400'"
        >
          <div class="text-3xl mb-2">{{ success ? '✅' : '⛔' }}</div>
          <div class="font-bold text-lg">{{ result }}</div>
        </div>
        
        <!-- Durum 2: Sonuç yoksa (Tarama yapılıyorsa) bunu göster -->
        <div v-else key="scanning" class="mt-6 text-center">
          <div class="inline-block w-2 h-2 bg-purple-500 rounded-full animate-ping mr-2"></div>
          <span class="text-xs text-slate-500 uppercase tracking-widest font-bold">Kamera Aktif</span>
        </div>

      </transition>

      <!-- Çıkış -->
      <NuxtLink to="/admin" class="block mt-8 text-center text-slate-500 text-sm hover:text-white transition flex items-center justify-center gap-2">
        <ArrowLeftIcon class="w-4 h-4" /> Admin Paneline Dön
      </NuxtLink>
    </div>
  </div>
</template>

<script setup>
import { onMounted, ref, onUnmounted } from 'vue'
import { Html5QrcodeScanner } from "html5-qrcode"
import { QrCodeIcon, ArrowLeftIcon } from '@heroicons/vue/24/outline'

definePageMeta({ layout: 'admin' })

const result = ref(null)
const success = ref(false)
let scanner = null

// QR Okunduğunda Çalışacak Fonksiyon
const onScanSuccess = async (decodedText) => {
  if (!scanner) return;
  
  // 1. Taramayı geçici durdur (sürekli istek atmasın)
  scanner.pause(true) 
  result.value = "İşleniyor..."
  
  try {
    // 2. Backend'e sor
    const res = await $fetch('/api/admin/qr/scan', {
      method: 'POST',
      body: { qr_code: decodedText }
    })
    
    // 3. Başarılı Sonuç
    success.value = true
    result.value = `${res.user_name} - Afiyet Olsun!`
    
    // 3 saniye sonra tekrar taramaya başla
    setTimeout(() => {
      result.value = null
      scanner.resume()
    }, 3000)

  } catch (err) {
    // 4. Hata Durumu
    success.value = false
    result.value = err.data?.message || "Okuma Hatası!"
    
    setTimeout(() => {
      result.value = null
      scanner.resume()
    }, 3000)
  }
}

onMounted(() => {
  // Kütüphane Ayarları
  scanner = new Html5QrcodeScanner("reader", { 
    fps: 10, 
    qrbox: { width: 250, height: 250 },
    aspectRatio: 1.0,
    showTorchButtonIfSupported: true
  });
  
  scanner.render(onScanSuccess, (errorMessage) => {
    // Hata mesajlarını konsola basıp kirletmesin diye boş bırakıyoruz
  });
})

onUnmounted(() => {
  if(scanner) {
    try {
      scanner.clear()
    } catch (e) {
      console.log("Scanner temizleme hatası (önemsiz)")
    }
  }
})
</script>

<style scoped>
/* Tarama Çizgisi Animasyonu */
@keyframes scan {
  0% { top: 0; opacity: 0; }
  10% { opacity: 1; }
  90% { opacity: 1; }
  100% { top: 100%; opacity: 0; }
}
.animate-scan {
  animation: scan 2s linear infinite;
}

.fade-enter-active,
.fade-leave-active { transition: opacity 0.3s ease; }
.fade-enter-from,
.fade-leave-to { opacity: 0; }

/* Kütüphanenin kendi stilini ezmek (Override) */
:deep(#reader) { border: none !important; }
:deep(#reader__scan_region) { background: transparent !important; }
:deep(#reader__dashboard_section_csr span) { display: none !important; }
:deep(#reader__dashboard_section_swaplink) {
  text-decoration: none !important;
  color: #a855f7 !important;
  border: 1px solid #a855f7;
  padding: 5px 10px;
  border-radius: 8px;
  font-size: 12px;
}
</style>