<template>
  <div class="space-y-8">
    <!-- HEADER -->
    <header class="flex items-center justify-between">
      <div>
        <h1 class="text-4xl font-extrabold text-white drop-shadow-lg">
          {{ isEdit ? '🧾 Menü Düzenle' : '🍽️ Yeni Menü Ekle' }}
        </h1>
        <NuxtLink
          to="/admin/menus"
          class="text-sm text-white/80 hover:text-orange-200 mt-2 inline-block transition"
        >
          ← Tüm Menülere Geri Dön
        </NuxtLink>
      </div>

      <div class="backdrop-blur-md bg-white/20 border border-white/30 rounded-xl px-4 py-2 text-white text-sm shadow-lg">
        {{ isEdit ? '✨ Son düzenleme: ' : '📅 Bugün: ' }}
        <span class="font-semibold">{{ lastUpdated || currentDate }}</span>
      </div>
    </header>

    <!-- FORM -->
    <form
      @submit.prevent="handleSubmit"
      class="relative backdrop-blur-xl bg-white/10 border border-white/20 shadow-2xl rounded-2xl p-8 transition hover:shadow-[0_0_25px_rgba(255,255,255,0.2)]"
    >
      <!-- TARİH -->
      <div class="mb-6">
        <label class="block text-lg font-medium text-white mb-2">📅 Menü Tarihi:</label>
        <input
          type="date"
          v-model="menuDate"
          required
          class="w-full px-4 py-2 rounded-lg border-none bg-white/80 text-gray-800 shadow focus:ring-4 focus:ring-orange-300 outline-none"
        />
      </div>

      <h3 class="text-xl font-semibold text-white/90 mb-4 border-b border-white/30 pb-2">
        🍛 Menü Öğeleri
      </h3>

      <transition-group name="fade" tag="div">
        <div
          v-for="(item, index) in menuItems"
          :key="index"
          class="p-5 rounded-xl bg-white/20 border border-white/30 mb-4 relative hover:bg-white/30 transition"
        >
          <button
            type="button"
            @click="removeItem(index)"
            v-if="menuItems.length > 1"
            class="absolute top-3 right-3 text-red-400 hover:text-red-600 text-sm font-bold"
          >
            ✕
          </button>

          <div class="mb-3">
            <label :for="'name-' + index" class="block text-sm font-medium text-white/90">
              🍲 Yemek Adı
            </label>
            <input
              type="text"
              :id="'name-' + index"
              v-model="item.name"
              required
              class="mt-1 block w-full px-3 py-2 rounded-lg border-none bg-white/80 text-gray-800 shadow focus:ring-2 focus:ring-sky-400 outline-none"
              placeholder="Örn: Mercimek Çorbası"
            />
          </div>

          <div>
            <label :for="'cal-' + index" class="block text-sm font-medium text-white/90">
              🔥 Kalori (kcal)
            </label>
            <input
              type="number"
              :id="'cal-' + index"
              v-model="item.calorie"
              class="mt-1 block w-full px-3 py-2 rounded-lg border-none bg-white/80 text-gray-800 shadow focus:ring-2 focus:ring-sky-400 outline-none"
              placeholder="Örn: 250"
            />
          </div>
        </div>
      </transition-group>

      <!-- YENİ ÖĞE EKLE -->
      <button
        type="button"
        @click="addItem"
        class="w-full py-3 rounded-lg border-2 border-dashed border-white/40 text-white/90 hover:bg-white/10 transition font-semibold"
      >
        + Yeni Yemek Öğesi Ekle
      </button>

      <!-- HATA -->
      <transition name="fade">
        <p v-if="error" class="text-red-300 bg-red-900/40 mt-4 p-3 rounded-lg border border-red-500/40">
          ⚠️ {{ error }}
        </p>
      </transition>

      <!-- KAYDET -->
      <button
        type="submit"
        :disabled="loading"
        class="w-full mt-6 py-3 rounded-lg text-white font-bold text-lg
               bg-gradient-to-r from-orange-400 to-sky-500
               hover:from-orange-300 hover:to-sky-400
               shadow-lg hover:shadow-[0_0_20px_rgba(255,255,255,0.4)]
               transition disabled:opacity-50"
      >
        {{ loading ? '🔄 Kaydediliyor...' : isEdit ? '💾 Menüyü Güncelle' : '➕ Menüyü Kaydet' }}
      </button>
    </form>

    <!-- 🍏 APPLE TARZ POPUP -->
    <transition name="fade">
      <div v-if="showPopup" class="fixed inset-0 flex items-center justify-center bg-black/40 backdrop-blur-sm z-50">
        <div
          class="bg-white/30 backdrop-blur-2xl border border-white/20 rounded-2xl px-10 py-8 shadow-2xl text-center animate-popup text-white"
        >
          <div class="text-5xl mb-3">✅</div>
          <h2 class="text-2xl font-bold">
            {{ isEdit ? 'Menü Güncellendi' : 'Yeni Menü Eklendi' }}
          </h2>
          <p class="mt-2 text-white/80">
            {{ isEdit ? 'Değişiklikler başarıyla kaydedildi 🍽️' : 'Yeni menü eklendi 🎉' }}
          </p>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import useAuth from '../composables/useAuth'
