<template>
  <div class="max-w-[1600px] mx-auto animate-fade-in">
    
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 mb-8">
      <div>
        <h1 class="text-3xl font-bold text-white flex items-center gap-3">
          <span class="p-2 bg-indigo-500/10 rounded-xl border border-indigo-500/20 text-indigo-400">
            <ClipboardDocumentCheckIcon class="w-8 h-8" />
          </span>
          Menü Geçmişi
        </h1>
        <p class="text-slate-400 mt-2 ml-1">Sisteme girilmiş tüm menü kayıtlarını inceleyin ve düzenleyin.</p>
      </div>
      
      <div class="flex items-center gap-4">
        <div class="px-4 py-2 bg-[#121212]/80 border border-white/10 rounded-xl text-sm text-slate-300 backdrop-blur-md">
          Toplam Kayıt: <span class="text-indigo-400 font-bold ml-1">{{ menus.length }}</span>
        </div>
        <NuxtLink to="/admin" class="px-4 py-2 bg-white/5 border border-white/10 rounded-xl text-sm hover:bg-white/10 transition flex items-center gap-2 text-slate-300">
          <ArrowLeftIcon class="w-4 h-4" /> Menüye Dön
        </NuxtLink>
      </div>
    </div>

    <!-- YÜKLENİYOR -->
    <div v-if="loading" class="flex flex-col items-center justify-center py-20">
      <div class="w-10 h-10 border-4 border-indigo-500/30 border-t-indigo-500 rounded-full animate-spin"></div>
      <p class="text-slate-500 mt-4 text-sm">Menüler yükleniyor...</p>
    </div>

    <!-- BOŞ DURUM -->
    <div v-else-if="menus.length === 0" class="flex flex-col items-center justify-center py-20 bg-[#121212]/40 rounded-3xl border border-dashed border-white/10 text-slate-500">
       <ClipboardDocumentListIcon class="w-16 h-16 mb-4 opacity-20" />
       <p>Henüz eklenmiş menü bulunamadı.</p>
    </div>

    <!-- MENÜ LİSTESİ (GRID) -->
    <transition-group v-else name="list" tag="div" class="grid gap-6 sm:grid-cols-2 xl:grid-cols-3">
      <div
        v-for="(menu, idx) in menus"
        :key="idOf(menu) || idx"
        class="group relative bg-[#121212]/60 border border-white/5 rounded-3xl p-6 flex flex-col hover:border-indigo-500/30 hover:shadow-[0_0_30px_rgba(99,102,241,0.1)] transition-all duration-300 backdrop-blur-sm"
      >
        <!-- Kart Başlığı -->
        <div class="flex justify-between items-start mb-6 border-b border-white/5 pb-4">
          <div class="flex items-center gap-3">
            <div class="p-2 bg-indigo-500/10 rounded-lg text-indigo-400">
              <CalendarIcon class="w-6 h-6" />
            </div>
            <div>
              <h2 class="text-lg font-bold text-white group-hover:text-indigo-300 transition-colors">
                {{ formatDate(menu.date) }}
              </h2>
              <p class="text-xs text-slate-500 uppercase tracking-wider font-mono mt-0.5">
                {{ new Date(menu.date).toLocaleDateString('tr-TR', { weekday: 'long' }) }}
              </p>
            </div>
          </div>
        </div>

        <!-- Yemek Listesi -->
        <div class="flex-1 space-y-3 mb-6">
          <div v-for="(item, i) in menu.items" :key="i" class="flex justify-between items-start text-sm">
            <span class="text-slate-300 font-medium leading-relaxed">{{ item.name }}</span>
            <span v-if="item.calorie" class="text-[10px] bg-white/5 px-1.5 py-0.5 rounded text-slate-500 font-mono whitespace-nowrap ml-2">
              {{ item.calorie }} kcal
            </span>
          </div>
        </div>

        <!-- Aksiyon Butonları -->
        <div class="flex gap-3 mt-auto pt-4 border-t border-white/5">
          <NuxtLink
            :to="{ name: 'admin-edit-menu', query: { id: idOf(menu) } }"
            class="flex-1 px-3 py-2 rounded-xl bg-indigo-500/10 hover:bg-indigo-500/20 text-indigo-400 hover:text-indigo-300 border border-indigo-500/20 transition flex items-center justify-center gap-2 text-xs font-bold"
          >
            <PencilSquareIcon class="w-4 h-4" /> Düzenle
          </NuxtLink>

          <button
            @click="confirmDelete(idOf(menu))"
            class="px-3 py-2 rounded-xl bg-red-500/10 hover:bg-red-500/20 text-red-400 hover:text-red-300 border border-red-500/20 transition flex items-center justify-center"
            title="Sil"
          >
            <TrashIcon class="w-4 h-4" />
          </button>
        </div>
      </div>
    </transition-group>

    <!-- SİLME POPUP (MODERN) -->
    <transition name="pop">
      <div v-if="showPopup" class="fixed inset-0 flex items-center justify-center bg-black/60 backdrop-blur-sm z-50 p-4">
        <div class="bg-[#1a1a1a] border border-red-500/30 rounded-3xl p-8 shadow-2xl text-center max-w-sm w-full relative overflow-hidden">
          <div class="absolute top-0 left-0 w-full h-1 bg-gradient-to-r from-red-500 via-orange-500 to-red-500"></div>
          
          <div class="w-16 h-16 bg-red-500/10 rounded-full flex items-center justify-center mx-auto mb-4 text-red-400 border border-red-500/20">
            <TrashIcon class="w-8 h-8" />
          </div>
          
          <h2 class="text-xl font-bold text-white mb-2">Menüyü Sil?</h2>
          <p class="text-slate-400 text-sm mb-6">Bu işlem geri alınamaz. Seçilen tarihe ait menü kalıcı olarak silinecektir.</p>
          
          <div class="flex gap-3">
            <button 
              @click="showPopup = false"
              class="flex-1 px-4 py-2.5 bg-white/5 hover:bg-white/10 border border-white/10 text-slate-300 rounded-xl transition text-sm font-medium"
            >
              Vazgeç
            </button>
            <button 
              @click="deleteMenu(selectedId)"
              class="flex-1 px-4 py-2.5 bg-red-600 hover:bg-red-500 text-white rounded-xl transition text-sm font-bold shadow-lg shadow-red-900/20"
            >
              Evet, Sil
            </button>
          </div>
        </div>
      </div>
    </transition>

  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import useAuth from '../composables/useAuth'
