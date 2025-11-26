<template>
  <div class="max-w-[1600px] mx-auto animate-fade-in-up">
    
    <!-- ÜST KARŞILAMA -->
    <div class="mb-10 flex flex-col md:flex-row justify-between items-end gap-6">
      <div>
        <h1 class="text-4xl font-black text-white mb-2 tracking-tight">
          Kontrol Paneli
        </h1>
        <p class="text-slate-400 text-lg">
          Hoş geldin, <span class="text-transparent bg-clip-text bg-gradient-to-r from-purple-400 to-pink-400 font-bold">{{ user?.name || 'Yönetici' }}</span>. Bugün sistem harika görünüyor. 🚀
        </p>
      </div>
      
      <div class="flex items-center gap-3 bg-white/5 border border-white/10 rounded-2xl px-5 py-2 backdrop-blur-md shadow-lg">
        <span class="relative flex h-3 w-3">
          <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
          <span class="relative inline-flex rounded-full h-3 w-3 bg-emerald-500"></span>
        </span>
        <div class="text-right">
          <div class="text-[10px] uppercase tracking-wider text-slate-500 font-bold">Sistem Saati</div>
          <div class="text-sm font-mono text-white font-bold">{{ currentTime }}</div>
        </div>
      </div>
    </div>

    <!-- KARTLAR GRID -->
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6">
      
      <!-- 1. QR TARAYICI (TURNİKE) - YENİ EKLENDİ -->
      <NuxtLink to="/admin/scanner" class="glass-card group relative overflow-hidden">
        <!-- Hareketli Arka Plan -->
        <div class="absolute inset-0 bg-gradient-to-br from-purple-600/20 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500 animate-pulse"></div>
        
        <div class="relative z-10 flex flex-col h-full justify-between">
          <div class="flex justify-between items-start">
            <div class="p-3 bg-purple-500/10 rounded-xl border border-purple-500/20 text-purple-400 group-hover:scale-110 transition-transform duration-300">
              <QrCodeIcon class="w-8 h-8" />
            </div>
            <div class="bg-purple-500/20 text-purple-300 text-[10px] font-bold px-2 py-1 rounded-full border border-purple-500/20">
              Görevli
            </div>
          </div>
          <div class="mt-6">
            <h3 class="text-xl font-black text-white group-hover:text-purple-300 transition-colors">QR Okuyucu</h3>
            <p class="text-sm text-slate-400 mt-1">Yemek dağıtımını başlat.</p>
          </div>
        </div>
      </NuxtLink>

      <!-- 2. ONAY BEKLEYENLER -->
      <NuxtLink to="/admin/onay" class="glass-card group">
        <div class="absolute inset-0 bg-gradient-to-br from-cyan-500/20 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
        <div class="relative z-10 flex flex-col h-full justify-between">
          <div class="flex justify-between items-start">
            <div class="p-3 bg-cyan-500/10 rounded-xl border border-cyan-500/20 text-cyan-400 group-hover:scale-110 transition-transform duration-300">
              <UserPlusIcon class="w-8 h-8" />
            </div>
            <div class="bg-cyan-500/20 text-cyan-300 text-[10px] font-bold px-2 py-1 rounded-full border border-cyan-500/20 animate-pulse">
              İşlem
            </div>
          </div>
          <div class="mt-6">
            <h3 class="text-xl font-bold text-white group-hover:text-cyan-300 transition-colors">Onaylar</h3>
            <p class="text-sm text-slate-400 mt-1">Yeni kayıtları incele.</p>
          </div>
        </div>
      </NuxtLink>

      <!-- 3. KULLANICI LİSTESİ -->
      <NuxtLink to="/admin/users" class="glass-card group">
        <div class="absolute inset-0 bg-gradient-to-br from-blue-500/20 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
        <div class="relative z-10 flex flex-col h-full justify-between">
          <div class="p-3 bg-blue-500/10 rounded-xl border border-blue-500/20 text-blue-400 group-hover:scale-110 transition-transform">
            <UsersIcon class="w-8 h-8" />
          </div>
          <div class="mt-6">
            <h3 class="text-xl font-bold text-white group-hover:text-blue-300 transition-colors">Kullanıcılar</h3>
            <p class="text-sm text-slate-400 mt-1">Tüm üyeleri yönet.</p>
          </div>
        </div>
      </NuxtLink>

      <!-- 4. SİSTEM LOGLARI (YENİ) -->
      <NuxtLink to="/admin/logs" class="glass-card group">
        <div class="absolute inset-0 bg-gradient-to-br from-slate-500/20 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
        <div class="relative z-10 flex flex-col h-full justify-between">
          <div class="p-3 bg-slate-500/10 rounded-xl border border-slate-500/20 text-slate-400 group-hover:scale-110 transition-transform">
            <DocumentTextIcon class="w-8 h-8" />
          </div>
          <div class="mt-6">
            <h3 class="text-xl font-bold text-white group-hover:text-slate-300 transition-colors">Log Kayıtları</h3>
            <p class="text-sm text-slate-400 mt-1">Sistem hareketlerini izle.</p>
          </div>
        </div>
      </NuxtLink>

      <!-- 5. BİRİM YÖNETİMİ -->
      <NuxtLink to="/admin/units" class="glass-card group">
        <div class="absolute inset-0 bg-gradient-to-br from-purple-500/20 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
        <div class="relative z-10 flex flex-col h-full justify-between">
          <div class="p-3 bg-purple-500/10 rounded-xl border border-purple-500/20 text-purple-400 group-hover:scale-110 transition-transform">
            <BuildingOffice2Icon class="w-8 h-8" />
          </div>
          <div class="mt-6">
            <h3 class="text-xl font-bold text-white group-hover:text-purple-300 transition-colors">Birim Yönetimi</h3>
            <p class="text-sm text-slate-400 mt-1">Fiyatlandırma ayarları.</p>
          </div>
        </div>
      </NuxtLink>

      <!-- 6. DUYURULAR -->
      <NuxtLink to="/admin/announcements" class="glass-card group">
        <div class="absolute inset-0 bg-gradient-to-br from-orange-500/20 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
        <div class="relative z-10 flex flex-col h-full justify-between">
          <div class="p-3 bg-orange-500/10 rounded-xl border border-orange-500/20 text-orange-400 group-hover:scale-110 transition-transform">
            <MegaphoneIcon class="w-8 h-8" />
          </div>
          <div class="mt-6">
            <h3 class="text-xl font-bold text-white group-hover:text-orange-300 transition-colors">Duyurular</h3>
            <p class="text-sm text-slate-400 mt-1">Bildirim yayınla.</p>
          </div>
        </div>
      </NuxtLink>

      <!-- 7. MENÜ EKLE -->
      <NuxtLink to="/admin/add-menu" class="glass-card group">
        <div class="absolute inset-0 bg-gradient-to-br from-emerald-500/20 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
        <div class="relative z-10 flex flex-col h-full justify-between">
          <div class="p-3 bg-emerald-500/10 rounded-xl border border-emerald-500/20 text-emerald-400 group-hover:scale-110 transition-transform">
            <ClipboardDocumentListIcon class="w-8 h-8" />
          </div>
          <div class="mt-6">
            <h3 class="text-xl font-bold text-white group-hover:text-emerald-300 transition-colors">Menü Ekle</h3>
            <p class="text-sm text-slate-400 mt-1">Günlük yemek listesi.</p>
          </div>
        </div>
      </NuxtLink>

      <!-- 8. YORUMLAR -->
      <NuxtLink to="/admin/reviews" class="glass-card group">
        <div class="absolute inset-0 bg-gradient-to-br from-pink-500/20 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
        <div class="relative z-10 flex flex-col h-full justify-between">
          <div class="p-3 bg-pink-500/10 rounded-xl border border-pink-500/20 text-pink-400 group-hover:scale-110 transition-transform">
            <ChatBubbleLeftRightIcon class="w-8 h-8" />
          </div>
          <div class="mt-6">
            <h3 class="text-xl font-bold text-white group-hover:text-pink-300 transition-colors">Yorumlar</h3>
            <p class="text-sm text-slate-400 mt-1">Geri bildirimleri oku.</p>
          </div>
        </div>
      </NuxtLink>

      <!-- 9. MENÜ GEÇMİŞİ -->
      <NuxtLink to="/admin/menus" class="glass-card group">
        <div class="absolute inset-0 bg-gradient-to-br from-indigo-500/20 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-500"></div>
        <div class="relative z-10 flex flex-col h-full justify-between">
          <div class="p-3 bg-indigo-500/10 rounded-xl border border-indigo-500/20 text-indigo-400 group-hover:scale-110 transition-transform">
            <ClipboardDocumentCheckIcon class="w-8 h-8" />
          </div>
          <div class="mt-6">
            <h3 class="text-xl font-bold text-white group-hover:text-indigo-300 transition-colors">Geçmiş</h3>
            <p class="text-sm text-slate-400 mt-1">Eski menü kayıtları.</p>
          </div>
        </div>
      </NuxtLink>

      <!-- 10. İSTATİSTİKLER -->
      <NuxtLink to="/admin/dashboard" class="glass-card group relative overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-r from-indigo-600/20 via-purple-600/20 to-blue-600/20 animate-gradient-x"></div>
        <div class="relative z-10 flex flex-col h-full justify-between">
          <div class="p-3 bg-white/10 rounded-xl border border-white/20 text-white group-hover:scale-110 transition-transform w-fit">
            <ChartBarIcon class="w-8 h-8" />
          </div>
          <div class="mt-6">
            <h3 class="text-xl font-bold text-white">Raporlar</h3>
            <p class="text-sm text-slate-300 mt-1">Finans ve analiz.</p>
          </div>
        </div>
      </NuxtLink>

    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import useAuth from '../composables/useAuth'
