<template>
  <div class="min-h-screen bg-gray-50 p-6">
    <div class="max-w-6xl mx-auto">
      <header class="py-4 border-b border-gray-200 mb-8">
        <h1 class="text-3xl font-extrabold text-blue-800">Kayıt Onayı Bekleyen Kullanıcılar</h1>
        <p class="text-gray-500 mt-1">Yeni kayıtları buradan inceleyebilir ve onaylayabilirsiniz.</p>
      </header>

      <div v-if="loading" class="text-center py-12 text-gray-500">
        <p class="text-lg">Kullanıcı listesi yükleniyor...</p>
      </div>

      <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
        <strong class="font-bold">Erişim Hatası!</strong>
        <span class="block sm:inline"> {{ error }}</span>
      </div>

      <div v-else>
        <h2 class="text-xl font-bold mb-4 text-gray-700">Toplam Bekleyen: {{ users.length }}</h2>
        
        <div class="overflow-x-auto bg-white shadow-lg rounded-lg">
          <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-gray-50">
              <tr>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ad Soyad</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Telefon / Birim</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Kayıt Tarihi</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Belge Durumu</th>
                <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">İşlemler</th>
              </tr>
            </thead>
            <tbody class="bg-white divide-y divide-gray-200">
              <tr v-for="user in users" :key="user.id">
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ user.name }} {{ user.surname }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                    <div class="font-semibold">{{ user.phone }}</div>
                    <div class="text-xs">{{ user.unit }}</div>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ formatDate(user.created_at) }}</td>
                <td class="px-6 py-4 whitespace-nowrap text-sm">
                  <span 
                    v-if="user.document_path" 
                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800"
                  >
                    Yüklendi
                  </span>
                  <span 
                    v-else 
                    class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800"
                  >
                    Yok
                  </span>
                </td>
                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                  <button 
                    @click="handleDownload(user.id)"
                    :disabled="!user.document_path"
                    class="bg-blue-600 hover:bg-blue-700 text-white py-1 px-3 rounded text-xs transition disabled:opacity-50"
                  >
                    Belgeyi İndir
                  </button>
                  <button 
                    @click="rejectUser(user.id)"
                    class="bg-red-500 hover:bg-red-600 text-white py-1 px-3 rounded text-xs transition"
                  >
                    Reddet/Sil
                  </button>
                  <button 
                    @click="approveUser(user.id)"
                    class="bg-green-500 hover:bg-green-600 text-white py-1 px-3 rounded text-xs transition"
                  >
                    Onayla
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import { useRouter } from 'vue-router'; 
import useAuth from '~/composables/useAuth'; 
import { checkAuthGuard } from '~/utils/auth-guard.js'; 

// --- Veri ve Durum Yönetimi ---
const router = useRouter();
const users = ref([]);
const loading = ref(true);
const error = ref(null);

const { getToken, logout } = useAuth(); 
const API_BASE = 'http://127.0.0.1:8000/api';

// --- Lifecycle ve Erişim Kontrolü ---
onMounted(() => {
    checkAuthGuard(); 
    fetchPendingUsers();
});

// --- API İşlemleri ---
const fetchPendingUsers = async () => {
    loading.value = true;
    error.value = null;
    try {
        const token = getToken(); 
        if (!token) throw new Error("Token not found");
        
        const response = await $fetch(`${API_BASE}/admin/pending-users`, {
            headers: { 'Authorization': `Bearer ${token}` }
        });
        users.value = response;

    } catch (err) {
        console.error("Bekleyen kullanıcılar yüklenemedi:", err);
        if (err.statusCode === 403 || err.statusCode === 401) {
            error.value = "Yönetici yetkiniz yok veya oturum süreniz doldu.";
            logout();
            router.push('/login');
        } else {
             error.value = "Veri yüklenirken bir hata oluştu.";
        }
    } finally {
        loading.value = false;
    }
};

const handleDownload = (userId) => {
    const token = getToken();
    const downloadUrl = `${API_BASE}/admin/download-document/${userId}?token=${token}`;
    window.open(downloadUrl, '_blank');
};

const approveUser = async (userId) => {
    if (!confirm('Bu kullanıcıyı onaylamak ve hesabı aktif etmek istediğinizden emin misiniz?')) return;
    
    try {
        const token = getToken();
        // API çağrısı
        await $fetch(`${API_BASE}/admin/approve-user/${userId}`, {
            method: 'POST', 
            headers: { 'Authorization': `Bearer ${token}` }
        });

        // Başarılı onay sonrası kullanıcıyı listeden kaldır
        users.value = users.value.filter(u => u.id !== userId);
        alert('Kullanıcı başarıyla onaylandı.');

    } catch (err) {
        console.error("Onaylama Hatası:", err);
        alert('Kullanıcı onaylanırken bir hata oluştu.');
    }
};

const rejectUser = async (userId) => {
    if (!confirm('UYARI: Bu kullanıcıyı reddetmek, kaydı kalıcı olarak silecektir. Devam etmek istiyor musunuz?')) return;

    try {
        const token = getToken();
        // API çağrısı
        await $fetch(`${API_BASE}/admin/reject-user/${userId}`, {
            method: 'DELETE', // DELETE metodunu kullanıyoruz
            headers: { 'Authorization': `Bearer ${token}` }
        });

        // Başarılı silme sonrası kullanıcıyı listeden kaldır
        users.value = users.value.filter(u => u.id !== userId);
        alert('Kullanıcı kaydı başarıyla silindi.');

    } catch (err) {
        console.error("Silme Hatası:", err);
        alert('Kayıt silinirken bir hata oluştu.');
    }
};


// --- Yardımcı Fonksiyonlar ---
const formatDate = (dateString) => {
  const options = { year: 'numeric', month: 'short', day: 'numeric' };
  return new Date(dateString).toLocaleDateString('tr-TR', options);
};

</script>