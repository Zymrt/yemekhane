<template>
  <div class="space-y-10">
    <div class="p-8 backdrop-blur-lg bg-white/10 rounded-2xl shadow-xl border border-white/10">
      <h1 class="text-4xl font-bold text-white">Hoş Geldin, <span class="text-cyan-300">{{ user?.name }}!</span></h1>
      <p class="text-gray-300 mt-2">Yönetim paneline hoş geldin. Buradan tüm işlemleri kolayca yönetebilirsin.</p>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
      
      <NuxtLink 
        to="/admin/onay" 
        class="group relative backdrop-blur-lg bg-white/10 p-8 rounded-2xl shadow-xl border border-white/10 transition-all duration-300 hover:bg-white/20 hover:-translate-y-2"
      >
        <div class="flex items-center justify-center w-16 h-16 bg-cyan-300/10 rounded-full mb-5 transition-all duration-300 group-hover:scale-110 group-hover:bg-cyan-400/20">
          <UserGroupIcon class="w-8 h-8 text-cyan-300" />
        </div>
        <h2 class="text-2xl font-bold text-white mb-2">Kullanıcı Onayları</h2>
        <p class="text-gray-300">Yeni kayıt olan kullanıcıları onayla veya reddet.</p>
        <div class="absolute top-4 right-4 text-xs font-bold bg-orange-400/80 text-white px-2 py-1 rounded-full">
          İşlem Gerekli
        </div>
      </NuxtLink>

      <div class="relative backdrop-blur-lg bg-white/5 p-8 rounded-2xl shadow-xl border border-white/10 filter grayscale opacity-60 cursor-not-allowed">
         <div class="flex items-center justify-center w-16 h-16 bg-gray-500/10 rounded-full mb-5">
          <ClipboardDocumentListIcon class="w-8 h-8 text-gray-400" />
        </div>
        <h2 class="text-2xl font-bold text-white mb-2">Menü Yönetimi</h2>
        <p class="text-gray-300">(Yakında) Günlük yemek menüsünü düzenle.</p>
      </div>

      <div class="relative backdrop-blur-lg bg-white/5 p-8 rounded-2xl shadow-xl border border-white/10 filter grayscale opacity-60 cursor-not-allowed">
         <div class="flex items-center justify-center w-16 h-16 bg-gray-500/10 rounded-full mb-5">
          <ChartBarIcon class="w-8 h-8 text-gray-400" />
        </div>
        <h2 class="text-2xl font-bold text-white mb-2">Raporlar</h2>
        <p class="text-gray-300">(Yakında) Kullanıcı hareketlerini görüntüle.</p>
      </div>

    </div>
  </div>
</template>

<script setup>
definePageMeta({
  layout: 'admin'
});

import useAuthGuard from '../composables/useAuthGuard';
import useAuth from '../composables/useAuth';
import { 
  UserGroupIcon, 
  ClipboardDocumentListIcon, 
  ChartBarIcon 
} from '@heroicons/vue/24/outline';

const { protectAdminPage } = useAuthGuard();
protectAdminPage();

const { user } = useAuth();
</script>