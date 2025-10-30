<template>
  <div class="min-h-screen bg-gradient-to-br from-[#0f172a] via-[#1e293b] to-[#0f172a] text-white px-6 py-10">
    <div class="max-w-7xl mx-auto">
      <!-- HEADER -->
      <header class="flex flex-col md:flex-row md:items-center md:justify-between mb-8 gap-4">
        <div>
          <h1 class="text-4xl font-extrabold flex items-center gap-3">
            <i class="i-lucide-user-check text-5xl text-emerald-400"></i>
            Kayıt Onayı Bekleyen Kullanıcılar
          </h1>
          <p class="text-sm text-gray-300 mt-1">
            Yeni kayıtları buradan inceleyebilir, belgelerini kontrol edebilir ve onay verebilirsiniz.
          </p>
        </div>

        <NuxtLink
          to="/admin"
          class="text-sm bg-white/10 hover:bg-white/20 border border-white/20 px-4 py-2 rounded-lg text-white/90 transition"
        >
          ← Admin Paneline Geri Dön
        </NuxtLink>
      </header>

      <!-- LOADING / ERROR -->
      <div v-if="loading" class="text-center py-12 text-gray-300">
        <p class="text-lg animate-pulse">🔄 Kullanıcı listesi yükleniyor...</p>
      </div>

      <div
        v-else-if="error"
        class="bg-red-900/40 border border-red-500/40 text-red-200 px-5 py-4 rounded-lg text-center mb-6"
      >
        ⚠️ {{ error }}
      </div>

      <!-- TABLE -->
      <div v-else class="backdrop-blur-lg bg-white/5 border border-white/10 rounded-2xl shadow-xl overflow-hidden">
        <div class="p-5 border-b border-white/10 flex items-center justify-between">
          <h2 class="text-lg font-semibold text-white/90">
            👥 Toplam Bekleyen: <span class="text-emerald-400 font-bold">{{ users.length }}</span>
          </h2>
        </div>

        <div class="overflow-x-auto">
          <table class="min-w-full divide-y divide-white/10">
            <thead class="bg-white/10 text-left text-xs uppercase text-gray-300">
              <tr>
                <th class="px-6 py-3 font-semibold">Ad Soyad</th>
                <th class="px-6 py-3 font-semibold">Telefon / Birim</th>
                <th class="px-6 py-3 font-semibold">Kayıt Tarihi</th>
                <th class="px-6 py-3 font-semibold">Belge</th>
                <th class="px-6 py-3 font-semibold text-center">İşlemler</th>
              </tr>
            </thead>

            <tbody class="divide-y divide-white/10">
              <tr
                v-for="user in users"
                :key="user._id"
                class="hover:bg-white/5 transition"
              >
                <td class="px-6 py-4 text-sm font-medium text-white">
                  {{ user.name }} {{ user.surname }}
                </td>

                <td class="px-6 py-4 text-sm text-gray-300">
                  <div>{{ user.phone }}</div>
                  <div class="text-xs text-gray-400">{{ user.unit }}</div>
                </td>

                <td class="px-6 py-4 text-sm text-gray-300">
                  {{ formatDate(user.created_at) }}
                </td>

                <td class="px-6 py-4">
                  <span
                    v-if="user.document_path"
                    class="px-3 py-1 text-xs font-semibold rounded-full bg-green-500/20 text-green-300 border border-green-500/30"
                  >
                    Yüklendi
                  </span>
                  <span
                    v-else
                    class="px-3 py-1 text-xs font-semibold rounded-full bg-red-500/20 text-red-300 border border-red-500/30"
                  >
                    Yok
                  </span>
                </td>

                <td class="px-6 py-4 text-center space-x-2">
                  <button
                    @click="handleDownload(user._id)"
                    :disabled="!user.document_path"
                    class="px-3 py-1.5 text-xs rounded-md bg-blue-500/20 hover:bg-blue-500/40 border border-blue-400/40 text-blue-200 transition disabled:opacity-50"
                  >
                    Belge
                  </button>
                  <button
                    @click="rejectUser(user._id)"
                    class="px-3 py-1.5 text-xs rounded-md bg-red-500/20 hover:bg-red-500/40 border border-red-400/40 text-red-300 transition"
                  >
                    Reddet
                  </button>
                  <button
                    @click="approveUser(user._id)"
                    class="px-3 py-1.5 text-xs rounded-md bg-emerald-500/20 hover:bg-emerald-500/40 border border-emerald-400/40 text-emerald-300 transition"
                  >
                    Onayla
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- EMPTY STATE -->
      <div v-if="!loading && users.length === 0" class="text-center text-gray-300 py-16 text-lg">
        🎉 Şu anda onay bekleyen kullanıcı bulunmuyor.
      </div>
    </div>
  </div>
