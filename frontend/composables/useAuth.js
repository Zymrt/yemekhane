// composables/useAuth.js

import { ref } from 'vue';

// Oturum durumunu tüm uygulamada takip edecek değişkenler
const user = ref(null); // Kullanıcı bilgisi (isim, email, vb.)
const isLoggedIn = ref(false); // Oturum açık mı?

export default function useAuth() {
  
  // 1. Durumu Başlangıçta Yükleme (Token varsa)
  const initializeAuth = () => {
    // Tarayıcı ve sunucu ortamında çalışır.
    if (process.client) { 
      const token = localStorage.getItem('authToken');
      if (token) {
        // Normalde burada token'ı çözümler (decode) veya
        // /api/auth/me gibi bir endpoint'e iste atıp kullanıcı bilgisini alırsınız.
        
        // Şimdilik sadece oturumu açık kabul edelim:
        isLoggedIn.value = true;
        // user.value = { name: 'Kullanıcı Adı', email: '...', token };
      }
    }
  };

  // 2. Çıkış Yapma Fonksiyonu
  const logout = async () => {
    // Token'ı depolamadan sil
    if (process.client) {
      localStorage.removeItem('authToken');
    }
    // Durumları sıfırla
    user.value = null;
    isLoggedIn.value = false;
    // Giriş sayfasına yönlendir
    await navigateTo('/login');
  };

  // Uygulama yüklendiğinde bir kez çalıştır
  initializeAuth();

  return {
    user,
    isLoggedIn,
    logout,
    // Login veya Register kodunuzda user'ı ve isLoggedIn'i güncelleyen fonksiyonları
    // buraya ekleyebilirsiniz (örn: loginSuccess(data))
  };
}