// frontend/composables/useAuth.js

import { ref } from 'vue';

// GLOBAL DURUM (State)
const user = ref(null); 
const isAuthenticated = ref(false); 
const isAdmin = ref(false); 

// API ENDPOINTS
const LOGOUT_API_URL = 'http://127.0.0.1:8000/logout'; 
// YENİ ROTA: Backend'de oluşturduğumuz profil çekme rotası
const PROFILE_API_URL = 'http://127.0.0.1:8000/api/user/profile'; 
const API_BASE_URL = 'http://127.0.0.1:8000'; 

// --- YENİ FONKSİYON: Kullanıcının güncel profilini çekme ---
const fetchUserProfile = async (token) => {
    try {
        const response = await $fetch(PROFILE_API_URL, {
            method: 'GET',
            headers: {
                'Authorization': `Bearer ${token}`
            },
            baseURL: API_BASE_URL
        });

        // Backend'den gelen detaylı kullanıcı verisiyle user state'ini güncelle
        user.value = response.user; 
        
        // Admin rolünü de güncelleyelim (Token yenilendiyse veya değiştiyse diye)
        isAdmin.value = !!response.user.is_admin;

        return response.user;

    } catch (e) {
        console.error('Profil Çekme Hatası:', e);
        // Profil çekilemezse (Token süresi dolmuş vb.), oturumu kapat
        await logout(); 
        return null;
    }
};

// --- AUTH MANTIĞI ---

// Login başarılı olduğunda bu fonksiyonu çağırıp durumu güncelleyeceğiz.
const setAuthData = async (token, userData) => { // ASENKRON YAPILDI
    if (process.client) {
        localStorage.setItem('authToken', token);
        localStorage.setItem('isAdmin', userData.is_admin ? 'true' : 'false'); 
    }
    isAuthenticated.value = true;
    
    // GİRİŞ BAŞARILIYSA HEMEN GÜNCEL PROFİLİ ÇEK
    await fetchUserProfile(token); 
};

// Çıkış Yapma Fonksiyonu (Logout) aynı kalır
const logout = async () => {
    const token = process.client ? localStorage.getItem('authToken') : null;

    if (token) {
        try {
            await $fetch(LOGOUT_API_URL, {
                method: 'POST',
                headers: {
                    'Authorization': `Bearer ${token}`
                },
                baseURL: API_BASE_URL
            });
        } catch (e) {
            console.error('Logout API hatası (Önemli Değil):', e);
        }
    }
    
    // Yerel depolamayı ve durumu temizle
    if (process.client) {
        localStorage.removeItem('authToken');
        localStorage.removeItem('isAdmin');
    }
    isAuthenticated.value = false;
    user.value = null;
    isAdmin.value = false; 
    
    await navigateTo('/login');
};

// Uygulama yüklendiğinde mevcut durumu kontrol eder
const checkAuthStatus = async () => { // ASENKRON YAPILDI
    if (process.client) {
        const token = localStorage.getItem('authToken');
        const adminStatus = localStorage.getItem('isAdmin') === 'true';

        if (token) {
            isAuthenticated.value = true;
            isAdmin.value = adminStatus;
            
            // Eğer token varsa, profil detaylarını çekip user state'ini doldur.
            await fetchUserProfile(token); 
        } else {
            isAuthenticated.value = false;
            isAdmin.value = false;
        }
    }
};

export default function useAuth() {
    // Composables her kullanıldığında durumu kontrol eder
    // Bu ASENKRON fonksiyonu burada çağırmak performansı etkileyebilir,
    // ancak uygulamada durumu hemen görmemiz için şimdilik kalsın.
    checkAuthStatus(); 

    return {
        user, // Artık bakiyeyi ve diğer detayları içerecek!
        isAuthenticated,
        isAdmin,
        logout,
        setAuthData, 
        checkAuthStatus,
    };
}