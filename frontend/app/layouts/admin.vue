<template>
  <div class="min-h-screen bg-gradient-to-br from-[#0f172a] via-[#1e293b] to-[#0f172a] text-white font-sans flex flex-col">
    <!-- 🧭 HEADER -->
    <header class="sticky top-0 z-20 backdrop-blur-xl bg-white/5 border-b border-white/10 shadow-md">
      <div class="max-w-7xl mx-auto px-6 py-4 flex items-center justify-between">
        <!-- Sol -->
        <NuxtLink
          to="/admin"
          class="text-2xl font-extrabold tracking-wide text-white hover:text-orange-400 transition"
        >
          🍽️ Mezitli Admin
        </NuxtLink>

        <!-- Orta (saat) -->
        <div
          class="hidden md:block absolute left-1/2 -translate-x-1/2 text-white/70 font-mono text-sm"
        >
          🕒 {{ currentTime }}
        </div>

        <!-- Sağ -->
        <div class="flex items-center gap-4">
          <div v-if="user" class="flex items-center gap-2 text-sm text-white/80">
            <span class="text-emerald-400 font-semibold">👤 {{ user.name }}</span>
            <span class="text-white/50">({{ user.role }})</span>
          </div>

          <button
            @click="handleLogout"
            class="bg-red-600 hover:bg-red-700 px-4 py-1.5 rounded-lg text-sm font-semibold shadow-md transition"
          >
            Çıkış
          </button>
        </div>
      </div>
    </header>

    <!-- 📦 ANA İÇERİK -->
    <main class="flex-grow relative z-10 max-w-7xl mx-auto w-full px-6 py-10">
      <slot />
    </main>

    <!-- 🌙 FOOTER -->
    <footer class="mt-auto text-center py-6 text-sm text-white/70 border-t border-white/10 backdrop-blur-md bg-white/5">
      © {{ currentYear }} Mezitli Belediyesi
      <span class="text-white/50">| Yemekhane Yönetim Sistemi</span>
      <div class="mt-1 italic text-white/40">“Sahilden Sofraya, Her Gün Mezitli!”</div>
    </footer>

    <!-- ✨ Hafif ışık efekti -->
    <div class="pointer-events-none fixed inset-0 bg-gradient-to-br from-transparent via-white/5 to-transparent blur-3xl opacity-10"></div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue'
import useAuth from '../composables/useAuth'

const { logout, user } = useAuth()

// 🕒 Saat (yalnızca client)
const currentTime = ref('--:--:--')
const currentYear = new Date().getFullYear()
let timer = null

onMounted(() => {
  const tick = () => {
    currentTime.value = new Date().toLocaleTimeString('tr-TR', {
      hour: '2-digit',
      minute: '2-digit',
      second: '2-digit'
    })
  }
  tick()
  timer = setInterval(tick, 1000)
})
onBeforeUnmount(() => {
  if (timer) clearInterval(timer)
})

const handleLogout = async () => {
  try {
    await logout()
  } finally {
    await navigateTo('/login')
  }
}
</script>

<style scoped>
/* Yumuşak fade utility (sayfa içi geçişlerde kullanabilirsin) */
.fade-enter-active,
.fade-leave-active { transition: opacity .3s ease; }
.fade-enter-from,
.fade-leave-to { opacity: 0; }
</style>
