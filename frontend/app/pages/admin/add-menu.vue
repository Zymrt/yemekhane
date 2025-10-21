<template>
  <div class="space-y-8">
    <!-- HEADER -->
    <header class="flex items-center justify-between">
      <div>
        <h1 class="text-4xl font-extrabold text-white drop-shadow-lg">🍽️ Menü Yönetimi</h1>
        <NuxtLink
          to="/admin"
          class="text-sm text-white/80 hover:text-orange-200 mt-2 inline-block transition"
        >
          ← Admin Paneline Geri Dön
        </NuxtLink>
      </div>

      <!-- TARİH KARTI -->
      <div class="backdrop-blur-md bg-white/20 border border-white/30 rounded-xl px-4 py-2 text-white text-sm shadow-lg">
        📆 Bugün: <span class="font-semibold">{{ todayDate }}</span>
      </div>
    </header>

    <!-- DASHBOARD KISMI -->
    <div class="grid md:grid-cols-3 gap-6 text-white">
      <div
        class="bg-white/10 backdrop-blur-md border border-white/20 p-4 rounded-xl shadow hover:bg-white/20 transition"
      >
        <div class="text-sm opacity-80">Toplam Menü Öğesi</div>
        <div class="text-2xl font-bold mt-1">{{ menuItems.length }}</div>
      </div>
      <div
        class="bg-white/10 backdrop-blur-md border border-white/20 p-4 rounded-xl shadow hover:bg-white/20 transition"
      >
        <div class="text-sm opacity-80">Durum</div>
        <div class="text-2xl font-bold mt-1">{{ loading ? 'Kaydediliyor...' : 'Hazır' }}</div>
      </div>
      <div
        class="bg-white/10 backdrop-blur-md border border-white/20 p-4 rounded-xl shadow hover:bg-white/20 transition"
      >
        <div class="text-sm opacity-80">Son Güncelleme</div>
        <div class="text-2xl font-bold mt-1">{{ lastUpdated || '—' }}</div>
      </div>
    </div>

    <!-- FORM -->
    <form
      @submit.prevent="submitMenu"
      class="relative backdrop-blur-xl bg-white/10 border border-white/20 shadow-2xl rounded-2xl p-8 transition hover:shadow-[0_0_25px_rgba(255,255,255,0.2)]"
    >
      <div class="mb-6">
        <label for="menuDate" class="block text-lg font-medium text-white mb-2">
          📅 Menü Tarihi:
        </label>
        <input
          type="date"
          id="menuDate"
          v-model="menuDate"
          required
          class="mt-1 block w-full px-4 py-2 border-none rounded-lg bg-white/80 text-gray-800 shadow focus:ring-4 focus:ring-orange-300 outline-none"
        />
      </div>

      <h3 class="text-xl font-semibold text-white/90 mb-4 border-b border-white/30 pb-2">
        🧆 Menü Öğeleri
      </h3>

      <!-- YEMEK ÖĞELERİ -->
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
              🍲 Yemek Adı (Zorunlu)
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
            <label :for="'desc-' + index" class="block text-sm font-medium text-white/90">
              📝 Açıklama (Opsiyonel)
            </label>
            <input
              type="text"
              :id="'desc-' + index"
              v-model="item.description"
              class="mt-1 block w-full px-3 py-2 rounded-lg border-none bg-white/80 text-gray-800 shadow focus:ring-2 focus:ring-sky-400 outline-none"
              placeholder="Örn: (Terbiyeli, etli)"
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
        <p
          v-if="error"
          class="text-red-300 bg-red-900/40 mt-4 p-3 rounded-lg border border-red-500/40"
        >
          ⚠️ {{ error }}
        </p>
      </transition>

      <!-- KAYDET -->
      <button
        type="submit"
        :disabled="loading"
        class="w-full mt-6 py-3 rounded-lg text-white font-bold text-lg
               bg-gradient-to-r from-sky-500 to-orange-400
               hover:from-sky-400 hover:to-orange-300
               shadow-lg hover:shadow-[0_0_20px_rgba(255,255,255,0.4)]
               transition disabled:opacity-50"
      >
        {{ loading ? '⏳ Kaydediliyor...' : '💾 Menüyü Kaydet' }}
      </button>
    </form>

    <!-- 🍏 APPLE TARZ BAŞARI POPUP -->
    <transition name="fade">
      <div
        v-if="showPopup"
        class="fixed inset-0 flex items-center justify-center bg-black/40 backdrop-blur-sm z-50"
      >
        <div
          class="bg-white/30 backdrop-blur-2xl border border-white/20 rounded-2xl px-10 py-8 shadow-2xl text-center animate-popup text-white"
        >
          <div class="text-5xl mb-3">✅</div>
          <h2 class="text-2xl font-bold">Menü Kaydedildi</h2>
          <p class="mt-2 text-white/80">Her şey harika görünüyor 🍽️</p>
        </div>
      </div>
    </transition>
  </div>
</template>

<script setup>
import { ref } from 'vue'
import useAuth from '../composables/useAuth'
import useAuthGuard from '../composables/useAuthGuard'

definePageMeta({ layout: 'admin' })
const { protectAdminPage } = useAuthGuard()
protectAdminPage()

const { logout, token } = useAuth()

const loading = ref(false)
const error = ref(null)
const successMessage = ref(null)
const showPopup = ref(false)
const lastUpdated = ref(null)
const todayDate = new Date().toLocaleDateString('tr-TR', { day: '2-digit', month: 'long', year: 'numeric' })

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

  if (!token.value) {
    await logout()
    return
  }

  try {
    const response = await $fetch('http://127.0.0.1:8000/api/admin/menu/add', {
      method: 'POST',
      headers: {
        Authorization: `Bearer ${token.value}`,
        'Content-Type': 'application/json',
      },
      body: { date: menuDate.value, items: validItems },
    })

    successMessage.value = response.message || 'Menü başarıyla kaydedildi.'
    menuItems.value = [{ name: '', description: '' }]
    lastUpdated.value = new Date().toLocaleTimeString('tr-TR')
    showPopup.value = true

    setTimeout(() => (showPopup.value = false), 2500)
  } catch (err) {
    console.error('Menü ekleme hatası:', err)
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
