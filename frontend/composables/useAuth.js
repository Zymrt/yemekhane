// composables/useAuth.js

export default function useAuth() {
  
  // useState çağrılarını fonksiyonun İÇİNE taşıdık.
  // Bu, Nuxt'ın yaşam döngüsü (lifecycle) için doğru olan yapıdır.
  const user = useState('user', () => null);
  const token = useState('token', () => null);
  const initialized = useState('auth-initialized', () => false);

  // HAFIZAYI GÜÇLENDİRME
  if (process.client && !initialized.value) {
    const storedToken = localStorage.getItem('authToken');
    const storedUser = localStorage.getItem('authUser');
    if (storedToken && storedUser) {
      token.value = storedToken;
      user.value = JSON.parse(storedUser);
    }
    initialized.value = true;
  }
  
  // OTOMATİK KAYDETME
  watch([token, user], ([newToken, newUser]) => {
    if (process.client) {
      if (newToken && newUser) {
        localStorage.setItem('authToken', newToken);
        localStorage.setItem('authUser', JSON.stringify(newUser));
      } else {
        localStorage.removeItem('authToken');
        localStorage.removeItem('authUser');
      }
    }
  }, { deep: true });

  // GETTERS
  const isLoggedIn = computed(() => !!token.value);
  const isAdmin = computed(() => user.value?.role === 'admin');

  // ACTIONS
  const login = (userData) => {
    user.value = userData.user;
    token.value = userData.token;
  };
  
  const logout = async () => {
    user.value = null;
    token.value = null;
    await navigateTo('/login');
  };

  return {
    user,
    token,
    isLoggedIn,
    isAdmin,
    login,
    logout,
  };
}