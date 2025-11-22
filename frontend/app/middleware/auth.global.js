// middleware/auth.global.js
import useAuth from '../composables/useAuth'

export default defineNuxtRouteMiddleware(async (to, from) => {
  // 1. Auto-import kullanıyoruz, manuel import'a gerek yok.
  const { initAuth, initialized, user } = useAuth()

  // 2. Oturum kontrolü (Server ve Client uyumlu)
  if (!initialized.value) {
    await initAuth()
  }

  const isLoggedIn = !!user.value
  const isAdmin = user.value?.role === 'admin'
  
  // 3. Sayfa Korumaları
  
  // A) Zaten giriş yapmış biri Login/Register sayfasına girmeye çalışırsa:
  if (isLoggedIn && (to.path === '/login' || to.path === '/register')) {
    return navigateTo(isAdmin ? '/admin' : '/menu')
  }

  // B) Giriş yapmamış biri korumalı sayfalara (Admin veya Menu) girmeye çalışırsa:
  // Not: '/api' isteklerini middleware engellememeli.
  if (!isLoggedIn && (to.path.startsWith('/admin') || to.path === '/menu')) {
     // API isteği değilse login'e at
     if (!to.path.startsWith('/api')) {
        return navigateTo('/login')
     }
  }

  // C) Admin olmayan biri Admin paneline girmeye çalışırsa:
  if (isLoggedIn && !isAdmin && to.path.startsWith('/admin')) {
    return navigateTo('/menu') // Normal kullanıcıyı menüye geri gönder
  }
})