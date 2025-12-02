<template>
  <div class="relative min-h-screen overflow-hidden">
    <div class="absolute inset-0 bg-[radial-gradient(60%_80%_at_20%_10%,rgba(59,130,246,0.30),transparent),radial-gradient(55%_60%_at_80%_20%,rgba(16,185,129,0.32),transparent),radial-gradient(70%_70%_at_50%_90%,rgba(234,88,12,0.28),transparent)]">
      <div class="absolute inset-0 animate-bgShift opacity-70 bg-gradient-to-br from-blue-500 via-emerald-500 to-orange-500 mix-blend-screen"></div>
    </div>

    <div class="pointer-events-none absolute -inset-20 blur-3xl opacity-40">
      <div class="aurora aurora-1"></div>
      <div class="aurora aurora-2"></div>
      <div class="aurora aurora-3"></div>
    </div>

    <div aria-hidden="true">
      <span v-for="n in 14" :key="n" class="particle"></span>
    </div>

    <div class="relative z-10 flex min-h-screen items-center justify-center p-6">
      <div class="w-full max-w-md">
        
        <div class="backdrop-blur-xl bg-white/15 border border-white/20 rounded-2xl shadow-[0_20px_60px_rgba(0,0,0,0.35)] p-6 md:p-8 animate-fadeInUp">
          
          <div class="flex flex-col items-center mb-6 text-center">
            <div class="group relative w-20 h-20 rounded-full bg-white/15 border border-white/30 shadow-xl flex items-center justify-center overflow-hidden mb-4">
              <div class="absolute inset-0 rounded-full bg-white/10 group-hover:bg-white/20 transition-colors"></div>
              <img src="/assets/logo.jpg" alt="Logo" class="relative w-14 h-14 object-contain transition-transform duration-500 group-hover:scale-110 group-hover:rotate-3 will-change-transform"/>
            </div>
            <h1 class="text-2xl font-extrabold text-white drop-shadow-sm tracking-tight">Şifre Sıfırlama</h1>
            <p v-if="step === 0" class="text-white/80 text-sm mt-1">Hesabınıza ait e-posta adresini girin.</p>
            <p v-else class="text-white/80 text-sm mt-1">E-postanıza gelen kodu ve yeni şifrenizi girin.</p>
          </div>

          <form v-if="step === 0" @submit.prevent="requestToken" class="space-y-5">
            <div>
              <label for="email" class="block text-sm font-semibold text-white mb-1">E-posta Adresi</label>
              <input 
                v-model="form.email" 
                id="email"
                type="email" 
                placeholder="ornek@gmail.com" 
                required
                class="input"
              >
            </div>
            
            <button type="submit" :disabled="loading" class="cta-btn btn-primary w-full">
              <span class="relative z-10 flex items-center gap-2">
                <span v-if="loading" class="animate-spin text-lg">⟳</span>
                {{ loading ? 'Gönderiliyor...' : 'Kod Gönder' }}
              </span>
              <span class="btn-shine" aria-hidden="true"></span>
            </button>
          </form>

          <form v-else @submit.prevent="resetPassword" class="space-y-5">
            <div>
              <label for="token" class="block text-sm font-semibold text-white mb-1">Doğrulama Kodu</label>
              <input 
                v-model="form.token" 
                id="token"
                type="text" 
                placeholder="6 haneli kod" 
                required
                maxlength="6"
                class="input text-center tracking-[0.2em] font-mono font-bold uppercase"
              >
            </div>

            <div>
              <label for="new_password" class="block text-sm font-semibold text-white mb-1">Yeni Şifre</label>
              <div class="relative">
                <input v-model="form.password" id="new_password" :type="show.p1 ? 'text' : 'password'" placeholder="••••••••" required class="input pr-10" />
                <button type="button" class="show-btn" @click="show.p1 = !show.p1">{{ show.p1 ? '🙈' : '👁️' }}</button>
              </div>
            </div>

            <div>
              <label for="new_password_confirmation" class="block text-sm font-semibold text-white mb-1">Yeni Şifre Tekrar</label>
              <div class="relative">
                <input v-model="form.password_confirmation" id="new_password_confirmation" :type="show.p2 ? 'text' : 'password'" placeholder="••••••••" required class="input pr-10" />
                <button type="button" class="show-btn" @click="show.p2 = !show.p2">{{ show.p2 ? '🙈' : '👁️' }}</button>
              </div>
            </div>

            <button type="submit" :disabled="loading" class="cta-btn btn-primary w-full">
              <span class="relative z-10 flex items-center gap-2">
                <span v-if="loading" class="animate-spin text-lg">⟳</span>
                {{ loading ? 'Sıfırlanıyor...' : 'Şifreyi Sıfırla' }}
              </span>
              <span class="btn-shine" aria-hidden="true"></span>
            </button>
          </form>
          
          <div v-if="message.text" class="mt-4 p-3 rounded-xl text-sm text-center border transition-all duration-300"
               :class="message.type === 'success' ? 'bg-emerald-500/20 border-emerald-500/30 text-emerald-100' : 'bg-red-500/20 border-red-500/30 text-red-100'">
             {{ message.text }}
          </div>

          <p class="mt-6 text-center text-sm text-white/90">
            <NuxtLink to="/login" class="font-semibold underline decoration-white/40 hover:decoration-white transition">
              ← Giriş Ekranına Dön
            </NuxtLink>
          </p>

        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { reactive, ref } from 'vue'

