import { useRouter } from 'vue-router'
import { useCookie } from '#app'

export default function protectGuestPage() {
  const router = useRouter()
  const token = useCookie('token')

  // Kullanıcı giriş yaptıysa anında yönlendir
  if (token.value) {
    // istersen burada role göre yönlendirme yapabiliriz
    // örn: if (user.value.role === 'admin') router.push('/admin/onay')
    router.push('/menu')
  }
}
