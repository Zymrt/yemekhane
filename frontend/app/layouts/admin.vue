<template>
  <div class="min-h-screen relative bg-[#050505] text-white font-sans selection:bg-purple-500 selection:text-white overflow-hidden">
    
    <!-- 🌌 ARKA PLAN EFEKTLERİ -->
    <div class="fixed inset-0 z-0 pointer-events-none">
      <div class="absolute top-[-10%] left-[-10%] w-[40%] h-[40%] bg-purple-900/20 rounded-full blur-[120px] animate-pulse"></div>
      <div class="absolute bottom-[-10%] right-[-10%] w-[40%] h-[40%] bg-blue-900/20 rounded-full blur-[120px] animate-pulse" style="animation-delay: 2s;"></div>
      <div class="absolute top-[20%] left-[40%] w-[20%] h-[20%] bg-pink-900/10 rounded-full blur-[100px] animate-pulse" style="animation-delay: 4s;"></div>
    </div>

    <!-- 🌓 SIDEBAR (MASAÜSTÜ) -->
    <aside class="fixed top-0 left-0 h-full w-72 bg-[#0a0a0a]/60 backdrop-blur-xl border-r border-white/5 z-50 hidden lg:flex flex-col">
      
      <!-- LOGO ALANI (MASAÜSTÜ) -->
      <div class="h-20 flex items-center px-8 border-b border-white/5">
        <NuxtLink to="/admin" class="flex items-center gap-3 text-white font-bold text-xl tracking-tight group">
          <img 
            src="~/assets/logo.jpg" 
            alt="Logo" 
            class="w-10 h-10 rounded-xl object-cover shadow-lg shadow-purple-500/20 group-hover:scale-105 transition-transform"
          />
          <span>Yemekhane<span class="text-purple-400">OS</span></span>
        </NuxtLink>
      </div>

      <!-- Menü Linkleri -->
      <nav class="flex-1 px-4 py-6 flex flex-col gap-1 overflow-y-auto custom-scrollbar">
        <p class="px-4 text-xs font-bold text-gray-500 uppercase tracking-widest mb-2">Yönetim</p>
        
        <NuxtLink to="/admin" class="nav-item">
          <i class="i-lucide-layout-grid w-5 h-5"></i> Ana Panel
        </NuxtLink>
        <NuxtLink to="/admin/dashboard" class="nav-item">
          <i class="i-lucide-bar-chart-2 w-5 h-5"></i> Raporlar
        </NuxtLink>
        <NuxtLink to="/admin/onay" class="nav-item">
          <i class="i-lucide-user-check w-5 h-5"></i> Onaylar
          <span class="ml-auto bg-purple-500/20 text-purple-300 text-[10px] font-bold px-2 py-0.5 rounded-full border border-purple-500/20">İşlem</span>
        </NuxtLink>

        <p class="px-4 text-xs font-bold text-gray-500 uppercase tracking-widest mt-6 mb-2">İçerik</p>
        
        <NuxtLink to="/admin/users" class="nav-item">
          <i class="i-lucide-users w-5 h-5"></i> Kullanıcılar
        </NuxtLink>
        <NuxtLink to="/admin/units" class="nav-item">
          <i class="i-lucide-building-2 w-5 h-5"></i> Birimler
        </NuxtLink>
        <NuxtLink to="/admin/add-menu" class="nav-item">
          <i class="i-lucide-plus-circle w-5 h-5"></i> Menü Ekle
        </NuxtLink>
        <NuxtLink to="/admin/menus" class="nav-item">
          <i class="i-lucide-utensils w-5 h-5"></i> Menü Geçmişi
        </NuxtLink>
        <NuxtLink to="/admin/announcements" class="nav-item">
          <i class="i-lucide-megaphone w-5 h-5"></i> Duyurular
        </NuxtLink>
        <NuxtLink to="/admin/reviews" class="nav-item">
          <i class="i-lucide-message-square w-5 h-5"></i> Yorumlar
        </NuxtLink>

        <!-- Spacer to push logout down -->
        <div class="mt-auto"></div>

        <!-- 👇 YENİ EKLENEN ÇIKIŞ BUTONU 👇 -->
        <button @click="handleLogout" class="nav-item text-red-400 hover:text-red-300 hover:bg-red-500/10 hover:border-red-500/20 mt-4 group">
          <i class="i-lucide-log-out w-5 h-5 group-hover:scale-110 transition-transform"></i> 
          Güvenli Çıkış
        </button>
      </nav>

      <!-- Profil -->
      <div class="p-4 border-t border-white/5 bg-black/20">
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-full bg-gradient-to-tr from-gray-700 to-gray-600 border border-white/10 flex items-center justify-center font-bold text-sm text-white shadow-inner">
            AD
          </div>
          <div class="flex-1 overflow-hidden">
            <h4 class="text-sm font-bold text-white truncate">Yönetici</h4>
            <p class="text-xs text-gray-500 truncate">Sistem Admini</p>
          </div>
        </div>
      </div>
    </aside>

    <!-- 📱 MOBİL HEADER (Sadece mobilde görünür) -->
    <header class="lg:hidden fixed top-0 left-0 right-0 h-16 bg-[#0a0a0a]/80 backdrop-blur-md border-b border-white/10 z-40 flex items-center justify-between px-6">
      <div class="flex items-center gap-2 font-bold text-white">
        <img src="~/assets/logo.jpg" alt="Logo" class="w-8 h-8 rounded-lg object-cover" />
        YemekhaneOS
      </div>
      <div class="flex items-center gap-3">
         <!-- Mobilde de çıkış butonu olsun -->
        <button @click="handleLogout" class="p-2 text-red-400"><i class="i-lucide-log-out w-6 h-6"></i></button>
        <NuxtLink to="/admin" class="p-2 text-gray-300"><i class="i-lucide-home w-6 h-6"></i></NuxtLink>
      </div>
    </header>

    <main class="lg:ml-72 pt-20 lg:pt-6 p-6 relative z-10 min-h-screen overflow-x-hidden">
      <slot />
    </main>

  </div>
</template>

<script setup>
import useAuth from '../composables/useAuth'

const { logout } = useAuth()

// Çıkış işlemi için güvenli fonksiyon
const handleLogout = async () => {
  if (confirm('Admin panelinden çıkış yapmak istediğinize emin misiniz?')) {
    await logout()
    await navigateTo('/login')
  }
}
</script>

<style scoped>
.nav-item {
  @apply flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-gray-400 hover:text-white hover:bg-white/5 transition-all duration-200 border border-transparent hover:border-white/5 w-full text-left;
}
.router-link-active {
  @apply bg-gradient-to-r from-purple-500/10 to-blue-500/10 text-white border-white/10 shadow-[0_0_20px_rgba(168,85,247,0.15)];
}
.router-link-active i {
  @apply text-purple-400;
}
.custom-scrollbar::-webkit-scrollbar { width: 4px; }
.custom-scrollbar::-webkit-scrollbar-thumb { background: #333; border-radius: 10px; }
</style>