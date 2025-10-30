<template>
  <div class="min-h-screen bg-gradient-to-br from-[#0f172a] via-[#1e293b] to-[#0f172a] text-white px-6 py-10">
    <!-- HEADER -->
    <header class="max-w-7xl mx-auto flex flex-col md:flex-row md:items-center md:justify-between mb-10 gap-4">
      <div>
        <h1 class="text-4xl font-extrabold flex items-center gap-3">
          <i class="i-lucide-user-check text-5xl text-emerald-400"></i>
          Onay Bekleyen Kullanıcılar
        </h1>
        <NuxtLink
          to="/admin"
          class="text-sm text-orange-300 hover:text-orange-400 mt-1 inline-block transition"
        >
          ← Admin Paneline Geri Dön
        </NuxtLink>
      </div>

      <div
        class="bg-white/10 backdrop-blur-md border border-white/20 rounded-xl px-5 py-3 shadow-md text-sm"
      >
        👥 Toplam Bekleyen: <span class="font-semibold text-white">{{ users.length }}</span>
      </div>
    </header>

    <!-- KULLANICI LİSTESİ -->
    <div class="max-w-7xl mx-auto">
      <div v-if="loading" class="text-gray-300 text-lg">🔄 Liste yükleniyor...</div>
      <div v-else-if="error" class="text-red-400 text-lg">{{ error }}</div>
      <div v-else-if="users.length === 0" class="text-gray-400 text-lg">🎉 Şu anda bekleyen kullanıcı yok.</div>

      <transition-group name="fade" tag="div" class="grid sm:grid-cols-2 xl:grid-cols-3 gap-8">
        <div
          v-for="u in users"
          :key="u._id || u.id"
          class="relative bg-white/10 border border-white/20 rounded-2xl shadow-md hover:shadow-lg transition-all duration-300 backdrop-blur-md p-6"
        >
          <!-- HEADER -->
          <div class="flex items-start justify-between mb-4">
            <div>
              <h2 class="text-xl font-semibold text-white flex items-center gap-2">
                <i class="i-lucide-user text-emerald-400"></i>
                {{ u.name || 'İsimsiz' }} {{ u.surname || '' }}
              </h2>
              <p class="text-gray-400 text-sm">{{ u.phone || u.email }}</p>
            </div>
            <span class="text-xs bg-yellow-400/20 border border-yellow-400/30 text-yellow-300 px-2 py-1 rounded-full">
              Onay Bekliyor
            </span>
          </div>

          <!-- DETAYLAR -->
          <div class="text-gray-300 text-sm space-y-1 mb-4">
            <p><span class="font-semibold text-gray-200">Birim:</span> {{ u.unit || 'Belirtilmemiş' }}</p>
            <p><span class="font-semibold text-gray-200">Kayıt Tarihi:</span>
              {{ new Date(u.created_at).toLocaleDateString('tr-TR') || '—' }}
            </p>
          </div>

          <!-- BUTONLAR -->
          <div class="flex flex-wrap gap-3 justify-between">
            <button
              class="flex-1 px-3 py-2 rounded-lg border border-blue-400/40 text-blue-300 hover:bg-blue-500/30 hover:text-white transition"
              @click="viewDocument(u)"
            >
              Belgeyi Gör
            </button>

            <button
              class="flex-1 px-3 py-2 rounded-lg border border-emerald-400/40 text-emerald-300 hover:bg-emerald-500/30 hover:text-white transition"
              :disabled="actionLoadingId === (u._id || u.id)"
              @click="approveUser(u._id || u.id)"
            >
              {{ actionLoadingId === (u._id || u.id) && actionType === 'approve' ? 'Onaylanıyor...' : 'Onayla' }}
            </button>

            <button
              class="flex-1 px-3 py-2 rounded-lg border border-red-400/40 text-red-300 hover:bg-red-500/30 hover:text-white transition"
              :disabled="actionLoadingId === (u._id || u.id)"
              @click="rejectUser(u._id || u.id)"
            >
              {{ actionLoadingId === (u._id || u.id) && actionType === 'reject' ? 'Reddediliyor...' : 'Reddet' }}
            </button>
          </div>
        </div>
      </transition-group>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import useAuth from '../composables/useAuth'

