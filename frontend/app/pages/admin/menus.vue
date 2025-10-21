<template>
  <div class="space-y-8">
    <!-- HEADER -->
    <header class="flex items-center justify-between">
      <div>
        <h1 class="text-4xl font-extrabold text-white drop-shadow-lg">📜 Tüm Menüler</h1>
        <NuxtLink
          to="/admin"
          class="text-sm text-white/80 hover:text-orange-200 mt-2 inline-block transition"
        >
          ← Admin Paneline Geri Dön
        </NuxtLink>
      </div>

      <div
        class="backdrop-blur-md bg-white/20 border border-white/30 rounded-xl px-4 py-2 text-white text-sm shadow-lg"
      >
        📅 Toplam Menü: <span class="font-semibold">{{ menus.length }}</span>
      </div>
    </header>

    <!-- MENÜ LİSTESİ -->
    <div v-if="loading" class="text-white/80">Yükleniyor...</div>
    <div v-else-if="error" class="text-red-300">{{ error }}</div>
    <div v-else-if="menus.length === 0" class="text-white/70">Henüz eklenmiş menü bulunamadı.</div>

    <transition-group name="fade" tag="div" class="space-y-5">
      <div
        v-for="(menu, idx) in menus"
        :key="idOf(menu) || idx"
        class="p-6 rounded-2xl backdrop-blur-md bg-white/20 border border-white/30 shadow-lg hover:shadow-[0_0_20px_rgba(255,255,255,0.3)] transition"
      >
        <div class="flex justify-between items-center mb-2">
          <h2 class="text-2xl font-semibold text-white drop-shadow">{{ formatDate(menu.date) }}</h2>

          <div class="flex items-center gap-3">
            <button
              @click="confirmDelete(idOf(menu))"
              class="px-4 py-2 rounded-lg text-red-300 border border-red-500/50 hover:bg-red-500/30 hover:text-white transition"
            >
              Sil
            </button>

            <NuxtLink
              :to="{ name: 'admin-edit-menu', query: { id: idOf(menu) } }"
              class="px-4 py-2 rounded-lg text-sky-300 border border-sky-400/50 hover:bg-sky-400/30 hover:text-white transition"
            >
              Düzenle
            </NuxtLink>
          </div>
        </div>

        <ul class="list-disc list-inside text-white/90">
  <li v-for="(item, i) in menu.items" :key="i">
    <span class="font-semibold">{{ item.name }}</span>
    <span v-if="item.description" class="text-white/70"> — {{ item.description }}</span>
  </li>
</ul>
      </div>
    </transition-group>

    <!-- 🧊 ONAY POPUP -->
    <transition name="fade">
      <div
        v-if="showPopup"
        class="fixed inset-0 flex items-center justify-center bg-black/40 backdrop-blur-sm z-50"
      >
        <div
          class="bg-white/30 backdrop-blur-2xl border border-white/20 rounded-2xl px-10 py-8 shadow-2xl text-center animate-popup text-white"
        >
          <h2 class="text-2xl font-bold mb-4">Bu menüyü silmek istediğine emin misin?</h2>
          <div class="flex justify-center gap-4">
            <button
              @click="deleteMenu(selectedId)"
              class="px-6 py-2 bg-red-500 hover:bg-red-600 text-white rounded-lg shadow-md transition"
            >
              Evet, Sil
            </button>
            <button
              @click="showPopup = false"
              class="px-6 py-2 bg-white/30 border border-white/30 rounded-lg hover:bg-white/40 text-white"
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
import useAuthGuard from '../composables/useAuthGuard'

definePageMeta({ layout: 'admin' })
const { protectAdminPage } = useAuthGuard()
protectAdminPage()

const { token, logout } = useAuth()
const API_BASE = 'http://127.0.0.1:8000/api/admin/menu'

const menus = ref([])
const loading = ref(true)
const error = ref(null)
const showPopup = ref(false)
const selectedId = ref(null)

/** _id normalizer: string | { $oid } | ObjectId(...) */
const idOf = (doc) => {
  if (!doc) return null
  const raw = doc._id ?? doc.id
  if (!raw) return null
  if (typeof raw === 'string') return raw
  if (raw?.$oid) return raw.$oid
  // Fallback: ObjectId("...") stringine dönmüşse çıkar
  const m = String(raw).match(/ObjectId\(["']?([a-f\d]{24})["']?\)/i)
  return m ? m[1] : null
}

const fetchMenus = async () => {
  try {
    const res = await $fetch(`${API_BASE}/all`, {
      headers: { Authorization: `Bearer ${token.value}` },
    })
    menus.value = Array.isArray(res) ? res : []
  } catch (err) {
    if (err?.statusCode === 401) await logout()
    error.value = 'Menüler alınamadı.'
  } finally {
    loading.value = false
  }
}

const confirmDelete = (id) => {
  if (!id) {
    console.error('❌ Menü ID boş/invalid, silme iptal.')
    return
  }
  selectedId.value = id
  showPopup.value = true
}

const deleteMenu = async (id) => {
  if (!id) {
    error.value = 'Geçersiz menü ID.'
    return
  }
  try {
    await $fetch(`${API_BASE}/${encodeURIComponent(id)}`, {
      method: 'DELETE',
      headers: { Authorization: `Bearer ${token.value}` },
    })
    menus.value = menus.value.filter((m) => idOf(m) !== id)
  } catch (err) {
    console.error('❌ Silme hatası:', err)
    error.value = 'Silme işlemi başarısız oldu.'
  } finally {
    showPopup.value = false
  }
}

const formatDate = (date) =>
  new Date(date).toLocaleDateString('tr-TR', { day: '2-digit', month: 'long', year: 'numeric' })

onMounted(fetchMenus)
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active { transition: opacity 0.4s ease; }
.fade-enter-from,
.fade-leave-to { opacity: 0; }
@keyframes popup {
  0% { transform: scale(0.8); opacity: 0; }
  60% { transform: scale(1.05); opacity: 1; }
  100% { transform: scale(1); }
}
.animate-popup { animation: popup 0.4s ease; }
</style>
