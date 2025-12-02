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
          src="https://www.google.com/url?sa=i&url=https%3A%2F%2Ftr.pinterest.com%2Fpin%2F640848221969926126%2F&psig=AOvVaw26I-1ShSZNYbDpDU18vp7L&ust=1764768280455000&source=images&cd=vfe&opi=89978449&ved=0CBIQjRxqFwoTCLDlnaaAn5EDFQAAAAAdAAAAABAE"
          alt="Logo"
          class="w-16 h-16 md:w-20 md:h-20 object-contain drop-shadow-xl hover:scale-110 transition-transform duration-300"
        />

        <!-- Sağ slot butonları -->
        <div class="flex items-center gap-2 md:gap-3">
          <slot name="right-buttons" />
        </div>
      </div>

      <!-- 🔹 Sağ: Çıkış Butonu -->
      <button
        type="button"
        @click="handleLogout"
        class="btn btn-danger"
      >
        <span class="hidden sm:inline-flex items-center gap-2">
          <span class="text-sm">🚪</span>
          <span>Çıkış Yap</span>
        </span>
        <span class="sm:hidden">🚪</span>
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
/* 🎨 Arka plan animasyonu */
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

/* 🔘 GLOBAL cam buton sistemi */
.btn {
  @apply inline-flex items-center justify-center gap-2
         px-4 py-2 rounded-2xl font-semibold tracking-wide
         transition duration-200 ease-out
         focus:outline-none focus-visible:ring-2 focus-visible:ring-white/70
         active:scale-95
         disabled:opacity-60 disabled:cursor-not-allowed
         backdrop-blur-xl shadow-md border;
}

/* Genel glass efekti */
.btn:hover {
  @apply translate-y-[1px] shadow-lg;
}

/* 🔹 Ghost – nav linkleri (ANA SAYFA, YORUMLAR vs.) */
.btn-ghost {
  @apply text-white/90
         bg-white/10
         border-white/30
         hover:bg-white/20 hover:border-white/60;
}

/* 🔹 Outline – Hesap hareketleri gibi sekmeler */
.btn-outline {
  @apply text-white
         bg-sky-500/10
         border-sky-200/60
         hover:bg-sky-500/25 hover:border-sky-100
         shadow-sky-500/30;
}

/* 🔸 Primary – turuncu cam (istersen başka yerlerde kullanırsın) */
.btn-primary {
  @apply text-white
         bg-orange-500/20
         border-orange-300/70
         hover:bg-orange-500/35
         shadow-orange-500/40;
}

/* 🟢 Bakiye butonu – YEŞİL CAM + para efekti */
.btn-balance {
  @apply text-emerald-50
         bg-emerald-500/25
         border-emerald-300/80
         hover:bg-emerald-500/40
         shadow-emerald-500/50;
  position: relative;
}

/* Tıklayınca para emojisi çıksın */
.btn-balance:active::after {
  content: " 💸";
  position: relative;
  top: 0;
}

/* 🔴 Danger – çıkış butonu */
.btn-danger {
  @apply text-white
         bg-rose-500/25
         border-rose-300/80
         hover:bg-rose-500/40
         shadow-rose-500/50
         px-4 py-2 rounded-2xl;
}

/* Mobilde biraz küçültelim */
@media (max-width: 768px) {
  .btn { @apply text-sm px-3 py-2 rounded-xl; }
}
</style>
