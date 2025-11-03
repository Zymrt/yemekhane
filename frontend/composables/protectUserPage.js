import { useRouter } from 'vue-router'
import { useCookie } from '#app'

export default function protectUserPage() {
  const router = useRouter()
  const token = useCookie('token')

  // Kullanıcı girişi yoksa login sayfasına yönlendir
  if (!token.value) {
    router.push('/login')
  }
}
