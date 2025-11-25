<template>
  <div class="max-w-[1200px] mx-auto animate-fade-in">
    
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
      <div>
        <h1 class="text-3xl font-bold text-white flex items-center gap-3">
          <span class="p-2 bg-emerald-500/10 rounded-xl border border-emerald-500/20 text-emerald-400">
            <ClipboardDocumentListIcon class="w-8 h-8" />
          </span>
          Menü Yönetimi
        </h1>
        <p class="text-slate-400 mt-2 ml-1">Günlük yemek menüsünü sisteme girin ve yönetin.</p>
      </div>
      
      <div class="flex items-center gap-4">
        <div class="bg-[#121212]/80 backdrop-blur-md border border-white/10 rounded-xl px-5 py-2.5 text-sm shadow-lg">
          <span class="text-slate-400">📅 Bugün:</span>
          <span class="font-bold text-emerald-400 ml-1">{{ todayDate }}</span>
        </div>
        <NuxtLink to="/admin" class="px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm hover:bg-white/10 transition flex items-center gap-2 text-slate-300">
          <ArrowLeftIcon class="w-4 h-4" /> İptal
        </NuxtLink>
      </div>
    </div>

    <!-- DASHBOARD MINI INFO (İstatistikler) -->
    <div class="grid md:grid-cols-3 gap-6 mb-8">
      <div class="bg-[#121212]/60 backdrop-blur-md border border-white/5 p-5 rounded-2xl shadow-lg hover:border-emerald-500/20 transition group">
        <div class="text-xs font-bold text-slate-500 uppercase tracking-wider group-hover:text-emerald-400 transition-colors">Toplam Öğe</div>
        <div class="text-2xl font-black text-white mt-1">{{ menuItems.length }}</div>
      </div>
      <div class="bg-[#121212]/60 backdrop-blur-md border border-white/5 p-5 rounded-2xl shadow-lg hover:border-emerald-500/20 transition group">
        <div class="text-xs font-bold text-slate-500 uppercase tracking-wider group-hover:text-emerald-400 transition-colors">Durum</div>
        <div class="text-2xl font-black text-white mt-1 flex items-center gap-2">
          <span class="w-2 h-2 rounded-full" :class="loading ? 'bg-yellow-500 animate-pulse' : 'bg-emerald-500'"></span>
          {{ loading ? 'İşleniyor...' : 'Hazır' }}
        </div>
      </div>
      <div class="bg-[#121212]/60 backdrop-blur-md border border-white/5 p-5 rounded-2xl shadow-lg hover:border-emerald-500/20 transition group">
        <div class="text-xs font-bold text-slate-500 uppercase tracking-wider group-hover:text-emerald-400 transition-colors">Son Güncelleme</div>
        <div class="text-2xl font-black text-white mt-1 font-mono">{{ lastUpdated || '—' }}</div>
      </div>
    </div>

    <!-- ANA FORM ALANI -->
    <div class="bg-[#121212]/80 border border-white/5 rounded-3xl p-8 backdrop-blur-xl shadow-2xl relative overflow-hidden">
      <!-- Dekoratif Arka Plan Işığı -->
      <div class="absolute top-0 right-0 w-64 h-64 bg-emerald-500/10 rounded-full blur-[100px] -mr-20 -mt-20 pointer-events-none"></div>

      <form @submit.prevent="submitMenu" class="relative z-10">
        
        <!-- Tarih Seçimi -->
        <div class="mb-8 max-w-md">
          <label for="menuDate" class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 block">Menü Tarihi</label>
          <div class="relative group">
            <input
              type="date"
              id="menuDate"
              v-model="menuDate"
              required
              class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-emerald-500 focus:ring-1 focus:ring-emerald-500/50 outline-none transition-all placeholder-slate-600"
            />
            <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-500 group-hover:text-emerald-400 transition-colors">
              <CalendarIcon class="w-5 h-5" />
            </div>
          </div>
        </div>

        <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2 border-b border-white/5 pb-2">
          <ListBulletIcon class="w-5 h-5 text-emerald-400" />
          Menü Öğeleri
        </h3>

        <!-- YEMEKLER LİSTESİ -->
        <transition-group name="list" tag="div" class="space-y-4 mb-8">
          <div
            v-for="(item, index) in menuItems"
            :key="index"
            class="bg-white/5 border border-white/5 rounded-2xl p-5 relative hover:border-white/10 transition-all group"
          >
            <!-- Silme Butonu -->
            <button
              v-if="menuItems.length > 1"
              type="button"
              @click="removeItem(index)"
              class="absolute top-4 right-4 text-slate-600 hover:text-red-400 hover:bg-red-500/10 p-1.5 rounded-lg transition"
              title="Kaldır"
            >
              <XMarkIcon class="w-5 h-5" />
            </button>

            <div class="grid md:grid-cols-2 gap-5 pr-10">
              <div>
                <label :for="'name-' + index" class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5 block">Yemek Adı</label>
                <input
                  type="text"
                  :id="'name-' + index"
                  v-model="item.name"
                  required
                  class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-2.5 text-white focus:border-emerald-500 outline-none transition-all placeholder-slate-700 text-sm"
                  placeholder="Örn: Mercimek Çorbası"
                />
              </div>

              <div>
                <label :for="'desc-' + index" class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5 block">Açıklama / Kalori</label>
                <input
                  type="text"
                  :id="'desc-' + index"
                  v-model="item.description"
                  class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-2.5 text-white focus:border-emerald-500 outline-none transition-all placeholder-slate-700 text-sm"
                  placeholder="Örn: (Terbiyeli, etli) veya 250 cal"
                />
              </div>
            </div>
          </div>
        </transition-group>

        <!-- EKLE BUTONU -->
        <button
          type="button"
          @click="addItem"
          class="w-full py-3 border border-dashed border-white/20 rounded-xl text-slate-400 hover:text-emerald-400 hover:border-emerald-500/30 hover:bg-emerald-500/5 transition flex items-center justify-center gap-2 text-sm font-bold uppercase tracking-wide"
        >
          <PlusCircleIcon class="w-5 h-5" /> Yeni Yemek Ekle
        </button>

        <!-- HATA MESAJI -->
        <transition name="fade">
          <div v-if="error" class="mt-6 p-4 bg-red-500/10 border border-red-500/20 rounded-xl flex items-center gap-3 text-red-300 text-sm">
            <ExclamationTriangleIcon class="w-5 h-5 shrink-0" />
            {{ error }}
          </div>
        </transition>

        <!-- KAYDET BUTONU -->
        <button
          type="submit"
          :disabled="loading"
          class="w-full mt-8 bg-gradient-to-r from-emerald-600 to-teal-600 hover:from-emerald-500 hover:to-teal-500 text-white font-bold py-4 rounded-xl transition-all shadow-lg shadow-emerald-900/20 active:scale-[0.99] disabled:opacity-50 disabled:cursor-not-allowed flex justify-center items-center gap-2"
        >
          <span v-if="loading" class="animate-spin w-5 h-5 border-2 border-white/30 border-t-white rounded-full"></span>
          {{ loading ? 'Kaydediliyor...' : 'Menüyü Yayınla' }}
        </button>

      </form>
    </div>

    <!-- BAŞARI POPUP -->
    <transition name="pop">
      <div v-if="showPopup" class="fixed inset-0 flex items-center justify-center bg-black/60 backdrop-blur-sm z-50 p-4">
        <div class="bg-[#1a1a1a] border border-emerald-500/30 rounded-3xl p-8 shadow-2xl text-center max-w-sm w-full relative overflow-hidden">
          <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-emerald-500 via-teal-500 to-emerald-500"></div>
          <div class="w-16 h-16 bg-emerald-500/10 rounded-full flex items-center justify-center mx-auto mb-4 text-emerald-400 text-3xl border border-emerald-500/20">
            ✓
          </div>
          <h2 class="text-2xl font-bold text-white mb-2">Başarılı!</h2>
          <p class="text-slate-400">Menü sisteme kaydedildi ve yayına alındı. 🍽️</p>
        </div>
      </div>
    </transition>

  </div>
