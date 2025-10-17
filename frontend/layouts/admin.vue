<template>
  <div class="min-h-screen bg-gradient-to-br from-gray-900 via-blue-900 to-gray-800 text-white">
    
    <header class="backdrop-blur-md bg-black/20 border-b border-white/10 sticky top-0 z-10">
      <div class="container mx-auto px-6 py-4 flex justify-between items-center">
        <NuxtLink to="/admin" class="text-xl font-bold hover:opacity-80 transition">
          Yönetim Paneli
        </NuxtLink>
        
        <div v-if="isLoggedIn" class="flex items-center gap-4">
          <span class="text-sm">Hoş geldin, <span class="font-semibold">{{ user?.name }}</span>!</span>
          <button @click="logout" class="bg-red-600 hover:bg-red-700 text-white text-sm font-bold py-1 px-3 rounded-md transition-colors">
            Çıkış Yap
          </button>
        </div>
      </div>
    </header>

    <main class="container mx-auto p-6 md:p-8">
      <slot />
    </main>
  </div>
</template>

<script setup>
import useAuth from '../composables/useAuth';
const { isLoggedIn, user, logout } = useAuth();
</script>