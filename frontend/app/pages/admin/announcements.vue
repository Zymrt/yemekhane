<template>
  <div class="max-w-[1200px] mx-auto animate-fade-in">
    
    <!-- HEADER -->
    <div class="flex items-center justify-between mb-8">
      <div>
        <h1 class="text-3xl font-bold text-white flex items-center gap-3">
          <span class="p-2 bg-orange-500/10 rounded-xl border border-orange-500/20 text-orange-400">
            <MegaphoneIcon class="w-8 h-8" />
          </span>
          Duyuru Yönetimi
        </h1>
        <p class="text-slate-400 mt-2 ml-1">Sistem genelinde yayınlanan bildirimleri yönetin.</p>
      </div>
      <NuxtLink to="/admin" class="px-4 py-2 bg-white/5 border border-white/10 rounded-xl text-sm hover:bg-white/10 transition flex items-center gap-2 text-slate-300">
        <ArrowLeftIcon class="w-4 h-4" /> Menüye Dön
      </NuxtLink>
    </div>

    <div class="grid md:grid-cols-3 gap-8">
      
      <!-- SOL PANEL: DUYURU EKLEME FORMU -->
      <div class="md:col-span-1">
        <div class="bg-[#121212]/80 border border-white/5 rounded-3xl p-6 backdrop-blur-xl shadow-2xl sticky top-6">
          <h2 class="text-lg font-bold text-white mb-6 flex items-center gap-2">
            <PlusCircleIcon class="w-5 h-5 text-orange-500" /> Yeni Duyuru
          </h2>
          
          <div class="space-y-5">
            <div>
              <label class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 block">Başlık</label>
              <input 
                v-model="form.title" 
                type="text" 
                placeholder="Örn: Yemekhane Bakımı" 
                class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-orange-500 focus:ring-1 focus:ring-orange-500/50 outline-none transition-all placeholder-slate-600"
              >
            </div>
            
            <div>
              <label class="text-xs font-bold text-slate-500 uppercase tracking-wider mb-2 block">İçerik</label>
              <textarea 
                v-model="form.content" 
                rows="6" 
                placeholder="Duyuru metnini buraya girin..." 
                class="w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-orange-500 focus:ring-1 focus:ring-orange-500/50 outline-none transition-all placeholder-slate-600 resize-none"
              ></textarea>
            </div>

            <button 
              @click="add" 
              :disabled="loading" 
              class="w-full bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-500 hover:to-red-500 text-white font-bold py-3 rounded-xl transition-all shadow-lg shadow-orange-900/20 active:scale-95 flex justify-center items-center gap-2 disabled:opacity-50 disabled:cursor-not-allowed"
            >
              <span v-if="loading" class="animate-spin w-4 h-4 border-2 border-white/30 border-t-white rounded-full"></span>
              <span>{{ loading ? 'Yayınlanıyor...' : 'Yayınla' }}</span>
            </button>
          </div>
        </div>
      </div>

      <!-- SAĞ PANEL: ZAMAN ÇİZELGESİ (TIMELINE) -->
      <div class="md:col-span-2 space-y-8">
        
        <div v-if="list.length === 0" class="flex flex-col items-center justify-center py-20 bg-white/5 rounded-3xl border border-dashed border-white/10 text-slate-500">
           <MegaphoneIcon class="w-16 h-16 mb-4 opacity-20" />
           <p>Henüz yayınlanmış bir duyuru yok.</p>
        </div>

        <!-- GÜN GÜN GRUPLANDIRILMIŞ LİSTE -->
        <div v-for="(group, dateIndex) in groupedList" :key="dateIndex" class="relative pl-4 md:pl-0">
          
          <!-- Tarih Başlığı -->
          <div class="sticky top-0 z-10 flex items-center mb-4 py-2 bg-[#050505]/95 backdrop-blur w-fit pr-4 rounded-r-xl border-y border-r border-white/5 md:border-none md:bg-transparent md:backdrop-blur-none md:w-full md:static">
            <div class="text-xs font-bold text-orange-400 uppercase tracking-widest bg-orange-500/10 px-3 py-1 rounded-lg border border-orange-500/20 shadow-sm">
              {{ group.date }}
            </div>
            <div class="h-[1px] bg-white/10 flex-grow ml-4 hidden md:block"></div>
          </div>

          <transition-group name="list" tag="div" class="space-y-4">
            <div 
              v-for="item in group.items" 
              :key="item._id" 
              class="group relative bg-[#121212]/60 border border-white/5 rounded-2xl p-5 hover:bg-white/5 hover:border-orange-500/30 transition-all duration-300 backdrop-blur-sm"
            >
              <!-- Sol Kenar Çizgisi (Dekoratif) -->
              <div class="absolute left-0 top-4 bottom-4 w-1 bg-orange-500 rounded-r opacity-50 group-hover:opacity-100 transition-opacity"></div>

              <div class="pl-4">
                <div class="flex justify-between items-start">
                  <h3 class="font-bold text-white text-lg group-hover:text-orange-300 transition-colors">{{ item.title }}</h3>
                  
                  <button 
                    @click="remove(item._id)" 
                    class="p-2 text-slate-600 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition opacity-0 group-hover:opacity-100"
                    title="Sil"
                  >
                    <TrashIcon class="w-5 h-5" />
                  </button>
                </div>
                
                <p class="text-slate-400 text-sm mt-2 whitespace-pre-wrap leading-relaxed">{{ item.content }}</p>
                
                <div class="mt-4 flex items-center gap-2 text-[10px] text-slate-600 font-mono uppercase tracking-wide">
                  <ClockIcon class="w-3 h-3" />
                  {{ new Date(item.created_at).toLocaleTimeString('tr-TR', { hour: '2-digit', minute: '2-digit' }) }}
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
import { MegaphoneIcon, ArrowLeftIcon, PlusCircleIcon, TrashIcon, ClockIcon } from '@heroicons/vue/24/outline'

