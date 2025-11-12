<template>
  <!-- Fullscreen animated gradient with subtle aurora & particles -->
  <div class="relative min-h-screen overflow-hidden text-white">
    <!-- Animated gradient backdrop -->
    <div class="absolute inset-0 bg-[radial-gradient(60%_80%_at_20%_10%,rgba(255,165,0,0.35),transparent),radial-gradient(50%_60%_at_80%_20%,rgba(16,185,129,0.35),transparent),radial-gradient(70%_70%_at_50%_90%,rgba(59,130,246,0.35),transparent)]">
      <div class="absolute inset-0 animate-bgShift opacity-70 bg-gradient-to-br from-orange-500 via-emerald-500 to-blue-600 mix-blend-screen"></div>
    </div>

    <!-- Aurora ribbons -->
    <div class="pointer-events-none absolute -inset-20 blur-3xl opacity-40">
      <div class="aurora aurora-1"></div>
      <div class="aurora aurora-2"></div>
      <div class="aurora aurora-3"></div>
    </div>

    <!-- Floating particles -->
    <div aria-hidden="true">
      <span v-for="n in 18" :key="n" class="particle"></span>
    </div>

    <!-- Content -->
    <div class="relative z-10 flex min-h-screen items-center justify-center p-6">
      <div class="w-full max-w-2xl">
        <div v-if="!isLoggedIn" class="text-center space-y-10">
          <!-- Logo card -->
          <div class="group mx-auto w-40 h-40 rounded-full bg-white/10 backdrop-blur-xl border border-white/20 shadow-2xl flex items-center justify-center overflow-hidden animate-fadeInUp">
            <div class="absolute inset-0 rounded-full bg-white/5 group-hover:bg-white/10 transition-colors"></div>
            <img
              src="/assets/logo.jpg"
              alt="Mezitli Belediyesi"
              class="relative w-28 h-28 object-contain transition-transform duration-500 group-hover:scale-110 group-hover:rotate-3 will-change-transform"
            />
          </div>

          <!-- Headline -->
          <div class="animate-fadeInUp [animation-delay:120ms]">
            <h1 class="text-4xl md:text-5xl font-extrabold drop-shadow-sm tracking-tight">
              Belediye Yemekhane
            </h1>
            <p class="mt-3 text-base md:text-lg text-white/90">
              Hoş geldin 👋 <span class="font-medium">Giriş yap</span> ya da <span class="font-medium">hemen kayıt ol</span>.
            </p>
          </div>

          <!-- CTA buttons -->
          <div class="mx-auto flex flex-col sm:flex-row items-center justify-center gap-4 sm:gap-6 animate-fadeInUp [animation-delay:220ms]">
            <NuxtLink
              to="/login"
              class="cta-btn btn-primary"
            >
              <span class="relative z-10">Giriş Yap</span>
              <span class="btn-shine" aria-hidden="true"></span>
            </NuxtLink>

            <NuxtLink
              to="/register"
              class="cta-btn btn-secondary"
            >
              <span class="relative z-10">Kayıt Ol</span>
              <span class="btn-shine" aria-hidden="true"></span>
            </NuxtLink>
          </div>

          <!-- Tiny helper text -->
          <p class="text-xs md:text-sm text-white/70 animate-fadeInUp [animation-delay:340ms]">
            Devam ederek kullanım koşullarını kabul etmiş olursun.
          </p>
        </div>

        <div v-else class="text-center animate-fadeInUp">
          <p class="text-2xl font-semibold">Yönlendiriliyorsunuz... 🚀</p>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup lang="js">
import useAuth from '../composables/useAuth'
import protectGuestPage from '../composables/protectGuestPage'

const { isLoggedIn } = useAuth()
await protectGuestPage()
</script>

<style scoped>
/* Keyframes */
@keyframes bgShift {
  0% { transform: translateY(0) scale(1); }
  50% { transform: translateY(-2%) scale(1.02); }
  100% { transform: translateY(0) scale(1); }
}
@keyframes floatUp {
  0% { transform: translateY(12px); opacity: 0; }
  100% { transform: translateY(0); opacity: 1; }
}
@keyframes shine {
  0% { transform: translateX(-120%); }
  100% { transform: translateX(220%); }
}

/* Utilities (scoped) */
.animate-bgShift { animation: bgShift 14s ease-in-out infinite; }
.animate-fadeInUp { animation: floatUp .8s cubic-bezier(.22,.61,.36,1) both; }

