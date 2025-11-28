<template>
  <div class="max-w-[1200px] mx-auto animate-fade-in pb-10">
    
    <!-- HEADER -->
    <div class="flex flex-col md:flex-row items-center justify-between gap-4 mb-8">
      <div>
        <h1 class="text-3xl font-bold text-white flex items-center gap-3">
          <span class="p-3 bg-gradient-to-br from-orange-500/20 to-red-500/20 rounded-xl border border-orange-500/20 text-orange-400 shadow-lg shadow-orange-900/20">
            <i class="i-lucide-megaphone w-8 h-8"></i>
          </span>
          Duyuru Yönetimi
        </h1>
        <p class="text-slate-400 mt-2 ml-1 text-sm">Sistem genelinde yayınlanan bildirimleri oluşturun ve yönetin.</p>
      </div>
      <NuxtLink to="/admin" class="px-5 py-2.5 bg-white/5 border border-white/10 rounded-xl text-sm font-medium hover:bg-white/10 hover:border-white/20 transition flex items-center gap-2 text-slate-300">
        <i class="i-lucide-arrow-left w-4 h-4"></i> Menüye Dön
      </NuxtLink>
    </div>

    <div class="grid lg:grid-cols-3 gap-8 items-start">
      
      <!-- 📝 SOL PANEL: DUYURU EKLEME FORMU (STICKY) -->
      <div class="lg:col-span-1 lg:sticky lg:top-6 z-10">
        <div class="glass-panel p-6">
          <h2 class="text-lg font-bold text-white mb-6 flex items-center gap-2 border-b border-white/10 pb-4">
            <i class="i-lucide-plus-circle text-orange-500"></i> Yeni Duyuru Oluştur
          </h2>
          
          <div class="space-y-5">
            <div>
              <label class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 block ml-1">Başlık</label>
              <div class="relative">
                <input 
                  v-model="form.title" 
                  type="text" 
                  placeholder="Örn: Yemekhane Bakımı Hakkında" 
                  class="input-field pl-10"
                >
                <i class="i-lucide-type absolute left-3.5 top-3.5 text-slate-500"></i>
              </div>
            </div>
            
            <div>
              <label class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-2 block ml-1">İçerik</label>
              <textarea 
                v-model="form.content" 
                rows="6" 
                placeholder="Duyuru metnini buraya girin..." 
                class="input-field resize-none"
              ></textarea>
            </div>

            <button 
              @click="add" 
              :disabled="loading" 
              class="btn-primary w-full group"
            >
              <span v-if="loading" class="flex items-center gap-2">
                <i class="i-lucide-loader-2 animate-spin"></i> Yayınlanıyor...
              </span>
              <span v-else class="flex items-center justify-center gap-2">
                <i class="i-lucide-send group-hover:translate-x-1 transition-transform"></i> Yayınla
              </span>
            </button>
          </div>
        </div>
      </div>

      <!-- 📜 SAĞ PANEL: DUYURU LİSTESİ (TIMELINE) -->
      <div class="lg:col-span-2 space-y-6">
        
        <div v-if="list.length === 0" class="flex flex-col items-center justify-center py-20 glass-panel border-dashed border-white/20 text-slate-500">
           <i class="i-lucide-bell-off w-16 h-16 mb-4 opacity-30"></i>
           <p>Henüz yayınlanmış bir duyuru yok.</p>
        </div>

        <!-- GÜN GÜN GRUPLANDIRILMIŞ LİSTE -->
        <div v-for="(group, dateIndex) in groupedList" :key="dateIndex" class="relative pl-4 md:pl-0 animate-fade-in-up" :style="{ animationDelay: `${dateIndex * 100}ms` }">
          
          <!-- Tarih Başlığı -->
          <div class="sticky top-0 z-10 flex items-center mb-4 py-2">
            <div class="text-xs font-bold text-orange-300 uppercase tracking-widest bg-orange-500/10 px-3 py-1.5 rounded-lg border border-orange-500/20 backdrop-blur-md shadow-sm">
              {{ group.date }}
            </div>
            <div class="h-[1px] bg-gradient-to-r from-orange-500/20 to-transparent flex-grow ml-4"></div>
          </div>

          <transition-group name="list" tag="div" class="space-y-4">
            <div 
              v-for="item in group.items" 
              :key="item._id" 
              class="group relative glass-panel p-5 hover:border-orange-500/40 transition-all duration-300"
            >
              <!-- Sol Kenar Çizgisi (Dekoratif) -->
              <div class="absolute left-0 top-6 bottom-6 w-1 bg-gradient-to-b from-orange-500 to-red-500 rounded-r opacity-0 group-hover:opacity-100 transition-all duration-300"></div>

              <div class="pl-2">
                <div class="flex justify-between items-start gap-4">
                  <h3 class="font-bold text-white text-lg leading-tight group-hover:text-orange-200 transition-colors">
                    {{ item.title }}
                  </h3>
                  
                  <!-- BURADAKİ ÇAĞRI DÜZELTİLDİ: item._id'nin varlığı kontrol edilmeli -->
                  <button 
                    @click="remove(item._id || item.id)" 
                    class="p-2 text-slate-500 hover:text-red-400 hover:bg-red-500/10 rounded-lg transition-all opacity-0 group-hover:opacity-100 focus:opacity-100 shrink-0"
                    title="Bu duyuruyu sil"
                  >
                    <i class="i-lucide-trash-2 w-5 h-5"></i>
                  </button>
                </div>
                
                <p class="text-slate-400 text-sm mt-3 whitespace-pre-wrap leading-relaxed border-l-2 border-white/5 pl-3">
                  {{ item.content }}
                </p>
                
                <div class="mt-4 flex items-center gap-2 text-[11px] text-slate-500 font-mono uppercase tracking-wide">
                  <i class="i-lucide-clock w-3 h-3"></i>
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
    // 🔥 YENİ EKLENEN KISIM: Duyuru eklendiğinde listeyi yenile
    await fetchAll() 
  } catch(e) { alert('Hata oluştu') } finally { loading.value = false }
}

