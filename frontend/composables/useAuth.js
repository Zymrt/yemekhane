export default function useAuth() {
  const user = useState('user', () => null);
  const token = useState('token', () => null);
  const initialized = useState('auth-initialized', () => false);
  const refreshTimer = useState('refresh-timer', () => null);
  const lastActivity = useState('last-activity', () => Date.now());

  const REFRESH_INTERVAL = 55 * 60 * 1000; // 55 dk
  const INACTIVITY_LIMIT = 60 * 60 * 1000; // 1 saat

  // 🧠 Kullanıcı etkileşimi takip
  if (process.client) {
    ['click', 'mousemove', 'keydown', 'scroll'].forEach(evt => {
      window.addEventListener(evt, () => {
        lastActivity.value = Date.now();
      });
    });
  }

  // 🔁 Token yenileme döngüsü
  const startRefreshCycle = () => {
    if (refreshTimer.value) clearInterval(refreshTimer.value);
    refreshTimer.value = setInterval(async () => {
      const inactiveTime = Date.now() - lastActivity.value;
      if (inactiveTime >= INACTIVITY_LIMIT) {
        console.log('⏳ İnaktif kullanıcı, sessiz çıkış...');
        await logout(false);
        return;
      }
      try {
        const response = await $fetch('http://127.0.0.1:8000/api/refresh', {
          method: 'POST',
          headers: { Authorization: `Bearer ${token.value}` },
        });
        if (response?.token) {
          token.value = response.token;
          localStorage.setItem('authToken', response.token);
          console.log('🔐 Token yenilendi');
        } else {
          await logout(false);
        }
      } catch (err) {
        console.warn('❌ Token yenilenemedi, geçersiz token.');
        await logout(false);
      }
    }, REFRESH_INTERVAL);
  };

  // 🚪 Çıkış (tam temizleme)
  const logout = async (redirect = true) => {
    user.value = null;
    token.value = null;
    localStorage.removeItem('authUser');
    localStorage.removeItem('authToken');
    if (refreshTimer.value) clearInterval(refreshTimer.value);
    refreshTimer.value = null;
    if (redirect) await navigateTo('/login');
  };

  // 🔓 Giriş
  const login = (userData) => {
    user.value = userData.user;
    token.value = userData.token;
    localStorage.setItem('authUser', JSON.stringify(userData.user));
    localStorage.setItem('authToken', userData.token);
    startRefreshCycle();
  };

  // 🚀 Sayfa yüklenince kontrol et
  if (process.client && !initialized.value) {
    const storedToken = localStorage.getItem('authToken');
    const storedUser = localStorage.getItem('authUser');

    // ✅ Önce temiz token check
    if (storedToken && storedUser) {
      (async () => {
        try {
          await $fetch('http://127.0.0.1:8000/api/user/profile', {
            headers: { Authorization: `Bearer ${storedToken}` },
          });
          user.value = JSON.parse(storedUser);
          token.value = storedToken;
          startRefreshCycle();
          console.log('✅ Oturum geçerli');
        } catch (err) {
          console.warn('🧹 Geçersiz token silindi.');
          localStorage.removeItem('authUser');
          localStorage.removeItem('authToken');
          await logout(false);
        }
      })();
    } else {
      localStorage.removeItem('authUser');
      localStorage.removeItem('authToken');
    }

    initialized.value = true;
  }

  const isLoggedIn = computed(() => !!token.value);
  const isAdmin = computed(() => user.value?.role === 'admin');

  return { user, token, isLoggedIn, isAdmin, login, logout };
}