import useAuthGuard from '../composables/useAuthGuard'

definePageMeta({ layout: 'admin' })
const { protectAdminPage } = useAuthGuard()
protectAdminPage()

const { token } = useAuth()
const route = useRoute()

const API_BASE = 'http://127.0.0.1:8000/api/admin'
const menuDate = ref(new Date().toISOString().slice(0, 10))
const menuItems = ref([{ name: '', calorie: '' }])
const loading = ref(false)
const error = ref(null)
const showPopup = ref(false)
const isEdit = ref(false)
const lastUpdated = ref(null)
const currentDate = new Date().toLocaleDateString('tr-TR', { day: '2-digit', month: 'long', year: 'numeric' })

const idOf = (doc) => {
  const raw = doc._id ?? doc.id
  if (!raw) return null
  if (typeof raw === 'string') return raw
  if (raw?.$oid) return raw.$oid
  const m = String(raw).match(/ObjectId\(["']?([a-f\d]{24})["']?\)/i)
  return m ? m[1] : null
}

onMounted(async () => {
  const menuId = route.query.id
  if (!menuId || menuId === 'undefined') {
    console.warn('❌ Menü ID eksik veya geçersiz.')
    return
  }
  isEdit.value = true
  try {
    const data = await $fetch(`${API_BASE}/menu/all`, {
      headers: { Authorization: `Bearer ${token.value}` },
    })
    const current = (data || []).find((m) => idOf(m) === String(menuId))
    if (current) {
      menuDate.value = new Date(current.date).toISOString().slice(0, 10)
      // eski veriler description içeriyorsa fallback:
      menuItems.value = (current.items || []).map(it => ({
        name: it.name,
        calorie: it.calorie ?? it.description ?? ''
      }))
    } else {
      error.value = 'Menü verisi bulunamadı.'
    }
  } catch {
    error.value = 'Menü yüklenemedi.'
  }
})

const addItem = () => menuItems.value.push({ name: '', calorie: '' })
const removeItem = (i) => menuItems.value.splice(i, 1)

const handleSubmit = async () => {
  loading.value = true
  error.value = null
  const payload = { date: menuDate.value, items: menuItems.value }
  try {
    if (isEdit.value) {
      await $fetch(`${API_BASE}/menu/${encodeURIComponent(route.query.id)}`, {
        method: 'PUT',
        headers: { Authorization: `Bearer ${token.value}`, 'Content-Type': 'application/json' },
        body: payload,
      })
      lastUpdated.value = new Date().toLocaleTimeString('tr-TR')
    } else {
      await $fetch(`${API_BASE}/menu/add`, {
        method: 'POST',
        headers: { Authorization: `Bearer ${token.value}`, 'Content-Type': 'application/json' },
        body: payload,
      })
    }
    showPopup.value = true
    setTimeout(() => (showPopup.value = false), 2500)
  } catch {
    error.value = 'Kaydetme işlemi başarısız oldu.'
  } finally {
    loading.value = false
  }
}
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
