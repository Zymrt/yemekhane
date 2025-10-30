<template>
  <div class="min-h-screen bg-gradient-to-br from-[#0f172a] via-[#1e293b] to-[#0f172a] text-white px-6 py-10">
    <!-- HEADER -->
    <header class="max-w-7xl mx-auto flex flex-col md:flex-row md:items-center md:justify-between mb-10 gap-4">
      <div>
        <h1 class="text-4xl font-extrabold text-white flex items-center gap-2 drop-shadow-lg">
          <i class="i-lucide-clipboard-list text-orange-400 text-5xl"></i> Tüm Menüler
        </h1>
        <NuxtLink
          to="/admin"
          class="text-sm text-orange-300 hover:text-orange-400 mt-1 inline-block transition"
        >
          ← Admin Paneline Geri Dön
        </NuxtLink>
      </div>

      <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl px-5 py-3 shadow-md text-sm">
        <span class="text-gray-300">📅 Toplam Menü: </span>
        <span class="font-semibold text-white">{{ menus.length }}</span>
      </div>
    </header>

    <!-- MENÜLER -->
    <div class="max-w-7xl mx-auto">
      <div v-if="loading" class="text-gray-300">Yükleniyor...</div>
      <div v-else-if="error" class="text-red-400">{{ error }}</div>
      <div v-else-if="menus.length === 0" class="text-gray-400 text-lg">Henüz eklenmiş menü bulunamadı.</div>

      <transition-group name="fade" tag="div" class="space-y-6">
        <div
          v-for="(menu, idx) in menus"
          :key="idOf(menu) || idx"
          class="bg-white/10 border border-white/20 rounded-2xl shadow-lg hover:shadow-[0_0_20px_rgba(255,255,255,0.2)] transition p-6 backdrop-blur-md"
        >
          <div class="flex justify-between items-center mb-4">
            <h2 class="text-2xl font-bold text-white flex items-center gap-2">
              <i class="i-lucide-calendar text-orange-300"></i>
              {{ formatDate(menu.date) }}
            </h2>

            <div class="flex gap-3">
              <button
                @click="confirmDelete(idOf(menu))"
                class="px-4 py-2 rounded-lg border border-red-500/50 text-red-300 hover:bg-red-500/30 hover:text-white transition"
              >
                Sil
              </button>
              <NuxtLink
                :to="{ name: 'admin-edit-menu', query: { id: idOf(menu) } }"
                class="px-4 py-2 rounded-lg border border-sky-400/50 text-sky-300 hover:bg-sky-400/30 hover:text-white transition"
              >
                Düzenle
              </NuxtLink>
            </div>
          </div>

          <ul class="list-disc list-inside text-gray-200 space-y-1">
            <li v-for="(item, i) in menu.items" :key="i">
              <span class="font-medium">{{ item.name }}</span>
              <span v-if="item.calorie" class="text-gray-400 text-sm"> — {{ item.calorie }} kcal</span>
            </li>
          </ul>
        </div>
      </transition-group>
    </div>

    <!-- POPUP -->
    <transition name="fade">
      <div
        v-if="showPopup"
        class="fixed inset-0 flex items-center justify-center bg-black/40 backdrop-blur-sm z-50"
      >
        <div
          class="bg-gray-900/90 border border-white/10 rounded-2xl px-10 py-8 shadow-2xl text-center animate-popup max-w-md w-full"
        >
          <h2 class="text-2xl font-bold mb-6 text-white">Bu menüyü silmek istediğine emin misin?</h2>
          <div class="flex justify-center gap-4">
            <button
              @click="deleteMenu(selectedId)"
              class="px-6 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg shadow-md transition"
            >
              Evet, Sil
            </button>
            <button
              @click="showPopup = false"
              class="px-6 py-2 bg-white/10 border border-white/20 rounded-lg hover:bg-white/20 text-gray-200"
            >
              Vazgeç
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

definePageMeta({ layout: 'admin' })



// ESKİ HALİ: const API_BASE = 'http://127.0.0.1:8000/api/admin/menu'
const API_BASE = '/api/admin/menu' // YENİ HALİ (Proxy için)
// ----------------------------------------------------

const menus = ref([])
const loading = ref(true)
const error = ref(null)
const showPopup = ref(false)
const selectedId = ref(null)

// 🧩 Menü ID dönüştürücü
const idOf = (doc) => {
  if (!doc) return null
  const raw = doc._id ?? doc.id
  if (!raw) return null
  if (typeof raw === 'string') return raw
  if (raw?.$oid) return raw.$oid
  const m = String(raw).match(/ObjectId\(["']?([a-f\d]{24})["']?\)/i)
  return m ? m[1] : null
}

// 📦 Menüler getir
const fetchMenus = async () => {
  try {
    const res = await $fetch(`${API_BASE}/all`, {
      method: 'GET',
      // ----------------------------------------------------
      // ✏️ DEĞİŞİKLİK 2: 'credentials' kaldırıldı
      // ----------------------------------------------------
      // credentials: 'include', // <-- PROXY İÇİN GEREK YOK
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
    })
    menus.value = Array.isArray(res) ? res : []
  } catch (err) {
    console.error('❌ Menü listesi alınamadı:', err)
    if (err?.statusCode === 401) {
      error.value = 'Oturum süresi dolmuş, yeniden giriş yapmanız gerekiyor.'
      await logout()
      return navigateTo('/login')
    } else {
      error.value = 'Menüler alınırken bir hata oluştu.'
    }
  } finally {
    loading.value = false
  }
}

// 🗑 Silme popup
const confirmDelete = (id) => {
  if (!id) return console.warn('⚠️ Menü ID geçersiz.')
  selectedId.value = id
  showPopup.value = true
}

// 🚮 Menü sil
const deleteMenu = async (id) => {
  try {
    await $fetch(`${API_BASE}/${encodeURIComponent(id)}`, {
      method: 'DELETE',
      // ----------------------------------------------------
      // ✏️ DEĞİŞİKLİK 2: 'credentials' kaldırıldı
      // ----------------------------------------------------
      // credentials: 'include', // <-- PROXY İÇİN GEREK YOK
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
    })
    menus.value = menus.value.filter((m) => idOf(m) !== id)
    showPopup.value = false
    alert('✅ Menü başarıyla silindi!')
  } catch (err) {
    console.error('❌ Silme hatası:', err)
    alert('Menü silinirken bir hata oluştu.')
  }
}

// 🕒 Tarih formatı
const formatDate = (date) =>
  new Date(date).toLocaleDateString('tr-TR', {
    day: '2-digit',
    month: 'long',
    year: 'numeric',
  })

onMounted(fetchMenus)
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.4s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
@keyframes popup {
  0% {
    transform: scale(0.8);
    opacity: 0;
  }
  60% {
    transform: scale(1.05);
    opacity: 1;
  }
  100% {
    transform: scale(1);
  }
}
.animate-popup {
  animation: popup 0.4s ease;
}
</style>