</template>


<script setup>
import { ref, onMounted } from 'vue'
import useAuth from '../composables/useAuth'

const { logout } = useAuth()

const users = ref([])
const loading = ref(true)
const error = ref(null)

// ----------------------------------------------------
// ✏️ DEĞİŞİKLİK 1: API_BASE güncellendi
// ----------------------------------------------------
const API_BASE = '/api/admin' // YENİ HALİ (Proxy için)

// 👮 Admin sayfası güvenliği

// 📦 Bekleyen kullanıcıları getir
const fetchPendingUsers = async () => {
  loading.value = true
  error.value = null
  try {
    const response = await $fetch(`${API_BASE}/users/pending`, {
      // credentials: 'include', // ✏️ 'credentials' kaldırıldı
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
    })
    users.value = response
  } catch (err) {
    console.error('❌ Bekleyen kullanıcılar yüklenemedi:', err)
    if (err.statusCode === 401) {
      error.value = 'Oturum süresi dolmuş, yeniden giriş yapmanız gerekiyor.'
      await logout()
      return navigateTo('/login')
    } else {
      error.value = 'Veri yüklenirken bir hata oluştu.'
    }
  } finally {
    loading.value = false
  }
}

// 📄 Belge görüntüleme (💡 $fetch ile iyileştirildi)
const handleDownload = async (userId) => {
  try {
    // ----------------------------------------------------
    // ✏️ DEĞİŞİKLİK 2: Standart 'fetch' yerine '$fetch' kullanıldı
    // ----------------------------------------------------
    const blob = await $fetch(`${API_BASE}/users/${userId}/document`, {
      method: 'GET',
      // credentials: 'include', // <-- Kaldırıldı
      responseType: 'blob' // <-- $fetch'e bunun bir dosya (blob) olduğunu söylüyoruz
    })
    
    // Kalan kod aynı
    const url = window.URL.createObjectURL(blob)
    const link = document.createElement('a')
    link.href = url
    link.download = `belge_${userId}.pdf`
    link.click()
    window.URL.revokeObjectURL(url); // Hafızayı temizle
  } catch (err) {
    console.error('❌ Belge indirilemedi:', err)
    alert('Belge indirilemedi!')
  }
}

// ✅ Kullanıcıyı onayla
const approveUser = async (userId) => {
  if (!confirm('Bu kullanıcıyı onaylamak istediğinizden emin misiniz?')) return
  try {
    await $fetch(`${API_BASE}/users/${userId}/approve`, {
      method: 'POST',
      // credentials: 'include', // ✏️ 'credentials' kaldırıldı
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
    })
    users.value = users.value.filter(u => u._id !== userId)
    alert('✅ Kullanıcı başarıyla onaylandı!')
  } catch {
    alert('Onay sırasında hata oluştu.')
  }
}

// ❌ Kullanıcıyı reddet
const rejectUser = async (userId) => {
  if (!confirm('Bu kullanıcıyı silmek istediğinizden emin misiniz?')) return
  try {
    await $fetch(`${API_BASE}/users/${userId}/reject`, {
      method: 'DELETE',
      // credentials: 'include', // ✏️ 'credentials' kaldırıldı
      headers: {
        Accept: 'application/json',
        'Content-Type': 'application/json',
      },
    })
    users.value = users.value.filter(u => u._id !== userId)
    alert('🚫 Kullanıcı silindi!')
  } catch {
    alert('Kullanıcı silinirken hata oluştu.')
  }
}

// 🕒 Tarih formatı
const formatDate = (dateString) => {
  const options = { year: 'numeric', month: 'short', day: 'numeric' }
  return new Date(dateString).toLocaleDateString('tr-TR', options)
}

onMounted(fetchPendingUsers)
</script>