const loading = ref(false)
const step = ref(0) // 0: E-posta, 1: Kod ve Şifre
const form = reactive({
  email: '',
  token: '',
  password: '',
  password_confirmation: ''
})

// Şifre göster/gizle state'i
const show = reactive({ p1: false, p2: false })

const message = reactive({ text: '', type: '' })

const requestToken = async () => {
  message.text = ''
  loading.value = true
  try {
    const res = await $fetch('/api/password/forgot', { method: 'POST', body: { email: form.email } })
    message.text = res.message || 'Doğrulama kodu e-posta adresinize gönderildi.'
    message.type = 'success'
    
    // Kullanıcı mesajı okusun diye azıcık bekletip geçirebiliriz veya direkt geçebiliriz.
    setTimeout(() => {
        step.value = 1 
        message.text = '' // Yeni ekranda mesaj kalabalık yapmasın
    }, 1000)
    
  } catch (e) {
    message.text = e.data?.message || 'E-posta isteği başarısız oldu.'
    message.type = 'error'
  } finally {
    loading.value = false
  }
}

const resetPassword = async () => {
  message.text = ''
  if (form.password !== form.password_confirmation) {
    message.text = 'Şifreler eşleşmiyor!'
    message.type = 'error'
    return
  }

  loading.value = true
  try {
    const res = await $fetch('/api/password/reset', { method: 'POST', body: form })
    message.text = res.message || 'Şifreniz başarıyla sıfırlandı. Yönlendiriliyorsunuz...'
    message.type = 'success'
    
    // Başarılı ise login'e at
    setTimeout(() => {
        navigateTo('/login')
    }, 2000)

  } catch (e) {
    message.text = e.data?.message || 'Şifre sıfırlama işlemi başarısız oldu. Kodu kontrol edin.'
    message.type = 'error'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
/* Keyframes - Diğer sayfalarla birebir aynı */
@keyframes bgShift { 0%{transform:translateY(0) scale(1);} 50%{transform:translateY(-2%) scale(1.02);} 100%{transform:translateY(0) scale(1);} }
@keyframes floatUp { 0%{transform:translateY(12px); opacity:0;} 100%{transform:translateY(0); opacity:1;} }
@keyframes shine { 0%{ transform: translateX(-120%);} 100%{ transform: translateX(220%);} }

.animate-bgShift{ animation:bgShift 14s ease-in-out infinite; }
.animate-fadeInUp{ animation:floatUp .8s cubic-bezier(.22,.61,.36,1) both; }

/* Aurora */
.aurora{ position:absolute; inset:0; background: conic-gradient(from 180deg, rgba(255,255,255,.06), rgba(255,255,255,0) 40%); border-radius:50%; filter: blur(60px); }
.aurora-1{ transform: translate(-30%,-20%) rotate(15deg); animation: auroraMove1 18s ease-in-out infinite; }
.aurora-2{ transform: translate(30%,-10%) rotate(-10deg); animation: auroraMove2 22s ease-in-out infinite; }
.aurora-3{ transform: translate(0%,20%) rotate(0deg); animation: auroraMove3 24s ease-in-out infinite; }
@keyframes auroraMove1{ 0%,100%{transform: translate(-30%,-20%) rotate(15deg);} 50%{transform: translate(-20%,-25%) rotate(10deg);} }
@keyframes auroraMove2{ 0%,100%{transform: translate(30%,-10%) rotate(-10deg);} 50%{transform: translate(25%,-5%) rotate(-6deg);} }
@keyframes auroraMove3{ 0%,100%{transform: translate(0%,20%) rotate(0deg);} 50%{transform: translate(5%,25%) rotate(4deg);} }

/* Particles */
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

/* Inputs */
.input{ @apply w-full px-4 py-3 rounded-xl border-none outline-none shadow-sm text-white placeholder-white/70 bg-white/10 focus:ring-2 focus:ring-emerald-300/70 focus:bg-white/15 transition; }

/* Buttons */
.cta-btn{ position:relative; display:inline-flex; align-items:center; justify-content:center; padding:.875rem 1.25rem; border-radius:1rem; font-weight:800; letter-spacing:.2px; transition: transform .2s ease, box-shadow .2s ease, background .2s ease; overflow:hidden; box-shadow:0 10px 25px rgba(0,0,0,.25); }
.btn-primary{ background: linear-gradient(135deg, rgba(255,255,255,0.15), rgba(255,255,255,0.08)); border:1px solid rgba(255,255,255,0.25); color:white; backdrop-filter: blur(6px); width:100%; }
.cta-btn:hover{ transform: translateY(-2px) scale(1.02); box-shadow:0 14px 30px rgba(0,0,0,.3); }
.btn-shine{ position:absolute; inset:-1px; background: linear-gradient(120deg, transparent 10%, rgba(255,255,255,.18) 40%, transparent 70%); transform: translateX(-120%); animation: shine 1.8s ease-in-out infinite; }

/* Show/Hide Btn */
.show-btn{ position:absolute; right:.75rem; top:50%; transform:translateY(-50%); font-size:1rem; opacity:.7; transition: opacity .2s; cursor: pointer;}
.show-btn:hover{ opacity:1; }

@media (prefers-reduced-motion: reduce){ .animate-bgShift, .animate-fadeInUp, .particle, .btn-shine, .aurora-1, .aurora-2, .aurora-3{ animation:none !important; } }
</style>