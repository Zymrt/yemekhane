<template>
  <div class="bg-white shadow rounded-lg p-6 mt-6">
    <h3 class="text-xl font-semibold text-gray-800 mb-4">Günün Menüsünü Değerlendir</h3>

    <div v-if="loading" class="text-center py-4 text-gray-500">
      İşleniyor...
    </div>

    <div v-else-if="isSuccess" class="text-center py-4 text-green-600 font-medium">
      <div class="text-4xl mb-2">✅</div>
      Değerli yorumunuz için teşekkürler!
    </div>

    <div v-else>
      <div class="flex items-center mb-4">
        <span class="mr-3 text-gray-700">Puanınız:</span>
        <div class="flex space-x-1">
          <button 
            v-for="star in 5" 
            :key="star" 
            @click="form.rating = star"
            type="button"
            class="focus:outline-none transition-colors duration-200"
          >
            <svg 
              xmlns="http://www.w3.org/2000/svg" 
              class="h-8 w-8" 
              :class="star <= form.rating ? 'text-yellow-400 fill-current' : 'text-gray-300'"
              fill="none" 
              viewBox="0 0 24 24" 
              stroke="currentColor"
            >
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z" />
            </svg>
          </button>
        </div>
      </div>

      <div class="mb-4">
        <label class="block text-gray-700 text-sm font-bold mb-2">Yorumunuz (İsteğe bağlı):</label>
        <textarea
          v-model="form.comment"
          rows="3"
          class="w-full border border-gray-300 rounded-lg p-3 focus:ring-2 focus:ring-blue-500 focus:border-transparent outline-none"
          placeholder="Yemek nasıldı? Fikrinizi belirtin..."
        ></textarea>
      </div>

      <div v-if="errorMessage" class="mb-4 p-3 bg-red-100 text-red-700 rounded text-sm">
        {{ errorMessage }}
      </div>

      <button
        @click="submitReview"
        class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded transition duration-200"
      >
        Yorumu Gönder
      </button>
    </div>
  </div>
</template>

<script setup>
// Parent sayfadan (menu.vue) menü ID'sini alıyoruz
const props = defineProps({
  menuId: {
    type: String,
    required: true
  }
});

const form = reactive({
  rating: 5, // Varsayılan 5 yıldız
  comment: ''
});

const loading = ref(false);
const isSuccess = ref(false);
const errorMessage = ref('');

// Nuxt'ın kendi fetch hook'unu veya axios kullanabilirsin. 
// Burada useFetch veya $fetch örneği yapıyorum (Nuxt 3 standardı)
async function submitReview() {
  loading.value = true;
  errorMessage.value = '';

  try {
    const { data, error } = await useFetch('/api/reviews', {
      method: 'POST',
      body: {
        menu_id: props.menuId,
        rating: form.rating,
        comment: form.comment
      },
      // Backend'e auth token gönderdiğinden emin ol (otomatik gidiyorsa sorun yok)
      headers: {
        // 'Authorization': `Bearer ${token}` // Eğer manuel ekliyorsan burayı aç
      }
    });

    if (error.value) {
      // Backend'den dönen hata mesajını göster (örn: "Satın almadınız")
      errorMessage.value = error.value.data?.message || 'Bir hata oluştu.';
    } else {
      isSuccess.value = true;
    }
  } catch (err) {
    errorMessage.value = 'Sunucu bağlantı hatası.';
  } finally {
    loading.value = false;
  }
}
</script>