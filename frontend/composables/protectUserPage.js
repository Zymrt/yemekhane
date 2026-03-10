import useAuth from './useAuth'

export default async function protectUserPage() {
  const { initAuth, isLoggedIn, initialized } = useAuth()

  if (!process.client) return

  // Hızlı yol: zaten giriş yapılmışsa tekrar kontrol etme
  if (initialized.value && isLoggedIn.value) return

  await initAuth()

  if (!isLoggedIn.value) {
    const route = useRoute()
    await navigateTo('/login?redirect=' + encodeURIComponent(route.fullPath))
  }
}
