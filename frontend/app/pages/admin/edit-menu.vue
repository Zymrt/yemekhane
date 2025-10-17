<template>
  <div class="space-y-8">
    <header>
      <h1 class="text-3xl font-bold text-gray-800">Menü Düzenle</h1>
      <NuxtLink to="/admin/menus" class="text-sm text-indigo-600 hover:underline mt-1 inline-block">
        &larr; Tüm Menülere Geri Dön
      </NuxtLink>
    </header>

    <form @submit.prevent="updateMenu" class="bg-white shadow-xl rounded-2xl p-8">
      <div class="mb-6">
        <label class="block text-lg font-medium text-gray-700 mb-2">Menü Tarihi:</label>
        <input 
          type="date"
          v-model="menuDate"
          required
          class="w-full border px-3 py-2 rounded-lg"
        >
      </div>

      <h3 class="text-xl font-semibold text-gray-700 mb-4 border-b pb-2">Menü Öğeleri</h3>
      
      <div 
        v-for="(item, index) in menuItems" 
        :key="index" 
        class="p-4 border rounded-lg mb-4 bg-gray-50 relative"
      >
        <button 
          type="button" 
          @click="removeItem(index)" 
          class="absolute top-2 right-2 text-red-500 hover:text-red-700 text-sm"
          v-if="menuItems.length > 1"
        >
          Kaldır
        </button>

        <input 
          v-model="item.name"
          type="text"
          placeholder="Yemek adı"
          class="w-full border px-3 py-2 rounded-lg mb-2"
          required
        >
        <input 
          v-model="item.description"
          type="text"
          placeholder="Açıklama (opsiyonel)"
          class="w-full border px-3 py-2 rounded-lg"
        >
      </div>

      <button 
        type="button"
        @click="addItem"
        class="w-full py-2 border-2 border-dashed border-gray-300 rounded-lg text-gray-600 hover:bg-gray-100 transition mb-6"
      >
        + Yeni Yemek Öğesi
      </button>

      <p v-if="success" class="text-green-600 bg-green-100 p-3 rounded mb-3">{{ success }}</p>
      <p v-if="error" class="text-red-600 bg-red-100 p-3 rounded mb-3">{{ error }}</p>

      <button 
        type="submit"
        :disabled="loading"
        class="w-full py-3 bg-indigo-600 text-white font-bold rounded-lg hover:bg-indigo-700 transition disabled:opacity-50"
      >
        {{ loading ? 'Güncelleniyor...' : 'Menüyü Güncelle' }}
      </button>
    </form>
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

const menuDate = ref('')
const menuItems = ref([])
const loading = ref(false)
const success = ref(null)
const error = ref(null)

onMounted(async () => {
  try {
    const data = await $fetch(`${API_BASE}/menu/all`, {
      headers: { Authorization: `Bearer ${token.value}` }
    })
    const current = data.find(m => m._id === route.query.id)
    if (current) {
      menuDate.value = new Date(current.date).toISOString().slice(0, 10)
      menuItems.value = current.items
    }
  } catch (err) {
    error.value = 'Menü yüklenemedi.'
  }
})

const addItem = () => menuItems.value.push({ name: '', description: '' })
const removeItem = (index) => menuItems.value.splice(index, 1)

const updateMenu = async () => {
  loading.value = true
  success.value = error.value = null
  try {
    await $fetch(`${API_BASE}/menu/${route.query.id}`, {
      method: 'PUT',
      headers: { 
        'Authorization': `Bearer ${token.value}`,
        'Content-Type': 'application/json'
      },
      body: { 
        date: menuDate.value,
        items: menuItems.value 
      }
    })
    success.value = 'Menü başarıyla güncellendi!'
  } catch (err) {
    error.value = 'Güncelleme başarısız oldu.'
  } finally {
    loading.value = false
  }
}
</script>
