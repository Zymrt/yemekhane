<template>
  <div class="relative min-h-screen overflow-hidden">
    <!-- Animated background (welcome ile tutarlı) -->
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
      <span v-for="n in 16" :key="n" class="particle"></span>
    </div>

    <!-- Content -->
    <div class="relative z-10 flex min-h-screen items-center justify-center p-6">
      <div class="w-full max-w-xl">
        <!-- Card -->
        <div class="backdrop-blur-xl bg-white/15 border border-white/20 rounded-2xl shadow-[0_20px_60px_rgba(0,0,0,0.35)] p-6 md:p-8 animate-fadeInUp">
          <!-- Header -->
          <div class="flex flex-col items-center mb-8 text-center">
            <div class="group relative w-24 h-24 rounded-full bg-white/15 border border-white/30 shadow-xl flex items-center justify-center overflow-hidden">
              <div class="absolute inset-0 rounded-full bg-white/10 group-hover:bg-white/20 transition-colors"></div>
              <img src="/assets/logo.jpg" alt="Mezitli Belediyesi" class="relative w-16 h-16 object-contain transition-transform duration-500 group-hover:scale-110 group-hover:rotate-3 will-change-transform"/>
            </div>
            <h2 class="text-3xl font-extrabold text-white mt-5 drop-shadow-sm tracking-tight">Yeni Hesap Oluştur</h2>
            <p class="text-white/80 text-sm mt-1 drop-shadow-sm">Mezitli Belediyesi yemekhane sistemine kayıt ol</p>
          </div>

          <!-- Form -->
          <form @submit.prevent="handleRegister" class="space-y-5">
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label for="name" class="block text-sm font-semibold text-white mb-1">Ad</label>
                <input v-model="name" id="name" type="text" required class="input" placeholder="Adınız" />
              </div>
              <div>
                <label for="surname" class="block text-sm font-semibold text-white mb-1">Soyad</label>
                <input v-model="surname" id="surname" type="text" required class="input" placeholder="Soyadınız" />
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label for="phone" class="block text-sm font-semibold text-white mb-1">Telefon Numarası</label>
                <input v-model="phone" id="phone" type="tel" required class="input" placeholder="5XX XXX XX XX" />
              </div>
              <div>
                <label for="unit" class="block text-sm font-semibold text-white mb-1">Bağlı Olduğu Birim</label>
                <input v-model="unit" id="unit" type="text" required class="input" placeholder="Müdürlük adı" />
              </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
              <div>
                <label for="password" class="block text-sm font-semibold text-white mb-1">Şifre</label>
                <div class="relative">
                  <input v-model="password" id="password" :type="show.password ? 'text' : 'password'" required class="input pr-10" placeholder="••••••••" @input="calcStrength"/>
                  <button type="button" class="show-btn" @click="toggle('password')">{{ show.password ? '🙈' : '👁️' }}</button>
                </div>
                <!-- Strength bar -->
                <div class="mt-2 h-1.5 rounded-full bg-white/10 overflow-hidden">
                  <div class="h-full" :style="{ width: strength.width, background: strength.color }"></div>
                </div>
              </div>
              <div>
                <label for="password_confirmation" class="block text-sm font-semibold text-white mb-1">Şifre Tekrarı</label>
                <div class="relative">
                  <input v-model="password_confirmation" id="password_confirmation" :type="show.password_confirmation ? 'text' : 'password'" required class="input pr-10" placeholder="••••••••"/>
                  <button type="button" class="show-btn" @click="toggle('password_confirmation')">{{ show.password_confirmation ? '🙈' : '👁️' }}</button>
                </div>
              </div>
            </div>

            <!-- File uploader -->
            <div>
              <label for="proof_document" class="block text-sm font-semibold text-white mb-1">Kurum Kimlik / Belgesi (PDF, JPG/PNG)</label>
              <label for="proof_document" class="dropzone">
                <input id="proof_document" type="file" class="sr-only" accept=".pdf,.jpg,.jpeg,.png" @change="handleFileUpload" required />
                <div class="flex items-center gap-3">
                  <div class="icon-ring"><span class="i">📎</span></div>
                  <div>
                    <p class="text-sm font-semibold text-white">Dosya yükle veya sürükle-bırak</p>
                    <p class="text-xs text-white/70">PDF, JPG, PNG desteklenir (max ~10MB)</p>
                  </div>
                </div>
                <div v-if="fileName" class="mt-3 text-xs text-white/90">Seçildi: <span class="font-semibold">{{ fileName }}</span></div>
              </label>
            </div>

            <p v-if="error" class="text-red-200 text-sm text-center pt-2">⚠️ Hata: {{ error }}</p>

            <button type="submit" :disabled="loading" class="cta-btn btn-primary w-full">
              <span class="relative z-10">{{ loading ? 'Kayıt Olunuyor...' : 'Kayıt Ol' }}</span>
              <span class="btn-shine" aria-hidden="true"></span>
            </button>

            <p class="mt-6 text-center text-sm text-white/90">
              Zaten hesabın var mı?
              <NuxtLink to="/login" class="font-semibold underline decoration-white/40 hover:decoration-white transition">Giriş Yap</NuxtLink>
            </p>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue'

