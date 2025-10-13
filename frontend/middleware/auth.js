// middleware/auth.js

import useAuth from '../composables/useAuth';

export default defineNuxtRouteMiddleware((to, from) => {
  // useAuth composable'ını kullanarak oturum durumunu al
  const { isLoggedIn } = useAuth();
  
  // Gidilmek istenen rota (to) korumalı bir rota ise (Ör: /profile)
  // ve kullanıcı girişi yapılmamışsa, login sayfasına yönlendir.
  
  // Örnek: Eğer rota adı 'profile' veya '/profile' ile başlıyorsa
  if (to.path.startsWith('/profile') && !isLoggedIn.value) {
    return navigateTo('/login');
  }
  
  // Örnek: Eğer kullanıcı zaten giriş yapmışsa ve '/login' sayfasına gitmeye çalışıyorsa
  if ((to.path === '/login' || to.path === '/register') && isLoggedIn.value) {
    return navigateTo('/'); // Ana sayfaya yönlendir
  }
});