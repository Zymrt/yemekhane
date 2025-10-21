<template>
  <div class="min-h-screen relative overflow-hidden text-white font-sans">
    
    <!-- 🌈 ANİMASYONLU GRADIENT ARKA PLAN -->
    <div class="absolute inset-0 animate-gradient bg-[length:400%_400%]
      bg-gradient-to-br from-cyan-700 via-sky-500 via-orange-400 to-yellow-400 opacity-90"></div>

    <!-- 🌊 DALGA SVG -->
    <svg class="absolute bottom-0 left-0 w-full opacity-40" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 1440 320">
      <path fill="#0099ff" fill-opacity="0.4"
        d="M0,192L48,170.7C96,149,192,107,288,117.3C384,128,480,192,576,197.3C672,203,768,149,864,128C960,107,1056,117,1152,149.3C1248,181,1344,235,1392,261.3L1440,288L1440,320L0,320Z">
      </path>
    </svg>

    <!-- ☀️ GÜNEŞ İKONU -->
    <div class="absolute top-20 right-10 flex items-center gap-2 text-yellow-300 animate-pulse">
      <svg xmlns="http://www.w3.org/2000/svg" class="w-8 h-8 drop-shadow-lg" fill="none" viewBox="0 0 24 24"
        stroke="currentColor" stroke-widh="2">
        <path stroke-linecap="round" stroke-linejoin="round"
          d="M12 3v1m0 16v1m8.66-9H21m-18 0H3m1.34 5.66l.7.7m12.73-12.73l.7.7m0 11.32l-.7.7M4.04 4.04l.7.7M12 8a4 4 0 100 8 4 4 0 000-8z" />
      </svg>
      <span class="text-sm font-medium tracking-wide">Mezitli'de Güneşli Bir Gün ☀️</span>
    </div>

    <!-- 💎 HEADER -->
    <header class="relative z-10 backdrop-blur-md bg-white/10 border-b border-white/20 sticky top-0 shadow-lg">
      <div class="max-w-7xl mx-auto px-6 py-4 flex justify-between items-center">
        <!-- Sol -->
        <NuxtLink to="/admin" class="text-2xl font-extrabold tracking-wide hover:text-orange-300 transition">
          🌅 Mezitli Admin
        </NuxtLink>

        <!-- Orta -->
        <div class="absolute left-1/2 transform -translate-x-1/2 text-white/90 font-semibold tracking-wide">
          🕒 {{ currentTime }}
        </div>

        <!-- Sağ -->
        <div class="flex items-center gap-6 text-sm font-medium">
          <div v-if="remainingTime > 0" class="text-emerald-300">
            ⏳ {{ formattedRemaining }}
          </div>
          <div v-else class="text-red-300 font-semibold">
            🔒 Oturum Doldu
          </div>
          <button
            @click="logout"
            class="bg-red-600 text-white px-3 py-1.5 rounded-lg hover:bg-red-700 transition"
          >
            Çıkış
          </button>
        </div>
      </div>
    </header>

    <!-- 🧊 ANA İÇERİK -->
    <main class="relative z-10 max-w-7xl mx-auto px-6 py-10">
      <slot />
    </main>

    <!-- 🍊 FOOTER -->
    <footer class="relative z-10 text-center text-white/80 py-6 text-sm">
      © {{ new Date().getFullYear() }} Mezitli Belediyesi - Yemekhane Yönetim Sistemi  
      <div class="mt-1 text-white/50 italic">"Sahilden Sofraya, Her Gün Mezitli!"</div>
    </footer>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, onBeforeUnmount } from 'vue'
import useAuth from '../composables/useAuth'

const { logout, token } = useAuth()

// 🕒 SAAT
const currentTime = ref('')
const updateTime = () => {
  const now = new Date()
  currentTime.value = now.toLocaleTimeString('tr-TR', {
    hour: '2-digit',
    minute: '2-digit',
    second: '2-digit'
  })
}
setInterval(updateTime, 1000)
updateTime()

// ⏳ TOKEN GERİ SAYIM
const TOKEN_TTL_MINUTES = 90
const tokenStartTime = ref(localStorage.getItem('tokenStartTime'))

if (!tokenStartTime.value && process.client && token.value) {
  localStorage.setItem('tokenStartTime', Date.now())
  tokenStartTime.value = Date.now()
}

const remainingTime = ref(0)
const formattedRemaining = computed(() => {
  const totalSeconds = Math.floor(remainingTime.value / 1000)
  const minutes = Math.floor(totalSeconds / 60)
  const seconds = totalSeconds % 60
  return `Oturum: ${minutes}dk ${seconds}s`
})

const updateRemaining = () => {
  if (!tokenStartTime.value) return
  const elapsed = Date.now() - tokenStartTime.value
  const ttlMs = TOKEN_TTL_MINUTES * 60 * 1000
  remainingTime.value = Math.max(ttlMs - elapsed, 0)

  if (remainingTime.value <= 0) {
    alert('Oturum süresi doldu, çıkış yapılıyor...')
    logout()
  }
}

let timer
onMounted(() => {
  updateRemaining()
  timer = setInterval(updateRemaining, 1000)
})
onBeforeUnmount(() => clearInterval(timer))
</script>

<style scoped>
@keyframes gradientShift {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}
.animate-gradient {
  animation: gradientShift 15s ease infinite;
}
</style>