// ✅ Layout & Guard
definePageMeta({ layout: 'admin' })
// ✅ Auth composable'dan sadece logout çekiyoruz
const { logout } = useAuth()

// ----------------------------------------------------
// ✏️ DEĞİŞİKLİK 1: API_BASE güncellendi
// ----------------------------------------------------
const API_BASE = '/api/admin' // YENİ HALİ (Proxy için)

const users = ref([])
const loading = ref(true)
const error = ref(null)
const actionLoadingId = ref(null)
const actionType = ref(null)

// 🧠 Bekleyen kullanıcıları getir (proxy üzerinden)
const fetchPending = async () => {
  loading.value = true
  error.value = null
  try {
    users.value = await $fetch(`${API_BASE}/users/pending`, {
      // credentials: 'include', // ✏️ 'credentials' kaldırıldı
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
    })
  } catch (err) {
    console.error('❌ Liste alınamadı:', err)
    if (err?.statusCode === 401) {
      alert('Oturum süresi doldu, lütfen tekrar giriş yapın.')
      await logout()
      return navigateTo('/login')
    }
    error.value = 'Liste alınamadı.'
  } finally {
    loading.value = false
  }
}

onMounted(fetchPending)

// 🧾 Belge görüntüleme (💡 $fetch ile iyileştirildi)
const viewDocument = async (u) => {
  try {
    // ----------------------------------------------------
    // ✏️ DEĞİŞİKLİK 2: Standart 'fetch' yerine '$fetch' kullanıldı
    // ----------------------------------------------------
    const blob = await $fetch(`${API_BASE}/users/${u._id || u.id}/document`, {
      // credentials: 'include', // <-- Kaldırıldı
      responseType: 'blob' // <-- $fetch'e bunun bir dosya (blob) olduğunu söylüyoruz
    })

    const fileURL = URL.createObjectURL(blob)
    window.open(fileURL, '_blank')
  } catch (err) {
    console.error('❌ Belge görüntülenemedi:', err)
    alert('Belge görüntülenemedi.')
  }
}

// ✅ Kullanıcı onayla
const approveUser = async (id) => {
  actionLoadingId.value = id
  actionType.value = 'approve'
  try {
    await $fetch(`${API_BASE}/users/${id}/approve`, {
      method: 'POST',
      // credentials: 'include', // ✏️ 'credentials' kaldırıldı
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
    })
    users.value = users.value.filter(u => (u._id || u.id) !== id)
  } catch (err) {
    console.error('❌ Onay hatası:', err)
    if (err?.statusCode === 401) {
      await logout()
      return navigateTo('/login')
    }
    alert('Onaylama başarısız.')
  } finally {
    actionLoadingId.value = null
    actionType.value = null
  }
}

// ❌ Kullanıcı reddet
const rejectUser = async (id) => {
  if (!confirm('Bu kullanıcıyı reddetmek istediğine emin misin?')) return

  actionLoadingId.value = id
  actionType.value = 'reject'
  try {
    await $fetch(`${API_BASE}/users/${id}/reject`, {
      method: 'DELETE',
      // credentials: 'include', // ✏️ 'credentials' kaldırıldı
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
    })
    users.value = users.value.filter(u => (u._id || u.id) !== id)
  } catch (err) {
    console.error('❌ Reddetme hatası:', err)
    if (err?.statusCode === 401) {
      await logout()
      return navigateTo('/login')
    }
    alert('Reddetme başarısız.')
  } finally {
    actionLoadingId.value = null
    actionType.value = null
  }
}
</script>

<style scoped>
.fade-enter-active,
.fade-leave-active {
  transition: opacity 0.3s ease;
}
.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
</style>