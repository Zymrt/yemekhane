<template>
  <div class="max-w-[1200px] mx-auto animate-fade-in">
    
    <!-- HEADER -->
    <div class="flex items-center justify-between mb-8">
      <div>
        <h1 class="text-3xl font-bold text-white flex items-center gap-3">
          <span class="p-2 bg-pink-500/10 rounded-xl border border-pink-500/20 text-pink-400">
            <ChatBubbleLeftRightIcon class="w-8 h-8" />
          </span>
          Gelen Yorumlar
        </h1>
        <p class="text-slate-400 mt-2 ml-1">Kullanıcıların yemekler hakkındaki geri bildirimleri.</p>
      </div>
      <NuxtLink to="/admin" class="px-4 py-2 bg-white/5 border border-white/10 rounded-xl text-sm hover:bg-white/10 transition flex items-center gap-2 text-slate-300">
        <ArrowLeftIcon class="w-4 h-4" /> Menüye Dön
      </NuxtLink>
    </div>

    <!-- İÇERİK ALANI -->
    <div class="grid md:grid-cols-3 gap-8">
      
      <!-- SOL PANEL: ÖZET İSTATİSTİK (EKSTRA EKLENDİ - Şık Durur) -->
      <div class="md:col-span-1">
        <div class="bg-[#121212]/80 border border-white/5 rounded-3xl p-6 backdrop-blur-xl shadow-2xl sticky top-6">
          <h2 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
            <StarIcon class="w-5 h-5 text-yellow-400" /> Memnuniyet Özeti
          </h2>
          
          <div class="flex flex-col items-center justify-center py-6 bg-white/5 rounded-2xl mb-6 border border-white/5">
            <div class="text-5xl font-black text-white">{{ averageRating }}</div>
            <div class="flex gap-1 mt-2 text-yellow-400">
              <StarIcon v-for="n in 5" :key="n" class="w-4 h-4" :class="n <= Math.round(averageRating) ? 'fill-current' : 'text-gray-600 fill-none'" />
            </div>
            <div class="text-xs text-slate-500 mt-2 font-mono uppercase tracking-wide">Genel Ortalama</div>
          </div>

          <div class="space-y-3">
            <div v-for="n in 5" :key="n" class="flex items-center gap-3 text-xs">
              <div class="flex items-center gap-1 w-12 text-slate-400">
                <span>{{ 6-n }}</span> <StarIcon class="w-3 h-3 text-slate-600" />
              </div>
              <div class="flex-1 h-2 bg-white/5 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-pink-500 to-purple-500 rounded-full" :style="`width: ${getPercentage(6-n)}%`"></div>
              </div>
              <div class="w-8 text-right text-slate-500">{{ getCount(6-n) }}</div>
            </div>
          </div>
        </div>
      </div>

      <!-- SAĞ PANEL: YORUM AKIŞI (TIMELINE) -->
      <div class="md:col-span-2 space-y-8">
        
        <div v-if="loading" class="text-center py-20">
          <div class="w-10 h-10 border-4 border-pink-500/30 border-t-pink-500 rounded-full animate-spin mx-auto"></div>
          <p class="text-slate-500 mt-4 text-sm">Yorumlar yükleniyor...</p>
        </div>

        <div v-else-if="reviews.length === 0" class="flex flex-col items-center justify-center py-20 bg-white/5 rounded-3xl border border-dashed border-white/10 text-slate-500">
           <ChatBubbleLeftRightIcon class="w-16 h-16 mb-4 opacity-20" />
           <p>Henüz hiç yorum yapılmamış.</p>
        </div>

        <!-- GÜN GÜN GRUPLANDIRILMIŞ LİSTE -->
        <div v-else v-for="(group, dateIndex) in groupedReviews" :key="dateIndex" class="relative pl-4 md:pl-0">
          
          <!-- Tarih Başlığı -->
          <div class="sticky top-0 z-10 flex items-center mb-6 py-3 bg-[#050505]/95 backdrop-blur w-fit pr-6 rounded-r-2xl border-y border-r border-white/5 md:border-none md:bg-transparent md:backdrop-blur-none md:w-full md:static">
            <div class="text-xs font-bold text-pink-400 uppercase tracking-widest bg-pink-500/10 px-4 py-1.5 rounded-xl border border-pink-500/20 shadow-sm">
              {{ group.date }}
            </div>
            <div class="h-[1px] bg-white/10 flex-grow ml-4 hidden md:block"></div>
          </div>

          <transition-group name="list" tag="div" class="space-y-4">
            <div 
              v-for="review in group.items" 
              :key="review._id" 
              class="group relative bg-[#121212]/60 border border-white/5 rounded-2xl p-6 hover:bg-white/5 hover:border-pink-500/30 transition-all duration-300 backdrop-blur-sm"
            >
              <!-- Sol Kenar Çizgisi -->
              <div class="absolute left-0 top-6 bottom-6 w-1 bg-pink-500 rounded-r opacity-50 group-hover:opacity-100 transition-opacity"></div>

              <div class="pl-4">
                <!-- Üst Kısım: Kullanıcı & Puan -->
                <div class="flex justify-between items-start mb-3">
                  <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-gradient-to-br from-slate-700 to-slate-800 border border-white/10 flex items-center justify-center font-bold text-slate-300 text-sm shadow-inner">
                      {{ review.user?.name?.[0] || '?' }}
                    </div>
                    <div>
                      <h4 class="font-bold text-white text-sm">
                        {{ maskName(review.user?.name) }} {{ maskName(review.user?.surname) }}
                      </h4>
                      <p class="text-[10px] text-slate-500 uppercase tracking-wider">{{ review.user?.unit || 'Birim Yok' }}</p>
                    </div>
                  </div>
                  
                  <div class="flex gap-0.5 bg-black/30 px-2 py-1 rounded-lg border border-white/5">
                    <StarIcon 
                      v-for="n in 5" 
                      :key="n" 
                      class="w-3.5 h-3.5" 
                      :class="n <= review.rating ? 'text-yellow-400 fill-yellow-400' : 'text-slate-700'" 
                    />
                  </div>
                </div>
                
                <!-- Yorum Metni -->
                <div class="bg-black/20 rounded-xl p-3 border border-white/5 mb-3">
                  <p class="text-slate-300 text-sm italic leading-relaxed">
                    "{{ review.comment || 'Yorum yazılmamış, sadece puan verilmiş.' }}"
                  </p>
                </div>
                
                <!-- Tarih -->
                <div class="flex items-center gap-2 text-[10px] text-slate-600 font-mono uppercase tracking-wide justify-end">
                  <ClockIcon class="w-3 h-3" />
                  {{ new Date(review.created_at).toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' }) }}
                </div>
              </div>
            </div>
          </transition-group>
        </div>

      </div>

    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { ChatBubbleLeftRightIcon, ArrowLeftIcon, StarIcon, ClockIcon } from '@heroicons/vue/24/outline'
import { useMask } from '../composables/useMask' // Import et
const { maskName } = useMask()

definePageMeta({ layout: 'admin' })

const reviews = ref([])
const loading = ref(true)

// Yorumları Çek
const fetchReviews = async () => {
  try {
    const data = await $fetch('/api/admin/reviews')
    // Tarihe göre sırala (Yeni en üstte)
    reviews.value = data.sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
  } catch (e) {
    console.error("Yorumlar çekilemedi:", e)
  } finally {
    loading.value = false
  }
}

// 🔥 GÜN GÜN GRUPLAMA MANTIĞI (Timeline) 🔥
const groupedReviews = computed(() => {
  const groups = []
  reviews.value.forEach(review => {
    const dateLabel = new Date(review.created_at).toLocaleDateString('tr-TR', { 
      day: 'numeric', month: 'long', year: 'numeric', weekday: 'long' 
    })
    
    let group = groups.find(g => g.date === dateLabel)
    if (!group) {
      group = { date: dateLabel, items: [] }
      groups.push(group)
    }
    group.items.push(review)
  })
  return groups
})

// 📊 İSTATİSTİK HESAPLAMALARI
const averageRating = computed(() => {
  if (reviews.value.length === 0) return "0.0"
  const sum = reviews.value.reduce((acc, r) => acc + r.rating, 0)
  return (sum / reviews.value.length).toFixed(1)
})

const getCount = (star) => reviews.value.filter(r => r.rating === star).length
const getPercentage = (star) => {
  if (reviews.value.length === 0) return 0
  return (getCount(star) / reviews.value.length) * 100
}

onMounted(fetchReviews)
</script>

<style scoped>
.list-enter-active,
.list-leave-active { transition: all 0.4s ease; }
.list-enter-from, .list-leave-to { opacity: 0; transform: translateX(-10px); }

@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.animate-fade-in { animation: fadeIn 0.5s ease-out forwards; }
</style>