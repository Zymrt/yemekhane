<template>
  <div class="min-h-screen bg-gradient-to-br from-[#0f172a] via-[#1e293b] to-[#0f172a] text-white px-6 py-10">
    <!-- HEADER -->
    <header class="max-w-6xl mx-auto flex flex-col md:flex-row md:items-center md:justify-between mb-10 gap-4">
      <div>
        <h1 class="text-4xl font-extrabold flex items-center gap-3">
          <i :class="isEdit ? 'i-lucide-edit text-sky-400 text-5xl' : 'i-lucide-utensils text-orange-400 text-5xl'"></i>
          {{ isEdit ? 'Menü Düzenle' : 'Yeni Menü Ekle' }}
        </h1>
        <NuxtLink
          to="/admin/menus"
          class="text-sm text-orange-300 hover:text-orange-400 mt-1 inline-block transition"
        >
          ← Tüm Menülere Geri Dön
        </NuxtLink>
      </div>

      <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl px-5 py-3 shadow-md text-sm">
        {{ isEdit ? '✨ Son Düzenleme: ' : '📅 Bugün: ' }}
        <span class="font-semibold">{{ lastUpdated || currentDate }}</span>
      </div>
    </header>

    <!-- FORM -->
    <form
      @submit.prevent="handleSubmit"
      class="max-w-6xl mx-auto bg-white/5 border border-white/10 backdrop-blur-2xl shadow-2xl rounded-2xl p-8 transition hover:shadow-[0_0_25px_rgba(255,255,255,0.2)]"
    >
      <!-- TARİH -->
      <div class="mb-8">
        <label class="block text-lg font-semibold text-white mb-2 flex items-center gap-2">
          <i class="i-lucide-calendar text-orange-300"></i> Menü Tarihi
        </label>
        <input
          type="date"
          v-model="menuDate"
          required
          class="mt-1 block w-full px-4 py-3 rounded-lg border-none bg-white/90 text-gray-900 font-medium shadow focus:ring-4 focus:ring-orange-400 outline-none"
        />
      </div>

      <h3 class="text-xl font-semibold text-white/90 mb-4 border-b border-white/20 pb-2 flex items-center gap-2">
        <i class="i-lucide-list text-sky-300"></i> Menü Öğeleri
      </h3>

      <!-- YEMEKLER -->
      <transition-group name="fade" tag="div">
        <div
          v-for="(item, index) in menuItems"
          :key="index"
          class="p-5 mb-5 rounded-xl bg-white/10 border border-white/20 relative hover:bg-white/15 transition"
        >
          <button
            v-if="menuItems.length > 1"
            type="button"
            @click="removeItem(index)"
            class="absolute top-3 right-3 text-red-400 hover:text-red-600 font-bold"
          >
            ✕
          </button>

          <div class="grid md:grid-cols-2 gap-4">
            <div>
              <label :for="'name-' + index" class="block text-sm font-medium text-white/90 mb-1">
                🍲 Yemek Adı
              </label>
              <input
                type="text"
                :id="'name-' + index"
                v-model="item.name"
                required
                class="block w-full px-3 py-2 rounded-lg border-none bg-white/90 text-gray-900 shadow focus:ring-2 focus:ring-sky-400 outline-none"
                placeholder="Örn: Mercimek Çorbası"
              />
            </div>

            <div>
              <label :for="'cal-' + index" class="block text-sm font-medium text-white/90 mb-1">
                🔥 Kalori (kcal)
              </label>
              <input
                type="number"
                :id="'cal-' + index"
                v-model="item.calorie"
                class="block w-full px-3 py-2 rounded-lg border-none bg-white/90 text-gray-900 shadow focus:ring-2 focus:ring-sky-400 outline-none"
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
        class="w-full py-3 rounded-lg border-2 border-dashed border-white/30 text-white/90 hover:bg-white/10 transition font-semibold"
      >
        + Yeni Yemek Öğesi Ekle
      </button>

      <!-- HATA -->
      <transition name="fade">
        <p
          v-if="error"
          class="text-red-300 bg-red-900/40 mt-5 p-3 rounded-lg border border-red-500/40 text-center"
        >
          ⚠️ {{ error }}
        </p>
      </transition>

      <!-- KAYDET -->
      <button
        type="submit"
        :disabled="loading"
        class="w-full mt-8 py-3 rounded-lg text-white font-bold text-lg
               bg-gradient-to-r from-orange-400 to-sky-500
               hover:from-orange-300 hover:to-sky-400
               shadow-lg hover:shadow-[0_0_20px_rgba(255,255,255,0.3)]
               transition disabled:opacity-50"
      >
        {{ loading ? '🔄 Kaydediliyor...' : isEdit ? '💾 Menüyü Güncelle' : '➕ Menüyü Kaydet' }}
      </button>
    </form>

    <!-- POPUP -->
    <transition name="fade">
      <div
        v-if="showPopup"
        class="fixed inset-0 flex items-center justify-center bg-black/40 backdrop-blur-sm z-50"
      >
        <div
          class="bg-gray-900/90 border border-white/10 rounded-2xl px-10 py-8 shadow-2xl text-center animate-popup max-w-md w-full"
        >
          <div class="text-5xl mb-3 text-green-400">✅</div>
          <h2 class="text-2xl font-bold mb-2">
            {{ isEdit ? 'Menü Güncellendi' : 'Yeni Menü Eklendi' }}
          </h2>
          <p class="text-gray-300">
            {{ isEdit ? 'Değişiklikler başarıyla kaydedildi 🍽️' : 'Yeni menü başarıyla eklendi 🎉' }}
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