const remove = async (id) => {
  // 🐛 Hata Kontrolü Eklendi: ID'nin undefined olmadığından emin ol
  if (!id) {
    console.error("Silme hatası: Duyuru ID'si bulunamadı (undefined).")
    return
  }

  if(!confirm('Bu duyuruyu silmek istediğine emin misin?')) return
  try {
    // İsteği doğru ID ile gönder
    await $fetch(`/api/admin/announcements/${id}`, { method: 'DELETE' })
    
    // Frontend'den anında sil (Admin sayfasında)
    list.value = list.value.filter(x => String(x._id) !== String(id) && String(x.id) !== String(id))

    // Kullanıcı sayfasındaki silme işlemi ise Backend (AdminController)
    // tarafındaki Socket sinyali ile gerçekleşir.

  } catch(e){ 
    alert('Silinemedi') 
    console.error(e)
  }
}

onMounted(fetchAll)
</script>

<style scoped>
/* Glassmorphism Classes */
.glass-panel {
  @apply bg-[#121212]/80 border border-white/5 rounded-2xl backdrop-blur-xl shadow-xl transition-all;
}

.input-field {
  @apply w-full bg-black/40 border border-white/10 rounded-xl px-4 py-3 text-white focus:border-orange-500/50 focus:ring-1 focus:ring-orange-500/30 outline-none transition-all placeholder-slate-600 focus:bg-black/60;
}

.btn-primary {
  @apply bg-gradient-to-r from-orange-600 to-red-600 hover:from-orange-500 hover:to-red-500 text-white font-bold py-3.5 rounded-xl transition-all shadow-lg shadow-orange-900/20 active:scale-[0.98] disabled:opacity-50 disabled:cursor-not-allowed;
}

/* Animations */
.list-enter-active,
.list-leave-active { transition: all 0.4s cubic-bezier(0.25, 0.8, 0.25, 1); }
.list-enter-from, .list-leave-to { opacity: 0; transform: translateX(-20px) scale(0.95); }

@keyframes fadeInUp { from { opacity: 0; transform: translateY(20px); } to { opacity: 1; transform: translateY(0); } }
.animate-fade-in-up { animation: fadeInUp 0.6s cubic-bezier(0.2, 0.8, 0.2, 1) forwards; opacity: 0; }
.animate-fade-in { animation: fadeInUp 0.4s ease-out forwards; }
</style>