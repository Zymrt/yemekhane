<template>
  <div class="flex flex-col items-center justify-center min-h-screen bg-gradient-to-br from-blue-500 via-orange-500 to-blue-600 text-white">
    <!-- Logo Alanı -->
    <div class="flex flex-col items-center mb-12">
      <div class="w-32 h-32 bg-white rounded-full shadow-lg flex items-center justify-center overflow-hidden">
        <!-- Buraya kendi logonu koyacaksın -->
        <img 
          src="..\assets\logo.jpg" 
          alt="Logo" 
          class="object-contain w-28 h-28 transition-transform duration-500 hover:scale-110"
        />
      </div>
      <h1 class="text-4xl font-bold mt-6 drop-shadow-md">Belediye Yemekhane</h1>
      <p class="text-lg opacity-90 mt-2">Hoş geldin 👋 Giriş yap veya hemen kayıt ol!</p>
    </div>

    <!-- Butonlar -->
    <div class="flex space-x-6">
      <NuxtLink
        to="/login"
        class="bg-white text-orange-600 font-semibold py-3 px-8 rounded-2xl shadow-lg transition-all duration-300 hover:bg-green-100 hover:scale-105"
      >
        Giriş Yap
      </NuxtLink>

      <NuxtLink
        to="/register"
        class="bg-green-700 text-blue font-semibold py-3 px-8 rounded-2xl shadow-lg transition-all duration-300 hover:bg-green-800 hover:scale-105"
      >
        Kayıt Ol
      </NuxtLink>
    </div>
  </div>
</template>

<script setup>
// Şimdilik ekstra JS gerek yok ama ileride logo animasyonu veya giriş kontrolü eklenebilir.
</script>