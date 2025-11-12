<template>
  <div class="min-h-screen relative overflow-hidden bg-gradient-to-br from-sky-400/80 via-emerald-300/80 to-amber-300/80 text-gray-900">
    <!-- 🎡 Arka plan animasyonu -->
    <div class="absolute inset-0 animate-gradientMove opacity-30"></div>

    <!-- 🧭 Üst Navigasyon -->
    <header
      class="flex flex-wrap items-center justify-between gap-4 px-6 md:px-10 py-4
             border-b border-white/30 backdrop-blur-2xl bg-white/10 sticky top-0 z-20 shadow-md">
      
      <!-- 🔹 Sol: Sistem Adı -->
      <div class="flex items-center gap-3">
        <h1 class="text-xl md:text-2xl font-extrabold text-white tracking-wide drop-shadow-lg">
          🍽️ Yemekhane Sistemi
        </h1>
      </div>

      <!-- 🔸 Orta: Logo + Butonlar -->
      <div class="flex items-center gap-3 md:gap-6">
        <!-- Sol slot butonları -->
        <div class="flex items-center gap-2 md:gap-3">
          <slot name="left-buttons" />
        </div>

        <!-- Logo -->
        <img
          src="https://mezitli.bel.tr/wp-content/uploads/2020/07/mezbellogo-1.png"
          alt="Mezitli Belediyesi"
          class="w-20 h-24 md:w-20 md:h-20 object-contain drop-shadow-lg hover:scale-110 transition-transform duration-300"
        />

        <!-- Sağ slot butonları -->
        <div class="flex items-center gap-2 md:gap-3">
          <slot name="right-buttons" />
        </div>
      </div>

      <!-- 🔹 Sağ: Çıkış Butonu -->
      <button
        @click="handleLogout"
        class="btn btn-danger"
      >
        Çıkış Yap
      </button>
    </header>

    <!-- 💫 İçerik Alanı -->
    <main class="max-w-6xl mx-auto px-4 md:px-6 py-8 md:py-12 relative z-10">
      <slot />
    </main>

    <!-- 🌈 Hafif ışık lekesi efekti -->
    <div class="absolute w-64 h-64 md:w-80 md:h-80 bg-white/20 rounded-full blur-3xl top-40 left-10 animate-pulse"></div>
    <div class="absolute w-72 h-72 md:w-96 md:h-96 bg-white/10 rounded-full blur-3xl bottom-20 right-10 animate-pulse"></div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import useAuth from '../composables/useAuth'

const router = useRouter()
const { logout } = useAuth()

const handleLogout = async () => {
  try {
    await logout()
  } finally {
    router.push('/login')
  }
}
</script>

<style>
/* 🎨 Arka plan */
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

/* 🔘 GLOBAL buton sistemi (slot içerisine düşen link/btn’ler için) */
.btn {
  @apply inline-flex items-center justify-center px-4 py-2 rounded-xl font-semibold transition
         focus:outline-none focus:ring-2 focus:ring-offset-0 active:scale-[.99];
}
.btn-ghost {
  @apply text-white/90 hover:text-white bg-white/0 hover:bg-white/10 border border-white/10;
}
.btn-outline {
  @apply text-white border border-white/40 bg-transparent hover:bg-white/10;
}
.btn-primary {
  @apply text-white bg-gradient-to-r from-orange-500 via-orange-500 to-orange-600
         hover:brightness-110 shadow-md;
}
.btn-soft {
  @apply text-sky-900 bg-white/70 hover:bg-white/90 border border-white/80 backdrop-blur-sm rounded-xl;
}
.btn-danger {
  @apply text-white bg-red-500 hover:bg-red-600 shadow-md px-4 py-2 rounded-xl;
}

/* Mobilde butonları biraz küçültelim */
@media (max-width: 768px) {
  .btn { @apply text-sm px-3 py-2; }
}
</style>
