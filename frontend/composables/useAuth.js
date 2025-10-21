export default function useAuth() {
  const user = useState('user', () => null);
  const token = useState('token', () => null);
  const initialized = useState('auth-initialized', () => false);
  const refreshTimer = useState('refresh-timer', () => null);
  const lastActivity = useState('last-activity', () => Date.now());

  const REFRESH_INTERVAL = 55 * 60 * 1000; // 55 dakika
  const INACTIVITY_LIMIT = 60 * 60 * 1000; // 1 saat

  // 🧠 Kullanıcı aktifse zamanı sıfırla
  if (process.client) {
    ['click', 'mousemove', 'keydown', 'scroll'].forEach(evt => {
      window.addEventListener(evt, () => {
        lastActivity.value = Date.now();
      });
    });
  }

  const startRefreshCycle = () => {
    if (refreshTimer.value) clearInterval(refreshTimer.value);
    refreshTimer.value = setInterval(async () => {
      const inactiveTime = Date.now() - lastActivity.value;
      if (inactiveTime >= INACTIVITY_LIMIT) {
        alert('Uzun süre işlem yapılmadı. Oturum kapatıldı.');
        await logout();
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
        }
      } catch (err) {
        console.error('Token yenilenemedi:', err);
        await logout();
      }
    }, REFRESH_INTERVAL);
  };

  const login = (userData) => {
    user.value = userData.user;
    token.value = userData.token;
    localStorage.setItem('authUser', JSON.stringify(userData.user));
    localStorage.setItem('authToken', userData.token);
    startRefreshCycle();
  };

  const logout = async () => {
    user.value = null;
    token.value = null;
    localStorage.removeItem('authUser');
    localStorage.removeItem('authToken');
    if (refreshTimer.value) clearInterval(refreshTimer.value);
    refreshTimer.value = null;
    await navigateTo('/login');
  };

  if (process.client && !initialized.value) {
    const storedToken = localStorage.getItem('authToken');
    const storedUser = localStorage.getItem('authUser');
    if (storedToken && storedUser) {
      user.value = JSON.parse(storedUser);
      token.value = storedToken;
      startRefreshCycle();
    }
    initialized.value = true;
  }

  const isLoggedIn = computed(() => !!token.value);
  const isAdmin = computed(() => user.value?.role === 'admin');

  return {
    user,
    token,
    isLoggedIn,
    isAdmin,
    login,
    logout,
  };
}
