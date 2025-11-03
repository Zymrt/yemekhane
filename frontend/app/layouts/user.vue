<template>
  <div class="min-h-screen relative overflow-hidden bg-gradient-to-br from-sky-400/80 via-emerald-300/80 to-amber-300/80 text-gray-900">

    <!-- 🎡 Arka plan animasyonu -->
    <div class="absolute inset-0 animate-gradientMove opacity-30"></div>

    <!-- 🧭 Üst Navigasyon -->
    <header class="flex justify-between items-center px-10 py-5 border-b border-white/30 backdrop-blur-2xl bg-white/10 sticky top-0 z-20 shadow-md">
      
      <!-- 🔹 Sol: Sistem Adı -->
      <div class="flex items-center gap-3">
        <h1 class="text-2xl font-extrabold text-white tracking-wide drop-shadow-lg">
          🍽️ Yemekhane Sistemi
        </h1>
      </div>

      <!-- 🔸 Orta: Logo + Butonlar -->
      <div class="flex items-center gap-6">
        <!-- Sol taraftaki buton alanı -->
        <slot name="left-buttons"></slot>

        <!-- Logo -->
        <img
          src="https://mezitli.bel.tr/wp-content/uploads/2020/07/mezbellogo-1.png"
          alt="Mezitli Belediyesi"
          class="w-21 h-20 object-contain drop-shadow-lg hover:scale-110 transition-transform duration-300"
        />

        <!-- Sağ taraftaki buton alanı -->
        <slot name="right-buttons"></slot>
      </div>

      <!-- 🔹 Sağ: Çıkış Butonu -->
      <button
        @click="handleLogout"
        class="bg-red-500 hover:bg-red-600 text-white py-2 px-4 rounded-xl shadow-md transition transform hover:scale-105 active:scale-95"
      >
        Çıkış Yap
      </button>
    </header>

    <!-- 💫 İçerik Alanı -->
    <main class="max-w-6xl mx-auto px-6 py-12 relative z-10">
      <slot />
    </main>

    <!-- 🌈 Hafif ışık lekesi efekti -->
    <div class="absolute w-80 h-80 bg-white/20 rounded-full blur-3xl top-40 left-10 animate-pulse"></div>
    <div class="absolute w-96 h-96 bg-white/10 rounded-full blur-3xl bottom-20 right-10 animate-pulse"></div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import useAuth from '../composables/useAuth'

const router = useRouter()
const { logout } = useAuth()

const handleLogout = async () => {
  await logout()
  router.push('/login')
}
</script>

<style scoped>
@keyframes gradientMove {
  0% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
  100% { background-position: 0% 50%; }
}
.animate-gradientMove {
  background: linear-gradient(270deg, #38bdf8, #34d399, #fbbf24);
  background-size: 600% 600%;
  animation: gradientMove 12s ease infinite;
}
</style>
