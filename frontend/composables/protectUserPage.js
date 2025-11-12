import { useRouter } from 'vue-router'
import useAuth from './useAuth'

export default async function protectUserPage() {
  const router = useRouter()
  const { initAuth, isLoggedIn } = useAuth()

  if (!process.client) return

  await initAuth()

  if (!isLoggedIn.value) {
    router.push('/login')
  }
}