definePageMeta({ layout: 'admin' })

const list = ref([])
const form = ref({ title: '', content: '' })
const loading = ref(false)

const fetchAll = async () => {
  try { 
    const data = await $fetch('/api/admin/announcements')
    // Tarihe göre yeni olandan eskiye sırala
    list.value = data.sort((a, b) => new Date(b.created_at) - new Date(a.created_at))
  } catch(e) { console.error(e) }
}

// 🔥 GÜN GÜN GRUPLAMA MANTIĞI 🔥
const groupedList = computed(() => {
  const groups = []
  
  list.value.forEach(item => {
    // Tarihi formatla (Örn: 25 Kasım 2025)
    const dateLabel = new Date(item.created_at).toLocaleDateString('tr-TR', { 
      day: 'numeric', 
      month: 'long', 
      year: 'numeric',
      weekday: 'long'
    })

    // Bu tarih grubunu bul
    let group = groups.find(g => g.date === dateLabel)
    
    // Yoksa yeni grup oluştur
    if (!group) {
      group = { date: dateLabel, items: [] }
      groups.push(group)
    }
    
    // İtemi gruba ekle
    group.items.push(item)
  })

  return groups
})

const add = async () => {
  if(!form.value.title || !form.value.content) return alert('Lütfen tüm alanları doldurun.')
  loading.value = true
  try {
    await $fetch('/api/admin/announcements', { method: 'POST', body: form.value })
    form.value = { title: '', content: '' }
    await fetchAll()
  } catch(e) { alert('Hata oluştu') } finally { loading.value = false }
}

const remove = async (id) => {
  if(!confirm('Bu duyuruyu silmek istediğine emin misin?')) return
  try {
    await $fetch(`/api/admin/announcements/${id}`, { method: 'DELETE' })
    list.value = list.value.filter(x => x._id !== id)
  } catch(e){ alert('Silinemedi') }
}

onMounted(fetchAll)
</script>

<style scoped>
.list-enter-active,
.list-leave-active { transition: all 0.4s ease; }
.list-enter-from, .list-leave-to { opacity: 0; transform: translateX(-10px); }

@keyframes fadeIn { from { opacity: 0; transform: translateY(10px); } to { opacity: 1; transform: translateY(0); } }
.animate-fade-in { animation: fadeIn 0.5s ease-out forwards; }
</style>