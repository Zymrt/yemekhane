<template>
  <div class="space-y-8">
    <header>
      <h1 class="text-3xl font-bold text-gray-800">Onay Bekleyen Kullanıcılar</h1>
      <NuxtLink to="/admin" class="text-sm text-indigo-600 hover:underline mt-1 inline-block">
        &larr; Admin Paneline Geri Dön
      </NuxtLink>
    </header>

    <div v-if="loading" class="text-gray-500">Liste yükleniyor...</div>
    <div v-else-if="error" class="text-red-600">{{ error }}</div>
    <div v-else-if="users.length === 0" class="text-gray-500">Onay bekleyen kullanıcı yok.</div>

    <div v-else class="grid md:grid-cols-2 gap-6">
      <div
        v-for="u in users"
        :key="u.id"
        class="bg-white border rounded-xl p-5 shadow-sm"
      >
        <div class="flex items-start justify-between gap-4">
          <div>
            <div class="text-lg font-semibold text-gray-800">
              {{ u.name || u.fullname || 'İsimsiz' }}
            </div>
            <div class="text-gray-500 text-sm">{{ u.email }}</div>
          </div>

          <span class="px-2 py-1 text-xs rounded bg-amber-100 text-amber-700 border border-amber-200">
            Onay Bekliyor
          </span>
        </div>

        <div class="mt-4 flex flex-wrap gap-3">
          <button
            class="px-3 py-2 rounded-lg border text-blue-600 border-blue-300 hover:bg-blue-50"
            @click="viewDocument(u)"
          >
            Belgeyi Gör
          </button>

          <button
            class="px-3 py-2 rounded-lg border text-emerald-700 border-emerald-300 hover:bg-emerald-50"
            :disabled="actionLoadingId === u.id"
            @click="approveUser(u.id)"
          >
            {{ actionLoadingId === u.id && actionType === 'approve' ? 'Onaylanıyor...' : 'Onayla' }}
          </button>

          <button
            class="px-3 py-2 rounded-lg border text-red-700 border-red-300 hover:bg-red-50"
            :disabled="actionLoadingId === u.id"
            @click="rejectUser(u.id)"
          >
            {{ actionLoadingId === u.id && actionType === 'reject' ? 'Reddediliyor...' : 'Reddet' }}
          </button>
        </div>
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

const { token, logout } = useAuth()

// API BASE
const API_BASE = 'http://127.0.0.1:8000/api/admin'

const users = ref([])
const loading = ref(true)
const error = ref(null)
const actionLoadingId = ref(null)
const actionType = ref(null)

const fetchPending = async () => {
  loading.value = true
  error.value = null
  try {
    users.value = await $fetch(`${API_BASE}/users/pending`, {
      headers: { Authorization: `Bearer ${token.value}` }
    })
  } catch (err) {
    if (err?.statusCode === 401) {
      alert('Oturum süresi doldu.')
      await logout()
      return
    }
    error.value = 'Liste alınamadı.'
    console.error(err)
  } finally {
    loading.value = false
  }
}

onMounted(fetchPending)

// --- FONKSİYONLAR ---

const viewDocument = async (u) => {
  try {
    const res = await fetch(`${API_BASE}/users/${u.id}/document`, {
      headers: { Authorization: `Bearer ${token.value}` }
    })

    if (!res.ok) throw new Error('Belge yüklenemedi.')

    const blob = await res.blob()
    const fileURL = URL.createObjectURL(blob)
    window.open(fileURL, '_blank')
  } catch (err) {
    console.error(err)
    alert('Belge görüntülenemedi.')
  }
}

const approveUser = async (id) => {
  actionLoadingId.value = id
  actionType.value = 'approve'
  try {
    await $fetch(`${API_BASE}/users/${id}/approve`, {
      method: 'POST',
      headers: { Authorization: `Bearer ${token.value}` }
    })
    users.value = users.value.filter(u => u.id !== id)
  } catch (err) {
    if (err?.statusCode === 401) {
      alert('Oturum süresi doldu.')
      await logout()
      return
    }
    alert('Onaylama başarısız.')
    console.error(err)
  } finally {
    actionLoadingId.value = null
    actionType.value = null
  }
}

const rejectUser = async (id) => {
  if (!confirm('Bu kullanıcıyı reddetmek istediğine emin misin?')) return

  actionLoadingId.value = id
  actionType.value = 'reject'
  try {
    await $fetch(`${API_BASE}/users/${id}/reject`, {
      method: 'DELETE',
      headers: { Authorization: `Bearer ${token.value}` }
    })
    users.value = users.value.filter(u => u.id !== id)
  } catch (err) {
    if (err?.statusCode === 401) {
      alert('Oturum süresi doldu.')
      await logout()
      return
    }
    alert('Reddetme başarısız.')
    console.error(err)
  } finally {
    actionLoadingId.value = null
    actionType.value = null
  }
}
</script>
