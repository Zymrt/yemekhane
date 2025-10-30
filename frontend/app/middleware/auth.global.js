// middleware/auth.global.js

import useAuth from '../composables/useAuth'

export default defineNuxtRouteMiddleware(async (to, from) => {
  // ----------------------------------------------------
  // 🚀 YENİ OTURUM KONTROLÜ MANTIĞI BURADA
  // ----------------------------------------------------
  
  // useAuth'dan state'lerimizi alalım
  const { initAuth, initialized, user } = useAuth()

  // Eğer 'initAuth' henüz çalışmadıysa (uygulama ilk kez yükleniyorsa)
  // 'await' ile bu fonksiyonun bitmesini BEKLİYORUZ.
  // Bu, navigasyonu duraklatır ve 'user' verisinin dolmasını garanti eder.
  if (!initialized.value) {
    console.log('--- 1. [MIDDLEWARE] Oturum kontrolü (initAuth) BAŞLADI ---');
    await initAuth();
    console.log('--- 2. [MIDDLEWARE] Oturum kontrolü (initAuth) BİTTİ ---');
  }

  // Artık 'user' state'imizin dolu veya boş olduğundan eminiz.
  // Şimdi sayfa korumalarını (guard) çalıştırabiliriz.
  
  // 1. Kural: Giriş yapmış bir kullanıcı /login veya /register'a gidemez
  if (user.value && (to.path === '/login' || to.path === '/register')) {
    console.log('--- 3. [MIDDLEWARE] Giriş yapılmış, anasayfaya yönlendiriliyor.');
    
    // Rolüne göre doğru sayfaya yönlendir
    return navigateTo(user.value.role === 'admin' ? '/admin' : '/menu');
  }

  // 2. Kural: Giriş yapmamış bir kullanıcı /admin ile başlayan bir yere gidemez
  if (!user.value && to.path.startsWith('/admin')) {
    console.log('--- 3. [MIDDLEWARE] Yetkisiz, /login sayfasına yönlendiriliyor.');
    return navigateTo('/login');
  }
  
  // 3. Kural: Giriş yapmamış bir kullanıcı /menu'ye gidemez
  if (!user.value && to.path === '/menu') {
    console.log('--- 3. [MIDDLEWARE] Yetkisiz, /login sayfasına yönlendiriliyor.');
    return navigateTo('/login');
  }

  // Diğer tüm durumlarda (örn: /login'e giden misafir) navigasyona izin ver
  console.log(`--- 3. [MIDDLEWARE] ${to.path} sayfasına erişim ONAYLANDI.`);
});