</template>

<script setup>
import { ref } from 'vue'
import useAuth from '../composables/useAuth'
import { 
  ClipboardDocumentListIcon, ArrowLeftIcon, CalendarIcon, ListBulletIcon, 
  XMarkIcon, PlusCircleIcon, ExclamationTriangleIcon 
} from '@heroicons/vue/24/outline'

definePageMeta({ layout: 'admin' })

const { logout } = useAuth()

const loading = ref(false)
const error = ref(null)
const showPopup = ref(false)
const lastUpdated = ref(null)

const todayDate = new Date().toLocaleDateString('tr-TR', {
  day: '2-digit',
  month: 'long',
  year: 'numeric',
})
const menuDate = ref(new Date().toISOString().substring(0, 10))
const menuItems = ref([{ name: '', description: '' }])

const addItem = () => menuItems.value.push({ name: '', description: '' })
const removeItem = (index) => {
  if (menuItems.value.length > 1) menuItems.value.splice(index, 1)
}

const submitMenu = async () => {
  loading.value = true
  error.value = null

  const validItems = menuItems.value.filter((item) => item.name.trim() !== '')
  if (validItems.length === 0) {
    error.value = 'Lütfen menüye en az bir yemek öğesi ekleyin.'
    loading.value = false
    return
  }

  try {
    await $fetch('/api/admin/menu/add', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: { date: menuDate.value, items: validItems },
    })

    // Başarılı kayıt
    menuItems.value = [{ name: '', description: '' }]
    lastUpdated.value = new Date().toLocaleTimeString('tr-TR')
    showPopup.value = true
    setTimeout(() => (showPopup.value = false), 2500)
  } catch (err) {
    console.error('❌ Menü ekleme hatası:', err)
    if (err?.statusCode === 401) await logout()
    error.value = err.data?.message || 'Menü kaydedilirken beklenmedik bir hata oluştu.'
  } finally {
    loading.value = false
  }
}
</script>

<style scoped>
/* Animasyonlar */
.list-enter-active,
.list-leave-active { transition: all 0.3s ease; }
.list-enter-from, .list-leave-to { opacity: 0; transform: translateX(-10px); }

.pop-enter-active, .pop-leave-active { transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
.pop-enter-from, .pop-leave-to { opacity: 0; transform: scale(0.9); }

@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.animate-fade-in { animation: fadeIn 0.5s ease-out forwards; }
</style>