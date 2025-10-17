import useAuth from '../composables/useAuth';

export default function useAuthGuard() {
  const { isLoggedIn, isAdmin } = useAuth();
  
  // 'useRouter()'ı tamamen kaldırdık, çünkü 'navigateTo' global bir fonksiyondur.

  const protectGuestPage = () => {
    // 'process.client' kontrolünü kaldırdık, artık sunucuda da çalışacak!
    if (isLoggedIn.value) {
      if (isAdmin.value) {
        // DEĞİŞTİ: router.push -> navigateTo
        return navigateTo('/admin'); 
      } else {
        // DEĞİŞTİ: router.push -> navigateTo
        return navigateTo('/menu');
      }
    }
  };

  const protectAdminPage = () => {
    // 'process.client' kontrolünü kaldırdık
    if (!isAdmin.value) {
      // DEĞİŞTİ: router.push -> navigateTo
      return navigateTo('/login');
    }
  };

  const protectUserPage = () => {
    // 'process.client' kontrolünü kaldırdık
    if (!isLoggedIn.value) {
      // DEĞİŞTİ: router.push -> navigateTo
      return navigateTo('/login');
    }
  };

  return {
    protectGuestPage,
    protectAdminPage,
    protectUserPage,
  };
}