const name = ref('')
const surname = ref('')
const phone = ref('')
const unit = ref('')
const password = ref('')
const password_confirmation = ref('')
const proof_document = ref<File | null>(null)
const fileName = computed(() => proof_document.value?.name || '')

const loading = ref(false)
const error = ref<string | null>(null)

const REGISTER_API_URL = '/api/register'

const show = ref<{ [k: string]: boolean }>({ password: false, password_confirmation: false })
const toggle = (k: 'password' | 'password_confirmation') => {
  show.value[k] = !show.value[k]
}

const strength = ref<{ width: string; color: string }>({ width: '0%', color: 'transparent' })
const calcStrength = () => {
  const v = password.value
  let s = 0
  if (v.length >= 8) s++
  if (/[A-Z]/.test(v)) s++
  if (/[a-z]/.test(v)) s++
  if (/\d/.test(v)) s++
  if (/[^\w]/.test(v)) s++
  const pct = Math.min(100, (s / 5) * 100)
  const color = pct < 40 ? '#ef4444' : pct < 70 ? '#f59e0b' : '#10b981'
  strength.value = { width: pct + '%', color }
}

const handleFileUpload = (e: Event) => {
  const t = e.target as HTMLInputElement
  if (t?.files && t.files[0]) {
    proof_document.value = t.files[0]
  }
}

const handleRegister = async () => {
  loading.value = true
  error.value = null

  if (password.value !== password_confirmation.value) {
    error.value = 'Şifreler eşleşmiyor.'
    loading.value = false
    return
  }
  if (!proof_document.value) {
    error.value = 'Lütfen kimlik belgenizi yükleyin.'
    loading.value = false
    return
  }

  const formData = new FormData()
  formData.append('name', name.value)
  formData.append('surname', surname.value)
  formData.append('phone', phone.value)
  formData.append('unit', unit.value)
  formData.append('password', password.value)
  formData.append('password_confirmation', password_confirmation.value)
  formData.append('proof_document', proof_document.value)

  try {
    await $fetch(REGISTER_API_URL, { method: 'POST', body: formData })
    alert('Kayıt işleminiz başarıyla alındı! Yöneticiniz onayladıktan sonra giriş yapabilirsiniz.')
    await navigateTo('/login')
  } catch (err: any) {
    const apiErrors = err?.data?.errors
    if (apiErrors) {
      error.value = (Object.values(apiErrors) as string[][]).flat().join(' | ')
    } else {
      error.value = err?.data?.message || 'Kayıt sırasında bir hata oluştu.'
    }
    console.error('Kayıt Hatası:', err)
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
/* Keyframes */
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
.particle:nth-child(15){ left:77%; bottom:-20vh; animation-duration: 26s; }
.particle:nth-child(16){ left:87%; bottom:-22vh; animation-duration: 28s; }

/* Inputs */
.input{
  @apply w-full px-4 py-2 rounded-lg border-none outline-none shadow-sm text-white placeholder-white/70 bg-white/10 focus:ring-2 focus:ring-emerald-300/70 focus:bg-white/15 transition;
}

/* Dropzone */
.dropzone{ @apply w-full rounded-xl border border-white/25 bg-white/10 hover:bg-white/15 transition p-4 cursor-pointer flex flex-col gap-2; }
.icon-ring{ @apply w-10 h-10 rounded-full bg-white/10 border border-white/20 flex items-center justify-center; }
.i{ @apply text-lg; }

/* CTA Button */
.cta-btn{ position:relative; display:inline-flex; align-items:center; justify-content:center; padding:.875rem 1.25rem; border-radius:1rem; font-weight:800; letter-spacing:.2px; transition: transform .2s ease, box-shadow .2s ease, background .2s ease; overflow:hidden; box-shadow:0 10px 25px rgba(0,0,0,.25); }
.btn-primary{ background: linear-gradient(135deg, rgba(255,255,255,0.15), rgba(255,255,255,0.08)); border:1px solid rgba(255,255,255,0.25); color:white; backdrop-filter: blur(6px); width:100%; }
.cta-btn:hover{ transform: translateY(-2px) scale(1.02); box-shadow:0 14px 30px rgba(0,0,0,.3); }
.btn-shine{ position:absolute; inset:-1px; background: linear-gradient(120deg, transparent 10%, rgba(255,255,255,.18) 40%, transparent 70%); transform: translateX(-120%); animation: shine 1.8s ease-in-out infinite; }

/* Show/Hide */
.show-btn{ position:absolute; right:.5rem; top:50%; transform:translateY(-50%); font-size:0.95rem; opacity:.8; transition: opacity .2s; }
.show-btn:hover{ opacity:1; }

/* Reduce motion */
@media (prefers-reduced-motion: reduce){ .animate-bgShift, .animate-fadeInUp, .particle, .btn-shine, .aurora-1, .aurora-2, .aurora-3{ animation:none !important; } }
</style>
