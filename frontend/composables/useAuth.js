// composables/useAuth.js
// 🚀 Cookie tabanlı, otomatik yenilemeli, güvenli auth yönetimi

export default function useAuth() {
  const user = useState('user', () => null)
  const initialized = useState('auth-initialized', () => false)
  const refreshTimer = useState('refresh-timer', () => null)
  const lastActivity = useState('last-activity', () => Date.now())

  // ⚙️ Ayarlar
  const REFRESH_INTERVAL = 55 * 60 * 1000   // 55 dakika
  const INACTIVITY_LIMIT = 60 * 60 * 1000   // 1 saat

  // 🖱 Kullanıcı etkileşimi takibi (inactivity reset)
  if (process.client) {
    ['click', 'mousemove', 'keydown', 'scroll'].forEach(evt => {
      window.addEventListener(evt, () => (lastActivity.value = Date.now()))
    })
  }

  // ♻️ Otomatik token yenileme döngüsü
  const startRefreshCycle = () => {
    if (!process.client) return          // 🔹 SSR'de interval başlatma
    if (refreshTimer.value) clearInterval(refreshTimer.value)

    refreshTimer.value = setInterval(async () => {
      const inactiveTime = Date.now() - lastActivity.value
      if (inactiveTime >= INACTIVITY_LIMIT) {
        console.log('⚠️ Kullanıcı uzun süre işlem yapmadı, çıkış yapılıyor...')
        await logout(false)
        return
      }

      try {
        await $fetch('/api/refresh', { method: 'POST', credentials: 'include' })
        console.log('🔄 Token sessizce yenilendi (cookie üzerinden)')
      } catch {
        await logout(false)
      }
    }, REFRESH_INTERVAL)
  }

  // 🚪 Çıkış fonksiyonu
  const logout = async (redirect = true) => {
    try {
      await $fetch('/api/logout', { method: 'POST', credentials: 'include' })
    } catch (err) {
      console.warn('Logout hatası:', err)
    }

    user.value = null
    if (refreshTimer.value) clearInterval(refreshTimer.value)
    refreshTimer.value = null

    if (redirect && process.client) await navigateTo('/login')
  }

  // 🔐 Giriş fonksiyonu
  const login = async (credentials) => {
    try {
      const res = await $fetch('/api/login', {
        method: 'POST',
        body: credentials,
        credentials: 'include', // 🍪 cookie otomatik saklanır
        headers: { Accept: 'application/json' },
      })
      user.value = res.user
      startRefreshCycle()
      console.log('✅ Giriş başarılı.')
      return true
    } catch (err) {
      console.error('❌ Giriş hatası:', err)
      return false
    }
  }

  // 🧠 Oturum doğrulama (uygulama açılışında / refresh'te)
  const initAuth = async () => {
    // Zaten initialize olduysa tekrar çalışma
    if (initialized.value) return

    try {
      // 🚀 KRİTİK DÜZELTME BURADA:
      // SSR sırasında (Sayfa F5 yapıldığında), tarayıcıdan gelen cookie'leri
      // yakalayıp Laravel API isteğine eklememiz lazım.
      const headers = useRequestHeaders(['cookie'])

      const res = await $fetch('/api/user/profile', {
        headers: headers // <--- Cookie'yi API'ye taşıyan sihirli kısım
      })

      user.value = res.user
      startRefreshCycle()
      console.log('✅ Aktif oturum doğrulandı.')
    } catch (err) {
      user.value = null
      console.warn('❌ Oturum bulunamadı veya süresi dolmuş.')
    } finally {
      initialized.value = true
    }
  }

  // ⚙️ Durumlar
  const isLoggedIn = computed(() => !!user.value)
  const isAdmin = computed(() => user.value?.role === 'admin')

  // 🎯 Return
  return {
    user,
    initialized,
    isLoggedIn,
    isAdmin,
    login,
    logout,
    initAuth
  }
}