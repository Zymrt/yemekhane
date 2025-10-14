// frontend/utils/auth-guard.js

import { navigateTo } from '#app';
import { useRouter } from 'vue-router'; // useRouter'ı manuel import etmeliyiz

export function checkAuthGuard() {
    if (process.client) { 
        const token = localStorage.getItem('authToken');
        const isAuthenticated = !!token;
        
        // Nuxt'ın useRouter'ını kullanarak mevcut rotayı alıyoruz
        const currentPath = useRouter().currentRoute.value.path; 

        // 1. Oturum Açıkken Giriş/Kayıt Engeli (LOGIN sayfasını koruyan mantık)
        if ((currentPath === '/login' || currentPath === '/register') && isAuthenticated) {
            // Kullanıcıyı menü sayfasına yönlendir
            return navigateTo('/menu'); 
        }
        
        // 2. Korumalı Rotayı Engelleme (Bu, menu.vue'yu korur)
        if ((currentPath.startsWith('/menu') || currentPath.startsWith('/admin')) && !isAuthenticated) {
            return navigateTo('/login');
        }

        // 3. Admin Kontrolü
        const isAdminRoute = currentPath.startsWith('/admin/');
        const isAdminUser = localStorage.getItem('isAdmin') === 'true'; 
        if (isAdminRoute && isAuthenticated && !isAdminUser) {
            return navigateTo('/menu');
        }
    }
}