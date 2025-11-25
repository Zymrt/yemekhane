<template>
  <div class="max-w-[1200px] mx-auto animate-fade-in">
    
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
      <div>
        <h1 class="text-3xl font-bold text-white flex items-center gap-3">
          <span class="p-2 bg-gradient-to-br from-sky-500/10 to-blue-500/10 rounded-xl border border-sky-500/20 text-sky-400">
            <i :class="isEdit ? 'i-lucide-edit w-8 h-8' : 'i-lucide-plus-circle w-8 h-8'"></i>
          </span>
          {{ isEdit ? 'Menü Düzenle' : 'Menü Oluştur' }}
        </h1>
        <p class="text-slate-400 mt-2 ml-1">Günlük yemek menüsünü sisteme girin ve yönetin.</p>
      </div>
      
      <div class="flex items-center gap-4">
        <div class="bg-[#121212]/80 backdrop-blur-md border border-white/10 rounded-xl px-5 py-2.5 text-sm shadow-lg hidden sm:block">
          <span class="text-slate-400">{{ isEdit ? '✨ Son Düzenleme:' : '📅 Bugün:' }}</span>
          <span class="font-bold text-sky-400 ml-1">{{ lastUpdated || currentDate }}</span>
        </div>
        <NuxtLink to="/admin/menus" class="px-4 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm hover:bg-white/10 transition flex items-center gap-2 text-slate-300">
          <ArrowLeftIcon class="w-4 h-4" /> Geri Dön
        </NuxtLink>
      </div>
    </div>

    <div class="grid md:grid-cols-3 gap-8">
      
      <!-- FORM ALANI -->
      <div class="md:col-span-2 bg-[#121212]/80 border border-white/5 rounded-3xl p-8 backdrop-blur-xl shadow-2xl relative overflow-hidden">
        <!-- Dekoratif Işık -->
        <div class="absolute top-0 right-0 w-64 h-64 bg-sky-500/5 rounded-full blur-[100px] -mr-20 -mt-20 pointer-events-none"></div>

        <form @submit.prevent="handleSubmit" class="relative z-10">
          
          <!-- Tarih Seçimi -->
          <div class="mb-8 max-w-md">
            <label for="menuDate" class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 block">Menü Tarihi</label>
            <div class="relative group">
              <input
                type="date"
                id="menuDate"
                v-model="menuDate"
                required
                class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-sky-500 focus:ring-1 focus:ring-sky-500/50 outline-none transition-all placeholder-slate-600 font-mono"
              />
              <div class="absolute inset-y-0 right-0 flex items-center px-3 pointer-events-none text-slate-500 group-hover:text-sky-400 transition-colors">
                <CalendarIcon class="w-5 h-5" />
              </div>
            </div>
          </div>

          <h3 class="text-lg font-bold text-white mb-6 flex items-center gap-2 border-b border-white/5 pb-2">
            <ListBulletIcon class="w-5 h-5 text-sky-400" />
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
                    class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-2.5 text-white focus:border-sky-500 outline-none transition-all placeholder-slate-700 text-sm"
                    placeholder="Örn: Mercimek Çorbası"
                  />
                </div>

                <div>
                  <label :for="'cal-' + index" class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-1.5 block">Kalori (kcal)</label>
                  <input
                    type="number"
                    :id="'cal-' + index"
                    v-model="item.calorie"
                    class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-2.5 text-white focus:border-sky-500 outline-none transition-all placeholder-slate-700 text-sm font-mono"
                    placeholder="Örn: 250"
                  />
                </div>
              </div>
            </div>
          </transition-group>

          <!-- EKLE BUTONU -->
          <button
            type="button"
            @click="addItem"
            class="w-full py-3 border border-dashed border-white/20 rounded-xl text-slate-400 hover:text-sky-400 hover:border-sky-500/30 hover:bg-sky-500/5 transition flex items-center justify-center gap-2 text-sm font-bold uppercase tracking-wide"
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
            class="w-full mt-8 bg-gradient-to-r from-sky-600 to-blue-600 hover:from-sky-500 hover:to-blue-500 text-white font-bold py-4 rounded-xl transition-all shadow-lg shadow-sky-900/20 active:scale-[0.99] disabled:opacity-50 disabled:cursor-not-allowed flex justify-center items-center gap-2"
          >
            <span v-if="loading" class="animate-spin w-5 h-5 border-2 border-white/30 border-t-white rounded-full"></span>
            {{ loading ? 'İşleniyor...' : (isEdit ? 'Değişiklikleri Kaydet' : 'Menüyü Yayınla') }}
          </button>

        </form>
      </div>

      <!-- ÖNİZLEME KARTI -->
      <div class="md:col-span-1">
        <div class="sticky top-6">
          <h3 class="text-sm font-bold text-slate-500 uppercase tracking-wider mb-4">Canlı Önizleme</h3>
          
          <!-- Kart Görünümü -->
          <div class="bg-white/5 backdrop-blur-md border border-white/10 rounded-3xl p-6 shadow-2xl relative overflow-hidden group hover:border-white/20 transition-all duration-500">
            <div class="absolute top-0 right-0 w-32 h-32 bg-sky-500/10 rounded-full blur-[60px] -mr-10 -mt-10 group-hover:bg-sky-500/20 transition-all duration-500"></div>
            
            <div class="relative z-10">
              <div class="flex items-center gap-3 mb-6 border-b border-white/5 pb-4">
                <div class="p-2 bg-sky-500/10 rounded-lg text-sky-400">
                  <CalendarIcon class="w-6 h-6" />
                </div>
                <div>
                  <div class="text-[10px] text-slate-400 uppercase font-bold tracking-wider">Tarih</div>
                  <div class="font-bold text-white">{{ menuDate ? new Date(menuDate).toLocaleDateString('tr-TR', { day: 'numeric', month: 'long', year: 'numeric' }) : 'Seçilmedi' }}</div>
                </div>
              </div>

              <div class="space-y-3 min-h-[100px]">
                <div v-if="menuItems.length === 0 || (menuItems.length === 1 && !menuItems[0].name)" class="text-center py-8 text-slate-500 text-sm flex flex-col items-center">
                  <ListBulletIcon class="w-8 h-8 mb-2 opacity-20" />
                  Menü içeriği boş.
                </div>
                <div v-else v-for="(item, idx) in menuItems" :key="idx" class="flex justify-between items-start py-2 border-b border-white/5 last:border-0 text-sm">
                  <span class="text-slate-200 font-medium leading-relaxed">{{ item.name || '...' }}</span>
                  <span v-if="item.calorie" class="text-sky-300 text-[10px] font-mono bg-sky-500/10 px-1.5 py-0.5 rounded whitespace-nowrap ml-2">{{ item.calorie }} kcal</span>
                </div>
              </div>

              <div class="mt-6 pt-4 border-t border-white/10 flex justify-between items-center">
                <span class="text-xs text-slate-400 uppercase font-bold">Toplam Kalori</span>
                <span class="text-lg font-bold text-white font-mono">{{ totalCalories }}</span>
              </div>
            </div>
          </div>

        </div>
      </div>

    </div>

    <!-- BAŞARI POPUP -->
    <transition name="pop">
      <div v-if="showPopup" class="fixed inset-0 flex items-center justify-center bg-black/60 backdrop-blur-sm z-50 p-4">
        <div class="bg-[#1a1a1a] border border-sky-500/30 rounded-3xl p-8 shadow-2xl text-center max-w-sm w-full relative overflow-hidden">
          <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-sky-500 via-blue-500 to-sky-500"></div>
          <div class="w-16 h-16 bg-sky-500/10 rounded-full flex items-center justify-center mx-auto mb-4 text-sky-400 text-3xl border border-sky-500/20">
            ✓
          </div>
          <h2 class="text-2xl font-bold text-white mb-2">{{ isEdit ? 'Güncellendi!' : 'Kaydedildi!' }}</h2>
          <p class="text-slate-400 text-sm">{{ isEdit ? 'Menü başarıyla güncellendi.' : 'Yeni menü başarıyla eklendi.' }} 🍽️</p>
        </div>
      </div>
    </transition>

  </div>
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import { useRoute } from 'vue-router'
import useAuth from '../composables/useAuth'
import { 
  ClipboardDocumentListIcon, ArrowLeftIcon, CalendarIcon, ListBulletIcon, 
  XMarkIcon, PlusCircleIcon, ExclamationTriangleIcon 
} from '@heroicons/vue/24/outline'