/* Aurora ribbons */
.aurora { position: absolute; inset: 0; background: conic-gradient(from 180deg, rgba(255,255,255,.06), rgba(255,255,255,0) 40%); border-radius: 50%; filter: blur(60px); }
.aurora-1 { transform: translate(-30%, -20%) rotate(15deg); animation: auroraMove1 18s ease-in-out infinite; }
.aurora-2 { transform: translate(30%, -10%) rotate(-10deg); animation: auroraMove2 22s ease-in-out infinite; }
.aurora-3 { transform: translate(0%, 20%) rotate(0deg); animation: auroraMove3 24s ease-in-out infinite; }
@keyframes auroraMove1 { 0%,100%{transform: translate(-30%,-20%) rotate(15deg);} 50%{transform: translate(-20%,-25%) rotate(10deg);} }
@keyframes auroraMove2 { 0%,100%{transform: translate(30%,-10%) rotate(-10deg);} 50%{transform: translate(25%,-5%) rotate(-6deg);} }
@keyframes auroraMove3 { 0%,100%{transform: translate(0%,20%) rotate(0deg);} 50%{transform: translate(5%,25%) rotate(4deg);} }

/* Particles */
.particle {
  position: absolute;
  width: 6px; height: 6px;
  background: rgba(255,255,255,0.9);
  border-radius: 9999px;
  box-shadow: 0 0 18px rgba(255,255,255,0.9);
  animation: particleFloat linear infinite;
}
@keyframes particleFloat {
  0% { transform: translateY(0) scale(1); opacity: .7; }
  100% { transform: translateY(-120vh) scale(1.1); opacity: 0; }
}
/* Randomize particle positions & durations */
.particle:nth-child(1) { left: 10%; bottom: -10vh; animation-duration: 18s; }
.particle:nth-child(2) { left: 20%; bottom: -12vh; animation-duration: 21s; }
.particle:nth-child(3) { left: 30%; bottom: -8vh; animation-duration: 16s; }
.particle:nth-child(4) { left: 40%; bottom: -15vh; animation-duration: 23s; }
.particle:nth-child(5) { left: 50%; bottom: -9vh; animation-duration: 19s; }
.particle:nth-child(6) { left: 60%; bottom: -13vh; animation-duration: 20s; }
.particle:nth-child(7) { left: 70%; bottom: -11vh; animation-duration: 24s; }
.particle:nth-child(8) { left: 80%; bottom: -14vh; animation-duration: 22s; }
.particle:nth-child(9) { left: 15%; bottom: -16vh; animation-duration: 25s; }
.particle:nth-child(10){ left: 25%; bottom: -18vh; animation-duration: 26s; }
.particle:nth-child(11){ left: 35%; bottom: -17vh; animation-duration: 18s; }
.particle:nth-child(12){ left: 45%; bottom: -15vh; animation-duration: 20s; }
.particle:nth-child(13){ left: 55%; bottom: -19vh; animation-duration: 22s; }
.particle:nth-child(14){ left: 65%; bottom: -18vh; animation-duration: 24s; }
.particle:nth-child(15){ left: 75%; bottom: -20vh; animation-duration: 26s; }
.particle:nth-child(16){ left: 85%; bottom: -22vh; animation-duration: 28s; }
.particle:nth-child(17){ left: 5%;  bottom: -21vh; animation-duration: 29s; }
.particle:nth-child(18){ left: 90%; bottom: -23vh; animation-duration: 30s; }

/* CTA buttons */
.cta-btn {
  position: relative;
  display: inline-flex; align-items: center; justify-content: center;
  padding: 0.875rem 1.5rem; /* py-3.5 px-6 */
  border-radius: 1rem; /* rounded-2xl-ish */
  font-weight: 700; letter-spacing: .2px;
  transition: transform .2s ease, box-shadow .2s ease, background .2s ease; 
  overflow: hidden;
  box-shadow: 0 10px 25px rgba(0,0,0,.25);
}
.btn-primary {
  background: linear-gradient(135deg, #ffffff, #f5f5f5);
  color: #b45309; /* orange-700 */
}
.btn-secondary {
  background: linear-gradient(135deg, rgba(255,255,255,0.15), rgba(255,255,255,0.08));
  border: 1px solid rgba(255,255,255,0.25);
  color: white;
  backdrop-filter: blur(6px);
}
.cta-btn:hover { transform: translateY(-2px) scale(1.02); box-shadow: 0 14px 30px rgba(0,0,0,.3); }
.cta-btn:active { transform: translateY(0) scale(.99); }

/* Shimmer */
.btn-shine {
  position: absolute; inset: -1px; 
  background: linear-gradient(120deg, transparent 10%, rgba(255,255,255,.18) 40%, transparent 70%);
  transform: translateX(-120%);
  animation: shine 1.8s ease-in-out infinite;
}

/* Reduce motion */
@media (prefers-reduced-motion: reduce) {
  .animate-bgShift, .animate-fadeInUp, .particle, .btn-shine, .aurora-1, .aurora-2, .aurora-3 { animation: none !important; }
}
</style>
