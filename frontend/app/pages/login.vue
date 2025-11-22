<template>
  <div class="relative min-h-screen overflow-hidden">
    <!-- Animated background (welcome/register ile tutarlı) -->
    <div class="absolute inset-0 bg-[radial-gradient(60%_80%_at_20%_10%,rgba(59,130,246,0.30),transparent),radial-gradient(55%_60%_at_80%_20%,rgba(16,185,129,0.32),transparent),radial-gradient(70%_70%_at_50%_90%,rgba(234,88,12,0.28),transparent)]">
      <div class="absolute inset-0 animate-bgShift opacity-70 bg-gradient-to-br from-blue-500 via-emerald-500 to-orange-500 mix-blend-screen"></div>
    </div>

    <!-- Aurora ribbons -->
    <div class="pointer-events-none absolute -inset-20 blur-3xl opacity-40">
      <div class="aurora aurora-1"></div>
      <div class="aurora aurora-2"></div>
      <div class="aurora aurora-3"></div>
    </div>

    <!-- Particles -->
    <div aria-hidden="true">
      <span v-for="n in 14" :key="n" class="particle"></span>
    </div>

    <!-- Content -->
    <div class="relative z-10 flex min-h-screen items-center justify-center p-6">
      <div class="w-full max-w-md">
        <!-- Card -->
        <div class="backdrop-blur-xl bg-white/15 border border-white/20 rounded-2xl shadow-[0_20px_60px_rgba(0,0,0,0.35)] p-6 md:p-8 animate-fadeInUp">
          <!-- Header -->
          <div class="flex flex-col items-center mb-8 text-center">
            <div class="group relative w-24 h-24 rounded-full bg-white/15 border border-white/30 shadow-xl flex items-center justify-center overflow-hidden">
              <div class="absolute inset-0 rounded-full bg-white/10 group-hover:bg-white/20 transition-colors"></div>
              <img src="/assets/logo.jpg" alt="Logo" class="relative w-16 h-16 object-contain transition-transform duration-500 group-hover:scale-110 group-hover:rotate-3 will-change-transform"/>
            </div>
            <h2 class="text-3xl font-extrabold text-white mt-5 drop-shadow-sm tracking-tight">Kullanıcı Girişi</h2>
          </div>

          <!-- Form -->
          <form @submit.prevent="handleLogin" class="space-y-5">
            <div>
              <label for="phone" class="block text-sm font-semibold text-white mb-1">Telefon Numarası</label>
              <input v-model="phone" id="phone" type="tel" required class="input" placeholder="5XX XXX XX XX" />
            </div>

            <div>
              <label for="password" class="block text-sm font-semibold text-white mb-1">Şifre</label>
              <div class="relative">
                <input v-model="password" id="password" :type="showPassword ? 'text' : 'password'" required class="input pr-10" placeholder="••••••••" />
                <button type="button" class="show-btn" @click="showPassword = !showPassword">{{ showPassword ? '🙈' : '👁️' }}</button>
              </div>
            </div>

            <p v-if="error" class="text-red-200 text-sm text-center mt-2">⚠️ {{ error }}</p>

            <button type="submit" :disabled="loading" class="cta-btn btn-primary w-full">
              <span class="relative z-10">{{ loading ? 'Giriş Yapılıyor...' : 'Giriş Yap' }}</span>
              <span class="btn-shine" aria-hidden="true"></span>
            </button>

            <p class="mt-6 text-center text-sm text-white/90">
              Hesabın yok mu?
              <NuxtLink to="/register" class="font-semibold underline decoration-white/40 hover:decoration-white transition">Kayıt Ol</NuxtLink>
            </p>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useRoute } from 'vue-router' // 🔹 redirect query'si için
import useAuth from '../composables/useAuth'

const route = useRoute()
const { user, login } = useAuth()

const phone = ref('')
const password = ref('')
const loading = ref(false)
const error = ref(null)
const showPassword = ref(false)

const handleLogin = async () => {
  loading.value = true
  error.value = null

  try {
    const ok = await login({ phone: phone.value, password: password.value })

    if (!ok) {
      error.value = 'Telefon veya şifre hatalı.'
      return
    }

    // 🔁 Eğer URL'de redirect query'si varsa önce onu kullan
    const redirectParam = route.query.redirect
    const redirectPath = typeof redirectParam === 'string' ? redirectParam : null

    // 🎯 Hedef:
    // 1) redirect varsa → oraya
    // 2) yoksa → role admin ise /admin, değilse /menu
    const fallbackPath = user.value?.role === 'admin' ? '/admin' : '/menu'
    const target = redirectPath || fallbackPath

    await navigateTo(target)
  } catch (err) {
    console.error('Login Hatası:', err)
    error.value = 'Sunucuya bağlanılamadı veya hata oluştu.'
  } finally {
    loading.value = false
  }
}
</script>


