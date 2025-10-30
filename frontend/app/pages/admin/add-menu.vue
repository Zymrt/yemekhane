<template>
  <div class="min-h-screen bg-gradient-to-br from-[#0f172a] via-[#1e293b] to-[#0f172a] text-white px-6 py-10">
    <!-- HEADER -->
    <header class="max-w-6xl mx-auto flex flex-col md:flex-row md:items-center md:justify-between mb-10 gap-4">
      <div>
        <h1 class="text-4xl font-extrabold flex items-center gap-3">
          <i class="i-lucide-utensils text-orange-400 text-5xl"></i>
          Menü Yönetimi
        </h1>
        <NuxtLink
          to="/admin"
          class="text-sm text-orange-300 hover:text-orange-400 mt-1 inline-block transition"
        >
          ← Admin Paneline Geri Dön
        </NuxtLink>
      </div>

      <div class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl px-5 py-3 shadow-md text-sm">
        📅 Bugün: <span class="font-semibold">{{ todayDate }}</span>
      </div>
    </header>

    <!-- DASHBOARD MINI INFO -->
    <div class="max-w-6xl mx-auto grid md:grid-cols-3 gap-6 mb-10 text-white">
      <div class="bg-white/10 backdrop-blur-md border border-white/20 p-5 rounded-xl shadow hover:bg-white/20 transition">
        <div class="text-sm opacity-80">Toplam Menü Öğesi</div>
        <div class="text-2xl font-bold mt-1">{{ menuItems.length }}</div>
      </div>
      <div class="bg-white/10 backdrop-blur-md border border-white/20 p-5 rounded-xl shadow hover:bg-white/20 transition">
        <div class="text-sm opacity-80">Durum</div>
        <div class="text-2xl font-bold mt-1">{{ loading ? 'Kaydediliyor...' : 'Hazır' }}</div>
      </div>
      <div class="bg-white/10 backdrop-blur-md border border-white/20 p-5 rounded-xl shadow hover:bg-white/20 transition">
        <div class="text-sm opacity-80">Son Güncelleme</div>
        <div class="text-2xl font-bold mt-1">{{ lastUpdated || '—' }}</div>
      </div>
    </div>

    <!-- FORM -->
    <form
      @submit.prevent="submitMenu"
      class="max-w-6xl mx-auto bg-white/5 border border-white/10 backdrop-blur-2xl shadow-2xl rounded-2xl p-8 transition hover:shadow-[0_0_25px_rgba(255,255,255,0.2)]"
    >
      <!-- TARİH -->
      <div class="mb-8">
        <label for="menuDate" class="block text-lg font-semibold text-white mb-2">
          📅 Menü Tarihi
        </label>
        <input
          type="date"
          id="menuDate"
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
              <label :for="'desc-' + index" class="block text-sm font-medium text-white/90 mb-1">
                📝 Açıklama
              </label>
              <input
                type="text"
                :id="'desc-' + index"
                v-model="item.description"
                class="block w-full px-3 py-2 rounded-lg border-none bg-white/90 text-gray-900 shadow focus:ring-2 focus:ring-sky-400 outline-none"
                placeholder="Örn: (Terbiyeli, etli)"
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
               bg-gradient-to-r from-sky-500 to-orange-400
               hover:from-sky-400 hover:to-orange-300
               shadow-lg hover:shadow-[0_0_20px_rgba(255,255,255,0.3)]
               transition disabled:opacity-50"
      >
        {{ loading ? '⏳ Kaydediliyor...' : '💾 Menüyü Kaydet' }}
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
          <h2 class="text-2xl font-bold mb-2">Menü Kaydedildi</h2>
          <p class="text-gray-300">Her şey harika görünüyor 🍽️</p>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref } from 'vue'
// Sadece 'useAuth' import ediliyor, 'useAuthGuard' değil. (DOĞRU)
import useAuth from '../composables/useAuth'

definePageMeta({ layout: 'admin' })

// Sadece 'logout' alınıyor, 'protectAdminPage' değil. (DOĞRU)
const { logout } = useAuth()

// 'await protectAdminPage()' çağrısı SİLİNDİ. (DOĞRU)

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
    // API isteği proxy'e uygun (DOĞRU)
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
    // 401 hatasında (cookie süresi dolarsa) logout yap (DOĞRU)
    if (err?.statusCode === 401) await logout()
    error.value = err.data?.message || 'Menü kaydedilirken beklenmedik bir hata oluştu.'
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