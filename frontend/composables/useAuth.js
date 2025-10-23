export default function useAuth() {
  const user = useState('user', () => null)
  const initialized = useState('auth-initialized', () => false)
  const refreshTimer = useState('refresh-timer', () => null)
  const lastActivity = useState('last-activity', () => Date.now())

  const REFRESH_INTERVAL = 55 * 60 * 1000
  const INACTIVITY_LIMIT = 60 * 60 * 1000

  if (process.client) {
    ['click', 'mousemove', 'keydown', 'scroll'].forEach(evt => {
      window.addEventListener(evt, () => {
        lastActivity.value = Date.now()
      })
    })
  }

  const startRefreshCycle = () => {
    if (refreshTimer.value) clearInterval(refreshTimer.value)

    refreshTimer.value = setInterval(async () => {
      const inactiveTime = Date.now() - lastActivity.value
      if (inactiveTime >= INACTIVITY_LIMIT) {
        console.log('⏳ İnaktif kullanıcı, çıkış...')
        await logout(false)
        return
      }

      try {
        await $fetch('http://127.0.0.1:8000/api/refresh', {
          method: 'POST',
          credentials: 'include', // 🍪 cookie üzerinden
        })
        console.log('✅ Token sessizce yenilendi (cookie)')
      } catch (err) {
        console.warn('❌ Token yenilenemedi, yeniden giriş gerekli.')
        await new Promise(r => setTimeout(r, 500))
        await logout(false)
      }
    }, REFRESH_INTERVAL)
  }

  const logout = async (redirect = true) => {
    try {
      await $fetch('http://127.0.0.1:8000/api/logout', {
        method: 'POST',
        credentials: 'include',
      })
    } catch (err) {
      console.warn('Logout sırasında hata:', err)
    }

    user.value = null
    if (refreshTimer.value) clearInterval(refreshTimer.value)
    refreshTimer.value = null

    if (redirect) await navigateTo('/login')
  }

  const login = async (credentials) => {
    try {
      const response = await $fetch('http://127.0.0.1:8000/api/login', {
        method: 'POST',
        body: JSON.stringify(credentials), // ✅ JSON string olarak gönder
        credentials: 'include',
        headers: {
          'Content-Type': 'application/json',
          'Accept': 'application/json',
        },
      })

      user.value = response.user
      startRefreshCycle()
      console.log('✅ Giriş başarılı (cookie alındı)')
      return true
    } catch (error) {
      console.error('❌ Giriş hatası:', error)
      return false
    }
  }

  if (process.client && !initialized.value) {
    (async () => {
      try {
        const response = await $fetch('http://127.0.0.1:8000/api/user/profile', {
          credentials: 'include',
        })
        user.value = response.user
        startRefreshCycle()
        console.log('✅ Oturum aktif (cookie)')
      } catch {
        console.warn('🧹 Oturum geçersiz, çıkış...')
        await logout(false)
      }
      initialized.value = true
    })()
  }

  const isLoggedIn = computed(() => !!user.value)
  const isAdmin = computed(() => user.value?.role === 'admin')

  return { user, isLoggedIn, isAdmin, login, logout }
}