import {
  UserGroupIcon, UsersIcon, ClipboardDocumentListIcon, ClipboardDocumentCheckIcon,
  ChartBarIcon, BuildingOffice2Icon, MegaphoneIcon, ChatBubbleLeftRightIcon, 
  QrCodeIcon, UserPlusIcon, DocumentTextIcon
} from '@heroicons/vue/24/outline'

definePageMeta({ layout: 'admin' })
const { user } = useAuth()
const currentTime = ref('')

const tick = () => {
  currentTime.value = new Date().toLocaleTimeString('tr-TR', { hour12: false, hour:'2-digit', minute:'2-digit' })
}

onMounted(() => {
  tick()
  setInterval(tick, 1000)
})
</script>

<style scoped>
.glass-card {
  @apply relative bg-[#121212]/60 border border-white/5 rounded-3xl p-6 overflow-hidden backdrop-blur-sm transition-all duration-300 hover:border-white/20 hover:shadow-[0_0_30px_rgba(255,255,255,0.05)] hover:-translate-y-1 cursor-pointer;
}

@keyframes fadeInUp {
  from { opacity: 0; transform: translateY(20px); }
  to { opacity: 1; transform: translateY(0); }
}
.animate-fade-in-up {
  animation: fadeInUp 0.6s ease-out forwards;
}

@keyframes gradient-x {
  0%, 100% { background-position: 0% 50%; }
  50% { background-position: 100% 50%; }
}
.animate-gradient-x {
  background-size: 200% 200%;
  animation: gradient-x 5s ease infinite;
}
</style>