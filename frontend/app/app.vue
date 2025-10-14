<script setup>
// Yönlendirme ve Nuxt yardımcılarını import ediyoruz
import { navigateTo, useRouter } from '#app';
import { onBeforeMount } from 'vue';

// Rotayı kontrol eden ana fonksiyon
const checkAuth = () => {
    // LocalStorage'a erişim sadece tarayıcıda
    if (process.client) { 
        const token = localStorage.getItem('authToken');
        const isAuthenticated = !!token;
        const currentPath = useRouter().currentRoute.value.path;

        // 1. Giriş Yapılmamışsa Koru
        // /menu veya /admin rotalarına erişimi engelle
        if ((currentPath.startsWith('/menu') || currentPath.startsWith('/admin')) && !isAuthenticated) {
            return navigateTo('/login');
        }

        // 2. Oturum Açıkken Giriş/Kayıt Engeli
        // Login veya Register sayfalarına erişimi engelle
        if ((currentPath === '/login' || currentPath === '/register') && isAuthenticated) {
            return navigateTo('/menu'); 
        }

        // 3. Admin Rotası Kontrolü
        const isAdminRoute = currentPath.startsWith('/admin/');
        const isAdminUser = localStorage.getItem('isAdmin') === 'true'; 

        if (isAdminRoute && isAuthenticated && !isAdminUser) {
            // Oturum açık, ama admin değilse, onu normal menüye yönlendir
            return navigateTo('/menu');
        }
    }
};

// Sayfa yüklenmeden hemen önce ve rota değişirken kontrol et
onBeforeMount(() => {
    checkAuth();
});

// useAuth composable'ını (veya useHead) kullanıyorsanız, buraya ekleyebilirsiniz:
// import useAuth from './composables/useAuth.js';
// useAuth(); 
</script>

<template>
  <div>
    <NuxtPage />
  </div>
</template>