<style scoped>
@keyframes bgShift { 0%{transform:translateY(0) scale(1);} 50%{transform:translateY(-2%) scale(1.02);} 100%{transform:translateY(0) scale(1);} }
@keyframes floatUp { 0%{transform:translateY(12px); opacity:0;} 100%{transform:translateY(0); opacity:1;} }
@keyframes shine { 0%{ transform: translateX(-120%);} 100%{ transform: translateX(220%);} }

.animate-bgShift{ animation:bgShift 14s ease-in-out infinite; }
.animate-fadeInUp{ animation:floatUp .8s cubic-bezier(.22,.61,.36,1) both; }

.aurora{ position:absolute; inset:0; background: conic-gradient(from 180deg, rgba(255,255,255,.06), rgba(255,255,255,0) 40%); border-radius:50%; filter: blur(60px); }
.aurora-1{ transform: translate(-30%,-20%) rotate(15deg); animation: auroraMove1 18s ease-in-out infinite; }
.aurora-2{ transform: translate(30%,-10%) rotate(-10deg); animation: auroraMove2 22s ease-in-out infinite; }
.aurora-3{ transform: translate(0%,20%) rotate(0deg); animation: auroraMove3 24s ease-in-out infinite; }
@keyframes auroraMove1{ 0%,100%{transform: translate(-30%,-20%) rotate(15deg);} 50%{transform: translate(-20%,-25%) rotate(10deg);} }
@keyframes auroraMove2{ 0%,100%{transform: translate(30%,-10%) rotate(-10deg);} 50%{transform: translate(25%,-5%) rotate(-6deg);} }
@keyframes auroraMove3{ 0%,100%{transform: translate(0%,20%) rotate(0deg);} 50%{transform: translate(5%,25%) rotate(4deg);} }

.particle{ position:absolute; width:6px; height:6px; background:rgba(255,255,255,0.9); border-radius:9999px; box-shadow:0 0 18px rgba(255,255,255,0.9); animation: particleFloat linear infinite; }
@keyframes particleFloat{ 0%{ transform: translateY(0) scale(1); opacity:.7;} 100%{ transform: translateY(-120vh) scale(1.1); opacity:0;} }
.particle:nth-child(1){ left:12%; bottom:-10vh; animation-duration: 18s; }
.particle:nth-child(2){ left:22%; bottom:-12vh; animation-duration: 21s; }
.particle:nth-child(3){ left:32%; bottom:-8vh; animation-duration: 16s; }
.particle:nth-child(4){ left:42%; bottom:-15vh; animation-duration: 23s; }
.particle:nth-child(5){ left:52%; bottom:-9vh; animation-duration: 19s; }
.particle:nth-child(6){ left:62%; bottom:-13vh; animation-duration: 20s; }
.particle:nth-child(7){ left:72%; bottom:-11vh; animation-duration: 24s; }
.particle:nth-child(8){ left:82%; bottom:-14vh; animation-duration: 22s; }
.particle:nth-child(9){ left:17%; bottom:-16vh; animation-duration: 25s; }
.particle:nth-child(10){ left:27%; bottom:-18vh; animation-duration: 26s; }
.particle:nth-child(11){ left:37%; bottom:-17vh; animation-duration: 18s; }
.particle:nth-child(12){ left:47%; bottom:-15vh; animation-duration: 20s; }
.particle:nth-child(13){ left:57%; bottom:-19vh; animation-duration: 22s; }
.particle:nth-child(14){ left:67%; bottom:-18vh; animation-duration: 24s; }

.input{ @apply w-full px-4 py-2 rounded-lg border-none outline-none shadow-sm text-white placeholder-white/70 bg-white/10 focus:ring-2 focus:ring-emerald-300/70 focus:bg-white/15 transition; }

.cta-btn{ position:relative; display:inline-flex; align-items:center; justify-content:center; padding:.875rem 1.25rem; border-radius:1rem; font-weight:800; letter-spacing:.2px; transition: transform .2s ease, box-shadow .2s ease, background .2s ease; overflow:hidden; box-shadow:0 10px 25px rgba(0,0,0,.25); }
.btn-primary{ background: linear-gradient(135deg, rgba(255,255,255,0.15), rgba(255,255,255,0.08)); border:1px solid rgba(255,255,255,0.25); color:white; backdrop-filter: blur(6px); width:100%; }
.cta-btn:hover{ transform: translateY(-2px) scale(1.02); box-shadow:0 14px 30px rgba(0,0,0,.3); }
.btn-shine{ position:absolute; inset:-1px; background: linear-gradient(120deg, transparent 10%, rgba(255,255,255,.18) 40%, transparent 70%); transform: translateX(-120%); animation: shine 1.8s ease-in-out infinite; }

.show-btn{ position:absolute; right:.5rem; top:50%; transform:translateY(-50%); font-size:0.95rem; opacity:.8; transition: opacity .2s; }
.show-btn:hover{ opacity:1; }

@media (prefers-reduced-motion: reduce){ .animate-bgShift, .animate-fadeInUp, .particle, .btn-shine, .aurora-1, .aurora-2, .aurora-3{ animation:none !important; } }
</style>