import { 
  ClipboardDocumentCheckIcon, ClipboardDocumentListIcon, ArrowLeftIcon, 
  CalendarIcon, PencilSquareIcon, TrashIcon 
} from '@heroicons/vue/24/outline'

definePageMeta({ layout: 'admin' })

const API_BASE = '/api/admin/menu'
const { logout } = useAuth()

const menus = ref([])
const loading = ref(true)
const error = ref(null)
const showPopup = ref(false)
const selectedId = ref(null)

// Helper: ID Çıkarıcı (MongoDB ObjectId uyumluluğu için)
const idOf = (doc) => {
  if (!doc) return null
  const raw = doc._id ?? doc.id
  if (!raw) return null
  if (typeof raw === 'string') return raw
  if (raw?.$oid) return raw.$oid
  const m = String(raw).match(/ObjectId\(["']?([a-f\d]{24})["']?\)/i)
  return m ? m[1] : null
}

// Menüleri Getir
const fetchMenus = async () => {
  loading.value = true
  try {
    const res = await $fetch(`${API_BASE}/all`, {
      headers: { Accept: 'application/json' }
    })
    // Tarihe göre sırala (en yeni en üstte)
    menus.value = Array.isArray(res) 
      ? res.sort((a, b) => new Date(b.date) - new Date(a.date))
      : []
  } catch (err) {
    console.error('Hata:', err)
    if (err?.statusCode === 401) {
      await logout()
      return navigateTo('/login')
    }
    error.value = 'Menüler alınamadı.'
  } finally {
    loading.value = false
  }
}

// Silme Onayı
const confirmDelete = (id) => {
  if (!id) return
  selectedId.value = id
  showPopup.value = true
}

// Silme İşlemi
const deleteMenu = async (id) => {
  try {
    await $fetch(`${API_BASE}/${encodeURIComponent(id)}`, {
      method: 'DELETE',
      headers: { Accept: 'application/json' }
    })
    menus.value = menus.value.filter((m) => idOf(m) !== id)
    showPopup.value = false
  } catch (err) {
    alert('Silme işlemi başarısız.')
  }
}

// Format Date
const formatDate = (date) =>
  new Date(date).toLocaleDateString('tr-TR', {
    day: '2-digit',
    month: 'long',
    year: 'numeric',
  })

onMounted(fetchMenus)
</script>

<style scoped>
.list-enter-active, .list-leave-active { transition: all 0.4s ease; }
.list-enter-from, .list-leave-to { opacity: 0; transform: scale(0.95); }

.pop-enter-active, .pop-leave-active { transition: all 0.3s cubic-bezier(0.34, 1.56, 0.64, 1); }
.pop-enter-from, .pop-leave-to { opacity: 0; transform: scale(0.9); }

@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.animate-fade-in { animation: fadeIn 0.5s ease-out forwards; }
</style>