definePageMeta({ layout: 'admin' })

const { logout } = useAuth()
const route = useRoute()
const API_BASE = '/api/admin'

const menuDate = ref(new Date().toISOString().slice(0, 10))
const menuItems = ref([{ name: '', calorie: '' }])
const loading = ref(false)
const error = ref(null)
const showPopup = ref(false)
const isEdit = ref(false)
const lastUpdated = ref(null)
const currentDate = new Date().toLocaleDateString('tr-TR', {
  day: '2-digit',
  month: 'long',
  year: 'numeric',
})

// Toplam Kalori Hesaplama
const totalCalories = computed(() => {
  return menuItems.value.reduce((sum, item) => sum + (Number(item.calorie) || 0), 0)
})

// ID Helper
const idOf = (doc) => {
  const raw = doc._id ?? doc.id
  if (!raw) return null
  if (typeof raw === 'string') return raw
  if (raw?.$oid) return raw.$oid
  const m = String(raw).match(/ObjectId\(["']?([a-f\d]{24})["']?\)/i)
  return m ? m[1] : null
}

// Düzenleme Modu Kontrolü
onMounted(async () => {
  const menuId = route.query.id
  if (!menuId || menuId === 'undefined') return

  isEdit.value = true
  try {
    const data = await $fetch(`${API_BASE}/menu/all`)
    const current = (data || []).find((m) => idOf(m) === String(menuId))
    if (current) {
      menuDate.value = new Date(current.date).toISOString().slice(0, 10)
      menuItems.value = (current.items || []).map((it) => ({
        name: it.name,
        calorie: it.calorie ?? '',
      }))
    } else {
      error.value = 'Menü verisi bulunamadı.'
    }
  } catch (err) {
    console.error('Hata:', err)
    if (err?.statusCode === 401) await logout()
    error.value = 'Menü yüklenirken hata oluştu.'
  }
})

const addItem = () => menuItems.value.push({ name: '', calorie: '' })
const removeItem = (i) => {
  if (menuItems.value.length > 1) menuItems.value.splice(i, 1)
}

const handleSubmit = async () => {
  loading.value = true
  error.value = null
  
  const validItems = menuItems.value.filter((item) => item.name.trim() !== '')
  if (validItems.length === 0) {
    error.value = 'Lütfen en az bir yemek ekleyin.'
    loading.value = false
    return
  }

  const payload = { date: menuDate.value, items: validItems }

  try {
    if (isEdit.value) {
      await $fetch(`${API_BASE}/menu/${encodeURIComponent(route.query.id)}`, {
        method: 'PUT',
        headers: { 'Content-Type': 'application/json' },
        body: payload,
      })
      lastUpdated.value = new Date().toLocaleTimeString('tr-TR')
    } else {
      await $fetch(`${API_BASE}/menu/add`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: payload,
      })
      // Formu sıfırla
      if (!isEdit.value) {
         menuItems.value = [{ name: '', calorie: '' }]
      }
    }
    
    showPopup.value = true
    setTimeout(() => (showPopup.value = false), 2500)
  } catch (err) {
    console.error('Hata:', err)
    if (err?.statusCode === 401) await logout()
    error.value = 'Kaydetme işlemi başarısız oldu.'
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