definePageMeta({ layout: 'admin' })
const { logout } = useAuth()

const route = useRoute()

// ----------------------------------------------------
// ✏️ DEĞİŞİKLİK 1: API_BASE güncellendi
// ----------------------------------------------------
// ESKİ HALİ: const API_BASE = 'http://127.0.0.1:8000/api/admin'
const API_BASE = '/api/admin' // YENİ HALİ (Proxy için)
// ----------------------------------------------------

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

// 🧩 Menü ID formatlayıcı
const idOf = (doc) => {
  const raw = doc._id ?? doc.id
  if (!raw) return null
  if (typeof raw === 'string') return raw
  if (raw?.$oid) return raw.$oid
  const m = String(raw).match(/ObjectId\(["']?([a-f\d]{24})["']?\)/i)
  return m ? m[1] : null
}

// 📦 Düzenleme modundaysa mevcut menüyü getir
onMounted(async () => {
  const menuId = route.query.id
  if (!menuId || menuId === 'undefined') return

  isEdit.value = true
  try {
    const data = await $fetch(`${API_BASE}/menu/all`, {
      // ✏️ 'credentials' kaldırıldı
      // credentials: 'include', 
    })
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
    console.error('❌ Menü yüklenemedi:', err)
    if (err?.statusCode === 401) await logout()
    error.value = 'Menü yüklenirken bir hata oluştu.'
  }
})

// ➕ Yeni öğe ekleme / çıkarma
const addItem = () => menuItems.value.push({ name: '', calorie: '' })
const removeItem = (i) => menuItems.value.splice(i, 1)

// 💾 Menü ekleme veya güncelleme
const handleSubmit = async () => {
  loading.value = true
  error.value = null
  const payload = { date: menuDate.value, items: menuItems.value }

  try {
    if (isEdit.value) {
      // -----------------------------------
      // ✏️ GÜNCELLEME (PUT) İsteği Düzeltildi
      // -----------------------------------
      await $fetch(`${API_BASE}/menu/${encodeURIComponent(route.query.id)}`, {
        method: 'PUT',
        // credentials: 'include', // <-- Kaldırıldı
        headers: { 'Content-Type': 'application/json' },
        body: payload, // <-- JSON.stringify kaldırıldı
      })
      lastUpdated.value = new Date().toLocaleTimeString('tr-TR')
    } else {
      // -----------------------------------
      // ✏️ EKLEME (POST) İsteği Düzeltildi
      // -----------------------------------
      await $fetch(`${API_BASE}/menu/add`, {
        method: 'POST',
        // credentials: 'include', // <-- Kaldırıldı
        headers: { 'Content-Type': 'application/json' },
        body: payload, // <-- JSON.stringify kaldırıldı
      })
    }
    showPopup.value = true
    setTimeout(() => (showPopup.value = false), 2500)
  } catch (err) {
    console.error('❌ Menü kaydetme hatası:', err)
    if (err?.statusCode === 401) await logout()
    error.value = 'Kaydetme işlemi başarısız oldu.'
  } finally {
    loading.value = false
  }
}
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