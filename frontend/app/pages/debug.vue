<template>
  <div v-if="isAdmin">
    <div class="min-h-screen bg-gray-100 flex flex-col items-center justify-center p-10 text-gray-800">
      <div class="bg-white shadow-lg rounded-xl p-8 w-full max-w-md space-y-4 text-center">
        <h1 class="text-2xl font-bold mb-4 text-blue-600">🧠 JWT Cookie Debug (Admin)</h1>

        <div v-if="!isLoggedIn">
          <input
            v-model="phone"
            placeholder="Telefon"
            class="border rounded-md p-2 w-full mb-2"
          />
          <input
            v-model="password"
            placeholder="Şifre"
            type="password"
            class="border rounded-md p-2 w-full mb-4"
          />
          <button
            @click="handleLogin"
            class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition"
          >
            Giriş Yap
          </button>
        </div>

        <div v-else>
          <p class="font-semibold">Hoş geldin, {{ user?.name }} {{ user?.surname }}</p>
          <p class="text-sm text-gray-500 mb-4">Birim: {{ user?.unit || 'Bilinmiyor' }}</p>

          <div class="flex justify-center gap-2">
            <button
              @click="getProfile"
              class="bg-green-500 text-white px-3 py-1 rounded-lg hover:bg-green-600 transition"
            >
              Profil Al
            </button>
            <button
              @click="handleLogout"
              class="bg-red-500 text-white px-3 py-1 rounded-lg hover:bg-red-600 transition"
            >
              Çıkış
            </button>
          </div>
        </div>

        <pre class="bg-gray-900 text-green-300 text-left p-4 rounded-lg overflow-x-auto mt-4">
{{ debugText }}
        </pre>
      </div>
    </div>
  </div>

  <div v-else class="min-h-screen flex items-center justify-center text-gray-700">
    <h2 class="text-lg font-semibold">⛔ Bu sayfaya sadece admin erişebilir.</h2>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'
import useAuth from '../composables/useAuth'

const { user, isLoggedIn, isAdmin, login, logout } = useAuth()
const phone = ref('')
const password = ref('')
const debugText = ref('Bekleniyor...')

async function handleLogin() {
  debugText.value = '🔄 Giriş yapılıyor...'
  // Bu 'login' fonksiyonu useAuth'dan geliyor ve ZATEN proxy uyumlu.
  const ok = await login({ phone: phone.value, password: password.value })
  debugText.value = ok
    ? '✅ Giriş başarılı! Cookie ayarlandı.'
    : '❌ Giriş başarısız! Telefon veya şifre hatalı.'
}

async function getProfile() {
  debugText.value = '📡 Profil verisi isteniyor...'
  try {
    // ----------------------------------------------------
    // ✏️ DEĞİŞİKLİK: API isteği proxy uyumlu hale getirildi.
    // ----------------------------------------------------
    const response = await $fetch('/api/user/profile', { // YENİ URL
      // credentials: 'include', // <-- PROXY İÇİN GEREK YOK
    })
    debugText.value = JSON.stringify(response, null, 2)
  } catch (e) {
    debugText.value = '❌ Profil alınamadı: ' + e.message
  }
}

async function handleLogout() {
  debugText.value = '🚪 Çıkış yapılıyor...'
  // Bu 'logout' fonksiyonu useAuth'dan geliyor ve ZATEN proxy uyumlu.
  await logout()
  debugText.value = '✅ Çıkış yapıldı, cookie silindi.'
}
</script>