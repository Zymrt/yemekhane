<template>
  <div class="p-6 sm:p-10 bg-gray-50 min-h-screen">
    <h1 class="text-3xl font-bold text-gray-800 mb-8">Kullanıcı Onay Paneli</h1>
    
    <div v-if="pending" class="text-center text-gray-500">
      <p>Kullanıcılar yükleniyor...</p>
    </div>
    
    <div v-else-if="error" class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative" role="alert">
      <strong class="font-bold">Hata!</strong>
      <span class="block sm:inline"> Veriler çekilirken bir sorun oluştu. Lütfen daha sonra tekrar deneyin.</span>
    </div>
    
    <div v-else-if="users && users.length === 0" class="bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded">
      <p>Onay bekleyen yeni bir kullanıcı bulunmuyor. Harika iş! ✅</p>
    </div>

    <div v-else class="overflow-x-auto bg-white rounded-lg shadow">
      <table class="min-w-full">
        <thead class="bg-gray-100 border-b">
          <tr>
            <th class="py-3 px-5 text-left text-sm font-semibold text-gray-600">Ad Soyad</th>
            <th class="py-3 px-5 text-left text-sm font-semibold text-gray-600">Telefon</th>
            <th class="py-3 px-5 text-left text-sm font-semibold text-gray-600">Birim</th>
            <th class="py-3 px-5 text-center text-sm font-semibold text-gray-600">İşlemler</th>
          </tr>
        </thead>
        <tbody class="text-gray-700">
          <tr v-for="user in users" :key="user._id" class="hover:bg-gray-50">
            <td class="py-3 px-5">{{ user.name }} {{ user.surname }}</td>
            <td class="py-3 px-5">{{ user.phone }}</td>
            <td class="py-3 px-5">{{ user.unit }}</td>
            <td class="py-3 px-5 text-center">
              <div class="flex items-center justify-center gap-2">
                <button @click="viewDocument(user._id)" :disabled="isProcessing" class="text-blue-500 hover:text-blue-700 disabled:opacity-50">Belgeyi Gör</button>
                <button @click="approveUser(user._id)" :disabled="isProcessing" class="bg-green-500 text-white px-3 py-1 rounded-full text-sm hover:bg-green-600 transition-colors disabled:opacity-50">Onayla</button>
                <button @click="rejectUser(user._id)" :disabled="isProcessing" class="bg-red-500 text-white px-3 py-1 rounded-full text-sm hover:bg-red-600 transition-colors disabled:opacity-50">Reddet</button>
              </div>
            </td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
definePageMeta({
  layout: 'admin'
});
import { ref } from 'vue';
import useAuth from '../composables/useAuth';
import useAuthGuard from '../composables/useAuthGuard';

// Sayfa koruması: Sadece adminler görebilir.
const { protectAdminPage } = useAuthGuard();
protectAdminPage();

// Gerekli state ve fonksiyonları yeni useAuth'dan alıyoruz
const { token } = useAuth();

const API_BASE_URL = 'http://127.0.0.1:8000/api/admin';

// Onay bekleyen kullanıcıları çekme
const { data: users, pending, error, refresh } = await useFetch(`${API_BASE_URL}/users/pending`, {
  headers: {
    'Authorization': `Bearer ${token.value}`, // .value kullanmayı unutmuyoruz!
    'Accept': 'application/json',
  }
});

const isProcessing = ref(false);

// ... approveUser, rejectUser, viewDocument fonksiyonların aynı kalabilir,
// sadece token'ı kullanırken token.value olduğundan emin olmalısın.
// Örneğin: headers: { 'Authorization': `Bearer ${token.value}` }
</script>