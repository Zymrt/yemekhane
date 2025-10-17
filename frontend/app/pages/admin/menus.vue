<template>
  <div class="space-y-8">
    <header>
      <h1 class="text-3xl font-bold text-gray-800">Tüm Menüler</h1>
      <NuxtLink to="/admin" class="text-sm text-indigo-600 hover:underline mt-1 inline-block">
        &larr; Admin Paneline Geri Dön
      </NuxtLink>
    </header>

    <div v-if="loading" class="text-gray-500">Menüler yükleniyor...</div>
    <div v-else-if="menus.length === 0" class="text-gray-500">Henüz menü eklenmemiş.</div>

    <div v-else class="space-y-4">
      <div 
        v-for="menu in menus" 
        :key="menu._id"
        class="border p-4 rounded-lg shadow bg-gray-50 flex justify-between items-center"
      >
        <div>
          <h2 class="text-lg font-semibold text-gray-800">
            {{ new Date(menu.date).toLocaleDateString('tr-TR') }}
          </h2>
          <ul class="text-gray-700 text-sm list-disc ml-5 mt-2">
            <li v-for="(item, i) in menu.items" :key="i">
              {{ item.name }} <span v-if="item.description"> - {{ item.description }}</span>
            </li>
          </ul>
        </div>

        <button 
          @click="deleteMenu(menu._id)"
          class="text-red-600 hover:text-red-800 font-semibold border px-3 py-1 rounded-lg"
        >
          Sil
        </button>
        <NuxtLink 
  :to="`/admin/edit-menu?id=${menu._id}`"
  class="text-blue-600 hover:text-blue-800 font-semibold border px-3 py-1 rounded-lg"
>
  Düzenle
</NuxtLink>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import useAuth from '../composables/useAuth'
import useAuthGuard from '../composables/useAuthGuard'

definePageMeta({ layout: 'admin' })

const { protectAdminPage } = useAuthGuard()
protectAdminPage()

const { token } = useAuth()
const menus = ref([])
const loading = ref(true)
const error = ref(null)

const API_BASE = 'http://127.0.0.1:8000/api/admin'

onMounted(async () => {
  try {
    menus.value = await $fetch(`${API_BASE}/menu/all`, {
      headers: { Authorization: `Bearer ${token.value}` }
    })
  } catch (err) {
    console.error(err)
    error.value = 'Menüler yüklenirken bir hata oluştu.'
  } finally {
    loading.value = false
  }
})

const deleteMenu = async (id) => {
  if (!confirm('Bu menüyü silmek istediğine emin misin?')) return

  try {
    await $fetch(`${API_BASE}/menu/${id}`, {
      method: 'DELETE',
      headers: { Authorization: `Bearer ${token.value}` }
    })
    menus.value = menus.value.filter(m => m._id !== id)
  } catch (err) {
    alert('Silme işlemi başarısız oldu.')
  }
}
</script>
