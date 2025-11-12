import { useRouter } from 'vue-router'
import useAuth from './useAuth'

export default async function protectGuestPage() {
  const router = useRouter()
  const { initAuth, isLoggedIn, isAdmin } = useAuth()

  if (!process.client) return

  await initAuth()

  if (isLoggedIn.value) {
    router.push(isAdmin.value ? '/admin/onay' : '/menu')
  }
}
